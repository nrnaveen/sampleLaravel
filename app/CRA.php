<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CRA extends Model {

	protected $table = 'cra';

	protected $fillable = [
		'id',
		'user_id',
		'mission_id',
		'status',
		'validation',
		'start',
		'end',
		'startHalf',
		'endHalf',
		'days',
		'comments',
		'broadcast_date',
		'created_at',
		'updated_at'
	];

	public static $months = [
		"Janvier",
		"Février",
		"Mars",
		"Avril",
		"Mai",
		"Juin",
		"Juillet",
		"Août",
		"Septembre",
		"Octobre",
		"Novembre",
		"Décembre",
	];

	public static $days = [
		"Dimanche",
		"Lundi",
		"Mardi",
		"Mercredi",
		"Jeudi",
		"Vendredi",
		"Samedi",
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function mission(){
		return $this->belongsTo('App\Mission', 'mission_id', 'id');
	}

	public static function getCRA($user_id, $month){
		$timestamp = Carbon::createFromFormat('d-m-Y', '05-' . $month);
		$startDate = $timestamp->format("Y-m-01");
		$endDate = $timestamp->format("Y-m-t");
		$result = \App\CRA::whereRaw('user_id = ? AND (start between ? and ? OR end between ? and ?)', [$user_id, $startDate, $endDate, $startDate, $endDate])->orderBy('start', 'ASC')->get();
		$data = [];
		foreach($result as $key => $value){
			$value->days = getDaysCount($value->start, $value->end, $value->startHalf, $value->endHalf);
			$value->broadcast = date("Y-m-d", strtotime($value->broadcast_date));
			$value->mission->name = $value->mission->label;
			$value->mission->color = $value->mission->client->color;
			if(isset($data[$value->mission_id])){
				$data[$value->mission_id]['days'] = $data[$value->mission_id]['days'] + $value->days;
				$data[$value->mission_id]['events'][] = $value;
			}else{
				$data[$value->mission_id] = ['code' => $value->mission->code, 'name' => $value->mission->label, 'days' => $value->days, 'color' => $value->mission->client->color, 'events' => [$value]];
			}
		}
		$broadcast = date("Y-m-d");
		if($result->count() > 0){
			$cra = $result->sortBy('broadcast_date')->last();
			$broadcast = date("Y-m-d", strtotime($cra->broadcast_date));
		}
		if(count($data) > 0){
			$data = collect($data)->sortByDesc('days');
			$data = array_values($data->toArray());
		}
		$cra_status = \App\CRAStatus::whereRaw("MONTH(`date`) = ? AND YEAR(`date`) = ? AND `user_id` = ?", [$timestamp->format("m"), $timestamp->format("Y"), $user_id])->orderBy('id', 'DESC')->first();
		return [
			'broadcast' => $broadcast,
			'craData' => $result,
			'missionData' => $data,
			'cra_status' => $cra_status,
			'isCraEntered' => ($result->count() > 0) ? true : false,
		];
	}

	public static function boot(){
		parent::boot();
		/*self::retrieved(function($cra){
			$cra->days = getDaysCount($cra->start, $cra->end, $cra->startHalf, $cra->endHalf);
		});*/
	}
}