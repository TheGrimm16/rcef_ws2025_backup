@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
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
    .btn-secondary, .btn-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        cursor: auto;
        opacity: 0.8;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <!-- UPLOAD PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    {{-- Search Filter --}}
                    Open for Replacement
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-3">
                        <select name="region" id="region" class="form-control">
                            <option value="0" selected>Please select Region</option>
                            @foreach ($regions as $row)
                                <option value="{{$row->region}}">{{$row->region}}</option>    
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="province_select" id="province_select" class="form-control province_select">
                            <option value="0">Please select a Province</option>
                        </select>
                    </div> 
                    <div class="col-md-3">
                        <select name="municipality" id="municipality" class="form-control">
                            <option value="0">Please select a municipality</option>
                        </select>
                    </div>

                    

                    <div class="col-md-3">
                        <button class="btn btn-success btn-block" id="filter_btn"><i class="fa fa-database"></i> FILTER TABLE</button>
                    </div>
                </div>
                <br>
            </div>
        </div><br>
        <!-- UPLOAD PANEL -->


        <div class="x_panel">
            {{-- <div class="x_title">
                <h2>
                     
                </h2>
                <div class="clearfix"></div>
            </div> --}}
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="municipal_tbl">
                    <thead>
                        <th>Region</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th style="width:200px;">Open for Replacement</th> 
                    </thead>
                </table>
            </div>
        </div><br>        

    </div>


    <!-- IAR UPLOAD MODAL -->
    <div id="confirm_modal" class="modal fade " role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="confirmStock_modal_title">Open For Replacement</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="prvId_val" value="">

                    

                    <div class="form-group">
                        You are about to Open Municipality for Seed Repalcement. Please be reminded that by opening these area
                        the changes will be immediately reflect to the mobile app being used.<br><br>

                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <select name="repalcement_reason" id="repalcement_reason" class="form-control province_select">
                                <option value="0">Please select reason for Replacement</option>
                                    @foreach ($repalcement_reasons as $row)
                                        <option value="{{$row->id}}">{{$row->reason_name}}</option>    
                                    @endforeach
                            </select>
                        </div> 

                    </div>
                    <br>
                    <br>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-ban"></i> cancel</button>
                    <button type="button" class="btn btn-success" disabled id="btn_confirm"><i class="fa fa-check"></i> confirm</button>
                    
                </div>
            </div>
        </div>
    </div>
    <!-- IAR UPLOAD MODAL -->

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>


    loadList();

    function loadList(region, province, municipality){
            $('#municipal_tbl').DataTable().clear();
            $("#municipal_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('distribution.replacement.data') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        region: region,
                        province: province,
                        municipality: municipality
  
                    }
                },
                "columns":[
                    {"data": "region"},
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "action_btn"}
                ]
            });
        } 



        // load_tbl('no_province', 'no_municipality', 'no_status');
        function load_tbl(region, province, municipality){
            $('#municipal_tbl').DataTable().clear();
            $("#municipal_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('distribution.replacement.municipality_tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        region: region,
                        province: province,
                        municipality: municipality
  
                    }
                },
                "columns":[
                    {"data": "region"},
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "action_btn"}
                ]
            });
        } 

        $("#region").on("change", function(e){
            var region = $("#region").val();
            $("#province_select").empty().append("<option value='0'>Loading provinces...</option>");
            $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('distribution.replacement.provinces') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#province_select").empty().append("<option value='0'>Please select a province</option>");
                    $("#province_select").append(data);
                }
            });
        });

        $("#province_select").on("change", function(e){          
            var province = $("#province_select").val();
            $("#municipality").empty().append("<option value='0'>loading municipalities...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('distribution.replacement.get_municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Select a municipality</option>");
                    $("#municipality").append(data);
                }
            });
        });
        

        $("#filter_btn").on("click", function(e){
            $("#repalcement_reason").prop('selectedIndex', 0);
           var province = $("#province_select").val();
           var municipality = $("#municipality").val();
           var region = $("#region").val();

           if(region == "0" || province == "0"){
               alert("Please fill-up all the required parameters.");
           }else{
               //proceed to load table..
               load_tbl(region,province,municipality);
            
               
           }
        });

        $("#btn_confirm").on("click", function(e){ 
             HoldOn.open(holdon_options) 
            var prvId_val = $("#prvId_val").val();
            var replacement_reason = $("#repalcement_reason").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('distribution.replacement.add') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    prvId_val:prvId_val,
                    replacement_reason:replacement_reason 
                },
                success: function(data){
                    alert('Status successfully updated');
                    $('#confirm_modal').modal('toggle');
                    $("#filter_btn").trigger("click");
                    HoldOn.close()
                    
                }
            });
        }); 


            // OpenModal('request');
            function OpenModal(prvId, replace_for){
            var prvId = prvId;
            var replace_for = replace_for;
            // if(replace_for != 3){
            //     $('#prvId_val').hidden();
            // }
            
            $('#prvId_val').val(prvId);
            $('#replacement_reason').val(replace_for);
            $('#confirm_modal').modal('show');
            
        }


        $("#repalcement_reason").on("change", function(e){  
            var reason = $("#repalcement_reason").val();
            if(reason!=0){
                $('#btn_confirm').removeAttr("disabled");
            }else{
                $('#btn_confirm').attr('disabled');
            }
        });

        

       
    </script>
@endpush
