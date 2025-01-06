@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
      .collapse.in {
            display: inline-block;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel" aria-expanded="false">
            <div class="x_title">
                <h2>
                    FILTER BY SEED SOURCE
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <select name="seed_type_select" id="seed_type_select" class="form-control">
                    <option value="ALL">VIEW ALL TRANSACTIONS</option>
                    <option value="SEED_COOP">SEED COOPERATIVE / ASSOCIATION</option>
                    <option value="PHILRICE_WAREHOUSE">PHILRICE DESIGNATED WAREHOUSE</option>
                    <option value="LGU_STOCKS">STOCKS IN LGU</option>
                    <option value="TRANSFERRED_SEEDS">TRANSFERRED SEEDS</option>
                </select>
            </div>
        </div>

        
        <div class="x_panel collapse" style="min-height:58px;height: 22.2222px;" id="collapseExample" aria-expanded="false">
            <div class="x_title">
                <h2>
                    PRESS THE ICON TO SHOW FILTER OPTIONS
                </h2>
                <button class="btn btn-info pull-right" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="border-radius:20px;">
                    <i class="fa fa-angle-double-right" id="toggle_btn"></i> TOGGLE FILTERS
                </button>                
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Date Range:</label>
                        <div class="col-md-10 col-sm-10 col-xs-10" required>
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="date_category" id="date_category" class="form-control">
                                        <option value="date_recorded">Date Recorded</option>
                                        <option value="to_delivery_date">Date of delivery</option>
                                        <option value="to_transfer_date">Date of Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-8" style="padding: 0">
                                    <input type="text" name="date_range" id="date_range" class="form-control" />
                                </div>

                                <div class="col-md-1">
                                    <button class="btn btn-success" id="date_range_btn"><i class="fa fa-search-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Title:</label>
                        <div class="col-md-10 col-sm-10 col-xs-10" required>
                            <div class="row">
                                <div class="col-md-11">
                                    <input type="text" name="transaction_title" id="transaction_title" class="form-control" style="width: 101%" />
                                </div>

                                <div class="col-md-1">
                                    <button class="btn btn-success" id="title_btn"><i class="fa fa-search-plus"></i></button>
                                </div> 
                            </div>
                        </div>
                    </div>
                    

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
                        <div class="col-md-10 col-sm-10 col-xs-10">
                            <button class="btn btn-success" id="search_transaction_btn"><i class="fa fa-search-plus"></i> SEARCH TRANSACTION</button>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#advanced_filter_modal"><i class="fa fa-list-ol"></i> CLICK TO SHOW MORE FILTERS</button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Seed Schedule 
                </h2>
                <a href="{{route('rcep.google_sheet.export_schedule')}}" target="_blank" class="btn btn-warning pull-right" style="border-radius:20px;"><i class="fa fa-table"></i> EXPORT TRANSACTIONS</a>
                <a href="{{route('rcep.google_sheet.schedule_form')}}" class="btn btn-success pull-right" style="border-radius:20px;"><i class="fa fa-plus-circle"></i> ADD SCHEDULE</a>
                <button type="button" role="button" class="btn btn-default pull-right" id="reset_btn" style="border-radius:20px;"><i class="fa fa-undo"></i> RESET TABLE</button>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-striped table-bordered" id="schedule_tbl">
                    <thead>
                        <th width="100px;">Overview</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th style="width:100px;">Action</th>
                    </thead>
                </table>
            </div>
        </div><br>        
    </div>


    <div id="advanced_filter_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        FILTER OPTIONS
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    SOURCE
                                </h3>
                            </div>
                            <div class="panel-body" style="background-color: white;">
                                <div class="form-group">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Type:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" required>
                                        <select name="seed_type" id="seed_type" class="form-control" required>
                                            <option value="INVENTORY_DS">Inventory (DS2020)</option>
                                            <option value="INVENTORY_WS">Inventory (WS2020)</option>
                                            <option value="NEW" selected>New Seeds (DS2021)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Source</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" required>
                                        <select name="source" id="source" class="form-control" required>
                                            <option value="SEED_COOP">Seed Cooperative / Association</option>
                                            <option value="TRANSFERRED_SEEDS">Transferred Seeds</option>
                                        </select>
                                    </div>
                                </div>
            
                                <div class="form-group">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Status:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" required>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="APPROVED">Approved</option>
                                            <option value="RESCHEDULED">Re-Scheduled</option>
                                            <option value="CANCELLED">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    FROM
                                </h3>
                            </div>
                            <div class="panel-body" style="background-color: white;">
                                <div class="form-group" id="from_province_div" style="display: none">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Province:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <select name="from_province" id="from_province" class="form-control" >
                                            <option value="0">Please select a province</option>
                                            @foreach ($provinces as $from_province)
                                                <option value="{{$from_province->province}}">{{$from_province->province}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                
                                <div class="form-group" id="from_municipality_div" style="display: none">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Municipality:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <select name=" " id="from_municipality" class="form-control" >
                                            <option value="0">Please select a municipality</option>
                                        </select>
                                    </div>
                                </div>
                
                                <div class="form-group" id="from_dop_div" style="display: none">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Dropoff-Point:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <input type="text" name="from_dop_name" id="from_dop_name" class="form-control">
                                    </div>
                                </div>
        
                                <div class="form-group" id="from_seedCoop_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Coop</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <select name="from_seed_coop" id="from_seed_coop" class="form-control" style="width:100%">
                                            <option value="0">Please select a seed cooperative</option>
                                            @foreach ($cooperatives as $row)
                                                <option value="{{$row->accreditation_no}}">{{$row->coopName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group" id="from_assignedPC_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Assigned PC</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <input type="text" class="form-control" id="from_assigned_pc" name="from_assigned_pc">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default" id="to_panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    TO
                                </h3>
                            </div>
                            <div class="panel-body" style="background-color: white;">
                                <div class="form-group" id="to_province_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Province:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <select name="to_province" id="to_province" class="form-control" >
                                            <option value="0">Please select a province</option>
                                            @foreach ($provinces as $to_province)
                                                <option value="{{$to_province->province}}">{{$to_province->province}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                
                                <div class="form-group" id="to_municipality_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Municipality:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <select name="to_municipality" id="to_municipality" class="form-control" >
                                            <option value="0">Please select a municipality</option>
                                        </select>
                                    </div>
                                </div>
                
                                <div class="form-group" id="to_dop_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Dropoff-Point:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <input type="text" name="to_dop_name" id="to_dop_name" class="form-control">
                                    </div>
                                </div>
                
                                <div class="form-group" id="to_deliveryDate_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Date of Delivery:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <input type="date" name="to_delivery_date" id="to_delivery_date" class="form-control">
                                    </div>
                                </div>
                
                                <div class="form-group" id="to_transferDate_div" style="display: none">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Date of Transfer:</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <input type="date" name="to_transfer_date" id="to_transfer_date" class="form-control">
                                    </div>
                                </div>
                
                                <div class="form-group" id="to_assignedPC_div">
                                    <label class="control-label col-md-2 col-sm-2 col-xs-2">Assigned PC</label>
                                    <div class="col-md-10 col-sm-10 col-xs-10" >
                                        <input type="text" class="form-control" name="to_assigned_pc" id="to_assigned_pc">
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" role="button" class="btn btn-primary" id="advanced_filter_btn"><i class="fa fa-list-ol"></i> PROCEED TO FILTER TABLE</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div id="show_status_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="status_modal_title">
                        [TRANSACTION CODE]
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <input type="hidden" name="transaction_code" id="transaction_code" value="">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Delivery Status:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <select name="delivery_status" id="delivery_status" class="form-control">
                                    <option value="0">Please select a delivery status</option>
                                    <option value="1">On Process</option>
                                    <option value="2">Paid</option>
                                    <option value="3">Unpaid</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Document Status:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <select name="document_status" id="document_status" class="form-control"> 
                                    <option value="0">Not yet submitted</option>
                                    <option value="1">Submitted</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="status_remarks_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Remarks:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <textarea name="document_status_remarks" id="document_status_remarks" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-success" id="save_status_btn"><i class="fa fa-edit"></i> Save Changes</button>
                </div>
                </form>
            </div>
        </div>
    </div>


    <div id="show_more_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 5%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="show_more_title">
                        [TRANSACTION CODE] - SHOW MORE DETAILS
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="hidden" value="" id="show_more_id">
                                <table class="table table-bordered table-striped">
                                    <tr><td colspan="2"><center><strong>SCHEDULE</strong></center></td></tr>
                                    <tr>
                                        <td style="width:150px;">Transaction Title:</td>
                                        <td id="schedule_transaction_title">--</td>
                                    </tr>
                                    <tr>
                                        <td>Transaction Code:</td>
                                        <td id="schedule_transaction_code">--</td>
                                    </tr>
                                    <tr>
                                        <td>Seed Type:</td>
                                        <td id="schedule_seed_type">--</td>
                                    </tr>
                                    <tr>
                                        <td>Source:</td>
                                        <td id="schedule_source">--</td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td id="schedule_status">--</td>
                                    </tr>
                                    <tr>
                                        <td>Seed Cooperatives:</td>
                                        <td id="schedule_from_coop">--</td>
                                    </tr>
                                    <tr>
                                        <td>Province (FROM):</td>
                                        <td id="schedule_from_province">--</td>
                                    </tr>
                                    <tr>
                                        <td>Municipality (FROM):</td>
                                        <td id="schedule_from_municipality">--</td>
                                    </tr>
                                    <tr>
                                        <td>Dropoff point (FROM):</td>
                                        <td id="schedule_from_dop">--</td>
                                    </tr>
                                    <tr>
                                        <td>Assigned PC:</td>
                                        <td id="schedule_from_assigned_pc">--</td>
                                    </tr>
                                    <tr>
                                        <td>Province (TO):</td>
                                        <td id="schedule_to_province">--</td>
                                    </tr>
                                    <tr>
                                        <td>Municipality (TO):</td>
                                        <td id="schedule_to_municipality">--</td>
                                    </tr>
                                    <tr>
                                        <td>Dropoff point (TO):</td>
                                        <td id="schedule_to_dop">--</td>
                                    </tr>
                                    <tr>
                                        <td>Assigned PC (TO):</td>
                                        <td id="schedule_to_assigned_pc">--</td>
                                    </tr>
                                    <tr>
                                        <td>Total Bags:</td>
                                        <td id="schedule_from_bags_total">--</td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Date:</td>
                                        <td id="schedule_to_delivery_date">--</td>
                                    </tr>
                                    <tr>
                                        <td>Transfer Date:</td>
                                        <td id="schedule_to_transfer_date">--</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-striped">
                                    <tr><td colspan="2"><center><strong>ACTUAL</strong></center></td></tr>
                                    <tr>
                                        <td style="width:150px;">Transaction Title:</td>
                                        <td id="actual_transaction_title">--</td>
                                    </tr>
                                    <tr>
                                        <td>Transaction Code:</td>
                                        <td id="actual_transaction_code">--</td>
                                    </tr>
                                    <tr>
                                        <td>Seed Type:</td>
                                        <td id="actual_seed_type">--</td>
                                    </tr>
                                    <tr>
                                        <td>Source:</td>
                                        <td id="actual_source">--</td>
                                    </tr>
                                    <tr>
                                        <td>Seed Cooperatives:</td>
                                        <td id="actual_from_coop">--</td>
                                    </tr>
                                    <tr>
                                        <td>Province (FROM):</td>
                                        <td id="actual_from_province">--</td>
                                    </tr>
                                    <tr>
                                        <td>Municipality (FROM):</td>
                                        <td id="actual_from_municipality">--</td>
                                    </tr>
                                    <tr>
                                        <td>Dropoff point (FROM):</td>
                                        <td id="actual_from_dop">--</td>
                                    </tr>
                                    <tr>
                                        <td>Assigned PC:</td>
                                        <td id="actual_from_assigned_pc">--</td>
                                    </tr>
                                    <tr>
                                        <td>Province (TO):</td>
                                        <td id="actual_to_province">--</td>
                                    </tr>
                                    <tr>
                                        <td>Municipality (TO):</td>
                                        <td id="actual_to_municipality">--</td>
                                    </tr>
                                    <tr>
                                        <td>Dropoff point (TO):</td>
                                        <td id="actual_to_dop">--</td>
                                    </tr>
                                    <tr>
                                        <td>Assigned PC (TO):</td>
                                        <td id="actual_to_assigned_pc">--</td>
                                    </tr>
                                    <tr>
                                        <td>Total Bags:</td>
                                        <td id="actual_from_bags_total">--</td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Date:</td>
                                        <td id="actual_to_delivery_date">--</td>
                                    </tr>
                                    <tr>
                                        <td>Transfer Date:</td>
                                        <td id="actual_to_transfer_date">--</td>
                                    </tr>
                                </table>
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
        $("#date_range").daterangepicker(null,function(a,b,c){
            //console.log(a.toISOString(),b.toISOString(),c)
        });

        $("#document_status").on("change", function(e){
            var document_status = $("#document_status").val();
            if(document_status == "1"){
                $("#status_remarks_div").css("display", "block");
            }else{
                $("#status_remarks_div").css("display", "none");
            }
        });

        $("#show_status_modal").on("show.bs.modal", function(e){
            var transaction_code = $(e.relatedTarget).data('id');
            $("#transaction_code").val(transaction_code);
            //alert(transaction_code);

            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.transaction_details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    transaction_code: transaction_code
                },
                success: function(data){
                    $("#status_modal_title").empty().html(data["transaction_title"] + " [" + data["transaction_code"]+"]");
                    $("#delivery_status").val(data["delivery_status"]).change();
                    $("#document_status").val(data["document_status"]).change();

                    if(data["document_status"] == 1){
                        $("#status_remarks_div").css("display", "block");
                        $("#document_status_remarks").val(data["document_status_remarks"]);
                    }else{
                        $("#status_remarks_div").css("display", "none");
                        $("#document_status_remarks").val("");
                    }
                }
            });
        });

        $("#save_status_btn").on("click", function(e){
            var transaction_code = $("#transaction_code").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.transaction_details.save') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    transaction_code: transaction_code,
                    delivery_status: $("#delivery_status").val(),
                    document_status: $("#document_status").val(),
                    document_status_remarks: $("#document_status_remarks").val()
                },
                success: function(data){
                    
                    if(data == "update_ok"){
                        alert("You have successfully updated the selected schedule, the table will refresh after this message.");
                        $('#show_status_modal').modal('hide');
                        load_all();
                    }else{
                        alert("There was an error found while executing this function, the transaction has been rolled back | please refresh & try again.");
                        $('#show_status_modal').modal('hide');
                        load_all();
                    }
                }
            });
        });

        function load_all(){
            $('#schedule_tbl').DataTable().clear();
            $("#schedule_tbl").DataTable({
                "searching": false,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('rcep.google_sheet.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "search_filter": "ALL"
                    }
                },
                "columns":[
                    {"data": "seed_col"},
                    {"data": "origin_col"},
                    {"data": "destination_col"},
                    {"data": "status_col"},
                    {"data": "action", searchable: false}
                ]
            });
        }
        $("#from_seed_coop").select2();
        load_all();

        $("#reset_btn").on("click", function(e){
            load_all();
        });

        $("#from_province").on("change", function(e){
            $("#from_municipality").empty().append("<option value='0'>Loading muicipalities please wait...</option>");
            $("#from_assigned_pc").empty().append("<option value='0'>Loading assigned PC(s)...</option>");
            var province = $(this).val();
            
            //load all municipalities
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    view: "WITH_BALANCE"
                },
                success: function(data){
                    $("#from_municipality").empty().append(data);
                }
            });
        });

        $("#to_province").on("change", function(e){
            $("#to_municipality").empty().append("<option value='0'>Loading muicipalities please wait...</option>");
            $("#to_assigned_pc").empty().append("<option value='0'>Loading assigned PC(s)...</option>");
            var province = $(this).val();
            
            //load all municipalities
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#to_municipality").empty().append(data);
                }
            });
        });


        $("#date_range_btn").on("click", function(e){
            $('#schedule_tbl').DataTable().clear();
            $("#schedule_tbl").DataTable({
                "searching": false,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('rcep.google_sheet.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "search_filter": "DATE_RANGE",
                        "date_category": $("#date_category").val(),
                        "date_range": $("#date_range").val()
                    }
                },
                "columns":[
                    {"data": "seed_col"},
                    {"data": "origin_col"},
                    {"data": "destination_col"},
                    {"data": "status_col"},
                    {"data": "action", searchable: false}
                ]
            });
        });

        $("#title_btn").on("click", function(e){
            $('#schedule_tbl').DataTable().clear();
            $("#schedule_tbl").DataTable({
                "searching": false,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('rcep.google_sheet.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "search_filter": "TITLE_OF_TRANSACTION",
                        "transaction_title": $("#transaction_title").val(),
                    }
                },
                "columns":[
                    {"data": "seed_col"},
                    {"data": "origin_col"},
                    {"data": "destination_col"},
                    {"data": "status_col"},
                    {"data": "action", searchable: false}
                ]
            });
        });

        $("#search_transaction_btn").on("click", function(e){
            if($("#transaction_title").val() != "" && $("#date_range").val() != ""){
                $('#schedule_tbl').DataTable().clear();
                $("#schedule_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "SEARCH_TRANSACTION",
                            "transaction_title": $("#transaction_title").val(),
                            "date_category": $("#date_category").val(),
                            "date_range": $("#date_range").val()
                        }
                    },
                    "columns":[
                        {"data": "seed_col"},
                        {"data": "origin_col"},
                        {"data": "destination_col"},
                        {"data": "status_col"},
                        {"data": "action", searchable: false}
                    ]
                });
            }else{
                alert("Please fill up all the required fields...");
            }
        });

        $("#advanced_filter_btn").on("click", function(e){
            if($("#seed_type").val() == "NEW" && $("#source").val() == "SEED_COOP" ||
               $("#seed_type").val() == "INVENTORY_WS" && $("#source").val() == "SEED_COOP" || 
               $("#seed_type").val() == "INVENTORY_DS" && $("#source").val() == "SEED_COOP"){

                $('#schedule_tbl').DataTable().clear();
                $("#schedule_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "seed_type": $("#seed_type").val(),
                            "source": $("#source").val(),
                            "status": $("#status").val(),
                            "search_filter": "FILTER_SEED_COOP",
                            "from_coop": $("#from_seed_coop").val(),
                            "to_province": $("#to_province").val(),
                            "to_municipality": $("#to_municipality").val(),
                            "to_dop_name": $("#to_dop_name").val(),
                            "to_delivery_date": $("#to_delivery_date").val(),
                            "to_assigned_pc": $("#to_assigned_pc").val()
                        }
                    },
                    "columns":[
                        {"data": "seed_col"},
                        {"data": "origin_col"},
                        {"data": "destination_col"},
                        {"data": "status_col"},
                        {"data": "action", searchable: false}
                    ]
                });
                $('#advanced_filter_modal').modal('hide');

            }else if(
               $("#seed_type").val() == "INVENTORY_WS" && $("#source").val() == "PHILRICE_WAREHOUSE" || 
               $("#seed_type").val() == "INVENTORY_DS" && $("#source").val() == "PHILRICE_WAREHOUSE"){

                $('#schedule_tbl').DataTable().clear();
                $("#schedule_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "FILTER_PHILRICE_WAREHOUSE",
                            "seed_type": $("#seed_type").val(),
                            "source": $("#source").val(),
                            "status": $("#status").val(),
                            "from_coop": $("#from_seed_coop").val(),
                            "from_province": $("#from_province").val(),
                            "from_municipality": $("#from_municipality").val(),
                            "from_dop_name": $("#from_dop_name").val(),
                            "to_province": $("#to_province").val(),
                            "to_municipality": $("#to_municipality").val(),
                            "to_dop_name": $("#to_dop_name").val(),
                            "to_delivery_date": $("#to_delivery_date").val(),
                            "to_assigned_pc": $("#to_assigned_pc").val()
                        }
                    },
                    "columns":[
                        {"data": "seed_col"},
                        {"data": "origin_col"},
                        {"data": "destination_col"},
                        {"data": "status_col"},
                        {"data": "action", searchable: false}
                    ]
                });
                $('#advanced_filter_modal').modal('hide');
            
            }else if(
               $("#seed_type").val() == "INVENTORY_WS" && $("#source").val() == "LGU_STOCKS" || 
               $("#seed_type").val() == "INVENTORY_DS" && $("#source").val() == "LGU_STOCKS"){

                $('#schedule_tbl').DataTable().clear();
                $("#schedule_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "FILTER_LGU_STOCKS",
                            "seed_type": $("#seed_type").val(),
                            "source": $("#source").val(),
                            "status": $("#status").val(),
                            "from_province": $("#from_province").val(),
                            "from_municipality": $("#from_municipality").val(),
                            "from_dop_name": $("#from_dop_name").val(),
                            "from_assigned_pc": $("#from_assigned_pc").val(),
                        }
                    },
                    "columns":[
                        {"data": "seed_col"},
                        {"data": "origin_col"},
                        {"data": "destination_col"},
                        {"data": "status_col"},
                        {"data": "action", searchable: false}
                    ]
                });
                $('#advanced_filter_modal').modal('hide');
            
            }else if(
               $("#seed_type").val() == "INVENTORY_WS" && $("#source").val() == "TRANSFERRED_SEEDS" || 
               $("#seed_type").val() == "INVENTORY_DS" && $("#source").val() == "TRANSFERRED_SEEDS" ||
               $("#seed_type").val() == "NEW" && $("#source").val() == "TRANSFERRED_SEEDS"){

                $('#schedule_tbl').DataTable().clear();
                $("#schedule_tbl").DataTable({
                    "searching": false,
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcep.google_sheet.tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "search_filter": "FILTER_TRANSFERRED",
                            "seed_type": $("#seed_type").val(),
                            "source": $("#source").val(),
                            "status": $("#status").val(),
                            "from_province": $("#from_province").val(),
                            "from_municipality": $("#from_municipality").val(),
                            "from_dop_name": $("#from_dop_name").val(),
                            "from_assigned_pc": $("#from_assigned_pc").val(),
                            "to_province": $("#to_province").val(),
                            "to_municipality": $("#to_municipality").val(),
                            "to_dop_name": $("#from_dop_name").val(),
                            "to_delivery_date": $("#to_delivery_date").val(),
                            "to_assigned_pc": $("#to_assigned_pc").val()
                        }
                    },
                    "columns":[
                        {"data": "seed_col"},
                        {"data": "origin_col"},
                        {"data": "destination_col"},
                        {"data": "status_col"},
                        {"data": "action", searchable: false}
                    ]
                });
                $('#advanced_filter_modal').modal('hide');
            }
        });
        
        $("#seed_type").on("change", function(e){
            if($("#seed_type").val() == "NEW"){
                $("#source").empty().append("<option value='SEED_COOP'>Seed Cooperative / Association</option>");
                $("#source").append('<option value="TRANSFERRED_SEEDS">Transferred Seeds</option>');

                //FROM
                $("#from_seedCoop_div").css("display", "block");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");

                //TO
                $("#to_panel").css("display", "block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "block");
                $("#to_transferDate_div").css("display", "none");
                $("#to_assignedPC_div").css("display", "block");

            }else{
                $("#source").empty();
                $("#source").append('<option value="SEED_COOP">Seed Cooperative / Association</option>');
                $("#source").append('<option value="PHILRICE_WAREHOUSE">PhilRice Designated Warehouse</option>');
                $("#source").append('<option value="LGU_STOCKS">Stocks in LGU</option>');
                $("#source").append('<option value="TRANSFERRED_SEEDS">Transferred Seeds</option>');       
            }
        });


        $("#source").on("change", function(e){
            if($("#source").val() == "SEED_COOP"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "block");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");

                //TO
                $("#to_panel").css("display", "block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "block");
                $("#to_transferDate_div").css("display", "none");
                $("#to_assignedPC_div").css("display", "block");

            }else if($("#source").val() == "PHILRICE_WAREHOUSE"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "block")
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "none");

                //TO
                $("#to_panel").css("display", "block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "block");
                $("#to_transferDate_div").css("display", "none");
                $("#to_assignedPC_div").css("display", "block");

            }else if($("#source").val() == "LGU_STOCKS"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "none");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "block");

                //TO
                $("#to_panel").css("display", "none");

            }else if($("#source").val() == "TRANSFERRED_SEEDS"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "none");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "none");
                //seed variety
                $("#seed_variety_total").val("0");
                $("#seed_variety_str").val("");
                $("#seed_list_div").empty().append("");

                //TO
                $("#to_panel").css("display", "block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "none");
                $("#to_transferDate_div").css("display", "block");
                $("#to_assignedPC_div").css("display", "block");

            }else{
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "none");
                $("#from_bagsRemaining_div").css("display", "none");
                $("#from_bagsDelivery_div").css("display", "none");
                $("#from_seedVariety_div").css("display", "none");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_bagsTransfer_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");
            }
        });

        $("#seed_type_select").on("change", function(e){
            $('#schedule_tbl').DataTable().clear();
            $("#schedule_tbl").DataTable({
                "searching": false,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('rcep.google_sheet.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "search_filter_seedType": $("#seed_type_select").val()
                    }
                },
                "columns":[
                    {"data": "seed_col"},
                    {"data": "origin_col"},
                    {"data": "destination_col"},
                    {"data": "status_col"},
                    {"data": "action", searchable: false}
                ]
            });
        });

        $('#show_more_modal').on('show.bs.modal', function (e) {
            var transaction_id = $(e.relatedTarget).data('id');
            $("#show_more_id").val(transaction_id);
            $("#show_more_title").empty().html("["+transaction_id+"] - SHOW MORE DETAILS");

            $("#schedule_transaction_title").empty().html("loading...");
            $("#schedule_transaction_code").empty().html("loading...");
            $("#schedule_seed_type").empty().html("loading...");
            $("#schedule_source").empty().html("loading...");
            $("#schedule_status").empty().html("loading...");
            $("#schedule_from_coop").empty().html("loading...");
            $("#schedule_from_province").empty().html("loading...");
            $("#schedule_from_municipality").empty().html("loading...");
            $("#schedule_from_dop").empty().html("loading...");
            $("#schedule_from_assigned_pc").empty().html("loading...");
            $("#schedule_to_province").empty().html("loading...");
            $("#schedule_to_municipality").empty().html("loading...");
            $("#schedule_to_dop").empty().html("loading...");
            $("#schedule_to_assigned_pc").empty().html("loading...");
            $("#schedule_from_bags_total").empty().html("loading...");
            $("#schedule_to_delivery_date").empty().html("loading...");
            $("#schedule_to_transfer_date").empty().html("loading...");

            $("#actual_transaction_title").empty().html("loading...");
            $("#actual_transaction_code").empty().html("loading...");
            $("#actual_seed_type").empty().html("loading...");
            $("#actual_source").empty().html("loading...");
            $("#actual_status").empty().html("loading...");
            $("#actual_from_coop").empty().html("loading...");
            $("#actual_from_province").empty().html("loading...");
            $("#actual_from_municipality").empty().html("loading...");
            $("#actual_from_dop").empty().html("loading...");
            $("#actual_from_assigned_pc").empty().html("loading...");
            $("#actual_to_province").empty().html("loading...");
            $("#actual_to_municipality").empty().html("loading...");
            $("#actual_to_dop").empty().html("loading...");
            $("#actual_to_assigned_pc").empty().html("loading...");
            $("#actual_from_bags_total").empty().html("loading...");
            $("#actual_to_delivery_date").empty().html("loading...");
            $("#actual_to_transfer_date").empty().html("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.schedule_more') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    transaction_id: transaction_id
                },
                success: function(data){
                    $("#schedule_transaction_title").empty().html(data["schedule_transaction_title"]);
                    $("#schedule_transaction_code").empty().html(data["schedule_transaction_code"]);
                    $("#schedule_seed_type").empty().html(data["schedule_seed_type"]);
                    $("#schedule_source").empty().html(data["schedule_source"]);
                    $("#schedule_status").empty().html(data["schedule_status"]);
                    $("#schedule_from_coop").empty().html(data["schedule_from_coop"]);
                    $("#schedule_from_province").empty().html(data["schedule_from_province"]);
                    $("#schedule_from_municipality").empty().html(data["schedule_from_municipality"]);
                    $("#schedule_from_dop").empty().html(data["schedule_from_dop"]);
                    $("#schedule_from_assigned_pc").empty().html(data["schedule_from_assigned_pc"]);
                    $("#schedule_to_province").empty().html(data["schedule_to_province"]);
                    $("#schedule_to_municipality").empty().html(data["schedule_to_municipality"]);
                    $("#schedule_to_dop").empty().html(data["schedule_to_dop"]);
                    $("#schedule_to_assigned_pc").empty().html(data["schedule_to_assigned_pc"]);
                    $("#schedule_from_bags_total").empty().html(data["schedule_from_bags_total"]);
                    $("#schedule_to_delivery_date").empty().html(data["schedule_to_delivery_date"]);
                    $("#schedule_to_transfer_date").empty().html(data["schedule_to_transfer_date"]);

                    $("#actual_transaction_title").empty().html(data["actual_transaction_title"]);
                    $("#actual_transaction_code").empty().html(data["actual_transaction_code"]);
                    $("#actual_seed_type").empty().html(data["actual_seed_type"]);
                    $("#actual_source").empty().html(data["actual_source"]);
                    $("#actual_from_coop").empty().html(data["actual_from_coop"]);
                    $("#actual_from_province").empty().html(data["actual_from_province"]);
                    $("#actual_from_municipality").empty().html(data["actual_from_municipality"]);
                    $("#actual_from_dop").empty().html(data["actual_from_dop"]);
                    $("#actual_from_assigned_pc").empty().html(data["actual_from_assigned_pc"]);
                    $("#actual_to_province").empty().html(data["actual_to_province"]);
                    $("#actual_to_municipality").empty().html(data["actual_to_municipality"]);
                    $("#actual_to_dop").empty().html(data["actual_to_dop"]);
                    $("#actual_to_assigned_pc").empty().html(data["actual_to_assigned_pc"]);
                    $("#actual_from_bags_total").empty().html(data["actual_from_bags_total"]);
                    $("#actual_to_delivery_date").empty().html(data["actual_to_delivery_date"]);
                    $("#actual_to_transfer_date").empty().html(data["actual_to_transfer_date"]);
                }
            });
        });
    </script>
@endpush
