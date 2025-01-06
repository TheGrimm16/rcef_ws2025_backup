@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    <div class="col-md-12 col-sm-12 col-xs-12" style="min-height: 1200px;">

        @include('layouts.message')

        <div class="row">
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Bags Distributed (20kg/bag)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-truck"></i> {{$total_bags_distributed}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Beneficiaries</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-users"></i> {{$total_farmer_beneficiaries}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Area Planted (ha)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-map-marker"></i> {{$total_area_planted}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_title">
                <h2>FILTER OPTIONS</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Station:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9" style="padding: 0">
                            <select name="station" id="station" class="form-control">
                                <option value="0">Please select a philrice station</option>
                                @foreach ($stations as $s_row)
                                    <option value="{{$s_row->stationId}}">{{$s_row->stationName}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-success" id="station_filter_btn"><i class="fa fa-search-plus"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Date Range:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9" style="padding: 0">
                            <input type="text" name="date_range" id="date_range" class="form-control" value="{{$filter_start}} - {{$filter_end}}"/>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-success" id="date_range_btn"><i class="fa fa-search-plus"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Region:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9" style="padding: 0">
                            <select name="region" id="region" class="form-control">
                                <option value="0">Please select a region</option>
                                @foreach ($regions as $r_row)
                                    <option value="{{$r_row->region}}">{{$r_row->region}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-success" id="region_btn"><i class="fa fa-search-plus"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Province:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9" style="padding: 0">
                            <select name="province" id="province" class="form-control">
                                <option value="0">Please select a province</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-success" id="province_btn"><i class="fa fa-search-plus"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Municipality:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9" style="padding: 0">
                            <select name="municipality" id="municipality" class="form-control">
                                <option value="0">Please select a municipality</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-success" id="municipality_btn"><i class="fa fa-search-plus"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
                        <div class="col-md-9 col-sm-9 col-xs-9" style="padding: 0">
                            <button class="btn btn-primary" id="all_filter_btn"><i class="fa fa-list-ol"></i> FILTER TABLE (ALL FILTERS APPLIED)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    GOOGLE SHEETS | WEEKLY REPORT
                </h2>
                <a href="{{route('rcep.google_sheet.weekly')}}" class="btn btn-success pull-right" style="border-radius:20px;"><i class="fa fa-arrow-circle-right"></i> PROCEED TO DATA-ENTRY FORM</a>
                <button id="reset_btn" class="btn btn-default pull-right" style="border-radius:20px;"><i class="fa fa-undo"></i> RESET TABLE</button>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal">
                    <table class="table table-bordered table-striped" id="weekly_tbl">
                        <thead>
                            <th>Report Information</th>
                            <th style="width:100px;">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
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
        $("#date_range").daterangepicker(null,function(a,b,c){
            //console.log(a.toISOString(),b.toISOString(),c)
            
        });

        $('#weekly_tbl').DataTable().clear();
        $("#weekly_tbl").DataTable({
            "searching": false,
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "search_filter": "ALL"
                }
            },
            "columns":[
                {"data": "report_col"},
                {"data": "action", searchable: false}
            ]
        });

        $("#reset_btn").on("click", function(e){
            $('#weekly_tbl').DataTable().clear();
            $("#weekly_tbl").DataTable({
                "searching": false,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "search_filter": "ALL"
                    }
                },
                "columns":[
                    {"data": "report_col"},
                    {"data": "action", searchable: false}
                ]
            });
        });

        $("#region").on("change", function(e){
            var region = $("#region").val();
            //load all provinces
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.weekly.provinces') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#province").empty().append(data);
                }
            });
        });

        $("#province").on("change", function(e){
            var province = $("#province").val();
            //load all municipalities
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.weekly.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append(data);
                }
            });
        });
        
        $("#station_filter_btn").on("click", function(e){
            if($("#station").val() != "0"){
                $('#weekly_tbl').DataTable().clear();
                $("#weekly_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "STATION_FILTER",
                            station: $("#station").val()
                        }
                    },
                    "columns":[
                        {"data": "report_col"},
                        {"data": "action", searchable: false}
                    ]
                });

            }else{
                alert("Please select a PhilRice Branch station.");
            }
        });

        $("#date_range_btn").on("click", function(e){
            $('#weekly_tbl').DataTable().clear();
            $("#weekly_tbl").DataTable({
                "searching": false,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "search_filter": "DATE_RANGE_FILTER",
                        date_range: $("#date_range").val()
                    }
                },
                "columns":[
                    {"data": "report_col"},
                    {"data": "action", searchable: false}
                ]
            });
        });

        $("#region_btn").on("click", function(e){
            if($("#region").val() != "0"){
                $('#weekly_tbl').DataTable().clear();
                $("#weekly_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "REGION_FILTER",
                            region: $("#region").val()
                        }
                    },
                    "columns":[
                        {"data": "report_col"},
                        {"data": "action", searchable: false}
                    ]
                });
            }else{
                alert("Please select a region.");
            }
        });

        $("#province_btn").on("click", function(e){
            if($("#province").val() != "0"){
                $('#weekly_tbl').DataTable().clear();
                $("#weekly_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "PROVINCE_FILTER",
                            province: $("#province").val(),
                            region: $("#region").val()
                        }
                    },
                    "columns":[
                        {"data": "report_col"},
                        {"data": "action", searchable: false}
                    ]
                });
            }else{
                alert("Please select a province.");
            }
        });

        $("#municipality_btn").on("click", function(e){
            if($("#municipality").val() != "0"){
                $('#weekly_tbl').DataTable().clear();
                $("#weekly_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "MUNICIPALITY_FILTER",
                            province: $("#province").val(),
                            region: $("#region").val(),
                            municipality: $("#municipality").val()
                        }
                    },
                    "columns":[
                        {"data": "report_col"},
                        {"data": "action", searchable: false}
                    ]
                });
            }else{
                alert("Please select a province.");
            }
        });

        $("#all_filter_btn").on("click", function(e){
            if($("#station").val() != "0" && $("#region").val() != "0" && $("#province").val() != "0" && $("#municipality").val() != "0"){
                $('#weekly_tbl').DataTable().clear();
                $("#weekly_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.weekly.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "ALL_FILTER",
                            station: $("#station").val(),
                            date_range: $("#date_range").val(),
                            province: $("#province").val(),
                            region: $("#region").val(),
                            municipality: $("#municipality").val()
                        }
                    },
                    "columns":[
                        {"data": "report_col"},
                        {"data": "action", searchable: false}
                    ]
                });
            }else{
                alert("Please fill up all the filters.");
            }
        });
    </script>
@endpush
