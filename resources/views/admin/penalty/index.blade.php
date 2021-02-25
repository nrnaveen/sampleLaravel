@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.PenaltyManagement')}}<small>{{trans('messages.DetailsOfPenalties')}}</small></h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.PenaltyManagement')}}</li>
	</ol>
	<a class="btn btn-primary" href="{{url('/admin/penalties/export')}}">{{trans('messages.ExcelExport')}}</a>
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
							<a href="{{urlWithQuery('/admin/penalties', ['query' => 'user_id', 'sort' => ($queryParam == 'user_id' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.UserId') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/penalties', ['query' => 'beginning', 'sort' => ($queryParam == 'beginning' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Beginning') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/penalties', ['query' => 'ending', 'sort' => ($queryParam == 'ending' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Ending') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/penalties', ['query' => 'total_duration', 'sort' => ($queryParam == 'total_duration' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.TotalDuration') }}</i>
							</a>
						</th> 
						<th>
							<a href="{{urlWithQuery('/admin/penalties', ['query' => 'type', 'sort' => ($queryParam == 'type' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Type') }}</i>
							</a>
						</th> 
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$penalties->isEmpty())
						@foreach($penalties as $penalty)
							<tr>
								<td>{{$penalty->user_id}}</td>
								<td>{{$penalty->beginning}}</td>
								<td>{{$penalty->ending}}</td>
								<td>{{$penalty->total_duration}}</td>
								<td>{{$penalty->type}}</td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/penalties/edit', [ 'id' => $penalty->id, ])}}"><i class="fa fa-edit fa-lg"></i></a>
									<form class="delete-form" method="POST" action="{{ url('/admin/penalties/delete', [ 'id' => $penalty->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
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
			{{ $penalties->render() }}
		</div>
	</div>
@stop