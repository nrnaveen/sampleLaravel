@extends('admin.layout')

@section('content_header')
	<h1>Profile</h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> Home</a></li>
		<li class="active"><i class="fa fa-user"></i> Profile</li>
	</ol>
@stop

@section('content')
	{{ Form::model($admin, array('url' => array('/admin/updateprofile'), 'class' => 'form-horizontal', 'id' => 'adminProfile', 'novalidate' => 'novalidate')) }}

	@if (count($errors) > 0)
		<div class="alert alert-danger alert-dismissable">
			<button type = "button" class = "close" data-dismiss = "alert" aria-hidden = "true">&times;</button>
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
			<label class="col-sm-2 control-label" for="name">{{trans('messages.FirstName')}}</label>
			<div class="col-sm-10">
				{{ Form::text('firstname', null, array('class' => 'form-control', 'id' => 'firstName', 'required' => '',)) }}
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="name">{{trans('messages.LastName')}}</label>
			<div class="col-sm-10">
				{{ Form::text('lastname', null, array('class' => 'form-control', 'id' => 'lastName', 'required' => '')) }}
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="password">{{trans('messages.Password')}}</label>
			<div class="col-sm-10">
				{{ Form::password('password', array('class' => 'form-control', 'id' => 'password', 'placeholder' => 'Password')) }}
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="password-confirm">{{trans('messages.ConfirmPassword')}}</label>
			<div class="col-sm-10">
				{{ Form::password('password_confirmation', array('class' => 'form-control', 'id' => 'password-confirm', 'placeholder' => 'Confirm Password')) }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
				<a class="btn btn-default" href="{{url('/admin')}}">{{trans('messages.Cancel')}}</a>
			</div> 
		</div>
		{{ Form::close() }}
	</div>
	</div>
	
@stop