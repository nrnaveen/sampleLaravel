<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model {

	protected $table = 'contact';

	protected $fillable = [ // fillable details
		'id',
		'subject',
		'description',
		'user_id',
		'attachement_1',
		'attachement_2',
		'created_at',
		'updated_at'
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}
}