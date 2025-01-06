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
                        STATION BALANCE ({{strtoupper($station_name)}}): {{$total_current_balance}}
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

                <div class="form-group" id="from_totalBags_div">
                    <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Total Bags</label>
                    <div class="col-md-10 col-sm-10 col-xs-10" >
                        <input type="number" class="form-control" id="from_bags_total" name="from_bags_total" min="1" max="9999" value="0">
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

        $("#from_province_div").css("display", "none");
        $("#from_municipality_div").css("display", "none");
        $("#from_dop_div").css("display", "none");
        $("#from_assignedPC_div").css("display", "none");
        $("#to_transferDate_div").css("display", "none");

        $("#seed_type").on("click", function(e){
            if($("#seed_type").val() == "NEW"){
                $("#source").empty().append("<option value='SEED_COOP'>Seed Cooperative / Association</option>");
                $("#source").append('<option value="TRANSFERRED_SEEDS">Transferred Seeds</option>');

                //FROM
                $("#from_seedCoop_div").css("display", "block");
                $("#from_totalBags_div").css("display", "block");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");
                $("#seed_list_con").css("display", "none");
                
                $("#from_bags_total").val('0');

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
                $("#from_totalBags_div").css("display", "block");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
                $("#from_assignedPC_div").css("display", "none");
                $("#from_bags_total").val('0');

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
                $("#from_totalBags_div").css("display", "block");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "none");

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
                $("#from_totalBags_div").css("display", "block");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "block");

                //TO
                $("#to_panel").css("display", "none");
                $("#from_province").val("0").change();
                $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
                $("#to_province").val("0").change();
                $("#to_municipality").empty().append("<option value='0'>Please select a municipality</option>");

            }else if($("#source").val() == "TRANSFERRED_SEEDS"){
                //FROM SECTION
                $("#from_seedCoop_div").css("display", "none");
                $("#from_totalBags_div").css("display", "block");
                $("#from_province_div").css("display", "block");
                $("#from_municipality_div").css("display", "block");
                $("#from_dop_div").css("display", "block");
                $("#from_assignedPC_div").css("display", "none");

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
                $("#from_totalBags_div").css("display", "none");
                $("#from_province_div").css("display", "none");
                $("#from_municipality_div").css("display", "none");
                $("#from_dop_div").css("display", "none");
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
			if($("#source").val() == "SEED_COOP" || $("#source").val() == "TRANSFERRED_SEEDS" || $("#source").val() == "PHILRICE_WAREHOUSE"){
                $.ajax({
					type: 'POST',
					url: "{{ route('rcep.google_sheet.municipalities') }}",
					data: {
						_token: "{{ csrf_token() }}",
						province: province,
						view: "ALL"
					},
					success: function(data){
						$("#to_municipality").empty().append(data);
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
						$("#to_municipality").empty().append(data);
					}
				});
            }
        });


        function clear_all_fields(){
			$("#title_transaction").val("");
            $("#from_seed_coop").val("0").change();
            $("#from_bags_total").val("0");
            $("#from_province").val("0").change();
            $("#from_municipality").empty().append("<option value='0'>Please select a municipality</option>");
            $("#from_dop_name").val("");
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

                if($("#from_seed_coop").val() != "0" && $("#from_bags_for_delivery").val() != "0" && $("#to_province").val() != "0" 
                   && $("#to_municipality").val() != "0" && $("#to_dop_name").val() != "" && $("#to_delivery_date").val() != "" 
                   && $("#to_assigned_pc").val() != "" && $("#title_transaction").val() != "" && $("#from_bags_total").val() != "0"){

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
                            from_bags_total: $("#from_bags_total").val(),
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
                if($("#from_province").val() != "0" && $("#from_province").val() != "0" && $("#from_municipality").val() != "0"
                  && $("from_dop_name").val() != "" && $("#to_province").val() != "0" && $("#to_municipality").val() != "0" && $("#to_dop_name").val() != ""
                  && $("#to_delivery_date").val() != "" && $("#to_assigned_pc").val() != "" && $("#title_transaction").val() != ""){

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
                            from_bags_total: $("#from_bags_total").val(),
                            from_province: $("#from_province").val(),
                            from_municipality: $("#from_municipality").val(),
                            from_dop_name: $("#from_dop_name").val(),
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
                if($("#from_bags_total").val() != "0" && $("#seed_variety_str").val() != "" && $("#from_province").val() != "0"
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
                            from_bags_total: $("#from_bags_total").val(),
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
                
                if($("#from_province").val() != "0" && $("#from_municipality").val() != "0"
                   && $("#from_dop_name").val() != "" && $("#from_bags_total").val() != "0" && $("#to_province").val() != "0"
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
                            from_bags_total: $("#from_bags_total").val(),
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
