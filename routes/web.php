<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function(){
	return view('index');
});

Route::post('amazon-sns/notifications', 'AmazonController@handleBounceOrComplaint');

/*Route::get('/google', 'GoogleController@getIndex');
Route::get('/google-ref', 'GoogleController@getRefresh');
Route::get('/google-users', 'GoogleController@getUsers');*/

Route::get('event-notify', 'EventNotifyController@getIndex')->middleware(['ipcheck']);

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'cron'], function(){
	Route::get('/send-cra', 'CronController@sendCRAMail');
	Route::get('/test-mail', 'CronController@testMail');
});

Route::group(['middleware' => 'guest:admins', 'prefix' => 'admin', 'namespace' => 'Admin'], function(){
	Route::get('login', 'AuthController@showLogin');
	Route::post('login', 'AuthController@authenticate');
});

Route::group(['middleware' => 'admins', 'prefix' => 'admin', 'namespace' => 'Admin'], function(){
	// Basic Routes
	Route::group(['prefix' => "/"], function(){
		Route::get('/', 'AdminController@index');
		Route::get('profile', 'AdminController@getProfile');
		Route::post('updateprofile', 'AdminController@postProfile');
		Route::get('logout', 'AdminController@logout');
		Route::get('site-info', 'AdminController@getSiteInfo');
		Route::post('site-info', 'AdminController@postSiteInfo');
		Route::post('site-info/update-email', 'AdminController@postAdminEmail');
	});

	// Home Content
	Route::group(['prefix' => "/"], function(){
		Route::get('homecontent', 'ContentController@getIndex');
		Route::post('homecontent', 'ContentController@postIndex');
	});

	// Emails Routes
	Route::group(['prefix' => "/emails"], function(){
		Route::get('/', 'EmailsController@getIndex');
		Route::get('add', 'EmailsController@getNew');
		Route::post('add', 'EmailsController@postNew');
		Route::delete('delete/{id}', 'EmailsController@deleteEmail');
		Route::get('edit/{id}', 'EmailsController@getEdit');
		Route::post('edit/{id}', 'EmailsController@postEdit');
	});
	// User Routes
	Route::group(['prefix' => "/users"], function(){
		Route::get('/', 'UsersController@index');
		Route::get('add', 'UsersController@newUser');
		Route::post('add', 'UsersController@postNewUser');
		Route::delete('delete/{id}', 'UsersController@deleteUser');
		Route::post('disable/{id}', 'UsersController@disableUser');
		Route::get('edit/{id}', 'UsersController@getEdit');
		Route::post('edit/{id}', 'UsersController@postEdit');
		Route::get('{id}/missions', 'UsersController@getUserMissions');
		Route::post('{id}/missions', 'UsersController@postUserMissions');
		Route::get('{user_id}/missions/{mission_id}/delete', 'UsersController@deleteUserMissions');
		Route::get('{id}/consultants', 'UsersController@getUserConsultants');
		Route::post('{id}/consultants', 'UsersController@postUserConsultants');
		Route::get('{user_id}/consultants/{consultant_id}/delete', 'UsersController@deleteUserConsultant');
	});
	// Absence Types Routes
	Route::group(['prefix' => "/absence-types"], function(){
		Route::get('/', 'AbsenceTypesController@index');
		Route::get('add', 'AbsenceTypesController@newType');
		Route::post('add', 'AbsenceTypesController@postNewType');
		Route::delete('delete/{id}', 'AbsenceTypesController@deleteType');
		Route::get('edit/{id}', 'AbsenceTypesController@getEdit');
		Route::post('edit/{id}', 'AbsenceTypesController@postEdit');
	});
	// Absences Routes
	Route::group(['prefix' => "/absences"], function(){
		Route::get('/', 'AbsencesController@index');
		Route::get('add', 'AbsencesController@newAbsence');
		Route::post('add', 'AbsencesController@postAbsence');
		Route::get('edit/{id}', 'AbsencesController@getEdit');
		Route::post('edit/{id}', 'AbsencesController@postEdit');
		Route::delete('delete/{id}', 'AbsencesController@deleteAbsence');
		Route::get('export', 'AbsencesController@excelExport');
	});
	// CRA's Routes
	Route::group(['prefix' => "/cras"], function(){
		Route::get('/', 'CRAController@index');
		Route::get('edit/{id}', 'CRAController@getEdit');
		Route::delete('delete/{id}', 'CRAController@deleteCra');
		Route::get('export', 'CRAController@excelExport');
	});
	// Penalty Routes
	Route::group(['prefix' => "/penalties"], function(){
		Route::get('/', 'PenaltyController@index');
		Route::get('edit/{id}', 'PenaltyController@getEdit');
		Route::post('edit/{id}', 'PenaltyController@postEdit');
		Route::delete('delete/{id}', 'PenaltyController@deletePenalty');
		Route::get('export', 'PenaltyController@excelExport');
	});
	// Clients Routes
	Route::group(['prefix' => "/clients"], function(){
		Route::get('/', 'ClientsController@index');
		Route::get('add', 'ClientsController@newClient');
		Route::post('add', 'ClientsController@postNewClient');
		Route::delete('delete/{id}', 'ClientsController@deleteClient');
		Route::get('edit/{id}', 'ClientsController@getEdit');
		Route::post('edit/{id}', 'ClientsController@postEdit');
	});
	// Missions Routes
	Route::group(['prefix' => "/missions"], function(){
		Route::get('/', 'MissionController@getIndex');
		Route::get('create', 'MissionController@getCreate');
		Route::post('create', 'MissionController@postCreate');
		Route::delete('delete/{id}', 'MissionController@deleteMission');
		Route::get('edit/{id}', 'MissionController@getEdit');
		Route::post('edit/{id}', 'MissionController@postEdit');
	});
	// Missions Routes
	Route::group(['prefix' => "/admins"], function(){
		Route::get('/', 'AdminsController@getIndex');
		Route::get('create', 'AdminsController@getCreate');
		Route::post('create', 'AdminsController@postCreate');
		Route::delete('delete/{id}', 'AdminsController@deleteAdmin');
		Route::get('edit/{id}', 'AdminsController@getEdit');
		Route::post('edit/{id}', 'AdminsController@postEdit');
	});
	// Events Routes
	Route::group(['prefix' => "/events"], function(){
		Route::get('/', 'EventsController@index');
		Route::get('add', 'EventsController@newEvent');
		Route::post('add', 'EventsController@postEvent');
		Route::get('edit/{id}', 'EventsController@getEdit');
		Route::post('edit/{id}', 'EventsController@postEdit');
		Route::delete('delete/{id}', 'EventsController@deleteEvent');
	});

	// Contact Routes
	Route::group(['prefix' => "/contact"], function(){
		Route::get('/', 'ContactController@getIndex');
		Route::delete('delete/{id}', 'ContactController@deleteContact');
	});

	// Version Routes
	Route::group(['prefix' => "/version"], function(){
		Route::get('/', 'VersionController@getIndex');
		Route::post('/', 'VersionController@postIndex');
		Route::get('{version_id}/delete', 'VersionController@deleteIndex');
	});

	// Mails Routes
	Route::group(['prefix' => "/deleted-mails"], function(){
		Route::get('/', 'MailsController@getIndex');
		Route::post('/cache', 'MailsController@postClearCache');
	});

	// Not Found
	Route::any('{query}', function(){
		return View('admin.errors.404', ['title' => trans('messages.NotFound')]);
	})->where('query', '.*');
});

/*-- Front section Authentication --*/
Route::group(['middleware' => 'guest', 'prefix' => '/' ], function(){
	Route::get('/login', 'Auth\LoginController@getLogin');
});