<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail, DbView, Config, Log;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class AmazonController extends Controller {

	public function handleBounceOrComplaint(Request $request){
		Log::info($request->json()->all());
		$data = $request->json()->all();
		if($request->json('Type') == 'SubscriptionConfirmation')
			Log::info("SubscriptionConfirmation came at: " . $data['Timestamp']);
		if($request->json('Type') == 'Notification'){
			$message = json_decode($request->json('Message', true), true);
			switch($message['notificationType']){
				case 'Bounce':
					$bounce = $message['bounce'];
					foreach($bounce['bouncedRecipients'] as $bouncedRecipient){
						$emailAddress = $bouncedRecipient['emailAddress'];
						$emailRecord = \App\BounceEmail::firstOrCreate(['email' => $emailAddress, 'type' => 'Bounce']);
						if($emailRecord){
							$emailRecord->increment('repeated_attempts', 1);
						}
					}
					break;
				case 'Complaint':
					$complaint = $message['complaint'];
					foreach($complaint['complainedRecipients'] as $complainedRecipient){
						$emailAddress = $complainedRecipient['emailAddress'];
						$emailRecord = \App\BounceEmail::firstOrCreate(['email' => $emailAddress, 'type' => 'Complaint']);
						if($emailRecord){
							$emailRecord->increment('repeated_attempts', 1);
						}
					}
					break;
				default:
					// Do Nothing
					break;
			}
		}
		return response()->json(['status' => 200, "message" => 'success']);
	}

}