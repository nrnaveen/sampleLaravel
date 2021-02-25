@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.CraManagement')}}<small>{{trans('messages.DetailsOfCra')}}</small></h1></br>
	<!-- <a class="btn btn-primary" href="{{url('/admin/absences/add')}}">Add new Absence</a> -->
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.CraManagement')}}</li>
	</ol>
@stop
@section('content')
	{{ Form::model($cra, array('url' => array('/admin/cras/edit/' . $cra->id), 'novalidate' => 'novalidate', 'id' => 'updateCRA', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="status">{{trans('messages.Status')}}</label>
					<div class="col-sm-10">
						{{ Form::select('status', array('pending' => 'Pending', 'approved' => 'Approved', 'cancelled_by_admin' => 'Cancel', 'deleted_by_admin' => 'Delete'), null, array('id' => '', 'class' => 'form-control')) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="cancel_reason">{{trans('messages.CancelReason')}}</label>
					<div class="col-sm-10">
						{{ Form::textarea('cancel_reason', null, array('id' => '', 'class' => 'form-control', 'required' => '', 'minlength' => '5', 'maxlength' => '190')) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin/cras')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop