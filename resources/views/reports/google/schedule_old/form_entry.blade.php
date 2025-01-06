@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
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
    input[type=number]::-webkit-inner-spin-button {
        opacity: 1
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" style="margin:0">
                    <button style="color: #7387a8;text-decoration:none;font-weight: 600;font-size:20px;" class="btn btn-link">
                        STATION BALANCE ({{strtoupper($station_name)}}): {{$total_current_balance}} / {{$total_original_balance}}
                    </button>
                </h5>
            </div>
        </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>
                SOURCE
            </h2>
            <button class="btn btn-warning pull-right" id="save_data_btn" style="border-radius: 20px;"><i class="fa fa-plus-circle"></i> SAVE SCHEDULE</button>               
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="form-horizontal form-label-left">
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Title</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="text" class="form-control" name="title_transaction" id="title_transaction" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Type</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" required>
                        <select name="seed_type" id="seed_type" class="form-control" required>
                            <option value="INVENTORY_DS">Inventory (DS2020)</option>
                            <option value="INVENTORY_WS">Inventory (WS2020)</option>
                            <option value="NEW" selected>New Seeds (DS2021)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Source</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" required>
                        <select name="source" id="source" class="form-control" required>
                            <option value="SEED_COOP">Seed Cooperative / Association</option>
                            <option value="TRANSFERRED_SEEDS">Transferred Seeds</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Status</label>
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
    </div>
    
    
    <div class="x_panel">
        <div class="x_title">
            <h2>
               FROM
            </h2>               
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="form-horizontal form-label-left">
                <div class="form-group" id="from_seedCoop_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Coop</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <select name="from_seed_coop" id="from_seed_coop" class="form-control" >
                            <option value="0">Please select a seed cooperative</option>
                            @foreach ($cooperatives as $row)
                                <option value="{{$row->accreditation_no}}">{{$row->coopName}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
				
				<div class="form-group" id="from_province_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Province:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <select name="from_province" id="from_province" class="form-control" >
                            <option value="0">Please select a province</option>
                            @foreach ($provinces as $from_province)
                                <option value="{{$from_province->province}}">{{$from_province->province}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" id="from_municipality_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Municipality:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <select name=" " id="from_municipality" class="form-control" >
                            <option value="0">Please select a municipality</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="from_dop_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Dropoff-Point:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="text" name="from_dop_name" id="from_dop_name" class="form-control">
                    </div>
                </div>

                <div class="form-group" id="from_assignedPC_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Assigned PC</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="text" class="form-control" id="from_assigned_pc" name="from_assigned_pc">
                    </div>
                </div>

                <div class="form-group" id="from_bagsRemaining_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Remaining Bags in LGU</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="number" class="form-control" id="from_bags_remaining" name="from_bags_remaining" min="1" max="9999" value="0" disabled readonly>
                    </div>
                </div>

                <div class="form-group" id="from_bagsDelivery_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>No. of Bags for Delivery:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="number" class="form-control" id="from_bags_for_delivery" name="from_bags_for_delivery" min="1" max="9999" value="0" disabled readonly>
                    </div>
                </div>

                <div class="form-group" id="from_bagsTransfer_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Bags for transfer</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="number" class="form-control" id="from_bags_for_transfer" name="from_bags_for_transfer" min="1" max="9999" value="0" disabled readonly>
                    </div>
                </div>

                <div class="form-group" id="from_seedVariety_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Variety:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <div class="row">
                            <div class="col-md-10">
                                <select name="from_seed_variety" id="from_seed_variety" class="form-control">
                                    <option value="0">Please select a seed variety</option>
                                    @foreach ($seed_varities as $seed)
                                        <option value="{{$seed->variety}}">{{$seed->variety}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <input type="number" id="seed_variety_total" name="seed_variety_total" class="form-control" min="1" max="9999" value="0">
                            </div>
                        </div>
                        <button class="btn btn-success" id="seed_variety_add_btn" style="border-radius: 20px;margin-top:5px;"><i class="fa fa-shopping-cart"></i> ADD TO SEED VARIETY LIST</button>

                        <br><br>
                        <input type="hidden" name="seed_variety_str" id="seed_variety_str" class="form-control">

                        <div id="seed_list_con" style="display: none">
                            <h4><strong><u>SEED VARIETY LIST</u></strong></h4>
                            <div id="seed_list_div">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="x_panel" id="to_panel">
        <div class="x_title">
            <h2>
               TO
            </h2>               
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="form-horizontal form-label-left">
                <div class="form-group" id="to_province_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Province:</label>
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
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Municipality:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <select name="to_municipality" id="to_municipality" class="form-control" >
                            <option value="0">Please select a municipality</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="to_dop_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Dropoff-Point:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="text" name="to_dop_name" id="to_dop_name" class="form-control">
                    </div>
                </div>

                <div class="form-group" id="to_deliveryDate_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Date of Delivery:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="date" name="to_delivery_date" id="to_delivery_date" class="form-control">
                    </div>
                </div>

                <div class="form-group" id="to_transferDate_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Date of Transfer:</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="date" name="to_transfer_date" id="to_transfer_date" class="form-control">
                    </div>
                </div>

                <div class="form-group" id="to_assignedPC_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Assigned PC</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="text" class="form-control" name="to_assigned_pc" id="to_assigned_pc">
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
        $("#from_seed_variety").select2();
        $("#from_seed_coop").select2();

        $("#seed_variety_add_btn").on("click", function(e){
            var seed_variety = $("#from_seed_variety").val();
            var seed_total   = $("#seed_variety_total").val();

            if(seed_variety != "0" && seed_total != "0"){

                $("#seed_list_con").css("display", "block");

                var variety_id = '';
                $.ajax({
                    type: 'POST',
                    url: "{{ route('rcep.google_sheet.variety_id') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        seed_variety: seed_variety
                    },
                    success: function(data){
                        variety_id = data;

                        var seed_str = $("#seed_variety_str").val();
                        $("#seed_variety_str").val(seed_str + seed_variety + "&" + seed_total + "|");
                        $('#from_seed_variety option:selected').remove();

                        var card_str = '';
                        card_str = card_str + '<div class="card" id="card_'+variety_id+'_id">';
                        card_str = card_str + '<div class="card-header" id="headingOne" style="height: 38px;">';
                        card_str = card_str + '<h5 class="mb-0" style="margin:0">';
                        card_str = card_str + '<h5 class="mb-0" style="margin:0">';
                        card_str = card_str + '<button style="color: #7387a8;text-decoration:none;margin-top: 4px;" class="btn btn-link">'+seed_variety+' - '+seed_total+' bag(s)</button>';
                        card_str = card_str + "<button onclick='removeTagFromList("+variety_id+", "+seed_total+")' class='btn btn-danger btn-xs pull-right' style='margin-top: 8px;margin-right: 10px;'><i class='fa fa-undo'></i> UNDO</button>";
                        card_str = card_str + '</h5>';
                        card_str = card_str + '</div>';
                        card_str = card_str + '</div>';
                        $("#seed_list_div").append(card_str);

                        $("#from_seed_variety").val("0").change();
                        $("#seed_variety_total").val("0");

                        var current_total = $("#from_bags_for_delivery").val();
                        var add_to_total = parseInt(current_total) + parseInt(seed_total);
                        
                        $("#from_bags_for_delivery").val(add_to_total);
                        $("#from_bags_remaining").val(add_to_total);
                        $("#from_bags_for_transfer").val(add_to_total);
                    }
                });

                
            }else{
                alert("Please select a seed variety & specify the volume of bags");
            }
        })


        function removeTagFromList(variety_id, seed_total){
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.variety_details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    variety_id: variety_id
                },
                success: function(data){
                    var variety_name = data

                    var card_id = "card_"+variety_id+"_id";
                    var option_text = variety_name;
                    var option_value = seed_total;

                    $("#from_seed_variety").append(new Option(option_text, option_value));
                    $("#"+card_id).remove();

                    var str_to_search = option_text+"&"+option_value+"|";
                    var temp_str = $("#seed_variety_str").val();
                    var new_str = temp_str.replace(str_to_search,'');

                    $("#seed_variety_str").val(new_str); 

                    var current_total = $("#from_bags_for_delivery").val();
                    var new_total = parseInt(current_total) - parseInt(option_value);
                    
                    $("#from_bags_for_delivery").val(new_total);
                    $("#from_bags_remaining").val(new_total);
                    $("#from_bags_for_transfer").val(new_total);
                },
            });
        };

        $("#from_bagsRemaining_div").css("display", "none");
        $("#from_province_div").css("display", "none");
        $("#from_municipality_div").css("display", "none");
        $("#from_dop_div").css("display", "none");
        $("#from_bagsTransfer_div").css("display", "none");
        $("#from_assignedPC_div").css("display", "none");
        $("#to_transferDate_div").css("display", "none");

        $("#seed_type").on("click", function(e){
            if($("#seed_type").val() == "NEW"){
                $("#source").empty().append("<option value='SEED_COOP'>Seed Cooperative / Association</option>");
                $("#source").append('<option value="TRANSFERRED_SEEDS">Transferred Seeds</option>');

                //FROM
                $("#from_seedCoop_div").css("display", "block");
                $("#from_bagsRemaining_div").css("display", "none");
                $("#from_bagsDelivery_div").css("display", "block");
                $("#from_seedVariety_div").css("display", "block");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_bagsTransfer_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");
                $("#seed_list_con").css("display", "none");
                //seed variety
                $("#seed_variety_total").val("0");
                $("#seed_variety_str").val("");
                $("#seed_list_div").empty().append("");

                $("#from_bags_for_delivery").val('0');
                $("#from_bags_for_delivery").attr('disabled', '');
                $("#from_bags_for_delivery").attr('readonly', '');

                //TO
                $("#to_panel").css("display", "inline-block");
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
                $("#from_bagsRemaining_div").css("display", "none");
                $("#from_bagsDelivery_div").css("display", "block");
                $("#from_seedVariety_div").css("display", "block");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_bagsTransfer_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");
                //seed variety
                $("#seed_variety_total").val("0");
                $("#seed_variety_str").val("");
                $("#seed_list_div").empty().append("");

                $("#from_bags_for_delivery").val('0');
                $("#from_bags_for_delivery").attr('disabled', '');
                $("#from_bags_for_delivery").attr('readonly', '');

                //TO
                $("#to_panel").css("display", "inline-block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "block");
                $("#to_transferDate_div").css("display", "none");
                $("#to_assignedPC_div").css("display", "block");

                $("#from_province").val("0").change();
                $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                $("#to_province").val("0").change();
                $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");

            }else if($("#source").val() == "PHILRICE_WAREHOUSE"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "block");
                $("#from_bagsRemaining_div").css("display", "none");
                $("#from_bagsDelivery_div").css("display", "block");
                $("#from_seedVariety_div").css("display", "block");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_bagsTransfer_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");
                //seed variety
                $("#seed_variety_total").val("0");
                $("#seed_variety_str").val("");
                $("#seed_list_div").empty().append("");
                //$("#from_bags_for_delivery").removeAttr('disabled');
                //$("#from_bags_for_delivery").removeAttr('readonly');

                //TO
                $("#to_panel").css("display", "inline-block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "block");
                $("#to_transferDate_div").css("display", "none");
                $("#to_assignedPC_div").css("display", "block");

                $("#from_province").val("0").change();
                $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                $("#to_province").val("0").change();
                $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");

            }else if($("#source").val() == "LGU_STOCKS"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "none");
                $("#from_bagsRemaining_div").css("display", "block");
                $("#from_bagsDelivery_div").css("display", "none");
                $("#from_seedVariety_div").css("display", "block");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_bagsTransfer_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "block");
                //seed variety
                $("#seed_variety_total").val("0");
                $("#seed_variety_str").val("");
                $("#seed_list_div").empty().append("");

                //TO
                $("#to_panel").css("display", "none");
                $("#from_province").val("0").change();
                $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                $("#to_province").val("0").change();
                $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");

            }else if($("#source").val() == "TRANSFERRED_SEEDS"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "none");
                $("#from_bagsRemaining_div").css("display", "none");
                $("#from_bagsDelivery_div").css("display", "none");
                $("#from_seedVariety_div").css("display", "block");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_bagsTransfer_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "none");
                //seed variety
                $("#seed_variety_total").val("0");
                $("#seed_variety_str").val("");
                $("#seed_list_div").empty().append("");

                //TO
                $("#to_panel").css("display", "inline-block");
                $("#to_province_div").css("display", "block");
                $("#to_municipality_div").css("display", "block");
                $("#to_dop_div").css("display", "block");
                $("#to_deliveryDate_div").css("display", "none");
                $("#to_transferDate_div").css("display", "block");
                $("#to_assignedPC_div").css("display", "block");

                $("#from_province").val("0").change();
                $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                $("#to_province").val("0").change();
                $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");

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

                $("#from_province").val("0").change();
                $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                $("#to_province").val("0").change();
                $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");
            }
        });

        $("#from_province").on("change", function(e){
            $("#from_municipality").empty().append("<option value='0'>Loading muicipalities please wait...</option>");
            $("#from_assigned_pc").empty().append("<option value='0'>Loading assigned PC(s)...</option>");
            var province = $(this).val();
            
            //load municipalities
            if($("#source").val() == "LGU_STOCKS" || $("#source").val() == "SEED_COOP"){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('rcep.google_sheet.municipalities') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                        view: "ALL"
                    },
                    success: function(data){
                        $("#from_municipality").empty().append(data);
                    }
                });
            }else{
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
            }
            
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
                    province: province,
                    view: "WITH_BALANCE"
                },
                success: function(data){
                    $("#to_municipality").empty().append(data);
                }
            });
        });


        function clear_all_fields(){
			$("#title_transaction").val("");
            $("#from_seed_coop").val("0").change();
            $("#from_bags_remaining").val("0");
            $("#from_bags_for_delivery").val("0");
            $("#from_seed_variety").val("0").change();
            $("#seed_variety_total").val("0");
            $("#seed_variety_str").val("");
            $("#seed_list_div").empty().append("");
            $("#seed_list_con").css("display", "none");
            $("#from_province").val("0").change();
            $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
            $("#from_dop_name").val("");
            $("#from_bags_for_transfer").val("0");
            $("#from_assigned_pc").val("");
            $("#to_province").val("0").change();
            $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");
            $("#to_dop_name").val("");
            $("#to_delivery_date").val("");
            $("#to_transfer_date").val("");
            $("#to_assigned_pc").val("");

            //refresh seed variety list
            $("#from_seed_variety").empty().append("<option value='0'>Refreshing seed varities please wait...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.seeds') }}",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(data){
                    $("#from_seed_variety").empty().append(data);
                }
            });
        }


        //submission of data
        $("#save_data_btn").on("click", function(e){
            var seed_type = $("#seed_type").val();
            var source = $("#source").val();

            if(seed_type == "NEW" && source == "SEED_COOP" || 
               seed_type == "INVENTORY_WS" && source == "SEED_COOP" ||
               seed_type == "INVENTORY_DS" && source == "SEED_COOP"){

                if($("#from_seed_coop").val() != "0" && $("#from_bags_for_delivery").val() != "0" && $("#seed_variety_str").val() != ""
                   && $("#to_province").val() != "0" && $("#to_municipality").val() != "0" && $("#to_dop_name").val() != "" 
                   && $("#to_delivery_date").val() != "" && $("#to_assigned_pc").val() != "" && $("#title_transaction").val() != ""){

                    //AJAX-SAVE NEW SEEDS DS(2021) | SOURCE: SEED COOP
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcep.google_sheet.saveNewDS2021') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            seed_type: seed_type,
                            source: source,
                            status: $("#status").val(),
                            from_seed_coop: $("#from_seed_coop").val(),
                            from_bags_for_delivery: $("#from_bags_for_delivery").val(),
                            from_seed_variety: $("#seed_variety_str").val(),
                            to_province: $("#to_province").val(),
                            to_municipality: $("#to_municipality").val(),
                            to_dop_name: $("#to_dop_name").val(),
                            to_delivery_date: $("#to_delivery_date").val(),
                            to_assigned_pc: $("#to_assigned_pc").val(),
                            title_transaction: $("#title_transaction").val()
                        },
                        success: function(data){
                            if(data == "balance_insufficient"){
                                alert("Insufficient balance! Please try a different amount & try again.")
                            }else if(data == "sql_error"){
                                alert("An error occurred while processing you transaction, please hit refresh & try again.");
                            }else if(data == "scheduled_ok"){
                                alert("Successfully saved new schedule");
                                clear_all_fields();
                            }
                        }
                    });

                }else{
                    alert("please fill-up all the fields...");
                }
            
            }else if(seed_type == "INVENTORY_WS" && source == "PHILRICE_WAREHOUSE" || seed_type == "INVENTORY_DS" && source == "PHILRICE_WAREHOUSE"){
                if($("#from_seed_coop").val() != "0" && $("#from_bags_for_delivery").val() != "0" && $("#from_province").val() != "0" && $("#from_municipality").val() != "0"
                  && $("from_dop_name").val() != "" && $("#to_province").val() != "0" && $("#to_municipality").val() != "0" && $("#to_dop_name").val() != ""
                  && $("#to_delivery_date").val() != "" && $("#to_assigned_pc").val() != "" && $("#seed_variety_str").val() != "" && $("#title_transaction").val() != ""){

                    //AJAX-SAVE INVENTORY | SOURCE: WAREHOUSE
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcep.google_sheet.saveInventory_warehouse') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            seed_type: seed_type,
                            source: source,
                            status: $("#status").val(),
                            from_seed_coop: $("#from_seed_coop").val(),
                            from_bags_for_delivery: $("#from_bags_for_delivery").val(),
                            from_province: $("#from_province").val(),
                            from_municipality: $("#from_municipality").val(),
                            from_dop_name: $("#from_dop_name").val(),
                            from_seed_variety: $("#seed_variety_str").val(),
                            to_province: $("#to_province").val(),
                            to_municipality: $("#to_municipality").val(),
                            to_dop_name: $("#to_dop_name").val(),
                            to_delivery_date: $("#to_delivery_date").val(),
                            to_assigned_pc: $("#to_assigned_pc").val(),
                            title_transaction: $("#title_transaction").val()
                        },
                        success: function(data){
                            if(data == "balance_insufficient"){
                                alert("Insufficient balance! Please try a different amount & try again.")
                            }else if(data == "sql_error"){
                                alert("An error occurred while processing you transaction, please hit refresh & try again.");
                            }else if(data == "scheduled_ok"){
                                alert("Successfully saved new schedule");
                                clear_all_fields();
                            }
                        }
                    });
                    
                }else{
                    alert("please fill-up all the fields...");
                }
           
            }else if(seed_type == "INVENTORY_WS" && source == "LGU_STOCKS" || seed_type == "INVENTORY_DS" && source == "LGU_STOCKS"){
                if($("#from_bags_remaining").val() != "0" && $("#seed_variety_str").val() != "" && $("#from_province").val() != "0"
                   && $("#from_municipality").val() != "0" && $("#from_dop_name").val() != "" && $("#from_assigned_pc").val() != ""
                   && $("#title_transaction").val() != ""){
                    
                    //AJAX-SAVE INVENTORY | SOURCE: LGU
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcep.google_sheet.saveInventory_lgu') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            seed_type: seed_type,
                            source: source,
                            status: $("#status").val(),
                            from_bags_remaining: $("#from_bags_remaining").val(),
                            from_seed_variety: $("#seed_variety_str").val(),
                            from_province: $("#from_province").val(),
                            from_municipality: $("#from_municipality").val(),
                            from_dop_name: $("#from_dop_name").val(),
                            from_assigned_pc: $("#from_assigned_pc").val(),
                            title_transaction: $("#title_transaction").val()
                        },
                        success: function(data){
                            if(data == "balance_insufficient"){
                                alert("Insufficient balance! Please try a different amount & try again.")
                            }else if(data == "sql_error"){
                                alert("An error occurred while processing you transaction, please hit refresh & try again.");
                            }else if(data == "scheduled_ok"){
                                alert("Successfully saved new schedule");
                                clear_all_fields();
                            }
                        }
                    });

                }else{
                    alert("please fill-up all the fields...");
                }

            }else if(seed_type == "INVENTORY_WS" && source == "TRANSFERRED_SEEDS" || 
                    seed_type == "INVENTORY_DS" && source == "TRANSFERRED_SEEDS" ||
                    seed_type == "NEW" && source == "TRANSFERRED_SEEDS"){
                
                if($("#seed_variety_str").val() != "" && $("#from_province").val() != "0" && $("#from_municipality").val() != "0"
                   && $("#from_dop_name").val() != "" && $("#from_bags_for_transfer").val() != "0" && $("#to_province").val() != "0"
                   && $("#to_municipality").val() != "0" && $("#to_dop_name").val() != "" && $("#to_transfer_date").val() != "" 
                   && $("#to_assigned_pc").val() != "" && $("#title_transaction").val() != ""){

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('rcep.google_sheet.saveInventory_transferred') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            seed_type: seed_type,
                            source: source,
                            status: $("#status").val(),
                            from_seed_variety: $("#seed_variety_str").val(),
                            from_province: $("#from_province").val(),
                            from_municipality: $("#from_municipality").val(),
                            from_dop_name: $("#from_dop_name").val(),
                            from_bags_for_transfer: $("#from_bags_for_transfer").val(),
                            to_province: $("#to_province").val(),
                            to_municipality: $("#to_municipality").val(),
                            to_dop_name: $("#to_dop_name").val(),
                            to_transfer_date: $("#to_transfer_date").val(),
                            to_assigned_pc: $("#to_assigned_pc").val(),
                            title_transaction: $("#title_transaction").val()
                        },
                        success: function(data){
                            if(data == "balance_insufficient"){
                                alert("Insufficient balance! Please try a different amount & try again.")
                            }else if(data == "sql_error"){
                                alert("An error occurred while processing you transaction, please hit refresh & try again.");
                            }else if(data == "scheduled_ok"){
                                alert("Successfully saved new schedule");
                                clear_all_fields();
                            }
                        }
                    });

                }else{
                    alert("please fill-up all the fields...");
                }
            }
        });
    </script>
@endpush
