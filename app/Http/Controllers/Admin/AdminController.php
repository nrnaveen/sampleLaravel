<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Session, Validator;
use App\Admin;

class AdminController extends Controller {

	public function index(){
		$title = "Admin";
		$usersCount = \App\User::get()->count();
		$adminsCount = \App\Admin::get()->count();
		$clientsCount = \App\Clients::get()->count();
		$missionsCount = \App\Mission::get()->count();
		$absencesCount = \App\Absences::get()->count();
		$crasCount = \App\CRA::get()->count();
		$penaltiesCount = \App\Penalty::get()->count();
		return view('admin.index', compact('title', 'usersCount', 'adminsCount', 'clientsCount', 'missionsCount', 'absencesCount', 'crasCount', 'penaltiesCount'));
	}

	public function getProfile(){
		$admin = Auth::guard('admins')->user();
		$title = 'Profile';
		if($admin) return view('admin.profile', compact('admin', 'title'));
		else{
			Auth::logout();
			return redirect('/admin')->withError('Admin not Found!');
		}
	}

	public function postProfile(Request $request){
		try{
			$validator = Validator::make($request->all(), ['firstname' => 'required|max:255',
				'lastname' => 'required|max:255', 'password' => 'nullable|confirmed|min:8|max:50',
			]);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$admin = Auth::guard('admins')->user();
			if($admin){
				$data = $request->only(['firstname', 'lastname',]);
				if($request->has('password') && !empty($request->input('password'))){
					$data['password'] = \Hash::make($request->input('password'));
				}
				$insert = $admin->update($data);
				if($insert) return redirect('/admin')->withMessage(trans('messages.ProfileUpdatedSuccessfully'));
				else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else{
				Auth::logout();
				return redirect('/admin/login')->withError(trans('messages.UserNotFound'));
			}
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function logout(){
		try{
			Auth::guard('admins')->logout();
			return redirect('/admin/login')->withMessage(trans("messages.LoggedOutSuccessfully"));
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function getSiteInfo(){
		try{
			$title = trans("messages.SiteInfo");
			$siteinfo = \App\SiteInfo::first();
			if(!$siteinfo){ $siteinfo = new \App\SiteInfo(); }
			$admin_mail = env('ADMIN_MAIL');
			return view('admin.siteinfo', compact('title', 'siteinfo', 'admin_mail'));
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function postSiteInfo(Request $request){
		try{
			$validator = Validator::make($request->all(), ['request_count' => 'required|numeric',]);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['request_count',]);
			$siteinfo = \App\SiteInfo::first();
			if($siteinfo){ $insert = $siteinfo->update($data); }
			else{ $insert = \App\SiteInfo::create($data); }
			if($insert) return redirect('/admin/site-info')->withMessage(trans("messages.UpdatedSuccessfully"));
			else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function postAdminEmail(Request $request){
		try{
			$validator = Validator::make($request->all(), ['email' => 'required|email',]);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$email = $request->get('email');
			setEnvironmentValue(['ADMIN_MAIL' => $email]);
			return redirect('/admin/site-info')->withMessage(trans("messages.UpdatedSuccessfully"));
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}
}