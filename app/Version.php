<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model {

	protected $table = 'version';

	protected $fillable = [
		'id',
		'version',
		'status',
		'created_at',
		'updated_at'
	];
}