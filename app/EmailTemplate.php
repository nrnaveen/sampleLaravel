<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator, DateTime, Config, File, View;
use Carbon\Carbon;

class EmailTemplate extends Model {

	protected $table = 'email_template';

	static protected $templates = [
		'forgot-password' => "Forgot Password", // auth.emails.password
		'newabsence' => "Notify New Absence", // emails.newabsence
		'admindelete' => "Notify Absence Delete to Admin/Manager", // emails.admindelete
		'postcras' => "Notify New CRAs", // emails.cras
		'postpenalty' => "Notify New Penalty", // emails.penalty
		'approved' => "Notify Approved Absence", // emails.approved
		'adminapproved' => "Notify Approved Absence to Admin/Manager", // emails.adminapproved
		'declined' => "Notify Declined Absence", // emails.declined
		'admindeclined' => "Notify Declined Absence to Admin/Manager", // emails.admindeclined
		'exported' => "Notify Absence/CRAs Export", // emails.exported
		'deleted' => "Notify Absence Delete", // emails.deleted
		'entercra' => "Notification When not Enter CRA", // emails.entercra
		'exportcra' => "Notification When Export CRA", // emails.exportcra
		'eventnotify' => "Notification When Event", // emails.eventnotify
		'contact' => "When the User submit the contact form", // emails.contact
	];

	protected $fillable = [
		'id',
		'subject',
		'email',
		'cc_email',
		'add_email',
		'status',
		'template',
		'created_at',
		'updated_at'
	];

	public function getNameAttribute(){
		return self::$templates[$this->email];
	}

	public function getDecodeTemplateAttribute(){
		return htmlspecialchars_decode($this->template);
	}

	public function getDecodeSubjectAttribute(){
		return htmlspecialchars_decode($this->subject);
	}

	public static function getTemplates(){
		return self::$templates;
	}

	public static function checkTemplates($template, $arguments){
		exec('php -B "' . $template . ';" -E -- ' . $arguments);
	}

	public static function checkTemplate($request, $template = 'template'){
		$user = new \App\User(['firstname' => "Admin", 'lastname' => "Admin", 'role' => "manager", 'email' => "admin@sample.com", 'status' => 1, 'deviceId' => null, 'registrationId' => null, 'deviceType' => "android", 'mobile' => null, 'address' => null, 'creation_date' => null, 'image' => null, 'api_token' => null, 'socialId' => null, 'color' => null, 'created_at' => "2018-04-14 22:43:24", 'updated_at' => "2018-04-14 22:43:24",]);
		$adminUser = new \App\Admin(['firstname' => "Admin", 'lastname' => "Admin", 'email' => "admin@sample.com", 'created_at' => "2018-04-14 22:43:24", 'updated_at' => "2018-04-14 22:43:24",]);
		$cra = ['start' => "2017-10-02", 'end' => "2017-10-06", 'startHalf' => false, 'endHalf' => false, 'days' => 5, 'comments' => "Testing",];
		$absence = ['user_id' => 4, 'status' => 'approved', 'start' => "2017-10-09",
			'end' => "2017-10-13", 'startHalf' => true, 'endHalf' => true,
			'days' => 4, 'reason' => 'paid_vacation', 'cancel_reason' => null,
			'accepted_date' => null, 'cancelled_date' => null, 'deleted_date' => null,
			'client_informed' => true, 'archive' => false, 'self' => false,
			'created_at' => "2017-10-04 19:47:23", 'updated_at' => "2017-10-04 19:47:23",
		];
		$penalty = ['user_id' => 4, 'mission_id' => 141, 'beginning' => "2018-09-24 09:00:00",
			'ending' => "2018-09-24 10:00:00", 'total_duration' => "01:00:00",
			'type' => "Active", 'at_home' => false, 'comments' => "Test", 'client_informed' => false,
			'created_at' => "2017-10-04 19:47:23", 'updated_at' => "2017-10-04 19:47:23",
		];
		if($request['email'] == 'eventnotify'){ // Notification When Event
			$data = ['event' => new \App\Events(['label' => "CRI provisoire", 'date' => "2018-04-20", 'description' => "CRI provisoire", 'status' => true, 'created_at' => "2018-04-14 22:43:24", 'updated_at' => "2018-04-14 22:43:24",]), ];
		}elseif($request['email'] == 'contact'){ // When the User submit the contact form
			$data = ['user' => $user,
				'data' => ['subject' => "Contact Form", 'description' => "Description Contact Form", 'user_id' => "3", 'attachement_1' => "", 'attachement_2' => "", 'web' => true, 'app_name' => "Test", 'app_version' => "1.0.0",]
			];
		}elseif($request['email'] == 'exportcra'){ // Notification When Export CRA
			$craData = [];
			$cras = collect();
			for($i = 0; $i < 5; $i++){
				$craData[] = [
					'code' => "cra" . $i, 'days' => $i + 4,
					'cras' => [$cra, $cra, $cra, $cra, $cra]
				];
				$cras->push(new \App\CRA($cra));
			}
			$data = ['user' => $user, 'cras' => $cras, 'craData' => $craData,];
		}elseif($request['email'] == 'entercra'){ // Notification When not Enter CRA
			$data = [
				'date' => $date = date("Y-m-d"),
				'month' => Carbon::now()->formatLocalized("%B %Y"),
				'craMonthStr' => 'Septembre 2018',
				'craMonth' => '01-09-2018',
				'user' => $user,
			];
		}elseif($request['email'] == 'deleted'){ // Notify Absence Delete
			$data = ['by' => 'Admin', 'userData' => $user, 'absence' => $absence];
		}elseif($request['email'] == 'exported'){ // Notify Absence/CRAs Export
			$data = ['type' => 'CRA', 'by' => 'Admin',];
		}elseif($request['email'] == 'admindeclined'){ // Notify Declined Absence to Admin/Manager
			$data = ['absence' => new \App\Absences($absence), 'userData' => $user, 'by' => 'Admin', 'reason' => "Test", 'declinedUser' => $adminUser, 'approvedUser' => $adminUser];
		}elseif($request['email'] == 'declined'){ // Notify Declined Absence
			$data = ['absence' => new \App\Absences($absence), 'userData' => $user, 'by' => 'Admin', 'reason' => "Test", 'declinedUser' => $adminUser, 'approvedUser' => $adminUser];
		}elseif($request['email'] == 'adminapproved'){ // Notify Approved Absence to Admin/Manager
			$data = ['absence' => new \App\Absences($absence), 'userData' => $user, 'by' => 'Admin', 'declinedUser' => $adminUser, 'approvedUser' => $adminUser];
		}elseif($request['email'] == 'approved'){ // Notify Approved Absence
			$data = ['absence' => new \App\Absences($absence), 'userData' => $user, 'by' => 'Admin', 'declinedUser' => $adminUser, 'approvedUser' => $adminUser];
		}elseif($request['email'] == 'postpenalty'){ // Notify New Penalty
			$data = ['penalty' => new \App\Penalty($penalty), 'user' => $user,];
		}elseif($request['email'] == 'postcras'){ // Notify New CRAs
			$data = ['user' => $user, 'craMonth' => 'Septembre 2018', 'craMonthStr' => '01-09-2018',
				'absences' => [
					'rtt' => [
						'reason' => "rtt",
						'reasonStr' => "RTT",
						'days' => '0.5',
						'absences' => [[
							'id' => 411,
							'user_id' => 1,
							'status' => "approved",
							'start' => "2018-09-18",
							'end' => "2018-09-18",
							'startHalf' => 0,
							'endHalf' => 1,
							'days' => "0.5",
							'reason' => "rtt",
							'cancel_reason' => "", 
							'accepted_date' => "2018-10-02",
							'cancelled_date' => "",
							'deleted_date' => "",
							'client_informed' => 1,
							'archive' => 0,
							'self' => 1,
							'created_at' => "2018-10-02 08:07:10",
							'updated_at' => "2018-10-02 08:07:14",
							'reasonStr' => "RTT",
						]],
					],
					'paid_vacation' => [
						'reason' => "paid_vacation",
						'reasonStr' => "Congés Payés",
						'days' => 2,
						'absences' => [
							[
								'id' => 408,
								'user_id' => 1,
								'status' => "approved",
								'start' => "2018-09-12",
								'end' => "2018-09-12",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 1,
								'reason' => "paid_vacation",
								'cancel_reason' => "",
								'accepted_date' => "2018-10-02",
								'cancelled_date' => "",
								'deleted_date' => "",
								'client_informed' => 1,
								'archive' => 0,
								'self' => 1,
								'created_at' => "2018-10-02 08:05:45",
								'updated_at' => "2018-10-02 08:05:59",
								'reasonStr' => "Congés Payés",
							], [
								'id' => 409,
								'user_id' => 1,
								'status' => "approved",
								'start' => "2018-09-17",
								'end' => "2018-09-17",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 1,
								'reason' => "paid_vacation",
								'cancel_reason' => "",
								'accepted_date' => "2018-10-02",
								'cancelled_date' => "",
								'deleted_date' => "",
								'client_informed' => 1,
								'archive' => 0,
								'self' => 1,
								'created_at' => "2018-10-02 08:06:14",
								'updated_at' => "2018-10-02 08:06:21",
								'reasonStr' => "Congés Payés",
							]
						],
					],
				],
				'cras' => [
					[
						"id" => 928,
						"user_id" => 1,
						"mission_id" => 9,
						"status" => 1,
						"validation" => 1,
						"start" => "2018-09-03",
						"end" => "2018-09-07",
						"startHalf" => 0,
						"endHalf" => 0,
						"days" => 5,
						"comments" => "Test",
						"broadcast_date" => "2018-09-12 16:41:48",
						"created_at" => "2018-09-12 16:42:23",
						"updated_at" => "2018-10-02 08:24:04",
						"mission_code" => "BNPP-201510-MCA",
					], [
						'id' => 929,
						'user_id' => 1,
						'mission_id' => 9,
						'status' => 1,
						'validation' => 1,
						'start' => "2018-09-10",
						'end' => "2018-09-11",
						'startHalf' => 0,
						'endHalf' => 0,
						'days' => 2,
						"comments" => "Test",
						'broadcast_date' => "2018-09-12 16:41:48",
						'created_at' => "2018-09-12 16:42:23",
						'updated_at' => "2018-10-02 08:24:04",
						'mission_code' => "BNPP-201510-MCA",
					], [
						'id' => 930,
						'user_id' => 1,
						'mission_id' => 9,
						'status' => 1,
						'validation' => 1,
						'start' => "2018-09-13",
						'end' => "2018-09-14",
						'startHalf' => 0,
						'endHalf' => 0,
						'days' => 2,
						"comments" => "Test",
						'broadcast_date' => "2018-09-12 16:41:48",
						'created_at' => "2018-09-12 16:42:23",
						'updated_at' => "2018-10-02 08:24:04",
						'mission_code' => "BNPP-201510-MCA",
					], [
						'id' => 933,
						'user_id' => 1,
						'mission_id' => 9,
						'status' => 1,
						'validation' => 1,
						'start' => "2018-09-18",
						'end' => "2018-09-18",
						'startHalf' => 1,
						'endHalf' => 0,
						'days' => "0.5",
						"comments" => "Test",
						'broadcast_date' => "2018-09-12 16:41:48",
						'created_at' => "2018-09-12 16:42:23",
						'updated_at' => "2018-10-02 08:24:04",
						'mission_code' => "BNPP-201510-MCA",
					], [
						'id' => 931,
						'user_id' => 1,
						'mission_id' => 9,
						'status' => 1,
						'validation' => 1,
						'start' => "2018-09-19",
						'end' => "2018-09-21",
						'startHalf' => 0,
						'endHalf' => 0,
						'days' => 3,
						"comments" => "Test",
						'broadcast_date' => "2018-09-12 16:41:48",
						'created_at' => "2018-09-12 16:42:23",
						'updated_at' => "2018-10-02 08:24:04",
						'mission_code' => "BNPP-201510-MCA",
					], [
						'id' => 932,
						'user_id' => 1,
						'mission_id' => 9,
						'status' => 1,
						'validation' => 1,
						'start' => "2018-09-24",
						'end' => "2018-09-28",
						'startHalf' => 0,
						'endHalf' => 0,
						'days' => 5,
						"comments" => "Test",
						'broadcast_date' => "2018-09-12 16:41:48",
						'created_at' => "2018-09-12 16:42:23",
						'updated_at' => "2018-10-02 08:24:04",
						'mission_code' => "BNPP-201510-MCA",
					],
				],
				'craData' => [
					"BNPP-201510-MCA" => [
						'code' => "BNPP-201510-MCA",
						'days' => "17.5",
						"comments" => "Test",
						'cras' => [
							[
								'id' => 928,
								'user_id' => 1,
								'mission_id' => 9,
								'status' => 1,
								'validation' => 1,
								'start' => "2018-09-03",
								'end' => "2018-09-07",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 5,
								"comments" => "Test",
								'broadcast_date' => "2018-09-12 16:41:48",
								'created_at' => "2018-09-12 16:42:23",
								'updated_at' => "2018-10-02 08:24:04",
								'mission_code' => "BNPP-201510-MCA",
							], [
								'id' => 929,
								'user_id' => 1,
								'mission_id' => 9,
								'status' => 1,
								'validation' => 1,
								'start' => "2018-09-10",
								'end' => "2018-09-11",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 2,
								"comments" => "Test",
								'broadcast_date' => "2018-09-12 16:41:48",
								'created_at' => "2018-09-12 16:42:23",
								'updated_at' => "2018-10-02 08:24:04",
								'mission_code' => "BNPP-201510-MCA",
							], [
								'id' => 930,
								'user_id' => 1,
								'mission_id' => 9,
								'status' => 1,
								'validation' => 1,
								'start' => "2018-09-13",
								'end' => "2018-09-14",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 2,
								"comments" => "Test",
								'broadcast_date' => "2018-09-12 16:41:48",
								'created_at' => "2018-09-12 16:42:23",
								'updated_at' => "2018-10-02 08:24:04",
								'mission_code' => "BNPP-201510-MCA",
							], [
								'id' => 933,
								'user_id' => 1,
								'mission_id' => 9,
								'status' => 1,
								'validation' => 1,
								'start' => "2018-09-18",
								'end' => "2018-09-18",
								'startHalf' => 1,
								'endHalf' => 0,
								'days' => "0.5",
								"comments" => "Test",
								'broadcast_date' => "2018-09-12 16:41:48",
								'created_at' => "2018-09-12 16:42:23",
								'updated_at' => "2018-10-02 08:24:04",
								'mission_code' => "BNPP-201510-MCA",
							], [
								'id' => 931,
								'user_id' => 1,
								'mission_id' => 9,
								'status' => 1,
								'validation' => 1,
								'start' => "2018-09-19",
								'end' => "2018-09-21",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 3,
								"comments" => "Test",
								'broadcast_date' => "2018-09-12 16:41:48",
								'created_at' => "2018-09-12 16:42:23",
								'updated_at' => "2018-10-02 08:24:04",
								'mission_code' => "BNPP-201510-MCA",
							], [
								'id' => 932,
								'user_id' => 1,
								'mission_id' => 9,
								'status' => 1,
								'validation' => 1,
								'start' => "2018-09-24",
								'end' => "2018-09-28",
								'startHalf' => 0,
								'endHalf' => 0,
								'days' => 5,
								"comments" => "Test",
								'broadcast_date' => "2018-09-12 16:41:48",
								'created_at' => "2018-09-12 16:42:23",
								'updated_at' => "2018-10-02 08:24:04",
								'mission_code' => "BNPP-201510-MCA",
							]
						]
					]
				],
			];
		}elseif($request['email'] == 'admindelete'){ // Notify Absence Delete to Admin/Manager
			$data = ['absence' => $absence, 'userData' => $user, 'by' => 'Admin', ];
		}elseif($request['email'] == 'newabsence'){ // Notify New Absence
			$data = ['absence' => new \App\Absences($absence), 'user' => $user, ];
		}elseif($request['email'] == 'forgot-password'){ // Forgot Password
			$data = ['token' => generateRandomStr(6), 'user' => $user, ];
		}
		try{
			$fileName = generateRandomStr(10) . '_' . time();
			File::put(resource_path('views/templates_store/') . $fileName . '.blade.php', htmlspecialchars_decode($request[$template]));
			View::make('templates_store.' . $fileName, $data)->render();
			File::delete(resource_path('views/templates_store/') . $fileName . '.blade.php');
			return true;
		}catch(\Exception $e){
			File::delete(resource_path('views/templates_store/') . $fileName . '.blade.php');
			$errors = explode('(View:', $e->getMessage());
			if(count($errors) > 1){
				$message = trans('messages.template_field');
				if($template == "subject"){ $message = trans('messages.subject_field'); }
				throw new \App\Exceptions\CustomException($errors[0] . ' ' . $message);
			}else{
				throw new \App\Exceptions\CustomException($e->getMessage());
			}
		}
	}
}