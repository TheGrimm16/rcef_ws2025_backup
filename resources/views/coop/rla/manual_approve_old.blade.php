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
    input[type=number]::-webkit-inner-spin-button {
        opacity: 1
    }
    .tab_link_active {
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
        text-align: center;
    } 
    .tab_link_active .x_content div span{
        border-bottom: 2px solid white;
    }
    span {
        cursor: pointer;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    <div class="col-md-12 col-sm-12 col-xs-12">

        @include('layouts.message')

        <div class="row">
            <div class="col-md-3">
                <div class="x_panel tab_link_active" id="pending_tab">
                    <div class="x_content" style="padding: 0;float: left;clear: both;margin-top: 0;">
                        <div style="padding:10px">
                            <span style="font-size: 15px;font-weight: 600;">PENDING REQUESTS</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="x_panel" style="text-align: center;" id="approved_tab">
                    <div class="x_content" style="padding: 0;float: left;clear: both;margin-top: 0;">
                        <div style="padding:10px">
                            <span style="font-size: 15px;font-weight: 600;">APPROVED REQUESTS</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="x_panel" style="text-align: center;" id="rejected_tab">
                    <div class="x_content" style="padding: 0;float: left;clear: both;margin-top: 0;">
                        <div style="padding:10px">
                            <span style="font-size: 15px;font-weight: 600;">REJECTED REQUESTS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    RLA requests
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-hover table-striped table-bordered" id="rla_tbl">
                    <thead>
                        <th style="width:250px">Seed Cooperative</th>
                        <th style="width:150px">Seed Grower</th>
                        <th style="width:100px">Variety</th>
                        <th>Seed Tag</th>
                        <th>Bags Passed</th>
                        <th style="width:80px">Action</th>
                    </thead>
                </table>
            </div>
        </div>      

    </div>


    <!-- APPROVE MODAL -->
    <div id="approve_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        APPROVE RLA UPLOAD REQUEST
                    </h4>
                </div>
                <div class="modal-body">
                    <p><strong>You are about to <u>approve</u> this request, please double check the details as displayed on the table below before you continue.</strong></p>
                    <input type="hidden" value="" id="request_id" name="request_id">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <th style="width:20%;">Description</th>
                            <th style="width:80%;">Correspongin Value</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Seed Cooperative</td>
                                <td id="approve_modal_seedCoop">--</td>
                            </tr>
                            <tr>
                                <td>MOA Number</td>
                                <td id="approve_modal_moaNumber">--</td>
                            </tr>
                            <tr>
                                <td>Seed Grower</td>
                                <td id="approve_modal_seedGrower">--</td>
                            </tr>
                            <tr>
                                <td>Seed Variety</td>
                                <td id="approve_modal_seedVariety">--</td>
                            </tr>
                            <tr>
                                <td>Seed Tag</td>
                                <td id="approve_modal_seedTag">--</td>
                            </tr>
                            <tr>
                                <td>Bags Passed</td>
                                <td id="approve_modal_bags">--</td>
                            </tr>
                            <tr>
                                <td>Certification Date</td>
                                <td id="approve_modal_certificationDate">--</td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
                <div class="modal-footer">
                    <button id="approve_btn" type="button" class="btn btn-success"><i class="fa fa-thumbs-up"></i> APPROVE RLA UPLOAD</button>
                </div>
            </div>
        </div>
    </div>
    <!-- APPROVE MODAL -->


    <!-- APPROVE MODAL -->
    <div id="reject_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        REJECT RLA UPLOAD REQUEST
                    </h4>
                </div>
                <div class="modal-body">
                    <p><strong>You are about to <u>reject</u> this request, please double check the details as displayed on the table below before you continue.</strong></p>
                    <input type="text" value="" id="request_id_reject" name="request_id_reject">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <th style="width:20%;">Description</th>
                            <th style="width:80%;">Correspongin Value</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Seed Cooperative</td>
                                <td id="reject_modal_seedCoop">--</td>
                            </tr>
                            <tr>
                                <td>MOA Number</td>
                                <td id="reject_modal_moaNumber">--</td>
                            </tr>
                            <tr>
                                <td>Seed Grower</td>
                                <td id="reject_modal_seedGrower">--</td>
                            </tr>
                            <tr>
                                <td>Seed Variety</td>
                                <td id="reject_modal_seedVariety">--</td>
                            </tr>
                            <tr>
                                <td>Seed Tag</td>
                                <td id="reject_modal_seedTag">--</td>
                            </tr>
                            <tr>
                                <td>Bags Passed</td>
                                <td id="reject_modal_bags">--</td>
                            </tr>
                            <tr>
                                <td>Certification Date</td>
                                <td id="reject_modal_certificationDate">--</td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
                <div class="modal-footer">
                    <button id="reject_btn" type="button" class="btn btn-danger"><i class="fa fa-thumbs-down"></i> REJECT RLA UPLOAD</button>
                </div>
            </div>
        </div>
    </div>
    <!-- APPROVE MODAL -->

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>


        $("#pending_tab").on("click", function(e){
            $("#pending_tab").addClass('tab_link_active');
            $("#approved_tab").removeClass('tab_link_active');
            $("#rejected_tab").removeClass('tab_link_active');
            load_datatable(1);
        });


        $("#approved_tab").on("click", function(e){
            $("#approved_tab").addClass('tab_link_active');
            $("#pending_tab").removeClass('tab_link_active');
            $("#rejected_tab").removeClass('tab_link_active');
            load_datatable(2);
        });

        $("#rejected_tab").on("click", function(e){
            $("#rejected_tab").addClass('tab_link_active');
            $("#pending_tab").removeClass('tab_link_active');
            $("#approved_tab").removeClass('tab_link_active');
            load_datatable(3);
        });

        function load_datatable(status){
            $('#rla_tbl').DataTable().clear();
            $("#rla_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('coop.rla.approve_table') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        status: status
                    }
                },
                "columns":[
                    {"data": "coop_name", searchable: false},
                    {"data": "sg_name"},
                    {"data": "seed_variety"},
                    {"data": "seed_tag"},
                    {"data": "no_of_bags"},
                    {"data": "action", searchable: false}
                ]
            });
        }

        load_datatable(1);
        $('#approve_modal').on('show.bs.modal', function (e) {
            var request_id = $(e.relatedTarget).data('id');
            $("#request_id").val(request_id);

            //load rla request details
            $("#approve_modal_seedCoop").empty().html('fetching data...');
            $("#approve_modal_moaNumber").empty().html('fetching data...');
            $("#approve_modal_seedGrower").empty().html('fetching data...');
            $("#approve_modal_seedVariety").empty().html('fetching data...');
            $("#approve_modal_seedTag").empty().html('fetching data...');
            $("#approve_modal_bags").empty().html('fetching data...');
            $("#approve_modal_certificationDate").empty().html('fetching data...');
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.rla.approve_requsetDetails') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    request_id: request_id,
                },
                success: function (data) {
                    $("#approve_modal_seedCoop").empty().html(data["seedCoop"]);
                    $("#approve_modal_moaNumber").empty().html(data["moaNumber"]);
                    $("#approve_modal_seedGrower").empty().html(data["seedGrower"]);
                    $("#approve_modal_seedVariety").empty().html(data["seedVariety"]);
                    $("#approve_modal_seedTag").empty().html(data["seedTag"]);
                    $("#approve_modal_bags").empty().html(data["bags"]);
                    $("#approve_modal_certificationDate").empty().html(data["certificationDate"]);
                }
            });
        });


        $('#reject_modal').on('show.bs.modal', function (e) {
            var request_id = $(e.relatedTarget).data('id');
            $("#request_id_reject").val(request_id);

            //load rla request details
            $("#reject_modal_seedCoop").empty().html('fetching data...');
            $("#reject_modal_moaNumber").empty().html('fetching data...');
            $("#reject_modal_seedGrower").empty().html('fetching data...');
            $("#reject_modal_seedVariety").empty().html('fetching data...');
            $("#reject_modal_seedTag").empty().html('fetching data...');
            $("#reject_modal_bags").empty().html('fetching data...');
            $("#reject_modal_certificationDate").empty().html('fetching data...');
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.rla.approve_requsetDetails') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    request_id: request_id,
                },
                success: function (data) {
                    $("#reject_modal_seedCoop").empty().html(data["seedCoop"]);
                    $("#reject_modal_moaNumber").empty().html(data["moaNumber"]);
                    $("#reject_modal_seedGrower").empty().html(data["seedGrower"]);
                    $("#reject_modal_seedVariety").empty().html(data["seedVariety"]);
                    $("#reject_modal_seedTag").empty().html(data["seedTag"]);
                    $("#reject_modal_bags").empty().html(data["bags"]);
                    $("#reject_modal_certificationDate").empty().html(data["certificationDate"]);
                }
            });
        });


        $("#approve_btn").on("click", function(e){
            var request_id = $("#request_id").val();

            $("#approve_btn").empty().html("loading...");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.rla.approve_confirm') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    request_id: request_id,
                },
                success: function (data) {
                    $("#approve_btn").empty().html('<i class="fa fa-thumbs-up"></i> APPROVE RLA UPLOAD');
                    $("#approve_modal").modal('hide');

                    load_datatable(1);
                }, 
                error: function(data){
                    alert("oops something went wrong while processing your transaction, please try again.");
                    $("#approve_btn").empty().html('<i class="fa fa-thumbs-up"></i> APPROVE RLA UPLOAD');
                    $("#approve_modal").modal('hide');
                }
            });
        });

        $("#reject_btn").on("click", function(e){
            var request_id = $("#request_id_reject").val();

            $("#reject_btn").empty().html("loading...");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.rla.approve_reject') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    request_id: request_id,
                },
                success: function (data) {
                    $("#reject_btn").empty().html('<i class="fa fa-thumbs-down"></i> REJECT RLA UPLOAD');
                    $("#reject_modal").modal('hide');
                    load_datatable(1);
                }, 
                error: function(data){
                    alert("oops something went wrong while processing your transaction, please try again.");
                    $("#reject_btn").empty().html('<i class="fa fa-thumbs-down"></i> REJECT RLA UPLOAD');
                    $("#reject_modal").modal('hide');
                }
            });
        });
    </script>
@endpush
