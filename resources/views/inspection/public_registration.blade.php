@extends('layouts.app')

@section('title', '| Inspector Registration')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/parsely.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">

            @include('layouts.message')

            <div class="panel panel-default">
                <div class="panel-heading">Registration Form</div>
                <div class="panel-body">
                    <form action="{{ route('rcef.registry.registration.save') }}" method="POST" data-parsley-validate="">
                    {!! csrf_field() !!}
                        <div class="x_content form-horizontal form-label-left">
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">ID Number <span>*</span></label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" class="form-control" name="id_number" id="id_number" data-inputmask="'mask': '99-9999'" placeholder="XX-XXXX" data-parsley-required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">First Name <span>*</span></label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" data-parsley-required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Middle Name <span>*</span></label>
                                <div class="col-md-5 col-sm-5 col-xs-5">
                                    <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" data-parsley-required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Last Name <span>*</span></label>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" data-parsley-required readonly disabled>
                                </div>
                                <div class="col-md-3 col-sm-3 col-xs-3">
                                    <input type="text" class="form-control" name="suffix_name" id="suffix_name" placeholder="Suffix" readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Email Address <span>*</span></label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="email" class="form-control" name="email_address" id="email_address" placeholder="xx.xxxx@philrice.gov.ph" data-parsley-required readonly disabled>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Position <span>*</span></label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="text" class="form-control" name="emp_position" id="emp_position" placeholder="(e.g. ITO III)" data-parsley-required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Station <span>*</span></label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="text" class="form-control" name="emp_station" id="emp_station" placeholder="(e.g. PHILRICE-CES)" data-parsley-required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Office </label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="text" class="form-control" name="emp_office" id="emp_office" placeholder="(e.g. ODEDD)" required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Division </label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="text" class="form-control" name="emp_division" id="emp_division" placeholder="(e.g. ISD)" required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2">Unit </label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="text" class="form-control" name="emp_unit" id="emp_unit" placeholder="" required readonly disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <input type="submit" class="btn btn-success pull-right" value="Process Inspector Profile">
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>

    <script>
        $("#id_number").on("keyup", function(e){
            var id_number = $(this).val();
            if(id_number.length == 7){

                $("#first_name").val("Loading data please wait...");
                $("#middle_name").val("Loading data please wait...");
                $("#last_name").val("Loading data please wait...");
                $("#suffix_name").val("Loading data please wait...");
                $("#email_address").val("Loading data please wait...");
                $("#emp_station").val("Loading data please wait...");
                $("#emp_position").val("Loading data please wait...");
                $("#emp_office").val("Loading data please wait...");
                $("#emp_division").val("Loading data please wait...");
                $("#emp_unit").val("Loading data please wait...");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('rcef.inspector.details') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_number: id_number
                    },
                    success: function(data){
                        $("#first_name").val(data["firstName"]);
                        $("#middle_name").val(data["middleName"]);
                        $("#last_name").val(data["lastName"]);
                        $("#suffix_name").val(data["extName"]);
                        $("#email_address").val(data["email"]);
                        $("#emp_station").val(data["station"]);
                        $("#emp_position").val(data["position"]);
                        $("#emp_office").val(data["office"]);
                        $("#emp_division").val(data["division"]);
                        $("#emp_unit").val(data["unit"]);
                    }
                });
            }
        });
    </script>
@endpush