<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, DateTime, Mail, Config, DbView;
use Carbon\Carbon;

class VisionController extends Controller {

	public function getIndex(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$consultants = \App\UserManager::where('manager_id', $user->id)->get();
			$consultantIds = $consultants->pluck('consultant_id');
			$startDate = date("Y-m-d");
			$due_absences = \App\Absences::whereIn('user_id', $consultantIds)->whereRaw('status = ? AND start < ?', ['pending', $startDate])->update(['status' => "approved", 'accepted_date' => date("Y-m-d"), 'self' => true]);
			$absences = \App\Absences::whereIn('user_id', $consultantIds)->whereRaw('status = ? AND start >= ?', ['pending', $startDate])->orderBy('start', 'ASC')->get();
			$users = \App\User::whereIn('id', $absences->pluck('user_id'))->get();
			foreach($users as $key => $cuser){
				$cuserAbsences = $absences->filter(function($item) use($cuser){ return ($item->user_id == $cuser->id); })->values();
				if($cuserAbsences->count() > 0){
					$cuser->name = $cuser->name;
					$cuserAbsencesgrp = $cuserAbsences->groupBy('reason');
					$cuser->absenceKeys = $cuserAbsencesgrp->keys();
					foreach($cuserAbsencesgrp as $key => $value){
						foreach($value as $key => $val){
							$val->startStr = date('d/m/Y', strtotime($val->start));
							$val->endStr = date('d/m/Y', strtotime($val->end));
							$val->clientInformed = $val->client_informed ? 'OUI' : 'NON';
						}
					}
					$cuser->absences = $cuserAbsencesgrp;
				}else{ $users->forget($key); }
			}
			$userData = getApiUserData($user);
			$count = 10;
			$siteinfo = \App\SiteInfo::first();
			if($siteinfo){ $count = $siteinfo->request_count; }
			$requests = \App\Absences::whereIn('user_id', $consultantIds)->whereIn('status', ['approved', 'cancelled_by_manager', 'deleted_by_manager', 'cancelled_by_admin', 'deleted_by_admin'])->orderBy('updated_at', 'DESC')->take($count)->get();
			foreach($requests as $key => $request){
				$request->user = $request->user;
				$request->user->name = $request->user->name;
			}
			return responseJson([
				'users' => $users,
				'data' => $userData,
				'requests' => $requests,
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function postIndex(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$validator = Validator::make($request->all(), ['absences' => "required|array"]);
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			$consultants = \App\UserManager::where('manager_id', $user->id)->get();
			$consultantIds = $consultants->pluck('consultant_id');
			$startDate = date("Y-m-d");
			$absences = \App\Absences::whereIn('user_id', $consultantIds)->whereRaw('status = ? AND start >= ?', ['pending', $startDate])->get();
			$absencesData = $request->get('absences');
			$absenceIds = [];
			$approved = \App\EmailTemplate::where('email', 'approved')->where('status', true)->first();
			$adminapproved = \App\EmailTemplate::where('email', 'adminapproved')->where('status', true)->first();
			$declined_user = $approved_user = $user;
			foreach($absencesData as $value){
				if(!is_array($value)){ return responseJson(['error' => trans('messages.PleaseEntervaliddata'), ], 400); }
				$validator = Validator::make($value, ['id' => "required"]);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
				$absence = $absences->where('id', $value['id'])->first();
				if(!$absence){ return responseJson(['error' => trans('messages.AbsenceDataNotFound'), ], 400); };
				$absenceIds[] = $absence->id;
				$absence->update(['status' => "approved", 'accepted_date' => date("Y-m-d"), 'self' => true]);
				$userData = $absence->user;
				$subject = trans("messages.YourAbsenceApproved");
				$yesText = trans('messages.Yes');
				$noText = trans('messages.No');
				$formatText = '%Y-%m-%d %H:%M:%S';
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
					$template = DbView::make($approved)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
					$subject = DbView::make($approved)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
					Mail::raw($template, function($m) use($userData, $subject, $template, $mails, $ccmails){ self::postIndexApprovedAction($m, $userData, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.approved', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($userData, $subject){ self::postIndexApprovedAction($m, $userData, $subject); });
				}
				$admin = env('ADMIN_MAIL');
				if($admin){
					$subject = trans('messages.ManagerAbsenceApproved', ['name' => $userData->name, 'by' => 'Manager']);
					if($adminapproved){
						$mails = [];
						$ccmails = [];
						if(!is_null($adminapproved->add_email)){
							$amails = explode(',', $adminapproved->add_email);
							foreach($amails as $key => $amail){
								$amail = trim($amail);
								if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
									if($amail == '{{$user->email}}'){ $mails[] = $userData->email; }
									/*if($amail == '{{$manager->email}}'){
										if($manager){
											$managerData = $manager->manager;
											$mails[] = $managerData->email;
										}
									}*/
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
									if($cmail == '{{$user->email}}'){ $ccmails[] = $userData->email; }
									/*if($cmail == '{{$manager->email}}'){
										if($manager){
											$managerData = $manager->manager;
											$ccmails[] = $managerData->email;
										}
									}*/
									if($cmail == '{{$admin->email}}'){
										$admin = env('ADMIN_MAIL');
										if($admin){ $ccmails[] = $admin; }
									}
								}else{ $ccmails[] = $cmail; }
							}
						}
						$mails = array_unique($mails);
						$ccmails = array_unique($ccmails);
						$template = DbView::make($adminapproved)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
						$subject = DbView::make($adminapproved)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
						Mail::raw($template, function($m) use($admin, $userData, $subject, $template, $mails, $ccmails){ self::postIndexAdminApprovedAction($m, $admin, $userData, $subject, $template, $mails, $ccmails); });
					}else{
						Mail::send('emails.adminapproved', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($admin, $userData, $subject){ self::postIndexAdminApprovedAction($m, $admin, $userData, $subject); });
					}
				}
			}
			$userData = getApiUserData($user);
			$count = 10;
			$siteinfo = \App\SiteInfo::first();
			if($siteinfo){ $count = $siteinfo->request_count; }
			$requests = \App\Absences::whereIn('user_id', $consultantIds)->whereIn('status', ['approved', 'cancelled_by_manager', 'deleted_by_manager', 'cancelled_by_admin', 'deleted_by_admin'])->orderBy('updated_at', 'DESC')->take($count)->get();
			foreach($requests as $key => $request){
				$request->user = $request->user;
				$request->user->name = $request->user->name;
			}
			return responseJson([
				'absenceIds' => $absenceIds,
				'data' => $userData,
				'message' => trans('messages.LeaveApproved'),
				'requests' => $requests,
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function putIndex(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$validator = Validator::make($request->all(), ['absences' => "required|array"]);
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			$consultants = \App\UserManager::where('manager_id', $user->id)->get();
			$consultantIds = $consultants->pluck('consultant_id');
			$startDate = date("Y-m-d");
			$absences = \App\Absences::whereIn('user_id', $consultantIds)->whereRaw('status = ? AND start >= ?', ['pending', $startDate])->get();
			$absencesData = $request->get('absences');
			$absenceIds = [];
			$declined = \App\EmailTemplate::where('email', 'declined')->where('status', true)->first();
			$admindeclined = \App\EmailTemplate::where('email', 'admindeclined')->where('status', true)->first();
			$declined_user = $approved_user = $user;
			foreach($absencesData as $value){
				if(!is_array($value)){ return responseJson(['error' => trans('messages.PleaseEntervaliddata'), ], 400); }
				$validator = Validator::make($value, ['id' => "required", 'reason' => "required|max:190"]);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
				$absence = $absences->where('id', $value['id'])->first();
				if(!$absence){ return responseJson(['error' => trans('messages.AbsenceDataNotFound'), ], 400); };
				$absenceIds[] = $absence->id;
				$absence->update(['status' => 'cancelled_by_manager', 'cancelled_date' => date("Y-m-d"), 'cancel_reason' => $value['reason'], ]);
				$userData = $absence->user;
				$subject = trans("messages.YourAbsenceDeclined");
				$yesText = trans('messages.Yes');
				$noText = trans('messages.No');
				$formatText = '%Y-%m-%d %H:%M:%S';
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
					$template = DbView::make($declined)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'reason' => $value['reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
					$subject = DbView::make($declined)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'reason' => $value['reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
					Mail::raw($template, function($m) use($userData, $subject, $template, $mails, $ccmails){ self::putIndexDeclinedAction($m, $userData, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.declined', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'reason' => $value['reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($userData, $subject){ self::putIndexDeclinedAction($m, $userData, $subject); });
				}
				$admin = env('ADMIN_MAIL');
				$subject = trans('messages.ManagerAbsenceDeclined', ['name' => $userData->name, 'by' => 'Manager']);
				if($admin){
					if($admindeclined){
						$mails = [];
						$ccmails = [];
						if(!is_null($admindeclined->add_email)){
							$amails = explode(',', $admindeclined->add_email);
							foreach($amails as $key => $amail){
								$amail = trim($amail);
								if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
									if($amail == '{{$user->email}}'){ $mails[] = $userData->email; }
									/*if($amail == '{{$manager->email}}'){
										if($manager){
											$managerData = $manager->manager;
											$mails[] = $managerData->email;
										}
									}*/
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
									if($cmail == '{{$user->email}}'){ $ccmails[] = $userData->email; }
									/*if($cmail == '{{$manager->email}}'){
										if($manager){
											$managerData = $manager->manager;
											$ccmails[] = $managerData->email;
										}
									}*/
									if($cmail == '{{$admin->email}}'){
										$admin = env('ADMIN_MAIL');
										if($admin){ $ccmails[] = $admin; }
									}
								}else{ $ccmails[] = $cmail; }
							}
						}
						$mails = array_unique($mails);
						$ccmails = array_unique($ccmails);
						$template = DbView::make($admindeclined)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'reason' => $value['reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
						$subject = DbView::make($admindeclined)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'reason' => $value['reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,])->render();
						Mail::raw($template, function($m) use($admin, $userData, $subject, $template, $mails, $ccmails){ self::putIndexAdminDeclinedAction($m, $admin, $userData, $subject, $template, $mails, $ccmails); });
					}else{
						Mail::send('emails.admindeclined', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'absence' => $absence, 'userData' => $userData, 'by' => 'Manager', 'reason' => $value['reason'], 'declinedUser' => $declined_user, 'approvedUser' => $approved_user,], function($m) use($admin, $userData, $subject){ self::putIndexAdminDeclinedAction($m, $admin, $userData, $subject); });
					}
				}
			}
			$userData = getApiUserData($user);
			$count = 10;
			$siteinfo = \App\SiteInfo::first();
			if($siteinfo){ $count = $siteinfo->request_count; }
			$requests = \App\Absences::whereIn('user_id', $consultantIds)->whereIn('status', ['approved', 'cancelled_by_manager', 'deleted_by_manager', 'cancelled_by_admin', 'deleted_by_admin'])->orderBy('updated_at', 'DESC')->take($count)->get();
			foreach($requests as $key => $request){
				$request->user = $request->user;
				$request->user->name = $request->user->name;
			}
			return responseJson([
				'absenceIds' => $absenceIds,
				'data' => $userData,
				'message' => trans('messages.LeaveRejected'),
				'requests' => $requests,
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public static function putIndexDeclinedAction($m, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($userData->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	public static function putIndexAdminDeclinedAction($m, $admin, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			$mail = $m->to($admin)->subject($subject);
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}
	}

	public static function postIndexApprovedAction($m, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($userData->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	public static function postIndexAdminApprovedAction($m, $admin, $userData, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			$mail = $m->to($admin)->subject($subject);
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}
	}
}