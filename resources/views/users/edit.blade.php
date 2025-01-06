@extends('layouts.index')

@section('content')
    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>User Management</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Edit User</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        {!! Form::model($user, ['method' => 'POST', 'route' => ['users.update', $user->userId]]) !!}
                        {{ method_field('PATCH') }}

                        <p><span style="color: red;">*</span> Indicates required field</p>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""><span style="color: red;">*</span> First Name</label>
                            <div class="col-md-7">
                                {!! Form::text('firstName', null, array('placeholder' => 'First Name', 'class' => ($errors->first('firstName')) ? 'form-control is-invalid form-control-lg' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('firstName') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Suffix</label>
                            <div class="col-md-4">
                                {!! Form::text('extName', null, array('placeholder' => 'Suffix', 'class' => ($errors->first('extName')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('extName') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Middle Name</label>
                            <div class="col-md-7">
                                {!! Form::text('middleName', null, array('placeholder' => 'Middle Name', 'class' => ($errors->first('middleName')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('middleName') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""><span style="color: red;">*</span> Last Name</label>
                            <div class="col-md-7">
                                {!! Form::text('lastName', null, array('placeholder' => 'Last Name', 'class' => ($errors->first('lastName')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('lastName') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""><span style="color: red;">*</span> Username</label>
                            <div class="col-md-7">
                                {!! Form::text('username', null, array('placeholder' => 'Username', 'class' => ($errors->first('username')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('username') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""><span style="color: red;">*</span> Password</label>
                            <div class="col-md-7">
                                {!! Form::password('password', array('placeholder' => 'Password', 'class' => ($errors->first('password')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('password') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Confirm Password</label>
                            <div class="col-md-7">
                                {!! Form::password('password2', array('placeholder' => 'Confirm Password', 'class' => 'form-control form-control-lg', 'style' => '')) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""><span style="color: red;">*</span> Email</label>
                            <div class="col-md-7">
                                {!! Form::email('email', null, array('placeholder' => 'Email', 'class' => ($errors->first('email')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('email') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Alternate Email</label>
                            <div class="col-md-7">
                                {!! Form::email('secondaryEmail', null, array('placeholder' => 'Alternate Email', 'class' => ($errors->first('secondaryEmail')) ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('secondaryEmail') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Sex</label>
                            <div class="col-md-7">
                                {!! Form::select('sex', ['M' => 'Male', 'F' => 'Female'], null, ['class' => 'form-control form-control-lg', 'style' => '']) !!}
                            </div>
                        </div>

                        <div class="form-group row" style="display: none;">
                            <label class="col-md-5 control-label" style="">Assigned Region</label>
                            <div class="col-md-7">
                                {{-- {!! Form::select('region', $regions, null, ['placeholder' => 'Select', 'class' => 'form-control form-control-lg', 'style' => '']) !!} --}}
                                <input type="text" name="region">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Assigned Province</label>
                            <div class="col-md-7">
                                {!! Form::select('province', $provinces, null, ['placeholder' => '--SELECT ASSIGNED PROVINCE--', 'class' => 'form-control form-control-lg', 'style' => '']) !!}
                                <p class="form-text text-muted">
                                    Assigned province for Seed Inspector.
                                </p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""> Assigned Municipality</label>
                            <div class="col-md-7">
                                <select name="municipality" class="form-control form-control-lg" style="">
                                    <option>--SELECT ASSIGNED MUNICIPALITY--</option>
                                </select>
                                <p class="form-text text-muted">
                                    Assigned municipality for Seed Inspector.
                                </p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Agency</label>
                            <div class="col-md-7">
                                {!! Form::select('agencyId', $agencies, null, ['placeholder' => '--SELECT AGENCY--', 'class' => 'form-control form-control-lg', 'style' => '']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Station</label>
                            <div class="col-md-7">
                                {!! Form::select('stationId', $stations, null, ['placeholder' => '--SELECT PHILRICE STATION--', 'class' => ($errors->first('stationId') ? 'form-control form-control-lg is-invalid' : 'form-control form-control-lg'), 'style' => '']) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('stationId') }}
                                </div>
                                <p class="form-text text-muted">
                                    Select station for PhiRice user.
                                </p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Position</label>
                            <div class="col-md-7">
                                {!! Form::text('position', null, array('placeholder' => 'Position', 'class' => 'form-control form-control-lg', 'style' => '')) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style="">Designation</label>
                            <div class="col-md-7">
                                {!! Form::text('designation', null, array('placeholder' => 'Designation', 'class' => 'form-control form-control-lg', 'style' => '')) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-5 control-label" style=""><span style="color: red;">*</span> Role</label>
                            <div class="col-md-7">
                                {!! Form::select('roles[]', $roles,[], array('class' => ($errors->first('roles')) ? 'form-control form-control-lg is-invalid js-example-basic-multiple' : 'form-control form-control-lg js-example-basic-multiple','multiple', 'style' => '')) !!}
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('roles') }}
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success" style="float: right; margin-top: 10px;">Submit</button>
                    	{!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('public/js/FormLoading.js') }}" type="module"></script>

    <script>
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        });

        window.Laravel = {!! json_encode([
            // 'api_token' => $data['api_token'],
            'csrf_token' => csrf_token(),
            'provRoute' => route('users.province'),
            'regionRoute' => route('users.region')
        ]) !!};
    </script>
@endpush
