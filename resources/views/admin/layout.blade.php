<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{{ $title }}</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/font-awesome/css/font-awesome.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/Ionicons/css/ionicons.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/dist/css/skins/_all-skins.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
		@yield('headerjs')
		<link rel="stylesheet" type="text/css" href="{{asset('/assets/dist/css/AdminLTE.min.css')}}">
		<style type="text/css">
			label.error {color: red;border-color: #ebccd1;}
			.box-header > .box-tools {width: 100%;}
		</style>
	</head>
	<body class="hold-transition skin-blue sidebar-mini">
		<div class="wrapper">
			@include('admin.header')
			<div class="content-wrapper">
				<section class="content-header">@yield('content_header')</section>
				<section class="content">
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
					@yield('content')
				</section>
			</div>
			<footer class="main-footer">
				<div class="pull-right hidden-xs">
					<b>{{trans('messages.Version')}}</b> 1.0
				</div>
				<strong>{{trans('messages.Copyright')}} &copy; 2017 <a href="{{url('/')}}">Sample</a>.</strong> {{trans('messages.AllRightsReserved')}}
			</footer>
		</div>
		<script src="{{asset('/assets/bower_components/jquery/dist/jquery.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/jquery-ui/jquery-ui.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/jquery-validation/additional-methods.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/jquery-validation/localization/messages_fr.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/raphael/raphael.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js')}}"></script>
		<!-- jQuery Knob Chart -->
		<script src="{{asset('/assets/bower_components/jquery-knob/dist/jquery.knob.min.js')}}"></script>
		<!-- daterangepicker -->
		<script src="{{asset('/assets/bower_components/moment/min/moment.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
		<script src="{{asset('/assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
		<script src="{{asset('/assets/bower_components/fastclick/lib/fastclick.js')}}"></script>
		<script src="{{asset('/assets/dist/js/adminlte.min.js')}}"></script>
		<!-- date picker -->
		<script src="{{asset('/assets/plugins/datetimepicker/jquery.datetimepicker.full.min.js')}}"></script>
		@yield('footerjs')
		<script>
			$(document).ready(function(){
				$("#newUser").validate({ rules: { password_confirmation: { equalTo: "#password", } } });
				$("#updateUser").validate({ rules: { password_confirmation: { equalTo: "#password", } } });
				$("#adminProfile").validate({ rules: { password_confirmation: { equalTo: "#password", } } });
				$("#updatePenalty").validate();
				$.datetimepicker.setLocale('fr');
				$('#datetimepicker1').datetimepicker();
				$('#datetimepicker2').datetimepicker();
				$("#addConsultant").validate();
				$("#addMission").validate();
				$('#start').datetimepicker({
					startDate: '<?php echo date("d/m/Y"); ?>',
					minDate: '<?php echo date("d/m/Y"); ?>',
					timepicker: false,
					format: 'd/m/Y',
					onShow: function(ct){
						this.setOptions({
							maxDate: $('#end').val() ? $('#end').val() : false,
						});
					},
				});
				$('#end').datetimepicker({
					startDate: '<?php echo date("d/m/Y"); ?>',
					minDate: '<?php echo date("d/m/Y"); ?>',
					timepicker: false,
					format: 'd/m/Y',
					onShow: function(ct){
						this.setOptions({
							minDate: $('#start').val() ? $('#start').val() : false,
						});
					},
				});
			});
		</script>
	</body>
</html>