@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.VersionManagement')}}</h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-info"></i> {{trans('messages.VersionManagement')}}</li>
	</ol>
@stop

@section('content')
	<div class="box">
		<div class="box-body">
			{{ Form::open(array('novalidate' => 'novalidate', 'id' => 'addVersion', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="version">{{trans('messages.Version')}}</label>
					<div class="col-sm-10">
						{{ Form::text('version', null, array('required' => '', 'class' => 'form-control', 'id' => 'version', 'placeholder' => trans('messages.Version'))) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Add'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
					</div>
				</div>
			{{ Form::close() }}
			<table class="table table-hover">
				<thead>
					<tr style="text-align:center">
						<th>
							<a href="{{urlWithQuery('/admin/version', ['query' => 'version', 'sort' => ($queryParam == 'version' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Version') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/version', ['query' => 'status', 'sort' => ($queryParam == 'status' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Status') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$versions->isEmpty())
						@foreach($versions as $version)
							<tr>
								<td>{{$version->version}}</td>
								<td>{{$version->status}}</td>
								<td>
									<a class="btn btn-danger" onClick="return confirm('{{trans('messages.AreYouSure')}}')" href="{{ url('/admin/version/' . $version->id . '/delete')}}">
										<i class="fa fa-trash-o fa-lg"></i>
									</a>
								</td>
							</tr>
						@endforeach
					@else
						<td colspan="5" class="text-center">{{trans('messages.Norecordsfound')}}</td>
					@endif
				</tbody>
			</table>
			{{ $versions->render() }}
		</div>
	</div>
@stop

@section('footerjs')
	<script>
		$(document).ready(function(){
			$('#addVersion').validate();
		});
	</script>
@stop