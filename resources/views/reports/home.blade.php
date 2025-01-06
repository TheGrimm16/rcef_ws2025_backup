<?php $qr_side = "active"; $qr_home="active"?>

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

        <div class="clearfix"></div>

            @include('layouts.message')

            <div class="col-md-12 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>Select Location</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>

                        <div class="row">
                            <div class="col-md-3">
                                <select name="province" id="province" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Province</option>
                                    @foreach ($d_provinces as $province)
                                        <option value="{{ $province->province }}">{{ $province->province }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <select name="municipality" id="municipality" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Municipality</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="dropoffPoint" id="dropoffPoint" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a dropoff point</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-block btn-success" id="summary_btn"><i class="fa fa-bar-chart"></i> Generate Summary</button>
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                <!-- distribution details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2 id="province_name">Province / Municipality Summary</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="registered_farmers">
                            <thead>
                                <th>Municipality</th>
                                <th>Beneficiaries</th>
                                <th>Male</th>
                                <th>Female</th>
                                <th>Distribution Area</th>
                                <th>Actual Area</th>
                                <th>Bags distributed (20kg/bag)</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="dropoff_name">N/A</td>
                                    <td id="total_farmer_count">N/A</td>
                                    <td id="male_b">N/A</td>
                                    <td id="female_b">N/A</td>
                                    <td id="dist_area">N/A</td>
                                    <td id="actual_area">N/A</td>
                                    <td id="total_bags">N/A</td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                </div><br>
                <!-- /distribution details -->

                <!-- seed details -->
                <div class="x_panel">
                    <div class="x_title">
                        <h2 id="province_name">Seed Varities</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <br/>
                            <table class="table table-hover table-striped table-bordered" id="seed_tbl">
                                <thead>
                                    <th>Variety</th>
                                    <th>Total Bags Distributed (20kg/bag)</th>
                                </thead>
                            </table>
                    </div>
                    </div><br>
                <!-- /seed details -->
            </div>

    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#seed_tbl").DataTable();

        $("#province").on("change", function(e){
            var province = $(this).val();
            $("#municipality").empty().append("<option value='0'>Loading municipalities please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.municipality.dropoff') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#municipality").append(data);
                }
            });
        });

        $("#municipality").on("change", function(e){
            var province = $("#province").val();
            var municipality = $("#municipality").val();

            $("#dropoffPoint").empty().append("<option value='0'>Loading dropoff points please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.dropoff.name') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality
                },
                success: function(data){
                    $("#dropoffPoint").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#dropoffPoint").append(data);
                }
            });
        });

        $("#summary_btn").on("click", function(e){
            if($("#province").val() != '0' &&
               $("#municipality").val() != '0' &&
               $("#dropoffPoint").val() != '0'){

                var province = $("#province").val();
                var municipality = $("#municipality").val();
                var dropoff = $("#dropoffPoint").val();

                $("#total_farmer_count").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#total_farm_area").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#province_name").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#dropoff_name").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#dist_area").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#actual_area").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#total_bags").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#male_b").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#female_b").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('rcef.report.beneficiaries.result') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        dropoff : dropoff
                    },
                    success: function(data){
                        if(data["table_conn"] == 'no_table_found'){
                            $("#total_farmer_count").html("Not Available");
                            $("#total_farm_area").html("Not Available");
                            $("#total_bags").html("Not Available");
                            $("#dist_area").html("Not Available");
                            $("#actual_area").html("Not Available");
                            $("#male_b").html("Not Available");
                            $("#female_b").html("Not Available");
                            
                            $("#province_name").html($("#region option:selected").text() + " > " + $("#province option:selected").text() + " > " + $("#municipality option:selected").text());
                            $("#dropoff_name").html($("#municipality option:selected").text());
                            
                            alert('No distribution data found.');
                        }else{
                            $("#dropoff_name").html($("#municipality option:selected").text());
                            $("#total_farmer_count").html(data["total_farmers"]);
                            $("#dist_area").html(data["dist_area"]);
                            $("#actual_area").html(data["actual_area"]);
                            $("#total_bags").html(data["total_bags"]);
                            $("#male_b").html(data["total_male"]);
                            $("#female_b").html(data["total_female"]);
                    
                            $("#province_name").html($("#province option:selected").text() + " > " + $("#municipality option:selected").text() + " > " + $("#dropoffPoint option:selected").text());
                        
                            $('#seed_tbl').DataTable().clear();
                            $("#seed_tbl").DataTable({
                                "bDestroy": true,
                                "searchHighlight": true,
                                "processing": true,
                                "serverSide": true,
                                "orderMulti": true,
                                "order": [],
                                "ajax": {
                                    "url": "{{ route('rcef.report.beneficiaries.variety') }}",
                                    "dataType": "json",
                                    "type": "POST",
                                    "data":{
                                        "_token": "{{ csrf_token() }}",
                                        "province": province,
                                        "municipality": municipality,
                                        "dropoff" : dropoff
                                    }
                                },
                                "columns":[
                                    {"data": "seed_variety"},
                                    {"data": "total_varieties"}
                                ]
                            });

                        }
                    }
                });

            }else{
                alert('please fill up all required fields...');
            }
        });
    </script>
@endpush
