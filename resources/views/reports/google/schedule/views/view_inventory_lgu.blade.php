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

        <form action="{{route('rcep.google_sheet.editInventoryLgu')}}" method="POST" data-parsley-validate="" id="edit_form">
            {{ csrf_field() }}
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        @if($source == "INVENTORY_WS")
                            INVENTORY (WS2020) | LGU STOCKS
                        @elseif($source == "INVENTORY_DS")
                            INVENTORY (DS2020) | LGU STOCKS
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
                                    <option value="LGU_STOCKS">Stocks in LGU</option>
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
                                    <option value="{{$schedule_from_municipality}}">{{$schedule_from_municipality}}</option>
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
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Assigned PC</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="text" class="form-control" id="from_assigned_pc" name="from_assigned_pc" value="{{$shcedule_from_assigned_pc}}">
                            </div>
                        </div>

                        <div class="form-group" id="from_bagsRemaining_div">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Total Bags</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" >
                                <input type="number" class="form-control" id="from_bags_total" name="from_bags_total" min="1" max="9999" value="{{$schedule_bagsInLgu}}">
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
        $("#from_seed_coop").select2();
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
                    province: province,
                    view: "ALL"
                },
                success: function(data){
                    $("#from_municipality").empty().append(data);
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
