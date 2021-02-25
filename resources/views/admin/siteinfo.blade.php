@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.SiteInfo')}}</h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.SiteInfo')}}</li>
	</ol>
@endsection

@section('content')
	<div class="box">
		<div class="box-body">
			{{ Form::model($siteinfo, array('novalidate' => 'novalidate', 'id' => 'addConsultant', 'class' => 'form-horizontal',)) }}
				@if(count($errors) > 0)
					<div class="alert alert-danger alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<ul>
							@foreach($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif
				<div class="form-group">
					<label class="col-sm-2 control-label" for="firstname">{{trans('messages.RequestCount')}}</label>
					<div class="col-sm-10">
						{{ Form::number('request_count', null, array('required' => '', 'class' => 'form-control', 'id' => 'request_count', 'placeholder' => trans('messages.RequestCount'))) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
					</div>
				</div>
			{{ Form::close() }}
		</div>
		<div class="box-body">
			{{ Form::open(array('url' => array('/admin/site-info/update-email'), 'novalidate' => 'novalidate', 'id' => 'admin_mail', 'class' => 'form-horizontal',)) }}
				<div class="form-group">
					<label class="col-sm-2 control-label" for="firstname">{{trans('messages.AdminMail')}}</label>
					<div class="col-sm-10">
						{{ Form::email('email', $admin_mail, array('required' => '', 'class' => 'form-control', 'id' => 'email', 'placeholder' => trans('messages.Email'))) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
					</div>
				</div>
			{{ Form::close() }}
		</div>
	</div>
@stop