@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>FLSAR PDF GENERATION</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            <div class="row">
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong><i class="fa fa-info-circle"></i> Notice!</strong> Please avoid processing large amount of rows. <b><u>[ Maximum of 1000 rows per process ]</u></b> this is to eliminate or minimize loading time.
                </div>
            </div>

            {{-- <div class="row">
                <div class="alert alert-danger alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong><i class="fa fa-info-circle"></i> Notice!</strong> The currently allowed station to download FAR are:
                     <ul>
                        @foreach($stations as $stat)
                        <li>{{strtoupper($stat)}} </li>
                        @endforeach
                    </ul>
                </div>
            </div> --}}



        					<input type="hidden" name="hidden_region" id="hidden_region" >

        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="province_fg" id="province_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                      @foreach ($provinces_list as $province)
                                                <option value="{{ $province->province }}">{{ $province->province}}</option>
                                        @endforeach
                                    </select>
                                </div>
       						</div>

       						<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="municipality_fg" id="municipality_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a municipality</option>
                                    </select>
                                </div>
                            </div>

                            
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Brgy</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="brgy_fg" id="brgy_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a Brgy</option>
                                    </select>
                                </div>
                            </div>



                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Total Beneficiary</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <label class="control-label col-md-1 col-sm-3 col-xs-3" id="beneficiary">0</label>
                                </div>
                            </div>

                            <!-- <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Mark Ebinhi Beneficiary</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="checkbox" name="mark_ebinhi" id="mark_ebinhi" class="form_control" style="width:20px; height:20px;">
                                </div>
                            </div> -->

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Less Than 1000 sqm (0.1 ha)</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="checkbox" name="pre_reg" id="pre_reg" class="form_control" style="width:20px; height:20px;">
                                </div>
                            </div>

                            {{-- <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Transfer Farmers only</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="checkbox" name="is_transfer" id="is_transfer" class="form_control" style="width:20px; height:20px;">
                                </div>
                            </div> --}}


							<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Download Row Range:</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="number" size=7 name="num_from" value="0" id="num_from" max="0" onchange="ifMax()" onkeypress="return isNumber(event);" disabled=""> to 
                                    <input type="number" size=7 name="num_to" value="0" id="num_to" max="0" onchange="ifMax()" onkeypress="return isNumber(event);" disabled="">
                                
                                </div>
                            </div>                            

                            <!--
                            <div class="form-group">
                                <div class="col-md-7">
                                   <button type="button" name="download_flsar" id="download_flsar" class="btn btn-lg btn-warning" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF</button> 
                                    <button type="button" name="download_flsar_a3" id="download_flsar_a3" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF (A3)</button>
                                   
                                    <button type="button" name="download_flsar_a3_blank" id="download_flsar_a3_blank" class="btn btn-lg btn-dark" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Blank PDF (A3)</button>
                                    
                                     <button type="button" name="download_flsar_excel" id="download_flsar_excel" class="btn btn-lg btn-success" style="float: right;" disabled=""><i class="fa fa-file-excel-o"></i> Download Excel</button>
                                </div>
                            </div>       -->

                            <div class="form-group">
                                <div class="col-md-8">

                                    <?php
                                    $ext_far = array();
                                    $ext_far["aldrin.castro"] =1;
                                    $ext_far["reuel.maramara"] =1;
                                    $ext_far["derwin.villena"] =1;
                                    $ext_far["deejay.jimenez"] =1;
                                    $ext_far["asc.fontanilla"] =1;
                                    $ext_far["v.tingson"] =1;
                                    $ext_far["marelie.tangog"] =1;
                                    $ext_far["kimbie.pedtamanan"] =1;




                                    ?>
                                    @if(isset($ext_far[Auth::user()->username]))
                                    <button type="button" name="download_flsar_ext" id="download_flsar_ext" class="btn btn-lg btn-warning" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Extension List</button>
                                    <button type="button" name="download_flsar_a3" id="download_flsar_a3" class="btn btn-lg btn-success" style="float: right; visibility: hidden;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF (A3)</button>
                                   
                                    <!-- <button type="button" name="download_flsar_cross" id="download_flsar_cross" class="btn btn-lg btn-primary" style="float: right; visibility: hidden;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Cross Checking List</button> -->

                                    @else
                                    <button type="button" name="download_flsar_rep" id="download_flsar_rep" class="btn btn-lg btn-warning" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF (REPLACEMENT)</button>

                                    @if(Auth::user()->roles->first()->name == "rcef-programmer")
                                    <button type="button" name="download_flsar_a3" id="download_flsar_a3" class="btn btn-lg btn-success" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF</button>
                                    @else
                                    <button type="button" name="download_flsar_a3" id="download_flsar_a3" class="btn btn-lg btn-success" style="float: right; display:none;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF</button>
                                    @endif

                                    <button type="button" name="open_stored" id="open_stored" class="btn btn-lg btn-success" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> View Downloaded FLSAR</button>


                                    {{-- <button type="button" name="download_flsar_cross" id="download_flsar_cross" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Cross Checking List</button> --}}
                                    <button type="button" name="download_flsar_ext" id="download_flsar_ext" class="btn btn-lg btn-warning" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Extension List</button> 
                                    {{-- <button onclick="window.location.href='{{url("public/FAR")}}'" type="button" name="pre_downloaded" id="pre_downloaded" class="btn btn-lg btn-success" style="float: right;" ><i class="fa fa-file-pdf-o"></i> Pre-downloaded List</button> --}}
                                   
                                    <button type="button"  name="dl_excel_extra" id="dl_excel_extra" class="btn btn-lg btn-success" style="float: right;" disabled=""><i class="fa fa-file-excel-o"></i> Download Excel File</button> 
                          
                                   

                                    @endif
                                </div>
                            </div>





       	</div>
    </div>



    <div id="downloaded_fars" class="modal fade" role="dialog" >
        <div class="modal-dialog" style="width: 40%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">
                        <span>Generated FARs</span><br>
                    </h4>
                </div>
    
                <div class="modal-body">
                    <div class="col-xs-12">
                        
                        <label for="" class="col-md-12"  style="color: #f3a72d; font-size: 18px;" id="location"><strong></strong> </label>
                        <button onclick="check_files()" class="btn btn-success btn-sm">REFRESH</button>
                        <table class="table table-hover table-striped table-bordered" id="generated_far_tbl">
                            <tr>
                                <th>Brgy Name</th>
                                <th>Action</th>
                            </tr>
                            <tbody id='generated_far'>
                            </tbody>
                        </table>
                      
                      
                         
                    </div>
    
                </div>
                <div class="modal-footer" id="modal_footer">      
                    
                </div>
            </div>
        </div>
    </div>


@endsection
@push('scripts')
    <script>
        //   $("#generated_far_tbl").DataTable({
        //     "order": [],
        //     "pageLength": 25
        // });
            function check_files()
            {
                 
                var provCode = document.getElementById("province_fg");
                var province = provCode.options[provCode.selectedIndex].text;
                var municipalCode = document.getElementById("municipality_fg");
                var municipality = municipalCode.options[municipalCode.selectedIndex].text;
                
                $('#location').empty().text(municipality+", "+province);

                var SITE_URL = "{{url('/')}}";               
              
                $.ajax({
                    type: 'GET',
                    url: SITE_URL +"/get/generated/far"+ "/"+province+"/"+municipality ,
                    data: {
                        _token: "{{ csrf_token() }}",
                      
                    },
                    dataType: 'json',
                success: function(data){
                    if(data === "false"){
                        $('#generated_far').empty();
                        alert("NO PRE-GENERATED FAR");
                    }else{
                        var tab = "";
                    $.each(data, function (i, d) {
                    tab = tab+"<tr>";
                    tab = tab+"<td>"+d['name']+"</td>";
                    tab = tab+"<td>"+d['path']+"</td>";
                    tab = tab+"</tr>";
                    
                }); 
                
                $('#generated_far').empty().append(tab);

                    }
                   
                },
                error: function(data){
                    
                }
                });


                
            }



          $("#open_stored").on('click', function() {
             
                var provCode = document.getElementById("province_fg");
                var province = provCode.options[provCode.selectedIndex].text;
                var municipalCode = document.getElementById("municipality_fg");
                var municipality = municipalCode.options[municipalCode.selectedIndex].text;
 
                
                $('#location').empty().text(municipality+", "+province);


                var SITE_URL = "{{url('/')}}";               
              
                $.ajax({
                    type: 'GET',
                    url: SITE_URL +"/get/generated/far"+ "/"+province+"/"+municipality ,
                    data: {
                        _token: "{{ csrf_token() }}",
                      
                    },
                    dataType: 'json',
                success: function(data){
                    if(data === "false"){
                        $('#generated_far').empty();
                        alert("NO PRE-GENERATED FAR");
                    }else{
                        var tab = "";
                    $.each(data, function (i, d) {
                    tab = tab+"<tr>";
                    tab = tab+"<td>"+d['name']+"</td>";
                    tab = tab+"<td>"+d['path']+"</td>";
                    tab = tab+"</tr>";
                    
                }); 
                
                $('#generated_far').empty().append(tab);
                $("#downloaded_fars").modal("show");
                    }
                   
                },
                error: function(data){
                    
                }
                });






             

                
            });
    </script>




	<script type="text/javascript">



// $('input[type="checkbox"]').on("click", function(){
//     $('input[type="checkbox"]').removeAttr("checked");
//     $(this).prop('checked', true);
    

// });
     


$("#dl_excel_extra").hide("slow");
$("#download_flsar_ext").hide("slow");
$("#download_flsar_rep").hide("slow");


        <?php 

        if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->username == "r.benedicto" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga"){


        }else{
            ?> 
           // $("#download_flsar_a3").hide();
            <?php
        }

        ?>

		function isNumber(event){	
		var keycode =event.keyCode;
			if(keycode>47 && keycode<58){
				ifMax();
				return true;

			}	
				return false;
		}

		function ifMax(){
			 if(parseInt(document.getElementById("num_from").value) > parseInt(document.getElementById("num_from").max)){
			 	document.getElementById("num_from").value = document.getElementById("num_from").max;
			 }
//alert(document.getElementById("num_to").value);
			 if(parseInt(document.getElementById("num_to").value) > parseInt(document.getElementById("num_to").max)){
			 	document.getElementById("num_to").value = document.getElementById("num_to").max;
			 }
			 


		}
        $("#dl_excel_extra").on("click", function(){
            var province = $("#province_fg").val();
            var municipality = $("#municipality_fg").val();
                
           var muni_text =  $( "#municipality_fg option:selected" ).text();
          

        if(municipality == "--SELECT ASSIGNED MUNICIPALITY--"){
            alert("select municipality");
            return;
        }
            var url = "{{url('/')}}";
            var link = url+"/fargeneration/excel/identify/"+province+"/"+muni_text;
            window.open(link, "_blank");
        });


    

            //FAR GENERATION LOAD MUNICIPALITIES
				$('select[name="province_fg"]').on('change', function () {
					HoldOn.open(holdon_options);
					var provCode = $(this).val();
                    var regionName = "";
				$('select[name="municipality_fg"]').empty();
				$('input[name="hidden_region"]').empty();
                    

             
                        document.getElementById("open_stored").disabled = true;
               
			//alert(provCode);
					$.ajax({
						method: 'GET',
						url: 'ps/get_municipalities/' + provCode,
						data: {
							_token: _token,
							provCode: provCode
						},
						dataType: 'json',
						success: function (source) {
				$('select[name="municipality_fg"]').append('<option>--SELECT ASSIGNED MUNICIPALITY--</option>');
					$.each(source, function (i, d) {
                        regionName = d.regionName;

                      

						$('select[name="municipality_fg"]').append('<option value="' + d.prv + '">' + d.municipality + '</option>');
					}); 
                    
                    
                    // if(regionName == "CENTRAL LUZON"){
                    //         alert("No Farmers found");
                    //         $('select[name="municipality_fg"]').empty();
                    //         return;
                    //     }



                    if(regionName == "DAVAO" || regionName == "CAR" || regionName == "CAGAYAN VALLEY"){

                        // $("#dl_excel_extra").show("slow");
                        $("#dl_excel_extra").removeAttr("disabled");
                    }
                    else{
                        if(provCode == "PAMPANGA"){
                            // $("#dl_excel_extra").show("slow");
                            $("#dl_excel_extra").removeAttr("disabled");
                        }else if(provCode == "BATAAN"){
                            // $("#dl_excel_extra").show("slow");
                            $("#dl_excel_extra").removeAttr("disabled");  
                        }
                        else{
                            // $("#dl_excel_extra").hide("slow");
                            $("#dl_excel_extra").removeAttr("disabled");
                            $("#dl_excel_extra").attr("disabled", true);
                        }       
                    }
				}
				}); //AJAX GET MUNICIPALITY



		      //document.getElementById("download_flsar_a3_blank").disabled = true;
				HoldOn.close();
			});  //END PROVINCE SELECT



  //FAR GENERATION LOAD MUNICIPALITIES
                $('select[name="municipality_fg"]').on('change', function () {
                    HoldOn.open(holdon_options);
                    var municipality = $(this).val();
                    document.getElementById("open_stored").disabled = true;
                

                    if(municipality != "--SELECT ASSIGNED MUNICIPALITY--"){
                        document.getElementById("open_stored").disabled = false;
                    }


                    $('select[name="brgy_fg"]').empty();

                    $.ajax({
                        method: 'GET',
                        url: 'ps/get_brgy/' + municipality,
                        data: {
                            _token: _token,
                            municipality: municipality
                        },
                        dataType: 'json',
                        success: function (source) {
                            $('select[name="brgy_fg"]').empty().append('<option value="0">--SELECT ASSIGNED BRGY--</option>');
                            $('select[name="brgy_fg"]').append('<option value="all">--ALL BRGY--</option>');
                            $('select[name="brgy_fg"]').append('<option value="NONE">NO BRGY INDICATED</option>');
                        $.each(source, function (i, d) {
                        
                                
                            
                            $('select[name="brgy_fg"]').append('<option value="' + d.geocode_brgy + '">' + d.name + '</option>');
                        }); 

                   
                }
                }); //AJAX GET MUNICIPALITY


                HoldOn.close();
            });  //END PROVINCE SELECT









            var userRole = "{{ Auth::user()->roles->first()->name }}";
            $('#pre_reg').on('change', function () {
                var pre_reg = $("#pre_reg").is(":checked");

                if (userRole === "rcef-programmer") {
                    // Add any specific behavior for "rcef-programmer" if needed
                } else {
                    if (pre_reg) {
                        $('#download_flsar_a3').show(); // Show the button
                        $('#open_stored').hide();
                    } else {
                        $('#download_flsar_a3').hide(); // Hide the button
                        $('#open_stored').show();
                    }
                }
            });


$('select[name="brgy_fg"]').on('change', function () {
    HoldOn.open(holdon_options);
    //var municipal = $(this).val();
    var regionName = $('input[name="hidden_region"]').val();
   // var provCode = $('select[name="province_fg"]').text();    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
 
    var brgyCode = $(this).val();
       var check = 1;
   

       var pre_reg = $("#pre_reg").is(":checked");


        if(pre_reg){
            pre_reg = 1;
        }else{
            pre_reg = 0;
        }


     $.ajax({
        method: 'GET',
        url: 'ps/get_report_beneficiary'+'/'+provName+'/'+municipalName+'/'+brgyCode + "/"+pre_reg,
        data: {
            _token: _token,
            provName: provName,
            municipalName: municipalName,
            brgyCode: brgyCode,
            pre_reg: pre_reg
        },
        dataType: 'json',
        success: function (source) {
            

            var x = source - source + 1;
            var y = source;
            
            //alert(x);
            //alert(source.total_farmers);

            if(source <= 0){
                    document.getElementById("download_flsar_a3").disabled = true;
                   
                    
                    
                    document.getElementById("download_flsar_rep").disabled = true;
                     document.getElementById("download_flsar_ext").disabled = true;
                  
                    $("#download_flsar_rep").hide();
                    $("#download_flsar_ext").hide();
                    

                    document.getElementById("num_from").disabled = true;
                    document.getElementById("num_to").disabled = true;
                    document.getElementById("num_from").value = 0;
                    document.getElementById("num_to").value = 0;

                }else{
                    if(document.getElementById("download_flsar_a3")) document.getElementById("download_flsar_a3").disabled = false;
                    document.getElementById("download_flsar_rep").disabled = false;

                    $("#download_flsar_rep").hide();
                    $("#download_flsar_ext").hide();
                    
                    document.getElementById("download_flsar_ext").disabled = false;
                
                    document.getElementById("num_from").disabled = false;
                    document.getElementById("num_to").disabled = false;
                    
                    document.getElementById("num_from").value = x;
                    document.getElementById("num_to").value = y;
                    //object.addEventListener("click", myScript);
                }

            $('label[id="beneficiary"]').text(source);
            document.getElementById("num_from").max = source; 
            document.getElementById("num_to").max = source; 
       

              HoldOn.close();
        }
    }); 





  
});  //END MUNICIPALITY SELECT







document.getElementById("download_flsar_rep").addEventListener("click", function() {
  
  HoldOn.open(holdon_options);
 
     var size = "A3";
   
     var regionName = $('input[name="hidden_region"]').val();
     var mark_ebinhi = $("#mark_ebinhi").is(":checked");
 
 
     var provCode = document.getElementById("province_fg");
     var provName = provCode.options[provCode.selectedIndex].text;
 
     var municipalCode = document.getElementById("municipality_fg");
     var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
 
     var brgyCode = $('select[name="brgy_fg"]').val();
 
 
     var check = 1;
 
 
     var maxRow = document.getElementById("num_to").max;
 
     var rowFrom = $('input[name="num_from"]').val();
     var rowTo = $('input[name="num_to"]').val();
 
     if( parseInt(rowTo) >= parseInt(rowFrom)){
     
    
             window.open("prev/replacement"+"/"+provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo, "_blank"); 
         
 
   
       
 
     }else{
     alert("Row From should be greater than Row To");
     }    
     HoldOn.close();
 });  
 


document.getElementById("download_flsar_a3").addEventListener("click", function() {
    var province = $('select[name="province_fg"]').val();
    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
  
    $.ajax({
    method: 'GET',
    url: `getRunningFLSAR/${province}/${municipalName}`,
    success: function (source) {
        console.log(source);
        if(source == "running"){
            alert("Generation is running. Please check the downloaded FARs for reference.");
        }else{
            generateFLSAR_A3();
        }
    }
    });
});

function generateFLSAR_A3(){
    HoldOn.open(holdon_options);

    var size = "A3";
  
    var regionName = $('input[name="hidden_region"]').val();
    var mark_ebinhi = $("#mark_ebinhi").is(":checked");


    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;

    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;


    var user_account = "{{Auth::user()->roles->first()->name}}";
    var station = "{{Auth::user()->stationId}}";

  
    var prefix_db = "{{$GLOBALS['season_prefix']}}";
  


    var brgyCode = $('select[name="brgy_fg"]').val();
    var pre_reg = $("#pre_reg").is(":checked");

    // var is_transfer = $("#is_transfer").is(":checked");


    if(pre_reg){
        pre_reg = 1;
    }else{
        pre_reg = 0;
    }


    // if(user_account == "rcef-programmer" || station == "11005")
    // {

        if(pre_reg == 0){
            $.ajax({
                method: 'GET',
                url: "https://rcef-checker.philrice.gov.ph/public/rcef_id_generator/gen_far_ws2025/trustTheSyst3m" + "/" +prefix_db + "/"+provName+"/" + municipalName,
                data: {
                    _token: _token,
                    province: provName,
                    municipality: municipalName,
                },
                dataType: 'json',
                success: function (source) {
                    $("#open_stored").click();
                }
            });

                alert("Check From Time to Time the Downloaded FAR View for processing status");
                $("#open_stored").click();
                // window.open("https://rcef-checker.philrice.gov.ph/public/rcef_id_generator/gen_far/trustTheSyst3m" + "/" +prefix_db + "/"+provName+"/" + municipalName, "_blank"); 
                HoldOn.close();

                return;



        }

     
    // }

    is_transfer = 2;

    // if(is_transfer){
    //     is_transfer = 1;
    // }else{
    //     is_transfer = 0;
    // }

    var check = 1;


    var maxRow = document.getElementById("num_to").max;

    var rowFrom = $('input[name="num_from"]').val();
    var rowTo = $('input[name="num_to"]').val();

    if( parseInt(rowTo) >= parseInt(rowFrom)){
    
        if(mark_ebinhi){
            window.open("prev/pdf" + "/" +"mark"+"/"+provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo+ "/" + size + "/" + pre_reg + "/" + is_transfer, "_blank"); 
        }else {
            window.open("prev/pdf" + "/"+ "unmark"+"/"+provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo+ "/" + size + "/" + pre_reg + "/" + is_transfer , "_blank"); 
        }

    }else{
    alert("Row From should be greater than Row To");
    }    

    HoldOn.close();
}


document.getElementById("download_flsar_ext").addEventListener("click", function() {
  
 HoldOn.open(holdon_options);

    var size = "ext";

    var regionName = $('input[name="hidden_region"]').val();

    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;

    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;

    var brgyCode = $('select[name="brgy_fg"]').val();

    var check = 1;


    var maxRow = document.getElementById("num_to").max;

    var rowFrom = $('input[name="num_from"]').val();
    var rowTo = $('input[name="num_to"]').val();

    if( parseInt(rowTo) >= parseInt(rowFrom)){
    window.open("prev/pdf" + "/"+"mark"+"/" + provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo+ "/" + size, "_blank"); 
        
        
        

    }else{
    alert("Row From should be greater than Row To");
    }    
    HoldOn.close();
}); 





	</script>

@endpush