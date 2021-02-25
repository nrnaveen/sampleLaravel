@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.ClientsManagement')}} <small>{{trans('messages.DetailsofClient')}}</small></h1></br>
	<a class="btn btn-primary" href="{{url('/admin/clients/add')}}">{{trans('messages.AddClient')}}</a>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user-secret"></i> {{trans('messages.ClientsManagement')}}</li>
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
							<a href="{{urlWithQuery('/admin/clients', ['query' => 'firstname', 'sort' => ($queryParam == 'firstname' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.FirstName') }}</i>
							</a>
						</th> 
						 <th>
							<a href="{{urlWithQuery('/admin/clients', ['query' => 'lastname', 'sort' => ($queryParam == 'lastname' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Name') }}</i>
							</a>
						 </th> 
						<th>
							<a href="{{urlWithQuery('/admin/clients', ['query' => 'email', 'sort' => ($queryParam == 'email' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Email') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/clients', ['query' => 'color', 'sort' => ($queryParam == 'color' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Color') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$clients->isEmpty())
						@foreach($clients as $client)
							<tr>
								<td>{{$client->firstname}}</td>
								<td>{{$client->lastname}}</td>
								<td>{{$client->email}}</td>
								<td><div style="background: {{$client->color}};width: 50px;height: 25px;border-radius: 10px;"></div></td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/clients/edit', [ 'id' => $client->id, ])}}">
										<i class="fa fa-edit fa-lg"></i>
									</a>
									<form class="delete-form" method="POST" action="{{ url('/admin/clients/delete', [ 'id' => $client->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
										{!! Form::token() !!}
										<input type="hidden" name="_method" value="DELETE">
										<button class="btn btn-danger">
											<i class="fa fa-trash-o fa-lg"></i>
										</button>
									</form>
								</td>
							</tr>
						@endforeach
					@else
						<td colspan="5" class="text-center">{{trans('messages.Norecordsfound')}}</td>
					@endif
				</tbody>
			</table>
			{{ $clients->render() }}
		</div>
	</div>
@stop