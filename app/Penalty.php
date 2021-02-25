<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penalty extends Model {

	protected $table = 'penalty';

	protected $fillable = [
		'id',
		'user_id',
		'mission_id',
		'beginning',
		'ending',
		'total_duration',
		'type',
		'at_home',
		'comments',
		'client_informed',
		'created_at',
		'updated_at',
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function mission(){
		return $this->belongsTo('App\Mission', 'mission_id', 'id');
	}
}