@extends('admin.layout')

@section('content')
	{{ Form::open(array('url' => '/admin/emails/add', 'novalidate' => 'novalidate', 'id' => 'newMail', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="email">{{trans('messages.EmailTemplate')}}</label>
					<div class="col-sm-10">
						{{ Form::select('email', $templates, null, ['class' => 'form-control', 'id' => 'email', 'placeholder' => trans('messages.SelectTemplate') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="add_email">A</label>
					<div class="col-sm-10">
						{{ Form::text('add_email', null, ['class' => 'form-control', 'id' => 'add_email', 'placeholder' => trans('messages.Email'), 'required' => '']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="cc_email">CC</label>
					<div class="col-sm-10">
						{{ Form::text('cc_email', null, ['class' => 'form-control', 'id' => 'cc_email', 'placeholder' => trans('messages.Email')]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="subject">{{trans('messages.Subject')}}</label>
					<div class="col-sm-10">
						{{ Form::text('subject', null, ['class' => 'form-control', 'id' => 'subject', 'placeholder' => trans('messages.Subject'), 'required' => '']) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="status">{{trans('messages.Status')}}</label>
					<div class="col-sm-10">
						{{ Form::select('status', [0, 1], null, ['class' => 'form-control', 'id' => 'status', 'placeholder' => trans('messages.SelectStatus') . ' ...', 'required' => "", ]) }}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="template">{{trans('messages.TemplateContent')}}</label>
					<div class="col-sm-10">
					{{ Form::textarea('template', null, array('id' => 'template', 'class' => 'form-control', 'required' => '', 'minlength' => '10')) }}
					<span class="help-block">
							<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal">
								Date formats
							</button>
						</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin/emails')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			</div>
		</div>
	{{ Form::close() }}
	@extends('admin.emails.date-modal')
@stop

@section('content_header')
	<h1>{{trans('messages.AddMail')}}<small>{{trans('messages.NewMail')}}</small></h1>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-columns"></i> {{trans('messages.EmailManagement')}}</li>
	</ol>
@stop

@section('footerjs')
	<script>
		$(document).ready(function(){
			$('#template').wysihtml5({ toolbar: { color: true, }});
			$("#newMail").validate({
				ignore: ":hidden:not(textarea)",
				rules: {
					WysiHtmlField: "required"
				},
			});
		});
	</script>
@stop