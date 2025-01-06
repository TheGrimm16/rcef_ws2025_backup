@extends('layouts.index')


@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
                <h2>Delivery vs Allocation (Provincial Level)</h2>

    </div>
</div>
    

    <div class="x_content form-horizontal form-label-left">
            <div class="row">
                <div class="col-md-10">
                    <div id="container" style="width:100%; height:700px;"></div>
                </div>
                <div class="col-md-2">
                    <div class="row tile_count">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <span class="count_top">Total Allocation</span>
                            <div class="count"><i class="fa fa-cube" aria-hidden="true"  id="total_allocation">--</i></div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <span class="count_top">Total Delivery</span>
                            <div class="count"><i class="fa fa-truck" aria-hidden="true"  id="total_delivered">--</i></div>
                        </div>
                        
                    </div>
                    
                </div>
            </div>
    </div>

  <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                   Select Region & Province
                </h2>

                <select name="region_allocation" id="region_allocation" class="form-control">
                            <option value="0">Please select a Region</option>
                    @foreach($region as $region)
                        <option value="{{$region->region}}">{{$region->region}}</option>
                    @endforeach
                </select>
                        <br>
                <select name="province_allocation" id="province_allocation" class="form-control">
                            <option value="0">Please select a Province</option>
                </select>
            


                         <div class="clearfix"></div>
        </div>
</div>
        <br>
        <!-- FILTER PANEL -->

<!-- DATA TABLE -->

 <div class="col-md-12 col-sm-12 col-xs-12">
<?php
//dd($userID->userId);
?>
    <!-- distribution details -->
        <div class="x_panel">

        <div class="x_content form-horizontal form-label-left">
            
            <table class="table table-hover table-striped table-bordered" id="tblAllocation">
                <thead>
                  <th>Region</th>
                  <th>Province</th>
                  <th>Allocation</th>
                  <th>Delivery</th>
                  <th>Difference</th>
                  <th>Percentage Completed</th>
                   
                </thead>
                <tbody>
                    
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

 


@endsection
@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script type="text/javascript">

    function loadChart() {
        HoldOn.open(holdon_options);
        var province = $('select[name="province_allocation"]').val();
        var region = $('select[name="region_allocation"]').val();
            if(province === "0"){
                province = "all";
            }


        var level = "provincial";
         $.ajax({
                    type: 'POST',
                    url: "{{route('delivery.allocation.chart')}}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                        region: region,
                        municipality: "all",
                        level: level,
                    },
                    success: function(data){
                        populateChart(data['x_list'],data['allocation_list'],data['delivered_list']);
                        //$("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        $("#total_allocation").empty().html(data["total_allocation"]);
                        $("#total_delivered").empty().html(data["total_delivered"]);
                        HoldOn.close();
                    }
                }); 
    }


    function populateChart(x_list, allocation, delivery){
         
                    $('#container').highcharts({
                        chart: {
                            type: 'bar'
                        },
                        title:{
                            text:''
                        },
                          xAxis: {
                                categories: x_list,
                                title: {
                                    text: null
                                }
                            },
                        yAxis: {
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            bar: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    inside: true
                                },
                                showInLegend: true
                            }
                        },
                        series:[
                        {
                            name: "Allocation",
                            data: allocation,
                            color: "#A4A4A4"
                        },{
                            name: "Delivered",
                            data: delivery,
                            color: "#81F781"
                        }
                        ]
                    });
    }




    function loadTBl(){
        var province = $('select[name="province_allocation"]').val();
        var municipality ="all";
        var region =  $('select[name="region_allocation"]').val();
        var level = "provincial";
        //alert(province+" "+municipality);
            $('#tblAllocation').DataTable().clear();
            $('#tblAllocation').DataTable({
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
                    "url": "{{route('delivery.allocation.gentable')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        region: region,
                        province: province,
                        municipality: municipality,
                        level: level
                    }
                },
                "columns":[
                    {"data": "region"},
                     {"data": "province"},
                    {"data": "allocated_bags", 'searchable': false, 'orderable': false},
                    {"data": "total_delivered", 'searchable': false, 'orderable': false},       
                    {"data": "difference", 'searchable': false, 'orderable': false},       
                     {"data": "percentage", 'searchable': false, 'orderable': false},       
                      
            ]});

    }



        $("#tblAllocation").DataTable({
            "order": [],
            "pageLength": 25
        });


        $('select[name="region_allocation"]').on('change', function () {
            HoldOn.open(holdon_options);        
        var region = $('select[name="region_allocation"]').val();
            $.ajax({
                method: 'POST',
                url: "{{route('delivery.allocation.provincial')}}",
                data: {
                    _token: _token,
                    region: region
                },
                dataType: 'json',
                success: function (source) {
                    $('select[name="province_allocation"]').empty().append('<option value="0">Please select a Municipality</option>');
                $.each(source, function (i, d) {
                    if(i == 0){
                        $('select[name="province_allocation"]').empty().append('<option value="all">--ALL PROVINCES--</option>');
                    }

                    $('select[name="province_allocation"]').append('<option value="' + d.province + '">' + d.province + '</option>');
                });
     
                loadTBl();
                loadChart();
                }
            }); //AJAX GET MUNICIPALITY

        HoldOn.close();
    });  //END PROVINCE SELECT


$('select[name="province_allocation"]').on('change', function () {
    HoldOn.open(holdon_options);
        loadTBl();
        loadChart();
    HoldOn.close();
});  //END MUNICIPALITY SELECT


    loadChart();
    </script>

@endpush