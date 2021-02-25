<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {

	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id',
		'firstname',
		'lastname',
		'role',
		'email',
		'password',
		'status',
		'deviceId',
		'registrationId',
		'deviceType',
		'mobile',
		'address',
		'creation_date',
		'image',
		'api_token',
		'socialId',
		'color',
		'remember_token',
		'created_at',
		'updated_at',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
		'api_token',
		'deviceId',
		'registrationId',
		'deviceType',
	];

	public function getNameAttribute(){
		return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
	}

	public function getRoleTextAttribute(){ return ucfirst($this->role); }

	public function absences(){
		return $this->hasMany('App\Absences', 'user_id', 'id');
	}

	public function cra(){
		return $this->hasMany('App\CRA', 'user_id', 'id');
	}

	public static function boot(){
		parent::boot();
		self::deleting(function($user){
			\App\Token::where('user_id', $user->id)->delete();
			\App\Absences::where('user_id', $user->id)->delete();
			\App\ActivityLog::where('user_id', $user->id)->delete();
			\App\Contact::where('user_id', $user->id)->delete();
			\App\CRA::where('user_id', $user->id)->delete();
			\App\Penalty::where('user_id', $user->id)->delete();
			\App\UserMission::where('user_id', $user->id)->delete();
			\App\UserManager::whereRaw('manager_id = ? OR consultant_id = ?', [$user->id, $user->id])->delete();
		});
	}
}