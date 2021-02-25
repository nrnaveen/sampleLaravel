<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator, Redirect, Auth, Mail, Hash, File, Image;

class UserController extends Controller {

	public function getAll(){
		try{
			$user = \Auth::guard("api")->user();
			$result = \App\User::get();
			if($result) return responseJson(['data' => $result], 200);
			else return responseJson(['error' => trans('messages.NoDataAvailable')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getClients(){
		try{
			$user = \Auth::guard("api")->user();
			$clients = \App\Clients::where('status', true)->get();
			if($clients->count() > 0){
				foreach($clients as $key => $value){ $value->name = $value->name; }
				$userData = getApiUserData($user);
				return responseJson(['data' => $clients, 'user' => $userData], 200);
			}else return responseJson(['error' => trans('messages.NoDataAvailable')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getMissions(){
		try{
			$user = \Auth::guard("api")->user();
			$usermissions = \App\UserMission::where('user_id', $user->id)->get()->pluck(['mission_id']);
			$missions = \App\Mission::where('status', true)->whereIn('id', $usermissions)->orderBy('order', 'ASC')->get();
			if($missions->count() > 0){
				foreach($missions as $key => $value){ $value->name = $value->code; }
				$userData = getApiUserData($user);
				return responseJson(['data' => $missions, 'user' => $userData], 200);
			}else return responseJson(['error' => trans('messages.NoDataAvailable')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getConsultants(){
		try{
			$user = \Auth::guard("api")->user();
			if($user && $user->role == 'manager'){
				$consultants = \App\UserManager::where('manager_id', $user->id)->paginate(10);
				foreach($consultants as $key => $value){
					$value->managerName = $value->manager->name;
					$value->consultantName = $value->consultant->name;
				};
				$userData = getApiUserData($user);
				return responseJson(['consultants' => $consultants->items(), 'lastPage' => $consultants->lastPage(), 'currentPage' => $consultants->currentPage(), 'user' => $userData], 200);
			}
			return responseJson(['error' => trans('messages.NoDataAvailable')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getProfile(){
		try{
			$user = \Auth::guard("api")->user();
			$userData = getApiUserData($user);
			return responseJson(['data' => $userData], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function postProfile(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$data = $request->all();
			if(!$user){ return responseJson(['error' => trans('messages.UserNotFound')], 400); }
			$validator = Validator::make($data, ['firstname' => 'required|min:2|max:255',
				'lastname' => 'required|min:2|max:255', 'email' => 'required|email|max:255',
				'image' => "mimes:jpeg,jpg,bmp,png",
			]);
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			if($request->hasFile('image')){
				$image_file = $request->file('image');
				$timestamp = time();
				$image = $image_file->getClientOriginalName();
				$image_file_name = pathinfo($image, PATHINFO_FILENAME);
				$image_extension = pathinfo($image, PATHINFO_EXTENSION);
				$fname = $image_file_name . '_' . $timestamp . "." . $image_extension;
				$image_file->move("uploads/image", $fname);
				$data['image'] = "uploads/image/" . $fname;
				if(isset($user->image)){
					File::delete(str_replace("/image/", "/image/thumbnail/", $user->image));
					File::delete($user->image);
				}
				$img = Image::make($data['image'])->resize(256, 256);
				$thumbImage = str_replace("/image/", "/image/thumbnail/", $data['image']);
				$img->save($thumbImage);
			}
			$user->update($data);
			$userData = getApiUserData($user);
			if($user) return responseJson(['user' => $userData, 'message' =>trans('messages.ProfileUpdatedSuccessfully')], 200);
			else return resopnse()->json(['error' => trans('messages.SomethingWentWrong')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function changePass(Request $request){
		$data = $request->all();
		$user = \Auth::guard("api")->user();
		$validator = Validator::make($data, [
			'current_password' => 'required', 'password' => 'required|min:6|max:25',
			'confirm_Password' => 'required|same:password'
		]);
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
		if(Hash::check($data['current_password'], $user['password'])){
			$pass = Hash::make($data['password']);
			$updated = $user->update(["password" => $pass]);
			if($updated){
				return responseJson(['message' => trans('messages.PasswordUpdatedSuccessfully!')], 200);
			}
			return responseJson(['error' => trans('messages.SomethingWentWrong')], 400);
		}
		return responseJson(['error' => trans('messages.CurrentPasswordNotMatched')], 400);
	}

	public function postUpdateDevice(Request $request){
		$data = $request->only(['deviceId', 'registrationId', 'deviceType', 'userId']);
		$validator = Validator::make($data, ['deviceId' => 'required', 'registrationId' => 'required', 'deviceType' => 'required|in:android,ios', 'userId' => 'required']); 
		if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true, ], 400);
		try{
			$user = Auth::guard("api")->user();
			if($user->id == $data['userId']){
				unset($data['userId']);
				\App\User::where("deviceId", $data['deviceId'])->update(['deviceId' => null, 'registrationId' => null,]);
				$user->update($data);
				return responseJson(['message' => trans("messages.DeviceInfoUpdatedSuccessfully")], 200);
			}
			return responseJson(['error' => trans("messages.UnabletoupdateDevice")], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function getHome(){
		try{
			$user = \Auth::guard("api")->user();
			$timestamp = Carbon::now();
			$startDate = $timestamp->format("Y-m-01");
			$endDate = $timestamp->format("Y-m-t");
			$firstEle = \App\CRA::whereRaw('user_id = ? AND validation = ? AND (start between ? and ? OR end between ? and ?)', [$user['id'], true, $startDate, $endDate, $startDate, $endDate])->orderBy('updated_at', 'desc')->first();
			$events = \App\Events::whereRaw('date > ? AND status = ?', [date('Y-m-d'), true])->orderBy('date', 'ASC')->get();
			$secondEle = null;
			if($events->count() > 0){ $secondEle = $events->first(); }
			$thirdEle = null;
			if($events->count() > 1){ $thirdEle = $events->get(1); }
			$pending = 0;
			$valid = 0;
			$canceled = 0;
			$deleted = 0;
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
			$content = '';
			$homecontent = \App\HomeContent::orderBy('id', 'DESC')->first();
			if($homecontent){ $content = $homecontent->content; }
			return responseJson([ 'absenceData' => [
					'pending' => $pending,
					'validated' => $valid,
					'canceled' => $canceled,
					'deleted' => $deleted,
				], 'validatedAbsences' => $validatedAbsences,
				'refusedAbsences' => $refusedAbsences,
				'data' => getApiUserData($user),
				'firstEle' => $firstEle,
				'secondEle' => $secondEle,
				'thirdEle' => $thirdEle,
				'content' => $content,
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function logout(){
		try{
			Auth::guard("api")->user()->update(['api_token' => '', 'deviceId' => null, 'registrationId' => null,]);
			Auth::logout();
			return responseJson(['message' => trans("messages.LoggedOutSuccessfully"), 'status' => true], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}
}