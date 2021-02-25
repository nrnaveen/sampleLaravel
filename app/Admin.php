<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable {

	use Notifiable;

	protected $guards = 'admins';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id',
		'firstname',
		'lastname',
		'email',
		'password',
		'remember_token',
		'created_at',
		'updated_at'
	]; // fillable details

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token']; // Hidden fileds

	/*
	 * The attribute included the model's JSON.
	 * get First name and Last name together
	 */
	public function getNameAttribute(){
		return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
	}
}