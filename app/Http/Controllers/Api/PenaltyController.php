<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Validator, Mail, DbView;
use Carbon\Carbon;

class PenaltyController extends Controller {

	protected $rules = [
		'mission_id'		=> "required|integer",
		'beginning'			=> "required|date_format:d/m/Y H:i",
		'ending'			=> "required|date_format:d/m/Y H:i",
		'total_duration'	=> "required",
		'type'				=> "required|in:active,Active,passive,Passive",
		// 'at_home'			=> "required|boolean",
		// 'comments'			=> "required|max:190",
		'client_informed'	=> "required|boolean",
	];

	public function getIndex(){
		try{
			$user = \Auth::guard("api")->user();
			$userData = getApiUserData($user);
			$penalties = \App\Penalty::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
			foreach($penalties as $key => $penalty){
				$penalty->begin = date('d/m/Y H', strtotime($penalty->beginning)) . 'h' . date('i', strtotime($penalty->beginning));
				$penalty->end = date('d/m/Y H', strtotime($penalty->ending)) . 'h' . date('i', strtotime($penalty->ending));
				$penalty->mission = $penalty->mission;
				if($penalty->mission){ $penalty->mission->name = $penalty->mission->label; }
			}
			$usermissions = \App\UserMission::where('user_id', $user->id)->get()->pluck(['mission_id']);
			$missions = \App\Mission::where('status', true)->whereIn('id', $usermissions)->orderBy('order', 'ASC')->get();
			foreach($missions as $key => $value){ $value->name = $value->code; }
			return responseJson(['missions' => $missions, 'user' => $userData, 'penalties' => $penalties], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function createPenalty(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$data = $request->only(['at_home', 'beginning', 'mission_id', 'client_informed', 'comments', 'ending', 'total_duration', 'type']);
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			if(empty($data['at_home']) || $data['at_home'] == NULL || $data['at_home'] == ''){ unset($data['at_home']); }
			$beginning = Carbon::createFromFormat('d/m/Y H:i', $data['beginning']);
			$ending = Carbon::createFromFormat('d/m/Y H:i', $data['ending']);
			$data['beginning'] = Carbon::createFromFormat('d/m/Y H:i', $data['beginning'])->format('Y-m-d H:i:s');
			$data['ending'] = Carbon::createFromFormat('d/m/Y H:i', $data['ending'])->format('Y-m-d H:i:s');
			$mission = \App\Mission::find($data['mission_id']);
			if(!$mission){ return responseJson(['error' => trans('messages.MissionNotFound')], 400); }
			$data['user_id'] = $user->id;
			$all = round((strtotime($data['ending']) - strtotime($data['beginning'])) / 60);
			$d = floor ($all / 1440);
			$h = floor (($all - $d * 1440) / 60);
			$m = $all - ($d * 1440) - ($h * 60);
			if($d > 0){
				$h = $h + (24 * $d);
			}
			$total = ($h > 10 ? $h : '0' . $h) . ':' . ($m > 0 ? $m : '00');
			$data['total_duration'] = $total;
			$insert = \App\Penalty::create($data);
			if($insert){
				$insert->mission = $insert->mission;
				$insert->mission->name = $insert->mission->name;
				$insert->begin = date('d/m/Y H', strtotime($insert->beginning)) . 'h' . date('i', strtotime($insert->beginning));
				$insert->end = date('d/m/Y H', strtotime($insert->ending)) . 'h' . date('i', strtotime($insert->ending));
				$manager = \App\UserManager::where('consultant_id', $user->id)->first();
				$mail = \App\EmailTemplate::where('email', 'postpenalty')->where('status', true)->first();
				$subject = trans('messages.NewAstreinteCreated');
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
					$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'penalty' => $insert, 'user' => $user,])->render();
					$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'penalty' => $insert, 'user' => $user,])->render();
					Mail::raw($template, function($m) use($manager, $subject, $template, $mails, $ccmails){ self::createPenaltyAction($m, $manager, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.penalty', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'penalty' => $insert, 'user' => $user,], function($m) use($manager, $subject){ self::createPenaltyAction($m, $manager, $subject); });
				}
				if($insert->mission){ $insert->mission->name = $insert->mission->label; }
				return responseJson(['data' => $insert, 'message' => trans('messages.YourRequestSavedSuccessFully')], 200);
			}
			return responseJson(['error' => trans('messages.SomethingwentWrong')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getPenalty($id){
		try{
			$user = \Auth::guard("api")->user();
			$result = \App\Penalty::where('id', $id)->first();
			if($result){
				$result['beginning'] = Carbon::createFromFormat('Y-m-d H:i:s', $result['beginning'])->format('d/m/Y H:i');
				$result['ending'] = Carbon::createFromFormat('Y-m-d H:i:s', $result['ending'])->format('d/m/Y H:i');
				$result->mission = $result->mission;
				if($result->mission){ $result->mission->name = $result->mission->label; }
				return responseJson(['data' => $result], 200);
			}
			return responseJson(['error' => trans('messages.PenaltyDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function updatePenalty(Request $request, $id){
		try{
			$user = \Auth::guard("api")->user();
			$result = \App\Penalty::where('id',$id)->first();
			if($result){
				$data = $request->all();
				$validator = Validator::make($data,$this->rules);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
				$data['beginning']=Carbon::createFromFormat('d/m/Y H:i', $data['beginning'])->format('Y-m-d H:i:s');
				$data['ending']=Carbon::createFromFormat('d/m/Y H:i', $data['ending'])->format('Y-m-d H:i:s');
				$update = $result->update($data);
				if($update){
					return responseJson(['message' => trans('messages.YourRequestUpdatedSuccessFully')], 200);
				}
				return responseJson(['error' => trans('messages.SomethingwentWrong')], 400);
			}else return responseJson(['error' => trans('messages.PenaltyDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function DeletePenalty($id){
		try{
			$user = \Auth::guard("api")->user();
			$penalty = \App\Penalty::find($id);
			if($penalty){
				$penalty->delete();
				return responseJson(['message' => trans("messages.PenaltyDeletedSuccessfully")], 200);
			}else return responseJson(['error' => trans('messages.PenaltyDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public static function createPenaltyAction($m, $manager, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			if($manager){
				$managerData = $manager->manager;
				$mail = $m->to($managerData->email)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
			$admin = env('ADMIN_MAIL');
			if($admin){
				$mail->to($admin)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
		}
	}
}
