<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail, DbView, Config;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class CronController extends Controller {

	public function sendCRAMail(){
	try {
		$monthdate = Carbon::createFromDate(date("Y"), date("m"), 21);
		$workingDate = getWorkingDate($monthdate->format("d/m/Y"));
		// To check whether entry has been created today
        $mailActivityLog = \App\MailActivityLog::where('action', 'cra_reminder_users')->orderBy('created_at', 'desc')->first();
        // If the dates match, that means cron is repeating
        if ($mailActivityLog->created_at->format('d/m/Y') == date('d/m/Y')) {
        	\App\MailActivityLog::create(['user_id' => 9999, 'object' => 'CRA&Absences', 'action' => 'reminder_mails_repeat', 'data' => 'Cron Mail Sending Repeated.',]);
        } else { 
		// if(date("d/m/Y") >= $workingDate){ // Every days
		if(date("d/m/Y") >= $workingDate && (!Carbon::now()->isSunday() && !Carbon::now()->isSaturday())){ // Only Weekdays
			$start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
			$end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
			$month = Carbon::now()->formatLocalized("%B %Y");
			$users = \App\User::where('status', true)->get();
			$date = Carbon::createFromFormat('d/m/Y', $workingDate)->format("Y-m-d");
			$craMonth = Carbon::createFromFormat('d/m/Y', $workingDate)->format("d-m-Y");
			$craMonthStr = \App\CRA::$months[Carbon::createFromFormat('d/m/Y', $workingDate)->format('n') - 1] . ' ' . Carbon::createFromFormat('d/m/Y', $workingDate)->format('Y');
			$absences = \App\Absences::whereRaw('start <= ? AND end >= ?', [$end_date, $start_date])->get();
			$cras = \App\CRA::whereRaw('start <= ? AND end >= ?', [$end_date, $start_date])->get();
			$emails = [];
			foreach($users as $key => $user){
				$workingDates = [];
				$startDate = Carbon::now()->startOfMonth();
				$endDate = Carbon::now()->endOfMonth();
				for($ldate = $startDate; $ldate->lte($endDate); $ldate->addDay()){
					if(!in_array($ldate->format('d/m/Y'), Config::get('leave_dates')) && !$ldate->isSunday() && !$ldate->isSaturday()){
						$workingDates[] = $ldate->format('Y-m-d');
					}
				}
				$userAbsences = $absences->filter(function($item) use($user){ return ($item->user_id == $user->id); });
				$userCras = $cras->filter(function($item) use($user){ return ($item->user_id == $user->id); });
				foreach([$userCras, $userAbsences] as $k1 => $value){
					foreach($value as $key => $absence){
						$absenceStart_date = Carbon::createFromFormat('Y-m-d', $absence->start);
						$absenceEnd_date = Carbon::createFromFormat('Y-m-d', $absence->end);
						for($ldate = $absenceStart_date; $ldate->lte($absenceEnd_date); $ldate->addDay()){
							$absenceDate = $ldate->format('Y-m-d');
							if(in_array($absenceDate, $workingDates)){
								$key = array_search($absenceDate, $workingDates);
								// Absences validation(checking if validation key is null as it doesn't exist in absences collection)
								if(isset($workingDates[$key]) && is_null($absence->validation)) { unset($workingDates[$key]); }
								// CRA validation(checking if he filled validation as well as that entries are validated)
								if(isset($workingDates[$key]) && $absence->validation) { unset($workingDates[$key]); }
							}
						}
					}
				}
				if(count($workingDates) > 0){ $emails[] = $user->email; }
			}
			$emails = array_unique($emails);
			$managers = \App\UserManager::all();
			$craUsers = $users->whereIn('email', $emails);
			$mail = \App\EmailTemplate::where('email', 'entercra')->where('status', true)->first();
			$subject = trans('messages.EnterCRAOfDay', ['date' => $date]);
			$yesText = trans('messages.Yes');
			$noText = trans('messages.No');
			$formatText = '%Y-%m-%d %H:%M:%S';
			$currentDate = date("Y-m-d");
			$userIdAndEmail = [];
			foreach($craUsers as $key => $craUser){
				$newMails = [$craUser->email];
				$manager = $managers->where('consultant_id', $craUser->id)->first();
				if($mail){
					$mails = [];
					$ccmails = [];
					$failedMails = [];
					if(!is_null($mail->add_email)){
						$amails = explode(',', $mail->add_email);
						foreach($amails as $key => $amail){
							$amail = trim($amail);
							if(!filter_var($amail, FILTER_VALIDATE_EMAIL)){
								if($amail == '{{$user->email}}'){ $mails[] = $craUser->email; }
								if($amail == '{{$manager->email}}' && $manager && $manager->manager){ $mails[] = $manager->manager->email; }
								if($amail == '{{$admin->email}}' && !empty(env('ADMIN_MAIL', null))){ $mails[] = env('ADMIN_MAIL'); }
							}else{ $mails[] = $amail; }
						}
					}
					if(!is_null($mail->cc_email)){
						$cmails = explode(',', $mail->cc_email);
						foreach($cmails as $key => $cmail){
							$cmail = trim($cmail);
							if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
								if($cmail == '{{$user->email}}'){ $ccmails[] = $craUser->email; }
								if($cmail == '{{$manager->email}}' && $manager && $manager->manager){ $ccmails[] = $manager->manager->email; }
								if($cmail == '{{$admin->email}}' && !empty(env('ADMIN_MAIL', null))){ $ccmails[] = env('ADMIN_MAIL'); }
							}else{ $ccmails[] = $cmail; }
						}
					}
					$mails = array_unique($mails);
					$ccmails = array_unique($ccmails);
					$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'date' => $currentDate, 'month' => $month, 'craMonth' => $craMonth, 'craMonthStr' => $craMonthStr, 'user' => $craUser])->render();
					$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'date' => $currentDate, 'month' => $month, 'craMonth' => $craMonth, 'craMonthStr' => $craMonthStr, 'user' => $craUser])->render();
					$userIdAndEmail[] = ['id' => $craUser->id, 'email' => $mails, 'ccmail' => $ccmails];
					Mail::raw($template, function($m) use($newMails, $date, $subject, $template, $mails, $ccmails){ self::sendCRAMailAction($m, $newMails, $date, $subject, $template, $mails, $ccmails); });
					sleep(6);
					if (count(Mail::failures()) > 0) {
						$failedMails = Mail::failures();
						\App\MailActivityLog::create(['user_id' => $craUser->id, 'object' => 'CRA&Absences', 'action' => 'mail_send_failure', 'data' => json_encode(['status' => 0, 'email' => $failedMails, 'add_email' => $mails, 'ccmails' => $ccmails]),]);
					}else{
						\App\MailActivityLog::create(['user_id' => $craUser->id, 'object' => 'CRA&Absences', 'action' => 'mail_send_success', 'data' => json_encode(['status' => 1, 'email' => $newMails, 'add_email' => $mails, 'ccmails' => $ccmails]),]);
					}
				}else{
					Mail::send('emails.entercra', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'date' => $currentDate, 'month' => $month, 'craMonth' => $craMonth, 'craMonthStr' => $craMonthStr, 'user' => $craUser], function($m) use($newMails, $date, $subject){ self::sendCRAMailAction($m, $newMails, $date, $subject); });
					sleep(6);
				}
			}
		}
		\App\MailActivityLog::create(['user_id' => null, 'object' => 'CRA&Absence', 'action' => 'cra_reminder_users', 'data' => json_encode($userIdAndEmail),]);
		return redirect('/');
	}
	}catch(\Exception $e){
		\App\MailActivityLog::create(['user_id' => null, 'object' => 'CRA&Absences', 'action' => 'exception', 'data' => json_encode(['error' => $e->getFile().'('.$e->getLine().')-'.$e->getMessage()]),]);
	}
		
	}

	public static function sendCRAMailAction($m, $emails, $date, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($emails)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}

	public function testMail(){
		Mail::raw('Developer', function($m){
			$m->to("admin@sample.com")->subject("Test Mail");
		});
	}
}
