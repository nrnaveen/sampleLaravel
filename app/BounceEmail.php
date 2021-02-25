<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BounceEmail extends Model {

	protected $table = 'bounce_emails';

	protected $fillable = [
		'id',
		'email',
		'type',
		'problem',
		'created_at',
		'updated_at'
	];
}