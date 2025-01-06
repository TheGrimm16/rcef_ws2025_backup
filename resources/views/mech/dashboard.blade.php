@extends('layouts.index')

@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        .order-card {
            color: #fff;
        }
        
        .bg-c-blue {
            background: linear-gradient(45deg,#4099ff,#73b4ff);
            height: 18rem;
            position: relative;
        }

        .bg-c-yellow {
            background: linear-gradient(45deg,#FFB64D,#ffcb80);
            height: 18rem;
            position: relative;
        }
        .bg-c-green {
            background: linear-gradient(45deg,#2ed8b6,#59e0c5);
            height: 18rem;
            position: relative;
        }
        .bg-c-pink {
            background: linear-gradient(45deg,#FF5370,#ff869a);
            height: 18rem;
            position: relative;
        }

        .card {
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
            box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
            border: none;
            -webkit-transition: all 0.3s ease-in-out;
            transition: all 0.3s ease-in-out;
        }

        .card .card-block {
            padding: 20px;
        }

        .order-card i {
            font-size: 26px;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">




<div class="row">
  
  
            <div class="col-md-4">
                
                 
                 
                    <div class="card bg-c-blue order-card" >
                        <div class="card-block" >
                            <h2 class="m-b-20">Total Participating Seed Growers</h2>
                            <h1 class=""><i class="fa fa-user"></i><span>       Seed Grower</span></h1>
                            
                         <h5 style="position: absolute; bottom:0;"> Total Cooperatives:     SGC/A's  </h5> 
                          
                        </div>
                    </div>
               
                    

            </div>

            <div class="col-md-4">

                <div class="card bg-c-green order-card">
                    <div class="card-block">
                        <h2 class="m-b-20">Expected Planting Dates</h2>
                        <h2 class="">  <i class="fa fa-calendar-check-o" aria-hidden="true"></i> Start:    </span></h2>
                        <h2 class=""> <i class="fa fa-history" aria-hidden="true"></i> End:   </span></h2>
                        
                        
                    <h5 style="position: absolute; bottom:0;"> Total Area:   (ha)  </h5>
                      
                    </div>
                </div>


            
                 
            </div>
          
            <div class="col-md-4">

                <div class="card bg-c-yellow order-card">
                    <div class="card-block">
                        <h2 id="synced_date">Synced Date : {{date("F j, Y (g:i a)")}}</h2>
                        <button class="btn btn-success btn-sm " onclick="sync_rla();">
                            <i class="fa fa-refresh" aria-hidden="true" > Sync</i> 
                        </button> <br>
               
                        <button class="btn btn-dark btn-sm " disabled>
                            <i class="fa fa-file-excel-o" aria-hidden="true" > Export Results</i> 
                        </button>
               
                      
                    </div>
                </div>

             
            </div>
          
    

</div>


<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>RSIS RLA DASHBOARD</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
            
                        <div class="card">
                            <div class="card-header" id="headingOne" >


                                <h5 class="mb-0" style="margin:0;"  data-toggle="collapse" data-target="#collapse" aria-controls="" onclick="getCoopsDetails(, 'list_')" >
                           
                                          
                                   
                                            <button style="color: #655f5f;text-decoration:none;width:98%; margin:0;text-align:left;"  class="btn btn-link" disabled>
                                                <span class="badge badge-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>

                                                </span>    
                                   
                                  


                                     
                                    </button>
                                    <i class="fa fa-plus pull-right" id="icon_id_" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" ></i>
                                </h5>



                            </div>

                            <div id="collapse" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                <div class="card-body">
                                    <ul class="list-group row" style="width: 97%;margin-left: 1vw;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>



    
<!-- COOP BATCHES MODAL -->
<div id="show_rla_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 1500px; margin: auto; position: relative; top: 4%; left: 1%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="coop_name_modal">
                    <span>COOP NAME</span><br>
                </h4>
                <div>
                    <b>Passed: </b>
                    <span id="passed_modal">99999 bag(s)</span>
                </div>
                <div>
                    <b>Rejected: </b>
                    <span id="rejected_modal">99999 bag(s)</span>
                </div>
                <div>
                    <b>Pending: </b>
                    <span id="pending_modal">99999 bag(s)</span>
                </div>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered" id="coop_rsis_rla" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="">Grower Accreditation</th>
                            <th style="">Grower Name</th>
                            <th style="">Seed Class</th>
                            <th style="">Lab No</th>
                            <th style="">Lat No</th>
                            <th style="">Variety</th>
                            <th style="">Bags</th>
                            <th style="">Bag Weight</th>
                            <th style="">Lab Result</th>
                            <th style="">Cause of Reject</th>
                            <th style="">Date Sampled</th>
                            <th style="">Lab Received</th>
                            <th style="">Date Test Completed</th>
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


@endsection

@push('scripts')
<script>
	
    function sync_rla(){
        var yesno = confirm("Sync New Data from RSIS?");

        if(yesno){
            HoldOn.open(holdon_options);
                    $.ajax({
                    type: 'GET',
                    url: "https://rcefis.philmech.gov.ph/api/profileAssocs",
                    data: {
                        XApiKey: "pgH7QzFHJx4w46fI~5Uzi4RvtTwlEXp",
                
                    },
                    dateType: "json",
                    success: function (response, textStatus, request) {
                        HoldOn.close();  
                    }
                });


        }

    }

	 
  $('#show_rla_modal').on('show.bs.modal', function (e) {
        var account = $(e.relatedTarget).data('account'); 
        var passed = $(e.relatedTarget).data('passed'); 
        var rejected = $(e.relatedTarget).data('rejected'); 
        var pending = $(e.relatedTarget).data('pending'); 
        var coop_name = $(e.relatedTarget).data('coop_name'); 

        $("#coop_name_modal").empty().text(coop_name);
        $("#passed_modal").empty().text(passed + " bag(s)");
        $("#rejected_modal").empty().text(rejected + " bag(s)");
        $("#pending_modal").empty().text(pending + " bag(s)");
        
        //get batch details of selected coop
        $('#coop_rsis_rla').DataTable().clear();
        $("#coop_rsis_rla").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('rsis.view.coop.rla') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "account": account
                }
            },
            "columns":[
                {"data": "GrowerAccreNum"},
                {"data": "SeedGrower"},
                {"data": "SeedClass"},
                {"data": "LabNo"},
                {"data": "LotNo"},
                {"data": "Variety"},
                {"data": "BagsReceived"},
                {"data": "BagWeight"},
                {"data": "lab_result"},
                {"data": "CauseOfReject"},
                {"data": "DateSampled"},
                {"data": "LabReceivedDate"},
                {"data": "DateTestCompleted"},
            ]
        });
    });





    function getCoopsDetails(region_id, list_id){
        $("#icon_id_"+region_id).toggleClass('fa-plus fa-minus');

        $("#"+list_id).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
        $.ajax({
            type: 'POST',
            url: "{{ route('rsis.get.rsis.rs_distribution') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region_id
            },
            success: function(data){
                $("#"+list_id).empty();
                //header
                $("#"+list_id).append("<li class = 'list-group-item col-xs-3'><strong>Seed Cooperative</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-3'><strong>RS Distribution</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-3'><strong>Expected Planting Calendar</strong></li>");
         
                $("#"+list_id).append("<li class = 'list-group-item col-xs-3'><strong>Action</strong></li>");

                var height_var = "100px";

                var count = 0;
                jQuery.each(data, function(index, array_value){
                    //link
                    count = count + 1;
                   var view_btn = "<button disabled class='btn btn-warning btn-sm' style='width: 10vw;'  data-toggle='modal' data-target='#show_rla_modal' data-account='"+array_value['account_no']+"' data-passed='"+array_value['passed']+"' data-rejected='"+array_value['rejected']+"' data-pending='"+array_value['pending']+"' data-coop_name='"+array_value['coop_name']+"' ><i class='fa fa-folder-open-o' aria-hidden='true' > View </i></button>";
                   var export_btn = "<button disabled class='btn btn-success btn-sm' onclick='export_excel("+array_value['account_no']+")' style='width: 10vw;'><i class='fa fa-file-excel-o' aria-hidden='true' id='exp_"+array_value['account_no']+"'> Export </i></button>";
                    
        
                    //body
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-3'  style='height:"+height_var+";'><big><strong>"+array_value['coop_name']+"</strong></big>"
                        +"<br>Acct #:<strong>"+array_value['coop_accre']+"</strong>"
                        +"<br>MOA #: <strong>"+array_value['moa_number']+"</strong>"
                        +"</li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-3' style='height:"+height_var+";'>  <i class='fa fa-link' aria-hidden='true'></i> Total Participating SG:<strong> "+array_value['total_sg']+"</strong>"
                        +"<br> <i class='fa fa-map-marker' aria-hidden='true'></i> Total RS Production:<strong> "+array_value['total_area']+"</strong>"
                        +"<br> <i class='fa fa-download' aria-hidden='true'></i> Total Volume of RS Received:<strong> "+array_value['total_rs_production']+"</strong>"
                        +"</li>");

                        $("#"+list_id).append("<li class = 'list-group-item col-xs-3'  style='height:"+height_var+";'>"
                        +"Start:<strong>"+array_value['earliest']+"</strong>"
                        +"<br>End : <strong>"+array_value['lastest']+"</strong>"
                        +"</li>");

                    $("#"+list_id).append("<li class = 'list-group-item col-xs-3'  style='height:"+height_var+";'><center>"+view_btn+export_btn+"</center></li>");
                     
                
                });


                if(count == 0){
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-12'  style='height:70px;'><center><strong><h2> NO DATA ON RSIS RLA</h2></strong></center></li>");
                }





            }
        });
    }

    function export_excel(account){
          

            var yesno = confirm("Export Excel?");

            if(yesno){
            
                    var url ="{{url('/')}}";
                
                window.open(url+"/rsis/coop/export/"+account,"_blank");


            }


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

		
    });
</script>
@endpush()
