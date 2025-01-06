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
                    Search Filter
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-3">
                        <select name="province" id="province" class="form-control">
                            <option value="0">Please select a Province</option>
                            @foreach ($provinces as $row)
                                <option value="{{$row->province}}">{{$row->province}}</option>    
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <select name="municipality" id="municipality" class="form-control">
                            <option value="0">Please select a municipality</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status_code" id="status_code" class="form-control">
                            <option value="1">Buffer Seeds</option>
                            <option value="0">For Distribution</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-success btn-block" id="filter_btn"><i class="fa fa-database"></i> FILTER TABLE</button>
                    </div>
                </div>
            </div>
        </div><br>
        <!-- UPLOAD PANEL -->


        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Stocks Summary 
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="stocks_tbl">
                    <thead>
                       <!-- <th style="width:80px;">Username</th> -->
                        <th style="width:120px;">Transaction Code</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Dropoff Point</th>
                        <th style="width:200px;">Seed Variety</th>
                        <th>Total Bags</th>
                        <th>Status</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div><br>        

    </div>


    <!-- IAR UPLOAD MODAL -->
    <div id="change_stocks_modal" class="modal fade " role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="confirmStock_modal_title"></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="form_transaction_code" value="">
                    <input type="hidden" id="form_isbuffer" value="">
                    <div class="form-group" id="info">
                    </div>
                    <label for="" class="col-xs-3">Province: </label>
                    <label id="modal_province"></label> <br>
                    <label for="" class="col-xs-3">Municipality: </label>
                    <label id="modal_municipality"></label> <br>
                    <label for="" class="col-xs-3">Drop off Point: </label>
                    <label id="modal_dop"></label> <br>
                    <label for="" class="col-xs-3">Seeds: </label>
                    <label id="modal_seed"></label> <br>









                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="change_stock_btn"><i class="fa fa-exchange" id="modal_btn"></i></button>
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

        $("#stocks_tbl").DataTable({
            "order": [],
            "pageLength": 25
        });


        load_tbl('0', '0', '1');
        function load_tbl(province, municipality, status){
            $('#stocks_tbl').DataTable().clear();
            $("#stocks_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('distribution.app.stocks_actual_tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        status: status
                    }
                },
                "columns":[
                    //{"data": "downloaded_by"},
                    {"data": "transaction_code"},
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "dop_name", searchable: false},
                    {"data": "seed_varieties", searchable: false},
                    {"data": "total_bags_str", searchable: false},
                    {"data": "status_name", searchable: false},
                    {"data": "action", searchable: false}
                ]
            });
        } 

        $("#province").on("change", function(e){
            var province = $("#province").val();
            $("#municipality").empty().append("<option value='0'>loading municipalities...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('distribution.app.get_actual_municipalities') }}",
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
           var province = $("#province").val();
           var municipality = $("#municipality").val();
           var status = $("#status_code").val();

           if(province == "0" || municipality == "0" || status == "2"){
               alert("Please fill-up all the required parameters.");
           }else{
               //proceed to load table..
               load_tbl(province, municipality, status);
           }
        });



        $('#change_stocks_modal').on('show.bs.modal', function (e) {
        var batchticketnumber = $(e.relatedTarget).data('batchticketnumber');
        var user = $(e.relatedTarget).data('user');
        var isbuffer = $(e.relatedTarget).data('isbuffer');
        var province = $(e.relatedTarget).data('province');
        var municipality = $(e.relatedTarget).data('municipality');
        var dop = $(e.relatedTarget).data('dop');
        var seeddata = $(e.relatedTarget).data('seeddata');

       // alert(seeddata);
            if(isbuffer == "0"){ 
                var proc= "For Distribution into Buffer Seeds";
                var btn = " Change to Buffer Seeds";      
                var newisbuffer = 1;
                          }
            else {
                var proc = "Buffer Seeds into For Distribution";
                var btn = " Change to For Distribution";
                var newisbuffer = 0;
            }


            var info = "You are about to change seed type of the selected transaction, from <b>" + proc +"</b> please be reminded that by changing the seed type will be immediately reflected to the mobile device being used. do you want to proceed?";

             $("#info").empty().html(info);
             $("#modal_province").empty().html(province);
             $("#modal_municipality").empty().html(municipality);
             $("#modal_dop").empty().html(dop);
             $("#modal_seed").empty().html(seeddata);
             $("#modal_btn").empty().html(btn);
             $("#form_transaction_code").val(batchticketnumber);
             $("#form_isbuffer").val(newisbuffer);
             
            $("#confirmStock_modal_title").empty().html("Confirm Change of Seed Type of <b>"+batchticketnumber+"</b>");
        });






        $("#change_stock_btn").on("click", function(e){
            var transaction_code = $("#form_transaction_code").val();
            var isbuffer = $("#form_isbuffer").val();
               var province =  $("#modal_province").text();
                 var municipality =  $("#modal_municipality").text();
            
            if(transaction_code != "" && isbuffer != ""){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('distribution.app.confirm_changeOfStockType') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        transaction_code: transaction_code,
                        isbuffer: isbuffer,
                    },
                    success: function(data){

                        if(data == "change_stock_type_success"){
                            $("#change_stocks_modal").modal("hide");
                            
                            load_tbl(province,municipality, isbuffer);
                        }else{
                            alert('There was an error during the execution of this action. please refresh & try again.');
                        }
                    }, error: function(data){
                        alert('There was an error during the execution of this action. please refresh & try again.');
                    }
                });
            
            }else{
                alert("Data Missing: Please refresh and try again.");
            }
        })
    </script>
@endpush
