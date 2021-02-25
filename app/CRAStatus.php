<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CRAStatus extends Model {

	protected $table = 'cra_status';

	protected $fillable = [
		'id',
		'user_id',
		'date',
		'created_at',
		'updated_at'
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function getMonthAttribute(){
		return date("m-Y", strtotime($this->date));
	}
}