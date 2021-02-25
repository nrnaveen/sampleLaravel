<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserManager extends Model {

	protected $table = 'user_manager';

	protected $fillable = [
		'id',
		'manager_id',
		'consultant_id',
		'created_at',
		'updated_at'
	];

	public function manager(){
		return $this->belongsTo('App\User', 'manager_id', 'id');
	}

	public function consultant(){
		return $this->belongsTo('App\User', 'consultant_id', 'id');
	}
}