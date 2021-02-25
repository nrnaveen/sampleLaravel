@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.MailsManagement')}} <small>{{trans('messages.DetailsofRemovedEmails')}}</small></h1></br>
	<form class="delete-form" method="POST" action="{{ url('/admin/deleted-mails/cache')}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
		{!! Form::token() !!}
		<button class="btn btn-danger">{{trans('messages.ClearCache')}}</button>
	</form>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-minus"></i> {{trans('messages.MailsManagement')}}</li>
	</ol>
@stop

@section('content')
	<div class="box">
		<div class="box-header">
			<h3 class="box-title"></h3>
		</div>
		<div class="box-body table-responsive no-padding">
			<table class="table table-hover">
				<thead>
					<tr class="row text-center">
						<th>
							<a href="{{urlWithQuery('/admin/deleted-mails', ['query' => 'role', 'sort' => ($queryParam == 'role' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Role') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/deleted-mails', ['query' => 'firstname', 'sort' => ($queryParam == 'firstname' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Name') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/deleted-mails', ['query' => 'email', 'sort' => ($queryParam == 'email' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Email') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/deleted-mails', ['query' => 'status', 'sort' => ($queryParam == 'status' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Status') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$users->isEmpty())
						@foreach($users as $user)
							<tr class="row">
								<td>{{$user->roleText}}</td>
								<td>{{$user->name}}</td>
								<td>{{$user->email}}</td>
								<td>{{$user->status}}</td>
								<td>
									<form class="delete-form" method="POST" action="{{ url('/admin/users/disable', [ 'id' => $user->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
										{!! Form::token() !!}
										<button @if(!$user->status) disabled @endif class="btn btn-danger">{{ trans('messages.Disable') }}</button>
									</form>
									<!-- <form class="delete-form" method="POST" action="{{ url('/admin/users/delete', [ 'id' => $user->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
										{!! Form::token() !!}
										<input type="hidden" name="_method" value="DELETE">
										<button class="btn btn-danger">
											<i class="fa fa-trash-o fa-lg"></i>
										</button>
									</form> -->
								</td>
							</tr>
						@endforeach
					@else
						<td colspan="5" class="text-center">{{trans('messages.Norecordsfound')}}</td>
					@endif
				</tbody>
			</table>
			{{ $users->render() }}
		</div>
	</div>
@stop