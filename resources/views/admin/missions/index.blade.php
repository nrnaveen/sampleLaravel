@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.MissionsManagement')}} <small>{{trans('messages.DetailsofMission')}}</small></h1></br>
	<a class="btn btn-primary" href="{{url('/admin/missions/create')}}">{{trans('messages.AddMission')}}</a>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.MissionsManagement')}}</li>
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
							<a href="{{urlWithQuery('/admin/missions', ['query' => 'client_id', 'sort' => ($queryParam == 'client_id' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Client') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/missions', ['query' => 'code', 'sort' => ($queryParam == 'code' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Code') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/missions', ['query' => 'label', 'sort' => ($queryParam == 'label' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Label') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/missions', ['query' => 'order', 'sort' => ($queryParam == 'order' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Order') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$missions->isEmpty())
						@foreach($missions as $mission)
							<tr>
								<td>{{$mission->client->name}}</td>
								<td>{{$mission->code}}</td>
								<td>{{$mission->label}}</td>
								<td>{{$mission->order}}</td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/missions/edit', [ 'id' => $mission->id, ])}}">
										<i class="fa fa-edit fa-lg"></i>
									</a>
									<form class="delete-form" method="POST" action="{{ url('/admin/missions/delete', [ 'id' => $mission->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
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
			{{ $missions->render() }}
		</div>
	</div>
@stop