<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config;

class MissionController extends Controller {

	protected $activityTypes = [];

	protected $rules = [
		'code'			=> "required|max:255|unique:mission",
		'label'			=> "required|max:255",
		'order'			=> "required|integer",
		'client_id'		=> "required|integer",
		'status'		=> "required|boolean",
	];

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(){
		$this->activityTypes = \App\Mission::getActivityTypes();
		if(count($this->activityTypes) > 0){
			$this->rules['activity_type'] = "nullable|in:" . implode(',', array_keys($this->activityTypes));
		}
	}

	public function getIndex(Request $request){
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
			$query = new \App\Mission();
			if($search){ $query = $query->whereRaw("code LIKE ? OR label LIKE ?", [$q, $q]); }
			$missions = $query->orderBy($queryParam, $sort)->paginate(15);
			$missions->appends(request()->except(['page', '_token']));
			return View('admin.missions.index', [
				'missions' => $missions,
				'title' => trans('messages.MissionsManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function getCreate(){
		try{
			$clients = \App\Clients::where('status', true)->get()->pluck('name', 'id');
			$users = \App\User::where('status', true)->get()->pluck('name', 'id');
			return View('admin.missions.create', [
				'title' => trans("messages.AddMission"),
				'clients' => $clients,
				'activity_types' => $this->activityTypes,
				'users' => $users,
			]);
		}catch(\Exception $e){
			return redirect('/admin/missions')->withError($e->getMessage());
		}
	}

	public function postCreate(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['code', 'label', 'order', 'status', 'client_id', 'activity_type', 'commercial']);
			$client = \App\Clients::find($data['client_id']);
			if(!$client){ return redirect('/admin/missions')->withError(trans("messages.ClientNotFound")); }
			$insert = \App\Mission::create($data);
			if($insert) return redirect('/admin/missions')->withMessage(trans("messages.MissionAddedSuccessfully"));
			else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/missions')->withError($e->getMessage());
		}
	}

	public function deleteMission(Request $request, $id){
		try{
			$mission = \App\Mission::find($id);
			if($mission){
				$cras = \App\CRA::where('mission_id', $id)->get();
				$penalties = \App\Penalty::where('mission_id', $id)->get();
				if($cras->count() > 0 || $penalties->count() > 0){ return redirect('/admin/missions')->withError(trans("messages.SomethingwentWrong")); }
				else{
					$mission->delete();
					return redirect('/admin/missions')->withMessage(trans("messages.MissionDeletedSuccessfully"));
				}
			}else return redirect('/admin/missions')->withError(trans('messages.MissionNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/missions')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$mission = \App\Mission::find($id);
			if($mission){
				$clients = \App\Clients::where('status', true)->get()->pluck('name', 'id');
				$users = \App\User::where('status', true)->get()->pluck('name', 'id');
				return View('admin.missions.edit', [
					'mission' => $mission,
					'title' => trans("messages.MissionEdit"),
					'clients' => $clients,
					'activity_types' => $this->activityTypes,
					'users' => $users
				]);
			}
			return redirect('/admin/missions')->withError(trans('messages.ClientNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/missions')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$rules = $this->rules;
			$rules['code'] .= ",code,$id";
			$validator = Validator::make($request->all(), $rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$mission = \App\Mission::find($id);
			if($mission){
				$data = $request->only(['code', 'label', 'order', 'status', 'client_id', 'activity_type', 'commercial']);
				$client = \App\Clients::find($data['client_id']);
				if(!$client){ return redirect('/admin/missions')->withError(trans("messages.ClientNotFound")); }
				$insert = $mission->update($data);
				if($insert) return redirect('/admin/missions')->withMessage(trans('messages.MissionUpdatedSuccessfully'));
				else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/missions')->withError(trans('messages.MissionNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/missions')->withError($e->getMessage());
		}
	}
}