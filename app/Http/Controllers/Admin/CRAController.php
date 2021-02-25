<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Mail, DbView;

class CRAController extends Controller {

	public function index(Request $request){
		try{
			$queryParam = 'id';
			$sort = 'DESC';
			$search = null;
			$q = null;
			if($request->has('query')){ $queryParam = $request->get('query'); }
			if($request->has('sort')){ $sort = $request->get('sort'); }
			if($request->has('q')){
				$search = $request->get('q');
				$q = '%' . $request->get('q') . '%';
			}
			$query = new \App\CRA();
			if($search){ $query = $query->whereRaw("start LIKE ? OR end LIKE ? OR days LIKE ? OR comments LIKE ? OR broadcast_date LIKE ?", [$q, $q, $q, $q, $q]); }
			$cras = $query->orderBy($queryParam, $sort)->paginate(15);
			$cras->appends(request()->except(['page', '_token']));
			return View('admin.cra.index', [
				'cras' => $cras,
				'title' => trans('messages.CraManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$cra = \App\CRA::find($id);
			if($cra) return View('admin.cra.edit', ['cra' => $cra, 'title' => trans('messages.CraEdit'),]);
			else return redirect('/admin/cras')->withError(trans('messages.CRANotFound'));
		}catch(\Exception $e){
			return redirect('/admin/cras')->withError($e->getMessage());
		}
	}

	public function deleteCra(Request $request, $id){
		try{
			$cra = \App\CRA::find($id);
			if($cra){
				\App\ActivityLog::create(['user_id' => $cra->user_id, 'object' => 'CRA', 'action' => 'delete by Admin', 'data' => json_encode($cra->toArray()),]);
				$cra->delete();
				return redirect('/admin/cras')->withMessage(trans("messages.CraDeletedSuccessfully"));
			}else return redirect('/admin/cras')->withError(trans('messages.CraNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/cras')->withError($e->getMessage());
		}		
	}

	public function excelExport(Request $request){
		try{
			$cras = \App\CRA::orderBy('id', 'DESC')->get();
			$emails = [];
			foreach($cras as $key => $value){
				$value->days = getDaysCount($value->start, $value->end, $value->startHalf, $value->endHalf);
				$uservalue = \App\User::where('id', $value->user_id)->first();
				$value->firstname = $uservalue->firstname;
				$value->lastname = $uservalue->lastname;
				$mission = \App\Mission::where('id', $value->mission_id)->first();
				$value->mission_label = $mission->label;
				$emails[] = $uservalue->email;
			}
			$emails = array_unique($emails);
			$mail = \App\EmailTemplate::where('email', 'exported')->where('status', true)->first();
			$subject = trans('messages.YourDataExported', ['type' => 'CRA', 'by' => 'Admin']);
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
							if($amail == '{{$user->email}}'){
								foreach($emails as $key => $email){ $mails[] = $email; }
							}
						}else{ $mails[] = $amail; }
					}
				}
				if(!is_null($mail->cc_email)){
					$cmails = explode(',', $mail->cc_email);
					foreach($cmails as $key => $cmail){
						$cmail = trim($cmail);
						if(!filter_var($cmail, FILTER_VALIDATE_EMAIL)){
							if($cmail == '{{$user->email}}'){
								foreach($emails as $key => $email){ $ccmails[] = $email; }
							}
						}else{ $ccmails[] = $cmail; }
					}
				}
				$mails = array_unique($mails);
				$ccmails = array_unique($ccmails);
				$template = DbView::make($mail)->field('decodeTemplate')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'type' => 'CRA', 'by' => 'Admin',])->render();
				$subject = DbView::make($mail)->field('decodeSubject')->with(['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'type' => 'CRA', 'by' => 'Admin',])->render();
				Mail::raw($template, function($m) use($emails, $subject, $template, $mails, $ccmails){ self::excelExportAction($m, $emails, $subject, $template, $mails, $ccmails); });
			}else{
				Mail::send('emails.exported', ['date_formatText' => $formatText, 'text_yes' => $yesText, 'text_no' => $noText, 'type' => 'CRA', 'by' => 'Admin',], function($m) use($emails, $subject){ self::excelExportAction($m, $emails, $subject); });
			}
			$datasheet = array();
			$datasheet[0] = array('id', 'firstname', 'lastname', 'mission_name', 'status', 'start', 'end', 'startHalf', 'endHalf', 'days', 'comments', 'broadcast_date',);
			foreach($cras as $key => $datanew){
				$datasheet[$key + 1] = array($datanew['id'],
					$datanew['firstname'],
					$datanew['lastname'],
					$datanew['mission_label'],
					$datanew['status'],
					$datanew['start'],
					$datanew['end'],
					$datanew['startHalf'],
					$datanew['endHalf'],
					$datanew['days'],
					$datanew['comments'],
					$datanew['broadcast_date'],
				);
			}
			return Excel::create('cras', function($excel) use($datasheet){
				return $excel->sheet('cra', function($sheet) use($datasheet){
					$sheet->fromArray($datasheet);
				});
			})->download('xlsx');
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}

	public static function excelExportAction($m, $emails, $subject, $template = null, $add_email = [], $cc_email = []){
		if(count($add_email) > 0){ $mail = $m->to($add_email)->subject($subject); }
		else{ $mail = $m->to($emails)->subject($subject); }
		if(count($cc_email) > 0){ $mail->bcc($cc_email); }
		if($template != null){ $mail->setBody($template, 'text/html'); }
	}
}