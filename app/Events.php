<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Events extends Model {

	protected $table = 'events';

	protected $fillable = [
		'id',
		'label',
		'date',
		'description',
		'status',
		'created_at',
		'updated_at',
	];
}