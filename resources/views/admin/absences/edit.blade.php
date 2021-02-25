@extends('admin.layout')
@section('content_header')
	<h1>{{trans('messages.EditAbsence')}}<small>{{trans('messages.ModifyAbsenceDetails')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-buysellads"></i> {{trans('messages.AbsencesManagement')}}</li>
	</ol>
@stop
@section('content')
	{{ Form::model($absence, array('url' => array('/admin/absences/edit/' . $absence->id), 'novalidate' => 'novalidate', 'id' => 'updateAbsence', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label">{{trans('messages.UserId')}}</label>
					<div class="col-sm-10">{{$absence->user_id}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.StartDate')}}</label>
					<div class="col-sm-10">{{$absence->start}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.EndDate')}}</label>
					<div class="col-sm-10">{{$absence->end}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.StartHalf')}}</label>
					<div class="col-sm-10">{{$absence->startHalf}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.EndHalf')}}</label>
					<div class="col-sm-10">{{$absence->endHalf}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.Days')}}</label>
					<div class="col-sm-10">{{$absence->days}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.Reason')}}</label>
					<div class="col-sm-10">{{$absence->reason}}</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">{{trans('messages.Status')}}</label>
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
						<a class="btn btn-default" href="{{url('/admin/absences')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop