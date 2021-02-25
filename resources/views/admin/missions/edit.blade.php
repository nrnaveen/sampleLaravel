@extends('admin.layout')

@section('content')
	{{ Form::model($mission, array('url' => array('/admin/missions/edit/' . $mission->id), 'novalidate' => 'novalidate', 'id' => 'updatePenalty', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="code">{{trans('messages.Code')}}</label>
					<div class="col-sm-10">
						{{ Form::text('code', null, ['required' => '', 'class' => 'form-control', 'id' => 'code', 'placeholder' => trans('messages.Code')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="label">{{trans('messages.Label')}}</label>
					<div class="col-sm-10">
						{{ Form::text('label', null, ['required' => '', 'class' => 'form-control', 'id' => 'label', 'placeholder' => trans('messages.Label')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="order">{{trans('messages.Order')}}</label>
					<div class="col-sm-10">
						{{ Form::number('order', null, ['required' => '', 'class' => 'form-control', 'id' => 'order', 'placeholder' => trans('messages.Order'), 'min' => '1', 'step' => '1']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="status">{{trans('messages.Status')}}</label>
					<div class="col-sm-10">
						{{ Form::select('status', [0, 1], null, ['class' => 'form-control', 'id' => 'status', 'placeholder' => trans('messages.SelectStatus') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="client">{{trans('messages.Client')}}</label>
					<div class="col-sm-10">
					{{ Form::select('client_id', $clients, null, ['required' => '', 'id' => 'client', 'class' => 'form-control']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="activity_type">{{trans('messages.ActivityType')}}</label>
					<div class="col-sm-10">
						{{ Form::select('activity_type', $activity_types, null, ['class' => 'form-control', 'id' => 'activity_type', 'placeholder' => trans('messages.SelectActivityType') . ' ...', ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="commercial">{{trans('messages.commercial')}}</label>
					<div class="col-sm-10">
						{{ Form::select('commercial', $users, null, ['class' => 'form-control', 'id' => 'commercial', 'placeholder' => trans('messages.SelectUsers') . ' ...', ]) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin/missions')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('content_header')
	<h1> {{trans('messages.EditMission')}}<small>{{trans('messages.ModifyMissionDetails')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.MissionsManagement')}}</li>
	</ol>
@stop

@section('headerjs')
	<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/selectize.js/dist/css/selectize.bootstrap3.css')}}">
@stop

@section('footerjs')
	<script type="text/javascript" src="{{asset('/assets/plugins/selectize.js/dist/js/standalone/selectize.js')}}"></script>
	<script>
		$(document).ready(function() {
			$('#commercial').selectize({ minItems: 0, maxItems: 1, plugins: ['remove_button'], persist: true, mode: 'multi', });
		});
	</script>
@stop