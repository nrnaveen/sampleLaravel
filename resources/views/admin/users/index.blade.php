@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.UsersManagement')}} <small>{{trans('messages.DetailsofUser')}}</small></h1></br>
	<a class="btn btn-primary" href="{{url('/admin/users/add')}}">{{trans('messages.AddUser')}}</a>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.UsersManagement')}}</li>
	</ol>
@stop

@section('content')
	<div class="box">
		<div class="box-header">
			<h3 class="box-title"></h3>
			<div class="box-tools col">
				<form>
					<div class="input-group input-group-sm col-xs-12 col-md-4 pull-right">
						<input type="text" name="q" class="form-control pull-right" placeholder="{{trans('messages.Search')}}" value="{{$search}}">
						<div class="input-group-btn">
							<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="box-body table-responsive no-padding">
			<table class="table table-hover">
				<thead>
					<tr style="text-align:center">
						<th>
							<a href="{{urlWithQuery('/admin/users', ['query' => 'role', 'sort' => ($queryParam == 'role' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Role') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/users', ['query' => 'firstname', 'sort' => ($queryParam == 'firstname' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Name') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/users', ['query' => 'email', 'sort' => ($queryParam == 'email' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Email') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$users->isEmpty())
						@foreach($users as $user)
							<tr>
								<td>{{$user->roleText}}</td>
								<td>{{$user->name}}</td>
								<td>{{$user->email}}</td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/users/edit', ['id' => $user->id])}}">
										<i class="fa fa-edit fa-lg"></i>
									</a>
									<form class="delete-form" method="POST" action="{{ url('/admin/users/delete', [ 'id' => $user->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
										{!! Form::token() !!}
										<input type="hidden" name="_method" value="DELETE">
										<button class="btn btn-danger">
											<i class="fa fa-trash-o fa-lg"></i>
										</button>
									</form>
									<a class="btn btn-primary" href="{{ url('/admin/users/' . $user->id . '/missions')}}">
										<i class="fa fa fa-user fa-lg"></i>
									</a>
									@if($user->role == 'manager')
										<a class="btn btn-primary" href="{{ url('/admin/users/' . $user->id . '/consultants')}}">
											<i class="fa fa fa-users fa-lg"></i>
										</a>
									@endif
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