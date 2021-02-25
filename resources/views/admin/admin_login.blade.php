<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{{trans('messages.AdminLogin')}}</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/font-awesome/css/font-awesome.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/Ionicons/css/ionicons.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/dist/css/AdminLTE.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/iCheck/square/blue.css')}}">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
		<style type="text/css">
			label.error {
				color: red;
				border-color: #ebccd1;
				padding:1px 20px 1px 20px;
			}
			.checkbox.icheck{
				margin-top: 5px;
				margin-bottom: 5px;
			}
		</style>
	</head>
	<body class="hold-transition login-page">
		<div class="login-box">
			<div class="login-logo">
				<b>{{trans('messages.AdminLogin1')}}</b>
			</div>
			<div>
				@if(Session::has('error'))
					<div class="alert alert-danger alert-dismissible">
						<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
						{{ Session::get('error') }}
					</div>
				@endif
				@if(Session::has('message'))
					<div class="alert alert-success alert-dismissible">
						<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
						{{ Session::get('message') }}
					</div>
				@endif
				<!--validation-->
				@if (count($errors) > 0)
					<div class="alert alert-danger">
						<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
						@foreach($errors->all() as $error)
							<p>{{ $error }}</p>
						@endforeach
					</div>
				@endif
				@if(session()->has('success'))
					<div class="alert alert-success">
						{{ session()->get('success') }}
					</div>
				@endif
			</div>
			<div class="login-box-body">
				<p class="login-box-msg">{{trans('messages.SignAdminPage')}}</p>
				{{ Form::open(array('url' => '/admin/login', 'novalidate' => 'novalidate', 'id' => 'myLogin')) }}
					<div class="form-group has-feedback">
						{{ Form::email('email', null, array('class' => 'form-control', 'type' => 'email', 'id' => 'email', 'placeholder' => trans('messages.Email'), 'required' => '')) }}
					</div>
					<div class="form-group has-feedback">
						{{ Form::password('password', array('class' => 'form-control', 'id' => 'password', 'placeholder' => trans('messages.Password'), 'required' => '', 'minlength' => '6')) }}
					</div>
					<div class="row">
						<div class="col-xs-6">
							<div class="checkbox icheck">
								{{ Form::checkbox('remember', 1, null, ['id' => 'remember', 'class' => 'className']) }}
								{{ Form::label('remember', trans('messages.RememberMe'))}}
							</div>
						</div>
						<div class="col-xs-6">
							{{Form::submit(trans('messages.SignIn'), array('class' => 'btn btn-primary btn-block btn-flat', 'id' => 'submit'))}}
						</div>
					</div>
				{{Form::close()}}
			</div>
		</div>
		<script src="{{asset('/assets/bower_components/jquery/dist/jquery.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/jquery-validation/additional-methods.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/jquery-validation/localization/messages_fr.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/iCheck/icheck.min.js')}}"></script>
		<script>
			$(function(){
				$('input').iCheck({
					checkboxClass: 'icheckbox_square-blue',
					radioClass: 'iradio_square-blue',
					increaseArea: '20%' // optional
				});
				$('#myLogin').validate();
			});
		</script>
	</body>
</html>