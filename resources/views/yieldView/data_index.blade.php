@extends('layouts.index')


@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        #category_text{
            font-size: 15px;
        }

        .tile_count .tile_stats_count:before {
        content: "";
        position: absolute;
        left: 0;
        height: 65px;
        border-left: 0;
        margin-top: 10px;
    }
  </style>
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">



<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Yield Information</h2>
                 
                <div class="clearfix"></div>
            </div>
            
            <div id="graph_container" class="col-md-9">
            </div>

            <div id="statistics" class="row col-md-3">
                <div class="x_panel">
                <div class="x_title">
                    <h2>Total Yield</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="count" id="total_yield"></div>
                        </div>
                    </div>
                </div>
            </div>
            </div>



        </div>
    </div>
</div>


  <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                   Filter Region and Province
                </h2>

                <select name="region_yield" id="region_yield" class="form-control">
                            <option value="0">Please select a Region</option>
                            @foreach($region as $region)
                                <option value="{{$region->region}}">{{$region->region}}</option>

                            @endforeach
                </select>
                        <br>
                <select name="province_yield" id="province_yield" class="form-control">
                            <option value="0">Please select a Municipality</option>
                </select>

                         <div class="clearfix"></div>
        </div>
</div>
        <br>
        <!-- FILTER PANEL -->




<!-- DATA TABLE -->

 <div class="col-md-12 col-sm-12 col-xs-12" >

    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
              Yield Information &nbsp; &nbsp;
            </h2>
            <button class="btn btn-success btn-sm" style="float:right; display: none;" id="export_excel">
               <i class="fa fa-file-excel-o" aria-hidden="true"></i> Export List to Excel
            </button>


            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th >Province</th>
                    <th >Municipality</th>
                    <th >Yield</th>
                    <th style="width: 100px;">Action</th>
                     
                   
                </thead>
                <tbody id='databody'>
                    
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

 


 








<!-- CURRENT HISTORY MODAL -->
<div id="history_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Update History List</span><br>
                     
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">User: </label>
                <label >
                    <select onchange="load_history_table($('#farmer_id').val());" class="form-control" id="user_name" name="user_name">
                        <option value="all">All User</option>
                    </select>
                </label> <br>
                <label for="" class="col-xs-3">Date From: </label>
                <label ><input onchange="load_history_table($('#farmer_id').val());" type="date" id="date_from" id="date_from"></label> <br>
                <label for="" class="col-xs-3">Date To: </label>
                <label ><input type="date" onchange="load_history_table($('#farmer_id').val());" id="date_to" id="date_to"></label> <br>
                <input type="hidden" id="farmer_id" name="farmer_id" value="%">

                <label for="" class="col-xs-3">Province: </label>
                <label id="modal_province"></label> <br>
                              
        <div class="form-group">

                <div>
                    
                     <table class="table table-hover table-striped table-bordered" id="history_table">
                        <thead >
                            <tr id="head_transfer_seedtag">
                                <th >Rsbsa #</th>
                                <th>Name</th>
                                <th>Field Updated</th>
                                <th>Update Info.</th>
                                <th>Date Updated</th>
                                <th>Author</th>
                                <th>Category</th>
                                
                            </tr>
                        </thead>
                        <tbody id="history_table_body">
                        </tbody>
                    </table>
                </div>
        </div>  
                
            </div>
            <div class="modal-footer">    
            <button class="btn btn-success btn-sm" style="float:right;" id="export_excel_history">
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i> Export to Excel
            </button>  
            </div>
        </div>
    </div>
</div>
<!-- CURRENT HISTORY MODAL END -->






@endsection
@push('scripts')
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>


    <script type="text/javascript">
chart();
        function chart(){

            var region = $('select[name="region_yield"]').val();
            var province = $('select[name="province_yield"]').val();

            $.ajax({
                method: 'POST',
                url: "{{route('data.yield.chart')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province
                },
                dataType: 'json',
                success: function (data) {
                    if(data['total'] <= 0){
                         $('#graph_container').empty().append("NO DATA");
                    }else{
                        load_chart(data['data_name'], data['data_value']);
                    }

                    $("#total_yield").empty().html(data['total']);
                    
                }
            }); //AJAX GET MUNICIPALITY
        }





          function load_chart(data_name, data_yield){

           $('#graph_container').highcharts({
                chart: {
                    type: 'bar'
                },
                title:{
                    text:''
                },
                xAxis: {
                    categories: data_name,

                },
                 plotOptions: {
                    series: {
                        dataLabels: {
                            enabled: true,
                            inside: true,
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                series: [{name: 'Yield',
                        data: data_yield,
                        
                    }]
            });
        }

    </script>






    <script type="text/javascript">
        <?php 
            $server = $_SERVER['SERVER_NAME'];
            $web_base = basename(getcwd());
            $link = "http://".$server."/".$web_base; 
        ?>

        function excel_download(province,municipality){
              window.open("{{$link}}"+"/data/yield/export/excel/"+province+"/"+municipality, "_blank");               
        }


        $('select[name="region_yield"]').on('change', function () {
            var region = $('select[name="region_yield"]').val();
            HoldOn.open(holdon_options); 
                 $.ajax({
                method: 'POST',
                url: "{{route('data.yield.province')}}",
                data: {
                    _token: _token,
                    region: region
                },
                dataType: 'json',
                success: function (source) {
                    $('select[name="province_yield"]').empty().append('<option value="0">Please select a Province</option>');
                $.each(source, function (i, d) {
                    if(i == 0){
                        $('select[name="province_yield"]').empty().append('<option value="all">--ALL PROVINCES--</option>');
                    }

                    $('select[name="province_yield"]').append('<option value="' + d.province + '">' + d.province + '</option>');
                });
                    load_table(); 
                    chart();
                }
            }); //AJAX GET MUNICIPALITY
            HoldOn.close();
        });


        $('select[name="province_yield"]').on('change', function () {
            HoldOn.open(holdon_options); 
                load_table();      
                chart();
            HoldOn.close();
        });


        function load_table(){
            var province = $('select[name="province_yield"]').val();
            var region = $('select[name="region_yield"]').val();

            $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": true,
                "searchHighlight": true,
                "searching": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{route('data.yield.table')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        region: region
                    }   
                },
                "columns":[
                    {"data": "province"},
                     {"data": "municipality"},
                    {"data": "yield", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false, 'orderable': false},     
            ]});


        }








    </script>

@endpush