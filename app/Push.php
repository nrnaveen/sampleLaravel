<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Push {

	// Will need to add looping with offset to get all devices.
	public static function getDevices($page = 0){
		$app_id = env("ONESIGNAL_APPID");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/players?app_id=" . $app_id . "&offset=" . $page);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . env("ONESIGNAL_ACCOUNTID")));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public static function sendToAll($content = [], $data = [], $headings = [], $send_after){
		$app_id = env("ONESIGNAL_APPID");
		$fields = json_encode([
			'app_id' => $app_id,
			'included_segments' => ['All',],
			'data' => $data,
			'contents' => $content,
			'headings' => $headings,
			'send_after' => $send_after,
		]);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . env("ONESIGNAL_ACCOUNTID")));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public static function sendById($content = [], $data = [], $ids, $headings = [], $send_after){
		$app_id = env("ONESIGNAL_APPID");
		$fields = json_encode([
			'app_id' => $app_id,
			'include_player_ids' => $ids,
			'data' => $data,
			'contents' => $content,
			'headings' => $headings,
			'send_after' => $send_after,
		]);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . env("ONESIGNAL_ACCOUNTID")));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

}