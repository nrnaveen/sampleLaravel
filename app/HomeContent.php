<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeContent extends Model {
	
	protected $table = 'home_content';

	protected $fillable = [
		'id',
		'content',
		'created_at',
		'updated_at'
	];
}