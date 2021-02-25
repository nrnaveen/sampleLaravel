<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Carbon\Carbon;

function getStartOREnd($request, $attribute){
	// $value = false;
	// if(is_null($attribute) || is_array($attribute) || is_array($request)){ return false; }
	// if($request->has($attribute)){ $value = $request->get($attribute); }
	$value = data_get($request, $attribute, false);
	return in_array($value, [1, true, '1', 'true'], true);
};

function getDaysCount($start, $end, $start_half = false, $end_half = false, $withoutWeekend = true, $withoutLeaves = true){
	$leave_dates = Config::get('leave_dates');
	list($exist_startDate, $exist_endDate) = [Carbon::createFromFormat('Y-m-d', $start), Carbon::createFromFormat('Y-m-d', $end)];
	list($exist_start, $exist_end) = [Carbon::createFromFormat('Y-m-d', $start)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $end)->format('Y-m-d')];
	$exist_dayCount = ($exist_startDate->diffInDays($exist_endDate) + 1);
	$exist_weekdayCounter = 0;
	if($withoutWeekend){
		while($exist_start <= $exist_end){
			$exist_day = date('N', strtotime($exist_start));
			if($exist_day == 7 || $exist_day == 6){ $exist_weekdayCounter++; }
			$exist_start = date("Y-m-d", strtotime($exist_start . "+1 day"));
		}
		$exist_dayCount = ($exist_dayCount - $exist_weekdayCounter);
	}
	if($withoutLeaves){
		$exist_start = Carbon::createFromFormat('Y-m-d', $start)->format('Y-m-d');
		while($exist_start <= $exist_end){
			if(in_array(date('d/m/Y', strtotime($exist_start)), $leave_dates)){ $exist_weekdayCounter++; }
			$exist_start = date("Y-m-d", strtotime($exist_start . "+1 day"));
		}
		$exist_dayCount = ($exist_dayCount - $exist_weekdayCounter);
	}
	if($start_half){ $exist_dayCount = $exist_dayCount - 0.5; }
	if($end_half){ $exist_dayCount = $exist_dayCount - 0.5; }
	return $exist_dayCount;
};

function getWorkingDate($date){
	$dates = Config::get('leave_dates');
	$timestamp = Carbon::createFromFormat('d/m/Y', $date);
	if(in_array($date, $dates) || $timestamp->isSunday() || $timestamp->isSaturday()){
		return getWorkingDate($timestamp->modify('-1 day')->format("d/m/Y"));
	}
	return $date;
};

function convertDate($date, $format = '%Y-%m-%d'){
	try {
		if(!$format){ $format = '%Y-%m-%d'; };
		return Carbon::parse($date)->formatLocalized($format);
	}catch(Exception $e){
		return false;
	}
};

function isSunday($date){
	try {
		return Carbon::parse($date)->isSunday();
	}catch(Exception $e){
		return false;
	}
};

function isMonday($date){
	try {
		return Carbon::parse($date)->isMonday();
	}catch(Exception $e){
		return false;
	}
};

function isTuesday($date){
	try {
		return Carbon::parse($date)->isTuesday();
	}catch(Exception $e){
		return false;
	}
};

function isWednesday($date){
	try {
		return Carbon::parse($date)->isWednesday();
	}catch(Exception $e){
		return false;
	}
};

function isThursday($date){
	try {
		return Carbon::parse($date)->isThursday();
	}catch(Exception $e){
		return false;
	}
};

function isFriday($date){
	try {
		return Carbon::parse($date)->isFriday();
	}catch(Exception $e){
		return false;
	}
};

function isSaturday($date){
	try {
		return Carbon::parse($date)->isSaturday();
	}catch(Exception $e){
		return false;
	}
};

function urlWithQuery($path = null, $qs = array(), $secure = null){
	$url = app('url')->to($path, $secure);
	if(count($qs)){
		foreach($qs as $key => $value){
			$qs[$key] = sprintf('%s=%s',$key, urlencode($value));
		}
		$url = sprintf('%s?%s', $url, implode('&', $qs));
	}
	return $url;
};

function getApiToken(){
	try{
		$token = md5(strtotime("now")) . '-' . generateRandomStr(10) . "-" . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
		return ["error" => false, "token" => $token, ];
	}catch(UnsatisfiedDependencyException $e){
		return ["error" => true, "exception" => $e, ];
	}
};

function generateRandomStr($length = 10){
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for($i = 0; $i < $length; $i++){
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
};

function getApiUserData($data){
	$user = $data->toArray();
	$user['name'] = $data->name;
	$user['api_token'] = $data->api_token;
	return $user;
};

function getRandomColor(){
	$rand = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
	$color = '#' . $rand[rand(0,15)] . $rand[rand(0,15)] . $rand[rand(0,15)] . $rand[rand(0,15)] . $rand[rand(0,15)] . $rand[rand(0,15)];
	return $color;
};

function responseJson($data, $status = 200){
	$version = \App\Version::orderBy('created_at', 'DESC')->first();
	$data['version'] = '0.0.1';
	if($version){ $data['version'] = $version->version; }
	$reasonLabels = \App\AbsenceTypes::getReasonLabels();
	$reasonColors = \App\AbsenceTypes::getReasonColors();
	$data['reasonLabels'] = $reasonLabels;
	$data['reasonColors'] = $reasonColors;
	return response()->json($data, $status);
};

function setEnvironmentValue(array $values){
	$envFile = app()->environmentFilePath();
	$str = file_get_contents($envFile);
	if (count($values) > 0) {
		foreach ($values as $envKey => $envValue) {
			$str .= "\n"; // In case the searched variable is in the last line without \n
			$keyPosition = strpos($str, "{$envKey}=");
			$endOfLinePosition = strpos($str, "\n", $keyPosition);
			$oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
			// If key does not exist, add it
			if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
				$str .= "{$envKey}={$envValue}\n";
			} else {
				$str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
			}
		}
	}
	$str = substr($str, 0, -1);
	if (!file_put_contents($envFile, $str)) return false;
	return true;
};