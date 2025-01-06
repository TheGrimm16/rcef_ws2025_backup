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

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
                Yield Tables
            </h2>
            <!--<button class="btn btn-success btn-sm" style="float:right;" id="excel_btn">
                Export to Excel (Statistics)
            </button>-->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <div class="accordion">
                @foreach ($provinces as $item)
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0" style="margin:0">
                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                    {{$item->province}}
                                </button>
                            </h5>
                            <button class="btn btn-warning btn-sm" style="top: 10%;margin-right: 10px;position: absolute;right: 0%;" data-toggle="modal" data-target="#show_municipal_tables" data-id="{{$item->report_id}}" data-province="{{$item->province}}"><i class="fa fa-eye"></i> VIEW MUNICIPAL TABLES</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

    <!-- MUNICIPAL TABLES MODAL -->
    <div id="show_municipal_tables" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xs" style="width: 900px; margin: auto; position: relative;top:4%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="province_name_lbl">
                        <span>[PROVINCE]]</span><br>
                    </h4>
                    <span id="coop_accreditation_modal"></span>
                </div>
                <div class="modal-body">
                    <div>
                        <select name="municipality_list" id="municipality_list" class="form-control">
                            <option value="0">Select a municipality</option>
                        </select>
                    </div>
                    
                    <p></p>

                    <div>                    
                        <div class="row">
                            <div class="col-md-4">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Yield t/ha (Mean)</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                             <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="mean_yield"><i class="fa fa-bar-chart"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Yield t/ha (Min)</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                             <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="min_yield"><i class="fa fa-bar-chart"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Yield t/ha (Max)</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                             <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="max_yield"><i class="fa fa-bar-chart"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Total # of observations</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                             <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="total_observations"><i class="fa fa-check-circle"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>Total # of farmer beneficiaries</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                             <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="total_beneficiaries"><i class="fa fa-users"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2>STDV</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content form-horizontal form-label-left">
                                        <div class="row tile_count" style="margin: 0">
                                             <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                                <div class="count" id="stdv_value"><i class="fa fa-bar-chart"></i> --</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- MUNICIPAL TABLES MODAL -->
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#show_municipal_tables").on('show.bs.modal', function (e) {
            var province_name = $(e.relatedTarget).data('province');
            $("#province_name_lbl").html(province_name);
            
            $.ajax({
                type: 'POST',
                url: "{{ route('yield_ui.provinces') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    province_name: province_name
                },
                success: function(data){
                    $("#municipality_list").empty().html(data);
                }

            });
        });

        $("#municipality_list").on("change", function(e){
            var province_name = $("#province_name_lbl").html();
            var municipality_name = $("#municipality_list option:selected").val();

            $("#min_yield").empty().html("loading...");
            $("#max_yield").empty().html("loading...");
            $("#mean_yield").empty().html("loading...");
            $("#total_observations").empty().html("loading...");
            $("#total_beneficiaries").empty().html("loading...");


            $.ajax({
                type: 'POST',
                url: "{{ route('yield_ui.data_table') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    province_name: province_name,
                    municipality_name: municipality_name
                },
                success: function(data){
                   $("#min_yield").empty().html("<i class='fa fa-bar-chart'></i> "+data['min_yield']+"");
                   $("#max_yield").empty().html("<i class='fa fa-bar-chart'></i> "+data['max_yield']+"");
                   $("#mean_yield").empty().html("<i class='fa fa-bar-chart'></i> "+data['mean_yield']+"");
                   $("#total_observations").empty().html("<i class='fa fa-check-circle'></i> "+data['total_observations']+"");
                   $("#total_beneficiaries").empty().html("<i class='fa fa-users'></i> "+data['total_farmers']+"");
                }
            });

            $("#stdv_value").empty().html("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('yield_ui.standard_dev') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    province_name: province_name,
                    municipality_name: municipality_name
                },
                success: function(data){
                    $("#stdv_value").empty().html("<i class='fa fa-bar-chart'></i> "+data+"");
                }
            });

        });
    </script>
@endpush
