<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config;

class UsersController extends Controller {

	protected $rules = [
		'firstname'	=> "required|max:255",
		'lastname'	=> "required|max:255",
		'email'		=> "required|email|unique:users",
		'password'	=> "required|confirmed|min:6|max:25",
		'status'	=> "required|boolean",
	];

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
			$query = new \App\User();
			if($search){ $query = $query->whereRaw("role LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?", [$q, $q, $q, $q]); }
			$users = $query->orderBy($queryParam, $sort)->paginate(15);
			$users->appends(request()->except(['page', '_token']));
			return View('admin.users.index', [
				'users' => $users,
				'title' => trans('messages.UsersManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function newUser(){
		try{
			$colors = Config::get('colors');
			return View('admin.users.add', ['title' => trans("messages.AddUser"), 'colors' => $colors]);
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function postNewUser(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['firstname', 'lastname', 'email', 'password', 'mobile', 'address', 'role', 'color', 'status']);
			$data['password'] = \Hash::make($request->input('password'));
			$dt = new DateTime();
			$data['creation_date'] = $dt->format('Y-m-d H:i:s');
			$insert = \App\User::create($data);
			if($insert) return redirect('/admin/users')->withMessage(trans("messages.UserAddedSuccessfully"));
			else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function deleteUser(Request $request, $id){
		try{
			$user = \App\User::find($id);
			if($user){
				$user->delete();
				return redirect('/admin/users')->withMessage(trans("messages.UserDeletedSuccessfully"));
			}else return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function disableUser(Request $request, $id){
		try{
			$user = \App\User::find($id);
			if($user){
				$user->update(['status' => false,]);
				return redirect()->back()->withMessage(trans("messages.UserDisabledSuccessfully"));
			}else return redirect()->back()->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect()->back()->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$user = \App\User::find($id);
			if($user){
				$colors = Config::get('colors');
				return View('admin.users.edit', ['user' => $user, 'title' => trans("messages.UserEdit"), 'colors' => $colors]);
			}
			return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$rules = $this->rules;
			$rules['email'] .= ",email,$id";
			$rules['password'] = "nullable|confirmed|min:8|max:100";
			$validator = Validator::make($request->all(), $rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$user = \App\User::find($id);
			if($user){
				$data = $request->only(['firstname', 'lastname', 'email', 'mobile', 'address', 'role', 'color', 'status']);
				if($request->has('password') && !empty($request->input('password'))){
					$data['password'] = \Hash::make($request->input('password'));
				}
				$insert = $user->update($data);
				if($insert) return redirect('/admin/users')->withMessage(trans('messages.UserUpdatedSuccessfully'));
				else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function getUserMissions($id){
		try{
			$user = \App\User::find($id);
			if($user){
				$usermissions = \App\UserMission::where('user_id', $id)->get();
				$missions = \App\Mission::where('status', true)->whereNotIn('id', $usermissions->pluck('mission_id'))->pluck('code', 'id');
				$usermissions = \App\UserMission::where('user_id', $id)->paginate(10);
				return View('admin.users.missions', ['user' => $user, 'title' => trans("messages.UserMissions"), 'missions' => $missions, 'usermissions' => $usermissions]);
			}
			return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function postUserMissions(Request $request, $id){
		try{
			$mission_ids = $request->get('mission_ids');
			if(count($mission_ids) <= 0){ return redirect('/admin/users/' . $id . '/missions')->withError(trans('messages.PleaseSelectOneMission'))->withInput(); }
			$user = \App\User::find($id);
			if(!$user){ return redirect('/admin/users')->withError(trans('messages.UserNotFound')); }
			$missions = \App\Mission::where('status', true)->get();
			foreach($mission_ids as $mission_id){
				$mission = $missions->where('id', $mission_id);
				if($mission->count() <= 0){ return redirect('/admin/users/' . $id . '/missions')->withError(trans('messages.MissionNotFound'))->withInput(); }
				\App\UserMission::create(['user_id' => $id, 'mission_id' => $mission_id]);
			}
			return redirect('/admin/users/' . $id . '/missions')->withMessage(trans('messages.UserMissionAddedSuccessfully'));
		}catch(\Exception $e){
			return redirect('/admin/users/' . $id . '/missions')->withError($e->getMessage());
		}
	}

	public function deleteUserMissions($user_id, $mission_id){
		try{
			$user = \App\User::find($user_id);
			if($user){
				$usermission = \App\UserMission::whereRaw('user_id = ? AND id = ?', [$user_id, $mission_id])->first();
				if($usermission){
					$usermission->delete();
					return redirect('/admin/users/' . $user_id . '/missions')->withMessage(trans('messages.UserMissionDeletedSuccessfully'));
				}
				return redirect('/admin/users/' . $user_id . '/missions')->withError(trans('messages.UserMissionNotFound'));
			}
			return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users/' . $user_id . '/missions')->withError($e->getMessage());
		}
	}

	public function getUserConsultants($id){
		try{
			$user = \App\User::find($id);
			if($user && $user->role == 'manager'){
				$userconsultants = \App\UserManager::where('manager_id', $id)->get();
				$consultants = \App\User::whereNotIn('id', $userconsultants->pluck('consultant_id'))->get()->pluck('name', 'id');
				$userconsultants = \App\UserManager::where('manager_id', $id)->paginate(10);
				return View('admin.users.consultants', ['user' => $user, 'title' => trans("messages.UserConsultants"), 'consultants' => $consultants, 'userconsultants' => $userconsultants]);
			}
			return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public function postUserConsultants(Request $request, $id){
		try{
			$consultant_ids = $request->get('consultant_ids');
			if(count($consultant_ids) <= 0){ return redirect('/admin/users/' . $id . '/consultants')->withError(trans('messages.PleaseSelectOneConsultant'))->withInput(); }
			$user = \App\User::find($id);
			if($user && $user->role == 'manager'){
				// $users = \App\User::where('role', 'consultant')->get();
				$users = \App\User::all();
				foreach($consultant_ids as $consultant_id){
					$user = $users->where('id', $consultant_id);
					if($user->count() <= 0){ return redirect('/admin/users/' . $id . '/consultants')->withError(trans('messages.ConsultantNotFound'))->withInput(); }
					\App\UserManager::create(['manager_id' => $id, 'consultant_id' => $consultant_id]);
				}
				return redirect('/admin/users/' . $id . '/consultants')->withMessage(trans('messages.UserConsultantAddedSuccessfully'));
			}
			return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users/' . $id . '/consultants')->withError($e->getMessage());
		}
	}

	public function deleteUserConsultant($user_id, $consultant_id){
		try{
			$user = \App\User::find($user_id);
			if($user && $user->role == 'manager'){
				$userconsultant = \App\UserManager::whereRaw('manager_id = ? AND id = ?', [$user_id, $consultant_id])->first();
				if($userconsultant){
					$userconsultant->delete();
					return redirect('/admin/users/' . $user_id . '/consultants')->withMessage(trans('messages.UserConsultantDeletedSuccessfully'));
				}
				return redirect('/admin/users/' . $user_id . '/consultants')->withError(trans('messages.UserConsultantNotFound'));
			}
			return redirect('/admin/users')->withError(trans('messages.UserNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/users/' . $user_id . '/consultants')->withError($e->getMessage());
		}
	}
}