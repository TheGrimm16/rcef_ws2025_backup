@extends('layouts.index')

@section('content')

<style type="text/css">
	
	.myTile{
		text-align: center;
	}

	.myTileText{
		font-size: 25px;
		font-weight: bold;
		font-family: "Bahnschrift Light";
	}

	.excel_btn{
		float: right;	
		cursor: not-allowed;
	}



</style>

	 <div class="row">
        <div class="col-md-4">
	 					<div class="x_panel">
                <div class="x_title">
                    <h2>Total Registered FCA</h2> 
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal myTile">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="myTileText"> {{$total_farmer}} registered FCA</div>
                            <div class="myTileText"> <a class="btn btn-success btn-sm"  data-toggle='modal' data-target='#modal_fca' >VIEW LIST OF FCA (REGION, PROVINCE)</a></div>
                        </div>
                      
                    </div>
                </div>
            	</div>
       		</div>

       		<div class="col-md-4">
	 					<div class="x_panel">
                <div class="x_title">
                    <h2>Techno-Demo Sites</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal myTile">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="myTileText"> {{$total_province}} Covered Provinces</div>
                            <div class="myTileText"> {{$total_municipality}} Established Sites</div>
                        </div>
                    </div>
                </div>
            	</div>
       		</div>

       		<div class="col-md-4">
	 					<div class="x_panel">
                <div class="x_title">
                    <h2>Estimated Area Covered (ha)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal myTile">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                            <div class="myTileText" >{{$total_area}} ha covered</div>
                            <div class="myTileText"> <a class="btn btn-success btn-sm" data-toggle='modal' data-target='#modal_area'><i class="fa fa-bar-chart" aria-hidden="true" ></i> VIEW CHART</a></div>
                        </div>
                      
                    </div>
                </div>
            	</div>
       		</div>
    </div>




    <div class="row">
        <div class="col-md-6">
	 					<div class="x_panel col-md-12">
                <div class="x_title">
                    <h2>Crop Status</h2> 

                    <button class="excel_btn" id="export_crop_status"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp; EXPORT</button>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal myTile">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0; height: 300px;" id="crop_graph"></div>
                    </div>
                </div>
            	</div>
            	<div class="x_panel col-md-12">
                <div class="x_title">
                    <h2>LOA Status</h2>
                    <button class="excel_btn" id="export_loa_status"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp; EXPORT</button>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal myTile">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0; height: 300px;" id="loa_graph"></div>
                      
                    </div>
                </div>
            	</div>
       		</div>

       		<div class="col-md-6">
	 					<div class="x_panel">
                <div class="x_title">
                    <h2>Techno-Demo Sites</h2>
                    <button class="excel_btn" id="export_sites"> <i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp; EXPORT</button>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal myTile  myTileTable">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0; height: 700px;">
                           	 <table class="table table-bordered table-striped" id="tbl_sites">
				                        <thead>
				                            <th>Crop Establishment</th>
				                            <th>No. of Sites</th>
				                            <th>Area (ha)</th>
				                        </thead>
                    				</table>
                        </div>
                    </div>
                </div>
            	</div>
       		</div>

       		
    </div>

    <!-- FCA MODAL-->
<div id="modal_fca" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>FCA LIST</span><br>
                </h4>
            </div>
            <div class="modal-body">
             		<div class="accordion">
                    @foreach ($region_list as $region)
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                        {{$region->add_region}}
                                    </button>
                                    <i class="fa fa-plus pull-right" id="icon_id_{{$region->regCode}}" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse{{$region->regCode}}" aria-controls="{{$region->regCode}}" onclick="getProvinceList('{{$region->regCode}}', '{{$region->add_region}}');"></i>
                                </h5>
                            </div>
                            <div id="collapse{{$region->regCode}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                <div class="card-body">
                                    <ul class="list-group row" style="width: 97%;margin-left: 1vw; " id="list_{{$region->regCode}}">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>       
            </div>
            <div class="modal-footer">      
            </div>
        </div>
    </div>
</div>
    <!-- FCA MODAL-->



    <!-- AREA MODAL-->
<div id="modal_area" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>AREA COVERED</span><br>
                </h4>
            </div>
            <div class="modal-body">
                    <div id="area_graph"></div>       
            </div>
            <div class="modal-footer">      
            </div>
        </div>
    </div>
</div>
    <!-- AREA MODAL-->



@endsection

@push('scripts')
<script src=" {{ asset('public/js/highcharts.js') }} "></script>
	<script>
			 function getProvinceList(regCode, region){

                var cards = "";
    				  $("#icon_id_"+regCode).toggleClass('fa-plus fa-minus');
        			$("#list_"+regCode).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
    
        		$.ajax({
                type: 'POST',
            		url: "{{ route('palaysikatan.dashboard.province_list') }}",
                 data: {
                _token: "{{ csrf_token() }}",
                region: region
            		},
                dataType: 'json',
                success: function (source) {
							      $("#list_"+regCode).empty();
                                    cards = cards + '<div class="accordion">';
			                $.each(source, function (i, d) {
                                    cards = cards + '<div class="card">';
                                    cards = cards + '<div class="card-header" id="headingOne">';
                                    cards = cards + '<h5 class="mb-0" style="margin:0">';
                                    cards = cards + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">'+d.add_province+'</button>';
                                    cards = cards + '<i class="fa fa-plus pull-right" id="icon_id_'+d.provCode+'" style="margin-top: 12px;margin-right: 10px; cursor:pointer" data-toggle="collapse" data-target="#collapse'+d.provCode+'" aria-controls="'+d.provCode+'" onclick="getMunicipalList('+"'"+d.provCode+"','"+d.add_province+"','"+region+"'"+ ');"></i>';
                                    cards = cards + '</h5></div>';  
                                    cards = cards + '<div id="collapse'+d.provCode+'" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion" style="margin-top: .5vw;">';
                                    cards = cards + '<div class="card-body">';      
                                    cards = cards + '<ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="list_muni_'+d.provCode+'">';
                                    cards = cards + '</ul></div></div></div>';

			                });    
                                    cards = cards + '</div>';

                                 $("#list_"+regCode).append(cards);
        				}
        		});


   		}

   		function getMunicipalList(provCode, province, region){
   			$("#icon_id_"+provCode).toggleClass('fa-plus fa-minus');
        $("#list_muni_"+provCode).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
        $.ajax({
                type: 'POST',
            		url: "{{ route('palaysikatan.dashboard.municipal_list') }}",
                 data: {
                _token: "{{ csrf_token() }}",
                region: region,
                province: province
            		},
                dataType: 'json',
                success: function (source) {
							      $("#list_muni_"+provCode).empty();
					            var dataHtml = "";
                                var btns = "";
                                var municipalCode ="";
					              $.each(source, function (i, d) {
                                        if(btns !== "")btns = btns +",";
				  						btns = btns + d.munCode;	

                                            if(municipalCode == "")municipalCode =d.munCode;

                                            		if(i == 0){

                                                        
                                                        //alert(d.munCode);
				  											dataHtml = dataHtml + '<table width="100%">';
				  											dataHtml = dataHtml + '<tr><td style="vertical-align:top; padding:0; margin:0;" ><button style="width:80%;text-align: center;" id="btn_muni_'+d.munCode+'" onclick="load_fca_tbl('+"'"+d.munCode+"',"+"'"+provCode+"'"+')" text-align: center;" class="btn btn-success btn-sm">'+d.add_municipality+' - '+d.f_count+' Farmer(s)'+'</button></td>';
				  											dataHtml = dataHtml + '<td style="width:70%; padding:0; margin:0; vertical-align:top;" rowspan="'+source.length+'"><table class="table table-bordered table-striped" width="100%" border=1 id="fca_list_'+provCode+'"><thead><th>FCA ID</th><th style="width:30%;">Name</th><th>Crop Establishment</th><th>Area</th><th>Seed Variety</th></thead> </table></td></tr>';

				  									}else{
				  											dataHtml = dataHtml + '<tr><td style="vertical-align:top;"><button style="width:80%; text-align: center;" id="btn_muni_'+d.munCode+'" onclick="load_fca_tbl('+"'"+d.munCode+"',"+"'"+provCode+"'"+')" class="btn btn-dark btn-sm">'+d.add_municipality+' - '+d.f_count+' Farmer(s)'+'</button></td></tr>';
				  									}         
				  									if(i == (source.length - 1)){
				  										dataHtml = dataHtml +'</table>';
                                                        dataHtml = dataHtml + '<input type="hidden" name="btns_'+provCode+'" id="btns_'+provCode+'" value="'+btns+'" >';

                                                        $("#list_muni_"+provCode).append(dataHtml); 
                                                      
				  									}


				                	});
				             load_fca_tbl(municipalCode,provCode); 
                            }
        		});
   		
   		}

                

        function load_crop_graph(){
                    const chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'crop_graph',
                                type: 'column',
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta:  15,
                                    depth: 50,
                                    viewDistance: 25
                                }
                            },
                            title: {
                                text: 'This Portion is under development'
                            },
                            subtitle: {
                                text: ''
                            },
                            plotOptions: {
                                column: {
                                    depth: 25
                                }
                            },
                            series: [{
                                data: [29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]
                            }]
                        });



        }


         function load_loa_graph(){
                    const chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'loa_graph',
                                type: 'column',
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta:  15,
                                    depth: 50,
                                    viewDistance: 25
                                }
                            },
                            title: {
                                text: 'This Portion is under development'
                            },
                            subtitle: {
                                text: ''
                            },
                            plotOptions: {
                                column: {
                                    depth: 25
                                }
                            },
                            series: [{
                                data: [29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]
                            }]
                        });



        }




        function load_area_graph(){
                    const chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'area_graph',
                                type: 'column',
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta:  15,
                                    depth: 50,
                                    viewDistance: 25
                                }
                            },
                            title: {
                                text: 'This Portion is under development'
                            },
                            subtitle: {
                                text: ''
                            },
                            plotOptions: {
                                column: {
                                    depth: 25
                                }
                            },
                            series: [{
                                data: [29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]
                            }]
                        });



        }


        load_loa_graph();
        load_crop_graph();


         $('#modal_area').on('show.bs.modal', function (e) {
           
            load_area_graph();

         });



                    
            function load_fca_tbl(munCode,provCode){
               // alert(munCode);
                //var currentButton ="btn_muni_"+munCode;
                var buttons = $("#btns_"+provCode).val();
                    buttons = buttons.split(",");
                        for(var i =0; i < buttons.length; i++){
                            if(buttons[i]==munCode){
                                $("#btn_muni_"+buttons[i]).removeAttr("class");
                                $("#btn_muni_"+buttons[i]).attr("class","btn btn-success btn-md");
                            }else{
                                $("#btn_muni_"+buttons[i]).removeAttr("class");
                                $("#btn_muni_"+buttons[i]).attr("class","btn btn-dark btn-md");
                            }
                        }




                     $('#fca_list_'+provCode).DataTable().clear();
                     $('#fca_list_'+provCode).DataTable({
                        "pageLength": 10,
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "ajax": {
                            "url": "{{ route('palaysikatan.dashboard.fca_list_tbl') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                munCode: munCode,
                                provCode: provCode
                            }
                        },
                        "columns":[
                            {"data": "fca"},
                            {"data": "name"},
                            {"data": "crop_establishment"},
                            {"data": "area"},
                            {"data": "seed_variety"},
                            
                        ]
                    });
            }         
                            
                            
                 
                    
                
            	
       		









			 function load_tbl(){
            $('#tbl_sites').DataTable().clear();
            $("#tbl_sites").DataTable({
                "pageLength": 25,
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('palaysikatan.dashboard.sitetbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                    }
                },
                "columns":[
                    {"data": "crop_establishment"},
                    {"data": "no_municipality"},
                    {"data": "area"},
                ]
            });
        }



        load_tbl();
	</script>
@endpush
