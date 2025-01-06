@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}

<meta name='viewport' content='width=device-width, initial-scale=1' />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v2.5.1/mapbox-gl.js'></script>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.5.1/mapbox-gl.css' rel='stylesheet' />


<input type="hidden" name="_token" value="{{ csrf_token() }}">

<style>

          #map_gad {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 98%;
          }

         

    

    .dash_div{
        padding-top:0px; margin-bottom:0px; font-size: 18px;
    }
    .dash_div_data{
        padding-top:0px; margin-bottom:0px; font-size: 18px; font-weight:bold;
    }


    #stacked_data {
    height: 400px;
    }

    #bar_land {
    height: 400px;
    }

.highcharts-figure,
.highcharts-data-table table {
    min-width: 310px;
    max-width: 800px;
    margin: 1em auto;
}

.highcharts-data-table table {
    font-family: Verdana, sans-serif;
    border-collapse: collapse;
    border: 1px solid #ebebeb;
    margin: 10px auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
}

.highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: #555;
}

.highcharts-data-table th {
    font-weight: 600;
    padding: 0.5em;
}

.highcharts-data-table td,
.highcharts-data-table th,
.highcharts-data-table caption {
    padding: 0.5em;
}

.highcharts-data-table thead tr,
.highcharts-data-table tr:nth-child(even) {
    background: #f8f8f8;
}

.highcharts-data-table tr:hover {
    background: #f1f7ff;
}


</style>

<div class="page-title">
    <div class="title_left">
        <h3> GAD Dashboard </h3>
    </div>
</div>

<div class="clearfix"></div>


<div class="row">
 
        <div class='col-md-6'>
           
                <div class="card">
                    <div class="col-md-12" style=" left-margin:2px; box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);">
                       
                        
                        <div class="col-md-9">
                            <div class="col-md-12" style="font-size:29px; font-weight:bold;">
                                 MALE
                            </div>



                            <div class="dash_div col-md-6" >
                                Population: 
                            </div>
                            <div class="dash_div_data col-md-6">
                                  {{number_format($dash_data->total_male)}} ({{number_format($dash_data->percent_male, 2)}}%) 
                            </div>
                        
                            <div class="dash_div col-md-6">
                               Est. Area Planted (ha):
                            </div>
                            <div class="dash_div_data col-md-6">
                                    {{number_format($dash_data->claimed_male)}}
                            </div>

                            <div class="dash_div col-md-6">
                                 Average landholding Area (ha):
                            </div>
                            <div class="dash_div_data col-md-6">
                                    {{-- uncomment me --}}
                                    {{-- {{number_format($dash_data->claimed_male / $dash_data->total_male, 2) }} --}}
                                    
                            </div>



                        </div>

                        <div class="col-md-3" >
                            <div class="col-md-12" style="text-align: right; margin:5px;">
                                <img src="{{asset('public/images/male_symbol.png')}}" style="width:130px; height:145px;">
                            </div>

                        </div>
                       
                    </div>
                    
                </div>
       
        </div>
  
        <div class='col-md-6' style=" left-margin:2px; ">
       
                <div class="card">
                    <div class="col-md-12" style="box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);">
                             
                        <div class="col-md-9">
                            <div class="col-md-12" style="font-size:29px; font-weight:bold;">
                                 FEMALE
                            </div>
                            <div class="dash_div col-md-6" >
                                Population: 
                            </div>
                            <div class="dash_div_data col-md-6">
                                  {{number_format($dash_data->total_female)}} ({{number_format($dash_data->percent_female, 2)}}%) 
                            </div>
                        
                            <div class="dash_div col-md-6">
                               Est. Area Planted (ha):
                            </div>
                            <div class="dash_div_data col-md-6">
                                    {{number_format($dash_data->claimed_female)}}
                            </div>

                            <div class="dash_div col-md-6">
                                 Average landholding Area (ha):
                            </div>
                            <div class="dash_div_data col-md-6">
                                    {{-- uncomment me --}}
                                    {{-- {{number_format($dash_data->claimed_female / $dash_data->total_female, 2) }} --}}
                            </div>



                        </div>

                        <div class="col-md-3" >
                            <div class="col-md-12" style="text-align: right; margin:5px;">
                                <img src="{{asset('public/images/female_symbol.png')}}" style="width:130px; height:145px;">
                            </div>

                        </div>
                    </div>
                    
                </div>
        
        </div>

</div>


<div class="row">
   
        <div class="col-md-6" style=" box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);">
            <div class="card">
            <div class="col-md-12"  style="font-size:21px; font-weight:bold;">
                @php
                    $season = $GLOBALS['season_prefix'];
                    $code = substr($season, 0, 2);
                    $year = substr($season, 2, 4);
                    $code = strtoupper($code);
                    echo "{$year} {$code}";
                @endphp
                
                by sex & age group
            </div>
            <div class="col-md-12" >
                    <div id="stacked_data" name="stacked_data"> </div>
            </div>
    
            </div>
        </div>
    
    
        <div class="col-md-6" style=" box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);">
            <div class="card">
            <div class="col-md-12"  style="font-size:21px; font-weight:bold;">
                @php
                    echo "{$year} {$code}";
                @endphp 
                
                avg. landholding, by sex & age group
            </div>
            <div class="col-md-12" id="bar_land" name="bar_land" >
    
            </div>
    
            </div>
        </div>


</div>





<div class="row" >
    <div class="card"> 
   

        <div class="col-md-12" style=" box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);">
            <button class="btn btn-success btn-sm" id="export_excel" name="export_excel" style="float:right; margin-top:5px;"  >
                <i class="fa fa-file-excel-o" aria-hidden="true"></i>  Export Excel
            </button>
            
            <button class="btn btn-success btn-sm" data-toggle='modal' data-target='#modal_gad_list' style="float:right; margin-top:5px;"  >
                <i class="fa fa-folder-open-o" aria-hidden="true"></i>      Open Saved Excel
                        </button>
                    <table class="table table-hover table-striped table-bordered" id="dataTBL" width="100%">
                
                    <thead>
                        <th  style=" text-align:center;width:250px; background-color:#4F6228; color:white;">Seed Variety</th>
                        <th  style=" text-align:center;width:150px; background-color:#4F6228; color:white;">Bags</th>
                        <th  style=" text-align:center;width:50px; background-color:#4F6228; color:white;">%</th>
                        <th  style=" text-align:center;width:150px;background-color:#16365C; color:white;">Bags <br> Male</th>
                        <th  style=" text-align:center;width:50px;background-color:#16365C; color:white;">% <br>Male</th>
                        <th  style=" text-align:center;width:150px;background-color:#16365C; color:white;">Bags <br>Female</th>
                        <th  style=" text-align:center;width:50px;background-color:#16365C; color:white;">% <br>Female</th>
                        <th style=" text-align:center;width:10px; border:none;"></th>
                        <th  style=" text-align:center;width:150px;background-color:#FFC000; color:black;">Bags <br>18-29</th>
                        <th  style=" text-align:center;width:50px;background-color:#FFC000; color:black;">% <br>18-29</th>
                        <th  style=" text-align:center;width:150px;background-color:#FFC000; color:black;">Bags <br>30-59</th>
                        <th  style=" text-align:center;width:50px;background-color:#FFC000; color:black;">% <br>30-59</th>
                        <th  style=" text-align:center;width:150px;background-color:#FFC000;  color:black;">Bags <br>60 up</th>
                        <th  style=" text-align:center;width:50px;background-color:#FFC000;  color:black;">% <br>60 up</th>
                        


                        
                    </thead>
                
                    
                    <tbody id='databody'>
                    </tbody>
                </table>

        </div>  
    </div>
</div>




<!-- CURRENT RLA MODAL -->
<div id="modal_gad_list" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 40%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Select downloaded GAD Report</span><br>
                </h4>
            </div>
            <div class="modal-body">

        <div class="form-group">

                <div style="overflow: auto;">
                    <button class="btn btn-success btn-sm"  style="float:right; margin:10px;"  id="store_excel" name="store_excel" >
                        <i class="fa fa-floppy-o" aria-hidden="true"></i>  Store Excel
                    </button>
                    
                     <table style="width: 100%;" class="table table-hover table-striped table-bordered" id="stocks_tbl">
                        <thead >
                            <tr id="head_transfer_seedtag">
                                <th>Date</th>
                                <th>Total Male</th>
                                <th>Total Female</th>
                                <th>Action </th>
                            </tr>
                        </thead>
                        <tbody id="tbl_transfer_seedtag">
                        </tbody>

                    </table>
                </div>
        </div>  
                
            </div>
      
        </div>
    </div>
</div>
<!-- CURRENT RLA MODAL END -->



<div class="row">

    <div class="col-md-12">
   
        <div class="col-md-6">
            <div id="graph_variety_sex" style="width: auto;  box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);" ></div>
        </div>


  
        <div class="col-md-6">
            <div id="graph_variety_group" style="width: auto;  box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07); " ></div>
        </div>
   
    </div>
    
</div>


<div class="row" style="margin-top:10px;">

    <div class="col-md-12">
        
        <!-- <div class="col-md-8" style="padding-left:10px;">
            <div style="width: auto;height:695px;  box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07);" >
                 
                        <table border=1 width="100%">
                            <tr><td style="width:90%;">
                            <div id="map_gad" style="border:solid;" >
                            </div>

                            </td></tr>
                        </table>    
                    
                   
                

            
            </div>
        </div> -->


  
        <div class="col-md-12" style="display: none">
            <div  style="width: auto;  box-shadow: 0 1px 2px rgba(0,0,0,0.07), 
                0 2px 4px rgba(0,0,0,0.07), 
                0 4px 8px rgba(0,0,0,0.07), 
                0 8px 16px rgba(0,0,0,0.07),
                0 16px 32px rgba(0,0,0,0.07), 
                0 32px 64px rgba(0,0,0,0.07); " >
                
            <div id="map_nav" style="padding-left:10px;">
                    <div class="row">
                        <div class="col-md-12" style="font-size:21px; font-weight:bold;">
                            Filters & Results
                        </div>
                    </div>

                    <div class="row" >
                            <div class="col-md-12">
                                <center>
                                <select name="map_region" id="map_region" class="form-control" style="width:20vw" onchange="map_region();male_female_percent();">
                                    <option value="0">Select Region</option>
                                    @foreach($region as $reg)
                                        <option value="{{$reg->regionName}}"> {{$reg->regionName}}</option>
                                    @endforeach
                                </select>
                                </center>
                            </div>
                    </div>

                    <div class="row" >
                            <div class="col-md-12" >
                                <center>
                                <select name="map_province" id="map_province" class="form-control" style="width:20vw" onchange="male_female_percent();">
                                    <option value="0">Select Province</option>
                                </select>
                                </center>

                            </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                                <div class="col-md-12" >
                                    <table  width="100%">
                                        <tr>
                                            <td align="right">
                                           
                                            <div id="male_percent" style='border:solid;margin-right:5px; height:123px; width:144px; background-color:#00E0FF; opacity:0.35; text-align:center; '><font size='5' color='black'> MALE </font> 

                                            <br> <font size='8' color='black'> {{number_format($dash_data->percent_male)}}%</font>
                                        
                                        </div>
                                           
                                        </td>
                                            <td> <div id="female_percent" style='border:solid;height:123px; width:144px;background-color:#FF0000; opacity:0.43; text-align:center;'><font size='5' color='black'> FEMALE </font> 
                                            <br> <font size='8' color='black'> {{number_format($dash_data->percent_female)}}%</font>
                                        </div></td>
                                        </tr>
                                    </table>
                                
                                
                                   
                                </div>


                       
                       
                    </div>

                    <div class="row">
                        <div class="col-md-12" style="text-align:center;">

                            <label style="font-size:21px; margin-bottom:0px;">Estimated Area Planted</label> <br>
                            <label id="est_area" style="font-size:42px; margin-top:0px;">{{number_format($dash_data->claimed_female+$dash_data->claimed_male)}} (ha)</label>
                        </div>
                    </div>

                    <div class="row">

                            <table width="100%">
                                <tr>
                                    <td style="width: 50%" align="right">
                                        <div  style='border:solid; height:121px; width:132px; background-color:#00FF38; opacity:0.31; text-align:center; margin-right:5px;' >
                                            <label id="ala" style="font-size:38px;weight:bold; color:black;" for="">
                                             {{-- uncomment me --}}
                                                {{-- {{number_format((($dash_data->claimed_male + $dash_data->claimed_female) / ($dash_data->total_male + $dash_data->total_female)),2 )}} --}}
                                            </label> <br>
                                            <label style="font-size:20px; weight:bold; color:black;" for="">
                                                hectares
                                            </label>

                                        </div>
                                    </td>
                                    <td>
                                    <div style="font-size:28px;">
                                        <strong>A</strong>verage <br>
                                        <strong>L</strong>andholding <br>
                                        <strong>A</strong>rea <br>
                                    </div>
                                    </td>
                                </tr>
                            </table>

                        
                        

                    </div>





                    <div class="row">
                        <div class="col-md-12" style="font-size:33px;weight:bold;text-align:center;">
                                    Age Groups
                        </div>
                    </div>


                    <div class="row" >
                        <div class="col-md-12">
                        <table width="100%" style="margin-bottom:10px;">
                            <tr>
                                <td align="right">
                                    <div  style="height:60px;width:77px; background-color:#FFD600; border:solid;">
                                        <label id="cat1" style="font-size:28px;weight:bold;">
                                           {{-- uncomment me --}}
                                            {{-- {{number_format(($dash_data->overall_cat1/($dash_data->total_male+$dash_data->total_female))*100)."%"}} --}}
                                          
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div  style="font-size:33px; margin-left:10px;">
                                        18-29 years old
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                    <div  style="height:60px;width:77px; background-color:#FDE566; border:solid;">
                                    <label id="cat2" style="font-size:28px;weight:bold;">
                                   {{-- uncomment me --}}
                                        {{-- {{number_format(($dash_data->overall_cat2/($dash_data->total_male+$dash_data->total_female))*100)."%"}}</label> --}}
                                    </div>
                                </td>
                                <td>
                                    <div  style="font-size:33px; margin-left:10px;">
                                    30-59 years old
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                    <div  style="height:60px;width:77px; background-color:#FCEEA5; border:solid;">
                                    <label id="cat3" style="font-size:28px;weight:bold;">
                                    {{-- uncomment me --}}
                                        {{-- {{number_format(($dash_data->overall_cat3/($dash_data->total_male+$dash_data->total_female))*100)."%"}} --}}
                                </label>
                                    </div>
                                </td>
                                <td>
                                    <div  style="font-size:33px; margin-left:10px;">
                                    60 and above
                                    </div>
                                </td>
                            
                            
                            </tr>

                        </table>
                        </div>
                        
                    </div>


                    

                   



            </div>
            
            
            </div>
        </div>
   
    </div>
    
</div>










@endsection
@push('scripts')

        <script src=" {{ asset('public/js/highcharts.js') }} "></script>

        <script type="text/javascript">
  //mapboxgl.accessToken = 'pk.eyJ1IjoiYWhyamhhY2UiLCJhIjoiY2t0bzQya3Y2MDk0aTJvcWludnF4c3B2ZiJ9.VPW25_j0CgfdOgrfgD6ojg';
//   mapboxgl.accessToken = 'pk.eyJ1IjoiYWhyamhhY2UiLCJhIjoiY2t0bzNvYjZhMDkwazJ1cDd5dG0xZGZoaiJ9.ODpa0PhB4E-fzNLONSoAHA';

// const map = new mapboxgl.Map({
//   container: 'map_gad',
//   style: 'mapbox://styles/mapbox/streets-v11',
//   center: [121.7740, 12.8797], //long, lat
//     //center: [120.6856995, 15.3909118], //zoom 8 for region
//   zoom: 5,
// });

// map.on('load', () => {
//                 map.resize();
//             }); //MAP ONLOAD


        function deleteLayer(arr){
            // alert(arr.length);
            for(var i = 0; i < arr.length;i++ ){
                map.removeLayer(arr[i]);
            }

        }

        function deleteSource(arr){
            // alert(arr.length);
            for(var i = 0; i < arr.length;i++ ){
                map.removeSource(arr[i]);
            }

        }
        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
        localStorage.clear();
        function generateMap(){

           var layer_created = JSON.parse(localStorage.getItem("layer_created"));
           var source_created = JSON.parse(localStorage.getItem("source_created"));
            
            

            if(layer_created !== null){
                deleteLayer(layer_created);
            }
            
            if(source_created !== null){
                deleteSource(source_created);
            }
           

            var layer_created =[];
            var source_created = [];
            
            var region = $("#map_region").val();
            var province = $("#map_province").val();

            var SITE_URL ="{{url('/')}}";

  
        if(province === "0"){
            if(region === "all"){
                HoldOn.open(options);
                $.ajax({
                        method: 'POST',
                        url: "{{route('api.region.list')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        dataType: 'json',
                        success: function (reg_list) {
                            $.each(reg_list, function (x, e) {
                            
                                var curr_reg = e.region;


                                        $.ajax({
                                        method: 'GET',
                                        url: SITE_URL + "/api/map/tiller/"+e.region+"/all",
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                        },
                                        dataType: 'json',
                                        success: function (source) {
                                            
                                            $.each(source, function (i, d) {
                                                if(d.properties.label_position === "R"){
                                                    var liner = 2;
                                                }else{
                                                    var liner = -2;
                                                }
                                                                    
                                                map.addSource(d.properties.ADM2_EN+"_tile_source", {
                                                            'type': 'geojson',
                                                            'data': {            
                                                'type': d.type,
                                                'geometry': {
                                                'type': d.geometry.type,
                                                // These coordinates outline Maine.
                                                'coordinates': d.geometry.coordinates
                                                }
                                                            }
                                                            });

                                                            
                                                         source_created.push(d.properties.ADM2_EN+"_tile_source");
                                                       // source_created = source_created + d.properties.ADM2_EN+"_tile_source" + ";";
                                                          
                                                                        // Add a new layer to visualize the polygon.
                                                        map.addLayer({
                                                        'id': d.properties.ADM2_EN+"_tile",
                                                        'type': 'fill',
                                                        'class': 'tile',
                                                        'source': d.properties.ADM2_EN+"_tile_source", // reference the data source
                                                        'layout': {},
                                                        'paint': {
                                                        'fill-color': '#0080ff', // blue color fill
                                                        'fill-opacity': 0.5
                                                        }
                                                        });
                                                        
                                                       layer_created.push(d.properties.ADM2_EN+"_tile");
                                                        
                                                        // Add a black outline around the polygon.
                                                        map.addLayer({
                                                        'id': d.properties.ADM2_EN+"_outline",
                                                        'type': 'line',
                                                        'source': d.properties.ADM2_EN+"_tile_source",
                                                        'layout': {},
                                                        'paint': {
                                                        'line-color': 'green',
                                                        'line-width': 1
                                                        }
                                                        });
                                                        
                                                       layer_created.push(d.properties.ADM2_EN+"_outline");
                                                      
                                    //LINE ADDER
                                    map.addSource(d.properties.ADM2_EN+"_line_source", {
                                    'type': 'geojson',
                                    'data': {
                                    'type': 'Feature',
                                    'properties': {},
                                    'geometry': {
                                    'type': 'LineString',
                                    'coordinates': [
                                        [ parseFloat(d.properties.lon), parseFloat(d.properties.lat)],
                                        [ parseFloat(d.properties.lon)+liner, parseFloat(d.properties.lat) ]
                                    ]
                                    }
                                    }
                                    });
                                    source_created.push(d.properties.ADM2_EN+"_line_source");


                                    map.addLayer({
                                    'id': d.properties.ADM2_EN+"_line",
                                    'type': 'line',
                                    'source': d.properties.ADM2_EN+"_line_source",
                                    'layout': {
                                    'line-join': 'round',
                                    'line-cap': 'round',
                                    },
                                    'paint': {
                                    'line-color': 'black',
                                    'line-width': 1
                                    }
                                    });

                                    layer_created.push(d.properties.ADM2_EN+"_line");



                                         map.addSource(d.properties.ADM2_EN+"_place_source", {
                                            'type': 'geojson',
                                            'data': {
                                                    'type': 'Feature',
                                                    'properties': {
                                                    'description': d.properties.ADM2_EN,
                                                    
                                                    },
                                                    'geometry': {
                                                    'type': 'Point',
                                                    'coordinates':   [ parseFloat(d.properties.lon)+liner, parseFloat(d.properties.lat) ]
                                                    }
                                                    }
                                            });
                                    source_created.push(d.properties.ADM2_EN+"_place_source");
                                          
                                            map.addLayer({
                                            'id': d.properties.ADM2_EN+"_place",
                                            'type': 'symbol',
                                            'source': d.properties.ADM2_EN+"_place_source",
                                            'layout': {
                                            'text-field': ['get', 'description'],
                                            'text-variable-anchor': ['top', 'bottom', 'left', 'right'],
                                            'text-radial-offset': 0.5,
                                            'text-justify': 'auto',
                                            'icon-image': ['get', 'icon'],
                                            'text-size': 10
                                            }
                                            });
                                            layer_created.push(d.properties.ADM2_EN+"_place");
                                            
                                                
                                                
                                            });  //FOREACH
                                               


                                            

                                            localStorage.setItem("layer_created", JSON.stringify(layer_created));
                                            localStorage.setItem("source_created", JSON.stringify(source_created));
                                            
                                        } //SUCCESS
                                    }); //AJAX  
                                    
                   
                            
                              

                            }); //FOREACH REGION


                            setInterval(Hold, 9000);

                           
                         
                            map.flyTo({
                                center: [121.7740, 12.8797], //long, lat
                                zoom: 5.3,
                                speed: 0.8, // make the flying slow
                                curve: 1,
                                essential: true // this animation is considered essential with respect to prefers-reduced-motion
                                
                                    }); //FLY TO




                         
                        }
                }); //AJAX

            }
            else {    
        $.ajax({
                method: 'GET',
                url: SITE_URL + "/api/map/tiller/"+region+"/"+province,
                data: {
                     _token: "{{ csrf_token() }}",
                },
                dataType: 'json',
                success: function (source) {
                    $.each(source, function (i, d) {
                        
                        if(d.properties.label_position === "R"){
                            var liner = 2;
                        }else{
                            var liner = -2;
                        }
                        map.addSource(d.properties.ADM2_EN+"_tile_source", {
                                    'type': 'geojson',
                                    'data': {            
                        'type': d.type,
                        'geometry': {
                        'type': d.geometry.type,
                        // These coordinates outline Maine.
                        'coordinates': d.geometry.coordinates
                        }
                                    }
                                    });

                                    source_created.push(d.properties.ADM2_EN+"_tile_source");


                             // Add a new layer to visualize the polygon.
                                map.addLayer({
                                'id': d.properties.ADM2_EN+"_tile",
                                'type': 'fill',
                                'class': 'tile',
                                'source': d.properties.ADM2_EN+"_tile_source", // reference the data source
                                'layout': {},
                                'paint': {
                                'fill-color': '#0080ff', // blue color fill
                                'fill-opacity': 0.5
                                }
                                });

                                layer_created.push(d.properties.ADM2_EN+"_tile");

                                // Add a black outline around the polygon.
                                map.addLayer({
                                'id': d.properties.ADM2_EN+"_outline",
                                'type': 'line',
                                'source': d.properties.ADM2_EN+"_tile_source",
                                'layout': {},
                                'paint': {
                                'line-color': 'green',
                                'line-width': 1
                                }
                                });
                                layer_created.push(d.properties.ADM2_EN+"_outline");

                                //LINE ADDER
                                    map.addSource(d.properties.ADM2_EN+"_line_source", {
                                    'type': 'geojson',
                                    'data': {
                                    'type': 'Feature',
                                    'properties': {},
                                    'geometry': {
                                    'type': 'LineString',
                                    'coordinates': [
                                        [ parseFloat(d.properties.lon), parseFloat(d.properties.lat)],
                                        [ parseFloat(d.properties.lon)+liner, parseFloat(d.properties.lat) ]
                                    ]
                                    }
                                    }
                                    });

                                    source_created.push(d.properties.ADM2_EN+"_line_source");


                                    map.addLayer({
                                    'id': d.properties.ADM2_EN+"_line",
                                    'type': 'line',
                                    'source': d.properties.ADM2_EN+"_line_source",
                                    'layout': {
                                    'line-join': 'round',
                                    'line-cap': 'round',
                                    },
                                    'paint': {
                                    'line-color': 'black',
                                    'line-width': 1
                                    }
                                    });
                                    layer_created.push(d.properties.ADM2_EN+"_line");

                                    //ADD PLACES
                                         map.addSource(d.properties.ADM2_EN+"_place_source", {
                                            'type': 'geojson',
                                            'data': {
                                                    'type': 'Feature',
                                                    'properties': {
                                                    'description': d.properties.ADM2_EN,
                                                    
                                                    },
                                                    'geometry': {
                                                    'type': 'Point',
                                                    'coordinates':   [ parseFloat(d.properties.lon)+liner, parseFloat(d.properties.lat) ]
                                                    }
                                                    }
                                            });

                                    source_created.push(d.properties.ADM2_EN+"_place_source");

                                            map.addLayer({
                                            'id': d.properties.ADM2_EN+"_place",
                                            'type': 'symbol',
                                            'source': d.properties.ADM2_EN+"_place_source",
                                            'layout': {
                                            'text-field': ['get', 'description'],
                                            'text-variable-anchor': ['top', 'bottom', 'left', 'right'],
                                            'text-radial-offset': 0.5,
                                            'text-justify': 'auto',
                                            'icon-image': ['get', 'icon']
                                            }
                                            });
                                            layer_created.push(d.properties.ADM2_EN+"_place");

                     });  //FOREACH
                     localStorage.setItem("layer_created", JSON.stringify(layer_created));
                     localStorage.setItem("source_created", JSON.stringify(source_created));
                     $.ajax({
                                method: 'POST',
                                url: "{{route('api.region.coordinates')}}",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    region: region,
                                },
                                dataType: 'json',
                                success: function (source) {
                                    map.flyTo({
                                    center: [source.lat,source.lon],
                                    zoom: 6,
                                    speed: 0.8, // make the flying slow
                                    curve: 1,
                                    essential: true // this animation is considered essential with respect to prefers-reduced-motion
                                });
                                }
                        }); //AJAX
         
        
                } //SUCCESS
             }); //AJAX  
            }
        }else{
                //PER PROVINCE
        $.ajax({
                method: 'GET',
                url: SITE_URL + "/api/map/tiller/"+region+"/"+province,
                data: {
                     _token: "{{ csrf_token() }}",
                },
                dataType: 'json',
                success: function (source) {
                    $.each(source, function (i, d) {
                        var randomColor = Math.floor(Math.random()*16777215).toString(16);
                        
                    
                        map.addSource(d.properties.ADM3_EN+"_tile_source", {
                                    'type': 'geojson',
                                    'data': {            
                                    'type': d.type,
                                    'geometry': {
                                    'type': d.geometry.type,
                                    // These coordinates outline Maine.
                                    'coordinates': d.geometry.coordinates
                                    }
                                    }
                                    });
                        
                                    source_created.push(d.properties.ADM3_EN+"_tile_source");



                             // Add a new layer to visualize the polygon.
                                map.addLayer({
                                'id': d.properties.ADM3_EN+"_tile",
                                'class': 'tile',
                                'type': 'fill',
                                'source': d.properties.ADM3_EN+"_tile_source", // reference the data source
                                'layout': {},
                                'paint': {
                                'fill-color': getRandomColor(), // blue color fill
                                'fill-opacity': 0.5
                                }
                                });
                                layer_created.push(d.properties.ADM3_EN+"_tile");


                                // Add a black outline around the polygon.
                                map.addLayer({
                                'id': d.properties.ADM3_EN+"_outline",
                                'type': 'line',
                                'source': d.properties.ADM3_EN+"_tile_source",
                                'layout': {},
                                'paint': {
                                'line-color': "black",
                                'line-width': 1
                                }
                                });
                                layer_created.push(d.properties.ADM3_EN+"_outline");


                            
                     });  //FOREACH

                                localStorage.setItem("layer_created", JSON.stringify(layer_created));
                                localStorage.setItem("source_created", JSON.stringify(source_created));
                                

                        $.ajax({
                                method: 'POST',
                                url: "{{route('api.province.coordinates')}}",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    province: province,
                                },
                                dataType: 'json',
                                success: function (source) {
                                    map.flyTo({
                                    center: [source.lon,source.lat],
                                    zoom: 8,
                                    speed: 0.8, // make the flying slow
                                    curve: 1,
                                    essential: true // this animation is considered essential with respect to prefers-reduced-motion
                                });
                                }
                        }); //AJAX
        


    



                     
                     
                } //SUCCESS
             }); //AJAX  
            
        } //PER PROVINCE

        
       
        }








      
    function male_female_percent(){
       var region =  $("#map_region").val();
        var province = $("#map_province").val();

        if(region == "0"){

        }else{

            HoldOn.open(holdon_options);
            generateMap();
            $.ajax({
                type: 'POST',
                url: "{{route('gad.gender.percent')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: "sex",
                    region: region,
                    province: province

                },
                dataType: "json",
                success: function(data){
                    
                    $("#male_percent").empty().append("<font size='5' color='black'> MALE </font> <br> <font size='10' color='black'>" + data["male"] + "</font>");
                    $("#female_percent").empty().append("<font size='5' color='black'>FEMALE </font> <br> <font size='10' color='black'>" + data["female"] + "</font>");
                    $("#est_area").empty().append(data["est_area"]);
                    $("#cat1").empty().append(data["cat1"]);
                    $("#cat2").empty().append(data["cat2"]);
                    $("#cat3").empty().append(data["cat3"]);
                    
                
                    $("#ala").empty().append(data["ala"]);

                    HoldOn.close();
                }
            }); 
        }


       


    }



    function map_region(){
        var SITE_URL = "{{url('/')}}";

region = $("#map_region").val();

$.ajax({
                type: 'GET',
                url:  SITE_URL+"/gad/province_list/"+region,
                data: {
                    _token: "{{ csrf_token() }}",
                    type: "variety_sex"
                },
                dataType: "json",
                success: function(data){
                    $("#map_province").empty().append("<option value='0'>Select Province</option>");
                    $.each(data, function(i, d) {
                       
                            if(i == 0){
                                $("#map_province").empty().append("<option value='0'>Select Province</option>");
                            }
                            $("#map_province").append("<option value='"+d.province+"'>"+d.province+"</option>");
                     });
                }
            });  


    }


   
        



    

        
        </script>


<script>
    






    ajax_variety_sex();
    ajax_variety_group();
       graphTwoLoad();
 loadList();
    function ajax_variety_sex(){
        var category = [];
        var male = [];
        var female = [];
                $.ajax({
                        type: 'POST',
                        url: "{{ route('gad.generate.graph') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            type: "variety_sex"
                        },
                        dataType: "json",
                        success: function(data){
                            $.each(data, function(i, d) {
                                category.push(d.seed_variety);
                                male.push(parseInt(d.total_male));
                                female.push(parseInt(d.total_female));
                             });
                       
                             load_variety_sex(category,male,female);
                        }
                    });  
    }



    function load_variety_sex(cat,mal,femal){

        
                    Highcharts.chart('graph_variety_sex', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: '{{$year}} {{$code}} relative share of varieties by sex'
                },
               
                xAxis: {
                    categories:cat,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Rainfall (mm)'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Male',
                    data: mal

                }, {
                    name: 'Female',
                    data: femal

                }]
            });

    }

    function ajax_variety_group(){
        var category = [];
        var cat1 = [];
        var cat2 = [];
        var cat3 =[];
                $.ajax({
                        type: 'POST',
                        url: "{{ route('gad.generate.graph') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            type: "variety_group"
                        },
                        dataType: "json",
                        success: function(data){
                            $.each(data, function(i, d) {
                                category.push(d.seed_variety);
                                cat1.push(parseInt(d.cat1));
                                cat2.push(parseInt(d.cat2));
                                cat3.push(parseInt(d.cat3));
                                
                             });
                       
                             load_variety_group(category,cat1,cat2,cat3);
                        }



                    });  
    }



    function  load_variety_group(cat,cat1,cat2,cat3){

        
                    Highcharts.chart('graph_variety_group', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: '{{$year}} {{$code}} relative share of varieties by age group'
                },
               
                xAxis: {
                    categories:cat,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Rainfall (mm)'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: '18-29',
                    data: cat1

                }, {
                    name: '30-59',
                    data: cat2

                }, {
                    name: '60 up',
                    data: cat3

                }]
            });

    }



    HoldOn.open(holdon_options);

            $.ajax({
                type: 'POST',
                url: "{{ route('gad.dashboard.data') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: "bar_land"
                },
                dataType: "json",
                success: function(data){
                    load_bar_land(data);
                    HoldOn.close();
                }
            });    


          function load_bar_land(data){

            var overall_1 = parseFloat(data.claimed_1) / parseFloat(data.farmer_1);
            var overall_2 = parseFloat(data.claimed_2) / parseFloat(data.farmer_2);
            var overall_3 = parseFloat(data.claimed_3) / parseFloat(data.farmer_3);
            var male_1 = parseFloat(data.claimed_male_1) / parseFloat(data.farmer_male_1);
            var male_2 = parseFloat(data.claimed_male_2) / parseFloat(data.farmer_male_2);
            var male_3 = parseFloat(data.claimed_male_3) / parseFloat(data.farmer_male_3);
            var female_1 = parseFloat(data.claimed_female_1) / parseFloat(data.farmer_female_1);
            var female_2 = parseFloat(data.claimed_female_2) / parseFloat(data.farmer_female_2);
            var female_3 = parseFloat(data.claimed_female_3) / parseFloat(data.farmer_female_3);

            
            var overall_1 = parseFloat(overall_1.toFixed(2));
            var overall_2 = parseFloat(overall_2.toFixed(2));
            var overall_3 = parseFloat(overall_3.toFixed(2));
            var male_1 = parseFloat(male_1.toFixed(2));
            var male_2 = parseFloat(male_2.toFixed(2));
            var male_3 = parseFloat(male_3.toFixed(2));
            var female_1 = parseFloat(female_1.toFixed(2));
            var female_2 = parseFloat(female_2.toFixed(2));
            var female_3 = parseFloat(female_3.toFixed(2));

            
            Highcharts.chart('bar_land', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: ''
                },
                
                xAxis: {
                    categories: [
                        'OVERALL',
                        'MALE',
                        'FEMALE'
                    ],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Landholding'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y:.1f} (ha)</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{y}'
                        }
                    }
                },
                series: [{
                    name: '18-29',
                    data: [overall_1, male_1, female_1]

                }, {
                    name: '30-59',
                    data: [overall_2, male_2, female_2]

                }, {
                    name: '60 up',
                    data: [overall_3, male_3, female_3]

                }]
            });
           

           

          }





          







            $.ajax({
                type: 'POST',
                url: "{{ route('gad.dashboard.data') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: "stacked"
                },
                dataType: "json",
                success: function(data){
                    load_stacked(data);
                }
            });           


        function load_stacked(data){
        
            Highcharts.chart('stacked_data', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: ''
                },
                xAxis: {
                    categories: ['MALE', 'FEMALE'], 
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Percentage'
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: ( // theme
                                Highcharts.defaultOptions.title.style &&
                                Highcharts.defaultOptions.title.style.color
                            ) || 'gray',
                            textOutline: 'none'
                        },
                        formatter: function () {
                            return this.total + " %";
                        }
                    }
                },
                legend: {
                    floating: false,
                    backgroundColor:
                        Highcharts.defaultOptions.legend.backgroundColor || 'white',
                    borderColor: '#CCC',
                    borderWidth: 1,
                    shadow: true
                },
                tooltip: {
                    headerFormat: '<b>{point.x}</b><br/>',
                    pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true,
                            format: '{y} %'
                        }
                    }
                },
                series: [ {
                    name: '18-29',
                    data: [parseFloat(data.male_1_percent.toFixed(2)), parseFloat(data.female_1_percent.toFixed(2))]
                }
                , {
                    name: '30-59',
                    data: [parseFloat(data.male_2_percent.toFixed(2)), parseFloat(data.female_2_percent.toFixed(2))]
                },
                {
                    
                    name: '60 up',
                    data: [parseFloat(data.male_3_percent.toFixed(2)), parseFloat(data.female_3_percent.toFixed(2))]
                }
               
            ]
            });


        }

        
    





        $('#modal_gad_list').on('show.bs.modal', function (e) {

                    $('#stocks_tbl').DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{route('gad.stored.excel')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",   
                        }
                    },
                    "columns":[
                        {"data": "title"},
                        {"data": "total_male","className": "text-right"},
                        {"data": "total_female","className": "text-right"},
                        {"data": "action","className": "text-center"},
                    ]
                });


        });





    $("#export_excel").on("click", function(e) {
        var SITE_URL = "{{url('/')}}";
        window.open(SITE_URL+"/gad/download/excel/dl","_blank");
    });

    $("#store_excel").on("click", function(e) {
        HoldOn.open(holdon_options);
        var SITE_URL = "{{url('/')}}";
       
        $.ajax({
                type: 'GET',
                url: SITE_URL+"/gad/download/excel/store",
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    if(data=="store"){
                        alert("Excel Stored");
                    }
              
                    HoldOn.close();
                }
            });


    });



function graphTwoLoad(){
    
    
}


 













$("#stocks_tbl").DataTable({
     "searching": false,
});
             
        //var coop_id = $("#coop_id").val();
        //var coop_name = $("#coop_id option:selected").text();
        //if (coop_id != '') {
          //  HoldOn.open(holdon_options);
//      var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2021/transfers/load_deliveries/oldseason';
            
    $("#dataTBL").DataTable();

            function loadList(){
                HoldOn.open(holdon_options);
//   $('#dataTBL').DataTable().clear();
                $('#dataTBL').DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 25,
                    "ajax": {
                        "url": "{{route('gad.monitoring.genTable')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",   
                        }
                    },
                    "columns":[
                        {"data": "seed_variety"},
                        
                        {"data": "total_bag","className": "text-right"},
                        {"data": "cent_bag","className": "text-right"},
                        {"data": "male_bag","className": "text-right"},
                        {"data": "male_cent","className": "text-right"},
                        {"data": "female_bag","className": "text-right"},
                        {"data": "female_cent","className": "text-right"},
                        {"data": "blank","bSortable": "false", "border":"none"},

                        {"data": "cat1_bag","className": "text-right"},
                        {"data": "cat1_cent","className": "text-right"},
                        {"data": "cat2_bag","className": "text-right"},
                        {"data": "cat2_cent","className": "text-right"},
                        {"data": "cat3_bag","className": "text-right"},
                        {"data": "cat3_cent","className": "text-right"},

                    
                    ]
                });

            
            HoldOn.close();
            }
        
        
       

       

     //plot data to seed chart
        function populateTransferChart(chart_data){
            //alert(chart_data);
            Highcharts.chart('male_bar', {
                chart: {
                    backgroundColor: null,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'bar'
                },
                title: {
                    text: "GAD Data"
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: '',
                    pointFormat: '{point.name}: <b>{point.y:,.0f}</b>'
                },
                plotOptions: {
                    bar: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>'
                        },
                        showInLegend: true
                    }
                },
                 xAxis: {
                    categories: ['Beneficiary', 'Bag/s', 'Area']},
                series: [{
                    showInLegend: false, 
                    colorByPoint: true,
                    data: chart_data
                }]
            });
        }









           


       // load_chart();

</script>
@endpush
