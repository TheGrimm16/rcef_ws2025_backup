@extends('layouts.index')

@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Seed Cooperative Delivery Report: <strong>Commitment vs Confirmed vs Inspected</strong></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
                    @foreach ($regions as $region)
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0" data-toggle="collapse" data-target="#collapse{{$region->id}}" onclick="getCoopsDetails({{$region->id}}, 'list_{{$region->id}}')">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                        {{$region->regDesc}}
                                    </button>
                                    <i class="fa fa-plus pull-right" id="icon_id_{{$region->id}}" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse{{$region->id}}" aria-controls="{{$region->id}}" onclick="getCoopsDetails({{$region->id}}, 'list_{{$region->id}}')"></i>
                                </h5>
                            </div>
                            <div id="collapse{{$region->id}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                <div class="card-body">
                                    <ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="list_{{$region->id}}">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COOP BATCHES MODAL -->
<div id="show_coop_batches_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 4%; left: 1%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="coop_name_modal">
                    <span>COOP NAME</span><br>
                </h4>
                <div>
                    <b>Commited Volume: </b>
                    <span id="committed_volume_modal">99999 bag(s)</span>
                </div>
                <div>
                    <b>Confirmed Volume: </b>
                    <span id="confirmed_volume_modal">99999 bag(s)</span>
                </div>
                <div>
                    <b>( Commited - Confirmed )</b>
                    <span id="inspected_volume_modal">99999 bag(s)</span>
                </div>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered" id="coop_batch_table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="">Batch Code</th>
                            <th style="">Seed's Variety</th>
                            <!--<th style="">Delivery (20kg/Bag)</th>-->
                            <th style="">Delivery Date</th>
                            <th style="">Province</th>
                            <th style="">Municipality</th>
                            <th style="">Drop off Points</th>
                            <th style="">Confirmed</th>
                            <th style="">Inspected</th>
                            <th style="">Status</th>
                             <th style="">Action</th>
                            <!--<th style="">Inspected (20kg/Bag)</th>-->
                            <!--<th style="">Inspected Date</th>-->
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- COOP BATCHES MODAL -->


<!-- COOP TRANSFER MODAL -->
<div id="show_coop_transfer_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 4%; left: 1%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="coop_name_trans_modal">
                    <span></span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-2">Batch Number:</label>
                <label id="modal_batch"></label> <br>
                <table class="table table-striped table-bordered" id="coop_batch_table_transfer" style="width: 100%;">
                    <thead> 
                        <tr>
                            <th style="">Actual Batch #</th>
                            <th style="">Origin</th>
                            <th style="">Destination</th>
                            <th style="">Seed Variety</th>
                            <th style="">Seed Tag</th>
                            <!--<th style="">Seed Type</th>-->
                            <th style="">Volume </th> 
                            <th style="">Date </th>
                            <th style="">Transfer Category </th>
                        </tr>
                    </thead>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- COOP TRANSFER MODAL -->






@endsection

@push('scripts')
<script>
	
	 
  $('#show_coop_transfer_modal').on('show.bs.modal', function (e) {
        var batch = $(e.relatedTarget).data('batch'); 
        var new_batch = $(e.relatedTarget).data('new_batch'); 
        var tc = $(e.relatedTarget).data('transfercategory');
        var seedVariety = $(e.relatedTarget).data('seedvariety');
        var prv_dropoff_id = $(e.relatedTarget).data('dopid');


            $("#coop_name_trans_modal").empty().html("TRANSFER HISTORY");
         
            $("#modal_batch").empty().html(batch);
        //get batch details of selected coop
        $('#coop_batch_table_transfer').DataTable().clear();
        $("#coop_batch_table_transfer").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('delivery_dashboard.batch.transfer.list') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "tc": tc,
                    "batch": batch,
                    "new_batch": new_batch,
                    "seedVariety": seedVariety,
                    "prv_dropoff_id": prv_dropoff_id 
                }
            },
            "columns":[
                {"data": "batch_num"},
                {"data": "origin"},
                {"data": "destination"},
                {"data": "seedVariety"},
                {"data": "seedTag"},
                //{"data": "seedType"}, 
                {"data": "bags"},
                {"data": "dateCreated"},
                {"data": "transferType"},
            ]
        });
    });





    function getCoopsDetails(region_id, list_id){
        $("#icon_id_"+region_id).toggleClass('fa-plus fa-minus');

        $("#"+list_id).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.coop.region') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region_id
            },
            success: function(data){
                $("#"+list_id).empty();
                //header
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2'><strong>Seed Cooperative</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2'><strong>Commitments</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2'><strong>Confirmed</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2'><strong>Inspected</strong></li>");
                
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2'><strong>Confirmed Replacement</strong></li>");

                $("#"+list_id).append("<li class = 'list-group-item col-xs-2'><strong>Action</strong></li>");


                var count = 0;
                jQuery.each(data, function(index, array_value){
                    //link
                    count = count + 1;
                    var button_id = "btn_"+region_id+"_"+count;
                    var button_id_py = "btn_py_"+region_id+"_"+count;
                    var button_id_fmd = "btn_fmd_"+region_id+"_"+count;
                    
                    var button_id_iop = "btn_iop_"+region_id+"_"+count;
                    var coop_accreditation = "'"+array_value['coop_accre']+"', '"+button_id+"'";
                    var coop_accreditation_py = "'"+array_value['coop_accre']+"', '"+button_id_py+"'";
                    var coop_accreditation_fmd = "'"+array_value['coop_accre']+"', '"+button_id_fmd+"'";
                    
                    var coop_accreditation_iop = "'"+array_value['coop_accre']+"', '"+button_id_iop+"'";
                    
                    var action_btn = "<a href='#' data-coop_accre="+array_value['coop_accre']+" data-coop="+array_value['cop_id']+" data-total_commit="+array_value['total_commit']+" data-total_confirmed="+array_value['total_confirmed']+" data-total_inspected="+array_value['total_inspected']+" data-toggle='modal' data-target='#show_coop_batches_modal' class='btn btn-warning btn-sm form-control'><i class='fa fa-search'></i> View Deliveries</a>";
                    var export_btn = '<button id="'+button_id+'" onclick="exportDeliveries('+coop_accreditation+')" class="btn btn-success btn-sm form-control"><i class="fa fa-table"></i> Export Deliveries</button>';
                    var export_btn_py = '<button id="'+button_id_py+'" onclick="exportDeliveriesPy('+coop_accreditation_py+')" class="btn btn-success btn-sm form-control"><i class="fa fa-table"></i> Export Deliveries v2</button>';
                    var export_btn_fmd = '<button  style="width:47%;" id="'+button_id_fmd+'" onclick="exportDeliveries_fmd('+coop_accreditation_fmd+')" class="btn btn-primary btn-sm form-control"><i class="fa fa-table"></i> FMD FORMAT</button>';
                    
                    var iop_button = '<button  style="width:47%;"  id="'+button_id_iop+'" onclick="exportindexOfPayment('+coop_accreditation_iop+')" class="btn btn-success btn-sm form-control"><i class="fa fa-money"></i> Index of Payment</button>';
                    //body
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:180px;'><strong>"+array_value['coop_name']+"</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:180px;'><strong>"+array_value['total_commit']+" bag(s)</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:180px;'><strong>"+array_value['total_confirmed']+" bag(s)</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:180px;'><strong>"+array_value['total_inspected']+" bag(s)</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:180px;'><strong>"+array_value['confirmed_replacement']+" bag(s)</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:180px;'><strong>"+action_btn+export_btn+export_btn_py+iop_button+export_btn_fmd+"</strong></li>");
                });
            }
        });
    }

    function exportindexOfPayment(coop_accreditation, button_id){
        $("#"+button_id).empty().html("Fetching data...");
        $("#"+button_id).attr("disabled","");

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.export_deliveries.iop') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accreditation: coop_accreditation
            },
            success: function (response, textStatus, request) {
                var a = document.createElement("a");
                a.href = response.file; 
                a.download = response.name;
                document.body.appendChild(a);
                a.click();
                a.remove();

                $("#"+button_id).removeAttr('disabled');
                $("#"+button_id).empty().html('<i class="fa fa-money"></i> Index of Payment');
            }
        });
    }

    function exportDeliveries_fmd(coop_accreditation, button_id){
        $("#"+button_id).empty().html("Fetching data...");
        $("#"+button_id).attr("disabled","");

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.export_deliveries_fmd') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accreditation: coop_accreditation
            },
            success: function (response, textStatus, request) {
                var a = document.createElement("a");
                a.href = response.file; 
                a.download = response.name;
                document.body.appendChild(a);
                a.click();
                a.remove();

                $("#"+button_id).removeAttr('disabled');
                $("#"+button_id).empty().html('<i class="fa fa-table"></i> FMD FORMAT');
            }
        });
    }


    function exportDeliveries(coop_accreditation, button_id){
        $("#"+button_id).empty().html("Fetching data...");
        $("#"+button_id).attr("disabled","");

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.export_deliveries') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accreditation: coop_accreditation
            },
            success: function (response, textStatus, request) {
                var a = document.createElement("a");
                a.href = response.file; 
                a.download = response.name;
                document.body.appendChild(a);
                a.click();
                a.remove();

                $("#"+button_id).removeAttr('disabled');
                $("#"+button_id).empty().html('<i class="fa fa-table"></i> Export Deliveries');
            }
        });
    }
    function exportDeliveriesPy(coop_accreditation, button_id_py){
        $("#"+button_id_py).empty().html("Fetching data...");
        $("#"+button_id_py).attr("disabled","");

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.export_deliveriesPy') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accreditation: coop_accreditation
            },
            success: function (response, textStatus, request) {
                // console.log(response.output);
                // var a = document.createElement("a");
                // a.href = response.file; 
                // a.download = response.name;
                // document.body.appendChild(a);
                // a.click();
                // a.remove();
                window.open(`report/home/${response.output}`, '_blank');
                setTimeout(() => {
                        $.ajax({
                        type: 'GET',
                        url: "{{ route('py_unlinking') }}", 
                        data: {
                            _token: "{{ csrf_token() }}",
                            uri: response.output
                        },
                        success: function(data){
                            // console.log(data);
                        }
                    });
                    }, 1000);

                $("#"+button_id_py).removeAttr('disabled');
                $("#"+button_id_py).empty().html('<i class="fa fa-table"></i> Export Deliveries v2');
            }
        });
    }

    $("#coop_batch_table").DataTable();

    $('#show_coop_batches_modal').on('show.bs.modal', function (e) {
        var coop_id = $(e.relatedTarget).data('coop');
        var coop_accre = $(e.relatedTarget).data('coop_accre');
        var total_commit = $(e.relatedTarget).data('total_commit');
        var total_confirmed = $(e.relatedTarget).data('total_confirmed');
        var total_inspected = $(e.relatedTarget).data('total_inspected');

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.coop.name') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_id: coop_id
            },
            success: function(data){
                $("#coop_name_modal").empty().html(data);
            }
        });
        $("#committed_volume_modal").empty().html(total_commit+" bag(s)");
        $("#confirmed_volume_modal").empty().html(total_confirmed+" bag(s)");
        $("#inspected_volume_modal").empty().html(total_inspected+" bag(s)");
    
        //get batch details of selected coop
        $('#coop_batch_table').DataTable().clear();
        $("#coop_batch_table").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('delivery_dashboard.batch.list') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "coop_accre": coop_accre
                }
            },
            "columns":[
                {"data": "batchTicketNumber"},
                {"data": "seedVariety"},
                {"data": "deliveryDate"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "dropOffPoint"},
                {"data": "confirmed"},
                {"data": "inspected"},
                {"data": "batch_status"},
                {"data": "action"}
            ]
        });

        /*$.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.batch.list') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accre: coop_accre
            },
            success: function(data){
               // $("#coop_name_modal").empty().html(data);
            }
        });*/
		
		
		
		
		
    });
</script>
@endpush()
