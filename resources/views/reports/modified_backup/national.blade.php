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
                National Report
            </h2>
            <!--<button class="btn btn-success btn-sm" style="float:right;" id="excel_btn">
                Export to Excel
            </button>-->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <br/>
                <table class="table table-hover table-striped table-bordered" id="national_tbl">
                    <thead>
                        <th>Covered municipalities</th>
                        <th>Total beneficiaries</th>
                        <th>Estimated area planted (ha)</th>
                        <th>Registered area (ha)</th>
                        <th>Bags distributed (20kg/bag)</th>
                        <th>Total male</th>
                        <th>Total female</th>
                    </thead>
                    <tbody>
                        @foreach($national_data as $row)
                            <tr>
                                <td>{{$row->municipalities}}</td>
                                <td>{{$row->total_farmers == '' ? 0 : number_format($row->total_farmers)}}</td>
                                <td>{{$row->total_dist_area == '' ? 0 : number_format($row->total_dist_area, '2', '.', ',')}}</td>
                                <td>{{$row->total_actual_area == '' ? 0 : number_format($row->total_actual_area, '2', '.', ',')}}</td>
                                <td>{{$row->total_bags == '' ? 0 : number_format($row->total_bags)}}</td>
                                <td>{{$row->total_male == '' ? 0 : number_format($row->total_male)}}</td>
                                <td>{{$row->total_female == '' ? 0 : number_format($row->total_female)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#national_tbl").DataTable({
            "order": []
        });

        $("#excel_btn").on("click", function(e){
            $("#excel_btn").empty().html('<i class="fa fa-cog fa-spin"></i> Exporting data to Excel...');
            $("#excel_btn").attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.excel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    excel_type: 'national'
                },
                success: function (response, textStatus, request) {
                    var a = document.createElement("a");
                    a.href = response.file; 
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    $("#excel_btn").removeAttr('disabled');
                    $("#excel_btn").empty().html('Export to Excel');
                }
            });
        });
    </script>
@endpush
