@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<?php
$connected = @fsockopen("google.com", 80);
//website, port  (try 80 or 443)
if ($connected) {
    $connection = '<span class="badge badge-success" style="background-color: #28a745;">Connected</span>';
    fclose($connected);
} else {
    $connection = '<span class="badge badge-danger" style="background-color: #dc3545;">Not connected</span>';
}
?>
<div class="page-title">
    <div class="title_left">
        <h3>Data Transfer: <span id="connection"><?php echo $connection ?></span></h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="row tile_count">
</div>
{{-- 150647/II --}}
<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title col-md-12" style="float:left">
                <div style="float:left" class=""><strong>Connect to: </strong></div>
                <div style="float:left" class="col-md-4">
                    <select class="form-control" id="connect_to">
                        <option value="rcef">RCEF {{$season}}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <!--<button type="button" class="btn btn-round btn-primary" id="_connect">Connect</button>-->
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        
            <div class="x_panel">
                <div class="x_title">
                    <h2>Origin</h2>
                    <div class="clearfix"></div>
                </div>
            

                <div class="x_content form-horizontal form-label-left">
                        <select class="form-control" id="coop_id" name="coop_id" style="width:50%"></select>
                    <br>
                <table class="table table-striped table-bordered" id="batch_tbl">
                    <thead>
                        <th style="width:120px;">Batch Ticket #</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Seed Variety</th>
                        <th>Seed Tag</th>
                    
                        <th style="width: 100px;">Total Bags</th>
                        <th style="width: 100px;">Inspected</th>
                        <th style="width: 100px;">Select a Delivery</th>
                    </thead>
                </table>
            </div>
            </div>
        
     
    </div>
    <input type="hidden" value="" id="temp_transfer">
    <div id="script_load"></div>
</div>
@endsection
@push('scripts')
<script>
    $("#batch_tbl").DataTable();
    $("#coop_id").change(function () {
        var coop_id = $("#coop_id").val();
        var coop_name = $("#coop_id option:selected").text();
        if (coop_id != '') {
            HoldOn.open(holdon_options);
                var url = "{{route('transfers.oldseason.deliveries')}}";


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
                    "url": url,
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        coop_id: coop_id,
                        coop_name: coop_name,
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

            
            HoldOn.close();
        }
    });




    $.ajax({
        method: 'POST',
       // url: 'https://rcef-seed.philrice.gov.ph/rcef_ds2021/connect/get_coops',
       //url: 'http://localhost/rcef_l/connect/get_coops',
        url: "{{route('connect.get_coops')}}",
        data: {
            _token: _token,
            province: "{{ Auth::user()->province }}"
        },
        dataType: 'json',
        success: function (source) {
            $('select[name="coop_id"]').append('<option>--SELECT Cooperative--</option>');
            $.each(source, function (i, d) {
                $('select[name="coop_id"]').append('<option value="' + d.accreditation_no + '">' + d.coopName + '</option>');
            });
        }
    });

    $('#coop_id').select2();


</script>
@endpush
