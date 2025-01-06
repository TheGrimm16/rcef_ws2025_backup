@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1;
        }

        .tile_count .tile_stats_count .count {
            font-size: 30px;
        }
        .tile_count .tile_stats_count .count {
        font-size: 30px;
    }

    #ws2021_block, #ds2021_block, #ws2020_block, #ds2020_block{
        background-color: red;
        color: white;
        cursor: pointer;
    }


    .fa-loader {
        -webkit-animation: spin 2s linear infinite;
        -moz-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }

    @-moz-keyframes spin {
        100% {
            -moz-transform: rotate(360deg);
        }
    }

    @-webkit-keyframes spin {
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    #myChart {
        height: 70vh !important;
		color:green !important;
    }

    .content-header h1 {
        border-bottom: 3px solid rgb(30, 155, 14);
        width: 20%;
        text-align: center;
        margin: 0 auto;
    }

    .margin-right-twenty {
        margin-right: 20px !important;
    }

    .show-calendar,
    .drp-calendar,
        {
        display: none !mportant;
    }
  </style>
@endsection

@section('content')

 <!-- UPLOAD PANEL -->
 <div class="x_panel">
    <div class="x_title">
        <h2>
            Search Filter
        </h2>
        <div class="clearfix"></div>
    </div>
    <div class="x_content form-horizontal form-label-left">
        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
        <div class="row">
            <div class="col-md-2">
                <select name="station" id="station" class="form-control">
                    <option value="0">Station</option>
                    @foreach ($stations as $row)
                        <option value="{{$row->stationId}}">{{$row->stationName}}</option>    
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="Encoder" id="Encoder" class="form-control">
                    <option value="0">RCEF Extension encoders</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="yearSelect" id="yearSelect" class="form-control">                    
                    <option value="">Year Encoded</option>
                    <option value="2020">2020</option> 
                    <option value="2021">2021</option>                   
                    <option value="2022">2022</option>                   
                </select>
            </div>
            <div class="col-md-2">
                <select name="monthSelect" id="monthSelect" class="form-control">                    
                    <option value="0">Select Month</option>
                    
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>


                </select>
            </div>
            <div class="col-md-2">
                <select name="weekSelect" id="weekSelect" class="form-control">
                    <option value="0">Select Week</option>
                </select>
            </div>

            <div class="col-md-1">
                <button class="btn btn-success btn-block" id="filter_btn_statistic" name="filter_btn_statistic"><i class="fa fa-filter"></i></button>
            </div>
            <div class="col-md-1">
                <button class="btn btn-success btn-block" id="filter_btn_statistic_export" name="filter_btn_statistic"><i class="fa fa-file-excel-o"></i></button>
            </div>
            
           {{--  <div class="col-md-3">
               
                    <button type="button" class="btn btn-success btn-block" id="filter_btn_pdf"><i class="fa fa-database"></i> Download PDF</button>
                               
            </div> --}}
        </div>
    </div>
</div><br>
<div class="row">
    <div class="col-md-3">
        <div class="x_panel" id="ws2021_block">
            <div class="x_title">
                <h2>WS2021 Crop Season</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row tile_count" style="margin: 0">
                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                        <div class="count" id="ws2021_status"><i class="fa fa-power-off"></i> -- DISCONNECTED</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="x_panel" id="ds2021_block">
            <div class="x_title">
                <h2>DS2021 Crop Season</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row tile_count" style="margin: 0">
                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                        <div class="count" id="ds2021_status"><i class="fa fa-power-off"></i> -- DISCONNECTED</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="x_panel" id="ws2020_block">
            <div class="x_title">
                <h2>WS2020 Crop Season</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row tile_count" style="margin: 0">
                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                        <div class="count" id="ws2020_status"><i class="fa fa-power-off"></i> -- DISCONNECTED</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="x_panel" id="ds2020_block">
            <div class="x_title">
                <h2>DS2020 Crop Season</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row tile_count" style="margin: 0">
                    <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                        <div class="count" id="ds2020_status"><i class="fa fa-power-off"></i> -- DISCONNECTED</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- UPLOAD PANEL -->

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><strong>RCEF Extension Encoder Statistics</strong></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
               
                <table class="table table-hover table-striped table-bordered" id="stocks_tbl">
                    <thead>
                        <th>Encoder</th>
                        <th>Total Encoded</th>
                    </thead>
                    <tbody id="tableBody">
                        
                    </tbody>
                </table>
            </div>
            </div>
            
        </div>
		
		
		<div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="box-body">
            <canvas id="myChart"></canvas>
        </div>
            </div>           
        </div>


        


        <input type="hidden" id="active_db" name="active_db" value="">
        <input type="hidden" id="connection_Status" name="connection_Status" value="">

@endsection()

@push('scripts')
   <script src="https://www.chartjs.org/dist/2.9.4/Chart.min.js"></script>

    <script>
	
	
	
/* chart */

var ctx = document.getElementById('myChart').getContext('2d');
    var json_url = "";

    var myChart = new Chart(ctx, {
        // The type of chart we want to create
        type: 'bar',
        // The data for our dataset
        data: {
            labels: [],
            datasets: [{
                    label: 'RCEF Extension Encoder Statistics',
                    backgroundColor: '#f39c12',
                    data: []
                }

            ]

        },
        // Configuration options go here
        options: {
            title: {
                display: true,
                text: []
            },
            tooltips: {
                mode: 'index',
                intersect: false
            },
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                xAxes: [{
                    stacked: true,
                }],
                yAxes: [{
                    stacked: true
                }]
            }
        }
    });
	
$("#stocks_tbl").DataTable();
        $('select[name="station"]').on('change', function () {
            HoldOn.open(holdon_options);
            var station = $(this).val();
            $('select[name="Encoder"]').empty();
            $.ajax({
                method: 'POST',
                url: 'encoderData',
                data: {                    
                    station: station
                },
                dataType: 'json',
                success: function (source) {
                  
                    $('select[name="Encoder"]').append('<option alue="">--RCEF Extension encoders--</option>');
					if(source !=""){
						$('select[name="Encoder"]').append('<option value="ALL">ALL</option>');
					}
                    
                    source.forEach(element => {
                        console.log(element.users);
                        $('select[name="Encoder"]').append('<option value="' + element.username + '">' + element.username + '</option>');
                    });
                  
                }
            });
            HoldOn.close();
        });

        $('select[name="monthSelect"]').on('change', function () {
            HoldOn.open(holdon_options);
        
            var year = $('#yearSelect').val();         
            if(year != ""){
                //var season = "ws2021";                               
                var month = $('#monthSelect').val();
                $('select[name="weekSelect"]').empty();
                $.ajax({
                    method: 'POST',
                    url: 'getWeek',
                    data: {                    
                        season: year,
                        month:month
                    },
                    dataType: 'json',
                    success: function (source) {
                    
                        $('select[name="weekSelect"]').append('<option alue="">--RCEF Extension encoders--</option>');
			    		if(source !=""){
			    			$('select[name="weekSelect"]').append('<option value="ALL">ALL</option>');
			    		}
                        for (let index = 1; index <= source; index++) {
                            $('select[name="weekSelect"]').append('<option value="Week '+index+ '">Week '+index +'</option>');                        
                        }                    
                    }
                });
            }else{

                alert("Please Select Year Encoded");
                $('#monthSelect').val("0");    
                
            }

            HoldOn.close();
        });
        $("#filter_btn_statistic").on('click', function () {            
            HoldOn.open(holdon_options);
            var station = $('#station').val();
            var Encoder = $('#Encoder').val();
            var season = $('#active_db').val();
            var month = $('#monthSelect').val();
            var week = $('#weekSelect').val();
            var year = $('#yearSelect').val();
            
			var connection_Status= $('#connection_Status').val();
            if(connection_Status == "connected"){
               loadData(station,Encoder,season,month,week,year);
			   search(myChart,station,Encoder,season,month,week,year);
            }else{
                alert("No Season Active");
            }
            

            HoldOn.close();
        });

        $("#filter_btn_statistic_export").on('click', function () {            
            HoldOn.open(holdon_options);
            var station = $('#station').val();
            var Encoder = $('#Encoder').val();
            var season = $('#active_db').val();
            var month = $('#monthSelect').val();
            var week = $('#weekSelect').val();
            var year = $('#yearSelect').val();
            


			var connection_Status=   $('#connection_Status').val();
            if(connection_Status == "connected"){
                console.log(station+"/"+Encoder+"/"+season+"/"+month+"/"+week+"/"+year);
               if(station !="" && Encoder !="" && season !="" && month !="" && week !="" && year !=""){
                window.open("Statistic-export/"+station+"/"+Encoder+"/"+season+"/"+month+"/"+week+"/"+year+"");
            }else{
                alert("Filter Data are not Valid");
            }
            }else{
                alert("No Season Active");
            }
            

            HoldOn.close();
        });

       
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
	
		function search(chart,station,Encoder,season,month,week,year) {

        chart.clear();



        var ranges = [];
        var count = [];
        var count = [];
        var total_jobs = 0;


        $.ajax({
             url: "{{ route('StatisticDatLoadChart') }}",
            method: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                   station: station,
                   username:Encoder,
                   seasonActive:season,
                   month:month,
                   week:week,
                   year:year
            },
            dataType: "json",
            beforeSend: function() {
                let datas = '';
            },
            success: function(response) {
				console.log(response);
                datas = response;      
                if (datas) {
                    $.each(datas, function(key, value) {
                        ranges.push(value.encoder);
                        count.push(value.totalData);
                        chart.data.labels = ranges;
                        chart.data.datasets[0].data = count;

                    });

                 
                    
                    chart.update();

                } else {
                    console.log('No data');
                }
            }
        });
    }
	
	function loadData(station,Encoder,season,month,week,year){
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
                        "url": "{{ route('StatisticDatLoad') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            station: station,
                            username:Encoder,
                            seasonActive:season,
                            month:month,
                            week:week,
                            year:year
                        }
                    },
                    "columns":[
                        {"data": "encoder"},                                      
                        {"data":"totalData"}, 

                    ]
                });
	}
    $("#ws2021_block").on("click", function(e){
	
            HoldOn.open(holdon_options);
            


             $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.connect.db')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    season: "ws2021",
                },
                dataType: 'json',
                success: function (source) {
                        
                    if(source['status'] === "1"){
                              $("#ws2021_block").css("background-color", "green");
                             $("#ws2021_status").empty().html("<i class='fa fa-power-off'></i> -- CONNECTED");
                             $("#active_db").empty().val(source["data"]["season"]);
                             //-disable other seasons
                            $("#ds2021_block").css("background-color", "red");
                            $("#ds2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                            $("#ws2020_block").css("background-color", "red");
                            $("#ws2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                            $("#ds2020_block").css("background-color", "red");
                            $("#ds2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                            
                            $("#connection_Status").empty().val("connected");
                            
							 var station = $('#station').val();
            				 var Encoder = $('#Encoder').val();
                             var season = $('#active_db').val();
                             var month = $('#monthSelect').val();
                             var week = $('#weekSelect').val();
							 if(station != "0" && Encoder != "" && season != ""){
							 	loadData(station,Encoder,season,month,week);
								search(myChart,station,Encoder,season,month,week);
							 }

                    }else{
                        alert("Connection failed");                    
                        $("#ws2021_block").css("background-color", "red");
                        $("#ws2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                        $("#connection_Status").empty().val("disconnected");
                    }   
                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
            }); //AJAX CONNECT
        });






        $("#ds2021_block").on("click", function(e){
			$('#stocks_tbl').DataTable().clear();
			   $('#stocks_tbl').DataTable();
             HoldOn.open(holdon_options);
             
             $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.connect.db')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    season: "ds2021",
                },
                dataType: 'json',
                success: function (source) {

                    if(source['status']==="1"){
                        $("#ds2021_block").css("background-color", "green");
                        $("#ds2021_status").empty().html("<i class='fa fa-power-off'></i> -- CONNECTED");
                        //-disable other seasons
                        $("#ws2021_block").css("background-color", "red");
                        $("#ws2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                        $("#ws2020_block").css("background-color", "red");
                        $("#ws2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                        $("#ds2020_block").css("background-color", "red");
                        $("#ds2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                        $("#connection_Status").empty().val("connected");
                        $("#active_db").empty().val(source["data"]["season"]);
						     var station = $('#station').val();
            				 var Encoder = $('#Encoder').val();
                             var season = $('#active_db').val();
                             var month = $('#monthSelect').val();
                             var week = $('#weekSelect').val();
							 if(station != "0" && Encoder != "" && season != ""){
							 	loadData(station,Encoder,season,month,week);
								search(myChart,station,Encoder,season,month,week);
							 }
                              
                    }else{
                         alert("Connection failed");                         
                         $("#ds2021_block").css("background-color", "red");
                        $("#ds2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                           $("#connection_Status").empty().val("disconnected");
                    }


                    

                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
            }); //AJAX CONNECT


        });

        $("#ws2020_block").on("click", function(e){
			$('#stocks_tbl').DataTable().clear();
			   $('#stocks_tbl').DataTable();
            HoldOn.open(holdon_options);
             
             $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.connect.db')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    season: "ws2020",
                },
                dataType: 'json',
                success: function (source) {

                    if(source['status'] === "1"){
                    //alert(source["data"]["info"]);
                    $("#ws2020_block").css("background-color", "green");
                    $("#ws2020_status").empty().html("<i class='fa fa-power-off'></i> -- CONNECTED");
                    //-disable other seasons
                    $("#ws2021_block").css("background-color", "red");
                    $("#ws2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                    $("#ds2021_block").css("background-color", "red");
                    $("#ds2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                    $("#ds2020_block").css("background-color", "red");
                    $("#ds2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
        
                    $("#active_db").empty().val(source["data"]["season"]);
                    $("#connection_Status").empty().val("connected");
					         var station = $('#station').val();
            				 var Encoder = $('#Encoder').val();
                             var season = $('#active_db').val();
                             var month = $('#monthSelect').val();
                             var week = $('#weekSelect').val();
							 if(station != "0" && Encoder != "" && season != ""){
							 	loadData(station,Encoder,season,month,week);
								search(myChart,station,Encoder,season,month,week);
							 }
                          
                    }
                    else{
                         alert("Connection failed");                       
                        $("#ws2020_block").css("background-color", "red");
                        $("#ws2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                           $("#connection_Status").empty().val("disconnected");
                    }
                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
            });

        });


        $("#ds2020_block").on("click", function(e){
			$('#stocks_tbl').DataTable().clear();
			   $('#stocks_tbl').DataTable();
            HoldOn.open(holdon_options);

             $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.connect.db')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    season: "ds2020",
                },
                dataType: 'json',
                success: function (source) {

                    if(source['status'] === "1"){
                    //alert(source["data"]["info"]);
                    $("#ds2020_block").css("background-color", "green");
                    $("#ds2020_status").empty().html("<i class='fa fa-power-off'></i> -- CONNECTED");
                    //-disable other seasons
                    $("#ws2021_block").css("background-color", "red");
                    $("#ws2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                    $("#ds2021_block").css("background-color", "red");
                    $("#ds2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                    $("#ws2020_block").css("background-color", "red");
                    $("#ws2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");    
                    $("#active_db").empty().val(source["data"]["season"]);
                            $("#connection_Status").empty().val("connected"); 
							 var station = $('#station').val();
            				 var Encoder = $('#Encoder').val();
                             var season = $('#active_db').val();
                             var month = $('#monthSelect').val();
                             var week = $('#weekSelect').val();
							 if(station != "0" && Encoder != "" && season != ""){
							 	loadData(station,Encoder,season,month,week);
								search(myChart,station,Encoder,season,month,week);
							 }
                             
                    }
                    else{
                         alert("Connection failed");                         
                         $("#ds2020_block").css("background-color", "red");
                         $("#ds2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                            $("#connection_Status").empty().val("disconnected");
                    }
                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
            });

        });
 
    </script>
@endpush
