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
        					<input type="hidden" name="hidden_region" id="hidden_region" >

        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="province_fg" id="province_fg" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                      @foreach ($provinces_list as $province)
                                                <option value="{{ $province->provCode }}">{{ $province->provDesc }}</option>
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

                             <div class="form-group" style="font-size: 10px;">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                                 <div class="col-md-9 col-sm-9 col-xs-9">
                                    <div class="radio">
                                        <label>
                                            <input type="checkbox" name="rsbsa_checking_fg" id="rsbsa_checking_fg" value="yes" class="flat" style="-webkit-transform: scale(1, 1);" checked="true"> RSBSA FILTER CHECK
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Total Beneficiary</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <label class="control-label col-md-1 col-sm-3 col-xs-3" id="beneficiary">0</label>
                                </div>
                            </div>

							<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Download Row Range:</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <input type="number" size=7 name="num_from" value="0" id="num_from" max="0" onchange="ifMax()" onkeypress="return isNumber(event);" disabled=""> to 
                                    <input type="number" size=7 name="num_to" value="0" id="num_to" max="0" onchange="ifMax()" onkeypress="return isNumber(event);" disabled="">
                                </div>
                            </div>                            


                            <div class="form-group">
                                <div class="col-md-7">
                                    <button type="button" name="download_flsar" id="download_flsar" class="btn btn-lg btn-primary" style="float: right;" disabled=""><i class="fa fa-sign-in"></i> Download</button>
                                </div>
                            </div>



       	</div>
    </div>
@endsection
@push('scripts')

	<script type="text/javascript">
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
                url: 'fargeneration/get_municipalities/' + provCode,
                data: {
                    _token: _token,
                    provCode: provCode
                },
                dataType: 'json',
                success: function (source) {
        $('select[name="municipality_fg"]').append('<option>--SELECT ASSIGNED MUNICIPALITY--</option>');
            $.each(source, function (i, d) {
                $('select[name="municipality_fg"]').append('<option value="' + d.citymunCode + '">' + d.citymunDesc + '</option>');
            }); 
        }
        }); //AJAX GET MUNICIPALITY


    $.ajax({
        method: 'GET',
        url: 'fargeneration/get_region/' + provCode,
        data: {
            _token: _token,
            provCode: provCode
        },
        dataType: 'json',
        success: function (source) {
            $('input[name="hidden_region"]').val(source.regDesc);
            //RESET VALUE
            document.getElementById("download_flsar").disabled = true;
            document.getElementById("num_from").disabled = true;
            document.getElementById("num_to").disabled = true;
            document.getElementById("num_from").value = 0;
            document.getElementById("num_to").value = 0; 
            $('label[id="beneficiary"]').text(0);
            document.getElementById("num_from").max = 0; 
            document.getElementById("num_to").max = 0; 
            }
        }); 
        HoldOn.close();
    });  //END PROVINCE SELECT


$('select[name="municipality_fg"]').on('change', function () {
    HoldOn.open(holdon_options);
    //var municipal = $(this).val();
    var regionName = $('input[name="hidden_region"]').val();
   // var provCode = $('select[name="province_fg"]').text();    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
    var checkbox_rsbsa = true;
    var checkbox_rsbsa = document.getElementById("rsbsa_checking_fg").checked;
    var check ;
        if(checkbox_rsbsa==true){
            check = 1;
        }else{
            check = 0;
        }
   // alert(check);
   // die();
     $.ajax({
        method: 'GET',
        url: 'fargeneration/get_report_beneficiary/' + regionName+'/'+provName+'/'+municipalName+'/'+check,
        data: {
            _token: _token,
            regionName: regionName,
            provName: provName,
            municipalName: municipalName,
            checkbox_rsbsa: checkbox_rsbsa
        },
        dataType: 'json',
        success: function (source) {
            

            var x = source - source + 1;
            var y = source;
            
            //alert(x);
            //alert(source.total_farmers);

            if(source <= 0){
                    document.getElementById("download_flsar").disabled = true;
                    document.getElementById("num_from").disabled = true;
                    document.getElementById("num_to").disabled = true;
                    document.getElementById("num_from").value = 0;
                    document.getElementById("num_to").value = 0;

                }else{
                    document.getElementById("download_flsar").disabled = false;
                    document.getElementById("num_from").disabled = false;
                    document.getElementById("num_to").disabled = false;
                    
                    document.getElementById("num_from").value = x;
                    document.getElementById("num_to").value = y;
                    //object.addEventListener("click", myScript);
                }

            $('label[id="beneficiary"]').text(source);
            document.getElementById("num_from").max = source; 
            document.getElementById("num_to").max = source; 
        }
    }); 
    HoldOn.close();
});  //END MUNICIPALITY SELECT



$('input[name="rsbsa_checking_fg"]').on('change', function () {
    HoldOn.open(holdon_options);
    //var municipal = $(this).val();
    var regionName = $('input[name="hidden_region"]').val();
   // var provCode = $('select[name="province_fg"]').text();    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
    var checkbox_rsbsa = true;
    var checkbox_rsbsa = document.getElementById("rsbsa_checking_fg").checked;
    var check ;
        if(checkbox_rsbsa==true){
            check = 1;
        }else{
            check = 0;
        }
   // alert(check);
   // die();
     $.ajax({
        method: 'GET',
        url: 'fargeneration/get_report_beneficiary/' + regionName+'/'+provName+'/'+municipalName+'/'+check,
        data: {
            _token: _token,
            regionName: regionName,
            provName: provName,
            municipalName: municipalName,
            checkbox_rsbsa: checkbox_rsbsa
        },
        dataType: 'json',
        success: function (source) {
            

            var x = source - source + 1;
            var y = source;
            
            //alert(x);
            //alert(source.total_farmers);

            if(source <= 0){
                    document.getElementById("download_flsar").disabled = true;
                    document.getElementById("num_from").disabled = true;
                    document.getElementById("num_to").disabled = true;
                    document.getElementById("num_from").value = 0;
                    document.getElementById("num_to").value = 0;

                }else{
                    document.getElementById("download_flsar").disabled = false;
                    document.getElementById("num_from").disabled = false;
                    document.getElementById("num_to").disabled = false;
                    
                    document.getElementById("num_from").value = x;
                    document.getElementById("num_to").value = y;
                    //object.addEventListener("click", myScript);
                }

            $('label[id="beneficiary"]').text(source);
            document.getElementById("num_from").max = source; 
            document.getElementById("num_to").max = source; 
        }
    }); 
    HoldOn.close();
}); //checkbox


document.getElementById("download_flsar").addEventListener("click", function() {
  
 HoldOn.open(holdon_options);

        var regionName = $('input[name="hidden_region"]').val();

    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;

    var municipalCode = document.getElementById("municipality_fg");
    var municipalName = municipalCode.options[municipalCode.selectedIndex].text;

    var checkbox_rsbsa = true;
    var checkbox_rsbsa = document.getElementById("rsbsa_checking_fg").checked;
    var check ;
        if(checkbox_rsbsa==true){
            check = 1;
        }else{
            check = 0;
        }


    var maxRow = document.getElementById("num_to").max;

    var rowFrom = $('input[name="num_from"]').val();
    var rowTo = $('input[name="num_to"]').val();

    if( parseInt(rowTo) >= parseInt(rowFrom)){
    window.open("fargeneration/pre_list/all/"+ regionName + "/" + provName + "/" + municipalName + "/" + rowFrom + "/" + rowTo + "/" + maxRow + "/" +check+" #page=1&zoom=100,-215,612", "_blank"); 
    }else{
    alert("Row From should be greater than Row To");
    }    
    HoldOn.close();
});  

	</script>

@endpush