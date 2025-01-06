@extends('layouts.index')

@section('styles')
    <style>
        .card:hover .card-header {
            background: #cac7c7;
            cursor: auto;
        }
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button {  

        opacity: 1;

        }
        .btn.btn-warning:disabled{
            color: #fff;
            background-color: #ec971f;
            border-color: #d58512;
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
        <h3>{{$currentSeason}} TRANSFER MODULE (STOCKS) / PARTIAL-TRANSFER</h3>
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

        <!--TRANSFER DETAILS PANEL-->
        <div class="x_panel">
            <div class="x_title">
                <h2>1.) TRANSFER DETAILS</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-8">
                            <select name="seed_tag_list" class="form-control" id="seed_tag_list">
                                <option value="0">PLEASE SELECT A SEEDTAG</option>
                                @foreach ($batch_details as $row)
                                    @if($row->totalBagCount > 0)
                                        <option value="{{ $row->seedTag }}">{{ $row->seedTag }} ({{$row->seedVariety}})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" min="1" id="seed_tag_value" name="seed_tag_value" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" value="" id="seed_tag_str_temp" name="seed_tag_str_temp">
                </div>
                <div class="form-group">
                    <button class="btn btn-warning form-control" id="add_to_selection_btn"><i class="fa fa-plus-circle"></i> ADD TO `TRANSFER LIST`</button>
                </div>

                <hr>

                <div id="transfer_list_con" style="display: none">
                    <h4><strong><u>TRANSFER LIST</u></strong></h4>
                    <div id="transfer_div">
                        
                    </div>
                </div>
            </div>
        </div><br>
        <!--TRANSFER DETAILS PANEL-->

        <!--DESTINATION PANEL-->
        <div class="x_panel">
            <div class="x_title">
                <h2>2.) TRANSFER DESTINATION</h2>
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
                    <button class="btn btn-success" id="confirm_transfer_btn"><i class="fa fa-check-circle"></i> PLEASE CLICK TO CONFRIM TRANSFER</button>
                </div>
            </div>
        </div>
        <!--DESTINATION PANEL-->


        <div id="confirm_transfer_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">CONFRIM TRANSFER (PARTIAL-TRANSFER)</h4>
                    </div>
                    <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
                        <p>You are about to transfer the selected delivery to another location, before doing so, 
                            please double check your input based on the table below to avoid mistakes
                            in the transfer procedure, Please be advised: you will no longer be able to undo this action once executed. 
                            Do you wish to proceed with this transaction?</p>
                        <input type="hidden" name="batch_number" id="batch_number" value="{{$batch_number}}">

                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-5">
                                <table class="table table-bordered table-striped">
                                    <tr><td style="text-align: center;"><strong>TRANSFER DESTINATION</strong></td></tr>
                                    <tr><td id="dest_province">--</td></tr>
                                    <tr><td id="dest_municipality">--</td></tr>
                                    <tr><td id="dest_dop">--</td></tr>
                                </table>
                                <div class="bs-example" data-example-id="simple-jumbotron">
                                    <div class="jumbotron" style="padding: 15px;">
                                        <h3>Please always double check your data before submission.</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-bordered table-striped" id="confrim_seedtagList_tble">
                                    <thead>
                                        <th>Seed tag</th>
                                        <th>Transfer volume</th>
                                    </thead>
                                    <tbody id="confirm_tbl_body">
        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> CLOSE</button>
                        <button type="button" class="btn btn-success" id="go_partial_transfer_btn"><i class="fa fa-check-circle"></i> CONFIRM TRANSFER</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>

    $("#seed_tag_list").select2();

    $('#confirm_transfer_modal').on('show.bs.modal', function (e) {
        var transfer_str = $("#seed_tag_str_temp").val();
        var exploded_transferred_str = transfer_str.split("*|*");

        var province_name = $("#transfer_province option:selected").text();
        var municipality_name = $("#transfer_municipality option:selected").text();
        var dop_name = $("#transfer_dop option:selected").text();

        $("#dest_province").empty().html("<b>PROVINCE:</b> "+province_name);
        $("#dest_municipality").empty().html("<b>MUNICIPALITY:</b> "+municipality_name);
        $("#dest_dop").empty().html("<b>DROPOFF POINT:</b> "+dop_name);

        $("#confirm_tbl_body").empty();

        jQuery.each(exploded_transferred_str, function(index, array_value){
            var table_str = "";
            if(array_value != ""){
                var split_str = array_value.split("&");
                table_str = table_str + '<tr>';
                table_str = table_str + '<td>'+split_str[0]+'</td>';
                table_str = table_str + '<td>'+split_str[1]+' bag(s)</td>';
                table_str = table_str + '</tr>';
                $("#confirm_tbl_body").append(table_str);
            }
        });

        $("#confrim_seedtagList_tble").DataTable();
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
                province_name: province_name,
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

    //click of add to selection
    $("#add_to_selection_btn").on("click", function(e){
        var current_seedtag_str = $("#seed_tag_str_temp").val();
        var seed_tag = $("#seed_tag_list").val();
        var batch_number = "{{$batch_number}}";
        var seed_tag_value = $("#seed_tag_value").val();

        if(seed_tag_value != "" && seed_tag != "0" || seed_tag_value != 0 && seed_tag != "0"){

            $("#transfer_list_con").css("display", "block");
            $("#add_to_selection_btn").empty().html('<i class="fa fa-spinner fa-spin"></i> Adding seed tag to the list...');
            $("#add_to_selection_btn").attr("disabled", "");

            $.ajax({
                type: 'POST',
                url: "{{ route('transfers.check_seedtag') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    batch_number: batch_number,
                    seed_tag: seed_tag,
                    seed_tag_value: seed_tag_value
                },
                success: function(data){
                    if(data["msg"] == "amount_to_transfer_ok"){
                        $("#seed_tag_str_temp").val(current_seedtag_str + seed_tag + "&" + seed_tag_value + "*|*");
                        $('#seed_tag_list option:selected').remove();

                        var card_str = '';
                        card_str = card_str + '<div class="card" id="card_'+data["card_id"]+'">';
                        card_str = card_str + '<div class="card-header" id="headingOne">';
                        card_str = card_str + '<h5 class="mb-0" style="margin:0">';
                        card_str = card_str + '<h5 class="mb-0" style="margin:0">';
                        card_str = card_str + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">'+seed_tag+' - '+seed_tag_value+' bag(s)</button>';
                        card_str = card_str + '<button onclick="removeTagFromList('+data["card_id"]+','+seed_tag_value+')" class="btn btn-danger btn-xs pull-right" style="margin-top: 8px;margin-right: 10px;"><i class="fa fa-undo"></i> UNDO</button>';
                        card_str = card_str + '</h5>';
                        card_str = card_str + '</div>';
                        card_str = card_str + '</div>';
                        $("#transfer_div").append(card_str);

                        $("#seed_tag_value").val('');
                        $("#add_to_selection_btn").removeAttr("disabled");
                        $("#add_to_selection_btn").empty().html('<i class="fa fa-plus-circle"></i> ADD TO `TRANSFER LIST`');

                    }else if(data["msg"] == "amount_to_transfer_exceeds"){
                        alert("Your desired voulume exceeded the maximum amount of bags tagged to the selected seed tag. The maximum allowed volume for transfer for this seed tag is: " + data["max_tag"]);

                        $("#seed_tag_value").val('');
                        $("#add_to_selection_btn").removeAttr("disabled");
                        $("#add_to_selection_btn").empty().html('<i class="fa fa-plus-circle"></i> ADD TO `TRANSFER LIST`');
                    }
                },
            });
        }else{
            alert("please select a seed tag and indicate the volume to be transferred.");
        }
        
    });

    $(':input[type="number"]').on('keypress', function (event) {
        var regex = new RegExp("^[a-zA-Z0-9]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return alert('Invalid Input : Special characters are not allowed (+,-,.)');
        }
    });

    function removeTagFromList(index, seed_tag_value){
        var actual_delivery_id = index;

        $.ajax({
            type: 'POST',
            url: "{{ route('transfers.seed_tag.details') }}",
            data: {
                _token: "{{ csrf_token() }}",
                actual_delivery_id: actual_delivery_id
            },
            success: function(data){
                var option_text = data["seed_tag"] + " (" + data["seed_variety"] + ")";
                var option_value = data["seed_tag"];
                $("#seed_tag_list").append(new Option(option_text, option_value));
                $("#card_"+actual_delivery_id).remove();

                var str_to_search = option_value+"&"+seed_tag_value+"*|*";
                var temp_str = $("#seed_tag_str_temp").val();
                var new_str = temp_str.replace(str_to_search,'');

                $("#seed_tag_str_temp").val(new_str); 
            },
        });
    }

    $("#confirm_transfer_btn").on("click", function(e){
        if($("#transfer_province").val() != "00" && $("#transfer_municipality").val() != "00" && $("#transfer_dop").val() != "00" && $("#seed_tag_str_temp").val() != ""){
            $("#confirm_transfer_modal").modal('toggle');
        }else{
            alert("Please specify your transfer details (Choose from the seed tag selection box located at the 2nd panel / box right-side), select a province, municipality & dropoff point");
        }
    });


    $("#go_partial_transfer_btn").on("click", function(e){
        HoldOn.open(holdon_options);

        var province_name = $("#transfer_province option:selected").text();
        var municipality_name = $("#transfer_municipality option:selected").text();
        var dop_id = $("#transfer_dop").val();
        var batch_number = $("#batch_number").val();

        var transfer_str = $("#seed_tag_str_temp").val();

        $.ajax({
            type: 'POST',
            url: "{{ route('confirm.transfer.partial') }}",
            data: {
                _token: "{{ csrf_token() }}",
                batch_number: batch_number,
                original_province: "{{ $original_location_province }}",
                original_municipality: "{{ $original_location_municipality }}",
                origin_dop_id: "{{ $original_location_dop }}",
                destination_province: province_name,
                destination_municipality: municipality_name,
                destination_dropoff: dop_id,
                total_bags_intval: "{{$total_bags_intval}}",
                transfer_str: transfer_str,
            },
            success: function(data){
                if(data["alert"] == "sql_error"){
                    alert("an sql error has been encountered while performing this task, the database has been rolled back. Please refresh the page and try again.");
                }else{
                    window.location = data;
                }
            
                HoldOn.close();
            },
        });
    });

    
</script>
@endpush
