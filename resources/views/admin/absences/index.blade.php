@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.AbsencesManagement')}}<small>{{trans('messages.DetailsOfAbsences')}}</small></h1></br>
	<a class="btn btn-primary" href="{{url('/admin/absences/add')}}">Add new Absence</a>
	<a class="btn btn-primary" href="{{url('/admin/absences/export')}}">{{trans('messages.ExcelExport')}}</a>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-buysellads"></i> {{trans('messages.AbsencesManagement')}}</li>
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
							<a href="{{urlWithQuery('/admin/absences', ['query' => 'user_id', 'sort' => ($queryParam == 'user_id' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.UserId') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/absences', ['query' => 'status', 'sort' => ($queryParam == 'status' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Status') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/absences', ['query' => 'start', 'sort' => ($queryParam == 'start' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.StartDate') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/absences', ['query' => 'end', 'sort' => ($queryParam == 'end' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.EndDate') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/absences', ['query' => 'days', 'sort' => ($queryParam == 'days' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Days') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/absences', ['query' => 'reason', 'sort' => ($queryParam == 'reason' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Reason') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$absences->isEmpty())
						@foreach($absences as $absence)
							<tr>
								<td>{{$absence->user->name}}</td>
								<td>{{$absence->status}}</td>
								<td>{{$absence->start}}</td>
								<td>{{$absence->end}}</td>
								<td>{{$absence->days}}</td>
								<td>{{$absence->reason}}</td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/absences/edit', [ 'id' => $absence->id, ])}}"><i class="fa fa-edit fa-lg"></i></a>
									<form class="delete-form" method="POST" action="{{ url('/admin/absences/delete', [ 'id' => $absence->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
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
			{{ $absences->render() }}
		</div>
	</div>
@stop