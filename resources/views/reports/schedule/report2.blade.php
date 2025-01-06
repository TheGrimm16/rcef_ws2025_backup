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

            <div class="row" style="display: inline-block;">
                
            <div class="col-md-12 col-sm-12 col-xs-12">

                <div class="alert alert-success alert-dismissible fade in" role="alert" id="alert_msg" style="display:none">
                    <strong><div class='fa fa-refresh fa-spin'></div> Loading data please wait... </strong> 
                </div>

                <div class="tile_count">
                    <div class="col-md-2 col-sm-4  tile_stats_count">
                        <span class="count_top"><i class="fa fa-building"></i> Covered Municipalities</span>
                        <div class="count" id="total_mun_count">--</div>
                        <span class="count_bottom" id="total_prov_count">out of <i class="green">N/A </i> Provinces</span>
                    </div>

                    <div class="col-md-3 col-sm-4  tile_stats_count">
                        <span class="count_top"><i class="fa fa-users"></i> Partial Beneficiaries</span>
                    <div class="count" id="total_farmer_count">--</div>
                        <span class="count_bottom"><i class="green" id="total_male_count">N/A Male </i>,<i class="red" id="total_female_count">N/A Female </i></span>
                    </div>

                    <div class="col-md-2 col-sm-4  tile_stats_count">
                        <span class="count_top"><i class="fa fa-certificate"></i> Actual Area (ha)</span>
                        <div class="count" id="total_area_count">--</div>
                    </div>

                    <div class="col-md-2 col-sm-4  tile_stats_count">
                        <span class="count_top"><i class="fa fa-certificate"></i> Distribution Area (ha)</span>
                        <div class="count" id="total_dist_count">--</div>
                    </div>

                    <div class="col-md-3 col-sm-4  tile_stats_count">
                        <span class="count_top"><i class="fa fa-calculator"></i> Bags Distributed</span>
                        <div class="count" id="total_bags_count">--</div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>Select Municipality / Province / Dropoff Point <small>Data will be generated every day at 12 MN</small></h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>

                        <div class="row">
                            <div class="col-lg-9 col-md-9 col-sm-12">
                                <select name="province" id="province" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Province</option>
                                    @foreach ($d_provinces as $province)
                                        <option value="{{ $province->province }}">{{ $province->province }}</option>
                                    @endforeach
                                </select>

                                <select name="municipality" id="municipality" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Municipality</option>
                                </select>

                                <select name="dropoffPoint" id="dropoffPoint" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a dropoff point</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-block btn-success" id="summary_btn"><i class="fa fa-bar-chart"></i> Generate Summary</button>
                                <button class="btn btn-block btn-warning" id="excel_btn"><i class="fa fa-file-excel-o"></i> Download Excel File</button>
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">

                <!-- distribution details (municipal) -->
                <div class="x_panel" id="tbl_provincial" style="display:none">
                    <div class="x_title">
                        <h2 id="province_name">Distribution Summary (Provincial)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <br/>
                            <table class="table table-hover table-striped table-bordered">
                                <thead>
                                    <th>Municipality</th>
                                    <th>Beneficiaries</th>
                                    <th>Distribution Area</th>
                                    <th>Actual Area</th>
                                    <th>Bags distributed (20kg/bag)</th>
                                </thead>
                                <tbody id="tbl_provincial_body"></tbody>
                            </table>
                    </div>
                    </div><br>
                    <!-- /distribution details (municipal) -->

                <!-- distribution details (municipal) -->
                <div class="x_panel" id="municipal_panel" style="display:block">
                <div class="x_title">
                    <h2 id="province_name">Distribution Summary</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="report_tbl">
                            <thead>
                                <th>Municipality</th>
                                <th>Beneficiaries</th>
                                <th>Distribution Area</th>
                                <th>Actual Area</th>
                                <th>Bags distributed (20kg/bag)</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="municipality_name">N/A</td>
                                    <td id="total_farmers">N/A</td>
                                    <td id="dist_area">N/A</td>
                                    <td id="actual_area">N/A</td>
                                    <td id="total_bags">N/A</td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                </div><br>
                <!-- /distribution details (municipal) -->

                <!-- gender details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2 id="province_name">Gender / Sex Statistics</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                                <th width="20%">Sex</th>
                                <th width="70%">Total Count</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Male Farmers:</td>
                                    <td id="total_male">NULL</td>
                                </tr>
                                <tr>
                                    <td>Female Farmers:</td>
                                    <td id="total_female">NULL</td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                </div><br>
                <!-- /gender details -->

                <!-- seeds details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2 id="province_name">Seeds Distributed</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="seed_tbl">
                            <thead>
                                <th>Variety</th>
                                <th>Bags distributed (20kg/bag)</th>
                            </thead>
                        </table>
                </div>
                </div><br>
                <!-- /seeds details -->
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

        /* HEADER AJAX */
        $("#alert_msg").css("display", "block");
        $.ajax({
            type: 'POST',
            url: "{{ route('rcep.report.total') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                $("#total_mun_count").empty().html(data["total_municipalities"]);
                $("#total_prov_count").empty().html("out of " + data["total_provinces"] + " Provinces");
                $("#total_farmer_count").empty().html(data["total_farmers"]);
                $("#total_area_count").empty().html(data["actual_area"]);
                $("#total_dist_count").empty().html(data["dist_area"]);
                $("#total_bags_count").empty().html(data["total_bags"]);
                $("#total_male_count").empty().html(data["total_male"] + " Male");
                $("#total_female_count").empty().html(data["total_female"]+ " Female");

                $("#alert_msg").css("display", "none");
            }
        });
        /* HEADER AJAX */

        $("#report_tbl").DataTable();
        $('#seed_tbl').DataTable()

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

                $("#tbl_provincial").css("display", "none");
                $("#municipal_panel").css("display", "block");

                var province = $("#province").val();
                var municipality = $("#municipality").val();
                var dropoff = $("#dropoffPoint").val();

                $("#municipality_name").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#total_farmers").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#dist_area").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#actual_area").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#total_bags").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#total_male").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                $("#total_female").html("Loading... <div class='fa fa-spinner fa-spin'></div>");
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('rcef.report.scheduled.post') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        dropoff : dropoff
                    },
                    success: function(data){
                        if(data["table_conn"] == 'no_table_found'){
                           // no data returned
                        }else{
                            // populate datatable
                            $("#municipality_name").empty().html(data["municipality_name"]);
                            $("#total_farmers").empty().html(data["total_farmers"]);
                            $("#dist_area").empty().html(data["dist_area"]);
                            $("#actual_area").empty().html(data["actual_area"]);
                            $("#total_bags").empty().html(data["total_bags"]);
                            $("#total_male").empty().html(data["total_male"]);
                            $("#total_female").empty().html(data["total_female"]);

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

            }else if($("#province").val() != '0' &&
                     $("#municipality").val() == '0' &&
                     $("#dropoffPoint").val() == '0'){

                    $("#municipal_panel").css("display", "none");
                    $("#tbl_provincial").css("display", "block");

                    var province = $("#province").val();

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcef.report.scheduled.post.provincial') }}",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "province": province,
                        },
                        success: function(data){
                            if(data == "no_distribution" || data == "no_database"){
                                alert("No distribution data.");
                            }else{
                                $("#tbl_provincial_body").empty().append(data["return_str"]);
                                $("#total_male").empty().html(data["total_male"]);
                                $("#total_female").empty().html(data["total_female"]);

                                $('#seed_tbl').DataTable().clear();
                                $("#seed_tbl").DataTable({
                                    "bDestroy": true,
                                    "searchHighlight": true,
                                    "processing": true,
                                    "serverSide": true,
                                    "orderMulti": true,
                                    "order": [],
                                    "ajax": {
                                        "url": "{{ route('rcef.report.beneficiaries.variety.provincial') }}",
                                        "dataType": "json",
                                        "type": "POST",
                                        "data":{
                                            "_token": "{{ csrf_token() }}",
                                            "province": province,
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

        $("#excel_btn").on("click", function(e){
            if($("#province").val() != '0' &&
               $("#municipality").val() != '0' &&
               $("#dropoffPoint").val() != '0'){

                var province = $("#province").val();
                var municipality = $("#municipality").val();
                var dropoff = $("#dropoffPoint").val();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('rcef.report.excel') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        dropoff : dropoff
                    },
                    success: function (response, textStatus, request) {
                        if(response == "no_data"){
                            alert("No distribution data");
                        }else{
                            var a = document.createElement("a");
                            a.href = response.file; 
                            a.download = response.name;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                        }
                        
                    }
                });
            }
        });
    </script>
@endpush
