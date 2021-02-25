<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use File;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(){
		Schema::defaultStringLength(191);
		if(!File::isDirectory("uploads")){ File::makeDirectory("uploads", 0775); }
		if(!File::isDirectory("uploads/image")){ File::makeDirectory("uploads/image", 0775); }
		if(!File::isDirectory("uploads/image/thumbnail")){ File::makeDirectory("uploads/image/thumbnail", 0775); }
		setlocale(LC_TIME, 'fr_FR.utf8', 'fr_FR');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, HEAD, OPTIONS');
		header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With, X-Auth-Token,x-xsrf-token');
		header('Access-Control-Allow-Credentials: true');
		Validator::extend('base64', function($attribute, $value, $parameters, $validator){
			if(preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $value)){
				return true;
			}else{
				return false;
			}
		});
		Validator::extend('base64image', function($attribute, $value, $parameters, $validator){
			$explode = explode(',', $value);
			$allow = ['png', 'jpg', 'svg'];
			$format = str_replace([
				'data:image/',
				';',
				'base64',
			], [
				'', '', '',
			], $explode[0]);
			// check file format
			if(!in_array($format, $allow)){
				return false;
			}
			// check base64 format
			if(!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1])){
				return false;
			}
			return true;
		});
		view()->composer('*', function($view){
			$data = [
				'admin' => \Auth::guard('admins')->user(),
				'guestAdmin' => \Auth::guard('admins')->guest(),
			];
			$view->with($data);
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register(){
		//
	}
}