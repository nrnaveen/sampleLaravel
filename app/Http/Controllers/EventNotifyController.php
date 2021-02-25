<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Push;
use App\Events;
use Validator, Auth, File, Image, Mail, DbView;

class EventNotifyController extends Controller {

	public function getIndex(Request $request){
		try{
			$date = date('Y-m-d');
			$events = Events::whereRaw('date = ? AND status = ?', [$date, true])->get();
			// $datetime = ($request->has('send_option') && (!$request->get('send_option') || $request->get('send_option') == '0')) ? date("Y-m-d H:i:s") : $request->get('date');
			$datetime = date("Y-m-d H:i:s");
			if($events->count() > 0){
				$start = date("Y-m-01");
				$end = date("Y-m-t");
				$cras = \App\CRA::whereRaw("start >= ? AND end <= ? AND validation = ?", [$start, $end, true])->groupBy('user_id')->get();
				$query = \App\User::whereNotIn('id', $cras->pluck('user_id'))->get();
				/*if($data['receipent'] == 'android'){ $query = $query->where('deviceType', 'android'); }
				elseif($data['receipent'] == 'ios'){ $query = $query->where('deviceType', 'ios'); }
				elseif($data['receipent'] == 'target'){
					$userIds = $request->get('users');
					if(count($userIds) <= 0){ return redirect()->back()->withError(trans('messages.PleaseSelectUser'))->withInput(); };
					$query = $query->whereIn('id', $userIds);
				}*/
				$users = $query->filter(function($item){
					return ($item->deviceId != '' && $item->registrationId != '');
				});
				$ids = [];
				foreach($users as $key => $user){ $ids[] = $user->deviceId; };
				foreach($events as $key => $event){
					$pushData = [];
					$content = ["en" => $event->label . ' ' . $event->date, 'fr' => $event->label . ' ' . $event->date,];
					$headings = ["en" => $event->label . ' ' . $event->date, 'fr' => $event->label . ' ' . $event->date,];
					$response = json_decode(Push::sendById($content, $pushData, $ids, $headings, $datetime));
					$emails = $query->pluck('email');
					if($emails->count() > 0){
						$emails = $emails->toArray();
						$eventnotify = \App\EmailTemplate::where('email', 'eventnotify')->where('status', true)->first();
						$subject = trans("messages.CRAEventNotification");
						$yesText = trans('messages.Yes');
						$noText = trans('messages.No');
						$formatText = '%Y-%m-%d %H:%M:%S';
						if($eventnotify){
							$mails = [];
							$ccmails = [];
							if(!is_null($eventnotify->add_email)){
								$amails = explode(',', $eventnotify->add_email);
								foreach($amails as $key => $amail){
									$amail = trim($amail);
									if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
										if($amail == '{{$user->email}}'){
											foreach($emails as $key => $email){ $mails[] = $email; }
										}
										if($amail == '{{$admin->email}}'){
											$admin = env('ADMIN_MAIL');
											if($admin){ $mails[] = $admin; }
										}
									}else{ $mails[] = $amail; }
								}
							}
							if(!is_null($eventnotify->cc_email)){
								$cmails = explode(',', $eventnotify->cc_email);
								foreach($cmails as $key => $cmail){
									$cmail = trim($cmail);
									if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
										if($cmail == '{{$user->email}}'){
											foreach($emails as $key => $email){ $ccmails[] = $email; }
										}
										if($amail == '{{$admin->email}}'){
											$admin = env('ADMIN_MAIL');
											if($admin){ $mails[] = $admin; }
										}
									}else{ $ccmails[] = $cmail; }
								}
							}
							$mails = array_unique($mails);
							$ccmails = array_unique($ccmails);
							$template = DbView::make($eventnotify)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'event' => $event,])->render();
							$subject = DbView::make($eventnotify)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'event' => $event,])->render();
							Mail::raw($template, function($m) use($emails, $subject, $template, $mails, $ccmails){ self::eventNotifyAction($m, $emails, $subject, $template, $mails, $ccmails); });
						}else{
							Mail::send('emails.eventnotify', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'event' => $event,], function($m) use($emails, $subject){ self::eventNotifyAction($m, $emails, $subject); });
						}
					}
					/*if(isset($response->errors) && count($response->errors) > 0){
						return redirect('/')->withError($response->errors[0]);
					}*/
					return redirect('/');
				}
			}
			return redirect('/');
		}catch(\Exception $e){
			return redirect('/')->withError($e->getMessage());
		}
	}

	public static function eventNotifyAction($m, $emails, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email[0])->subject($subject);
			if(count($add_email) > 1){
				array_shift($add_email);
				$mail->bcc($add_email);
			}
		}else{
			$mail = $m->to($emails[0])->subject($subject);
			if(count($emails) > 1){
				array_shift($emails);
				$mail->bcc($emails);
			}
		}
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}
}