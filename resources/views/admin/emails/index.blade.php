@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.EmailManagement')}}</h1></br>
	<a class="btn btn-primary" href="{{url('/admin/emails/add')}}">{{trans('messages.AddMail')}}</a>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-columns"></i> {{trans('messages.EmailManagement')}}</li>
	</ol>
@stop

@section('content')
	<div class="box">
		<div class="box-header">
			<h3 class="box-title"></h3>
			<div class="box-tools col">
				<form>
					<div class="input-group input-group-sm col-xs-12 col-md-4 pull-right">
						<input type="text" name="q" class="form-control pull-right" placeholder="{{trans('messages.Search')}}" value="{{$search}}">
						<div class="input-group-btn">
							<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="box-body table-responsive no-padding">
			<table class="table table-hover">
				<thead>
					<tr style="text-align:center">
						<th>
							<a href="{{urlWithQuery('/admin/emails', ['query' => 'email', 'sort' => ($queryParam == 'email' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Template') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/emails', ['query' => 'status', 'sort' => ($queryParam == 'status' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Status') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$emails->isEmpty())
						@foreach($emails as $email)
							<tr>
								<td>{{$email->name}}</td>
								<td>{{$email->status}}</td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/emails/edit', ['id' => $email->id])}}">
										<i class="fa fa-edit fa-lg"></i>
									</a>
									<form class="delete-form" method="POST" action="{{ url('/admin/emails/delete', [ 'id' => $email->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
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
			{{ $emails->render() }}
		</div>
	</div>
@stop