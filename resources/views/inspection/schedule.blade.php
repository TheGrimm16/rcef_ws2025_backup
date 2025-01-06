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

    <div>
        <div class="clearfix"></div>

        @include('layouts.message')

        <div class="row">
            <div class="col-md-8">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>1. Select Location</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Region:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="drop_region" id="drop_region" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Region</option>
                                    @foreach($delivery_regions as $row)
                                        <option value="{{ $row->region }}">{{ $row->region }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Province:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="drop_province" id="drop_province" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Province</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="drop_municipality" id="drop_municipality" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Municipality</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <button class="btn btn-success form-control" type="button" id="generate_batch_btn"><i class="fa fa-cloud-download"></i> GET DELIVERIES (BATCH TICKETS)</button>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- BATCH TICKETS TABLE -->
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>2. DELIVERY DETAILS</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-striped table-bordered" id="batch_delivery_tbl">
                            <thead>
                                <th>Batch Ticket #</th>
                                <th>Seed Cooperative</th>
                                <th>Dropoff Point</th>
                                <th>Total Bags</th>
                                <th>Date of Delivery</th>
                                <th>Delivery Status</th>
                                <th>Action</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- BATCH TICKETS TABLE -->

        <!--VIEW INSPECTOR MODAL-->
        <div id="view_inspector_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal_title">DELIVERY DETAILS:</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="batch_number" name="batch_number" value="">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <th width="20%">Description</th>
                                <th>Corresponding Value</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Seed Cooperative</td>
                                    <td id="modal_seed_coop">--</td>
                                </tr>
                                <tr>
                                    <td>Delivery address</td>
                                    <td id="modal_address"></td>
                                </tr>
                                <tr>
                                    <td>Dropoff Point</td>
                                    <td id="modal_dropoff"></td>
                                </tr>
                                <tr>
                                    <td>Total Bags</td>
                                    <td id="modal_total_bags"></td>
                                </tr>
                                <tr>
                                    <td>Date of Delivery</td>
                                    <td id="modal_date_of_delivery"></td>
                                </tr>
                                <tr>
                                    <td>Assigned Inspector</td>
                                    <td id="modal_inspector"></td>
                                </tr>
                            </tbody>    
                        </table><hr style="border-top: 2px solid #cecece;">

                        <div id="inspector_wrapper" style="display:none">
                            <label for="inspector">*Please select a seed inspector:</label>
                            <select name="inspector" id="inspector" class="form-control">
                                <option value="0">Please select an Inspector</option>
                            </select><br><br>

                            <label for="">*Please state a reason for replacing the currently assigned seed inspector</label>
                            <textarea name="inspector_reason" id="inspector_reason" rows="5" class="form-control"></textarea><br>

                            <button class="btn btn-success" role="button" id="update_inspector_btn"><i class="fa fa-retweet"></i> UPDATE ASSIGNED SEED INSPECTOR</button>
                        </div> 
                        

                    </div>
                </div>
                <!-- Modal content-->
            </div>
        </div>
        <!--VIEW INSPECTOR MODAL-->

    </div>
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script>
        $("#batch_delivery_tbl").DataTable();
        $("#drop_region").on("change", function(e){
            var region = $(this).val();
            $("#coop_region").val("loading please wait...");
            $("#drop_province").empty().append("<option value='0'>Loading provinces please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.inspector.province') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#drop_province").empty().append("<option value='0'>Please select a province</option>");
                    $("#drop_province").append(data);
                }
            });
        });

        $("#drop_province").on("change", function(e){
            var province = $(this).val();
            var region = $("#drop_region").val();
            $("#drop_municipality").empty().append("<option value='0'>Loading municipalities please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.inspector.municipality') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    region: region
                },
                success: function(data){
                    $("#drop_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#drop_municipality").append(data);
                }
            });
        });

        //generate batch deliveries
        $("#generate_batch_btn").on("click", function(e){
            var region = $("#drop_region").val();
            var province = $("#drop_province").val();
            var municipality = $("#drop_municipality").val();

            if(region != "0" && province != "0" && municipality != "0"){
                $("#batch_delivery_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('rcef.inspector.table') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "region": region,
                            "province": province,
                            "municipality": municipality
                        }
                    },
                    "columns":[
                        {"data": "batchTicketNumber"},
                        {"data": "seed_coop"},
                        //{"data": "dropOffPoint"},
						{"data": "dropoff_name"},
                        {"data": "total_dBags"},
                        {"data": "date_of_delivery"},
                        {"data": "delivery_status"},
                        {"data": "action_fld"},
                    ]
                });
            }else{
                alert("Please select a region, province and municipality to continue.");
            }
        });

        $('#view_inspector_modal').on('show.bs.modal', function (e) {
            $("#inspector_wrapper").css("display", "none");

            var batch_number = $(e.relatedTarget).data('batch');
            $("#modal_title").empty().html("DELIVERY DETAILS: ("+batch_number+")");
            $("#batch_number").val(batch_number);

            $("#modal_seed_coop").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_address").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_dropoff").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');            
            $("#modal_total_bags").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_date_of_delivery").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_inspector").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');

            $.ajax({
                type: 'POST',
                url: "{{ route('inspector.batch.fetch_details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    batch_number: batch_number,
                },
                success: function(data){
                    $("#modal_seed_coop").empty().html(data["seed_coop"]);
                    $("#modal_address").empty().html(data["region"]+", "+data["province"]+", "+data["municipality"]);
                    $("#modal_dropoff").empty().html(data["dropoff"]);
                    $("#modal_total_bags").empty().html(data["total_bags"] + " bag(s)");
                    $("#modal_date_of_delivery").empty().html(data["date_of_delivery"]);
                    $("#modal_inspector").empty().html(data["current_inspector"]);

                    if(data["inspector_list"] != "no_inspector"){
                        $("#inspector_wrapper").css("display", "block");
                        
                        $("#inspector").empty().append("<option value='0'>Please select a seed inspector</option>");
                        $("#inspector").append(data["inspector_list"]);
                        $("#inspector").select2();

                    }else{
                        $("#inspector_wrapper").css("display", "none");
                    }
                }
            });
        });

        $("#update_inspector_btn").on("click", function(e){
            var inspectorID = $("#inspector").val();
            var reason = $("#inspector_reason").val();
            var batch_number = $("#batch_number").val();

            if(inspectorID != "0" && reason != ""){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('inspector.schedule.update') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        inspectorID: inspectorID,
                        reason: reason,
                        batch_number: batch_number
                    },
                    success: function(data){
                        alert("You have successfully updated the assigned seed inspector for the batch ticket number: ("+batch_number+")");
                        window.location = data;
                    }
                });
            }else{
                alert("please select a seed inspector and input a reason to continue...");
            }
        });
    </script>
@endpush