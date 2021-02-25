@extends('admin.layout')

@section('content')
	{{ Form::open(['url' => '/admin/events/add', 'novalidate' => 'novalidate', 'id' => 'updatePenalty', 'class' => 'form-horizontal']) }}
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
					<label class="col-sm-2 control-label" for="label">{{trans('messages.Label')}}</label>
					<div class="col-sm-10">
						{{ Form::text('label', null, array('required' => '', 'id' => 'label', 'class' => 'form-control', )) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="date">{{trans('messages.Date')}}</label>
					<div class="col-sm-10">
						{{ Form::text('date', null, array('required' => '', 'id' => 'date', 'class' => 'form-control', 'readonly' => '')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="description">{{trans('messages.Description')}}</label>
					<div class="col-sm-10">
					{{ Form::textarea('description', null, array('id' => 'description', 'class' => 'form-control', 'required' => '', 'minlength' => '5', 'maxlength' => '190')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="status">{{trans('messages.Status')}}</label>
					<div class="col-sm-10">
						{{ Form::select('status', [0, 1], null, ['class' => 'form-control', 'id' => 'status', 'placeholder' => trans('messages.SelectStatus') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), ['class' => 'btn btn-primary', 'id' => 'save']) }}
						<a class="btn btn-default" href="{{url('/admin/events')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('content_header')
	<h1>{{trans('messages.AddEvent')}}<small>{{trans('messages.NewEvent')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-calendar"></i> {{trans('messages.EventsManagement')}}</li>
	</ol>
@stop

@section('headerjs')
	<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/datetimepicker/jquery.datetimepicker.min.css')}}">
@stop

@section('footerjs')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#date').datetimepicker({
				startDate: '<?php echo date("d/m/Y"); ?>',
				minDate: '<?php echo date("d/m/Y"); ?>',
				timepicker: false,
				format: 'd/m/Y',
			});
		});
	</script>
@stop