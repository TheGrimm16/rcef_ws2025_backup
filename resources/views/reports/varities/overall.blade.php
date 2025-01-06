
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

    <div class="x_panel">
        <div class="x_title">
            <h2>
                Seed Variety Report
            </h2>
            <button id="download_btn" class="btn btn-success btn-sm pull-right"><i class="fa fa-download"></i> DOWNLOAD EXCEL FILE (OVERALL DATA)</button>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="bs-example" data-example-id="simple-jumbotron">     
                <div class="jumbotron">
                    <h3 style="font-size: 41px;font-weight: 600;">@if(!empty($total_seed_data['total_seed_data']) && isset($total_seed_data['total_seed_data'][0]))
    {{ number_format($total_seed_data['total_seed_data'][0]) }}
@else
    <!-- Handle the case where there is no data -->
    0
@endif bags (20kg/bag), @if(!empty($total_seed_variety['total_seed_variety']) && isset($total_seed_variety['total_seed_variety'][0]))
    {{ $total_seed_variety['total_seed_variety'][0] }}
@else
    <!-- Handle the case where there is no data -->
    0
@endif
 seed varieties</span></h3>
                    <p>This report displays the overall total of distributed seeds for each seed variety.</p>
                </div>
            </div>

            <hr style="border-top: 2px solid #d6d3d3;">
            <div class="row">
                <div class="col-md-3">
                    <select name="region" id="region" name="reegion" class="form-control">
                        <option value="0">Please select a region</option>
                        @foreach ($regions['region'] as $row)
                            <option value="{{$row}}">{{$row}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="region" id="province" name="province" class="form-control">
                        <option value="0">Please select a province</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="region" id="municipality" name="municipality" class="form-control">
                        <option value="0">Please select a municipality</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-success form-control" id="filter_btn"><i class="fa fa-filter"></i> FILTER DATA</button>
                </div>
            </div>
            <hr style="border-top: 2px solid #d6d3d3;">

            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <table class="table table-striped table-bordered" id="overall_tbl">
                        <thead>
                            <th width="5%">#</th>
                            <th>Seed Variety</th>
                            <th>Total Voume (20kg/bag)</th>
                        </thead>
                        <tbody>
                        @foreach ($overall_seed_data['seed_variety'] as $index => $variety)
                            <tr>
                                <td>{{$index+1}}</td>
                                    <td>{{ $variety }}</td>
                                    <td>{{$overall_seed_data['total_seed_bags'][$index]}} bag(s)</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>


<!--     <div class="x_panel">
        <div class="x_title">
            <h2>
                Graphical Representation
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <img id="chart_gif" src="{{asset('public/images/load_chart.gif')}}" alt="" id="loading_gif" style="display: block;margin: auto;height: 300px;padding-top: 25px;">
            <div id="seed_chart" style="width:100%; height:500px;"></div>
        </div>
    </div> -->

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
        $("#overall_tbl").DataTable({
            sorting: []
        });
        Highcharts.setOptions({
            lang: {
                decimalPoint: '.',
                thousandsSep: ','
            },

            tooltip: {
                yDecimals: 2 // If you want to add 2 decimals
            }
        });

       //get default data for chart
       $.ajax({
            type: 'POST',
            url: "{{ route('report.variety.chart') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                load_seed_chart(data["seed_variety_list"], data["seed_bag_list"]);
                $("#chart_gif").css("display", "none");
            }
        });

        $("#download_btn").on("click", function(e){
            $("#download_btn").empty().html("Fetching data...");
            $("#download_btn").attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.variety_excel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    excel_type: 'regional'
                },
                success: function (response, textStatus, request) {
                    var a = document.createElement("a");
                    a.href = response.file; 
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    $("#download_btn").empty().html("<i class='fa fa-download'></i> DOWNLOAD EXCEL FILE");
                    $("#download_btn").removeAttr('disabled');
                }
            });
        });

        $("#region").on("change", function(e){
            var region = $("#region").val();
            $("#province").empty().append("<option value='0'>Loading provinces...</option>");
            $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('report.variety.provinces') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#province").empty().append("<option value='0'>Please select a province</option>");
                    $("#province").append(data);
                }
            });
        });

        $("#province").on("change", function(e){
            var region = $("#region").val();
            var province = $("#province").val();
            $("#municipality").empty().append("<option value='0'>Loading municipalities...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('report.variety.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region,
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#municipality").append(data);
                }
            });
        });

        $("#filter_btn").on("click", function(e){
            var region = $("#region").val();
            var province = $("#province").val();
            var municipality = $("#municipality").val();

            if(region == "0" && province == "0" && municipality == "0"){
                alert("please select fill-up all the required fields (region=required, province=optional, municipality=optional)");
            }else{
                $("#overall_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('report.variety.table') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}", 
                            region: region,
                            province: province,
                            municipality: municipality
                        }
                    },
                    "columns":[
                        {"data": "DT_Row_Index"},
                        {"data": "seed_variety"},
                        {"data": "volume", searchable: false},
                    ]
                });

                $("#chart_gif").css("display", "block");

                //refresh chart
                $.ajax({
                    type: 'POST',
                    url: "{{ route('report.variety.chart_filter') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        region: region,
                        province: province,
                        municipality: municipality
                    },
                    success: function(data){
                        load_seed_chart(data["seed_variety_list"], data["seed_bag_list"]);
                        $("#chart_gif").css("display", "none");
                    }
                });
            }
        });
    </script>
@endpush
