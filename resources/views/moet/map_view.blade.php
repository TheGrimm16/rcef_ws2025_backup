@extends('layouts.index')

@section('content')


<meta charset='utf-8' />
   <meta name='viewport' content='width=device-width, initial-scale=1' />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v2.5.1/mapbox-gl.js'></script>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.5.1/mapbox-gl.css' rel='stylesheet' />

    <style>
          body {
            margin: 0;
            padding: 0;
          }

          #map {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
          }

          .mapboxgl-popup {
            max-width: 200px;
          }

          .mapboxgl-popup-content {
            text-align: center;
            font-family: 'Open Sans', sans-serif;
          }
   

    </style>


    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Farm Map View</h3>
             
            </div>
        </div>

        	<div class="clearfix"></div>
            <div class="col-md-12">
               <div class="x_panel">
                <div class="row">
                <div class="form-group">
                          <label class="control-label col-md-1 col-sm-1 col-xs-1">Region:</label>
                    <div class="col-md-4">
                        <select class="form-control" name="region" id="region" onchange="remap(this.value, 'region')">
                            <option value="0">Please Select a Region</option>
                            @foreach($regions as $region)
                                <option value="{{$region->regCode}}">{{$region->regDesc}}</option>


                            @endforeach
                            </select>
                    </div>

                  </div> 
                  </div>
                     <br>
                    <div class="row">
                         <div class="form-group">
                          <label class="control-label col-md-1 col-sm-1 col-xs-1">Province:</label>
                            <div class="col-md-4">
                                <select class="form-control" name="province" id="province" onchange="remap(this.value, 'province')">
                                    <option value="0">Please Select a Province</option>
                                    </select>
                            </div>

                          </div>

                    </div>
                 
                </div>
            </div>
        <div class="x_content form-horizontal form-label-left">

            <!--
        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Please avoid processing large amount of rows. <b><u>[ Maximum of 1000 rows per process ]</u></b> this is to eliminate or minimize loading time.
            </div>
        </div> -->

      
               <div class="x_panel">
                  <div class="col-md-12" style=" padding-bottom: 30px; height:800px; width: 100%; margin-top:10px; margin-bottom: 10px;">
                  <div id="map" style="width:100%" ></div> 
                  </div>


               </div>



       	</div>
    </div>




@endsection
@push('scripts')


    <script type="text/javascript">    
   //mapboxgl.accessToken = 'pk.eyJ1IjoiYWhyamhhY2UiLCJhIjoiY2t0bzQya3Y2MDk0aTJvcWludnF4c3B2ZiJ9.VPW25_j0CgfdOgrfgD6ojg';
   mapboxgl.accessToken = 'pk.eyJ1IjoiYWhyamhhY2UiLCJhIjoiY2t0bzNvYjZhMDkwazJ1cDd5dG0xZGZoaiJ9.ODpa0PhB4E-fzNLONSoAHA';

const map = new mapboxgl.Map({
  container: 'map',
  style: 'mapbox://styles/mapbox/streets-v11',
  center: [121.7740, 12.8797], //long, lat
    //center: [120.6856995, 15.3909118], //zoom 8 for region
  zoom: 5.2,
  minZoom: 5.2,
  maxZoom: 15
});

     $('select[name="region"]').on('change', function () {
            HoldOn.open("sk-cube-grid");
            var regionCode = $('select[name="region"]').val();
            $('select[name="province"]').empty().append("<option value='0'>Please select a Province</option>");
                $.ajax({
                    method: 'POST',
                    url: "{{route('moet.get.province_list')}}",
                    data: {
                        _token: _token,
                        regCode: regionCode,
                        type : "map_provinces"
                    },
                    dataType: 'json',
                    success: function (source) {
                        $.each(source, function (i, d) {
                          $('select[name="province"]').append('<option value="' + d.provCode + '">' + d.provDesc + '</option>');
                        }); 
                        HoldOn.close();
                    }
                }); //AJAX GET MUNICIPALITY 
            } );  //END MUNICIPALITY SELECT



function remap(region, level){

     $.ajax({
            method: 'POST',
            url: "{{route('moet.map_view.coordinates')}}",
            data: {
                _token: _token,
                region: region,
                type: level
            },
            dataType: 'json',
            success: function (source) {
                var long = source["lon"];
                var lang = source["lan"];
                var zoom = source["zoom"];
                 map.flyTo({
                        container: 'map',
                                  style: 'mapbox://styles/mapbox/streets-v11',
                                  //center: [121.7740, 12.8797], //long, lat
                                    center: [long, lang], //zoom 8 for region

                        speed: 1, // make the flying slow
                        curve: 1, // change the speed at which it zooms out
                         zoom: zoom,
                         //pitch: 80,
                        bearing: 0,

                        easing: (t) => t,
                         
                        essential: true
                });



            }
        }); //AJAX GET MUNICIPALITY 






   

}

//var zoom = mapbox.getZoom();

      $.ajax({
                method: 'POST',
                url: "{{route('moet.map_view.data')}}",
                data: {
                    _token: _token,
                },
                dataType: 'json',
                success: function (source) {
             
                  const geojson = source;
                  // add markers to map
          for (const feature of geojson.features) {
                // create a HTML element for each feature
                const el = document.createElement('div');
                el.className = 'pin';
                var pin_point = "background-size:100% 100%; border-style='ridge'; border-color:black; width: 40px; height: 20px;  cursor:pointer;";
                pin_point = pin_point + "background-image: url('"+feature.geometry.img_path+"'), url('"+feature.geometry.img_on_err+"');";


                el.style.cssText = pin_point;
              






              // make a marker for each feature and add it to the map
              new mapboxgl.Marker(el)
              .setLngLat(feature.geometry.coordinates)
              .setPopup(
              new mapboxgl.Popup({ offset: 25 }) // add popups
              .setHTML(
              `<strong>${feature.properties.title}</strong><p style="text-align:left">${feature.properties.description}</p>`
              )
              )
              .addTo(map);
              }
          }
        });



           map.on('zoom', () => {
                        var x= document.getElementById("map");
                       var changeme = 0;

                if(map.getZoom() <= 6){
                    w = 40;
                    h = 20;    
                    changeme = 1;
                }else if(map.getZoom() > 6 && map.getZoom() <= 7){
                    w = 60;
                    h = 35;
                      changeme = 1;
                }else if(map.getZoom() > 7 && map.getZoom() <= 8 ){
                    w = 70;
                    h = 40;
                      changeme = 1;
                }else if(map.getZoom() > 8 && map.getZoom() <= 15){
                    w = 100;
                    h = 70;
                      changeme = 1;
                       
                        
                }

                if(changeme == 1){
                   for(var i=0, len=x.getElementsByClassName('pin').length; i<len; i++)
                    {
                     
                        x.getElementsByClassName('pin')[i].style.width = w+"px";
                        x.getElementsByClassName('pin')[i].style.height = h+"px";
                           
                    } 
                }

                    

                

           
                });

           


    </script>

@endpush