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

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title col-md-12" style="float:left">
                <div style="float:left" class=""><strong>Connect to: </strong></div>
                <div style="float:left" class="col-md-4">
                    <select class="form-control" id="connect_to">
                        <option value="rcef">RCEF WS2020</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <!--<button type="button" class="btn btn-round btn-primary" id="_connect">Connect</button>-->
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Origin</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" >
                    <div id="coops_div" class="col-md-12">
                        <select class="form-control" id="coop_id" name="coop_id" style="width:100%"></select>
                    </div>
                    <div class="col-md-12  ">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Deliveries</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <table class="table" id="varieties_table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Variety</th>
                                            <th>Total # of bags available</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="varieties_table_body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Destination</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="col-md-12" id="drop_off_div">
                        <select class="form-control" id="drop_id" name="drop_id" style="width:100%"></select>
                    </div>

                    <div class="col-md-12  ">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Transfers</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <table class="table" id="transfer_table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Variety</th>
                                            <th>Total # of bags transfered</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transfer_table_body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12" style="text-align: right;    background: #fff;    padding: 10px 0;    padding-right: 23px;">
            <button type="button" class="btn btn-round btn-success" id="_proceed_tranfer">Proceed Transfer</button>
        </div>
    </div>
    <input type="hidden" value="" id="temp_transfer">
    <div id="script_load"></div>
</div>
@endsection
@push('scripts')
<script>
    $("#_proceed_tranfer").click(function () {
        var ctr = 0;
        var temp_transfer = $("#temp_transfer").val();
        var drop_id = $("#drop_id").val();
        var drop_name = $("#drop_id option:selected").text();
        var coop_id = $("#coop_id").val();
        var explode = temp_transfer.split("<");
        for (var i = 0; i < parseInt(explode.length) - 1; i++) {
            var exp = explode[i].split(">");
            if (exp[1] != undefined) {
                ctr++;
            }
        }
        if (ctr > 0 && drop_id != '') {
            if (confirm('Are you sure you want to proceed?')) {	
				HoldOn.open(holdon_options);
                $.ajax({
                    method: 'POST',
                    url: "{{ route('rcef.transfers.transfer_proceed') }}",
                    data: {
                        _token: '{{csrf_token()}}',
                        temp_transfer: temp_transfer,
                        drop_id: drop_id,
                        drop_name: drop_name,
                        coop_id: coop_id
                    },
                    dataType: 'json',
                    success: function (source) {
						HoldOn.close();
                        $.ajax({
                            method: 'POST',
                            url: "https://rcef-seed.philrice.gov.ph/rcef_ws2020/connect/save_transfer",
                            data: {
                                _token: '{{csrf_token()}}',
                                temp_transfer: temp_transfer,
                                coop_id: coop_id,
								drop_id: drop_id,
                                created_by: '{{Auth::user()->username}}'
                            },
                            dataType: 'json',
                            success: function (source) {
								HoldOn.close();
                                alert("Transfer Successfuly processed!");
                                location.reload();
                            }
                        });
                    }
                });
            }
        } else {
            alert("Please complete the transaction.");
        }
    });
    $("#coop_id").change(function () {
        var coop_id = $("#coop_id").val();
        if (coop_id != '') {
            HoldOn.open(holdon_options);
            $.ajax({
                method: 'POST',
                url: 'https://rcef-seed.philrice.gov.ph/rcef_ws2020/connect/get_varieties',
                data: {
                    province: "{{ Auth::user()->province }}",
                    coop_id: coop_id
                },
                dataType: 'json',
                success: function (source) {
                    HoldOn.close();
                    $("#varieties_table_body").html("");
                    var ctr = 1;
                    var html = '';
                    $.each(source, function (i, d) {
                        html = html + '<tr><td>' + ctr + '</td><td>' + d.seedVariety + '</td><td>' + d.bags + '</td><td><button bags="' + d.bags + '" for="' + d.seedVariety + '" type="button" class="btn btn-round btn-primary select_var">Select</button></td></tr>';
                        ctr = parseInt(ctr) + 1;
                    });
                    $('#varieties_table > tbody:last-child').append(html);

                    $(".select_var").click(function () {
                        var txt;
                        var bags_total = $(this).attr("bags");
                        bags_total = bags_total.replace(",", "");
                        var bags = prompt("Please enter number of bags:", "");
                        if (isNaN(bags)) {
                            alert("Please input a number");
                        } else
                        if (bags == null || bags == "") {
                        } else if (parseInt(bags) > parseInt(bags_total)) {
                            alert("Stocks is not enough.");
                        } else {
                            var temp_transfer = $("#temp_transfer").val();
                            var coop_id = $("#coop_id").val();
                            var seed_variety = $(this).attr("for");
                            var arr = coop_id + ">" + seed_variety + ">" + bags + '<';
                            var arr2 = coop_id + ">" + seed_variety + ">";
                            var search_ = temp_transfer.search(arr2) >= 0;
                            var replace = temp_transfer.replace(arr2, "");

                            $("#temp_transfer").val(replace + arr);
                            reload_transfer();
                        }
                    });

                }
            });
        }
    });
    function reload_transfer() {
        var html = '';
        var ctr = 1;
        $("#transfer_table_body").html("");
        var temp_transfer = $("#temp_transfer").val();
        var explode = temp_transfer.split("<");
        for (var i = 0; i < parseInt(explode.length) - 1; i++) {
            var exp = explode[i].split(">");
            if (exp[1] != undefined) {
                html = html + '<tr><td>' + ctr + '</td><td>' + exp[1] + '</td><td>' + exp[2] + '</td><td><button bags="' + exp[2] + '" for="' + exp[1] + '" type="button" class="btn btn-round btn-danger delete_var">Delete</button></td></tr>';
                ctr = parseInt(ctr) + 1;
            }
        }
        $("#script_load").load("{{ route('rcef.transfers.loadscript') }}", {_token: '{{csrf_token()}}'}, function () {

        });
        $('#transfer_table > tbody:last-child').append(html);
    }


    $.ajax({
        method: 'POST',
        url: 'https://rcef-seed.philrice.gov.ph/rcef_ws2020/connect/get_coops',
        data: {
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


    $.ajax({
        method: 'POST',
        url: 'https://rcef-seed.philrice.gov.ph/rcef_ds2021/connect/get_dropoffpoints',
        data: {
            _token: _token,
            province: "{{ Auth::user()->province }}"
        },
        dataType: 'json',
        success: function (source) {
            $('select[name="drop_id"]').append('<option value="">--SELECT DROP OFF POINT--</option>');
            $.each(source, function (i, d) {
                $('select[name="drop_id"]').append('<option value="' + d.prv_dropoff_id + '">' + d.dropOffPoint + '</option>');
            });
        }
    });
    $('#drop_id').select2();
    $('#coop_id').select2();


</script>
@endpush
