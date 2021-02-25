<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Mail, File, Image, DbView;
use Carbon\Carbon;

class ContactController extends Controller {

	public function getAll(){
		try{
			$user = \Auth::guard("api")->user();
			$contact = \App\Contact::where('user_id', $user->id)->orderBy('id', 'DESC')->paginate(15);
			return responseJson(['contact' => $contact->items(), 'user' => $user,
				'lastPage' => $contact->lastPage(),
				'currentPage' => $contact->currentPage(),
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function postNewContact(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$validator = Validator::make($request->all(), [
				'subject' => "required", 'description' => 'required',
				'base64' => "required|boolean",
				'web' => "required|boolean", 'app_name' => "required",
			]);
			$validator->sometimes(['attachement_1', 'attachement_2'], "mimes:jpeg,png,jpg", function($input){
				return in_array($input->base64, [false, 0, "0"]);
			});
			$validator->sometimes(['attachement_1', 'attachement_2'], "base64image", function($input){
				return in_array($input->base64, [true, 1, "1"]);
			});
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			$data = $request->only(['subject', 'web', 'app_name', 'description']);
			$data['app_version'] = '0.0.1';
			$version = \App\Version::orderBy('created_at', 'DESC')->first();
			if($version){ $data['app_version'] = $version->version; }
			$attachements = ['attachement_1', 'attachement_2'];
			foreach($attachements as $key => $attachement){
				if(in_array($request->get('base64'), [false, 0, "0"])){
					if($request->hasFile($attachement)){
						$image_file = $request->file($attachement);
						$timestamp = time();
						$photo = $image_file->getClientOriginalName();
						$image_file_name = pathinfo($photo, PATHINFO_FILENAME);
						$image_extension = pathinfo($photo, PATHINFO_EXTENSION);
						$fname = $image_file_name . '_' . $timestamp . "." . $image_extension;
						$image_file->move("uploads/image", $fname);
						$data[$attachement] = "uploads/image/" . $fname;
						$imgthumb = Image::make($data[$attachement])->resize(256, 256);
						$thumbImage = str_replace("/image/", "/image/thumbnail/", $data[$attachement]);
						$imgthumb->save($thumbImage);
					}
				}elseif(in_array($request->get('base64'), [true, 1, "1"])){
					if($request->has($attachement)){
						$image = $request->get($attachement); // your base64 encoded
						$image = str_replace('data:image/png;base64,', '', $image);
						$image = str_replace('data:image/jpg;base64,', '', $image);
						$image = str_replace('data:image/jpeg;base64,', '', $image);
						$image = str_replace(' ', '+', $image);
						$timestamp = time();
						$fname = 'base_' . str_random(10) . $timestamp . '.png';
						\File::put(public_path(). '/uploads/image/' . $fname, base64_decode($image));
						$data[$attachement] = "uploads/image/" . $fname;
						$imgthumb = Image::make($data[$attachement])->resize(256, 256);
						$thumbImage = str_replace("/image/", "/image/thumbnail/", $data[$attachement]);
						$imgthumb->save($thumbImage);
					}
				}
			}
			$data['user_id'] = $user->id;
			$insert = \App\Contact::create($data);
			if($insert){
				$manager = \App\UserManager::where('consultant_id', $user->id)->first();
				$mail = \App\EmailTemplate::where('email', 'contact')->where('status', true)->first();
				$subject = trans('messages.NewContactCreated');
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
					$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'data' => $data, 'user' => $user])->render();
					$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'data' => $data, 'user' => $user])->render();
					Mail::raw($template, function($m) use($manager, $subject, $data, $template, $mails, $ccmails){ self::createContactAction($m, $manager, $subject, $data, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.contact', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'data' => $data, 'user' => $user] , function($m) use($manager, $subject, $data){ self::createContactAction($m, $manager, $subject, $data); });
				}
				$userData = getApiUserData($user);
				return responseJson(['message' => trans("messages.ContactAddedSuccessfully"), 'data' => $userData,], 200);
			}else return responseJson(['error' => trans('messages.SomethingwentWrong')], 400);
		}catch(\Exception $e){
			return redirect('/admin/contact')->withError($e->getMessage());
		}
	}

	public function deleteContact(Request $request, $id){
		try{
			$user = \Auth::guard("api")->user();
			if(!$user) return responseJson(['error' => trans('messages.UserNotFound')], 400);
			$contact = \App\Contact::whereRaw("id = ? AND user_id = ?", [$id, $user->id])->delete();
			if($contact){ return responseJson(['message' => trans("messages.ContactDeletedSuccessfully")], 200);}
			else{ return responseJson(['error' => trans('messages.ContactNotFound')], 400);}
		}catch(\Exception $e){
			return responseJson(['error' => trans('messages.ContactNotFound')], 400);
		}
	}

	public static function createContactAction($m, $manager, $subject, $data, $template = null, $add_email = [], $cc_email = []){
		$attachements = ['attachement_1', 'attachement_2'];
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
			/*foreach($attachements as $key => $attachement){
				if(isset($data[$attachement])){ $mail->attach(public_path() . '/' . $data[$attachement]); }
			}*/
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
			/*foreach($attachements as $key => $attachement){
				if(isset($data[$attachement])){ $mail->attach(public_path() . '/' . $data[$attachement]); }
			}*/
		}
	}
}