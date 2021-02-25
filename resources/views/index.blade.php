<!DOCTYPE html>
<html lang="en" ng-app="BicApp">
	<head>
		<title>Sample</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="{{asset('/css/normalize.css')}}">
		<link rel="stylesheet" href="{{asset('/css/bootstrap.css')}}">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="{{asset('/assets/plugins/ng-dialog/css/ngDialog.min.css')}}">
		<link rel="stylesheet" href="{{asset('/assets/plugins/ng-dialog/css/ngDialog-theme-default.min.css')}}">
		<link rel="stylesheet" href="{{asset('/assets/plugins/ng-responsive-calendar/dist/css/calendar.min.css')}}">
		<link rel="stylesheet" href="{{asset('/assets/plugins/jQueryUI/jquery-ui.min.css')}}">
		<link rel="stylesheet" href="{{asset('/assets/plugins/jquery-confirm/jquery-confirm.min.css')}}">
		<link rel="stylesheet" href="{{asset('/assets/plugins/timepicker/jquery-ui-timepicker-addon.css')}}" />
		<link rel="stylesheet" href="{{asset('/assets/css/main.css')}}">
	</head>
	<body ng-controller="AppController" ng-class="{ 'loginbg': !userLogged, }">
		<div ng-class="dataLoading ? 'loader': ''"></div>
		<div id="wrapper" ng-class="{ 'wrapperlogin': !userLogged, }">
			<ng-include src="'ng/modules/main/views/header.html'"></ng-include>
			<ng-include src="'ng/modules/main/views/sidebar.html'"></ng-include>
			<div ng-view></div>
		</div>
		<script src="{{asset('/js/jquery-2.0.3.min.js')}}"></script>
		<script src="{{asset('/js/main.js')}}"></script>
		<script src="{{asset('/js/bootstrap.js')}}"></script>
		<script data-require="modernizr@*" data-semver="2.6.2" src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.js"></script>
		<script src="{{asset('/assets/plugins/noty/packaged/jquery.noty.packaged.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/async/async.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/moment/moment.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/moment/locales.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/moment/moment-timezone.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/moment/moment-range.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/jQueryUI/jquery-ui.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/jQueryUI/i18n/datepicker-fr.js')}}"></script>
		<script src="{{asset('/assets/plugins/jquery-confirm/jquery-confirm.min.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/timepicker/jquery-ui-timepicker-addon.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/timepicker/i18n/jquery-ui-timepicker-fr.js')}}"></script>
		<script type="text/javascript" src="{{asset('/assets/plugins/timepicker/jquery-ui-sliderAccess.js')}}"></script>
		<script type="text/javascript">
			window['moment-range'].extendMoment(moment);
			var hexToRgbA = function(hex, opacity){
				var opacity = opacity || 1;
				var c;
				if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
					c= hex.substring(1).split('');
					if(c.length== 3){
						c= [c[0], c[0], c[1], c[1], c[2], c[2]];
					}
					c= '0x'+c.join('');
					return 'rgba(' + [(c>>16)&255, (c>>8)&255, c&255].join(',') + ', ' + opacity + ')';
				}
				throw new Error('Bad Hex');
			};
		</script>
		<!-- Angular Default Modules -->
		<script src="{{asset('/js/angular/angular.min.js')}}"></script>
		<script src="{{asset('/js/angular/angular-touch.min.js')}}"></script>
		<script src="{{asset('/js/angular/angular-sanitize.min.js')}}"></script>
		<script src="{{asset('/js/angular/angular-messages.min.js')}}"></script>
		<script src="{{asset('/js/angular/angular-route.min.js')}}"></script>
		<script src="{{asset('/js/angular/angular-cookies.min.js')}}"></script>
		<script src="{{asset('/js/angular-i18n/angular-locale_fr-fr.js')}}"></script>
		<!-- Angular Default Modules -->
		<!-- Angular Third party Modules -->
		<script src="{{asset('/js/angular-validation-match/angular-validation-match.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/ng-dialog/js/ngDialog.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/ng-file-upload/ng-file-upload-shim.min.js')}}"></script>
		<script src="{{asset('/assets/plugins/ng-file-upload/ng-file-upload.min.js')}}"></script>
		<script src="{{asset('/assets/js/angular-google-plus.min.js')}}"></script>
		<!-- Angular Third party Modules -->
		<!-- Angular Custom Modules -->
		<script src="{{asset('/ng/app.js')}}"></script>
		<script src="{{asset('/assets/plugins/ng-responsive-calendar/dist/js/calendar-tpls.js')}}"></script>
		<script src="{{asset('/ng/modules/authentication/services.js')}}"></script>
		<script src="{{asset('/ng/modules/authentication/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/home/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/home/services.js')}}"></script>
		<script src="{{asset('/ng/modules/absences/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/absences/services.js')}}"></script>
		<script src="{{asset('/ng/modules/cra/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/cra/services.js')}}"></script>
		<script src="{{asset('/ng/modules/profile/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/profile/services.js')}}"></script>
		<script src="{{asset('/ng/modules/manager/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/manager/services.js')}}"></script>
		<script src="{{asset('/ng/modules/contact/controllers.js')}}"></script>
		<script src="{{asset('/ng/modules/contact/services.js')}}"></script>
		<!-- Angular Custom Modules -->
	</body>
</html>