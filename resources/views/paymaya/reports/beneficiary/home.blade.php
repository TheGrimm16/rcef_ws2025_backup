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

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Provincial Report
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="province_tbl">
                    <thead>
                        <th>Province</th>
                        <th> Target Bags</th>
                        <th> Target Beneficiaries</th>
                        <th> Target Area</th>
                        <th> Accepted Bags</th>
                        <th>Bags Distributed</th>
                        <th>Beneficiaries (Claimants)</th>
                        <th>Equivalent Area</th>
                        
                        <th>Action</th>
                    </thead>
                    <tbody>
                        @foreach ($province_arr as $row)
                            <tr>
                                <td>{{$row["province"]}}</td>
                                <td>{{$row["total_bags"]}}</td>
                                <td>{{$row["total_beneficiaries"]}}</td>
                                <td>{{$row["total_area"]}}</td>
                                <td>{{$row["accepted_bags"]}}</td>
                                <td>{{$row["claim_total_bags"]}}</td>
                                <td>{{$row["claim_total_beneficiaries"]}}</td>
                                <td>{{$row["claim_total_area"]}}</td>
                                <td>
                                    <a href="#" target="_blank" data-province="{{$row['province']}}" data-toggle='modal' data-target='#download_modal' class="btn btn-success btn-sm"><i class="fa fa-download"></i> Download</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div><br>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Municipal Report
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <select name="province" id="province" class="form-control">
                            <option value="0">Please select a Province</option>
                            @foreach ($province_arr as $row)
                                <option value="{{$row["province"]}}">{{$row["province"]}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-success form-control" id="load_report_btn"><i class="fa fa-database"></i> LOAD PROVINCIAL REPORT</button>
                    </div>
                </div><hr>

                <table class="table table-hover table-striped table-bordered" id="municipal_tbl">
                    <thead>
                        <th>Municipality</th>
                        <th>Target Bags</th>
                        <th>Target Benediciaries</th>
                        <th>Target Area</th>
                        <th> Accepted Bags</th>
                        
                        <th>Bags Distributed</th>
                        <th>Beneficiaries (Claimants)</th>
                        <th>Equivalent Area</th>
                        
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div><br>

    </div>


<!-- DOWNLOAD MODAL -->
<div id="download_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Download Excel</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">PROVINCE:</label>
                <label id="modal_province"></label> <br>
                      
                <label for="" class="col-xs-3">FROM:</label>
                <label id="from">
        <input type="text" style="width: 50%; text-align: center;" value="{{date('m/01/Y')}}" class="form-control" name="date1" id="date1" placeholder="Date From">
                </label> <br>
                
                  <label for="" class="col-xs-3">TO:</label>
                <label id="to">
        <input type="text" style="width: 50%; text-align: center;" value="{{date('m/d/Y')}}" class="form-control" name="date2" id="date2" placeholder="Date To">
                </label> 

                     <button class="btn btn-success" style="float:right;" id="btn_download" >Download File</button>
            </div>
            </div>
        </div>



    </div>
</div>
<!-- DOWNLOAD NEW -->








@endsection()

@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
       

         $('#download_modal').on('show.bs.modal', function (e) {
                var province = $(e.relatedTarget).data('province');
                $("#modal_province").empty().text(province);
         });


           $('#btn_download').on('click', function (e) {
                var province = $("#modal_province").text();
                var date1 = $("#date1").val();
                var date2 =  $("#date2").val();
                date1 = date1.replace("/", "-");
                date1 = date1.replace("/", "-");
                date2 = date2.replace("/", "-");
                date2 = date2.replace("/", "-");




               window.open("provincial/"+province+"/"+date1+"/"+date2, "_blank");


         });


        $("#date1").datepicker();
        $("#date2").datepicker();

        $("#province_tbl").DataTable();
        $("#municipal_tbl").DataTable();         

        $("#load_report_btn").on("click", function(e){
            $('#municipal_tbl').DataTable().clear();
            $("#municipal_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('paymaya.beneficiary_report.province') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: $("#province").val()
                    }
                },
                "columns":[
                    {"data": "municipality"},
                    {"data": "total_bags"},
                    {"data": "total_beneficiaries"},
                    {"data": "total_area"},
                     {"data": "accepted_bags"},
                    {"data": "claim_total_bags"},
                    {"data": "claim_total_beneficiaries"},
                    {"data": "claim_total_area"},
                    {"data": "action"}
                ]
            });
        });
    </script>
@endpush
