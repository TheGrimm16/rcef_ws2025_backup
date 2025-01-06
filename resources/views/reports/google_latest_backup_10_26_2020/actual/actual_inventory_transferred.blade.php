@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12" style="height: 1200px;">

        <form action="{{route('rcep.google_sheet.actualInventoryTransferred')}}" method="POST" data-parsley-validate="" id="actual_form">
            {{ csrf_field() }}
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        @if($source == "INVENTORY_WS")
                            INVENTORY (WS2020) | SEED COOPERATIVE
                        @elseif($source == "INVENTORY_DS")
                            INVENTORY (DS2020) | SEED COOPERATIVE
                        @elseif($source == "NEW")
                            NEW SEEDS (DS2021) | SEED COOPERATIVE
                        @endif
                    </h2>

                    <button role="button" type="button" id="submit_actual_btn" class="btn btn-danger pull-right" style="border-radius:20px;"><i class="fa fa-send"></i> SUBMIT ACTUAL</button>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <input type="hidden" id="transaction_code" name="transaction_code" value="{{$schedule_transaction_code}}">

                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Title</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" class="form-control" name="title_transaction" id="title_transaction" value="{{$schedule_title}}" required disabled>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Type</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="seed_type" id="seed_type" class="form-control" required disabled>
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

                                    @if($source == "NEW")
                                        <option value="NEW" selected>New Seeds (DS2021)</option>
                                    @else
                                        <option value="NEW">New Seeds (DS2021)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Source</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="source" id="source" class="form-control" required disabled>
                                    <option value="TRANSFERRED_SEEDS" selected>Transferred Seeds</option>
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Status</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="status" id="status" class="form-control" required disabled>
                                    <option value="APPROVED" selected>Approved</option>
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

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Bags for Transfer:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="number" class="form-control" id="from_bags_for_transfer" name="from_bags_for_transfer" min="1" max="9999" value="{{$schedule_bagsForTransfer}}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
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
                                        <input type="number" id="seed_variety_total" name="seed_variety_total" class="form-control" min="0" max="9999" value="0">
                                    </div>
                                </div>
                                <button role="button" type="button" class="btn btn-success" id="seed_variety_add_btn" style="border-radius: 20px;margin-top:5px;"><i class="fa fa-shopping-cart"></i> ADD TO SEED VARIETY LIST</button>
        
                                <br><br>
                                <input type="hidden" name="seed_variety_str" id="seed_variety_str" class="form-control" value="{{$schedule_seed_str}}">
        
                                <div id="seed_list_con">
                                    <h4><strong><u>SEED VARIETY LIST</u></strong></h4>
                                    <div id="seed_list_div">
                                        @foreach ($schedule_seedList as $seed_row)
                                            <div class="card" id="card_{{$seed_row['seed_id']}}_id">
                                                <div class="card-header" id="headingOne" style="height: 38px;">
                                                    <h5 class="mb-0" style="margin:0">
                                                        <button role="button" type="button" style="color: #7387a8;text-decoration:none;margin-top: 4px;" class="btn btn-link">{{$seed_row['seed_variety']}} - {{$seed_row['seed_volume']}} bag(s)</button>
                                                        <button role="button" type="button" onclick='removeTagFromList({{$seed_row['seed_id']}}, {{$seed_row['seed_volume']}})' class='btn btn-danger btn-xs pull-right' style='margin-top: 8px;margin-right: 10px;'><i class='fa fa-undo'></i> UNDO</button>
                                                    </h5>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Province:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="from_province" id="from_province" class="form-control" value="{{$schedule_from_province}}" readonly>
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Municipality:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="from_municipality" id="from_municipality" class="form-control" value="{{$schedule_from_municipality}}" readonly>
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Dropoff-Point:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="from_dop_name" id="from_dop_name" class="form-control" value="{{$shcedule_from_dop}}" required>
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
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Province:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="to_province" id="to_province" class="form-control" value="{{$schedule_to_province}}" readonly>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_municipality_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Municipality:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="to_municipality" id="to_municipality" class="form-control" value="{{$schedule_to_municipality}}" readonly>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_dop_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Dropoff-Point:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" name="to_dop_name" id="to_dop_name" class="form-control" value="{{$schedule_to_dop}}" required>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_deliveryDate_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Date of Transfer:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="date" name="to_delivery_date" id="to_delivery_date" class="form-control" value="{{$schedule_to_transfer_date}}" required>
                            </div>
                        </div>
        
                        <div class="form-group" id="to_assignedPC_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Assigned PC</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" class="form-control" name="to_assigned_pc" id="to_assigned_pc" value="{{$schedule_to_assigned_pc}}" required readonly>
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
        $("#date_range").daterangepicker(null,function(a,b,c){
            //console.log(a.toISOString(),b.toISOString(),c)
        });

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
                    province: province
                },
                success: function(data){
                    $("#to_municipality").empty().append(data);
                }
            });
        });

        $("#submit_actual_btn").on("click", function(e){
            $("#actual_form").submit();
        });
    </script>
@endpush
