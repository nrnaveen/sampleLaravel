@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.ContactManagement')}} <small>{{trans('messages.DetailsofContact')}}</small></h1></br>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-address-book"></i> {{trans('messages.ContactManagement')}}</li>
	</ol>
@stop

@section('content')
	<div class="box">
		<div class="box-header">
			<h3 class="box-title"></h3>
			
		</div>
		<div class="box-body table-responsive no-padding">
			<table class="table table-hover">
				<thead>
					<tr class="row text-center">
						<th class="col-md-3">{{trans('messages.User')}}</th>
						<th class="col-md-4">{{trans('messages.Subject')}}</th>
						<th class="col-md-4">{{trans('messages.Description')}}</th>
						<th class="col-md-3">{{trans('messages.Attachements')}}</th>
						<th class="col-md-2">{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$contact->isEmpty())
						@foreach($contact as $con)
							<tr class="row">
								<td class="col-md-3">{{$con->user->name}}</td>
								<td class="col-md-4">{{$con->subject}}</td>
								<td class="col-md-4">{{$con->description}}</td>
								<td class="col-md-3">
									@if($con->attachement_1)
										<a href="{{url(str_replace('/image/', '/image/thumbnail/', $con->attachement_1))}}" target="_blank">
											<img src="{{url($con->attachement_1)}}" height="42" width="42" class="css-class" alt="alt text">
										</a>
									@endif
									@if($con->attachement_2)
										<a href="{{url(str_replace('/image/', '/image/thumbnail/', $con->attachement_2))}}" target="_blank">
											<img src="{{url($con->attachement_2)}}" height="42" width="42" class="css-class" alt="alt text">
										</a>
									@endif
								</td>
								<td class="col-md-2">
									<form class="delete-form" method="POST" action="{{ url('/admin/contact/delete', [ 'id' => $con->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
										{!! Form::token() !!}
										<input type="hidden" name="_method" value="DELETE">
										<button class="btn btn-danger">
											<i class="fa fa-trash-o fa-lg"></i>
										</button>
									</form>
								</td>
							</tr>
						@endforeach
					@else
						<td colspan="5" class="text-center">{{trans('messages.Norecordsfound')}}</td>
					@endif
				</tbody>
			</table>
			{{ $contact->render() }}
		</div>
	</div>
@stop