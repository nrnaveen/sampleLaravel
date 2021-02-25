<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\GApi;
use Validator, Auth, File, Image, Mail, DbView;

use Google_Service_Directory, Google_Client;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Library\V1\PhotosLibraryResourceFactory;
use Google_Service_Directory_Resource_UsersPhotos, Google_Service_Directory_UserPhoto;

class GoogleController extends Controller {

	public function getIndex(Request $request){
		$client = GApi::getClient();
		if($request->has('code')){
			$token_code = $request->get('code');
			$client->authenticate($token_code);
			$access_token = $client->getAccessToken();
			if($access_token){
				$client->setAccessToken($access_token);
				GApi::whereNotNull('id')->delete();
				GApi::create([
					'access_token' => $access_token['access_token'],
					'expires_in' => $access_token['expires_in'],
					'refresh_token' => $access_token['refresh_token'],
					'scope' => $access_token['scope'],
					'token_type' => $access_token['token_type'],
					'id_token' => $access_token['id_token'],
					'created' => $access_token['created'],
				]);
				return redirect()->to('/');
			}
		}
		$authUrl = $client->createAuthUrl();
		return redirect()->to($authUrl);
	}

	public function getRefresh(Request $request){
		$client = GApi::getClient();
		$token = GApi::orderBy('created_at', 'desc')->first();
		if($token){
			$client->setAccessToken($token->toArray());
			$isExpired = $client->isAccessTokenExpired();
			if($isExpired){
				$client->refreshToken($token->refresh_token);
				$access_token = $client->getAccessToken();
				$token->update([
					'access_token' => $access_token['access_token'],
					'expires_in' => $access_token['expires_in'],
					'refresh_token' => $access_token['refresh_token'],
					'scope' => $access_token['scope'],
					'token_type' => $access_token['token_type'],
					'id_token' => $access_token['id_token'],
					'created' => $access_token['created'],
				]);
			}
			return redirect()->to('/');
		}
	}

	public function getUsers(Request $request){
		try{
			$emails = GApi::getGoogleUsers();
			echo "<pre>";print_r($emails);exit;
			$client = GApi::getClientWithToken();
			if($client){
				$dir = new Google_Service_Directory($client);
				$list = $dir->users->listUsers(array('domain' => 'bi-consulting.com', 'maxResults' => 50));
				$emails = [];
				$nextPageToken = $list->nextPageToken;
				while($nextPageToken != null){
					foreach($list->users as $ukey => $listUser){
						foreach($listUser->emails as $ekey => $listEmail){
							if(isset($listEmail['address'])){ $emails[] = $listEmail['address']; }
						}
					}
					$list = $dir->users->listUsers(array('domain' => 'bi-consulting.com', 'maxResults' => 50, 'pageToken' => $nextPageToken));
					$nextPageToken = $list->nextPageToken;
				}
				echo "<pre>";print_r($emails);exit;
				// /*$list = $dir->users->get('103357362484974372291');
				// echo "<pre>";print_r($list);*/
				// $path = 'assets/img/images.jpg';
				// $type = pathinfo($path, PATHINFO_EXTENSION);
				// $data = file_get_contents($path);
				// // $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
				// $base64 = strtr(base64_encode($data), '/+', '_-');
				// // $base64 = strtr(rtrim(base64_encode($data), '='), '/+', '_-');
				// list($width, $height) = getimagesize($path);
				// $userObj = new Google_Service_Directory_UserPhoto([
				// 	'photoData' =>  $base64,
				// 	'mimeType' => 'image/' . $type,
				// 	'width' => $width,
				// 	'height' => $height,
				// 	/*'id' => $list->id,
				// 	'primaryEmail' => $list->primaryEmail,
				// 	'etag' => $list->etag,
				// 	'kind' => $list->kind,*/
				// ]);
				// $list = $dir->users_photos->update('103357362484974372291', $userObj);
				// echo "<pre>";print_r($list);
				// // $list = $dir->users_photos->get('103357362484974372291');
				// // echo "<pre>";print_r($dir->users_photos);
			}
		}catch(\Exception $e){
			echo "<pre>";print_r($e->getMessage());
		}
	}
}