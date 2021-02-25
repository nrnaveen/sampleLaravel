@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.PenaltyManagement')}}<small>{{trans('messages.DetailsOfPenalties')}}</small></h1></br>
	<!-- <a class="btn btn-primary" href="{{url('/admin/absences/add')}}">Add new Absence</a> -->
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.PenaltyManagement')}}</li>
	</ol>
@stop

@section('content')
	{{ Form::model($penalty, array('url' => array('/admin/penalties/edit/' . $penalty->id), 'novalidate' => 'novalidate', 'id' => 'updatePenalty', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="user_id">{{trans('messages.UserId')}}</label>
					<div class="col-sm-10">
					{{$penalty->user_id}}
					<!-- {{ Form::text('client_name', null, array('required' => '', 'class' => 'form-control', 'id' => 'client_name', 'placeholder' => 'Client Name')) }} -->
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="beginning">{{trans('messages.Beginning')}}</label>
					<div class="col-sm-10">
					{{ Form::text('beginning', null, array('required' => '', 'id' => 'datetimepicker1', 'class' => 'form-control')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="ending">{{trans('messages.Ending')}}</label>
					<div class="col-sm-10">
					{{ Form::text('ending', null, array('required' => '', 'id' => 'datetimepicker2', 'class' => 'form-control')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="total_duration">{{trans('messages.TotalDuration')}}</label>
					<div class="col-sm-10">
					{{$penalty->total_duration}}
					<!-- {{ Form::text('total_duration', null, array('required' => '', 'class' => 'form-control', 'id' => 'client_name', 'placeholder' => 'Client Name')) }} -->
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="type"></label>
					<div class="col-sm-10">
						<label>{{Form::radio('type', 'Active', null)}} Active</label>&nbsp;&nbsp;&nbsp;&nbsp;
						<label>{{Form::radio('type', 'Passive', null)}} Passive</label>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin/penalties')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('headerjs')
	<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/datetimepicker/jquery.datetimepicker.min.css')}}">
@stop