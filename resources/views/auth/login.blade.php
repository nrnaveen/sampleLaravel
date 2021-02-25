<!DOCTYPE html>
<html lang="en" ng-app='bicApp'>
	<head>
		<title>Login</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="{{asset('css/normalize.css')}}">
		<link rel="stylesheet" href="{{asset('css/bootstrap.css')}}">
		<link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}">
		<link rel="stylesheet" href="{{asset('css/main.css')}}">
		<script src="{{asset('js/jquery.min.js')}}"></script>
	</head>
	<body class="loginbg" ng-controller='MainCtrl'>
		<section id="login">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-sm-12 col-xs-12">
						<div class="form-center">
							<!-- form -->
							<form id="loginForm" ng-submit="processLoginForm()">
								{{ csrf_field() }}
								<div class="form-group">
									<input type="email" name="email" id="email" class="form-control" placeholder="Email" ng-model="formData.email">
								</div>
								<div class="form-group">
									<input type="password" name="password" id="password" class="form-control" placeholder="Password" ng-model="formData.password">
								</div>
								<button type="submit" class="btn valider" id="SubmitBtn">valider</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
	</body>
	<script src="{{asset('js/main.js')}}"></script>
	<script src="{{asset('js/bootstrap.js')}}"></script>
	<script src="{{asset('js/angular.min.js')}}"></script>
	<script type="text/javascript" src='https://rawgithub.com/gsklee/ngStorage/master/ngStorage.js'></script>
	<script src="{{asset('js/bicApp.js')}}"></script>
	</body>
</html>