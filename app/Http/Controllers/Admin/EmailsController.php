<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Validator, DateTime, Config, File, View;

class EmailsController extends Controller {

	protected $templates = [];

	protected $rules = [
		'subject'	=> "required|min:10",
		'add_email'	=> "required",
		'email'		=> "required",
		'status'	=> "required|boolean",
		'template'	=> "required|min:10",
	];

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(){
		if(!File::isDirectory(resource_path('views/templates_store'))){ File::makeDirectory(resource_path('views/templates_store'), 0775); }
		$this->templates = \App\EmailTemplate::getTemplates();
		if(count($this->templates) > 0){
			$this->rules['email'] = "required|in:" . implode(',', array_keys($this->templates));
		}
	}

	public function getIndex(Request $request){
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
			$query = new \App\EmailTemplate();
			if($search){ $query = $query->whereRaw("email LIKE ? OR status LIKE ?", [$q, $q]); }
			$emails = $query->orderBy($queryParam, $sort)->paginate(15);
			$emails->appends(request()->except(['page', '_token']));
			return View('admin.emails.index', [
				'emails' => $emails,
				'title' => trans('messages.EmailManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function getNew(){
		try{
			$templates = \App\EmailTemplate::getTemplates();
			return View('admin.emails.add', ['title' => trans("messages.AddMail"), 'templates' => $templates]);
		}catch(\Exception $e){
			return redirect('/admin/emails')->withError($e->getMessage());
		}
	}

	public function deleteEmail(Request $request, $id){
		try{
			$mail = \App\EmailTemplate::find($id);
			if($mail){
				$mail->delete();
				return redirect('/admin/emails')->withMessage(trans("messages.MailDeletedSuccessfully"));
			}else return redirect('/admin/emails')->withError(trans('messages.MailNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/emails')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$mail = \App\EmailTemplate::find($id);
			if($mail){
				$templates = \App\EmailTemplate::getTemplates();
				return View('admin.emails.edit', ['mail' => $mail, 'title' => trans("messages.EditMail"), 'templates' => $templates]);
			}
			return redirect('/admin/emails')->withError(trans('messages.MailNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/emails')->withError($e->getMessage());
		}
	}

	public function postNew(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['subject', 'email', 'status', 'template', 'add_email', 'cc_email']);
			$checkTemplate = \App\EmailTemplate::checkTemplate($data);
			$checkSubject = \App\EmailTemplate::checkTemplate($data, 'subject');
			if($checkTemplate && $checkSubject){
				$insert = \App\EmailTemplate::create($data);
				if($insert) return redirect('/admin/emails')->withMessage(trans("messages.MailAddedSuccessfully"));
				else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
			}
		}catch(\App\Exceptions\CustomException $e){
			return redirect()->back()->withError($e->getMessage())->withInput();
		}catch(\Exception $e){
			return redirect()->back()->withError($e->getMessage())->withInput();
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$rules = $this->rules;
			$rules['email'] .= ",email,$id";
			$validator = Validator::make($request->all(), $rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$mail = \App\EmailTemplate::find($id);
			if($mail){
				$data = $request->only(['subject', 'email', 'status', 'template', 'add_email', 'cc_email']);
				$checkTemplate = \App\EmailTemplate::checkTemplate($data);
				$checkSubject = \App\EmailTemplate::checkTemplate($data, 'subject');
				if($checkTemplate && $checkSubject){
					$insert = $mail->update($data);
					if($insert) return redirect('/admin/emails')->withMessage(trans('messages.MailUpdatedSuccessfully'));
					else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
				}
			}else return redirect('/admin/emails')->withError(trans('messages.MailNotFound'));
		}catch(\App\Exceptions\CustomException $e){
			return redirect()->back()->withError($e->getMessage())->withInput();
		}catch(\Exception $e){
			return redirect()->back()->withError($e->getMessage())->withInput();
		}
	}
}