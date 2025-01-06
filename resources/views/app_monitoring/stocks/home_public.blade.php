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
                            <option value="2" selected>Please select a Status</option>
                            <option value="1">Released</option>
                            <option value="0">Unreleased</option>
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
                        <th style="width:80px;">Username</th>
                        <th style="width:120px;">Transaction Code</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Dropoff Point</th>
                        <th style="width:200px;">Seed Variety</th>
                        <th>Total Bags</th>
                        <th>Status</th>
                    </thead>
                </table>
            </div>
        </div><br>        

    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        load_tbl('no_province', 'no_municipality', 'no_status');
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
                "ajax": {
                    "url": "{{ route('distribution.app.stocks_tbl_public') }}",
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
                    {"data": "downloaded_by"},
                    {"data": "transaction_code"},
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "dop_name", searchable: false},
                    {"data": "seed_varieties", searchable: false},
                    {"data": "total_bags_str", searchable: false},
                    {"data": "status_name", searchable: false}
                ]
            });
        } 

        $("#province").on("change", function(e){
            var province = $("#province").val();
            $("#municipality").empty().append("<option value='0'>loading municipalities...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('distribution.app.get_stock_municipalities') }}",
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
    </script>
@endpush
