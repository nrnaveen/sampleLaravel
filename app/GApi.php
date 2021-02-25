<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache;

use Google_Service_Directory, Google_Client;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Library\V1\PhotosLibraryResourceFactory;

class GApi extends Model {
	
	protected $table = 'gapi';

	protected $fillable = [
		'id',
		'access_token',
		'expires_in',
		'refresh_token',
		'scope',
		'token_type',
		'id_token',
		'created',
		'created_at',
		'updated_at',
	];

	public static function getClient(){
		$google_redirect_url = url('google');
		$client = new Google_Client();
		$client->setApplicationName(config('services.google.app_name'));
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		$client->setClientId(config('services.google.client_id'));
		$client->setClientSecret(config('services.google.client_secret'));
		$client->setRedirectUri($google_redirect_url);
		$client->setIncludeGrantedScopes(true);
		$client->addScope([
			'https://www.googleapis.com/auth/plus.me',
			'https://www.googleapis.com/auth/userinfo.email',
			'https://www.googleapis.com/auth/userinfo.profile',
			Google_Service_Directory::ADMIN_DIRECTORY_USER,
			Google_Service_Directory::ADMIN_DIRECTORY_CUSTOMER,
			Google_Service_Directory::ADMIN_DIRECTORY_DOMAIN,
			Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
			Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER,
			Google_Service_Directory::ADMIN_DIRECTORY_USER,
			Google_Service_Directory::ADMIN_DIRECTORY_USER_ALIAS,
			Google_Service_Directory::ADMIN_DIRECTORY_USER_SECURITY,
			Google_Service_Directory::ADMIN_DIRECTORY_USERSCHEMA,
		]);
		return $client;
	}

	public static function getClientWithToken(){
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
				$client->setAccessToken($access_token);
			}
			return $client;
		}
		return null;
	}

	public static function getGoogleUsers(){
		$client = GApi::getClientWithToken();
		$emails = [];
		if($client){
			if(!Cache::has('emails')){
				$dir = new Google_Service_Directory($client);
				$list = $dir->users->listUsers(array('domain' => 'bi-consulting.com', 'maxResults' => 50));
				$nextPageToken = $list->nextPageToken;
				function getEmails($list, &$emails){
					foreach($list->users as $ukey => $listUser){
						foreach($listUser->emails as $ekey => $listEmail){
							if(isset($listEmail['address'])){ $emails[] = $listEmail['address']; }
						}
					}
				}
				while($nextPageToken != null){
					getEmails($list, $emails);
					$list = $dir->users->listUsers(array('domain' => 'bi-consulting.com', 'maxResults' => 50, 'pageToken' => $nextPageToken));
					$nextPageToken = $list->nextPageToken;
				}
				getEmails($list, $emails);
				Cache::put('emails', $emails, 60);
			}
			$emails = Cache::get('emails');
		}
		return $emails;
	}
}