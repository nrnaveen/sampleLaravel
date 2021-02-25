@extends('admin.layout')

@section('content')
	<div class="error-page">
		<h2 class="headline text-yellow">404</h2>
		<div class="error-content">
			<h3><i class="fa fa-warning text-yellow"></i> Oops! Page not found.</h3>
			<p>We could not find the page you were looking for. Meanwhile, you may <a href="{{url('/admin')}}">return to dashboard</a> or try using the search form.</p>
		</div>
	</div>
@stop

@section('content_header')
	<h1>404 {{trans('messages.NotFound')}}</h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active">{{trans('messages.NotFound')}}</li>
	</ol>
@stop