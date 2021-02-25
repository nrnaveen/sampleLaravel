<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth, Session;

class AuthController extends Controller {

	public function __construct(){
		$this->middleware('guest:admins');
	}

	protected $rules = ['email' => 'required|email', 'password' => 'required|min:6', ];

	public function showLogin(){
		return view('admin.admin_login');
	}

	public function authenticate(Request $request){

		$validator = Validator::make($request->all(),$this->rules);
		if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
		if(Auth::guard('admins')->attempt($request->only(['email', 'password']), $request->get('remember'))){
			return redirect('/admin')->withMessage(trans('messages.LoggedInSuccessfully'));
		}
		return redirect()->back()->withError(trans('messages.Incorrectemailpassword'))->withInput();
	}
}