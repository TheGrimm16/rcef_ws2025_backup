@extends('layouts.index')

@section('styles')
    <style>
        #transfer_progress{
            width: 100%;
            background-color: #e9ecef;
            height: 350px;
            padding: 15px;
            max-height: 350px;
            overflow: auto;
            font-size: 15px;
            font-weight: 500;
        }
    </style>
@endsection

@section('content')

<?php
        $curr =  basename(getcwd());
        //$curr = "rcef_ws2021";
      $curr = str_replace("rcef_", "", $curr);
          if(strlen($curr) == 6){
            $curYr =  intval(substr($curr, 2, 4));
            $curSeason = strtoupper(substr($curr, 0, 2));
            $currentSeason = $curSeason.' '.$curYr;
          }else{
            $currentSeason = "";
              }
    
?>


<div class="page-title">
    <div class="title_left" style="width: 100%;">
        <h3> {{$currentSeason}} - {{$currentSeason}} TRANSFER MODULE (STOCKS) </h3>
    </div>
</div>

<div class="clearfix"></div><br>

<div class="row">
    <div class="col-md-12">

        <div class="x_panel">
            <div class="x_title">
                <h2>DELIVERY LOCATION</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="form-group">
                    <select name="transfer_province" class="form-control" id="transfer_province">
                        <option value="00">PLEASE SELECT A PROVINCE</option>
                        @foreach ($provinces as $row)
                            <option value="{{ $row->provCode }}">{{ $row->province }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <select name="transfer_municipality" class="form-control" id="transfer_municipality">
                        <option>PLEASE SELECT A MUNICIPALITY</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="transfer_dop" class="form-control" id="transfer_dop">
                        <option>PLEASE SELECT A DROPOFF POINT</option>
                    </select>
                </div>

                <div class="form-group">
                    <button class="btn btn-success" id="load_batch_btn"><i class="fa fa-database"></i> FETCH DELIVERIES (INSPECTED DELIVERIES)</button>
                </div>
            </div>
        </div><br>

        <!---BATCH DELIVERIES-->
        <div class="x_panel">
            <div class="x_title">
                <h2>DELIVERY LIST</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-striped table-bordered" id="batch_tbl">
                    <thead>
                        <th style="width:120px;">Batch Ticket #</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Seed Variety</th>
                        <th>Seed Tag</th>
                        
                        <th style="width: 100px;">Total Bags</th>
                        <th style="width: 100px;">Inspected</th>
                        <th style="width: 100px;">Action</th>
                    </thead>
                </table>
            </div>
        </div><br>
        <!---BATCH DELIVERIES-->

        <div id="proceed_transfer_modal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">TRANSFER OPTIONS</h4>
                    </div>
                    <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
                        <p>To continue this procedure, please select from the options A). WHOLE-TRANSFER - transfer the whole delivery to another location. or 
                            B). PARTIAL-TRANSFER - transfer a part of the original delivery to another location (this will create and save a new batch ticket number that will be tagged to the transferred delivery)</p>
                            <input type="hidden" name="batch_number" id="batch_number">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" id="a_option_btn"><i class="fa fa-truck"></i> A) WHOLE-TRANSFER</button>
                        <button type="button" class="btn btn-success" id="b_option_btn"><i class="fa fa-truck"></i> B) PARTIAL-TRANSFER</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
</div>
@endsection

@push('scripts')
<script>
    $("#batch_tbl").DataTable();

    $('#proceed_transfer_modal').on('show.bs.modal', function (e) {
        var batch_number = $(e.relatedTarget).data('batch');
        $("#batch_number").val(batch_number);
    });

    //on select province
    $("#transfer_province").on("change", function(e){
        var province = $(this).val();
        var province_name = $("#transfer_province option:selected").text();

        $("#transfer_municipality").empty().append("<option>Loading municipalities please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('transfers.municipalities') }}",
            data: {
                _token: "{{ csrf_token() }}",
                province: province,
                province_name: province_name
            },
            success: function(data){
                $("#transfer_municipality").empty().append("<option value='00'>PLEASE SELECT A MUNICIPALITY</option>");
                $("#transfer_dop").empty().append("<option value='00'>PLEASE SELECT A DROPOFF POINT</option>");
                $("#transfer_municipality").append(data);
            },
        });
    });

    $("#transfer_municipality").on("change", function(e){
        var province_name = $("#transfer_province option:selected").text();
        var municipality_name = $("#transfer_municipality option:selected").text();

        $("#transfer_dop").empty().append("<option>Loading dropoff points please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('transfers.dropoff') }}",
            data: {
                _token: "{{ csrf_token() }}",
                province: province_name,
                municipality: municipality_name
            },
            success: function(data){
                $("#transfer_dop").empty().append("<option value='00'>PLEASE SELECT A DROPOFF POINT</option>");
                $("#transfer_dop").append(data);
            },
        });
    });

    $("#load_batch_btn").on("click", function(e){
        if($("#transfer_province").val() != "00" && $("#transfer_municipality").val() != "00" && $("#transfer_dop").val() != "00"){
            var province_name = $("#transfer_province option:selected").text();
            var municipality_name = $("#transfer_municipality option:selected").text();
            var dop_id = $("#transfer_dop").val();

            $('#batch_tbl').DataTable().clear();
            $("#batch_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('transfers.ws2020.deliveries') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province_name,
                        municipality: municipality_name,
                        dropoff: dop_id
                    }
                },
                "columns":[
                    {"data": "batchTicketNumber"},
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "variety_list", searchable: false},
                    {"data": "seed_tags"},
                    
                    {"data": "total_bags"},
                    {"data": "date_inspected"},
                    {"data": "action", searchable: false},
                ]
            });

        }else{
            alert("Please select a province, municipality & dropoff point");
        }
    });

    $("#a_option_btn").on("click", function(e){
        var batch_number = $("#batch_number").val();
        var url = '{{ route("transfers.ws2020.whole", ":batch_number") }}';
        url = url.replace(':batch_number', batch_number);
        window.location.href=url;
    });

    $("#b_option_btn").on("click", function(e){
        var batch_number = $("#batch_number").val();
        var url = '{{ route("transfers.ws2020.partial", ":batch_number") }}';
        url = url.replace(':batch_number', batch_number);
        window.location.href=url;
    });

</script>
@endpush
