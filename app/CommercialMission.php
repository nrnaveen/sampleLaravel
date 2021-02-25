<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommercialMission extends Model {

	protected $table = 'mission_commercial';

	protected $fillable = [
		'id',
		'user_id',
		'mission_id',
		'created_at',
		'updated_at'
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function mission(){
		return $this->belongsTo('App\Mission', 'mission_id', 'id');
	}
}