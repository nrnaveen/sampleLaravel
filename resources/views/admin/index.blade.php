@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.Dashboard')}}<small>{{trans('messages.Controlpanel')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> {{trans('messages.Home')}}</a></li>
		<li class="active">{{trans('messages.Dashboard')}}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>{{$usersCount}}</h3>
					<p>{{trans('messages.UserRegistrations')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-users"></i>
				</div>
				<a href="{{url('/admin/users')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-aqua">
				<div class="inner">
					<h3>{{$adminsCount}}</h3>
					<p>{{trans('messages.AdminRecords')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
				<a href="{{url('/admin/admins')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-green">
				<div class="inner">
					<h3>{{$clientsCount}}</h3>
					<p>{{trans('messages.ClientRecords')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-secret"></i>
				</div>
				<a href="{{url('/admin/clients')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-red">
				<div class="inner">
					<h3>{{$missionsCount}}</h3>
					<p>{{trans('messages.MissionRecords')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-user"></i>
				</div>
				<a href="{{url('/admin/missions')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-teal">
				<div class="inner">
					<h3>{{$absencesCount}}</h3>
					<p>{{trans('messages.AbsenceRecords')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-buysellads"></i>
				</div>
				<a href="{{url('/admin/absences')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-blue-active">
				<div class="inner">
					<h3>{{$crasCount}}</h3>
					<p>{{trans('messages.CRARecords')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-info-circle"></i>
				</div>
				<a href="{{url('/admin/cras')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<div class="col-lg-3 col-xs-6">
			<div class="small-box bg-maroon-active">
				<div class="inner">
					<h3>{{$penaltiesCount}}</h3>
					<p>{{trans('messages.PenaltyRecords')}}</p>
				</div>
				<div class="icon">
					<i class="fa fa-minus-circle"></i>
				</div>
				<a href="{{url('/admin/penalties')}}" class="small-box-footer">
					{{trans('messages.Moreinfo')}} <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
	</div>
@stop