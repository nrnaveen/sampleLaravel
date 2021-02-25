<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, File;

class ContactController extends Controller {

	public function getIndex(Request $request){
		try{
			$contact = \App\Contact::orderBy('id', 'DESC')->paginate(15);
			return View('admin.contact.index', [
				'contact' => $contact,
				'title' => trans('messages.ContactManagement'),
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function deleteContact(Request $request, $id){
		try{
			$contact = \App\Contact::find($id);
			if($contact){
				$attachements = ['attachement_1', 'attachement_2'];
				foreach($attachements as $key => $attachement){
					if(isset($contact->$attachement)){
						File::delete(str_replace("/image/", "/image/thumbnail/", $contact->$attachement));
						File::delete($contact->$attachement);
					}
				}
				$contact->delete();
				return redirect('/admin/contact')->withMessage(trans("messages.ContactDeletedSuccessfully"));
			}
			return redirect('/admin/contact')->withError(trans('messages.ContactNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/contact')->withError($e->getMessage());
		}
	}
}