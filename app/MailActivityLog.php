<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailActivityLog extends Model
{
    protected $table = 'mail_activity_log';

	protected $fillable = [
		'id',
		'user_id',
		'object',
		'action',
		'data',
		'created_at',
		'updated_at',
	];
}
