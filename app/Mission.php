<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Mission extends Model {

	protected $table = 'mission';

	protected $fillable = [
		'id',
		'code',
		'label',
		'order',
		'status',
		'activity_type',
		'commercial',
		'client_id',
		'created_at',
		'updated_at',
	];

	protected static $activity_types = [
		'contract' => "Régie",
		'fixed_price' => "Forfait Activité",
	];

	public function client(){
		return $this->belongsTo('App\Clients', 'client_id', 'id');
	}

	public function cra(){
		return $this->hasMany('App\CRA', 'mission_id', 'id');
	}

	public static function getActivityTypes(){
		return self::$activity_types;
	}

	public function getActivityTypeStrAttribute(){
		return self::$activity_types[$this->activity_type];
	}
}