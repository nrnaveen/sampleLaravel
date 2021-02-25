@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.UsersManagement')}} <small>{{trans('messages.UserMissions')}}</small></h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-user"></i> {{trans('messages.UsersManagement')}}</li>
	</ol>
@stop

@section('content')
	<div class="box">
		<div class="box-body">
			{{ Form::open(array('url' => '/admin/users/' . $user->id . '/missions', 'novalidate' => 'novalidate', 'id' => 'addMission', 'class' => 'form-horizontal',)) }}
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
					<label class="col-sm-2 control-label" for="mission_ids">{{trans('messages.Missions')}}</label>
					<div class="col-sm-10">
						<select id="mission_ids" name="mission_ids[]" class="form-control" required="">
							<option disabled="" selected="" value=""> -- {{ trans('messages.SelectMission')}} -- </option>
							@foreach($missions as $key => $mission)
								<option value="{{$key}}">{{$mission}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						{{Form::submit(trans('messages.Add'), array('class' => 'btn btn-primary', 'id' => 'save'))}}
						<a class="btn btn-default" href="{{url('/admin/users')}}">{{trans('messages.Cancel')}}</a>
					</div>
				</div>
			{{ Form::close() }}
			<table class="table table-hover">
				<thead>
					<tr style="text-align:center">
						<th>{{ trans('messages.User') }}</th>
						<th>{{ trans('messages.Mission') }}</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$usermissions->isEmpty())
						@foreach($usermissions as $usermission)
							<tr>
								<td>{{$usermission->user->name}}</td>
								<td>{{$usermission->mission->code}}</td>
								<td>
									<a class="btn btn-danger" onClick="return confirm('{{trans('messages.AreYouSure')}}')" href="{{ url('/admin/users/' . $user->id . '/missions/' . $usermission->id . '/delete')}}">
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
			{{ $usermissions->render() }}
		</div>
	</div>
@stop

@section('headerjs')
	<link rel="stylesheet" type="text/css" href="{{asset('/assets/plugins/selectize.js/dist/css/selectize.bootstrap3.css')}}">
@stop

@section('footerjs')
	<script type="text/javascript" src="{{asset('/assets/plugins/selectize.js/dist/js/standalone/selectize.js')}}"></script>
	<script>
		$(document).ready(function() {
			var missions = <?php echo json_encode($missions, true); ?>;
			$('#mission_ids').selectize({ minItems: 1, maxItems: 10, items: missions, });
		});
	</script>
@stop