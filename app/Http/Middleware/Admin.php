<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin {

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @param string|null $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'admins'){
		if(Auth::guard($guard)->guest()){
			if($request->ajax() || $request->wantsJson()){ return responseJson(["error" => trans('messages.Unauthorized')], 401); }
			return redirect()->guest('/admin/login');
		}
		return $next($request);
	}
}