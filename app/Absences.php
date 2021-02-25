<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTime, Config;

class Absences extends Model {

	static protected $reasons = [];

	protected $table = 'absences';

	protected $fillable = [
		'id',
		'user_id',
		'status',
		'start',
		'end',
		'startHalf',
		'endHalf',
		'days',
		'reason',
		'cancel_reason',
		'accepted_date',
		'cancelled_date',
		'deleted_date',
		'client_informed',
		'archive',
		'self',
		'created_at',
		'updated_at',
	];

	public function user(){
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public static function getClientCras($id, $month){
		$leave_dates = Config::get('leave_dates');
		$timestamp = Carbon::createFromFormat('d-m-Y', '05-' . $month);
		$startDate = $timestamp->format("Y-m-01");
		$endDate = $timestamp->format("Y-m-t");
		$result = \App\Absences::whereRaw('user_id = ? AND (start between ? and ? OR end between ? and ?)', [$id, $startDate, $endDate, $startDate, $endDate])->whereIn('status', ['pending', 'approved'])->get();
		$absenceTypes = [];
		$diseaseLeaves = 0;
		$paidLeaves = 0;
		$holidays = 0;
		$unpaidLeave = 0;
		$rttLeaves = 0;
		$reasons = AbsenceTypes::all();
		foreach($result as $data){
			$start_date = Carbon::parse($data['start']);
			$end_date = Carbon::parse($data['end']);
			$start = $start_date->format('m-Y');
			$end = $end_date->format('m-Y');
			$startDate = $start_date->format('Y-m-d');
			$data->days = getDaysCount($data->start, $data->end, $data->startHalf, $data->endHalf);
			if($start == $end){
				$endDate = $end_date->format('Y-m-d');
				$weekendCounts = 0;
				$start = new DateTime($startDate);
				$end = new DateTime($endDate);
				$days = $start->diff($end, true)->days;
				$sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
				$saturdays = intval($days / 6) + ($start->format('N') + $days % 6 >= 7);
				$weekendCounts = ($sundays + $saturdays);
				$total = $end_date->diffInDays($start_date) + 1;
				$total = $total - $weekendCounts;
				$daterange = new \DatePeriod(new \DateTime($startDate), new \DateInterval('P1D'), (new \DateTime($endDate))->modify('+1 day'));
				foreach($daterange as $date){
					if(in_array($date->format('d/m/Y'), $leave_dates)){
						$total = $total - 1;
					}
				}
				if($data['startHalf'] == 1){ $total = $total - 0.5; }
				if($data['endHalf'] == 1){ $total = $total - 0.5; }
				$reason = $reasons->filter(function($item) use($data){ return ($item->slug == $data['reason']); })->first();
				if(isset($absenceTypes[$data['reason']])){
					$absenceTypes[$data['reason']]['days'] = $absenceTypes[$data['reason']]['days'] + $diseaseLeaves + $total;
					$absenceTypes[$data['reason']]['events'][] = [ 'start' => $startDate, 'end' => $endDate,
						'startHalf' => $data['startHalf'], 'endHalf' => $data['endHalf'], 'color' => $data->user->color,
					];
				}else{
					$color = '#9e9e9e';
					$absence_name = $data['reason'];
					if($reason){
						$color = $reason->color;
						$absence_name = $reason->label;
					}
					$absenceTypes[$data['reason']] = ['absence_name' => $absence_name, 'absence_type' => $data['reason'], 'days' => $diseaseLeaves + $total, 'color' => $color];
					$absenceTypes[$data['reason']]['events'][] = ['start' => $startDate, 'end' => $endDate,
						'startHalf' => $data['startHalf'], 'endHalf' => $data['endHalf'], 'color' => $data->user->color,
					];
				}
			}elseif($end == $month){
				$startDate = Carbon::parse($data['end'])->startOfMonth();
				$endDate = Carbon::parse($data['end']);
				$weekendCounts = 0;
				$start = new DateTime($startDate);
				$end = new DateTime($endDate);
				$days = $start->diff($end, true)->days;
				$sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
				$saturdays = intval($days / 6) + ($start->format('N') + $days % 6 >= 7);
				$weekendCounts = ($sundays + $saturdays);
				$total = $startDate->diffInDays($end_date) + 1;
				$total = $total - $weekendCounts;
				$daterange = new \DatePeriod(new \DateTime($startDate), new \DateInterval('P1D'), (new \DateTime($endDate))->modify('+1 day'));
				foreach($daterange as $date){
					if(in_array($date->format('d/m/Y'), $leave_dates)){
						$total = $total - 1;
					}
				}
				if($data['endHalf'] == 1){ $total = $total - 0.5; }
				$reason = $reasons->filter(function($item) use($data){ return ($item->slug == $data['reason']); })->first();
				if(isset($absenceTypes[$data['reason']])){
					$absenceTypes[$data['reason']]['days'] = $absenceTypes[$data['reason']]['days'] + $diseaseLeaves + $total;
					$absenceTypes[$data['reason']]['events'][] = [ 'start' => $startDate, 'end' => $endDate,
						'startHalf' => $data['startHalf'], 'endHalf' => $data['endHalf'], 'color' => $data->user->color,
					];
				}else{
					$color = '#9e9e9e';
					$absence_name = $data['reason'];
					if($reason){
						$color = $reason->color;
						$absence_name = $reason->label;
					}
					$absenceTypes[$data['reason']] = ['absence_name' => $absence_name, 'absence_type' => $data['reason'], 'days' => $diseaseLeaves + $total, 'color' => $color];
					$absenceTypes[$data['reason']]['events'][] = ['start' => $startDate, 'end' => $endDate,
						'startHalf' => $data['startHalf'], 'endHalf' => $data['endHalf'], 'color' => $data->user->color,
					];
				}
			}else{
				$endDate = Carbon::parse($data['start'])->endOfMonth();
				$weekendCounts = 0;
				$start = new DateTime($startDate);
				$end = new DateTime($endDate);
				$days = $start->diff($end, true)->days;
				$sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
				$saturdays = intval($days / 6) + ($start->format('N') + $days % 6 >= 7);
				$weekendCounts = ($sundays + $saturdays);
				$total = $endDate->diffInDays($start_date) + 1;
				$total = $total - $weekendCounts;
				$daterange = new \DatePeriod(new \DateTime($startDate), new \DateInterval('P1D'), (new \DateTime($endDate))->modify('+1 day'));
				foreach($daterange as $date){
					if(in_array($date->format('d/m/Y'), $leave_dates)){
						$total = $total - 1;
					}
				}
				if($data['startHalf'] == 1){ $total = $total - 0.5; }
				$reason = $reasons->filter(function($item) use($data){ return ($item->slug == $data['reason']); })->first();
				if(isset($absenceTypes[$data['reason']])){
					$absenceTypes[$data['reason']]['days'] = $absenceTypes[$data['reason']]['days'] + $diseaseLeaves + $total;
					$absenceTypes[$data['reason']]['events'][] = [ 'start' => $startDate, 'end' => $endDate->format("Y-m-d"),
						'startHalf' => $data['startHalf'], 'endHalf' => 0, 'color' => $data->user->color,
					];
				}else{
					$color = '#9e9e9e';
					$absence_name = $data['reason'];
					if($reason){
						$color = $reason->color;
						$absence_name = $reason->label;
					}
					$absenceTypes[$data['reason']] = ['absence_name' => $absence_name, 'absence_type' => $data['reason'], 'days' => $diseaseLeaves + $total, 'color' => $color];
					$absenceTypes[$data['reason']]['events'][] = [ 'start' => $startDate, 'end' => $endDate->format("Y-m-d"),
						'startHalf' => $data['startHalf'], 'endHalf' => 0, 'color' => $data->user->color,
					];
				}
			}
		}
		return ['absenceTypes' => $absenceTypes, 'absence' => $result,];
	}

	public function getReasonStrAttribute(){
		self::$reasons = \App\AbsenceTypes::getReasonLabels()->toArray();
		if(isset(self::$reasons[$this->reason])){
			return self::$reasons[$this->reason];
		}
		return $this->reason;
	}

	public static function getReasons(){
		self::$reasons = \App\AbsenceTypes::getReasonLabels()->toArray();
		return self::$reasons;
	}
}