<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Validator, DateTime, Config;

class VersionController extends Controller {

	protected $rules = ['version' => "required|unique:version"];

	public function getIndex(Request $request){
		try{
			$queryParam = 'id';
			$sort = 'DESC';
			if($request->has('query')){ $queryParam = $request->get('query'); }
			if($request->has('sort')){ $sort = $request->get('sort'); }
			$versions = \App\Version::orderBy($queryParam, $sort)->paginate(15);
			$versions->appends(request()->except(['page', '_token']));
			return View('admin.versions.index', [
				'versions' => $versions,
				'title' => trans("messages.VersionManagement"),
				'queryParam' => $queryParam,
				'sort' => $sort,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function postIndex(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$version = $request->get('version');
			$oldVersion = "0.0.0";
			$versionData = \App\Version::orderBy('created_at', 'DESC')->first();
			if($versionData){ $oldVersion = $versionData->version; }
			if(version_compare($version, $oldVersion) > 0){
				$insert = \App\Version::create(['version' => $version, 'status' => true]);
				if($insert) return redirect('/admin/version')->withMessage(trans("messages.VersionAddedSuccessfully"));
				else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
			}
			return redirect()->back()->withError(trans("messages.PleaseEnterValidVersion"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/version')->withError($e->getMessage());
		}
	}

	public function deleteIndex($id){
		try{
			$version = \App\Version::where('id', $id)->first();
			if($version){
				$version->delete();
				return redirect('/admin/version')->withMessage(trans('messages.VersionDeletedSuccessfully'));
			}
			return redirect('/admin/version')->withError(trans('messages.VersionNotFound'));
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}
}