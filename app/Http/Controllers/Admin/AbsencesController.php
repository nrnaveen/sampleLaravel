<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime, Validator, Auth, File, Image, Config, Mail, DbView;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AbsencesController extends Controller {

	protected $reasons = [];

	protected $types = [
		0 => 'None',
		1 => 'Débuter par une demi-journée',
		2 => 'Finir par une demi-journée',
	];

	protected $rules = [
		'user_id'	=> "required|integer",
		'start'		=> "required|date_format:d/m/Y|after_or_equal:today",
		'end'		=> "required|date_format:d/m/Y|after_or_equal:start",
		'type'		=> "required|in:0,1,2",
		'reason'	=> "required",
	];

	public function __construct(){
		$this->reasons = \App\AbsenceTypes::getReasonLabels()->toArray();
		$this->rules['reason'] = "required|in:" . implode(',', array_keys($this->reasons));
	}

	public function index(Request $request){
		try{
			$queryParam = 'id';
			$sort = 'DESC';
			$search = null;
			$q = null;
			if($request->has('query')){ $queryParam = $request->get('query'); }
			if($request->has('sort')){ $sort = $request->get('sort'); }
			if($request->has('q')){
				$search = $request->get('q');
				$q = '%' . $request->get('q') . '%';
			}
			$query = new \App\Absences();
			if($search){ $query = $query->whereRaw("status LIKE ? OR start LIKE ? OR end LIKE ? OR days LIKE ? OR reason LIKE ?", [$q, $q, $q, $q, $q]); }
			$absences = $query->orderBy($queryParam, $sort)->paginate(15);
			$absences->appends(request()->except(['page', '_token']));
			return View('admin.absences.index', [
				'absences' => $absences,
				'title' => trans('messages.AbsencesManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function newAbsence(){
		try{
			$users = \App\User::all()->pluck('name', 'id');
			return View('admin.absences.add', [
				'title' => trans("messages.AddAbsence"),
				'users' => $users,
				'reasons' => $this->reasons,
				'types' => $this->types,
			]);
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function excelExport(Request $request){
		try{
			$absences = \App\Absences::orderBy('id', 'DESC')->get();
			$emails = [];
			foreach($absences as $key => $value){
				$uservalue = \App\User::where('id', $value->user_id)->first();
				$value->firstname = $uservalue->firstname;
				$value->lastname = $uservalue->lastname;
				$emails[] = $uservalue->email;
			}
			$emails = array_unique($emails);
			$mail = \App\EmailTemplate::where('email', 'exported')->where('status', true)->first();
			$subject = trans('messages.YourDataExported', ['type' => 'Absences', 'by' => 'Admin']);
			$yesText = trans('messages.Yes');
			$noText = trans('messages.No');
			$formatText = '%Y-%m-%d %H:%M:%S';
			if($mail){
				$mails = [];
				$ccmails = [];
				if(!is_null($mail->add_email)){
					$amails = explode(',', $mail->add_email);
					foreach($amails as $key => $amail){
						$amail = trim($amail);
						if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
							if($amail == '{{$user->email}}'){
								foreach($emails as $key => $email){ $mails[] = $email; }
							}
						}else{ $mails[] = $amail; }
					}
				}
				if(!is_null($mail->cc_email)){
					$cmails = explode(',', $mail->cc_email);
					foreach($cmails as $key => $cmail){
						$cmail = trim($cmail);
						if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
							if($cmail == '{{$user->email}}'){
								foreach($emails as $key => $email){ $ccmails[] = $email; }
							}
						}else{ $ccmails[] = $cmail; }
					}
				}
				$mails = array_unique($mails);
				$ccmails = array_unique($ccmails);
				$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'type' => 'Absences', 'by' => 'Admin',])->render();
				$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'type' => 'Absences', 'by' => 'Admin',])->render();
				Mail::raw($template, function($m) use($emails, $subject, $template, $mails, $ccmails){ self::excelExportAction($m, $emails, $subject, $template, $mails, $ccmails); });
			}else{
				Mail::send('emails.exported', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'type' => 'Absences', 'by' => 'Admin',], function($m) use($emails, $subject){ self::excelExportAction($m, $emails, $subject); });
			}
			$datasheet = array();
			$datasheet[0] = array('id', 'firstname', 'lastname', 'status', 'start', 'end', 'startHalf', 'endHalf', 'days', 'reason', 'cancel_reason', 'accepted_date', 'cancelled_date', 'deleted_date', 'client_informed', 'archive');
			foreach($absences as $key => $datanew){
				$datasheet[$key + 1] = array($datanew['id'],
					$datanew['firstname'],
					$datanew['lastname'],
					$datanew['status'],
					$datanew['start'],
					$datanew['end'],
					$datanew['startHalf'],
					$datanew['endHalf'],
					$datanew['days'],
					$datanew['reason'],
					$datanew['cancel_reason'],
					$datanew['accepted_date'],
					$datanew['cancelled_date'],
					$datanew['deleted_date'],
					$datanew['client_informed'],
					$datanew['archive'],
				);
			}
			return Excel::create('absenses', function($excel) use($datasheet){
				return $excel->sheet('absense', function($sheet) use($datasheet){
					$sheet->fromArray($datasheet);
				});
			})->download('xlsx');
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function postAbsence(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$absence = $request->only(['user_id', 'start', 'end', 'type', 'reason', ]);
			$user = \App\User::find($absence['user_id']);
			if(!$user){ return redirect('/admin/absences')->withMessage(trans("messages.UserNotFound")); }
			$absences = \App\Absences::where('user_id', $user->id)->get();
			$cras = \App\CRA::where('user_id', $user->id)->get();
			$penalties = \App\Penalty::where('user_id', $user->id)->get();
			$absence['startHalf'] = 0;
			$absence['endHalf'] = 0;
			if($request->get('type') == 1){ $absence['startHalf'] = 1; }
			if($request->get('type') == 2){ $absence['endHalf'] = 1; }
			$data_start = Carbon::createFromFormat('d/m/Y', $absence['start'])->format('Y-m-d');
			$data_end = Carbon::createFromFormat('d/m/Y', $absence['end'])->format('Y-m-d');
			$startcount = $absences->whereIn('start',[$data_start, $data_end])->count();
			$endcount = $absences->WhereIn('end',[$data_start, $data_end])->count();
			if($startcount > 0 || $endcount){
				return redirect('/admin/absences')->withError(trans("messages.DateAlreadyExists"));
			}
			list($start_date, $end_date) = [$absence['start'], $absence['end']];
			list($startDate, $endDate) = [Carbon::createFromFormat('d/m/Y', $absence['start']), Carbon::createFromFormat('d/m/Y', $absence['end'])];
			list($start, $end) = [Carbon::createFromFormat('d/m/Y', $absence['start'])->format('Y-m-d'), Carbon::createFromFormat('d/m/Y', $absence['end'])->format('Y-m-d')];
			$dayCount = ($startDate->diffInDays($endDate) + 1);
			list($startDay, $endDay) = [date('N', strtotime($start)), date('N', strtotime($end))];
			if(($startDay == 7 || $startDay == 6) || ($endDay == 7 || $endDay == 6)){
				return redirect()->back()->withError(trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
			}
			$dates = Config::get('leave_dates');
			if(in_array($absence['start'], $dates) || in_array($absence['end'], $dates)){
				return redirect()->back()->withError(trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
			}
			$weekdayCounter = 0;
			while($start <= $end){
				$day = date('N', strtotime($start));
				if($day == 7 || $day == 6){ $weekdayCounter++; }else{
					$day = date('d/m/Y', strtotime($start));
					if(in_array($day, $dates)){ $weekdayCounter++; }
				}
				$start = date("Y-m-d", strtotime($start . "+1 day"));
			}
			if($weekdayCounter == $dayCount){
				return redirect()->back()->withError(trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
			}
			$absence['start'] = Carbon::createFromFormat('d/m/Y', $absence['start'])->format('Y-m-d');
			$absence['end'] = Carbon::createFromFormat('d/m/Y', $absence['end'])->format('Y-m-d');
			if(($absence['startHalf'] == 1 || $absence['startHalf'] == true) || ($absence['endHalf'] == 1 || $absence['endHalf'] == true)){
				$conflictAbsences = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
				foreach($conflictAbsences as $key => $conflictAbsence){
					if($conflictAbsence->start == $conflictAbsence->end){ $conflicts = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == $absence['endHalf']) && ($item->startHalf == $absence['startHalf'])); }); }else{
						$conflicts = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == true)); });
						if($absence['startHalf'] == 1 || $absence['startHalf'] == true){ $conflicts = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->startHalf == true)); }); }
					}
					if($conflicts->count() > 0){
						return redirect()->back()->withError(trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
					}
				}
				$conflictcras = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
				foreach($conflictcras as $key => $conflictcra){
					if($conflictcra->start == $conflictcra->end){ $conflicts = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == $absence['endHalf']) && ($item->startHalf == $absence['startHalf'])); }); }else{
						$conflicts = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == true)); });
						if($absence['startHalf'] == 1 || $absence['startHalf'] == true){ $conflicts = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->startHalf == true)); }); }
					}
					if($conflicts->count() > 0){
						return redirect()->back()->withError(trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
					}
				}
				$conflictpenalties = $penalties->filter(function($item) use($absence){ return (($item->beginning <= $absence['end']) && ($item->ending >= $absence['start'])); });
				if($conflictpenalties->count() > 0){
					return redirect()->back()->withError(trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
				}
			}else{
				$conflictAbsences = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
				$conflictcras = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
				$conflictpenalties = $penalties->filter(function($item) use($absence){ return (($item->beginning <= $absence['end']) && ($item->ending >= $absence['start'])); });
				if($conflictAbsences->count() > 0 || $conflictcras->count() > 0 || $conflictpenalties->count() > 0){
					return redirect()->back()->withError(trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]))->withInput();
				}
			}
			$days = ($dayCount - $weekdayCounter);
			if($dayCount == 1){
				if($absence['endHalf'] || $absence['startHalf']){ $days = $days - 0.5; }
			}else{
				if($absence['endHalf']){ $days = $days - 0.5; }
				if($absence['startHalf']){ $days = $days - 0.5; }
			}
			$insert = \App\Absences::create(['client_informed' => true, 'days' => $days,
				'end' => $absence['end'], 'endHalf' => $absence['endHalf'],
				'reason' => $absence['reason'], 'start' => $absence['start'],
				'startHalf' => $absence['startHalf'], 'status' => 'approved', 
				'user_id' => $user->id,
			]);
			if($insert) return redirect('/admin/absences')->withMessage(trans("messages.YourRequestSavedSuccessFully"));
			return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$absence = \App\Absences::find($id);
			if($absence) return View('admin.absences.edit', ['absence' => $absence, 'title' => trans('messages.AbsenceEdit'),]);
			else return redirect('/admin/absences')->withError(trans('messages.AbsenceNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/absences')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$absence = \App\Absences::find($id);
			if($absence){
				$data = $request->only(['status', 'cancel_reason']);
				$dt = new DateTime();
				$isUpdated = ($data['status'] == $absence->status) ? false : true;
				if($data['status'] == "approved") $data['accepted_date'] = $dt->format('Y-m-d H:i:s');
				else if($data['status'] == "cancelled_by_admin") $data['cancelled_date'] = $dt->format('Y-m-d H:i:s');
				else $data['deleted_date'] = $dt->format('Y-m-d H:i:s');
				$insert = $absence->update($data);
				if($insert){
					$yesText = trans('messages.Yes');
					$noText = trans('messages.No');
					$formatText = '%Y-%m-%d %H:%M:%S';
					if($isUpdated && $data['status'] != "pending"){
						$userData = $absence->user;
						$manager = \App\UserManager::where('consultant_id', $userData->id)->first();
						$managerData = null;
						if($manager){ $managerData = $manager->manager; }
						$declined_user = $approved_user = Auth::guard('admins')->user();
						if($data['status'] == 'approved'){
							if($managerData){
								$adminapproved = \App\EmailTemplate::where('email', 'adminapproved')->where('status', true)->first();
								$subject = trans('messages.ManagerAbsenceApproved', ['name' => $userData->name, 'by' => 'Admin']);
								if($adminapproved){
									$mails = [];
									$ccmails = [];
									if(!is_null($adminapproved->add_email)){
										$amails = explode(',', $adminapproved->add_email);
										foreach($amails as $key => $amail){
											$amail = trim($amail);
											if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
												if($amail == '{{$user->email}}'){ $mails[] = $user->email; }
												if($amail == '{{$manager->email}}'){
													if($manager){
														$managerData = $manager->manager;
														$mails[] = $managerData->email;
													}
												}
												if($amail == '{{$admin->email}}'){
													$admin = env('ADMIN_MAIL');
													if($admin){ $mails[] = $admin; }
												}
											}else{ $mails[] = $amail; }
										}
									}
									if(!is_null($adminapproved->cc_email)){
										$cmails = explode(',', $adminapproved->cc_email);
										foreach($cmails as $key => $cmail){
											$cmail = trim($cmail);
											if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
												if($cmail == '{{$user->email}}'){ $ccmails[] = $user->email; }
												if($cmail == '{{$manager->email}}'){
													if($manager){
														$managerData = $manager->manager;
														$ccmails[] = $managerData->email;
													}
												}
												if($cmail == '{{$admin->email}}'){
													$admin = env('ADMIN_MAIL');
													if($admin){ $ccmails[] = $admin; }
												}
											}else{ $ccmails[] = $cmail; }
										}
									}
									$mails = array_unique($mails);
									$ccmails = array_unique($ccmails);
									$template = DbView::make($adminapproved)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
									$subject = DbView::make($adminapproved)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
									Mail::raw($template, function($m) use($managerData, $userData, $subject, $template, $mails, $ccmails){ self::postEditAdminApprovedAction($m, $managerData, $userData, $subject, $template, $mails, $ccmails); });
								}else{
									Mail::send('emails.adminapproved', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($managerData, $userData, $subject){ self::postEditAdminApprovedAction($m, $managerData, $userData, $subject); });
								}
							}
							$approved = \App\EmailTemplate::where('email', 'approved')->where('status', true)->first();
							$subject = trans("messages.YourAbsenceApproved");
							if($approved){
								$mails = [];
								$ccmails = [];
								if(!is_null($approved->add_email)){
									$amails = explode(',', $approved->add_email);
									foreach($amails as $key => $amail){
										$amail = trim($amail);
										if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
											if($amail == '{{$user->email}}'){ $mails[] = $userData->email; }
										}else{ $mails[] = $amail; }
									}
								}
								if(!is_null($approved->cc_email)){
									$cmails = explode(',', $approved->cc_email);
									foreach($cmails as $key => $cmail){
										$cmail = trim($cmail);
										if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
											if($cmail == '{{$user->email}}'){ $ccmails[] = $userData->email; }
										}else{ $ccmails[] = $cmail; }
									}
								}
								$template = DbView::make($approved)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
								$subject = DbView::make($approved)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
								Mail::raw($template, function($m) use($userData, $subject, $template, $mails, $ccmails){ self::postEditApprovedAction($m, $userData, $subject, $template, $mails, $ccmails); });
							}else{
								Mail::send('emails.approved', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($userData, $subject){ self::postEditApprovedAction($m, $userData, $subject); });
							}
						}else{
							if($managerData){
								$admindeclined = \App\EmailTemplate::where('email', 'admindeclined')->where('status', true)->first();
								$subject = trans('messages.ManagerAbsenceDeclined', ['name' => $userData->name, 'by' => 'Admin']);
								if($admindeclined){
									$mails = [];
									$ccmails = [];
									if(!is_null($admindeclined->add_email)){
										$amails = explode(',', $admindeclined->add_email);
										foreach($amails as $key => $amail){
											$amail = trim($amail);
											if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
												if($amail == '{{$user->email}}'){ $mails[] = $user->email; }
												if($amail == '{{$manager->email}}'){
													if($manager){
														$managerData = $manager->manager;
														$mails[] = $managerData->email;
													}
												}
												if($amail == '{{$admin->email}}'){
													$admin = env('ADMIN_MAIL');
													if($admin){ $mails[] = $admin; }
												}
											}else{ $mails[] = $amail; }
										}
									}
									if(!is_null($admindeclined->cc_email)){
										$cmails = explode(',', $admindeclined->cc_email);
										foreach($cmails as $key => $cmail){
											$cmail = trim($cmail);
											if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
												if($cmail == '{{$user->email}}'){ $ccmails[] = $user->email; }
												if($cmail == '{{$manager->email}}'){
													if($manager){
														$managerData = $manager->manager;
														$ccmails[] = $managerData->email;
													}
												}
												if($cmail == '{{$admin->email}}'){
													$admin = env('ADMIN_MAIL');
													if($admin){ $ccmails[] = $admin; }
												}
											}else{ $ccmails[] = $cmail; }
										}
									}
									$mails = array_unique($mails);
									$ccmails = array_unique($ccmails);
									$template = DbView::make($admindeclined)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'reason' => $data['cancel_reason']])->render();
									$subject = DbView::make($admindeclined)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'reason' => $data['cancel_reason'], 'name' => $userData->name])->render();
									Mail::raw($template, function($m) use($managerData, $userData, $subject, $template, $mails, $ccmails){ self::postEditAdminDeclinedAction($m, $managerData, $userData, $subject, $template, $mails, $ccmails); });
								}else{
									Mail::send('emails.admindeclined', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'reason' => $data['cancel_reason']], function($m) use($managerData, $userData, $subject){ self::postEditAdminDeclinedAction($m, $managerData, $userData, $subject); });
								}
							}
							$declined = \App\EmailTemplate::where('email', 'declined')->where('status', true)->first();
							$subject = trans("messages.YourAbsenceDeclined");
							if($declined){
								$mails = [];
								$ccmails = [];
								if(!is_null($declined->add_email)){
									$amails = explode(',', $declined->add_email);
									foreach($amails as $key => $amail){
										$amail = trim($amail);
										if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
											if($amail == '{{$user->email}}'){ $mails[] = $userData->email; }
										}else{ $mails[] = $amail; }
									}
								}
								if(!is_null($declined->cc_email)){
									$cmails = explode(',', $declined->cc_email);
									foreach($cmails as $key => $cmail){
										$cmail = trim($cmail);
										if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
											if($cmail == '{{$user->email}}'){ $ccmails[] = $userData->email; }
										}else{ $ccmails[] = $cmail; }
									}
								}
								$template = DbView::make($declined)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'reason' => $data['cancel_reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
								$subject = DbView::make($declined)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'reason' => $data['cancel_reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
								Mail::raw($template, function($m) use($userData, $subject, $template, $mails, $ccmails){ self::postEditDeclinedAction($m, $userData, $subject, $template, $mails, $ccmails); });
							}else{
								Mail::send('emails.declined', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Admin', 'reason' => $data['cancel_reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($userData, $subject){ self::postEditDeclinedAction($m, $userData, $subject); });
							}
						}
					}
					return redirect('/admin/absences')->withMessage(trans('messages.AbsenceUpdatedSuccessfully'));
				}else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/absences')->withError(trans('messages.AbsenceNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/absences')->withError($e->getMessage());
		}
	}

	public function deleteAbsence(Request $request, $id){
		try{
			$validator = Validator::make($request->all(), ['_method' => "required|in:DELETE,delete", ]);
			if($validator->fails()){
				return redirect()->back()->withError(trans('messages.SomethingwentWrong'));
			}
			$absence = \App\Absences::find($id);
			if($absence){
				$userData = $absence->user;
				$data = $absence->toArray();
				$data['deleted_date'] = date('Y-m-d');
				$absence->delete();
				$manager = \App\UserManager::where('consultant_id', $userData->id)->first();
				$managerData = null;
				if($manager){ $managerData = $manager->manager; }
				$admindelete = \App\EmailTemplate::where('email', 'admindelete')->where('status', true)->first();
				$deleted = \App\EmailTemplate::where('email', 'deleted')->where('status', true)->first();
				$subject = trans('messages.ManagerAbsenceDeleted', ['name' => $userData->name, 'by' => 'Admin']);
				$yesText = trans('messages.Yes');
				$noText = trans('messages.No');
				$formatText = '%Y-%m-%d %H:%M:%S';
				if($managerData){
					if($admindelete){
						$mails = [];
						$ccmails = [];
						if(!is_null($admindelete->add_email)){
							$amails = explode(',', $admindelete->add_email);
							foreach($amails as $key => $amail){
								$amail = trim($amail);
								if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
									if($amail == '{{$user->email}}'){ $mails[] = $userData->email; }
									if($amail == '{{$manager->email}}'){
										if($manager){
											$managerData = $manager->manager;
											$mails[] = $managerData->email;
										}
									}
									if($amail == '{{$admin->email}}'){
										$admin = env('ADMIN_MAIL');
										if($admin){ $mails[] = $admin; }
									}
								}else{ $mails[] = $amail; }
							}
						}
						if(!is_null($admindelete->cc_email)){
							$cmails = explode(',', $admindelete->cc_email);
							foreach($cmails as $key => $cmail){
								$cmail = trim($cmail);
								if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
									if($cmail == '{{$user->email}}'){ $ccmails[] = $userData->email; }
									if($cmail == '{{$manager->email}}'){
										if($manager){
											$managerData = $manager->manager;
											$ccmails[] = $managerData->email;
										}
									}
									if($cmail == '{{$admin->email}}'){
										$admin = env('ADMIN_MAIL');
										if($admin){ $ccmails[] = $admin; }
									}
								}else{ $ccmails[] = $cmail; }
							}
						}
						$mails = array_unique($mails);
						$ccmails = array_unique($ccmails);
						$template = DbView::make($admindelete)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $userData, 'by' => 'Admin',])->render();
						$subject = DbView::make($admindelete)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $userData, 'by' => 'Admin', 'name' => $userData->name])->render();
						Mail::raw($template, function($m) use($managerData, $userData, $subject, $template, $mails, $ccmails){ self::deleteAbsenceAdminAction($m, $managerData, $userData, $subject, $template, $mails, $ccmails); });
					}else{
						Mail::send('emails.admindelete', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $userData, 'by' => 'Admin',], function($m) use($managerData, $userData, $subject){ self::deleteAbsenceAdminAction($m, $managerData, $userData, $subject); });
					}
				}
				$subject = trans("messages.YourAbsenceDeleted");
				if($deleted){
					$mails = [];
					$ccmails = [];
					if(!is_null($deleted->add_email)){
						$amails = explode(',', $deleted->add_email);
						foreach($amails as $key => $amail){
							$amail = trim($amail);
							if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
								if($amail == '{{$user->email}}'){ $mails[] = $userData->email; }
							}else{ $mails[] = $amail; }
						}
					}
					if(!is_null($deleted->cc_email)){
						$cmails = explode(',', $deleted->cc_email);
						foreach($cmails as $key => $cmail){
							$cmail = trim($cmail);
							if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
								if($cmail == '{{$user->email}}'){ $ccmails[] = $userData->email; }
							}else{ $ccmails[] = $cmail; }
						}
					}
					$template = DbView::make($deleted)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $userData, 'by' => 'Admin',])->render();
					$subject = DbView::make($deleted)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $userData, 'by' => 'Admin',])->render();
					Mail::raw($template, function($m) use($userData, $subject, $template, $mails, $ccmails){ self::deleteAbsenceAction($m, $userData, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.deleted', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $userData, 'by' => 'Admin',], function($m) use($userData, $subject){ self::deleteAbsenceAction($m, $userData, $subject); });
				}
				return redirect('/admin/absences')->withMessage(trans('messages.AbsenceDeletedSuccessfully'));
			}
			return redirect('/admin/absences')->withError(trans('messages.AbsenceDataNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/absences')->withError($e->getMessage());
		}
	}

	public static function excelExportAction($m, $emails, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($emails)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	public static function deleteAbsenceAction($m, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($userData->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	public static function deleteAbsenceAdminAction($m, $managerData, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			$mail = $m->to($managerData->email)->subject($subject);
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}
	}

	public static function postEditAdminApprovedAction($m, $managerData, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			$mail = $m->to($managerData->email)->subject($subject);
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}
	}

	public static function postEditApprovedAction($m, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($userData->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	public static function postEditAdminDeclinedAction($m, $managerData, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			$mail = $m->to($managerData->email)->subject($subject);
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}
	}

	public static function postEditDeclinedAction($m, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($userData->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}
}