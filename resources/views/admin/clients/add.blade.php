@extends('admin.layout')

@section('content')
	{{ Form::open(['url' => '/admin/clients/add', 'novalidate' => 'novalidate', 'id' => 'updatePenalty', 'class' => 'form-horizontal']) }}
		@if (count($errors) > 0)
			<div class="alert alert-danger alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
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
					<label class="col-sm-2 control-label" for="firstname">{{trans('messages.FirstName')}}</label>
					<div class="col-sm-10">
						{{ Form::text('firstname', null, ['class' => 'form-control', 'id' => 'firstname', 'placeholder' => trans('messages.FirstName')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="firstname">{{trans('messages.LastName')}}</label>
					<div class="col-sm-10">
						{{ Form::text('lastname', null, ['required' => '', 'class' => 'form-control', 'id' => 'lastname', 'placeholder' => trans('messages.LastName')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="email">{{trans('messages.Email')}}</label>
					<div class="col-sm-10">
						{{ Form::email('email', null, ['required' => '', 'class' => 'form-control', 'id' => 'email', 'placeholder' => trans('messages.Email')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="status">{{trans('messages.Status')}}</label>
					<div class="col-sm-10">
						{{ Form::select('status', [0, 1], null, ['class' => 'form-control', 'id' => 'status', 'placeholder' => trans('messages.SelectStatus') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="contact">{{trans('messages.Mobile')}}</label>
					<div class="col-sm-10">
						{{ Form::text('mobile', null, ['class' => 'form-control', 'id' => 'contact', 'placeholder' => trans('messages.Mobile')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="address">{{trans('messages.Address')}}</label>
					<div class="col-sm-10">
						{{ Form::text('address', null, ['class' => 'form-control', 'id' => 'address', 'placeholder' => trans('messages.Address')]) }}
					</div>
				</div>
				<!-- <div class="form-group">
					<label class="col-sm-2 control-label" for="color">Color</label>
					<div class="col-sm-10">
					{{ Form::select('color', $colors, null, ['required' => '', 'id' => 'color', 'class' => 'form-control']) }}
					</div>
				</div> -->
				<div class="form-group">
					<label class="col-sm-2 control-label" for="color">Color</label>
					<div class="col-sm-10">
						<!-- {{ Form::select('color', $colors, null, array('id' => '', 'class' => 'form-control')) }} -->
						<select id="color" name="color" class="form-control" required="">
							<option disabled="" selected="" value="">{{ trans('messages.SelectColor')}}</option>
							@foreach($colors as $key => $color)
								<option style="background-color: {{$key}};" value="{{$key}}">{{$color}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), ['class' => 'btn btn-primary', 'id' => 'save']) }}
						<a class="btn btn-default" href="{{url('/admin/clients')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('content_header')
	<h1>{{trans('messages.AddClient')}}<small>{{trans('messages.NewClient')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user-secret"></i> {{trans('messages.ClientsManagement')}}</li>
	</ol>
@stop