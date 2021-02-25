<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Session, Validator;
use App\Admin;

class ContentController extends Controller {

	public function getIndex(){
		try{
			$title = trans("messages.HomeGreeting");
			$homecontent = \App\HomeContent::first();
			if(!$homecontent){ $homecontent = new \App\HomeContent(); }
			return view('admin.homecontent', compact('title', 'homecontent'));
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function postIndex(Request $request){
		try{
			$validator = Validator::make($request->all(), ['content' => 'required',]);
			if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
			$data = $request->only(['content',]);
			$homecontent = \App\HomeContent::first();
			if($homecontent){ $insert = $homecontent->update($data); }
			else{ $insert = \App\HomeContent::create($data); }
			if($insert) return redirect('/admin/homecontent')->withMessage(trans("messages.UpdatedSuccessfully"));
			else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}
}