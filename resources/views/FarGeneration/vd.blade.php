@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h2>FLSAR PDF GENERATION (VALIDATED PROFILES RE-DEPLOYMENT)</h2>
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

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Mark Ebinhi Beneficiary</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="checkbox" name="mark_ebinhi" id="mark_ebinhi" checked class="form_control" style="width:20px; height:20px;">
                                </div>
                            </div>

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
                                 
                                    <button type="button" name="download_flsar_a3" id="download_flsar_a3" class="btn btn-lg btn-success" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF (A3)</button>
                                    {{-- <button type="button" name="download_flsar_cross" id="download_flsar_cross" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Cross Checking List</button> --}}
                                    <button type="button" name="download_flsar_ext" id="download_flsar_ext" class="btn btn-lg btn-warning" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download Extension List</button> 
                                    {{-- <button onclick="window.location.href='{{url("public/FAR")}}'" type="button" name="pre_downloaded" id="pre_downloaded" class="btn btn-lg btn-success" style="float: right;" ><i class="fa fa-file-pdf-o"></i> Pre-downloaded List</button> --}}
                                   
                                    @endif
                                </div>
                            </div>





       	</div>
    </div>
@endsection
@push('scripts')

	<script type="text/javascript">
        <?php 

        if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga"){


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


            //FAR GENERATION LOAD MUNICIPALITIES
				$('select[name="province_fg"]').on('change', function () {
					HoldOn.open(holdon_options);
					var provCode = $(this).val();

				$('select[name="municipality_fg"]').empty();
				$('input[name="hidden_region"]').empty();
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
						$('select[name="municipality_fg"]').append('<option value="' + d.prv + '">' + d.municipality + '</option>');
					}); 
				}
				}); //AJAX GET MUNICIPALITY


		      //document.getElementById("download_flsar_a3_blank").disabled = true;
				HoldOn.close();
			});  //END PROVINCE SELECT



  //FAR GENERATION LOAD MUNICIPALITIES
                $('select[name="municipality_fg"]').on('change', function () {
                    HoldOn.open(holdon_options);
                    var municipality = $(this).val();

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
                    $.each(source, function (i, d) {
                     
                            
                        
                        $('select[name="brgy_fg"]').append('<option value="' + d.geocode_brgy + '">' + d.name + '</option>');
                    }); 
                }
                }); //AJAX GET MUNICIPALITY


                HoldOn.close();
            });  //END PROVINCE SELECT










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
   // alert(check);
   // die();
     $.ajax({
        method: 'GET',
        url: 'vd/get_report_beneficiary'+'/'+provName+'/'+municipalName+'/'+brgyCode,
        data: {
            _token: _token,
            provName: provName,
            municipalName: municipalName,
            brgyCode: brgyCode
        },
        dataType: 'json',
        success: function (source) {
            

            var x = source - source + 1;
            var y = source;
            
            //alert(x);
            //alert(source.total_farmers);

            if(source <= 0){
                    document.getElementById("download_flsar_a3").disabled = true;
                     document.getElementById("download_flsar_ext").disabled = true;
                  
                    

                    document.getElementById("num_from").disabled = true;
                    document.getElementById("num_to").disabled = true;
                    document.getElementById("num_from").value = 0;
                    document.getElementById("num_to").value = 0;

                }else{
                    document.getElementById("download_flsar_a3").disabled = false;
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









document.getElementById("download_flsar_a3").addEventListener("click", function() {
  
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
    
        if(mark_ebinhi){
            window.open("vd/pdf" + "/" +"mark"+"/"+provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo+ "/" + size , "_blank"); 
        }else {
            window.open("vd/pdf" + "/"+ "unmark"+"/"+provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo+ "/" + size , "_blank"); 
        }

  
      

    }else{
    alert("Row From should be greater than Row To");
    }    
    HoldOn.close();
});  




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
    window.open("vd/pdf" + "/"+"mark"+"/" + provName + "/" + municipalName + "/" + brgyCode + "/" + rowFrom + "/" + rowTo+ "/" + size, "_blank"); 
        
        
        

    }else{
    alert("Row From should be greater than Row To");
    }    
    HoldOn.close();
}); 






	</script>

@endpush