<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
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

    .total_container{
        font-size: 30px;
        font-weight: bold;
    }


  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="row">
        {{-- <hr style="border-top: 2px solid #d6d3d3;"> --}}
         <div class="row">
            <button style="width:15vw; float:right;" class="btn btn-success form-control" id="export_excel"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                 
                   
            </div>
            <div class="row">

                


                <div class="col-md-3">
                    <select name="region" id="region" name="region" class="form-control">
                        <option value="0">Please select a region</option>
                        @foreach ($regions as $row)
                            <option value="{{$row->regionName}}">{{$row->regionName}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="province" id="province" name="province" class="form-control">
                        <option value="0">Please select a province</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-success form-control" id="filter_btn" disabled=""><i class="fa fa-filter"></i> FILTER DATA</button>
                </div>
            </div>

           

            <hr style="border-top: 2px solid #d6d3d3;">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        Yield Report Statistic
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <h2 align="center" id="stateOfData"></h2>
                <div class="x_content form-horizontal form-label-left">
                    <div id="top5_chart" style="width:100%;height:500px;"></div>
                </div>
            </div>
        </div>  



        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        Yield Report Data
                    </h2>

                    
                    <div class="clearfix"></div>
                </div>
                <h2 align="center" id="stateOfData"></h2>
                <div class="x_content form-horizontal form-label-left">
                    <table class="table table-hover table-striped table-bordered" id="stocks_tbl">
                        <thead>
                            <th >Province</th>
                            <th>Municipality</th>
                            <th>Total Production</th>
                            <th>Total Area</th>
                            <th>Yield</th>
                            
                        </thead>
                        <tbody id="tableBody">

                        </tbody>
                    </table>
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
    
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
       $("#stocks_tbl").DataTable();

       $('#filter_btn').click(function(){
        
        var season = "-";
        var level="";
        var name ="";
        var province = $('#province').val();
        var region = $('#region').val();

        if(season == "0" || region == "0"){
            alert("Select Season and Province");
        }else{

            if(province=="" || province==0){
            level="regional";
            var name = $('#region').val();      
            $('#stateOfData').text('Regional Data')
            

            
        }else{       
            level="province";
            $('#stateOfData').text('Provincial Data')     
           

             var name = $('#province').val();
        }
     

        pieData(season,level,name);
        loadData(season,level,name);
        }
       });
     

       $('#export_excel').click(function(){
        var season = $('#season').val();
			if(season=="0"){
				alert("Please Select Season");
			}else{
				window.open("export/"+season+"");
			}
		});
        
       function pieData(season,level,name){
        var URL_SITE = "{{url('/')}}";
      
        $.ajax({
            type: 'GET',
            url: URL_SITE+"/yield_ui/get_count/per/"+season+"/"+level+"/"+name+"",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                load_top5(data);
            }
       
        });
       }

        $("#region").on("change", function(e){
            var region = $("#region").val();
            $("#province").empty().append("<option value='0'>Loading provinces...</option>");
            $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('report.variety.provinces_data') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#province").empty().append("<option value='0'>Please select a province</option>");
                    $("#province").append(data);
                        $("#filter_btn").removeAttr("disabled");
                }
            });
        });

        $("#province").on("change", function(e){
            var region = $("#region").val();
            var province = $("#province").val();
            $("#municipality").empty().append("<option value='0'>Loading municipalities...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('report.variety.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region,
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");
                    $("#municipality").append(data);
                  
                }
            });
        });




        function load_top5(pie_data){            
                    
            Highcharts.chart('top5_chart', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                    margin: [0, 0, 0, 0],
                    spacingTop: 0,
                    spacingBottom: 0,
                    spacingLeft: 0,
                    spacingRight: 0
                },
                title: {
                    text: ''
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: '',
                    pointFormat: '{point.name}: {point.y}%'
                },
                plotOptions: {
                    pie: {
                        size:'100%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y}%',
                            distance: '-40%'
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: pie_data
                }]
            });
        }
      


        function loadData(season,level,name ){
		 $('#stocks_tbl').DataTable().clear();
                $("#stocks_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [ 1, "desc"],
                    "ajax": {
                        "url": "../yield_ui/get_count/perDataTable/"+season+"/"+level+"/"+name+"",
                        "dataType": "json",
                        "type": "get",
                        "data":{
                            _token: "{{ csrf_token() }}",
                        }
                    },
                    "columns":[
                        {"data": "province"},  
                        {"data": "municipality"},           
                        {"data":"total_production"}, 
                        {"data":"area"}, 
                        {"data":"municipality_yield"}, 





                    ]
                });
	}

    </script>
@endpush
