@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.HomeGreeting')}}</h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-home"></i> {{trans('messages.HomeGreeting')}}</li>
	</ol>
@endsection

@section('content')
	<div class="box">
		<div class="box-body">
			{{ Form::model($homecontent, array('novalidate' => 'novalidate', 'id' => 'addContent', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="firstname">{{trans('messages.MessageContent')}}</label>
					<div class="col-sm-10">
						{{ Form::textarea('content', null, array('id' => 'content', 'class' => 'form-control', 'required' => '', 'minlength' => '5')) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Save'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			{{ Form::close() }}
		</div>
	</div>
@stop

@section('headerjs')
	<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/summernote/summernote.css')}}">
@stop

@section('footerjs')
	<script src="{{asset('/assets/plugins/summernote/summernote.js')}}"></script>
	<script>
		$(document).ready(function(){
			// $('#content').wysihtml5({ toolbar: { color: true, }});
			$('#content').summernote({
				toolbar: [
					['style', ['style']],
					['font', ['bold', 'underline', 'clear']],
					['fontname', ['fontname']],
					['color', ['color']],
					['para', ['ul', 'ol', 'paragraph']],
					['table', ['table']],
					// ['insert', ['link', 'picture', 'video']],
					['insert', ['link', 'picture']],
					// ['view', ['fullscreen', 'codeview', 'help']]
				],
			});
			$("#addContent").validate({
				ignore: ":hidden:not(textarea)",
				rules: {
					WysiHtmlField: "required"
				},
			});
		});
	</script>
@stop