@extends('admin.layout')

@section('content_header')
	<h1>{{trans('messages.AbsenceTypesManagement')}} <small>{{trans('messages.DetailsOfAbsenceTypes')}}</small></h1></br>
	<a class="btn btn-primary" href="{{url('/admin/absence-types/add')}}">{{trans('messages.AddAbsenceType')}}</a>
	<ol class="breadcrumb">
		<li><a href="{{url('/admin')}}"><i class="fa fa-home"></i> {{trans('messages.Home')}}</a></li>
		<li class="active"><i class="fa fa-buysellads"></i> {{trans('messages.AbsenceTypesManagement')}}</li>
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
							<a href="{{urlWithQuery('/admin/absence-types', ['query' => 'slug', 'sort' => ($queryParam == 'slug' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Slug') }}</i>
							</a>
						</th> 
						 <th>
							<a href="{{urlWithQuery('/admin/absence-types', ['query' => 'label', 'sort' => ($queryParam == 'label' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Label') }}</i>
							</a>
						 </th> 
						<th>
							<a href="{{urlWithQuery('/admin/absence-types', ['query' => 'status', 'sort' => ($queryParam == 'status' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.Status') }}</i>
							</a>
						</th>
						<th>
							<a href="{{urlWithQuery('/admin/absence-types', ['query' => 'auto_approve', 'sort' => ($queryParam == 'auto_approve' && $sort == 'DESC') ? 'ASC' : 'DESC'] )}}">
								<i class="fa fa-sort" aria-hidden="true">{{ trans('messages.AutoApprove') }}</i>
							</a>
						</th>
						<th>{{ trans('messages.Color') }}</th>
						<th>{{ trans('messages.Action') }}</th>
					</tr>
				</thead>
				<tbody>
					@if(!$types->isEmpty())
						@foreach($types as $type)
							<tr>
								<td>{{$type->slug}}</td>
								<td>{{$type->label}}</td>
								<td>{{$type->status}}</td>
								<td>{{$type->auto_approve}}</td>
								<td><div style="background: {{$type->color}};width: 50px;height: 25px;border-radius: 10px;"></div></td>
								<td>
									<a class="btn btn-primary" href="{{ url('/admin/absence-types/edit', [ 'id' => $type->id, ])}}">
										<i class="fa fa-edit fa-lg"></i>
									</a>
									@if(!in_array($type->slug, $default_types))
										<form class="delete-form" method="POST" action="{{ url('/admin/absence-types/delete', [ 'id' => $type->id, ])}}" onsubmit="return confirm('{{trans('messages.AreYouSure')}}')">
											{!! Form::token() !!}
											<input type="hidden" name="_method" value="DELETE">
											<button class="btn btn-danger">
												<i class="fa fa-trash-o fa-lg"></i>
											</button>
										</form>
									@else
										<a class="btn btn-danger" disabled><i class="fa fa-trash fa-lg"></i></a>
									@endif
								</td>
							</tr>
						@endforeach
					@else
						<td colspan="5" class="text-center">{{trans('messages.Norecordsfound')}}</td>
					@endif
				</tbody>
			</table>
			{{ $types->render() }}
		</div>
	</div>
@stop