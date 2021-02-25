<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function(Request $request){
	return $request->user();
});

// Before authentication
Route::group(['prefix' => '/', 'middleware' => 'guest:api', "namespace" => "Api"], function(){
	Route::post('/postRegister', 'AuthController@register');
	Route::post('/socialLogin', 'AuthController@postSocial');
	Route::post('/postLogin', 'AuthController@authenticate');
	Route::post('/postForgotPass', 'AuthController@forgotPassword');
	Route::post('/postVerifyToken', 'AuthController@postVerifyToken');
	Route::post('/restPassword', 'AuthController@postResetPassword');
});

// Vision Manager
Route::group(['prefix' => '/vision', 'middleware' => ['auth:api', 'checkrole:manager'], "namespace" => "Api"], function(){
	Route::get('/', 'UserController@getProfile');
	Route::get('/requests', 'VisionController@getIndex');
	Route::post('/requests', 'VisionController@postIndex');
	Route::put('/requests', 'VisionController@putIndex');
});

// After authentication
Route::group(['prefix' => "/", 'middleware' => 'auth:api', "namespace" => "Api"], function(){
	Route::get('/', 'UserController@getProfile');
	Route::get('/home', 'UserController@getHome');
	Route::get('users', 'UserController@getAll');
	Route::get('clients', 'UserController@getClients');
	Route::get('missions', 'UserController@getMissions');
	Route::get('my-consultants', 'UserController@getConsultants');
	Route::get('/profile', 'UserController@getProfile');
	Route::post('/profile', 'UserController@postProfile');
	Route::post('/changePassword', 'UserController@changePass');
	Route::post('/update-device', 'UserController@postUpdateDevice');
	Route::get('/logout', 'UserController@logout');

	// Absence roots
	Route::group(['prefix' => "absences"], function(){
		Route::get('/', 'AbsencesController@getIndex');
		Route::get('getLatest', 'AbsencesController@getData');
		Route::post('add', 'AbsencesController@postAbsences');
		Route::get('{id}', 'AbsencesController@getAbsence');
		Route::post('{id}', 'AbsencesController@postEditAbsence');
		Route::delete('{id}', 'AbsencesController@DeleteAbsence');
		Route::get('total/{id}/{month}', 'AbsencesController@getClientAbsences');
		Route::get('by-type/{id}/{month}', 'AbsencesController@getClientAbsencesByType');
	});

	// CRA roots
	Route::group(['prefix' => "cra"], function(){
		Route::get('/', 'CRAController@getIndex');
		Route::get('generate/{client_id}', 'CRAController@getGenerateCra');
		Route::post('add', 'CRAController@postCras');
		Route::post('add-validation', 'CRAController@postCrasWithValidation');
		Route::post('export-cra', 'CRAController@postExportCRA');
		Route::get('getCra/{id}/{month}', 'CRAController@getCra');
		Route::post('updateCra/{id}', 'CRAController@postCra');
		Route::put('update', 'CRAController@putCras');
		Route::delete('{id}', 'CRAController@DeleteCRA');
		Route::get('getLast', 'CRAController@getLast');
	});

	// Penalty rootes
	Route::group(['prefix' => "penalty"], function(){
		Route::get('/', 'PenaltyController@getIndex');
		Route::post('add', 'PenaltyController@createPenalty');
		Route::get('getPenalty/{id}', 'PenaltyController@getPenalty');
		Route::post('update/{id}', 'PenaltyController@updatePenalty');
		Route::delete('{id}', 'PenaltyController@DeletePenalty');
	});

	// contact rootes
	Route::group(['prefix' => "contact"], function(){
		// Route::get('/', 'ContactController@getAll');
		Route::post('create', 'ContactController@postNewContact');
		// Route::delete('/{id}', 'ContactController@deleteContact');
	});
});