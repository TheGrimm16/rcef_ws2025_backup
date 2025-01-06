@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12" style="height: 1200px;">

        <form action="{{route('rcep.google_sheet.editInventoryWarehouse')}}" method="POST" data-parsley-validate="" id="edit_form">
            {{ csrf_field() }}
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        @if($source == "INVENTORY_WS")
                            INVENTORY (WS2020) | PHILRICE WAREHOUSE
                        @elseif($source == "INVENTORY_DS")
                            INVENTORY (DS2020) | PHILRICE WAREHOUSE
                        @elseif($source == "NEW")
                            NEW SEEDS (DS2021) | PHILRICE WAREHOUSE
                        @endif
                    </h2>

                    @if($schedule_edit_final_flag == 1)
                        <button role="button" type="button" id="save_as_draft_btn" class="btn btn-warning pull-right" style="border-radius:20px;" disabled readonly><i class="fa fa-edit"></i> EDIT SCHEDULE</button>
                        <!--<button role="button" type="button" id="save_as_final_btn" class="btn btn-danger pull-right" style="border-radius:20px;" disabled readonly><i class="fa fa-lock"></i> SAVE AS FINAL</button>-->                    
                    @else
                        <button role="button" type="button" id="save_as_draft_btn" class="btn btn-warning pull-right" style="border-radius:20px;"><i class="fa fa-edit"></i> EDIT SCHEDULE</button>
                        <!--<button role="button" type="button" id="save_as_final_btn" class="btn btn-danger pull-right" style="border-radius:20px;"><i class="fa fa-lock"></i> SAVE AS FINAL</button>-->
                    @endif
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <input type="hidden" id="edit_draft_flag" name="edit_draft_flag" value="{{$schedule_edit_draft_flag}}">
                    <input type="hidden" id="edit_final_flag" name="edit_final_flag" value="{{$schedule_edit_final_flag}}">
                    <input type="hidden" id="transaction_code" name="transaction_code" value="{{$schedule_transaction_code}}">

                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Title</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" class="form-control" name="title_transaction" id="title_transaction" value="{{$schedule_title}}" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Type</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="seed_type" id="seed_type" class="form-control" required>
                                    @if($source == "INVENTORY_WS")
                                        <option value="INVENTORY_WS" selected>Inventory (WS2020)</option>
                                    @else
                                        <option value="INVENTORY_WS">Inventory (WS2020)</option>
                                    @endif

                                    @if($source == "INVENTORY_DS")
                                        <option value="INVENTORY_DS" selected>Inventory (DS2020)</option>
                                    @else
                                        <option value="INVENTORY_DS">Inventory (DS2020)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Source</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="source" id="source" class="form-control" required readonly>
                                    <option value="PHILRICE_WAREHOUSE">PhilRice Designated Warehouse</option>
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Status</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="status" id="status" class="form-control" required>
                                    @if($status == "APPROVED")
                                        <option value="APPROVED" selected>Approved</option>
                                    @else
                                        <option value="APPROVED">Approved</option>
                                    @endif

                                    @if($status == "RESCHEDULED")
                                        <option value="RESCHEDULED" selected>Re-Scheduled</option>
                                    @else
                                        <option value="RESCHEDULED">Re-Scheduled</option>
                                    @endif

                                    @if($status == "CANCELLED")
                                        <option value="CANCELLED" selected>Cancelled</option>
                                    @else
                                        <option value="CANCELLED">Cancelled</option>
                                    @endif
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
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Coop</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="from_seed_coop" id="from_seed_coop" class="form-control" required>
                                    <option value="0">Please select a seed cooperative</option>
                                    @foreach ($cooperatives as $row)
                                        @if($schedule_coop == $row->accreditation_no)
                                            <option value="{{$row->accreditation_no}}" selected>{{$row->coopName}}</option>
                                        @else
                                            <option value="{{$row->accreditation_no}}">{{$row->coopName}}</option>
                                        @endif            
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
                                        @if($schedule_from_province == $from_province->province)
                                            <option value="{{$from_province->province}}" selected>{{$from_province->province}}</option>
                                        @else
                                            <option value="{{$from_province->province}}">{{$from_province->province}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group" id="from_municipality_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Municipality:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <select name="from_municipality" id="from_municipality" class="form-control" >
                                    <option value="{{$schedule_from_municipality}}">{{$schedule_from_municipality}} = {{$schedule_from_municipality_balance}} bag(s)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Dropoff-Point:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="from_dop_name" id="from_dop_name" class="form-control" value="{{$shceduled_from_dop}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Total Bags:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="number" class="form-control" id="from_bags_total" name="from_bags_total" min="1" max="9999" value="{{$schedule_bagsForDelivery}}">
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
                                <select name="to_province" id="to_province" class="form-control" required>
                                    <option value="0">Please select a province</option>
                                    @foreach ($provinces as $to_province)
                                        @if($schedule_to_province == $to_province->province)
                                            <option value="{{$to_province->province}}" selected>{{$to_province->province}}</option>
                                        @else
                                            <option value="{{$to_province->province}}">{{$to_province->province}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_municipality_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Municipality:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <select name="to_municipality" id="to_municipality" class="form-control" required data-parsley-min="1">
                                    <option value="{{$schedule_to_municipality}}">{{$schedule_to_municipality}} = {{$schedule_to_municipality_balance}} bag(s)</option>
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_dop_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Dropoff-Point:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="to_dop_name" id="to_dop_name" class="form-control" value="{{$schedule_to_dop}}" required>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_deliveryDate_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Date of Delivery:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="date" name="to_delivery_date" id="to_delivery_date" class="form-control" value="{{$schedule_to_delivery_date}}" required>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_assignedPC_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Assigned PC</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" class="form-control" name="to_assigned_pc" id="to_assigned_pc" value="{{$schedule_to_assigned_pc}}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                        card_str = card_str + '<button role="button" type="button" style="color: #7387a8;text-decoration:none;margin-top: 4px;" class="btn btn-link">'+seed_variety+' - '+seed_total+' bag(s)</button>';
                        card_str = card_str + "<button role='button' type='button' onclick='removeTagFromList("+variety_id+", "+seed_total+")' class='btn btn-danger btn-xs pull-right' style='margin-top: 8px;margin-right: 10px;'><i class='fa fa-undo'></i> UNDO</button>";
                        card_str = card_str + '</h5>';
                        card_str = card_str + '</div>';
                        card_str = card_str + '</div>';
                        $("#seed_list_div").append(card_str);

                        $("#from_seed_variety").val("0").change();
                        $("#seed_variety_total").val("0");

                        var current_total = $("#from_bags_for_delivery").val();
                        var add_to_total = parseInt(current_total) + parseInt(seed_total);
                        
                        $("#from_bags_for_delivery").val(add_to_total);
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
                },
            });
        };

        $("#from_province").on("change", function(e){
            $("#from_municipality").empty().append("<option value='0'>Loading muicipalities please wait...</option>");
            $("#from_assigned_pc").empty().append("<option value='0'>Loading assigned PC(s)...</option>");
            var province = $(this).val();
            
            //load all municipalities
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#from_municipality").empty().append(data);
                }
            });
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
                    view: "ALL"
                },
                success: function(data){
                    $("#to_municipality").empty().append(data);
                }
            });
        });

        $("#save_as_draft_btn").on("click", function(e){
            $("#edit_draft_flag").val("1");
            $("#edit_final_flag").val("0");

            $("#edit_form").submit();
        });

        $("#save_as_final_btn").on("click", function(e){
            $("#edit_draft_flag").val("0");
            $("#edit_final_flag").val("1");

            $("#edit_form").submit();
        });
    </script>
@endpush
