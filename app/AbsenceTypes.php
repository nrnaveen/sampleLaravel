<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Cviebrock\EloquentSluggable\Sluggable;
use Cache;

class AbsenceTypes extends Model {

	use Sluggable;

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable(){
		return [
			'slug' => [
				'source' => 'label'
			]
		];
	}

	protected $table = 'absence_types';

	protected $fillable = [
		'id',
		'slug',
		'label',
		'status',
		'auto_approve',
		'color',
		'created_at',
		'updated_at',
	];

	static protected $default_types = ['paid_vacation', 'family_holiday', 'unpaid_leave', 'rtt', 'disease'];

	public static function getDefaultTypes(){
		return self::$default_types;
	}

	public static function getReasons(){
		$reasons = collect();
		if(!Cache::has('reasons')){
			$reasons = AbsenceTypes::where('status', true)->get();
			Cache::put('reasons', $reasons, 30);
		}
		$reasons = Cache::get('reasons');
		return $reasons;
	}

	public static function getReasonLabels(){
		$reasons = self::getReasons();
		return $reasons->pluck('label', 'slug');
	}

	public static function getReasonColors(){
		$reasons = self::getReasons();
		return $reasons->pluck('color', 'slug');
	}
}