@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>e-Binhi FLSAR PDF GENERATION</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            <div class="row">
         
        </div>
        					<input type="hidden" name="hidden_region" id="hidden_region" >

        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="province_fg" id="province_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                      @foreach ($provinces_list as $province)
                                                <option value="{{ $province->province_name }}">{{ $province->province_name}}</option>
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
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Barangay</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="brgy_fg" id="brgy_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a Barangay</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">DOP</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="dop_fg" id="dop_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a DOP</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Date Range</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                      <b> From: </b> <input type="text" name="date1" id="date1" class="form-control" value="{{date('m/01/Y')}}" />
                                      <b> To: </b> <input type="text" name="date2" id="date2" class="form-control" value="{{date('m/d/Y')}}" />
                                </div>
                            </div>



                            <div class="form-group">
                                <div class="col-md-7">
                                    <button type="button" name="download_flsar" id="download_flsar" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-sign-in"></i> Download e-Binhi FAR</button>
                                    <button type="button" name="download_flsar_ext_e" id="download_flsar_ext_e" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-sign-in"></i> Download Extension FAR</button>

                                    <button type="button" name="download_flsar_prereg" id="download_flsar_prereg" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-sign-in"></i> Download Pre-Registration FAR</button>
                                </div>
                            </div>








       	</div>
    </div>
@endsection
@push('scripts')
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
	<script type="text/javascript">
         $("#date1").datepicker();
        $("#date2").datepicker();

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
                method: 'POST',
                url: "{{route('FarGeneration.ebinhi.get_municipalities')}}",
                data: {
                    _token: _token,
                    province: provCode
                },
                dataType: 'json',
                success: function (source) {
        $('select[name="municipality_fg"]').append('<option value="0">--SELECT ASSIGNED MUNICIPALITY--</option>');
            $.each(source, function (i, d) {
                $('select[name="municipality_fg"]').append('<option value="' + d.municipality_name + '">' + d.municipality_name + '</option>');
            }); 
        }
        }); //AJAX GET MUNICIPALITY

        HoldOn.close();
    });  //END PROVINCE SELECT

    

$('select[name="municipality_fg"]').on('change', function () {
    HoldOn.open(holdon_options);
    //var municipal = $(this).val();
    var regionName = $('input[name="hidden_region"]').val();
    var provCode = $('select[name="province_fg"]').val();    
    var municipality = $('select[name="municipality_fg"]').val();

    //alert(provCode+" "+municipality);
    if(municipality === "0"){
        document.getElementById("download_flsar").disabled = true;
        document.getElementById("download_flsar_ext_e").disabled = true;
        
    }else{
        document.getElementById("download_flsar").disabled = false;
        document.getElementById("download_flsar_ext_e").disabled = false;
        
    }


           $.ajax({
                method: 'POST',
                url: "{{route('FarGeneration.ebinhi.get_brgy')}}",
                data: {
                    _token: _token,
                    province: provCode,
                    municipality: municipality
                },
                dataType: 'json',
                success: function (source) {
                    $('select[name="brgy_fg"]').empty();
                    $('select[name="brgy_fg"]').append('<option value="0">--SELECT ASSIGNED BRGY--</option>');
                        $.each(source, function (i, d) {
                            if(i==0){
                                $('select[name="brgy_fg"]').append('<option value="all">--ALL BRGY--</option>');
                            }
                            $('select[name="brgy_fg"]').append('<option value="' + d.code + '">' + d.barangay + '</option>');
                        }); 
                    }
        }); //AJAX GET MUNICIPALITY 
    HoldOn.close();
});  //END MUNICIPALITY SELECT



$('select[name="brgy_fg"]').on('change', function () {
    HoldOn.open(holdon_options);
    //var municipal = $(this).val();
    var regionName = $('input[name="hidden_region"]').val();
    var provCode = $('select[name="province_fg"]').val();    
    var municipality = $('select[name="municipality_fg"]').val();
    var brgy = $('select[name="brgy_fg"]').val();
    //alert(provCode+" "+municipality);
    if(municipality === "0"){
        document.getElementById("download_flsar").disabled = true;
        document.getElementById("download_flsar_ext_e").disabled = true;
        
    }else{
        document.getElementById("download_flsar").disabled = false;
        document.getElementById("download_flsar_ext_e").disabled = false;
        
    }


           $.ajax({
                method: 'POST',
                url: "{{route('FarGeneration.ebinhi.get_dop')}}",
                data: {
                    _token: _token,
                    province: provCode,
                    municipality: municipality,
                    brgy: brgy
                },
                dataType: 'json',
                success: function (source) {
                     $('select[name="dop_fg"]').empty();
        $('select[name="dop_fg"]').append('<option value="0">--SELECT ASSIGNED DOP--</option>');
            $.each(source, function (i, d) {


                $('select[name="dop_fg"]').append('<option value="' + d.drop_off_point + '">' + d.drop_off_point + '</option>');
            }); 
        }
        }); //AJAX GET MUNICIPALITY







        if(brgy != "0"){
            $("#download_flsar_prereg").removeAttr("disabled");
        }else{
            $("#download_flsar_prereg").removeAttr("disabled");
            $("#download_flsar_prereg").attr("disabled", "true");
        }


    
 
    HoldOn.close();
});  //END MUNICIPALITY SELECT





document.getElementById("download_flsar_prereg").addEventListener("click", function() {
  
            
            
              
                var provCode = document.getElementById("province_fg");
                var provName = provCode.options[provCode.selectedIndex].text;
            
                var municipalCode = document.getElementById("municipality_fg");
                var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
            
                var brgy = $('select[name="brgy_fg"]').val();
            
                if(brgy == "0"){
                    alert("Please select barangay");
                }

            
                //rowFrom = 0;
                //rowTo = 0;
              
                window.open("preregistration/"+ provName + "/" + municipalName +"/"+brgy , "_blank" ); 
                 
 });  
 






document.getElementById("download_flsar").addEventListener("click", function() {
  
 HoldOn.open(holdon_options);

        var regionName = $('input[name="hidden_region"]').val();

    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;

    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;

    var brgy = $('select[name="brgy_fg"]').val();
    var dop = $('select[name="dop_fg"]').val();
  
    var maxRow = 10;

    var fromdate = $("#date1").val();
    var todate = $("#date2").val();

    fromdate = fromdate.replace("/","-");
    todate = todate.replace("/","-");
    fromdate = fromdate.replace("/","-");
    todate = todate.replace("/","-");
    var rowFrom = fromdate;
    var rowTo = todate;

    //rowFrom = 0;
    //rowTo = 0;
    if( parseInt(rowTo) >= parseInt(rowFrom)){
    window.open("ebinhi/pdf/"+ provName + "/" + municipalName +"/"+brgy+"/"+dop+"/"+ rowFrom + "/" + rowTo + "/A3" , "_blank" ); 
    }else{
    alert("Row From should be greater than Row To");
    }    
    HoldOn.close();
});  



document.getElementById("download_flsar_ext_e").addEventListener("click", function() {
  
 HoldOn.open(holdon_options);

        var regionName = $('input[name="hidden_region"]').val();

    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;

    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;

    var brgy = $('select[name="brgy_fg"]').val();
    var dop = $('select[name="dop_fg"]').val();
  
    var maxRow = 10;

    var fromdate = $("#date1").val();
    var todate = $("#date2").val();

    fromdate = fromdate.replace("/","-");
    todate = todate.replace("/","-");
    fromdate = fromdate.replace("/","-");
    todate = todate.replace("/","-");
    var rowFrom = fromdate;
    var rowTo = todate;

    if( parseInt(rowTo) >= parseInt(rowFrom)){
    window.open("ebinhi/pdf/"+ provName + "/" + municipalName +"/"+brgy+"/"+dop+ "/" + rowFrom + "/" + rowTo + "/ext" , "_blank" ); 
    }else{
    alert("Row From should be greater than Row To");
    }    
    HoldOn.close();
});  



	</script>

@endpush