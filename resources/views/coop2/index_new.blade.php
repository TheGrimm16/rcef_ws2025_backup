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
    .custom-header-span{
        font-size: 15px;
        font-family: inherit;
    }
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="clearfix"></div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                @include('layouts.message')
                
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>Commited Deliveries per Seed Cooperative</h2>                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="coop_tbl">
                            <thead>
                                <th>Cooperative</th>
                                <th>Accreditation Number</th>
                                <th>Commitment</th>
                            </thead>
                        </table>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

            <div id="commitmentModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="coopName"></h4>
                            </div>
                            <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
                                <div class="row" id="modal_row_id">
                                    <div class="col-md-12" id="add_moa_fld">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="add_moa" id="add_moa" placeholder="Please enter the current MOA Number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12" id="add_commit_fld">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="add_commit_total" id="add_commit_total" value="TOTAL COMMITMENT: 0 bags" disabled readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="seed_variety_fld">
                                        <div class="form-group">
                                            <select name="seed_variety" id="seed_variety" style="width: 100%;" class="form-control">
                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="delivery_commitment_fld">
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="commitment_value" id="commitment_value" placeholder="Please enter a value (20kg/bag)" data-parsley-required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <button class="btn btn-warning btn-block" id="Add_BTN">Add Commitment</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12" style="display:none" id="row_id_commitment">
                                        <div class="form-group">
                                            <label for="total_commitment"><b>TOTAL COMMITMENT:</b> </label>
                                            <input type="text" class="form-control" name="total_commitment" id="total_commitment" placeholder="Please enter the total commitment of the seed cooperative">
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="coopID" name="coopID" value="">
                                
                                <hr style="border-top:1px solid black;display:none" id="hr_line">

                                <table class="table table-bordered table-striped" id="coop_add_tbl" style="display:none;width:100%;">
                                    <thead>
                                        <th>Variety</th>
                                        <th>Volume (20kg/bag)</th>
                                        <th>Date Added</th>
                                        <th>Action</th>
                                    </thead>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" id="deleteAllCommitment"><i class="fa fa-times-circle"></i> Cancel Transaction</button>
                                <button type="button" class="btn btn-success" id="computeTotalCommitment"><i class="fa fa-plus-circle"></i> Save Delivery Commitment</button>
                            </div>
                        </div>
                        
                </div>
            </div>

            <div id="coopDetails" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                   
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="coopDetailsName">
                                    Seed Cooperative Name
                                </h4>
                                <span class="custom-header-span" id="coopDetailsTotal">Total Commitment: </span><br>
                                <span class="custom-header-span" id="coopDetailsNumber">Accreditation Number: </span><br>
                                <span class="custom-header-span" id="coopDetailsMOA">MOA Number: </span>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered table-striped" id="coop_detail_tbl">
                                    <thead>
                                        <th>Variety</th>
                                        <th>Volume (20kg/bag)</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                        <th>Action</th>
                                    </thead>
                                </table>

                                <input type="hidden" id="coopDetailsID" name="coopDetailsID" value="">
                                <div class="row" style="display:block" id="add_more_btn_fld">
                                    <div class="col-md-4">
                                        <button class="btn btn-success btn-block" id="add_more_btn"><i class="fa fa-plus-circle"></i> ADD MORE</button>
                                    </div>
                                </div>

                                <hr style="border-top:1px solid black;display:none" id="hr_line_add">

                                <div class="row" style="display:none" id="add_more_variety_fld">
                                    <div class="col-md-6">
                                        <select name="add_more_variety" id="add_more_variety" style="width: 100%;" class="form-control">

                                        </select>
                                    </div>
                                    <div class="col-md-4" style="display:none" id="add_more_value_fld">
                                        <input type="number" name="add_more_value" id="add_more_value" class="form-control" placeholder="Please enter a value">
                                    </div>
                                    <div class="col-md-2" style="display:none" id="add_submit_btn_fld">
                                         <button class="btn btn-success btn-block" id="add_submit_btn">ADD</button>
                                    </div>
                                </div>
                            </div>
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

        $("#seed_variety").select2();

        $("#add_more_btn").on("click", function(e){
            $("#add_more_btn_fld").css("display", "none");
            $("#hr_line_add").css("display", "block");
            $("#add_more_variety_fld").css("display", "block");
            $("#add_more_value_fld").css("display", "block");
            $("#add_submit_btn_fld").css("display", "block");
        });

        $("#coop_tbl").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('coop.commitment.table') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "coopName"},
                {"data": "accreditation_no", 'searchable': false },
                {"data": "action", 'searchable': false }
            ]
        });

        $("#deleteAllCommitment").on("click", function(e){
           var coopID = $("#coopID").val();
           $("#deleteAllCommitment").empty().html("<div class='fa fa-refresh fa-spin'></div> Cancelling Transaction...");
           $("#deleteAllCommitment").attr("disabled", "");

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.commitment.cancel') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: coopID
                },
                success: function(data){
                    window.location = data;
                }
            });
        }); 

        $('#commitmentModal').on('show.bs.modal', function (e) {
			var coopID = $(e.relatedTarget).data('id');
			var coopName = $(e.relatedTarget).data('name');
            var add_moa = $(e.relatedTarget).data('acn');

			$("#coopID").val(coopID);
			$("#coopName").html(coopName);
            $("#add_moa").val("MOA Number: " + add_moa);
            $("#add_moa").attr("disabled", "");
            $("#add_commit_total").val("TOTAL COMMITMENT: ");
        

            //get seed varities
            $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: coopID
                },
                success: function(data){
                    $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                    $("#seed_variety").append(data);
                }
            });
		});

        function removeRecord(data) {      
            var id = jQuery(data).data('id');
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities.delete') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    commitmentID: id
                },
                success: function(coop_id){
                    $('#coop_add_tbl').DataTable().clear();
                    $("#coop_add_tbl").DataTable({
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "ajax": {
                            "url": "{{ route('coop.save.table') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                "coopID": coop_id
                            }
                        },
                        "columns":[
                            {"data": "commitment_variety"},
                            {"data": "variety_bags"},
                            {"data": "date_add"},
                            {"data": "action"}
                        ]
                    });

                    //get seed varities
                    $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.varities') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coopID: coop_id
                        },
                        success: function(data){
                            $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                            $("#seed_variety").append(data);
                        }
                    });
                }
            });
        }

        //remove data from record
        function removeRecordDetails(data) {      
            var id = jQuery(data).data('id');
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities.delete.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    commitmentID: id
                },
                success: function(coop_id){
                    if(coop_id == "zero_neg_rem"){
                        alert("Action not allowed.");

                        //AJAX TO GET COOPID
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.id') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                commitmentID: id
                            },
                            success: function(coop_id_data){
                               //get seed varities
                                $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ route('coop.varities') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        coopID: coop_id_data
                                    },
                                    success: function(data){
                                        $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                                        $("#seed_variety").append(data);
                                    }
                                });

                                //update total bags displayed
                                $("#coopDetailsTotal").empty().html("Total Commitment: Computing value please wait...");
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ route('coop.commitment.total') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        coopDetailsID: coop_id_data
                                    },
                                    success: function(data){
                                        $("#coopDetailsTotal").empty().html("Total Commitment: " + data + " bags");
                                    }
                                });
                            }
                        });

                    }else{
                        $('#coop_detail_tbl').DataTable().clear();
                        $("#coop_detail_tbl").DataTable({
                            "bDestroy": true,
                            "autoWidth": false,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.details.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopDetailsID": coop_id
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
                                {"data": "status_btn"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });
                    }
                    
                    $("#add_more_btn_fld").css("display", "block");
                    $("#hr_line_add").css("display", "none");
                    $("#add_more_variety_fld").css("display", "none");
                    $("#add_more_value_fld").css("display", "none");
                    $("#add_submit_btn_fld").css("display", "none");
                    $("#add_more_value_fld").val("");

                    $("#add_more_totalCommit_fld").css("display", "none");
                    $("#add_more_totalCommit").css("border", "1px solid black");
                    $("#add_more_totalCommit").val("");

                    //get seed varities
                    $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.varities') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coopID: coop_id
                        },
                        success: function(data){
                            $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                            $("#seed_variety").append(data);
                        }
                    });

                    //update total bags displayed
                    $("#coopDetailsTotal").empty().html("Total Commitment: Computing value please wait...");
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('coop.commitment.total') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coopDetailsID: coop_id
                        },
                        success: function(data){
                            $("#coopDetailsTotal").empty().html("Total Commitment: " + data + " bags");
                        }
                    });
                }
            });
        }

        $("#Add_BTN").on("click", function(e){
            var coopID = $("#coopID").val();
            var seed_variety = $("#seed_variety").val();
            var commitment_value = $("#commitment_value").val();

            $("#Add_BTN").attr("disabled", "");
            
            if(seed_variety == '0'){
                alert("Please specify a seed variety");
                $("#Add_BTN").removeAttr("disabled");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.commitment.save') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coopID: coopID,
                        seed_variety: seed_variety,
                        commitment_value: commitment_value
                    },
                    success: function(data){
                        $("#Add_BTN").removeAttr("disabled");
                        $("#commitment_value").val("");
                        $("#hr_line").css("display", "block");
                        $('#coop_add_tbl').css("display", "inline-table")
                        $('#coop_add_tbl').DataTable().clear();
                        $("#coop_add_tbl").DataTable({
                            "bDestroy": true,
                            "autoWidth": false,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.save.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopID": coopID
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });

                        //compute sub-total of commmitments
                        $("#add_commit_total").empty().val("Computing data please wait...");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.commitment.sub_total') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopID: coopID
                            },
                            success: function(data){
                                $("#add_commit_total").val("TOTAL COMMITMENT: " + data + " bags");
                            }
                        });

                        //get seed varities
                        $("#seed_variety").empty().append("<option value='0'>Updating please wait...</option>");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.varities') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopID: coopID
                            },
                            success: function(data){
                                $("#seed_variety").empty().append("<option value='0'>Please select a variety</option>");
                                $("#seed_variety").append(data);
                            }
                        });
                    }
                });
            }
        });

        $("#computeTotalCommitment").on("click", function(e){
            var coopID = $("#coopID").val();
            var total_commitment = $("#total_commitment").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.commitment.save.total') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: coopID,
                    total_commitment: total_commitment
                },
                success: function(data){
                    if(data == 'no_commit'){
                        alert("Please add a delivery commitment");
                    }else if(data == 'small_commit'){
                        alert("Please ensure that the total commitment of the cooperative is larger than the sub-total of the previously added seed varities.");
                    }else if(data == "zero_commit"){
                        $("#row_id_commitment").css("display", "block");
                        $("#total_commitment").css("border", "1px solid red");
                        alert("Please input the total commitment for the seed cooperative..");
                    }else{
                        alert('Successfully saved commitment data!');
                        window.location = data;
                    }
                }
            });
        });

        $('#coopDetails').on('show.bs.modal', function (e) {

            $("#add_more_btn_fld").css("display", "block");
            $("#hr_line_add").css("display", "none");
            $("#add_more_variety_fld").css("display", "none");
            $("#add_more_value_fld").css("display", "none");
            $("#add_submit_btn_fld").css("display", "none");

			var coopDetailsID = $(e.relatedTarget).data('id');
			var coopDetailsName = $(e.relatedTarget).data('name');
            var coopDetailsBags = $(e.relatedTarget).data('bags');

            var accreditation_no = $(e.relatedTarget).data('acn');
            var moa_number = $(e.relatedTarget).data('moa');

			$("#coopDetailsID").val(coopDetailsID);

            $("#coopDetailsName").empty().html(coopDetailsName);
            $("#coopDetailsTotal").empty().html("Total Commitment: " + coopDetailsBags + " bags");
            $("#coopDetailsNumber").empty().html("Accreditation Number: " + accreditation_no);
            $("#coopDetailsMOA").empty().html("MOA Number: " + moa_number);
			//$("#coopDetailsName").empty().html(coopDetailsName + " <br><span class='custom-header-span'>Total Commitment: " + coopDetailsBags + " bags (20kg/bag)</span><br><span class='custom-header-span'>Accreditation Number: " + accreditation_no + "</span><br><span class='custom-header-span'>MOA Number: " + moa_number + "</span>");

            $('#coop_detail_tbl').DataTable().clear();

            $("#coop_detail_tbl").DataTable({
                "bDestroy": true,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('coop.details.table') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "coopDetailsID": coopDetailsID
                    }
                },
                "columns":[
                    {"data": "commitment_variety"},
                    {"data": "variety_bags"},
                    {"data": "status_btn"},
                    {"data": "date_add"},
                    {"data": "action"}
                ]
            });

            //generate data for seed varities
            $("#add_more_variety").select2();
            $("#add_more_variety").empty().append("<option value='0'>Updating please wait...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.varities.details.add') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopDetailsID: coopDetailsID
                },
                success: function(data){
                    $("#add_more_variety").empty().append("<option value='0'>Please select a variety</option>");
                    $("#add_more_variety").append(data);
                }
            });
		});

        $("#add_submit_btn").on("click", function(e){
            var coopDetailsID = $("#coopDetailsID").val();
            var seed_variety = $("#add_more_variety").val();
            var seed_value = $("#add_more_value").val();

            if(seed_variety == "0"){
                alert("Please select a seed variety.");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.add_more.submit') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coopDetailsID: coopDetailsID,
                        seed_variety: seed_variety,
                        seed_value: seed_value,
                    },
                    success: function(data){
                        $('#coop_detail_tbl').DataTable().clear();
                        $("#coop_detail_tbl").DataTable({
                            "bDestroy": true,
                            "searchHighlight": true,
                            "processing": true,
                            "serverSide": true,
                            "orderMulti": true,
                            "order": [],
                            "ajax": {
                                "url": "{{ route('coop.details.table') }}",
                                "dataType": "json",
                                "type": "POST",
                                "data":{
                                    "_token": "{{ csrf_token() }}",
                                    "coopDetailsID": coopDetailsID
                                }
                            },
                            "columns":[
                                {"data": "commitment_variety"},
                                {"data": "variety_bags"},
                                {"data": "status_btn"},
                                {"data": "date_add"},
                                {"data": "action"}
                            ]
                        });

                        $("#add_more_btn_fld").css("display", "block");
                        $("#hr_line_add").css("display", "none");
                        $("#add_more_variety_fld").css("display", "none");
                        $("#add_more_value_fld").css("display", "none");
                        $("#add_submit_btn_fld").css("display", "none");
                        $("#add_more_value").val("");

                        //generate data for seed varities
                        $("#add_more_variety").select2();
                        $("#add_more_variety").empty().append("<option value='0'>Updating please wait...</option>");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.varities.details.add') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopDetailsID: coopDetailsID
                            },
                            success: function(data){
                                $("#add_more_variety").empty().append("<option value='0'>Please select a variety</option>");
                                $("#add_more_variety").append(data);
                            }
                        });

                        //update total bags displayed
                        $("#coopDetailsTotal").empty().html("Total Commitment: Computing value please wait...");
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('coop.commitment.total') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                coopDetailsID: coopDetailsID
                            },
                            success: function(data){
                                $("#coopDetailsTotal").empty().html("Total Commitment: " + data + " bags");
                            }
                        });
                    }
                });
            }

            
        });

    </script>
@endpush
