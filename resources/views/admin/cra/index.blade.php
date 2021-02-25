@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.CraManagement')}}<small>{{trans('messages.DetailsOfCra')}}</small></h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.CraManagement')}}</li>
	</ol>
	<a class="btn btn-primary" href="{{url('/admin/cras/export')}}">{{trans('messages.ExcelExport')}}</a>
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
							<a href="{{urlWithQuery('/admin/cras', ['query' => 'user_id', 'sort' => ($queryParam == 'user_id' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.UserName') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/cras', ['query' => 'start', 'sort' => ($queryParam == 'start' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.StartDate') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/cras', ['query' => 'end', 'sort' => ($queryParam == 'end' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.EndDate') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/cras', ['query' => 'days', 'sort' => ($queryParam == 'days' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Days') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/cras', ['query' => 'comments', 'sort' => ($queryParam == 'comments' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Comments') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/cras', ['query' => 'broadcast_date', 'sort' => ($queryParam == 'broadcast_date' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.BroadcastDate') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$cras->isEmpty())
						@foreach($cras as $cra)
						<tr>
							<td>{{$cra->user->name}}</td>
							<td>{{$cra->start}}</td>
							<td>{{$cra->end}}</td>
							<td>{{$cra->days}}</td>
							<td>{{$cra->comments}}</td>
							<td>{{$cra->broadcast_date}}</td>
							<td>
								<form class="delete-form" method="POST" action="{{ url('/admin/cras/delete', [ 'id' => $cra->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
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
			{{ $cras->render() }}
		</div>
	</div>
@stop