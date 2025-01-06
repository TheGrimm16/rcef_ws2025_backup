@extends('layouts.index')

@section('content')
	<div class="row">
        <div class="col-lg-12 mb-4">
            <ol class="breadcrumb" style="font-family: Noto; font-size: 1vw;">
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Edit Role</li>
            </ol>
        </div>
    </div>

	<div class="row">
	    <div class="col-lg-4 margin-tb">
	        <div class="pull-left">
	            <h2 class="mb-4" style="font-family: Fjalla_One; font-size: 3vw;">Edit Role</h2>
	        </div>
	    </div>
	</div>

    {!! Form::model($role, ['method' => 'POST', 'route' => ['roles.update', $role->roleId]]) !!}
    {{ method_field('PATCH') }}
	<div class="row">
		<div class="col-lg-6">
			<div class="card mb-4">
				<div class="card-body">
					<div class="row" style="margin-bottom:15px; font-family: Noto; font-size: 1vw;">
						<div class="col-md-12">
							<span style="color: red;">*</span> Indicates required field
						</div>
					</div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-5 col-form-label" style="font-family: Noto; font-size: 1vw;"><span style="color: red;">*</span> Role Name</div>
                                <div class="col-md-7">
                                    {!! Form::text('name', null, array('placeholder' => 'Role Name', 'class' => ($errors->first('name')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => 'font-family: Noto; font-size: 1vw;')) !!}
            						<div class="invalid-feedback d-block" style="font-family: Noto; font-size: .8vw;">
            							{{ $errors->first('name') }}
            						</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-5 col-form-label" style="font-family: Noto; font-size: 1vw;"><span style="color: red;">*</span> Display Name</div>
                                <div class="col-md-7">
                                    {!! Form::text('display_name', null, array('placeholder' => 'Name', 'class' => ($errors->first('display_name')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => 'font-family: Noto; font-size: 1vw;')) !!}
            						<div class="invalid-feedback d-block" style="font-family: Noto; font-size: .8vw;">
            							{{ $errors->first('display_name') }}
            						</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-5 col-form-label" style="font-family: Noto; font-size: 1vw;"><span style="color: red;">*</span> Description</div>
                                <div class="col-md-7">
                                    {!! Form::textarea('description', null, array('placeholder' => 'Description', 'class' => ($errors->first('description')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => 'font-family: Noto; font-size: 1vw;')) !!}
            						<div class="invalid-feedback d-block" style="font-family: Noto; font-size: .8vw;">
            							{{ $errors->first('description') }}
            						</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-5 col-form-label" style="font-family: Noto; font-size: 1vw;"><span style="color: red;">*</span> Permissions</div>
                                <div class="col-md-7">
                                    <div style="height: 400px; overflow-y: scroll; padding: 10px;">
            							@foreach($permissions as $value)
            								<label style="font-family: Noto; font-size: 1vw;">{!! Form::checkbox('permission[]', $value->permissionId, in_array($value->permissionId, $rolePermissions) ? true : false, array('class' => 'name checkbox')) !!}
            								{{ $value->display_name }}</label>
            								<br/>
            							@endforeach
            						</div>
            						<div class="invalid-feedback d-block" style="font-family: Noto; font-size: .8vw;">
            							{{ $errors->first('permission') }}
            						</div>
                                </div>
                            </div>
                        </div>
                    </div>
					<div id="app2"></div>
				</div>
			</div>
		</div>
	</div>
	{!! Form::close() !!}
@endsection

@push('scripts')
	<script src="{{ asset('public/js/FormLoading.js') }}" type="module"></script>
@endpush
