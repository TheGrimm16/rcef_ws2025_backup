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

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time.
            </div>
        </div>

        <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    1). Select a Region
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <select name="region" id="region" class="form-control">
                            <option value="0">Please select a region</option>
                            @foreach ($region_list as $row)
                                <option value="{{$row->region}}">{{$row->region}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-success form-control" id="load_report_btn"><i class="fa fa-database"></i> LOAD PROVINCIAL REPORT</button>
                    </div>
                </div>
            </div>
        </div><br>
        <!-- FILTER PANEL -->

        <!-- distribution details -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    2). Provincial Report
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="province_tbl">
                    <thead>
                        <th>Province</th>
                        <th>Total Beneficiaries</th>
                        <th>Registered Area</th>
                        <th>Bags Distributed (20kg/bag)</th>
                        <th>Male</th>
                        <th>Female</th>
						<th>Not Syned data (Area & Sex)</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div><br>
        <!-- /distribution details -->
    </div>


    <!-- SEED COOPERATIVE MODAL -->
    <div id="export_option_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="province_export_title">
                        {PROVINCE_NAME} EXPORT OPTIONS
                    </h4>
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <p>
                        The system detected that the selected <strong>province</strong> has more than <strong><u>30,000 seed beneficiaries</u></strong>, to avoid long processing time the system automatically
                        splits the seed beneficiary list into smaller files to ease load time and download.
                    </p><br>
                    <div id="province_export_body">
                        ....
                    </div>             
                </div>
            </div>
        </div>
    </div>
    <!-- SEED COOPERATIVE MODAL -->

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#province_tbl").DataTable(); 
        $("#load_report_btn").on("click", function(e){
            var region = $("#region").val();

            $('#province_tbl').DataTable().clear();
            $("#province_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('generate.province.report') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        region: region
                    }
                },
                "columns":[
                    {"data": "province"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_registered_area"},
                    {"data": "total_bags_distributed"},
                    {"data": "total_male_count"},
                    {"data": "total_female_count"},
					{"data": "total_not_synced_count", 'searchable': false},
                    {"data": "action"}
                ]
            });
        });
        

        $("#excel_btn").on("click", function(e){
            $("#excel_btn").empty().html('<i class="fa fa-cog fa-spin"></i> Exporting data to Excel...');
            $("#excel_btn").attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.excel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    excel_type: 'provincial'
                },
                success: function (response, textStatus, request) {
                    var a = document.createElement("a");
                    a.href = response.file; 
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    $("#excel_btn").removeAttr('disabled');
                    $("#excel_btn").empty().html('Export to Excel (Statistics)');
                }
            });
        });

		function number_format(number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        $('#export_option_modal').on('show.bs.modal', function (e) {
            var province = $(e.relatedTarget).data('province');
            var region = $("#region").val();

            $("#province_export_title").empty().html(province + " EXPORT OPTIONS: ");
			
			 $("#province_export_body").empty().append("loading data...");

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.excel_check_volume') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region,
                    province: province
                },
                success: function(data){
                    $("#province_export_body").empty();
                    var button_str =  '';

                    var start_count = 1;
                    var end_count = 20000;

                    for (index = 1; index <= data; ++index) {
                        var url = "{{route('rcef.report.excel.province', ":province")}}";
                        url = url.replace(':province', province+"___"+start_count+"_"+end_count);
                        button_str = "<a class='btn btn-success btn-sm' href='"+url+"' target='_blank'><i class='fa fa-calendar'></i> "+number_format(start_count)+" - "+number_format(end_count)+"</a>";
                        
                        $("#province_export_body").append(button_str);

                        start_count = start_count + 20000;
                        end_count = end_count + 20000;
                    }
                },
                error: function(data){
                    $("#province_export_body").empty().append("system error, please try again.");
                }
            });
        });
    </script>
@endpush
