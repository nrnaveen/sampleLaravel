<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Token extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'token';

	protected $fillable = [
		'id',
		'user_id',
		'token',
		'created_at',
		'updated_at',
	];

	/*
	 * The attribute included the model's JSON.
	 * get First name and Last name together
	 */
	public function isExpired(){
		$dtToronto = Carbon::now();
		$dtVancouver = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at);
		$interval = $dtVancouver->diffInHours($dtToronto);
		return ($interval < 24);
	}
}