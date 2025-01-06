@extends('layouts.index')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <ol class="breadcrumb" style="font-family: Noto; font-size: 1vw;">
                <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
                <li class="breadcrumb-item active">Create New Permission</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="pull-left">
                <h2 class="mb-4" style="font-family: Fjalla_One; font-size: 3vw;">Create New Permission</h2>
            </div>
        </div>
    </div>

    {!! Form::open(array('route' => 'permissions.store','method'=>'POST')) !!}

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
                                <div class="col-md-5 col-form-label" style="font-family: Noto; font-size: 1vw;"><span style="color: red;">*</span> Permission Name</div>
                                <div class="col-md-7">
                                    {!! Form::text('name', null, array('placeholder' => 'Permission Name', 'class' => ($errors->first('name')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => 'font-family: Noto; font-size: 1vw;')) !!}
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
                                    {!! Form::text('display_name', null, array('placeholder' => 'Display Name', 'class' => ($errors->first('display_name')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => 'font-family: Noto; font-size: 1vw;')) !!}
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

                    <div id="app"></div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection

@push('scripts')
    <script src="{{ asset('public/js/FormLoading.js') }}" type="module"></script>
@endpush
