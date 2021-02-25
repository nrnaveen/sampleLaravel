{{trans('messages.Contact')}} <br><br>
{{trans('messages.Name')}}: {{$user->name}} <br>
{{trans('messages.Subject')}}: {{$data['subject']}} <br>
{{trans('messages.Description')}}: {{$data['description']}} <br>
{{trans('messages.Userlogin')}}: {{$user->email}} <br>
@if(in_array($data['web'], [true, 1, "1"]))
	{{trans('messages.Webbrowser')}}: {{$data['app_name']}} <br>
@else
	{{trans('messages.OSversion')}}: {{$data['app_name']}} <br>
@endif

{{trans('messages.ApplicationVersion')}}: {{$data['app_version']}} <br>