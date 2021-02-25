<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole {

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, $role){
		if(\Auth::guard("api")->user()->role != $role){ return responseJson(["error" => trans('messages.YouAreNotAllowed')], 404); }
		return $next($request);
	}
}