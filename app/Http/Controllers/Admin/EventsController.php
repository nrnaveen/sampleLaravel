<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime, Validator, Auth, File, Image, Config, Mail, DbView;
use Carbon\Carbon;

class EventsController extends Controller {
	
	protected $rules = [
		'label'	=> "required",
		'date'	=> "required|date_format:d/m/Y|after_or_equal:today",
		'status'=> "required|boolean",
	];

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
			$query = \App\Events::where('date', '>', date("Y-m-d"));
			if($search){ $query = $query->whereRaw("label LIKE ? OR date LIKE ? OR status LIKE ?", [$q, $q, $q]); }
			$events = $query->orderBy($queryParam, $sort)->paginate(15);
			$events->appends(request()->except(['page', '_token']));
			return View('admin.events.index', [
				'events' => $events,
				'title' => trans('messages.EventsManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function newEvent(){
		try{
			return View('admin.events.add', ['title' => trans("messages.AddEvent"),]);
		}catch(\Exception $e){
			return redirect('/admin/events')->withError($e->getMessage());
		}
	}

	public function postEvent(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['label', 'date', 'description', 'status']);
			$data['date'] = Carbon::createFromFormat('d/m/Y', $data['date'])->format('Y-m-d');
			$insert = \App\Events::create($data);
			if($insert) return redirect('/admin/events')->withMessage(trans("messages.YourRequestSavedSuccessFully"));
			return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/events')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$event = \App\Events::find($id);
			if($event){
				$event->date = date('d/m/Y', strtotime($event->date));
				return View('admin.events.edit', ['event' => $event, 'title' => trans('messages.EventEdit'),]);
			}
			else return redirect('/admin/events')->withError(trans('messages.EventNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/events')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$event = \App\Events::find($id);
			if($event){
				$validator = Validator::make($request->all(), $this->rules);
				if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
				$data = $request->only(['label', 'date', 'description', 'status']);
				$data['date'] = Carbon::createFromFormat('d/m/Y', $data['date'])->format('Y-m-d');
				$insert = $event->update($data);
				if($insert) return redirect('/admin/events')->withMessage(trans('messages.EventUpdatedSuccessfully'));
				else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/events')->withError(trans('messages.EventNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/events')->withError($e->getMessage());
		}
	}

	public function deleteEvent(Request $request, $id){
		try{
			$validator = Validator::make($request->all(), ['_method' => "required|in:DELETE,delete", ]);
			if($validator->fails()){
				return redirect()->back()->withError(trans('messages.SomethingwentWrong'));
			}
			$event = \App\Events::find($id);
			if($event){
				$event->delete();
				return redirect('/admin/events')->withMessage(trans('messages.EventDeletedSuccessfully'));
			}
			return redirect('/admin/events')->withError(trans('messages.EventDataNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/events')->withError($e->getMessage());
		}
	}

}