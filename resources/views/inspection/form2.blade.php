<?php $inspection_side = "active"; $inspection_form="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
    .x_content {
        padding: 0 5px 6px;
        float: left;
        clear: both;
        margin-top: 0;
    }
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>

        <form action="{{ route('rcef.inspector.save') }}" method="POST" id="registryForm" data-parsley-validate>
        {!! csrf_field() !!}
        <div class="clearfix"></div>

            @include('layouts.message')

            <div class="col-md-6 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>1. Choose a Location</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Region:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="drop_region" id="drop_region" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Region</option>
                                    @foreach($delivery_details as $delivery)
                                        <option value="{{ $delivery->region }}">{{ $delivery->region }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Province:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="drop_province" id="drop_province" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Province</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="drop_municipality" id="drop_municipality" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Municipality</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <button class="btn btn-success btn-block" id="loc_btn"><i class="fa fa-search-plus"></i> Search Confirmed Deliveries</button>
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /delivery details -->

                <!-- seed grower details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2 style="width: 100%;vertical-align">2. Confirmed Deliveries</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Ticket #:</label>

                    <div class="form-group">
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="dropOffID" id="dropOffID" class="form-control" data-parsley-min="1">
                                <option value="0">Please specify the address</option>
                            </select>
                        </div>
                    </div>
					<div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
                        <div class="col-md-10 col-sm-10 col-xs-10" id="batch_list_container">

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Delivery Date:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="delivery_date" id="delivery_date" readonly>
                        </div>
                    </div>
                </div>
                </div>
                <!-- /seed grower details -->
            </div>


            <div class="col-md-6 col-sm-6 col-xs-6">
                <!-- farm performance -->
                <div class="x_panel">
                    <div class="x_title">
                    <h2>3. Delivery Inspector</h2>
                    <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Personnel <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="inspectorID" id="inspectorID" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a delivery inspector</option>
                                    @foreach($inspector_details as $inspector_detail)
                                        @if($inspector_detail->middleName = '' || $inspector_detail->extName = '')
                                            <option value="{{ $inspector_detail->userId }}">{{ $inspector_detail->firstName }} {{ $inspector_detail->lastName }} - ({{ $inspector_detail->username }})</option>
                                        @else
                                            <option value="{{ $inspector_detail->userId }}">{{ $inspector_detail->firstName }} {{ $inspector_detail->middleName }} {{ $inspector_detail->lastName }} {{ $inspector_detail->extName }} - ({{ $inspector_detail->username }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="ln_solid"></div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Inspect Date <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="text" name="date_of_inspection" id="date_of_inspection" class="form-control" readonly/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Remarks:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                               <textarea name="pmo_remarks" class="form-control" id="pmo_remarks" rows="5" placeholder="Please enter your remarks..."></textarea>
                            </div>
                        </div>

                        <div class="form-group" id="save_validate_btn" style="display:none">
                            <div class="col-md-9 col-md-offset-3">
                                <input type="reset" class="btn btn-round btn-danger" value="Reset Fields">
                                <input type="submit" id="submit_btn" class="btn btn-round btn-success" value="save & Validate">
                            </div>
                        </div>
                    </div>
                    </div><br>
                <!-- /farm performance -->
        </div>
        </form>



    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#inspectorID").select2();
        $("#ticket_list").select2({
            tags: true
        });

        $("#drop_region").on("change", function(e){
            var region = $(this).val();
            $("#coop_region").val("loading please wait...");
            $("#drop_province").empty().append("<option value='0'>Loading provinces please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.provinces.dropoff') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#drop_province").empty().append("<option value='0'>Please select a region</option>");
                    $("#drop_province").append(data);
                }
            });
        });

        $("#drop_province").on("change", function(e){
            var province = $(this).val();
            $("#drop_municipality").empty().append("<option value='0'>Loading municipalities please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.municipalities.dropoff') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#drop_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#drop_municipality").append(data);
                }
            });
        });

        $("#loc_btn").on("click", function(e){
            e.preventDefault();
            if($("#drop_region").val() != '0' &&
               $("#drop_province").val() != '0' &&
               $("#drop_munmicipality").val() != '0'){

                var region = $("#drop_region").val();
                var province = $("#drop_province").val();
                var municipality = $("#drop_municipality").val();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.dropoff.search') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        region: region,
                        province: province,
                        municipality: municipality
                    },
                    success: function(data){
						$("#batch_list_container").empty().html("");
                        $("#dropOffID").empty().append("<option value='0'>Please select a drop off point ticket</option>");
                        $("#dropOffID").append(data);
                        alert('you can now select a drop off point');
                    }
                });

            }else{
                alert('please select a region, province and municipality');
            }

        });

        $("#dropOffID").on("change", function(e){
            var deliveryId = $(this).val();
            $("#delivery_date").val("loading ticket details please wait...");
            $("#date_of_inspection").val("loading please wait...");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.dropoff.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    deliveryId: deliveryId
                },
                success: function(data){
					if(data["batch_string"] != ""){
						$("#save_validate_btn").css("display", "block");
						$("#delivery_date").val(data["delivery_date"]);
						$("#batch_list_container").empty().html(data["batch_string"]);
						$("#date_of_inspection").val(data["delivery_date"]);
					}else{
						$("#save_validate_btn").css("display", "none");
						$("#delivery_date").val("Not available.");
						$("#batch_list_container").empty().html("");
						$("#date_of_inspection").val("Not available.");
						alert("There are no deliveries available for assignment of seed inspector.");
					}                    
                }
            });
        });

        //check fields...
        $("#submit_btn").on("click", function(e){
            e.preventDefault();

            if($("#inspectorID").val() == "0"){
                alert("Please select a delivery inspector");
            }else if($("#pmo_remarks").val() == ""){
                alert("Please populate the remarks field...");
            }else{
                $("#registryForm").submit();
            }
        })
    </script>
@endpush
