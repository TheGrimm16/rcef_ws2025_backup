@extends('layouts.index')

@section('styles')
    <style>
        .card:hover .card-header {
            background: #cac7c7;
            cursor: auto;
        }
    </style>
@endsection

@section('content')
<?php
      $curr =  basename(getcwd());
      $curr = str_replace("rcef_", "", $curr);
          if(strlen($curr) == 6){
            $curYr =  intval(substr($curr, 2, 4));
            $curSeason = strtoupper(substr($curr, 0, 2));
            $currentSeason = $curSeason.$curYr;
          }else{
            $currentSeason = "";
              }
    
?>
<div class="page-title">
    <div class="title_left" style="width: 100%;">
        <h3>{{$currentSeason}} TRANSFER MODULE (STOCKS) / WHOLE-TRANSFER</h3>
    </div>
</div>

<div class="clearfix"></div><br>

@include('layouts.message')

<div class="row">
    <div class="col-md-6">
        <div class="x_panel">
            <div class="x_title">
                <h2>[{{$batch_number}}] DELIVERY DETAILS</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                @foreach ($batch_details as $row)
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0" style="margin:0">
                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                    {{$row->seedVariety}} : {{$row->seedTag}}
                                </button>
                                <i class="fa fa-asterisk pull-right" style="margin-top: 12px;margin-right: 10px;" > {{$row->totalBagCount}} bag(s)</i>
                            </h5>
                        </div>
                    </div>
                @endforeach  
            </div>
        </div>
    </div>
    <div class="col-md-6">

        <!--ORIGIN PANEL-->
        <div class="x_panel">
            <div class="x_title">
                <h2>ORIGINAL LOCATION</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="form-group">
                    <input type="text" value="PROVINCE: {{$original_location_province}}" class="form-control" disabled readonly>
                </div>
                <div class="form-group">
                    <input type="text" value="MUNICIPALITY: {{$original_location_municipality}}" class="form-control" disabled readonly>
                </div>
                <div class="form-group">
                    <input type="text" value="DROPOFF POINT: {{$original_location_dop_name}}" class="form-control" disabled readonly>
                </div>
            </div>
        </div><br>
        <!--ORIGIN PANEL-->

        <!--DESTINATION PANEL-->
        <div class="x_panel">
            <div class="x_title">
                <h2>TRANSFER DESTINATION</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="form-group">
                    <select name="transfer_province" class="form-control" id="transfer_province">
                        <option value="00">PLEASE SELECT A PROVINCE</option>
                        @foreach ($provinces as $row)
                            <option value="{{ $row->province }}">{{ $row->province }}</option>
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
                    <button class="btn btn-success" id="confirm_transfer_btn"><i class="fa fa-check-circle"></i> PLEASE CLICK TO CONFRIM TRANSFER OF {{$total_bags}}</button>
                </div>
            </div>
        </div>
        <!--DESTINATION PANEL-->


        <div id="confirm_transfer_modal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">CONFRIM TRANSFER (WHOLE-TRANSFER)</h4>
                    </div>
                    <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
                        <p>You are about to transfer the selected delivery to another location, do you wish to proceed?</p>
                        <input type="hidden" name="batch_number" id="batch_number" value="{{$batch_number}}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> CLOSE</button>
                        <button type="button" class="btn btn-success" id="go_transfer_btn"><i class="fa fa-check-circle"></i> CONFIRM TRANSFER</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
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

    $("#confirm_transfer_btn").on("click", function(e){
        if($("#transfer_province").val() != "00" && $("#transfer_municipality").val() != "00" && $("#transfer_dop").val() != "00"){
            $("#confirm_transfer_modal").modal('toggle');
        }else{
            alert("Please select a province, municipality & dropoff point");
        }
    });

    $("#go_transfer_btn").on("click", function(e){
        var province_name = $("#transfer_province option:selected").text();
        var municipality_name = $("#transfer_municipality option:selected").text();
        var dop_id = $("#transfer_dop").val();
        var batch_number = $("#batch_number").val();

        $.ajax({
            type: 'POST',
            url: "{{ route('confirm.transfer.whole') }}",
            data: {
                _token: "{{ csrf_token() }}",
                batch_number: batch_number,
                original_province: "{{ $original_location_province }}",
                original_municipality: "{{ $original_location_municipality }}",
                origin_dop_id: "{{ $original_location_dop }}",
                destination_province: province_name,
                destination_municipality: municipality_name,
                destination_dropoff: dop_id,
                total_bags_intval: "{{$total_bags_intval}}"
            },
            success: function(data){
                if(data == "sql_error"){
                    alert("an sql error has been encountered while performing this task, the database has been rolled back. Please refresh the page and try again.");
                }else{
                    window.location = data;
                }
            },
        });
    });
</script>
@endpush
