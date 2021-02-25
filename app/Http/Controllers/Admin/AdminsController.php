<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Admin;
use Validator, Auth, File, Image;

class AdminsController extends Controller {

	protected $rules = [
		'firstname'	=> "required|max:255",
		'lastname'	=> "required|max:255",
		'email'		=> "required|email|unique:admins",
		'password'	=> "confirmed|min:8|max:100",
	];

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
			$query = \App\Admin::where('id', '!=', Auth::guard('admins')->user()->id);
			if($search){ $query = $query->whereRaw("firstname LIKE ? OR lastname LIKE ? OR email LIKE ?", [$q, $q, $q]); }
			$admins = $query->orderBy($queryParam, $sort)->paginate(15);
			$admins->appends(request()->except(['page', '_token']));
			return View('admin.admins.index', [
				'admins' => $admins,
				'title' => trans('messages.ManagementAdministrators'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function deleteAdmin(Request $request, $id){
		$admins = Admin::whereRaw('id != ? AND id = ?', [Auth::guard('admins')->user()->id, $id,])->first();
		if($admins){
			$admins->delete();
			return redirect('/admin/admins')->withMessage(trans("messages.AdminDeletedSuccessfully"));
		}else return redirect('/admin/admins')->withError(trans('messages.AdminNotFound'));
	}

	public function getEdit($id){
		$admin = Admin::whereRaw('id != ? AND id = ?', [Auth::guard('admins')->user()->id, $id,])->first();
		if($admin) return view('admin.admins.edit', ['admins' => $admin, 'title' => trans("messages.AdminEdit"), ]);
		else return redirect('/admin/admins')->withError(trans('messages.AdminNotFound'));
	}

	public function postEdit(Request $request, $id){
		$rules = $this->rules;
		$rules['email'] .= ",email,$id";
		$rules['password'] = "nullable|confirmed|min:8|max:100";
		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
		$admins = Admin::find($id);
		if($admins){
			$data = $request->only(['firstname', 'lastname', 'email',]);
			if($request->has('password') && !empty($request->input('password'))){
				$data['password'] = \Hash::make($request->input('password'));
			}
			$insert = $admins->update($data);
			if($insert) return redirect('/admin/admins')->withMessage(trans('messages.AdminUpdatedSuccessfully'));
			else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
		}else return redirect('/admin/admins')->withError(trans('messages.AdminNotFound'));
	}

	public function getCreate(){		
		return view('admin.admins.create', ['title' => trans("messages.CreateAdministrator"),]);
	}

	public function postCreate(Request $request){
		$validator = Validator::make($request->all(), $this->rules);
		if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
		$data = $request->only(['firstname', 'lastname', 'email',]);
		$data['password'] = \Hash::make($request->input('password'));
		$insert = \App\Admin::create($data);
		if($insert) return redirect('/admin/admins')->withMessage(trans("messages.AdminCreatedSuccessfully"));
		else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
	}	
}