<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, DateTime, Mail, Config, DbView;
use Carbon\Carbon;

class AbsencesController extends Controller {

	protected $reasons = [];

	protected $rules = [
		'start'		=> "required|date_format:d/m/Y",
		'end'		=> "required|date_format:d/m/Y|after_or_equal:start",
		'days'		=> "required",
		'startHalf'	=> "required|boolean",
		'endHalf'	=> "required|boolean",
		'reason'	=> "required",
	];

	public function __construct(){
		$this->reasons = \App\AbsenceTypes::getReasonLabels()->toArray();
		$this->rules['reason'] = "required|in:" . implode(',', array_keys($this->reasons));
		$this->rules['start'] = "required|date_format:d/m/Y|after_or_equal:" . date('d/m/Y', strtotime('-1 year'));
	}

	public function getIndex(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$startDate = date("Y-m-01");
			$endDate = date("Y-m-t");
			if($request->has('date') && !empty($request->get('date'))){
				$startDate = date("Y-m-01", strtotime($request->get('date')));
				$endDate = date("Y-m-t", strtotime($request->get('date')));
			}
			\App\Absences::whereRaw('user_id = ? AND status = ? AND start < ?', [$user->id, 'pending', date('Y-m-d')])->update(['status' => "approved", 'accepted_date' => date("Y-m-d"), 'self' => true]);
			$absences = \App\Absences::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $endDate, $startDate])->whereIn('status', ['pending', 'approved'])->orderBy('start', 'ASC')->get();
			$userData = getApiUserData($user);
			$month = date("m-Y", strtotime($request->get('date')));
			$result = \App\CRA::whereRaw('user_id = ? AND (start between ? and ? OR end between ? and ?)', [$user->id, $startDate, $endDate, $startDate, $endDate])->get();
			foreach($absences as $key => $absence){
				$absence->days = getDaysCount($absence->start, $absence->end, $absence->startHalf, $absence->endHalf);
				$absence->reasonStr = $absence->reasonStr;
			}
			foreach($result as $key => $value){
				$value->days = getDaysCount($value->start, $value->end, $value->startHalf, $value->endHalf);
				$value->client_name = $value->mission->code;
				$value->color = $value->mission->client->color;
				$value->broadcast = date("Y-m-d", strtotime($value->broadcast_date));
				unset($value->client);
			}
			$refusedAbsences = \App\Absences::whereRaw('user_id = ? AND (status = ? or status = ? or status = ? or status = ?)',[$user->id, "cancelled_by_admin", "deleted_by_admin", "cancelled_by_manager", "deleted_by_manager"])->whereRaw('(start between ? and ? OR end between ? and ?)', [$startDate, $endDate, $startDate, $endDate])->orderBy('created_at', 'desc')->get();
			return responseJson(['absences' => $absences, 'data' => $userData, 'cras' => $result, 'refusedAbsences' => $refusedAbsences ], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function postAbsences(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$validator = Validator::make($request->all(), ['absences' => "required|array"]);
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			$absencesData = $request->get('absences');
			foreach($absencesData as $absence){
				if(!is_array($absence)){ return responseJson(['error' => trans('messages.PleaseEntervaliddata'), ], 400); }
				$validator = Validator::make($absence, $this->rules);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
				$start = Carbon::createFromFormat('d/m/Y', $absence['start'])->format('Y-m-d');
				$end = Carbon::createFromFormat('d/m/Y', $absence['end'])->format('Y-m-d');
				if(($start == $end) && (($absence['startHalf'] == 1 || $absence['startHalf'] == true) && ($absence['endHalf'] == 1 || $absence['endHalf'] == true))){ return responseJson(['error' => trans('messages.SomethingwentWrong')], 400); }
			}
			$absences = \App\Absences::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->get();
			$cras = \App\CRA::where('user_id', $user->id)->get();
			$penalties = \App\Penalty::where('user_id', $user->id)->get();
			$data = [];
			$manager = \App\UserManager::where('consultant_id', $user->id)->first();
			$managerData = null;
			if($manager){ $managerData = $manager->manager; }
			$reasons = \App\AbsenceTypes::all();
			foreach($absencesData as $aKey => $absence){
				if($aKey != 0){ $absences = \App\Absences::where('user_id', $user->id)->get(); }
				list($start_date, $end_date) = [$absence['start'], $absence['end']];
				list($startDate, $endDate) = [Carbon::createFromFormat('d/m/Y', $absence['start']), Carbon::createFromFormat('d/m/Y', $absence['end'])];
				list($start, $end) = [Carbon::createFromFormat('d/m/Y', $absence['start'])->format('Y-m-d'), Carbon::createFromFormat('d/m/Y', $absence['end'])->format('Y-m-d')];
				$dayCount = ($startDate->diffInDays($endDate) + 1);
				list($startDay, $endDay) = [date('N', strtotime($start)), date('N', strtotime($end))];
				if(($startDay == 7 || $startDay == 6) || ($endDay == 7 || $endDay == 6)){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$dates = Config::get('leave_dates');
				if(in_array($absence['start'], $dates) || in_array($absence['end'], $dates)){
					return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400);
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
				if($weekdayCounter == $dayCount){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$absence['start'] = Carbon::createFromFormat('d/m/Y', $absence['start'])->format('Y-m-d');
				$absence['end'] = Carbon::createFromFormat('d/m/Y', $absence['end'])->format('Y-m-d');
				if(($absence['startHalf'] == 1 || $absence['startHalf'] == true) || ($absence['endHalf'] == 1 || $absence['endHalf'] == true)){
					$conflictAbsences = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
					foreach($conflictAbsences as $key => $conflictAbsence){
						if($conflictAbsence->start == $conflictAbsence->end){ $conflicts = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == $absence['endHalf']) && ($item->startHalf == $absence['startHalf'])); }); }else{
							$conflicts = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == true)); });
							if($absence['startHalf'] == 1 || $absence['startHalf'] == true){ $conflicts = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->startHalf == true)); }); }
						}
						if($conflicts->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date])], 400); }
					}
					$conflictcras = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
					foreach($conflictcras as $key => $conflictcra){
						if($conflictcra->start == $conflictcra->end){ 
							$conflicts = $cras->filter(function($item) use($absence){ 
								return ((($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->endHalf == $absence['endHalf']) && ($item->startHalf == $absence['startHalf'])) || (($item->start <= $absence['end']) && ($item->end >= $absence['start']) && ($item->startHalf == 0) && ($item->endHalf == 0))); 
							}); 
						}
						else
						{
							$conflicts = $cras->filter(function($item) use($absence){ 
								return ((($item->start < $absence['end']) && ($item->end > $absence['start'])) || ((($item->start == $absence['start']) && ($item->start == $absence['end']) && ($item->startHalf == 0) && ($item->endHalf == 0)) || (($item->end == $absence['start']) && ($item->end == $absence['end']) && ($item->startHalf == 0) && ($item->endHalf == 0))) || ((($item->start == $absence['start']) && ($item->start == $absence['end']) && ($item->startHalf == $absence['startHalf'])) || (($item->end == $absence['end']) && ($item->end == $absence['start']) && ($item->endHalf == $absence['endHalf']))) ); 
							});
						}
						if($conflicts->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date])], 400); }
					}
					$conflictpenalties = $penalties->filter(function($item) use($absence){ return (($item->beginning <= $absence['end']) && ($item->ending >= $absence['start'])); });
					if($conflictpenalties->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date])], 400); }
				}else{
					$conflictAbsences = $absences->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
					$conflictcras = $cras->filter(function($item) use($absence){ return (($item->start <= $absence['end']) && ($item->end >= $absence['start'])); });
					$conflictpenalties = $penalties->filter(function($item) use($absence){ return (($item->beginning <= $absence['end']) && ($item->ending >= $absence['start'])); });
					if($conflictAbsences->count() > 0 || $conflictcras->count() > 0 || $conflictpenalties->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date])], 400); }
				}
				$absence['status'] = "pending";
				$reason = $reasons->filter(function($item) use($absence){ return ($item->slug == $absence['reason']); })->first();
				if($reason && $reason->auto_approve){
					$absence['status'] = "approved";
					$dt = new DateTime();
					$absence['accepted_date'] = $dt->format('Y-m-d H:i:s');
				}
				$days = ($dayCount - $weekdayCounter);
				if($dayCount == 1){
					if($absence['endHalf'] || $absence['startHalf']){ $days = $days - 0.5; }
				}else{
					if($absence['endHalf']){ $days = $days - 0.5; }
					if($absence['startHalf']){ $days = $days - 0.5; }
				}
				$insert = \App\Absences::create(['client_informed' => $absence['client_informed'], 'days' => $days,
					'end' => $absence['end'], 'endHalf' => $absence['endHalf'],
					'reason' => $absence['reason'], 'start' => $absence['start'],
					'startHalf' => $absence['startHalf'], 'status' => $absence['status'], 
					'user_id' => $user->id,
				]);
				if(!$insert){ return responseJson(['error' => trans('messages.SomethingWentWrong')], 400); }
				$admin = env('ADMIN_MAIL');
				if($managerData || $admin){
					$subject = trans('messages.NewAbsenceRequest');
					$yesText = trans('messages.Yes');
					$noText = trans('messages.No');
					$formatText = '%Y-%m-%d %H:%M:%S';
					$mail = \App\EmailTemplate::where('email', 'newabsence')->where('status', true)->first();
					if($mail){
						$mails = [];
						$ccmails = [];
						if(!is_null($mail->add_email)){
							$amails = explode(',', $mail->add_email);
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
						if(!is_null($mail->cc_email)){
							$cmails = explode(',', $mail->cc_email);
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
						$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $insert, 'user' => $user])->render();
						$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $insert, 'user' => $user])->render();
						Mail::raw($template, function($m) use($managerData, $admin, $subject, $template, $mails, $ccmails){ self::postAbsencesAction($m, $managerData, $admin, $subject, $template, $mails, $ccmails); });
					}else{
						Mail::send('emails.newabsence', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $insert, 'user' => $user], function($m) use($managerData, $admin, $subject){ self::postAbsencesAction($m, $managerData, $admin, $subject); });
					}
				}
			}
			$userData = getApiUserData($user);
			return responseJson(['data' => $userData, 'message' => trans('messages.YourRequestSavedSuccessFully')], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getAbsence($id){
		try{
			$user = \Auth::guard("api")->user();
			$absence = \App\Absences::where('id', $id)->first();
			if($absence) return responseJson(['absence' => $absence], 200);
			else return responseJson(['error' => trans('messages.NoDataAvailable')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function postEditAbsence(Request $request, $id){
		try{
			$user = \Auth::guard("api")->user();
			$result = \App\Absences::where('id', $id)->first();
			if($result){
				if($result['status'] != 'pending') return responseJson(['error' => trans('messages.YourRequestAlreadyApproved')], 400);
				else{
					$this->rules['start'] = "required|date_format:d/m/Y";
					$data = $request->only('start', 'end', 'days', 'startHalf', 'endHalf', 'reason');
					$validator = Validator::make($data, $this->rules);
					if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
					$absences = \App\Absences::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->get();
					list($start_date, $end_date) = [$data['start'], $data['end']];
					$data['start'] = Carbon::createFromFormat('d/m/Y', $data['start'])->format('Y-m-d');
					$data['end'] = Carbon::createFromFormat('d/m/Y', $data['end'])->format('Y-m-d');
					$conflictAbsences = $absences->filter(function($item) use($data, $id){
						return (($id != $item->id) && ($item->start <= $data['end']) && ($item->end >= $data['start']));
					});
					if($conflictAbsences->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
					$reasons = \App\AbsenceTypes::all();
					$reason = $reasons->filter(function($item) use($data){ return ($item->slug == $data['reason']); })->first();
					if($reason && $reason->auto_approve){ $data['status'] = "approved"; }
					$update = $result->update($data);
					if($update) return responseJson(['message' => trans('messages.YourRequestUpdatedSuccessFully'), 'absence' => $result], 200);
					else return responseJson(['error' => trans('messages.SomethingwentWrong')], 400);
				}
			}else return responseJson(['error' => trans('messages.AbsenceDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function DeleteAbsence($id){
		try{
			$user = \Auth::guard("api")->user();
			$absence = \App\Absences::find($id);
			if($absence){
				$data = $absence->toArray();
				$data['deleted_date'] = date('Y-m-d');
				if(in_array($absence->status, ['pending', 'cancelled_by_user', 'deleted_by_user'])){ $absence->delete(); }
				else{ $absence->update(['status' => 'deleted_by_user', 'deleted_date' => date('Y-m-d'), 'archive' => true]); }
				$manager = \App\UserManager::where('consultant_id', $user->id)->first();
				$managerData = null;
				if($manager){ $managerData = $manager->manager; }
				$admin = env('ADMIN_MAIL');
				if($managerData || $admin){
					$mail = \App\EmailTemplate::where('email', 'admindelete')->where('status', true)->first();
					$subject = trans('messages.ManagerAbsenceDeleted', ['name' => $user->name, 'by' => 'Consultant']);
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
						if(!is_null($mail->cc_email)){
							$cmails = explode(',', $mail->cc_email);
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
						$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $user, 'by' => 'Consultant',])->render();
						$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $user, 'by' => 'Consultant', 'name' => $user->name,])->render();
						Mail::raw($template, function($m) use($managerData, $admin, $user, $subject, $template, $mails, $ccmails){ self::DeleteAbsenceAction($m, $managerData, $admin, $user, $subject, $template, $mails, $ccmails); });
					}else{
						Mail::send('emails.admindelete', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $data, 'userData' => $user, 'by' => 'Consultant',], function($m) use($managerData, $admin, $user, $subject){ self::DeleteAbsenceAction($m, $managerData, $admin, $user, $subject); });
					}
				}
				return responseJson(['message' => trans("messages.AbsenceDeletedSuccessfully")], 200);
			}
			return responseJson(['error' => trans('messages.AbsenceDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getClientAbsences($id, $month){
		try{
			$user = \Auth::guard("api")->user();
			$timestamp = Carbon::createFromFormat('d-m-Y', '02-' . $month);
			$startDate = $timestamp->format("Y-m-01");
			$endDate = $timestamp->format("Y-m-t");
			$result = \App\Absences::whereRaw('user_id = ? AND (start between ? and ? OR end between ? and ?)', [$id, $startDate, $endDate, $startDate, $endDate])->whereIn('status', ['pending', 'approved'])->get();
			$data2 = [];
			$count = 0;
			$color = '';
			foreach($result as $data){
				$start_date = Carbon::parse($data['start']);
				$end_date = Carbon::parse($data['end']);
				$start = $start_date->format('m-Y');
				$end = $end_date->format('m-Y');
				$startDate = $start_date->format('Y-m-d');
				$color = $data->user->color;
				if($start == $month){
					if($start == $end){
						$endDate = $end_date->format('Y-m-d');
						$weekendCounts = 0;
						$start = new DateTime($startDate);
						$end = new DateTime($endDate);
						$days = $start->diff($end, true)->days;
						$sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
						$saturdays = intval($days / 6) + ($start->format('N') + $days % 6 >= 7);
						$weekendCounts = ($sundays + $saturdays);
						$total = $end_date->diffInDays($start_date) + 1;
						$total = $total - $weekendCounts;
						$count = $count + $total;
						if($data['startHalf'] == 1 || $data['endHalf'] == 1){
							$count = $count - 0.5;
							$total = $total - 0.5;
						}
						$data2[] = ['start' => $startDate, 'end' => $endDate, 'days' => $total,
							'user_id' => $id, 'startHalf' => $data['startHalf'], 'endHalf' => $data['endHalf'],
							'color' => $data->user->color,
						];
					}else{
						$endDate = Carbon::parse($data['start'])->endOfMonth();
						$weekendCounts = 0;
						$start = new DateTime($startDate);
						$end = new DateTime($endDate);
						$days = $start->diff($end, true)->days;
						$sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
						$saturdays = intval($days / 6) + ($start->format('N') + $days % 6 >= 7);
						$weekendCounts = ($sundays + $saturdays);
						$total = $endDate->diffInDays($start_date) + 1;
						$total = $total - $weekendCounts;
						$count = $count + $total;
						if($data['startHalf'] == 1){
							$count = $count - 0.5;
							$total = $total - 0.5;
						}
						$data2[] = ['start' => $startDate, 'end' => $endDate->format('Y-m-d'),
							'days' => $total, 'user_id' => $id, 'startHalf' => $data['startHalf'],
							'endHalf' => 0, 'color' => $data->user->color,
						];
					}
				}
			}
			return responseJson(['data' => $data2, 'totalDays' => $count, 'color' => $color], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}
	
	public function getClientAbsencesByType($id, $month){
		try{
			$user = \Auth::guard("api")->user();
			$absence = \App\Absences::getClientCras($id, $month);
			$data = \App\CRA::getCRA($user->id, $month);
			return responseJson([
				'absences' => $absence['absence'],
				'data' => $absence['absenceTypes'],
				'craData' => $data['craData'],
				'broadcast' => $data['broadcast'],
				'missionData' => $data['missionData'],
				'cra_status' => $data['cra_status'],
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getData(){
		try{
			$user = \Auth::guard("api")->user();
			$pending = 0;
			$valid = 0;
			$canceled = 0;
			$deleted = 0;
			$timestamp = Carbon::now();
			$startDate = $timestamp->format("Y-m-01");
			$endDate = $timestamp->format("Y-m-t");
			$absences = \App\Absences::whereRaw('user_id = ? AND (start between ? and ? OR end between ? and ?)', [$user['id'], $startDate, $endDate, $startDate, $endDate])->orderBy('created_at', 'desc')->get();
			foreach($absences as $absence){
				if($absence['status'] == "pending") $pending++;
				else if($absence['status'] == "approved") $valid++;
				else if($absence['status'] == "cancelled_by_admin" || $absence['status'] == "cancelled_by_manager") $canceled++;
				else $deleted++;
			}
			$validatedAbsences = $absences->filter(function($item){ return $item->status == 'approved'; })->take(5)->values();
			$refusedAbsences = $absences->filter(function($item){ return ($item->status == 'cancelled_by_admin' || $item->status == 'deleted_by_admin' || $item->status == 'cancelled_by_manager' || $item->status == 'deleted_by_manager'); })->take(5)->values();
			$valid = 0;
			if($user->role == 'manager'){
				$consultants = \App\UserManager::where('manager_id', $user->id)->get();
				$absences = \App\Absences::whereIn('user_id', $consultants->pluck('consultant_id'))->whereRaw('status = ? AND start >= ?', ['pending', date("Y-m-d")])->orderBy('start', 'ASC')->get();
				$valid = $absences->count();
			}
			return responseJson([ 'absenceData' => [
					'pending' => $pending,
					'validated' => $valid,
					'canceled' => $canceled,
					'deleted' => $deleted,
				], 'validatedAbsences' => $validatedAbsences,
				'refusedAbsences' => $refusedAbsences,
				'data' => getApiUserData($user),
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public static function postAbsencesAction($m, $managerData, $admin, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			if($managerData){
				$mail = $m->to($managerData->email)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
			if($admin){
				$mail = $m->to($admin)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
		}
	}

	public static function DeleteAbsenceAction($m, $managerData, $admin, $user, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			if($managerData){
				$mail = $m->to($managerData->email)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
			if($admin){
				$mail = $m->to($admin)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
		}
	}
}