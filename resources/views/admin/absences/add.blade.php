@extends('admin.layout')

@section('content')
	{{ Form::open(['url' => '/admin/absences/add', 'novalidate' => 'novalidate', 'id' => 'updatePenalty', 'class' => 'form-horizontal']) }}
		@if (count($errors) > 0)
			<div class="alert alert-danger alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<ul>
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif
		<div class="box">
			<div class="box-body">
				<div class="form-group">
					<label class="col-sm-2 control-label" for="user_id">{{trans('messages.User')}}</label>
					<div class="col-sm-10">
					{{ Form::select('user_id', $users, null, ['required' => '', 'id' => 'user_id', 'class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reason">{{trans('messages.Reason')}}</label>
					<div class="col-sm-10">
						{{ Form::select('reason', $reasons, null, ['required' => '', 'id' => 'reason', 'class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="start">{{trans('messages.StartDate')}}</label>
					<div class="col-sm-10">
						{{ Form::text('start', null, array('required' => '', 'id' => 'start', 'class' => 'form-control', 'readonly' => '')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="end">{{trans('messages.EndDate')}}</label>
					<div class="col-sm-10">
						{{ Form::text('end', null, array('required' => '', 'id' => 'end', 'class' => 'form-control', 'readonly' => '')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="type">{{trans('messages.Type')}}</label>
					<div class="col-sm-10">
						{{ Form::select('type', $types, null, ['required' => '', 'id' => 'type', 'class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), ['class' => 'btn btn-primary', 'id' => 'save']) }}
						<a class="btn btn-default" href="{{url('/admin/absences')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('content_header')
	<h1>{{trans('messages.AddAbsence')}}<small>{{trans('messages.NewAbsence')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-buysellads"></i> {{trans('messages.AbsencesManagement')}}</li>
	</ol>
@stop

@section('headerjs')
	<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/datetimepicker/jquery.datetimepicker.min.css')}}">
@stop