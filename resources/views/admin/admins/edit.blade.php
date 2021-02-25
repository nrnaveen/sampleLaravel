@extends('admin.layout')

@section('content')
	{{ Form::model($admins, array('url' => array('/admin/admins/edit/' . $admins->id), 'class' => 'form-horizontal', 'id' => 'useradd', 'files' => 'true', )) }}
		@if(count($errors) > 0)
			<div class="alert alert-danger alert-dismissable">
				<button type = "button" class = "close" data-dismiss = "alert" aria-hidden = "true">&times;</button>
				<ul>
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif
		<div class="box administrateurs">
			<div class="box-body">
				<div class="form-group">
					<label class="col-sm-2 control-label" for="firstname">Prénom:</label>
					<div class="col-sm-10">
						{{ Form::text('firstname', null, array('required' => '', 'class' => 'form-control', 'id' => 'firstname', 'placeholder' => trans('messages.FirstName'))) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="lastname">Nom:</label>
					<div class="col-sm-10">
						{{ Form::text('lastname', null, array('required' => '', 'class' => 'form-control', 'id' => 'lastname', 'placeholder' => trans('messages.LastName'))) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="email">Adresse e-mail:</label>
					<div class="col-sm-10">
						{{ Form::email('email', null, array('required' => '', 'id' => 'email', 'placeholder' => trans('messages.Email'), "class" => "form-control"))}}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="password">{{trans('messages.Password')}}:</label>
					<div class="col-sm-10">
						{{ Form::password('password', array('class' => 'form-control', 'id' => 'password', 'placeholder' => trans('messages.Password'))) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="password_confirmation">{{trans('messages.PasswordConfirmation')}}:</label>
					<div class="col-sm-10">
						{{ Form::password('password_confirmation', array('class' => 'form-control', 'id' => 'password_confirmation', 'placeholder' => trans('messages.Password'))) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin/admins')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('content_header')
	<h1><i class="fa fa-user-circle-o"></i> {{trans('messages.ManagementAdministrators')}}<small>{{trans('messages.ModifyAdministrators')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user-circle-o"></i> {{trans('messages.ManagementAdministrators')}}</li>
	</ol>
@stop

@section('footerjs')
	<script>
		$(document).ready(function(){
			$(function(){
				$("#useradd").validate({
					rules: {
						firstname: "required",
						lastname : "required",
						email:"required",
						email:true,
						// password: "required",
						password_confirmation: { equalTo: "#password", },
					},
				});
			});
		});
	</script>
@stop