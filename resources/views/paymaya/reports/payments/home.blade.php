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
                    E-Binhi Payments
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-8">
                        <label for="" class="col-xs-1">FROM:</label>
                        <label id="from">
                        <input type="text" style="width: 80%; text-align: center;" value="{{date('m/d/Y')}}" class="form-control" name="date1" id="date1" placeholder="Date From">
                        </label> <br>
                        
                        <label for="" class="col-xs-1">TO:</label>
                        <label id="to">
                        <input type="text" style="width: 80%; text-align: center;" value="{{date('m/d/Y')}}" class="form-control" name="date2" id="date2" placeholder="Date To">
                        </label> 
                        
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success form-control" id="load_report_btn"><i class="fa fa-database"></i> LOAD PAYMENT DETAILS</button>
                    </div>
                    
                </div><hr>
                <div class="x_content" id="container_div" style="display: none">

                        <table class="table table-hover table-striped table-bordered" id="payment_tbl">
                            <thead>
                                <th></th>
                                <th>Name of Coop</th>
                                <th>No. of Bags</th>
                                <th>Amount</th>
                                <th>Account Number</th>
                                <th>Bank Name/Branch</th>
                                <th>Address</th>
                                <th>Form to be used</th>
                                <th>Delivery Date</th>
                                


                                <th>For DBP Form use</th>
                                {{-- <th>Date Printed</th> --}}
                            </thead>
                        </table>
                        <div id="dl1" style="display: block">
                            <a href="#" target="_blank" data-toggle='modal' data-target='#download_modal_dbp' class="btn btn-success btn-sm" ><i class="fa fa-download"></i> Download Payment Form</a>
                            {{-- <a href="" target="_blank" data-toggle='modal' class="btn btn-success btn-sm" ><i class="fa fa-download"></i> Download Payment Report</a> --}}
                            <button class="btn btn-success btn-sm" id="btn_report_dl"><i class="fa fa-download"></i> Download Payment Report</button>
                        </div>
                        @if(Auth::user()->username == "r.benedicto_2" ||  Auth::user()->username == "dc.gaspar" ||  Auth::user()->username == "jt.rivera" ||  Auth::user()->username == "reggie_dioses" ||  Auth::user()->username == "renaida_pascual" || Auth::user()->username == "processor_jbl" || Auth::user()->username == "ar.aromin")
                        @endif
                </div>
            </div>
        </div><br>

<!-- DOWNLOAD MODAL -->
<div id="download_modal_dbp" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Download Payment Form</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-4">Select Billing Date:</label>
                <label id="from">
                    <input type="text" style="width: 50%; text-align: center;" value="{{date('m/d/Y')}}" class="form-control" name="date3" id="date3" placeholder="Date From">
                </label> 
                <br><br><br>
                <button class="btn btn-success" name="btn_payment_dbp" id="btn_payment_dbp" style="display: none"> <i class="fa fa-file" ></i> DBP</button>
                <button class="btn btn-success" name="btn_payment_other" id="btn_payment_other" style="display: none"><i class="fa fa-file" ></i> Other Bank</button>
            </div>
        </div>
    </div>
</div>
<!-- DOWNLOAD MODAL END-->


    </div>

@endsection()

@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

        var currentDate = new Date();
        // Get the day of the week (0-6, where 0 is Sunday and 6 is Saturday)
        var currentDay = currentDate.getDay();
        //1 is monday
        if (currentDay == 1){
            $("#date1").daterangepicker(
           {
            singleDatePicker: true,
            minDate:moment().subtract(3, 'days'),
           }
        );

        }else{
                $("#date1").daterangepicker(
            {
                singleDatePicker: true,
                minDate:moment(),
            }
            );
        }

        
        $("#date2").daterangepicker({
            singleDatePicker: true,
            minDate:moment()});
        $("#date3").daterangepicker({
            singleDatePicker: true,
            minDate:moment()});
        $("#payment_tbl").DataTable();         

        $("#load_report_btn").on("click", function(e){
            load_data();
        });


        $('#btn_report_dl').on('click', function (e) {
                var province = $("#modal_province").text();
                var date1 = $("#date1").val();
                var date2 =  $("#date2").val();
                date1 = date1.replace("/", "-");
                date1 = date1.replace("/", "-");
                date2 = date2.replace("/", "-");
                date2 = date2.replace("/", "-");



            var coop = [];
            $('input[name=selected_coop]:checked').map(function() {
                coop.push($(this).val());
            });

            for (var i = 0; i < coop.length; i++) {
                coop[i] = coop[i].replace("/", "_");
            }

            if(coop == ''){
                alert('Please Select Coop to Print');
                return;
            }



               window.open("../../paymaya/report/dl/"+date1+"/"+date2+"/"+coop, "_blank");

         


         });

        function load_data(){
            HoldOn.open(holdon_options);
            $('#btn_payment_other').prop('disabled', false);
            $('#btn_payment_dbp').prop('disabled', false);

            $("#dl1").css("display", "none"); 
            var date1 = $("#date1").val();
            var date2 =  $("#date2").val();
            date1 = date1.replace("/", "-");
            date1 = date1.replace("/", "-");
            date2 = date2.replace("/", "-");
            date2 = date2.replace("/", "-");

            if(date1 > date2){
                alert('Invalid Date Range');
                $("#container_div").css("display", "none"); 
                $('#payment_tbl').DataTable().clear();
            }
            else{
                
            $("#container_div").css("display", "inline"); 
            $('#payment_tbl').DataTable().clear();
            $("#payment_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('paymaya.reports.payments.coop') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        date1: $("#date1").val(),
                        date2:  $("#date2").val()
                    }
                },
                "columns":[
                    {"data": "select"},
                    {"data": "coop"},
                    {"data": "bags"},
                    {"data": "amount"},
                    {"data": "account"},
                    {"data": "bank"},
                    {"data": "address_1"},
                    {"data": "form_type"},
                    {"data": "date"},
                    {"data": "print_dbp"},
                    
                    // {"data": "is_paid"},
                    // {"data": "date_paid"}
                    
                ]

            });

            $.ajax({
                type: 'POST',
                url: "{{ route('paymaya.reports.payments.coop_dl') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    date1: $("#date1").val(),
                    date2:  $("#date2").val()
                },
                success: function(data){
                    $("#btn_payment_dbp").css("display", "none"); 
                    $("#btn_payment_other").css("display", "none"); 
                    // $("#container_div").css("display", "none"); 

                 if(data.db > 0){
                    console.log("DB!");
                    $("#btn_payment_dbp").css("display", "inline"); 
                    $("#dl1").css("display", "block"); 
                }if(data.other > 0){
                     console.log("OTH!");
                    $("#btn_payment_other").css("display", "inline"); 
                    $("#dl1").css("display", "block"); 
                 }

                }
                
            });

            }
            HoldOn.close();
        }
//wit
        $("#btn_payment_other").on("click", function(e){
            var coop = [];
            $('input[name=selected_coop]:checked').map(function() {
                coop.push($(this).val());
            });

            for (var i = 0; i < coop.length; i++) {
                coop[i] = coop[i].replace("/", "_");
            }

            if(coop == ''){
                alert('Please Select Coop to Print');
                $('#download_modal_dbp').modal('toggle');
            }else{
                var date1 = $("#date1").val();
                var date2 =  $("#date2").val();
                var date3 =  $("#date3").val();
        
                date1 = date1.replace("/", "-");
                date1 = date1.replace("/", "-");
                date2 = date2.replace("/", "-");
                date2 = date2.replace("/", "-");
                date3 = date3.replace("/", "-");
                date3 = date3.replace("/", "-");
                // $("#btn_payment_other").css("display", "disabled"); 
                $('#btn_payment_other').prop('disabled', true);
                $('#download_modal_dbp').modal('toggle');
                // $('#payment_tbl').DataTable().load();
                        
                window.open('../../paymaya/report/payments/'+date1+'/'+date2+'/'+date3+'/'+coop).focus();
                setTimeout(function(){   load_data(); }, 2000);

            }     
    
        });

//irwin
        $("#btn_payment_dbp").on("click", function(){
            
            var coop = [];
            $('input[name=selected_coop]:checked').map(function() {
                coop.push($(this).val());
            });

            for (var i = 0; i < coop.length; i++) {
                coop[i] = coop[i].replace("/", "_");
            }

            if(coop == ''){
                alert('Please Select Coop to Print');
                $('#download_modal_dbp').modal('toggle');
            }else{
                
                var date1 = $("#date1").val();
                var date2 =  $("#date2").val();
                var date3 =  $("#date3").val();
    
                date1 = date1.replace("/", "-");
                date1 = date1.replace("/", "-");
                date2 = date2.replace("/", "-");
                date2 = date2.replace("/", "-");
                date3 = date3.replace("/", "-");
                date3 = date3.replace("/", "-");

        //    $('#btn_payment_dbp').prop('disabled', true);
           $('#download_modal_dbp').modal('toggle');
            window.open('../../paymaya/report/payments/dbp/'+date1+'/'+date2+'/'+date3+'/'+coop, "tabOne");
            setTimeout(function(){   
                window.open('../../paymaya/report/payments/dbp2/'+date1+'/'+date2+'/'+date3+'/'+coop, "tabTwo");
                load_data(); 
            }, 2000);
            window.open('../../paymaya/report/payments/dbp2/'+date1+'/'+date2+'/'+date3+'/'+coop, "tabTwo");
            }
        });



        $("#btn_approved").on("click", function(e){         
            var current_moa = $("#moa_hidden").val();
            var checkboxValues = [];
            var batch_length = 0;
            // $('input[name=selected_coop]:checked').map(function() {
            //     checkboxValues.push($(this).val());
            // });
            // alert(checkboxValues);

        });

        $('body').on('click', '#btn_payment_dbp_individual', function(e){
            var coop_name = $(this).attr("data-id");
            var date1 = $("#date1").val();
            var date2 =  $("#date2").val();
            var date3 =  $("#date3").val();

            coop_name = coop_name.replaceAll(" ", "_");
            date1 = date1.replace("/", "-");
            date1 = date1.replace("/", "-");
            date2 = date2.replace("/", "-");
            date2 = date2.replace("/", "-");
            date3 = date3.replace("/", "-");
            date3 = date3.replace("/", "-");

            window.open('../../paymaya/report/payments/dbp_individual/'+date1+'/'+date2+'/'+date3+'/'+coop_name).focus();
            setTimeout(function(){   load_data(); }, 2000);
          
        });

    </script>
@endpush
