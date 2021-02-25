<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Mail, Config, DbView;
use Carbon\Carbon;

class CRAController extends Controller {

	protected $rules = [
		'mission_id'	=> "required|integer",
		'days'			=> "required",
		'start'			=> "required|date_format:Y-m-d",
		'end'			=> "required|date_format:Y-m-d|after_or_equal:start",
		// 'broadcast_date'=> "required|date_format:Y-m-d H:i:s|after_or_equal:today",
		'broadcast_date'=> "required|date_format:Y-m-d H:i:s",
		'startHalf'		=> "required|boolean",
		'endHalf'		=> "required|boolean",
	];

	public function getIndex(){
		try{
			$user = \Auth::guard("api")->user();
			$craData = \App\CRA::where('user_id', $user->id)->orderBy('start', 'ASC')->get();
			if($craData->count() > 0) return responseJson(['craData' => $craData], 200);
			else return responseJson(['error' => trans('messages.NoDataAvailable')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function getGenerateCra(Request $request, $id){
		try{
			$validsubmit = false;
			if($request->has('validsubmit')){ $validsubmit = true; }
			$user = \Auth::guard("api")->user();
			$mission = \App\Mission::whereRaw('id = ? AND status = ?', [$id, true])->first();
			if(!$mission){ return responseJson(['error' => trans('messages.MissionNotFound'), ], 400); }
			$startDate = date("Y-m-01");
			$endDate = date("Y-m-t");
			$start_half = getStartOREnd($request, 'start_half');
			$end_half = getStartOREnd($request, 'end_half');
			$oldData = null;
			if($request->has('oldData') && !empty($request->get('oldData'))){ $oldData = json_decode($request->get('oldData'), true); }
			if($request->has('start_date') && $request->has('end_date')){
				$data = $request->only(['start_date', 'end_date']);
				$validator = Validator::make($data, ['start_date' => "required|date_format:Y-m-d", 'end_date' => "required|date_format:Y-m-d|after_or_equal:start_date"]);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
				list($startDate, $endDate) = [$data['start_date'], $data['end_date']];
			}
			if($startDate == $endDate && $start_half && $end_half){ return responseJson(['error' => trans('messages.PleaseEntervaliddata'), ], 400); }
			$dates = [];
			$start_date = Carbon::createFromFormat('Y-m-d', $startDate);
			$end_date = Carbon::createFromFormat('Y-m-d', $endDate);
			$daterange = new \DatePeriod(new \DateTime($startDate), new \DateInterval('P1D'), (new \DateTime($endDate))->modify('+1 day'));
			foreach($daterange as $date){
				$startDay = $date->format('N');
				if(($startDay != 7 && $startDay != 6)){ $dates[] = $date->format('Y-m-d'); }
			}
			$absences = \App\Absences::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $endDate, $startDate])->whereIn('status', ['pending', 'approved'])->orderBy('start', 'ASC')->get();
			$cras = \App\CRA::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $endDate, $startDate])->orderBy('start', 'ASC')->get();
			$craIdstoRemove = [];
			if(getStartOREnd($request, 'delete_exists')){
				$cras = \App\CRA::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $endDate, $startDate])->orderBy('start', 'ASC')->get();
				$craIdstoRemove = $cras->pluck('id');
				$originalCraCount = $cras->count();
				if(!$start_half && !$end_half){
					foreach($cras as $exist_cra_key => $exist_cra){
						if($exist_cra->start >= $startDate && $exist_cra->end <= $endDate){ $cras->forget($exist_cra_key); }
					}
					if($cras->count() > 0){
						$crasCount = $cras->count();
						foreach($cras as $exist_cra_key => $exist_cra){
							if($exist_cra->start >= $startDate && $exist_cra->start <= $endDate){
								$exist_cra->start = date("Y-m-d", strtotime($endDate . " +1 day"));
								$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
							}
							if($exist_cra->end >= $startDate && $exist_cra->end <= $endDate){
								$exist_cra->end = date("Y-m-d", strtotime($startDate . " -1 day"));
								$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
							}
							if($exist_cra->start < $startDate && $exist_cra->end > $endDate){
								$exist_cra_new = $exist_cra->replicate();
								$exist_cra->end = date("Y-m-d", strtotime($startDate . " -1 day"));
								$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
								$exist_cra_new->start = date("Y-m-d", strtotime($endDate . " +1 day"));
								$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
								$cras->push($exist_cra_new);
							}
						}
					}
				}else{
					foreach($cras as $exist_cra_key => $exist_cra){
						if($exist_cra->start >= $startDate && $exist_cra->end <= $endDate){
							if($exist_cra_key != 0 && $originalCraCount != $exist_cra_key + 1){ $cras->forget($exist_cra_key); }
						}
					}
					if($cras->count() > 0){
						$crasCount = $cras->count();
						foreach($cras as $exist_cra_key => $exist_cra){
							if($originalCraCount > 1){
								if($exist_cra->start < $startDate && $exist_cra->end > $endDate){
									if($start_half && $end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra->endHalf = $end_half;
										$exist_cra_new->startHalf = $start_half;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}else if(!$start_half && $end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra_new->startHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate . ' -1 day'));
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}else if($start_half && !$end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra->endHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate . ' +1 day'));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}
								}
								if($startDate >= $exist_cra->start && $endDate > $exist_cra->end){
									if($start_half && $end_half){
										$exist_cra->endHalf = $end_half;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									}else if(!$start_half && $end_half){
										$exist_cra->end = date("Y-m-d", strtotime($startDate . ' -1 day'));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									}else if($start_half && !$end_half){
										$exist_cra->endHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									}
								}
								if($endDate <= $exist_cra->end && $startDate < $exist_cra->start){
									if($start_half && $end_half){
										$exist_cra->startHalf = $start_half;
										$exist_cra->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									}else if(!$start_half && $end_half){
										$exist_cra->startHalf = true;
										$exist_cra->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									}else if($start_half && !$end_half){
										$exist_cra->start = date("Y-m-d", strtotime($endDate . ' +1 day'));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									}
								}
							}else{
								if($exist_cra->start >= $startDate && $exist_cra->start <= $endDate || $exist_cra->end >= $startDate && $exist_cra->end <= $endDate){
									if($start_half && $end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra->endHalf = $end_half;
										$exist_cra_new->startHalf = $start_half;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}else if(!$start_half && $end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra_new->startHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate . ' -1 day'));
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}else if($start_half && !$end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra->endHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate . ' +1 day'));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}
								}
								if($exist_cra->start < $startDate && $exist_cra->end > $endDate){
									if($start_half && $end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra->endHalf = $end_half;
										$exist_cra_new->startHalf = $start_half;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}else if(!$start_half && $end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra_new->startHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate . ' -1 day'));
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}else if($start_half && !$end_half){
										$exist_cra_new = $exist_cra->replicate();
										$exist_cra->endHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate . ' +1 day'));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$cras->push($exist_cra_new);
									}
								}
							}
						}
						if($start_half){ $start_half = false; }
						if($end_half){ $end_half = false; }
					}
				}
			}
			if($oldData){ foreach($oldData as $key => $value){ $cras->push(new \App\CRA($value)); } }
			foreach([$cras, $absences] as $k1 => $value){
				foreach($value as $key => $absence){
					$absenceStart_date = Carbon::createFromFormat('Y-m-d', $absence->start);
					$absenceEnd_date = Carbon::createFromFormat('Y-m-d', $absence->end);
					for($date = $absenceStart_date; $date->lte($absenceEnd_date); $date->addDay()){
						$absenceDate = $date->format('Y-m-d');
						if(in_array($absenceDate, $dates)){
							$key = array_search($absenceDate, $dates);
							if(isset($dates[$key])){ unset($dates[$key]); }
						}
					}
				}
			}
			$leave_dates = Config::get('leave_dates');
			foreach($leave_dates as $key => $leave_date){
				$leave_date = Carbon::createFromFormat('d/m/Y', $leave_date)->format("Y-m-d");
				if(in_array($leave_date, $dates)){
					$key = array_search($leave_date, $dates);
					if(isset($dates[$key])){ unset($dates[$key]); }
				}
			}
			$total = count($dates);
			usort($dates, function($a, $b){ return strtotime($a) - strtotime($b); });
			list($out, $last, $dex) = [[], null, 0];
			foreach($dates as $key => $value){
				$current = $value;
				if(is_null($last)){ $out[$dex][] = $value; }
				else if(Carbon::createFromFormat('Y-m-d', $last)->diffInDays(Carbon::createFromFormat('Y-m-d', $current)) <= 1){
					$out[$dex][] = $value;
				}else{
					$dex++;
					$out[$dex][] = $value;
				}
				$last = $current;
			}
			$dates = [];
			foreach($out as $key => $value){
				$absenceStart = Carbon::createFromFormat('Y-m-d', $value[0]);
				$absenceEnd = Carbon::createFromFormat('Y-m-d', $value[count($value) - 1]);
				$event = ['start' => $value[0], 'end' => $value[count($value) - 1], 'days' => ($absenceStart->diffInDays($absenceEnd) + 1), 'startHalf' => false, 'endHalf' => false, 'mission_id' => $id];
				if($key == 0 && $start_half){
					$event['days'] = $event['days'] - 0.5;
					$event['startHalf'] = true;
					$total = $total - 0.5;
				}
				if($key == (count($out) - 1) && $end_half){
					$event['days'] = $event['days'] - 0.5;
					$event['endHalf'] = true;
					$total = $total - 0.5;
				}
				$dates[] = $event;
			}
			$timestamp = generateRandomStr(10);
			$halfDays = [];
			foreach([$cras, $absences] as $k1 => $value){
				foreach($value as $key => $absence){
					$absenceStart_date = Carbon::createFromFormat('Y-m-d', $absence->start);
					$absenceEnd_date = Carbon::createFromFormat('Y-m-d', $absence->end);
					if($absence->startHalf){
						$absenceDate = $absenceStart_date->format('Y-m-d');
						if($absenceStart_date->format('m') == $start_date->format('m')){
							$halfDays[] = ['start' => $absenceDate, 'end' => $absenceDate, 'days' => 0.5, 'startHalf' => false, 'endHalf' => true, 'mission_id' => $id];
						}
					}
					if($absence->endHalf){
						$absenceDate = $absenceEnd_date->format('Y-m-d');
						if($absenceEnd_date->format('m') == $end_date->format('m')){
							$halfDays[] = ['start' => $absenceDate, 'end' => $absenceDate, 'days' => 0.5, 'startHalf' => true, 'endHalf' => false, 'mission_id' => $id];
						}
					}
				}
			}
			foreach($halfDays as $halfDayKey => $halfDay){
				foreach([$cras, $absences] as $k1 => $value){
					$halfAbsences = $value->filter(function($item) use($halfDay){
						return (($item->start <= $halfDay['end']) && ($item->end >= $halfDay['start']));
					});
					if($halfAbsences->count() > 0){
						foreach($halfAbsences as $key => $halfAbsence){
							if(
								($halfAbsence->start == $halfDay['start'] || 
								$halfAbsence->start == $halfDay['end'] || 
								$halfAbsence->end == $halfDay['start'] || 
								$halfAbsence->end == $halfDay['end']) && 
								($halfAbsence->startHalf || $halfAbsence->endHalf) &&
								$halfAbsence->startHalf == $halfDay['startHalf'] && 
								$halfAbsence->endHalf == $halfDay['endHalf']
							){
								unset($halfDays[$halfDayKey]);
							}elseif($validsubmit && $halfAbsence->start == $halfDay['start'] && $halfAbsence->end == $halfDay['end'] && $halfAbsence->startHalf == $halfDay['startHalf'] && $halfAbsence->endHalf == $halfDay['endHalf']){
								unset($halfDays[$halfDayKey]);
							}elseif($validsubmit && $halfAbsence->start <= $halfDay['end'] && $halfAbsence->end >= $halfDay['start'] && ($halfAbsence->startHalf == $halfDay['startHalf'] || $halfAbsence->endHalf == $halfDay['endHalf'])){
								unset($halfDays[$halfDayKey]);
							}
						}
					}
				}
			}
			foreach($halfDays as $halfDayKey => $halfDay){
				$dates[] = $halfDay;
				$total = $total + $halfDay['days'];
			}
			if(count($dates) <= 0){ return responseJson(['error' => trans('messages.NoDataAvailable'), ], 400); }
			$ncras = \App\CRA::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $start_date->format("Y-m-t"), $start_date->format("Y-m-01")])->orderBy('start', 'ASC')->get();
			foreach($ncras as $key => $ncra){
				foreach($dates as $nkey => $ndate){
					if(($ndate['startHalf'] || $ndate['endHalf']) && $ncra->start == $ndate['start'] && $ncra->end == $ndate['end'] && $ncra->startHalf == $ndate['startHalf'] && $ncra->endHalf == $ndate['endHalf']){
						$total = $total - $ndate['days'];
						unset($dates[$nkey]);
					}
				}
			}
			$dates = array_values($dates);
			$userData = getApiUserData($user);
			$mission->name = $mission->label;
			$mission->color = $mission->client->color;
			$missionData = \App\CRA::getCRA($user->id, Carbon::createFromFormat('Y-m-d', $startDate)->format('m-Y'));
			if(getStartOREnd($request, 'delete_exists') && $craIdstoRemove->count() > 0){
				foreach($craIdstoRemove as $key => $fid){
					$missionData['craData'] = $missionData['craData']->keyBy('id');
					$removeCra = $missionData['craData']->get($fid);
					$code = $removeCra->mission->code;
					foreach($missionData['missionData'] as $key => $fmissionData){
						if($fmissionData['code'] == $code){
							$missionData['missionData'][$key]['days'] = $missionData['missionData'][$key]['days'] - $removeCra->days;
						}
					}
					$missionData['craData']->forget($fid);
				}
				foreach($cras as $key => $cra){
					$cra->broadcast = date("Y-m-d", strtotime($cra->broadcast_date));
					$cra->mission->name = $cra->mission->label;
					$cra->mission->color = $cra->mission->client->color;
					$code = $cra->mission->code;
					$isPresent = false;
					foreach($missionData['missionData'] as $key => $fmissionData){
						if($fmissionData['code'] == $code){
							$missionData['missionData'][$key]['days'] = $missionData['missionData'][$key]['days'] + $cra->days;
						}
					}
					$missionData['craData']->push($cra);
				}
			}
			$absence = \App\Absences::getClientCras($user->id, $start_date->format("m-Y"));
			$missionData['absences'] = $absence['absence'];
			$missionData['data'] = $absence['absenceTypes'];
			return responseJson([ 'craData' => [
					'dates' => $dates, 'mission' => $mission, 'total' => $total,
					'timestamp' => $timestamp, 'start' => $startDate, 'end' => $endDate,
				], 'oldData' => $missionData, 'user' => $userData, 'missionData' => $missionData['missionData'], 
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function getLast(){
		try{
			$user = \Auth::guard("api")->user();
			$timestamp = Carbon::now();
			$startDate = $timestamp->format("Y-m-01");
			$endDate = $timestamp->format("Y-m-t");
			$firstEle = \App\CRA::whereRaw('user_id = ? AND validation = ? AND (start between ? and ? OR end between ? and ?)', [$user['id'], true, $startDate, $endDate, $startDate, $endDate])->orderBy('updated_at', 'desc')->first();
			$events = \App\Events::whereRaw('date > ? AND status = ?', [date('Y-m-d'), true])->orderBy('date', 'ASC')->get();
			$secondEle = null;
			if($events->count() > 0){ $secondEle = $events->first(); }
			$thirdEle = null;
			if($events->count() > 1){ $thirdEle = $events->get(1); }
			return responseJson(['firstEle' => $firstEle, 'secondEle' => $secondEle, 'thirdEle' => $thirdEle], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function postCras(Request $request){
		return $this->createCRA($request, false);
	}

	public function postCrasWithValidation(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$usermissions = \App\UserMission::where('user_id', $user->id)->get()->pluck(['mission_id']);
			$mission = \App\Mission::where('status', true)->whereIn('id', $usermissions)->first();
			$currentMonth = Carbon::now();
			if($request->has('currentMonth') && !empty($request->get('currentMonth'))){
				$currentMonth = Carbon::createFromFormat('m-Y', $request->get('currentMonth'));
			}
			$new_req = ['id' => $mission->id, 
				'start_date' => $currentMonth->format('Y-m-01'),
				'end_date' => $currentMonth->format('Y-m-t'),
				'start_half' => 0, 'end_half' => 0, 'oldData' => [],
				'delete_exists' => false,
			];
			if($request->has('generateData') && !empty($request->get('generateData'))){
				$generateData = $request->get('generateData');
				$new_req = ['id' => $generateData['id'],
					'validsubmit' => true,
					'start_date' => $currentMonth->format('Y-m-01'),
					'end_date' => $currentMonth->format('Y-m-t'),
					'start_half' => 0, 'end_half' => 0,
					'oldData' => $generateData['craDatas'],
					'delete_exists' => false,
				];
			}
			$response = $this->getGenerateCra(new Request($new_req), $mission->id);
			if($response->getStatusCode() == 400){ return $this->createCRA($request, true); }
			return responseJson(['error' => trans('messages.cra_incomplete'), ], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage(), ], 400);
		}
	}

	public function postExportCRA(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$timestamp = Carbon::now();
			if($request->has('month')){ $timestamp = Carbon::createFromFormat('d-m-Y', '05-' . $request->get('month')); }
			$startDate = $timestamp->format("Y-m-01");
			$endDate = $timestamp->format("Y-m-t");
			$craData = \App\CRA::whereRaw('user_id = ? AND (start between ? and ? OR end between ? and ?)', [$user->id, $startDate, $endDate, $startDate, $endDate])->orderBy('start', 'ASC')->get();
			/* foreach ($craData as $key1 => $value1) {
				foreach ($craData as $key2 => $value2) {
					//condition check -> user_id, mission_id, start, end, days
					if($key1 != $key2) {
						if ($value1->user_id == $value2->user_id && $value1->mission_id == $value2->mission_id &&  $value1->start == $value2->start && $value1->end == $value2->end && $value1->days == $value2->days && $value1->start_half == $value2->start_half && $value1->end_half == $value2->end_half) {
							unset($craData[$key1]);
						}
					}
				}
			} */

			foreach ($craData as $key1 => $cra1) {
				//remove duplicate entries
				foreach ($craData as $key2 => $cra2) {
					if ($key1 != $key2) {
						if ($cra1['user_id'] == $cra2['user_id'] && $cra1['mission_id'] == $cra2['mission_id'] &&  $cra1['start'] == $cra2['start'] && $cra1['end'] == $cra2['end'] && $cra1['days'] == $cra2['days'] && empty($cra1['startHalf']) == empty($cra2['startHalf']) && empty($cra1['endHalf']) == empty($cra2['endHalf'])) {
								unset($craData[$key1]);
						}
					}
				}
				//remove duplicate entry of dates in between
				list($start, $end) = [Carbon::createFromFormat('Y-m-d', $cra1['start']), Carbon::createFromFormat('Y-m-d', $cra1['end'])];
				if($start->diffInDays($end) > 0) {
					//get dates in between two dates
					$datesRemove = [];
				    for($date = $start; $date->lte($end); $date->addDay()) {
				        $datesRemove[] = $date->format('Y-m-d');
				    }
					foreach ($craData as $key2 => $cra2) {
						foreach ($datesRemove as $date) {
							if ($date == $cra2['start'] && $date == $cra2['end'] && $cra1['user_id'] == $cra2['user_id'] && empty($cra1['startHalf']) == empty($cra2['startHalf']) && empty($cra1['endHalf']) == empty($cra2['endHalf'])) {
								unset($craData[$key2]);
							}
						}
					}
				}
			}			
			$craData = array_values($craData);
			if($craData->count() > 0){
				$fdata = [];
				foreach($craData as $key => $value){
					if(isset($fdata[$value->mission->code])){
						$fdata[$value->mission->code]['days'] += $value->days;
						$fdata[$value->mission->code]['cras'][] = $value;
					}else{
						$fdata[$value->mission->code] = [];
						$fdata[$value->mission->code]['code'] = $value->mission->label;
						$fdata[$value->mission->code]['days'] = $value->days;
						$fdata[$value->mission->code]['cras'] = [];
						$fdata[$value->mission->code]['cras'][] = $value;
					}
				}
				$mail = \App\EmailTemplate::where('email', 'exportcra')->where('status', true)->first();
				$subject = trans('messages.CRAExported');
				$yesText = trans('messages.Yes');
				$noText = trans('messages.No');
				$formatText = '%Y-%m-%d %H:%M:%S';
				if($mail){
					$mails = [];
					$ccmails = [];
					if(!is_null($mail->add_email)){
						$amails = explode(',', $mail->add_email);
						foreach($amails as $key => $amail){
							$amail = trim($amail);
							if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
								if($amail == '{{$user->email}}'){ $mails[] = $user->email; }
							}else{ $mails[] = $amail; }
						}
					}
					if(!is_null($mail->cc_email)){
						$cmails = explode(',', $mail->cc_email);
						foreach($cmails as $key => $cmail){
							$cmail = trim($cmail);
							if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
								if($cmail == '{{$user->email}}'){ $ccmails[] = $user->email; }
							}else{ $ccmails[] = $cmail; }
						}
					}
					$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'craData' => $fdata, 'cras' => $craData, 'user' => $user,])->render();
					$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'craData' => $fdata, 'cras' => $craData, 'user' => $user,])->render();
					Mail::raw($template, function($m) use($user, $subject, $template, $mails, $ccmails){ self::postExportCRAAction($m, $user, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.exportcra', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'craData' => $fdata, 'cras' => $craData, 'user' => $user,], function($m) use($user, $subject){ self::postExportCRAAction($m, $user, $subject); });
				}
			}
			return responseJson(['message' => trans('messages.YourRequestReceived')], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function putCras(Request $request){
		try{
			$user = \Auth::guard("api")->user();
			$validator = Validator::make($request->all(), ['craData' => "required|array"]);
			if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			$craData = $request->get('craData');
			foreach($craData as $cra){
				$this->rules['id'] = 'required|integer';
				$this->rules['broadcast_date'] = 'required|date_format:Y-m-d H:i:s';
				$validator = Validator::make($cra, $this->rules);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			}
			$absences = \App\Absences::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->get();
			$cras = \App\CRA::where('user_id', $user->id)->orderBy('start', 'ASC')->get();
			$data = [];
			foreach($craData as $cra){
				list($start_date, $end_date) = [$cra['start'], $cra['end']];
				list($startDate, $endDate) = [Carbon::createFromFormat('Y-m-d', $cra['start']), Carbon::createFromFormat('Y-m-d', $cra['end'])];
				list($start, $end) = [Carbon::createFromFormat('Y-m-d', $cra['start'])->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $cra['end'])->format('Y-m-d')];
				$dayCount = ($startDate->diffInDays($endDate) + 1);
				list($startDay, $endDay) = [date('N', strtotime($start)), date('N', strtotime($end))];
				if(($startDay == 7 || $startDay == 6) || ($endDay == 7 || $endDay == 6)){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$dates = Config::get('leave_dates');
				if(in_array($endDate->format("d/m/Y"), $dates) || in_array($endDate->format("d/m/Y"), $dates)){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$weekdayCounter = 0;
				while($start <= $end){
					$day = date('N', strtotime($start));
					if($day == 7 || $day == 6){ $weekdayCounter++; }else{
						$day = date('d/m/Y', strtotime($start));
						if(in_array($day, $dates)){ $weekdayCounter++; }
					}
					$start = date("Y-m-d", strtotime($start . "+1 day"));
				}
				if($weekdayCounter == $dayCount){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$conflictAbsences = $absences->filter(function($item) use($cra){ return (($item->start <= $cra['end']) && ($item->end >= $cra['start']) && ($item->startHalf == $cra['startHalf'] && $item->endHalf != $cra['endHalf'])); });
				$conflictCras = $cras->filter(function($item) use($cra){ return (($item->id != $cra['id']) && ($item->start <= $cra['end']) && ($item->end >= $cra['start'])); }); 
				if($conflictAbsences->count() > 0){
					$conflictAbsence = $conflictAbsences->first();
					if($conflictAbsence->startHalf == true && $conflictAbsence->endHalf == true){
						$conflictAbsences = $absences->filter(function($item) use($cra){ return (($item->start == $cra['end']) && ($item->end == $cra['start'])); });
						$conflictCras = $cras->filter(function($item) use($cra){ return (($item->id != $cra['id']) && ($item->start == $cra['end']) && ($item->end == $cra['start'])); });
					}
				}
				if($conflictAbsences->count() > 0 || $conflictCras->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				if($cra['startHalf'] || $cra['endHalf']){ $dayCount = $dayCount - 0.5; }
				$currentCRA = $cras->find($cra['id']);
				if(!$currentCRA){ return responseJson(['error' => trans('messages.CraDataNotFound')], 400); }
				if($currentCRA->start != $cra['start'] || $currentCRA->end != $cra['end']){
					$conflictAbsence = $absences->filter(function($item) use($cra){
						return ($item->start <= $cra['end']) && ($item->end >= $cra['start']);
					});
					if($conflictAbsence->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				}
				$updated = $currentCRA->update(['end' => $cra['end'], 'start' => $cra['start'], 'comments' => $cra['comments'], 'days' => ($dayCount - $weekdayCounter)]);
				if(!$updated){ return responseJson(['error' => trans('messages.SomethingWentWrong')], 400); }
				\App\ActivityLog::create(['user_id' => $user->id, 'object' => 'CRA', 'action' => 'update', 'data' => json_encode($currentCRA->toArray()),]);
			}
			return responseJson(['message' => trans('messages.YourRequestSavedSuccessFully')], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function getCra($user_id, $month){
		try{
			$user = \Auth::guard("api")->user();
			$absence = \App\Absences::getClientCras($user_id, $month);
			$data = \App\CRA::getCRA($user_id, $month);
			$userData = getApiUserData($user);
			return responseJson([
				'absences' => $absence['absence'],
				'userData' => $userData,
				'craData' => $data['craData'],
				'data' => $absence['absenceTypes'],
				'broadcast' => $data['broadcast'],
				'missionData' => $data['missionData'],
				'isCraEntered' => $data['isCraEntered'],
				'cra_status' => $data['cra_status'],
			], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function postCra(Request $request, $id){
		try{
			$user = \Auth::guard("api")->user();
			$result = \App\CRA::where('id', $id)->first();
			if($result){
				$data = $request->only('user_name', 'days', 'comments', 'broadcast_date', 'absence_type');
				$validator = Validator::make($data, $this->rules);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
				$update = $result->update($data);
				if($update) return responseJson(['message' => trans('messages.YourRequestUpdatedSuccessFully')], 200);
				else return responseJson(['error' => trans('messages.SomethingwentWrong')], 400);
			}else return responseJson(['error' => trans('messages.CraDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public function DeleteCRA($id){
		try{
			$user = \Auth::guard("api")->user();
			$cra = \App\CRA::find($id);
			if($cra){
				\App\ActivityLog::create(['user_id' => $user->id, 'object' => 'CRA', 'action' => 'delete', 'data' => json_encode($cra->toArray()),]);
				$timestamp = Carbon::createFromFormat('Y-m-d', $cra->start);
				\App\CRAStatus::whereRaw("MONTH(`date`) = ? AND YEAR(`date`) = ?", [$timestamp->format("m"), $timestamp->format("Y")])->delete();
				$cra->delete();
				return responseJson(['message' => trans("messages.CraDeletedSuccessfully")], 200);
			}else return responseJson(['error' => trans('messages.CraDataNotFound')], 400);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}

	public static function postCrasAction($m, $manager, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){
			$mail = $m->to($add_email)->subject($subject);
			if(count($cc_email) > 0){ $mail->bcc($cc_email); }
			if($template != null){ $mail->setBody($template, 'text/html'); }
		}else{
			if($manager){
				$managerData = $manager->manager;
				$mail = $m->to($managerData->email)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
			$admin = env('ADMIN_MAIL');
			if($admin){
				$mail = $m->to($admin)->subject($subject);
				if($template != null){ $mail->setBody($template, 'text/html'); }
			}
		}
	}

	public static function postExportCRAAction($m, $user, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($user->email)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	private function createCRA($request, $valid){
		try{
			$user = \Auth::guard("api")->user();
			$craData = $request->get('craData');
			$updateComment = $request->get('updateComment');
			$currentMonth = Carbon::now();
			if($request->has('currentMonth') && !empty($request->get('currentMonth'))){
				$currentMonth = Carbon::createFromFormat('m-Y', $request->get('currentMonth'));
			}
			$currentAbsences = \App\Absences::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $currentMonth->format('Y-m-t'), $currentMonth->format('Y-m-01')])->whereIn('status', ['pending', 'approved'])->orderBy('start', 'ASC')->get();
			$craMonth = \App\CRA::$months[$currentMonth->format('n') - 1] . ' ' . $currentMonth->format('Y');
			$craMonthStr = $currentMonth->format('d-m-Y');
			foreach($craData as $cra){
				$validator = Validator::make($cra, $this->rules);
				if($validator->fails()) return responseJson(['errors' => $validator->errors(), 'validation' => true], 400);
			}
			if(getStartOREnd($request, 'delete_exists')){
				foreach($craData as $cKey => $cra){
					list($start_date, $end_date) = [$cra['start'], $cra['end']];
					list($start_half, $end_half) = [getStartOREnd($cra, 'startHalf'), getStartOREnd($cra, 'endHalf')];
					list($startDate, $endDate) = [Carbon::createFromFormat('Y-m-d', $cra['start'])->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $cra['end'])->format('Y-m-d')];
					$cras = \App\CRA::whereRaw('user_id = ? AND start <= ? AND end >= ?', [$user->id, $endDate, $startDate])->orderBy('start', 'ASC')->get();
					$craIdstoRemove = $cras->pluck('id');
					$originalCraCount = $cras->count();
					if(!$start_half && !$end_half){
						foreach($cras as $exist_cra_key => $exist_cra){
							if($exist_cra->start >= $startDate && $exist_cra->end <= $endDate){ $exist_cra->delete(); }
						}
						if($cras->count() > 0){
							$crasCount = $cras->count();
							foreach($cras as $exist_cra_key => $exist_cra){
								if($exist_cra->start >= $startDate && $exist_cra->start <= $endDate){
									$exist_cra->start = date("Y-m-d", strtotime($endDate . " +1 day"));
									$exist_days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									$exist_cra->update(['days' => $exist_days, 'start' => date("Y-m-d", strtotime($endDate . " +1 day"))]);
								}
								if($exist_cra->end >= $startDate && $exist_cra->end <= $endDate){
									$exist_cra->end = date("Y-m-d", strtotime($startDate . " -1 day"));
									$exist_days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									$exist_cra->update(['days' => $exist_days, 'end' => date("Y-m-d", strtotime($startDate . " -1 day"))]);
								}
								if($exist_cra->start < $startDate && $exist_cra->end > $endDate){
									$exist_cra_new = $exist_cra->replicate();
									$exist_cra->end = date("Y-m-d", strtotime($startDate . " -1 day"));
									$exist_days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									$exist_cra->update(['days' => $exist_days, 'end' => date("Y-m-d", strtotime($startDate . " -1 day"))]);
									$exist_cra_new->start = date("Y-m-d", strtotime($endDate . " +1 day"));
									$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
									$exist_cra_new->push();
								}
							}
						}
					}else{
						foreach($cras as $exist_cra_key => $exist_cra){
							if($exist_cra->start >= $startDate && $exist_cra->end <= $endDate){
								if($exist_cra_key != 0 && $originalCraCount != $exist_cra_key + 1){ $exist_cra->delete(); }
							}
						}
						if($cras->count() > 0){
							$crasCount = $cras->count();
							foreach($cras as $exist_cra_key => $exist_cra){
								if($start_half && $end_half){
									$exist_cra_new = $exist_cra->replicate();
									$exist_cra->endHalf = $end_half;
									$exist_cra->end = date("Y-m-d", strtotime($startDate));
									$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
									$exist_cra->save();
								}else if(!$start_half && $end_half){
									$exist_cra_new = $exist_cra->replicate();
									if($exist_cra->start != $exist_cra->end && $startDate == $endDate && $exist_cra->end == $endDate){
										$exist_cra_new->end = date("Y-m-d", strtotime($startDate . ' -1 day'));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$exist_cra_new->push();
										$exist_cra->startHalf = true;
										$exist_cra->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra->save();
									}else{
										if ($exist_cra->start != $startDate){
											$exist_cra_new->end = date("Y-m-d", strtotime($startDate . ' -1 day'));
											$exist_cra_new->days = getDaysCount($exist_cra->start, $exist_cra_new->end, $exist_cra->startHalf, $exist_cra->endHalf);
											$exist_cra_new->push();	
										}
										$exist_cra->startHalf = true;
										$exist_cra->start = date("Y-m-d", strtotime($endDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra->save();
									}
								}else if($start_half && !$end_half){
									$exist_cra_new = $exist_cra->replicate();
									if($exist_cra->start != $exist_cra->end && $startDate == $endDate && $exist_cra->start == $startDate){
										$exist_cra_new->start = date("Y-m-d", strtotime($endDate . ' +1 day'));
										$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra_new->end, $exist_cra_new->startHalf, $exist_cra_new->endHalf);
										$exist_cra_new->push();
										$exist_cra->endHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra->save();
									}else{
										if ($exist_cra->end != $endDate){
											$exist_cra_new->start = date("Y-m-d", strtotime($endDate . ' +1 day'));
											$exist_cra_new->days = getDaysCount($exist_cra_new->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
											$exist_cra_new->push();	
										}
										$exist_cra->endHalf = true;
										$exist_cra->end = date("Y-m-d", strtotime($startDate));
										$exist_cra->days = getDaysCount($exist_cra->start, $exist_cra->end, $exist_cra->startHalf, $exist_cra->endHalf);
										$exist_cra->save();
									}
								}
							}
						}
					}
				}
			}
			$absences = \App\Absences::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->get();
			$cras = \App\CRA::where('user_id', $user->id)->get();
			$data = [];
			foreach($craData as $cKey => $cra){
				if($cKey != 0){ $cras = \App\CRA::where('user_id', $user->id)->get(); }
				list($start_date, $end_date) = [$cra['start'], $cra['end']];
				list($startDate, $endDate) = [Carbon::createFromFormat('Y-m-d', $cra['start']), Carbon::createFromFormat('Y-m-d', $cra['end'])];
				list($start, $end) = [Carbon::createFromFormat('Y-m-d', $cra['start'])->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $cra['end'])->format('Y-m-d')];
				$dayCount = ($startDate->diffInDays($endDate) + 1);
				list($startDay, $endDay) = [date('N', strtotime($start)), date('N', strtotime($end))];
				if(($startDay == 7 || $startDay == 6) || ($endDay == 7 || $endDay == 6)){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$dates = Config::get('leave_dates');
				if(in_array($startDate->format("d/m/Y"), $dates) || in_array($endDate->format("d/m/Y"), $dates)){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$weekdayCounter = 0;
				while($start <= $end){
					$day = date('N', strtotime($start));
					if($day == 7 || $day == 6){ $weekdayCounter++; }else{
						$day = date('d/m/Y', strtotime($start));
						if(in_array($day, $dates)){ $weekdayCounter++; }
					}
					$start = date("Y-m-d", strtotime($start . "+1 day"));
				}
				if($weekdayCounter == $dayCount){ return responseJson(['error' => trans('messages.WeekendErrorMsg', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
				$conflictAbsences = $absences->filter(function($item) use($cra){ return (($item->start <= $cra['end']) && ($item->end >= $cra['start'])); });
				$conflictCras = $cras->filter(function($item) use($cra){ return (($item->start <= $cra['end']) && ($item->end >= $cra['start'])); });
				if($cra['endHalf'] || $cra['startHalf']){
					$conflictCras1 = $cras->filter(function($item) use($cra){ return ($item->start == $cra['start'] && $item->end == $cra['end'] && $item->startHalf == $cra['startHalf'] && $item->endHalf == $cra['endHalf']); });
					if($conflictCras1->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
					if($conflictAbsences->count() > 1 || $conflictCras->count() > 1){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
					if($conflictAbsences->count() > 0){ $conflict = $conflictAbsences->first(); }
					else{ $conflict = $conflictCras->first(); }
					if($conflict){
						$conflictStart = Carbon::createFromFormat('Y-m-d', $conflict->start);
						$conflictEnd = Carbon::createFromFormat('Y-m-d', $conflict->end);
						if($conflictStart->eq($conflictEnd)){
							if(($conflict->startHalf != $cra['startHalf']) && ($conflict->endHalf != $cra['endHalf'])){
								$data[] = ['status' => true, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s"), 'user_id' => $user->id,
									'end' => $cra['end'], 'start' => $cra['start'], 'comments' => $cra['comments'], 'broadcast_date' => $cra['broadcast_date'],
									'days' => ($dayCount - $weekdayCounter) - 0.5, 'startHalf' => $cra['startHalf'], 'endHalf' => $cra['endHalf'],
									'mission_id' => $cra['mission_id'], 'validation' => $valid,
								];
							}else{ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
						}else{
							if($cra['endHalf'] && $cra['startHalf'] != $conflict->startHalf){
								$data[] = ['status' => true, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s"), 'user_id' => $user->id,
									'end' => $cra['end'], 'start' => $cra['start'], 'comments' => $cra['comments'], 'broadcast_date' => $cra['broadcast_date'],
									'days' => ($dayCount - $weekdayCounter) - 0.5, 'startHalf' => $cra['startHalf'], 'endHalf' => $cra['endHalf'],
									'mission_id' => $cra['mission_id'], 'validation' => $valid,
								];
							}elseif($cra['startHalf'] && $cra['endHalf'] != $conflict->endHalf){
								$data[] = ['status' => true, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s"), 'user_id' => $user->id,
									'end' => $cra['end'], 'start' => $cra['start'], 'comments' => $cra['comments'], 'broadcast_date' => $cra['broadcast_date'],
									'days' => ($dayCount - $weekdayCounter) - 0.5, 'startHalf' => $cra['startHalf'], 'endHalf' => $cra['endHalf'],
									'mission_id' => $cra['mission_id'], 'validation' => $valid,
								];
							}else{ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date]),], 400); }
						}
					}else{
						$days = ($dayCount - $weekdayCounter);
						if($cra['startHalf']){ $days = $days - 0.5; }
						if($cra['endHalf']){ $days = $days - 0.5; }
						$data[] = ['status' => true, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s"), 'user_id' => $user->id,
							'end' => $cra['end'], 'start' => $cra['start'], 'comments' => $cra['comments'], 'broadcast_date' => $cra['broadcast_date'],
							'days' => $days, 'startHalf' => $cra['startHalf'], 'endHalf' => $cra['endHalf'], 'mission_id' => $cra['mission_id'], 'validation' => $valid,
						];
					}
				}else{
					if($conflictAbsences->count() > 0 || $conflictCras->count() > 0){ return responseJson(['error' => trans('messages.WeekendErrorMsg1', ['start_date' => $start_date, 'end_date' => $end_date])], 400); }
					$data[] = ['status' => true, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s"), 'user_id' => $user->id,
						'end' => $cra['end'], 'start' => $cra['start'], 'comments' => $cra['comments'], 'broadcast_date' => $cra['broadcast_date'],
						'days' => ($dayCount - $weekdayCounter), 'startHalf' => $cra['startHalf'], 'endHalf' => $cra['endHalf'],
						'mission_id' => $cra['mission_id'], 'validation' => $valid,
					];
				};
			};
			$userData = getApiUserData($user);
			if(count($data) > 0){
				foreach ($data as $key1 => $cra1) {
					//remove duplicate entries
					foreach ($data as $key2 => $cra2) {
						if ($key1 != $key2) {
							if ($cra1['user_id'] == $cra2['user_id'] && $cra1['mission_id'] == $cra2['mission_id'] &&  $cra1['start'] == $cra2['start'] && $cra1['end'] == $cra2['end'] && $cra1['days'] == $cra2['days'] && empty($cra1['startHalf']) == empty($cra2['startHalf']) && empty($cra1['endHalf']) == empty($cra2['endHalf'])) {
									unset($data[$key1]);
							}
						}
					}
					//remove duplicate entry of dates in between
					list($start, $end) = [Carbon::createFromFormat('Y-m-d', $cra1['start']), Carbon::createFromFormat('Y-m-d', $cra1['end'])];
					if($start->diffInDays($end) > 0) {
						//get dates in between two dates
						$datesRemove = [];
					    for($date = $start; $date->lte($end); $date->addDay()) {
					        $datesRemove[] = $date->format('Y-m-d');
					    }
						foreach ($data as $key2 => $cra2) {
							foreach ($datesRemove as $date) {
								if ($date == $cra2['start'] && $date == $cra2['end'] && $cra1['user_id'] == $cra2['user_id'] && empty($cra1['startHalf']) == empty($cra2['startHalf']) && empty($cra1['endHalf']) == empty($cra2['endHalf'])) {
									unset($data[$key2]);
								}
							}
						}
					}
				}			
				$data = array_values($data);
				$insert = \App\CRA::insert($data);
				if($insert){ \App\ActivityLog::create(['user_id' => $user->id, 'object' => 'CRA', 'action' => 'create', 'data' => json_encode($data),]); }
			}
			if($updateComment){
				if(isset($updateComment['Ids']) && count($updateComment['Ids']) > 0){
					\App\CRA::whereIn('id', $updateComment['Ids'])->update(['comments' => $updateComment['comment'], 'validation' => $valid,]);
					$oldcras = \App\CRA::whereIn('id', $updateComment['Ids'])->get()->toArray();
					foreach($oldcras as $key => $cra){ $data[] = $cra; }
				}
			}
			\App\CRAStatus::whereRaw("MONTH(`date`) = ? AND YEAR(`date`) = ?", [$currentMonth->format("m"), $currentMonth->format("Y")])->delete();
			$cra_status = null;
			if($valid && (count($data) > 0 || $currentAbsences->count() > 0)){
				usort($data, function($a, $b){
					return $a['start'] <=> $b['start'];
				});
				$usermissions = \App\UserMission::where('user_id', $user->id)->get()->pluck(['mission_id']);
				$missions = \App\Mission::whereIn('id', $usermissions)->orderBy('order', 'ASC')->get()->pluck('label', 'id');
				if(count($data) > 0){
					foreach ($data as $key1 => $cra1) {
						//remove duplicate entries
						foreach ($data as $key2 => $cra2) {
							if ($key1 != $key2) {
								if ($cra1['user_id'] == $cra2['user_id'] && $cra1['mission_id'] == $cra2['mission_id'] &&  $cra1['start'] == $cra2['start'] && $cra1['end'] == $cra2['end'] && $cra1['days'] == $cra2['days'] && $cra1['startHalf'] == $cra2['startHalf'] && $cra1['endHalf'] == $cra2['endHalf']) {
										unset($data[$key1]);
								}
							}
						}
						//remove duplicate entry of dates in between
						list($start, $end) = [Carbon::createFromFormat('Y-m-d', $cra1['start']), Carbon::createFromFormat('Y-m-d', $cra1['end'])];
						if($start->diffInDays($end) > 0) {
							//get dates in between two dates
							$datesRemove = [];
						    for($date = $start; $date->lte($end); $date->addDay()) {
						        $datesRemove[] = $date->format('Y-m-d');
						    }
							foreach ($data as $key2 => $cra2) {
								foreach ($datesRemove as $date) {
									if ($date == $cra2['start'] && $date == $cra2['end'] && $cra1['user_id'] == $cra2['user_id'] && $cra1['startHalf'] == $cra2['startHalf'] && $cra1['endHalf'] == $cra2['endHalf']) {
											unset($data[$key2]);
									}
								}
							}
						}
					}
				}
				$data = array_values($data);
				foreach($data as $key => $cra){
					$data[$key]['mission_code'] = '';
					if(isset($missions[$cra['mission_id']])){
						$data[$key]['mission_code'] = $missions[$cra['mission_id']];
					}
				}
				$fdata = [];
				foreach($data as $key => $cra){
					if(isset($fdata[$data[$key]['mission_code']])){
						$fdata[$data[$key]['mission_code']]['days'] += $data[$key]['days'];
						$fdata[$data[$key]['mission_code']]['cras'][] = $data[$key];
					}else{
						$fdata[$data[$key]['mission_code']] = [];
						$fdata[$data[$key]['mission_code']]['code'] = $data[$key]['mission_code'];
						$fdata[$data[$key]['mission_code']]['comments'] = $data[$key]['comments'];
						$fdata[$data[$key]['mission_code']]['days'] = $data[$key]['days'];
						$fdata[$data[$key]['mission_code']]['cras'] = [];
						$fdata[$data[$key]['mission_code']]['cras'][] = $data[$key];
					}
				}
				ksort($fdata);
				$adata = [];
				foreach($currentAbsences as $key => $currentAbsence){
					$currentAbsence->reasonStr = $currentAbsence->reasonStr;
					if(isset($adata[$currentAbsence->reason])){
						$adata[$currentAbsence->reason]['days'] += $currentAbsence->days;
						$adata[$currentAbsence->reason]['absences'][] = $currentAbsence->toArray();
					}else{
						$adata[$currentAbsence->reason] = [];
						$adata[$currentAbsence->reason]['reason'] = $currentAbsence->reason;
						$adata[$currentAbsence->reason]['reasonStr'] = $currentAbsence->reasonStr;
						$adata[$currentAbsence->reason]['days'] = $currentAbsence->days;
						$adata[$currentAbsence->reason]['absences'] = [];
						$adata[$currentAbsence->reason]['absences'][] = $currentAbsence->toArray();
					}
				}
				if(isset($adata['rtt'])){ $adata = array('rtt' => $adata['rtt']) + $adata; }
				$manager = \App\UserManager::where('consultant_id', $user->id)->first();
				$mail = \App\EmailTemplate::where('email', 'postcras')->where('status', true)->first();
				$subject = trans('messages.NewCRACreated');
				$yesText = trans('messages.Yes');
				$noText = trans('messages.No');
				$formatText = '%Y-%m-%d %H:%M:%S';
				if($mail){
					$mails = [];
					$ccmails = [];
					if(!is_null($mail->add_email)){
						$amails = explode(',', $mail->add_email);
						foreach($amails as $key => $amail){
							$amail = trim($amail);
							if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
								if($amail == '{{$user->email}}'){ $mails[] = $user->email; }
								if($amail == '{{$manager->email}}'){
									if($manager){
										$managerData = $manager->manager;
										$mails[] = $managerData->email;
									}
								}
								if($amail == '{{$admin->email}}'){
									$admin = env('ADMIN_MAIL');
									if($admin){ $mails[] = $admin; }
								}
							}else{ $mails[] = $amail; }
						}
					}
					if(!is_null($mail->cc_email)){
						$cmails = explode(',', $mail->cc_email);
						foreach($cmails as $key => $cmail){
							$cmail = trim($cmail);
							if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
								if($cmail == '{{$user->email}}'){ $ccmails[] = $user->email; }
								if($cmail == '{{$manager->email}}'){
									if($manager){
										$managerData = $manager->manager;
										$ccmails[] = $managerData->email;
									}
								}
								if($cmail == '{{$admin->email}}'){
									$admin = env('ADMIN_MAIL');
									if($admin){ $ccmails[] = $admin; }
								}
							}else{ $ccmails[] = $cmail; }
						}
					}
					$mails = array_unique($mails);
					$ccmails = array_unique($ccmails);
					$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'craData' => $fdata, 'cras' => $data, 'user' => $user, 'craMonth' => $craMonth, 'craMonthStr' => $craMonthStr, 'absences' => $adata])->render();
					$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'craData' => $fdata, 'cras' => $data, 'user' => $user, 'craMonth' => $craMonth, 'craMonthStr' => $craMonthStr, 'absences' => $adata])->render();
					Mail::raw($template, function($m) use($manager, $subject, $template, $mails, $ccmails){ self::postCrasAction($m, $manager, $subject, $template, $mails, $ccmails); });
				}else{
					Mail::send('emails.cras', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'craData' => $fdata, 'cras' => $data, 'user' => $user, 'craMonth' => $craMonth, 'craMonthStr' => $craMonthStr, 'absences' => $adata], function($m) use($manager, $subject){ self::postCrasAction($m, $manager, $subject); });
				}
				$cra_status = \App\CRAStatus::create(['user_id' => $user->id, 'date' => $currentMonth->format("y-m-d")]);
			}
			return responseJson(['data' => $userData, 'cra_data' => $craData, 'cra_status' => $cra_status, 'message' => trans('messages.YourRequestSavedSuccessFully')], 200);
		}catch(\Exception $e){
			return responseJson(['error' => $e->getMessage()], 400);
		}
	}
}