<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, File, Cache;

class MailsController extends Controller {

	public function getIndex(Request $request){
		try{
			$emails = \App\GApi::getGoogleUsers();
			// $users = \App\User::all(); // Check All mails
			$emails = array_map('strtolower', $emails);
			$users = \App\User::whereRaw("email LIKE ?", ['%bi-consulting.com%'])->get(); // Only bi-consulting.com
			foreach($users as $key => $user){
				if(in_array(strtolower($user->email), $emails)){ $users->forget($key); }
			}
			$bounceMails = \App\BounceEmail::where('type', 'Bounce')->get();
			// $removedMails = $users->pluck('email')->merge($bounceMails->pluck('email'));
			$removedMails = $bounceMails->pluck('email');
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
			$users = \App\User::whereIn('email', $removedMails)->orderBy($queryParam, $sort)->paginate(15);
			//Checking for default queryparam and not link to pagination if url params are empty
			if($queryParam != "id"){
				$users->appends(['query' => $queryParam, 'sort' => $sort])->links();
			}
			return View('admin.mails.index', [
				'users' => $users,
				'title' => trans('messages.ContactManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function postClearCache(Request $request){
		Cache::flush();
		return redirect('/admin/deleted-mails')->withMessage(trans('messages.CacheClearedSuccessfully'));
	}

}