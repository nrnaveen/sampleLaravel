<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteInfo extends Model {

	protected $table = 'siteinfo';

	protected $fillable = [
		'id',
		'request_count',
		'created_at',
		'updated_at',
	];
}