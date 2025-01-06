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
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2> BUFFER INSPECTION CHANGE INSPECTOR</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">

                     
                    </div>
                </div>
            </div>
        </div>



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
                                <th>Date of 1st Inspection</th>
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
                        <input type="hidden" id="cid" name="cid" value="">

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
                                    <td>Dropoff Point</td>
                                    <td id="modal_dropoff"></td>
                                </tr>
                                <tr>
                                    <td>Total Bags</td>
                                    <td id="modal_total_bags"></td>
                                </tr>
                                <tr>
                                    <td>Date of First Inspection</td>
                                    <td id="modal_date_of_delivery"></td>
                                </tr>
                                <tr>
                                    <td>Assigned Inspector</td>
                                    <td id="modal_inspector"></td>
                                </tr>
                                
                            </tbody>    
                        </table><hr style="border-top: 2px solid #cecece;">

                        CHANGE INSPECTOR

                        <table class="table table-striped table-bordered">
                            <thead>
                                <th width="50%">Select Inspector</th>
                                <th>Reason</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                            <select name='changeInspector' id='changeInspector' style='width:100%' class='form-control'>
                                                <option value='0'>Please Select Inspector </option>
                                                    @foreach($inspectors as $inspectors)
                                                        <option value='{{$inspectors->userId}}'>{{$inspectors->lastName}},{{$inspectors->firstName}} {{$inspectors->middleName}} ( {{$inspectors->username}} )</option>
                                                    @endforeach
                                            </select>   
                                    </td>
                                    <td>
                                            <textarea name='remarks' id='remarks' class='form-control'></textarea>

                                    </td>
                                </tr>
                             
                            </tbody>    
                        </table><hr style="border-top: 2px solid #cecece;">



                    </div>
                    <div class='modal-footer'> 
                        <button class="btn btn-primary" id="updateInspector"> Update </button>

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
                url: "{{ route('api.provinces.buffer.dropoff') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region,
                    tagged: 1
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
                url: "{{ route('api.municipalities.buffer.dropoff') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    region: region,
                    tagged: 1
                },
                success: function(data){
                    $("#drop_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#drop_municipality").append(data);
                }
            });
        });



        function generateBatch(){
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
                        "url": "{{ route('rcef.buffer.inspector.table') }}",
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
                        {"data": "action_fld"},
                    ]
                });
            }else{
                alert("Please select a region, province and municipality to continue.");
            }

        }



        //generate batch deliveries
        $("#generate_batch_btn").on("click", function(e){
            generateBatch();
        });

        $('#view_inspector_modal').on('show.bs.modal', function (e) {
         

            var batch_number = $(e.relatedTarget).data('batch');
            var inspector = $(e.relatedTarget).data('inspector');
            var id =  $(e.relatedTarget).data('id');
            var coop_name =  $(e.relatedTarget).data('coop');
            var bags =  $(e.relatedTarget).data('bags');
            var dop =  $(e.relatedTarget).data('dop');
            var ins_date =  $(e.relatedTarget).data('ins_date');
            var cid =  $(e.relatedTarget).data('cid');
            

            $("#modal_title").empty().html("DELIVERY DETAILS: ("+batch_number+")");
            $("#batch_number").val(batch_number);
            $("#cid").val(cid);
            

            $("#modal_seed_coop").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_dropoff").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');            
            $("#modal_total_bags").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_date_of_delivery").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#modal_inspector").empty().html('<i class="fa fa-refresh fa-spin"></i> Fetching data, please wait...');
            $("#remarks").empty();


            $("#modal_seed_coop").empty().html(coop_name);
            $("#modal_dropoff").empty().html(dop);
            $("#modal_total_bags").empty().html(bags);
            $("#modal_date_of_delivery").empty().html(ins_date);
            $("#modal_inspector").empty().html(inspector);

           
        });

        $("#updateInspector").on("click", function(e){
            var inspectorID = $("#changeInspector").val();
            var reason = $("#remarks").val();
            var batch_number = $("#batch_number").val();
            var cid = $("#cid").val();
            

            if(inspectorID != "0" && reason != ""){
                var yesno = confirm("Change Inspector for batch: "+batch_number);
                HoldOn.open(holdon_options);
                if(yesno){
                    $.ajax({
                    type: 'POST',
                    url: "{{ route('inspector.buffer.schedule.update') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        inspectorID: inspectorID,
                        reason: reason,
                        batch_number: batch_number,
                        cid: cid
                    },
                    success: function(data){
                        
                        alert(data);
                        generateBatch();
                        HoldOn.close();
                        $('#view_inspector_modal').modal('hide');
                    }
                });
                }





                
            }else{
                alert("please select a seed inspector and input a reason to continue...");
                HoldOn.close();
            }
        });


        $('select[name="changeInspector"]').select2();


    </script>
@endpush