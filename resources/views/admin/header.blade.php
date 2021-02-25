<header class="main-header">
	<a href="{{ url('/admin')}}" class="logo">
		<!-- mini logo for sidebar mini 50x50 pixels -->
		<span class="logo-mini"><b>BIC</b></span>
		<!-- logo for regular state and mobile devices -->
		<span class="logo-lg"><b>Sample</b></span>
	</a>
	<!-- Header Navbar: style can be found in header.less -->
	<nav class="navbar navbar-static-top">
		<!-- Sidebar toggle button-->
		<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
			<span class="sr-only">Toggle navigation</span>
		</a>
		<div class="navbar-custom-menu">
			<ul class="nav navbar-nav">
				<li class="dropdown user user-menu">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<img src="{{asset('assets/dist/img/avatar.png')}}" class="user-image" alt="User Image">
						<span class="hidden-xs">{{$admin->firstname}} {{$admin->lastname}}</span>
					</a>
					<ul class="dropdown-menu">
						<li class="user-header">
							<p>{{$admin->firstname}} {{$admin->lastname}}</p>
						</li>
						<!-- Menu Footer-->
						<li class="user-footer">
							<div class="pull-left">
								<a href="{{ url('admin/profile')}}" class="btn btn-default btn-flat">{{trans('messages.Profile')}}</a>
							</div>
							<div class="pull-right">
								<a href="{{ url('admin/logout')}}" class="btn btn-default btn-flat">{{trans('messages.Signout')}}</a>
							</div>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
	<!-- sidebar: style can be found in sidebar.less -->
	<section class="sidebar">
		<!-- Sidebar user panel -->
		<div class="user-panel">
			<div class="pull-left image">
				<img src="{{asset('assets/dist/img/avatar.png')}}" class="img-circle" alt="User Image">
			</div>
			<div class="pull-left info">
				<p>{{$admin->firstname}} {{$admin->lastname}}</p>
				<a href="#"><i class="fa fa-circle text-success"></i> Online</a>
			</div>
		</div>
		<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu" data-widget="tree">
			<li class="header">{{trans('messages.MAINNAVIGATION')}}</li>
			<li class="class = @if(Request::is('admin')) active @endif">
				<a href="{{ url('admin')}}">
					<i class="fa fa-dashboard"></i><span>{{trans('messages.Dashboard')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/users') || Request::is('admin/users/*')) active @endif">
				<a href="{{ url('/admin/users')}}">
					<i class="fa fa-users"></i> <span>{{trans('messages.UsersManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/admins') || Request::is('admin/admins/*')) active @endif">
				<a href="{{url('/admin/admins')}}">
					<i class="fa fa-user-circle-o"></i> <span>{{trans('messages.ManagementAdministrators')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/clients') || Request::is('admin/clients/*')) active @endif">
				<a href="{{ url('/admin/clients')}}">
					<i class="fa fa-user-secret"></i> <span>{{trans('messages.ClientsManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/missions') || Request::is('admin/missions/*')) active @endif">
				<a href="{{ url('/admin/missions')}}">
					<i class="fa fa-user"></i> <span>{{trans('messages.MissionsManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/absences') || Request::is('admin/absences/*')) active @endif">
				<a href="{{ url('/admin/absences')}}">
					<i class="fa fa-buysellads"></i> <span>{{trans('messages.AbsencesManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/absence-types') || Request::is('admin/absence-types/*')) active @endif">
				<a href="{{ url('/admin/absence-types')}}">
					<i class="fa fa-buysellads"></i> <span>{{trans('messages.AbsenceTypesManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/cras') || Request::is('admin/cras/*')) active @endif">
				<a href="{{ url('/admin/cras')}}">
					<i class="fa fa-info-circle"></i> <span>{{trans('messages.CraManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/penalties') || Request::is('admin/penalties/*')) active @endif">
				<a href="{{ url('/admin/penalties')}}">
					<i class="fa fa-minus-circle"></i> <span>{{trans('messages.PenaltyManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/emails') || Request::is('admin/emails/*')) active @endif">
				<a href="{{ url('/admin/emails')}}">
					<i class="fa fa-columns"></i> <span>{{trans('messages.EmailManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/events') || Request::is('admin/events/*')) active @endif">
				<a href="{{ url('/admin/events')}}">
					<i class="fa fa-calendar"></i> <span>{{trans('messages.EventsManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/version') || Request::is('admin/version/*')) active @endif">
				<a href="{{ url('/admin/version')}}">
					<i class="fa fa-info"></i> <span>{{trans('messages.VersionManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/site-info')) active @endif">
				<a href="{{ url('/admin/site-info')}}">
					<i class="fa fa-lock"></i> <span>{{trans('messages.SiteInfo')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/homecontent')) active @endif">
				<a href="{{ url('/admin/homecontent')}}">
					<i class="fa fa-home"></i> <span>{{trans('messages.HomeGreeting')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/contact')) active @endif">
				<a href="{{ url('/admin/contact')}}">
					<i class="fa fa-address-book"></i> <span>{{trans('messages.ContactManagement')}}</span>
				</a>
			</li>
			<li class="@if(Request::is('admin/deleted-mails')) active @endif">
				<a href="{{ url('/admin/deleted-mails')}}">
					<i class="fa fa-minus"></i> <span>{{trans('messages.RemovedEmails')}}</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- /.sidebar -->
</aside>