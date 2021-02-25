<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config;

class ClientsController extends Controller {

	protected $rules = [
		'lastname'	=> "required|max:255",
		'email'		=> "required|email|unique:clients",
		'color'		=> "required",
		'status'	=> "required|boolean",
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
			$query = new \App\Clients();
			if($search){ $query = $query->whereRaw("firstname LIKE ? OR lastname LIKE ? OR email LIKE ?", [$q, $q, $q]); }
			$clients = $query->orderBy($queryParam, $sort)->paginate(15);
			$clients->appends(request()->except(['page', '_token']));
			return View('admin.clients.index', [
				'clients' => $clients,
				'title' => trans('messages.ClientsManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function newClient(){
		try{
			$colors = Config::get('colors');
			return View('admin.clients.add', ['title' => trans("messages.AddClient"), 'colors' => $colors]);
		}catch(\Exception $e){
			return redirect('/admin/clients')->withError($e->getMessage());
		}
	}

	public function postNewClient(Request $request){
		try{
			$validator = Validator::make($request->all(), $this->rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['firstname', 'lastname', 'email', 'mobile', 'address', 'status', 'color']);
			$data['creation_date'] = date('Y-m-d H:i:s');
			$insert = \App\Clients::create($data);
			if($insert) return redirect('/admin/clients')->withMessage(trans("messages.ClientAddedSuccessfully"));
			else return redirect()->back()->withError(trans("messages.SomethingwentWrong"))->withInput();
		}catch(\Exception $e){
			return redirect('/admin/clients')->withError($e->getMessage());
		}
	}

	public function deleteClient(Request $request, $id){
		try{
			$client = \App\Clients::find($id);
			if($client){
				$missions = \App\Mission::where('client_id', $client->id)->get();
				if($missions->count() > 0){ return redirect('/admin/clients')->withError(trans("messages.SomethingwentWrong")); }
				else{
					$client->delete();
					return redirect('/admin/clients')->withMessage(trans("messages.ClientDeletedSuccessfully"));
				}
			}else return redirect('/admin/clients')->withError(trans('messages.ClientNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/clients')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$client = \App\Clients::find($id);
			if($client){
				$colors = Config::get('colors');
				return View('admin.clients.edit', ['client' => $client, 'title' => trans("messages.ClientEdit"), 'colors' => $colors]);
			}
			return redirect('/admin/clients')->withError(trans('messages.ClientNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/clients')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$rules = $this->rules;
			$rules['email'] .= ",email,$id";
			$validator = Validator::make($request->all(), $rules);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$client = \App\Clients::find($id);
			if($client){
				$data = $request->only(['firstname', 'lastname', 'email', 'mobile', 'address', 'status', 'color']);
				$insert = $client->update($data);
				if($insert) return redirect('/admin/clients')->withMessage(trans('messages.ClientUpdatedSuccessfully'));
				else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/clients')->withError(trans('messages.ClientNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/clients')->withError($e->getMessage());
		}
	}
}
