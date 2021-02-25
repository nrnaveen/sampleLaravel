@extends('admin.layout')

@section('content')
	{{ Form::model($type, array('url' => array('/admin/absence-types/edit/' . $type->id), 'novalidate' => 'novalidate', 'id' => 'updateclient', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="label">{{trans('messages.Label')}}</label>
					<div class="col-sm-10">
						{{ Form::text('label', null, ['required' => "", 'class' => 'form-control', 'id' => 'label', 'placeholder' => trans('messages.Label')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="status">{{trans('messages.Status')}}</label>
					<div class="col-sm-10">
						{{ Form::select('status', [0, 1], null, ['class' => 'form-control', 'id' => 'status', 'placeholder' => trans('messages.SelectStatus') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="auto_approve">{{trans('messages.AutoApprove')}}</label>
					<div class="col-sm-10">
						{{ Form::select('auto_approve', [0, 1], null, ['class' => 'form-control', 'id' => 'auto_approve', 'placeholder' => trans('messages.SelectAutoApprove') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="color">Color</label>
					<div class="col-sm-10">
						<select id="color" name="color" class="form-control" required="" style="background-color: {{$type->color}};">
							<option disabled="" selected="" value="">{{ trans('messages.SelectColor')}}</option>
							@foreach($colors as $key => $color)
								<option style="background-color: {{$key}};" value="{{$key}}" {{ $key === $type->color ? 'selected' : '' }}>{{$color}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), ['class' => 'btn btn-primary', 'id' => 'save']) }}
						<a class="btn btn-default" href="{{url('/admin/absence-types')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
@stop

@section('content_header')
	<h1>{{trans('messages.EditAbsenceType')}}<small>{{trans('messages.ModifyAbsenceTypeDetails')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-buysellads"></i> {{trans('messages.AbsenceTypesManagement')}}</li>
	</ol>
@stop

@section('footerjs')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#color').change(function(){
				$('#color').css('background-color', $(this).val());
			});
		});
	</script>
@stop