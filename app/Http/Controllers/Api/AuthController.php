<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator, Redirect, Auth, Mail, Hash, File, DbView, Image;
use \App\RandomColor;

class AuthController extends Controller {

	public function register(Request $request){
		$data = $request->all();
		$validator = Validator::make($data, [
			'firstname' => 'required|min:2|max:255', 'lastname' => 'required|min:2|max:255',
			'email' => 'required|email', 'password' => 'required|confirmed|min:6|max:25', 'address' => 'required|min:3|max:25',
			'mobile' => 'required|min:10|max:10', 'role' => 'required|max:25', 'image' => "mimes:jpeg,jpg,bmp,png",
		]);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true, ], 400);
		try{
			$user = \App\User::where('email', '=', $request->get('email'))->first();
			if($user) return responseJson(['error' => trans("messages.Emailalreadyregistered"), ], 400);
			else{
				if($request->hasFile('image')){
					$image_file = $request->file('image');
					$timestamp = time();
					$image = $image_file->getClientOriginalName();
					$image_file_name = pathinfo($image, PATHINFO_FILENAME);
					$image_extension = pathinfo($image, PATHINFO_EXTENSION);
					$fname = $image_file_name . '_' . $timestamp . "." . $image_extension;
					$image_file->move("uploads/image", $fname);
					$data['image'] = "uploads/image/" . $fname;
					$img = Image::make($data['image'])->resize(256, 256);
					$thumbImage = str_replace("/image/", "/image/thumbnail/", $data['image']);
					$img->save($thumbImage);
				}
				$data['password'] = \Hash::make($data['password']);
				$data['api_token'] = getApiToken();
				$user = \App\User::create($data);
				Auth::user($user);
				$userData = getApiUserData($user);
				return responseJson(['status' => true, 'success' => true, "api_token" => $user['api_token'], 'data' => $userData, 'message' => trans("messages.RegisteredSuccessfully"), ], 201);
			}
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function postSocial(Request $request){
		$data = $request->only(['email', 'password', 'socialId']);
		$validator = Validator::make($data, ['email' => 'required|email|max:255', 'password' => 'required|min:6|max:50', 'socialId' => "required",]);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true, ], 400);
		try{
			$data['password'] = \Hash::make($data['password']);
			$user = \App\User::where('email', '=', $request->get('email'))->orWhere('socialId', '=', $request->get('socialId'))->first();
			$api = getApiToken();
			if($api['error']){ return responseJson(["error" => $api['exception']->getMessage(), "status" => false, ], 400); }
			else if($user){
				if(!$user->status) return responseJson(['error' => trans('messages.account_disabled'), ], 400);
				else{
					Auth::user($user);
					$data = ["api_token" => $api['token'], 'socialId' => $data['socialId']];
					if($user->color == null || $user->color == ''){
						$data['color'] = RandomColor::one(['format' => 'hex']);
					}
					if($request->has('picture') && !empty($request->get('picture'))){
						$fname = $user->id . '_' . time() . ".png";
						$ch = curl_init($request->get('picture'));
						$fp = fopen("uploads/image/" . $fname, 'wb');
						curl_setopt($ch, CURLOPT_FILE, $fp);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_exec($ch);
						curl_close($ch);
						fclose($fp);
						$data['image'] = "uploads/image/" . $fname;
						$img = Image::make($data['image'])->resize(256, 256);
						$thumbImage = str_replace("/image/", "/image/thumbnail/", $data['image']);
						$img->save($thumbImage);
						if(isset($user->image)){
							File::delete(str_replace("/image/", "/image/thumbnail/", $user->image));
							File::delete($user->image);
						}
					}
					$user->update($data);
					$userData = getApiUserData($user);
					return responseJson(['api_token' => $api['token'], 'data' => $userData, 'message' => trans("messages.LoggedInSuccessfully"), ], 201);
				}
			}
			return responseJson(['error' => trans('messages.YouAreNotValidUser'), ], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function authenticate(Request $request){
		$rules = ['email' => 'required|email', 'password' => "required|min:6|max:25"];
		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true, ], 400);
		$user = \App\User::where('email', '=', $request->get('email'))->first();
		if(!$user) return responseJson(['error' => trans('messages.Incorrectemailpassword'), ], 400);
		elseif(!$user->status) return responseJson(['error' => trans('messages.account_disabled'), ], 400);
		else if(Auth::attempt($request->only(['email', 'password']))){
			try{
				$api = getApiToken();
				if($api['error']){ return responseJson(["error" => $api['exception']->getMessage(), "status" => false, ], 400); }
				$user->update(["api_token" => $api['token']]);
				$userData = getApiUserData($user);
				return responseJson(["api_token" => $api['token'], 'data' => $userData, 'message' => trans("messages.LoggedInSuccessfully"), ], 201);
			}catch(\Exception $e){
				return responseJson(["error" => $e->getMessage(), "status" => false, ], 400);
			}
		}
		return responseJson(["error" => trans("messages.PleaseCheckEmailPassword"), "status" => false, ], 400);
	}
	
	public function forgotPassword(Request $request){
		$validator = Validator::make($request->all(), ['email' => 'required|email', ]);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true, ], 400);
		$user = \App\User::where('email', '=', $request->get('email'))->first();
		if(!$user) return responseJson(['error' => trans("messages.EmailNotfound"), ], 400);
		elseif(!$user->status) return responseJson(['error' => trans('messages.account_disabled'), ], 400);
		else{
			\App\Token::where("user_id", '=', $user->id)->delete();
			try{
				$token = generateRandomStr(6);
				\App\Token::create(["user_id" => $user->id, 'token' => $token, ]);
				$mail = \App\EmailTemplate::where('email', 'forgot-password')->where('status', true)->first();
				$subject = trans('messages.ResetToken');
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
							}else{ $mails[] = $amail; }
						}
					}
					if(!is_null($mail->cc_email)){
						$cmails = explode(',', $mail->cc_email);
						foreach($cmails as $key => $cmail){
							$cmail = trim($cmail);
							if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
								if($cmail == '{{$user->email}}'){ $ccmails[] = $user->email; }
							}else{ $ccmails[] = $cmail; }
						}
					}
					$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'token' => $token, 'user' => $user])->render();
					$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'token' => $token, 'user' => $user])->render();
					Mail::raw($template, function($m) use($user, $subject, $template, $mails, $ccmails){ self::forgotPasswordAction($m, $user, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('auth.emails.password', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'token' => $token, 'user' => $user], function($m) use($user, $subject){ self::forgotPasswordAction($m, $user, $subject); });
				}
				return responseJson(['status' => true, 'success' => true, 'message' => trans("messages.Pleasecheckemailfurtherdetails"), ], 201);
			}catch(\Exception $e){
				return responseJson(["error" => $e->getMessage(), "status" => false, ], 400);
			}
		}
		return responseJson(["error" => trans("messages.PleaseCheckEmail"), "status" => false, ], 400);
	}
	
	public function postVerifyToken(Request $request){
		$data = $request->only(['email', 'token', ]);
		$validator = Validator::make($data, ['email' => 'required|email', 'token' => 'required|min:6,max:6',]);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true,], 400);
		try{
			$user = \App\User::where('email', '=', $data['email'])->first();
			if(!$user) return responseJson(['error' => trans("messages.EmailNotfound"), ], 400);
			elseif(!$user->status) return responseJson(['error' => trans('messages.account_disabled'), ], 400);
			else{
				$token= \App\Token::whereRaw('user_id = ? AND token = ?',[$user->id, $data['token']])->first();
				if(!$token) return responseJson(['error' => trans("messages.TokenNotFound"), ], 400);
				else if($token->isExpired()){ return responseJson(['status' => true, 'success' => true, 'message' => trans("messages.TokenVerified"), ], 201); }
				else{ return responseJson(['status' => false, "expired" => true, 'message' => trans("messages.TokenExpired"), ], 400); }
			}
			return responseJson(["error" => trans("messages.SomethingwentWrong"), "status" => false, ], 400);
		}catch(\Exception $e){
			return responseJson(["error" => $e->getMessage(), "status" => false, ], 400);
		}
	}

	public function postResetPassword(Request $request){
		$data = $request->all();
		$validator = Validator::make($data, ['password' => 'required|confirmed|min:8|max:50', 'token' => 'required|min:6,max:6',]);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true, ], 400);
		try{
			$token = \App\Token::where('token', $data['token'])->first();
			if(!$token) return responseJson(['error' => trans("messages.TokenNotFound"), ], 400);
			if($token->isExpired()){
				$user = \App\User::where('id', '=', $token->user_id)->first();
				if(!$user) return responseJson(['error' => trans("messages.UserNotfound"), ], 400);
				elseif(!$user->status) return responseJson(['error' => trans('messages.account_disabled'), ], 400);
				else{
					$password = $request->get('password');
					$user->update(['password' => \Hash::make($request->get('password')), ]);
					$token->delete();
					return responseJson(['status' => true, 'success' => true, 'message' => trans("messages.PasswordUpdatedSuccessfully"), ], 201);
				}
			}else{
				return responseJson(['status' => false, "expired" => true, 'message' => trans("messages.TokenExpired"), ], 400);
			}return responseJson(["error" => trans("messages.SomethingwentWrong"), "status" => false, ], 400);
		}catch(\Exception $e){
			return responseJson(["error" => $e->getMessage(), "status" => false, ], 400);
		}
	}

	public static function forgotPasswordAction($m, $user, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($user->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}
}