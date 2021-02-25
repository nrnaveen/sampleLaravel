<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {
	
	protected $table = 'activity_log';

	protected $fillable = [
		'id',
		'user_id',
		'object',
		'action',
		'data',
		'created_at',
		'updated_at',
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}
}