<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Cache;

class AbsenceTypesController extends Controller {

	protected $rules = [
		'label'			=> "required|max:255",
		'status'		=> "required|boolean",
		'auto_approve'	=> "required|boolean",
		'color'			=> "required",
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
			$query = new \App\AbsenceTypes();
			if($search){ $query = $query->whereRaw("slug LIKE ? OR label LIKE ?", [$q, $q]); }
			$types = $query->orderBy($queryParam, $sort)->paginate(15);
			$types->appends(request()->except(['page', '_token']));
			$default_types = \App\AbsenceTypes::getDefaultTypes();
			return View('admin.abtypes.index', [
				'types' => $types,
				'title' => trans('messages.AbsenceTypesManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
				'default_types' => $default_types,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function newType(){
		try{
			$colors = Config::get('colors');
			return View('admin.abtypes.add', ['title' => trans("messages.AddAbsenceType"), 'colors' => $colors]);
		}catch(\Exception $e){
			return redirect('/admin/absence-types')->withError($e->getMessage());
		}
	}

	public function postNewType(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['label', 'status', 'auto_approve', 'color']);
			$insert = \App\AbsenceTypes::create($data);
			if($insert){
				Cache::flush();
				return redirect('/admin/absence-types')->withMessage(trans("messages.AbsenceTypeCreatedSuccessfully"));
			}else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/absence-types')->withError($e->getMessage());
		}
	}

	public function deleteType(Request $request, $id){
		try{
			$type = \App\AbsenceTypes::find($id);
			if($type){
				$default_types = \App\AbsenceTypes::getDefaultTypes();
				if(!in_array($type->slug, $default_types)){
					Cache::flush();
					if($type->status){
						$type->update(['status' => 0]);
						return redirect('/admin/absence-types')->withMessage(trans("messages.AbsenceTypeDisabledSuccessfully"));
					}else{
						$type->delete();
						return redirect('/admin/absence-types')->withMessage(trans("messages.AbsenceTypeDeletedSuccessfully"));
					}
				}else{
					return redirect('/admin/absence-types')->withError(trans("messages.DefaultAbsenceTypeCannotbeDeleted"));
				}
			}else return redirect('/admin/absence-types')->withError(trans('messages.AbsenceTypeNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/absence-types')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$type = \App\AbsenceTypes::find($id);
			if($type){
				$colors = Config::get('colors');
				return View('admin.abtypes.edit', ['type' => $type, 'title' => trans("messages.AbsenceTypeEdit"), 'colors' => $colors]);
			}
			return redirect('/admin/absence-types')->withError(trans('messages.AbsenceTypeNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/absence-types')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$type = \App\AbsenceTypes::find($id);
			if($type){
				$data = $request->only(['label', 'status', 'auto_approve', 'color']);
				$insert = $type->update($data);
				if($insert){
					Cache::flush();
					return redirect('/admin/absence-types')->withMessage(trans('messages.AbsenceTypeUpdatedSuccessfully'));
				}else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/absence-types')->withError(trans('messages.AbsenceTypeNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/absence-types')->withError($e->getMessage());
		}
	}
}