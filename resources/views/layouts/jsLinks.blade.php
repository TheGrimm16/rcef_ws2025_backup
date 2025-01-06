{{-- <script src="{{ asset('public/js/jquery.min.js') }}"></script> --}}
<script src="{{ asset('public/js/app.js') }}"></script>
<script src="{{ asset('public/js/jquery.mask.js') }}"></script>
<script src="{{ asset('public/js/mask.test.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

<script src=" {{ asset('public/js/dialog/jquery-confirm.min.js') }} "></script>


<script>
	$("#stat_date1").datepicker();
    $("#stat_date2").datepicker();


    function UrlExists(url)
    {
        var http = new XMLHttpRequest(); 
        http.open('HEAD', url, false); 
                http.send(); 
                if (http.status === 200) { 
                    return 1; 
                } else { 
                    return 0; 
                }
    }



    function genBlankFAR(size){
        var pages = prompt("How many pages?", "1");
            pages = parseInt(pages);

            if(pages > 0){
                var base_link = "{{url('/')}}";

window.open(base_link+'/fargeneration/new/blank/pdf/'+size+'/'+pages, '_blank');

            }

       
    }

    $("#generate_municipal_statistics").on("click", function(e){
        var date_1 = $("#stat_date1").val();
        var date_2 = $("#stat_date2").val();
        date_1 = date_1.replace("/", "-");
        date_1 = date_1.replace("/", "-");
        date_2 = date_2.replace("/", "-");
        date_2 = date_2.replace("/", "-");

  
        var url = "{{url('report/export/municipal/statistics')}}";
        url = url+"/"+date_1+"/"+date_2+"/all";
        
        window.open(url, '_blank');


    });



    $("#generate_flsar_blank_btn").on("click", function(e){
        var base_link = "{{url('/')}}";
        var pages = $("#blank_page").val();
        var redirectWindow = window.open(base_link+'/farmer/pre_list/blank/'+pages, '_blank');
        redirectWindow.location;
    });
    

    $("#generate_flsar_btn_excel").on("click", function(e){
        //alert("generate excel button");
       
        var municipal_str = $("#flsar_municipality").val();
        var prv_dop = municipal_str.substr(0,6);

        var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2021/flsar/excel/'+prv_dop;
        var redirectWindow = window.open(url, '_blank');
        redirectWindow.location;
    });

    function getRegionName(code){
        var data = [];
        data["01"] = "ILOCOS";
        data["02"] = "CAGAYAN VALLEY";
        data["03"] = "CENTRAL LUZON";
        data["04"] = "CALABARZON";
        data["17"] = "MIMAROPA";
        data["05"] = "BICOL";
        data["06"] = "WESTERN VISAYAS";
        data["07"] = "CENTRAL VISAYAS";
        data["08"] = "EASTERN VISAYAS";
        data["09"] = "ZAMBOANGA PENINSULA";
        data["10"] = "NORTHERN MINDANAO";
        data["11"] = "DAVAO";
        data["12"] = "SOCCSKSARGEN";
        data["13"] = "NCR";
        data["14"] = "CAR";
        data["15"] = "BARMM";
        data["16"] = "CARAGA";        
        return data[code];
    }







    $("#generate_flsar_btn").on("click", function(e){
        var pdf_path = $("#flsar_municipality").val();
       
        if(pdf_path == "0"){
            alert("Please select a municipality");
        }else{
            var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2021/public/flsar/'+pdf_path;
             //alert(url);
            var file_check = UrlExists(url);
            // alert(file_check);
              var regCode = pdf_path.substr(0,2);
            var regName = getRegionName(regCode);   
            var municipalCode = document.getElementById("flsar_municipality");
            var municipalName = municipalCode.options[municipalCode.selectedIndex].text;
            var proMun = municipalName.split(' < ');
          // alert(proMun[0]);
            if(file_check == 0){
                var pdf_path2 = regName + '/' + proMun[0] + '/' + proMun[1];

               // alert(pdf_path2);
                var url2 = 'https://rcef-seed.philrice.gov.ph/rcef_ws2021/public/flsar/_FLSAR_BATCH/'+pdf_path2;
                     var file_check2 = UrlExists(url2);
                    // alert(file_check2);
                        if (file_check2 ==0){
                        alert("The FLSAR of the selected municipality has not yet completed processing (server-side) please try again at a later time.");
                        }else{
                             var redirectWindow = window.open(url2, '_blank');
                             redirectWindow.location;
                        }
            
            
            }else{
                //var redirectWindow = window.open('http://localhost/rcef/farmer/pre_list/'+pdf_path, '_blank');
                var redirectWindow = window.open(url, '_blank');
                redirectWindow.location;
            }
        }
    });












    $('#flsar_modal').on('show.bs.modal', function (e) {
        $("#flsar_municipality").select2();
        $("#flsar_municipality").empty().append("<option value='0'>Loading municipalities...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('pre_list.municipalities') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                $("#flsar_municipality").empty().append(data);
            },
            error: function(data){
                alert("An error occured while processing your data, please try again.");
            }
        });
    });
	
    $("#change_pass_btn").on("click", function(e){
        var new_pass = $("#user_new_password").val();
        var confirm_pass = $("#user_confirm_password").val();

        $("#change_pass_btn").attr('disabled', '');
        $("#change_pass_btn").empty().html("Processing Request");
        if(new_pass != "" && confirm_pass != ""){
            if(new_pass == confirm_pass){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('users.change_password') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        new_pass: new_pass,
                        confirm_pass: confirm_pass
                    },
                    success: function(data){
                        $("#user_new_password").val('');
                        $("#user_confirm_password").val('');
                        
                        alert("You have successfully changed your password. please enter your new credentials on your next login, thank you.");
                        $("#change_pass_btn").removeAttr('disabled');
                        $("#change_pass_btn").empty().html('<i class="fa fa-unlock"></i> Confirm Change Password');
                    }, 
                    error: function(e){
                        $("#user_new_password").val('');
                        $("#user_confirm_password").val('');

                        alert("The server encountered an error while changing your password, please try again.");
                        $("#change_pass_btn").removeAttr('disabled');
                        $("#change_pass_btn").empty().html('<i class="fa fa-unlock"></i> Confirm Change Password');
                    }
                });
            }else{
                $("#user_new_password").val('');
                $("#user_confirm_password").val('');

                alert("Password Mismatch.");
                $("#change_pass_btn").removeAttr('disabled');
                $("#change_pass_btn").empty().html('<i class="fa fa-unlock"></i> Confirm Change Password');
            }   
        }else{
            $("#user_new_password").val('');
            $("#user_confirm_password").val('');

            alert("Please fill-up all fields.");
            $("#change_pass_btn").removeAttr('disabled');
            $("#change_pass_btn").empty().html('<i class="fa fa-unlock"></i> Confirm Change Password');
        }
    });
	
	$('#paymaya_tags_modal').on('show.bs.modal', function (e) {
        $("#seedTag_paymaya").select2({
            ajax: {
                url: "{{ route('paymaya.list.seedTags.search') }}",
                dataType: 'json',
                type: "GET",
                quietMillis: 50,
                data: function (params) {
                    return {
                        searchTerm: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });
    });

    $("#flag_qr_btn").on("click", function(e){
        //check required fields
        var tag = $("#seedTag_paymaya").val();
        var qr_code = $("#qr_code_paymaya").val();
        $("#flag_qr_btn").empty().html("executing action, please wait...");
        $("#flag_qr_btn").attr("disabled", "");

        if(tag == "0" || qr_code == ""){
            alert("please supply all the required data for input.");
            $("#flag_qr_btn").empty().html("<i class='fa fa-flag'></i> Flag as unusable");
            $("#flag_qr_btn").removeAttr("disabled");
        }else{
            //go save
            $.ajax({
                type: 'POST',
                url: "{{ route('paymaya.seedTags.flag') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    tag: tag,
                    qr_code: qr_code
                },
                success: function(data){
                   if(data == "flag_success"){
                        alert("QR code successfully tagged as unusable");
                        $("#flag_qr_btn").empty().html("<i class='fa fa-flag'></i> Flag as unusable");
                        $("#flag_qr_btn").removeAttr("disabled");
                   }else{
                        alert("An error occured while processing your data, please try again.");
                        $("#flag_qr_btn").empty().html("<i class='fa fa-flag'></i> Flag as unusable");
                        $("#flag_qr_btn").removeAttr("disabled");
                   }
                },
                error: function(data){
                    alert("An error occured while processing your data, please try again.");
                    $("#flag_qr_btn").empty().html("<i class='fa fa-flag'></i> Flag as unusable");
                    $("#flag_qr_btn").removeAttr("disabled");
                }
            });
        }
    });


 
	
	// FOR NRP GENERATION
    $('#export_nrp').on('show.bs.modal', function (e) {
        $("#nrp_province").empty().append("<option value='0'>Loading municipalities...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('nrp.provinces') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                $("#nrp_province").empty().append(data);
            },
            error: function(data){
                alert("An error occured while processing your data, please try again.");
            }
        });
    });

    $("#generate_nrp_btn").on("click", function(e){       
        var nrp_province = $("#nrp_province").val();
        var url = 'https://rcef-seed.philrice.gov.ph/rcef_ws2021/nrp/export/'+nrp_province;
        var redirectWindow = window.open(url, '_blank');
        redirectWindow.location;
    });
	


     //UTILITY REPRINT IAR
    $('#iar_print_log').on('show.bs.modal', function (e) {
        $('label[id="iar_province"]').text('N/A');
        $('label[id="iar_municipality"]').text('N/A');
        $('label[id="iar_volume"]').text('N/A');
        $('label[id="iar_delDate"]').text('N/A');
        $("#util_iarbatch").select2();
        $("#util_iarbatch").empty().append("<option value='0'>Loading Batch Number...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('pre_list.iar_list') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                $("#util_iarbatch").empty().append(data);
            },
            error: function(data){
                alert("An error occured while processing your data, please try again.");
                //alert(data);
            }
        });
    }); 


    $('select[name="util_iarbatch"]').on('change', function () {
    HoldOn.open(holdon_options);
   
    var batchNumber = $(this).val();
   //alert(batchNumber);
    $('label[id="iar_province"]').text('N/A');
        $('label[id="iar_municipality"]').text('N/A');
        $('label[id="iar_volume"]').text('N/A');
        $('label[id="iar_delDate"]').text('N/A');
   if(batchNumber !== "0"){
      // die();


        
      var base_link = "{{url('/')}}";
                  
        $.ajax({
           method: 'GET',
           url: base_link+'/utility/pullIarInfo/' + batchNumber, 
           data: {
               _token: _token,
               batchNumber: batchNumber
           },
           dataType: 'json',
           success: function (source) {
            
               $('label[id="iar_province"]').text(source.province);
                $('label[id="iar_municipality"]').text(source.municipality);
               $('label[id="iar_volume"]').text(source.sumBags);
               $('label[id="iar_delDate"]').text(source.deliveryDate);
                  
           }
       }); 
    }else{
        
    }

    HoldOn.close();
});  

    $("#reset_iar").on("click", function(e){
   

        var batchNumberID = $('select[name="util_iarbatch"]').val();


        if(batchNumberID !== "0"){
              var yesno = confirm("Reset IAR : "+batchNumberID+" ?");
                if(yesno==true){           
                
                    var base_link = "{{url('/')}}";
                    
            //alert(batchNumberID);
             $.ajax({
                method: 'GET',
                url: base_link+'/utility/reprint_iar/' + batchNumberID,
                data: {
                    _token: _token,
                    batchNumberID: batchNumberID
                },
                dataType: 'json',
                success: function(data){
                           
                            alert("You May now Reprint IAR");
                            //$("#iar_print_log").modal("hide");
                            //$("#iar_print_log").modal("show");
                            
                        },
                error: function(data){
                            alert("An error occured while processing your data, please try again.");
                        }
                    }); 
                }
            }else{
                alert(".....Please select batch number.....");
            }
          
    });














	
    //UTILITY CANCEL DELIVERY
    $('#utilDel_modal').on('show.bs.modal', function (e) {
        $('label[id="util_moa"]').text('N/A');
        $('label[id="util_volume"]').text('N/A');
        $('label[id="util_dop"]').text('N/A');
        $('label[id="util_delDate"]').text('N/A');
        $("#util_batchNumber").select2();
        $("#util_batchNumber").empty().append("<option value='0'>Loading Batch Number...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('pre_list.batchNumber') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
                $("#util_batchNumber").empty().append(data);
            },
            error: function(data){
                alert("An error occured while processing your data, please try again.");
                //alert(data);
            }
        });
    }); 


   
    $('select[name="util_batchNumber"]').on('change', function () {
    HoldOn.open(holdon_options);
   
    var batchNumber = $(this).val();
   
   if(batchNumber>0){
      // die();
        $.ajax({
           method: 'GET',
           url: 'utility/pullDelInfo/' + batchNumber,
           data: {
               _token: _token,
               batchNumber: batchNumber
           },
           dataType: 'json',
           success: function (source) {
               //alert(source.moa_number +' '+source.accreditation_no);
               $('label[id="util_volume"]').text(source.instructed_delivery_volume);
               $('label[id="util_delDate"]').text(source.delivery_date);
               
               var dop = source.prv_dropoff_id;
               var moa = source.moa_number;
               $.ajax({
                    method: 'GET',
                    url: 'utility/pullDopInfo/' + dop +'/'+ moa,
                    data: {
                        dop: dop,
                        moa: moa
                    },
                    dataType: 'json',
                    success: function (source) {
                    $('label[id="util_moa"]').text(source[1]);
                    $('label[id="util_dop"]').text(source[0]);

                    }
                });
           }
       }); 
    }else{
        $('label[id="util_moa"]').text("N/A");
        $('label[id="util_acre"]').text("N/A");
    }

    HoldOn.close();
});  



     $("#cancel_delivery_btn").on("click", function(e){
         HoldOn.open(holdon_options);

        var batchNumber = document.getElementById("util_batchNumber");
        var batchNumberText = batchNumber.options[batchNumber.selectedIndex].text;
        var batchNumberID = batchNumber.value;
//alert(batchNumber);
        if(batchNumberID>0){
              var yesno = confirm("Cancel "+batchNumberText+" ?");
                if(yesno==true){           
                
            //alert(batchNumberID);
             $.ajax({
                method: 'GET',
                url: 'utility/cancel_delivery/process/' + batchNumberID,
                data: {
                    _token: _token,
                    batchNumberID: batchNumberID
                },
                dataType: 'json',
                success: function(data){
                            
                            //$('#utilDel_modal').on('hide.bs.modal');
                            $("#utilDel_modal").modal("hide");
                            alert("DELIVERY CANCELLED!");
                            $("#utilDel_modal").modal("show");
                            
                        },
                error: function(data){
                            alert("An error occured while processing your data, please try again.");
                        }
                    }); 
                }
            }else{
                alert(".....Please select batch number.....");
            }
            HoldOn.close();
    });

    //END UTILITY CANCEL DELIVERY


    $("#search_button_rla").on("click", function(e){ 
        var rla_lab =  $("#find_lab").val();
        var rla_lot =  $("#find_lot").val();

                $('#rla_found').DataTable({
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
                        "url": "{{route('rla.monitoring.find_rla')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",   
                            "lab" : rla_lab,
                            "lot" : rla_lot,
                            
                        }
                    },
                    "columns":[
                        {"data": "season_checked"},
                        {"data": "coop_name"},
                        {"data": "sg_name"},
                        {"data": "batchTicketNumber"},
                        {"data": "labNo"},
                        {"data": "lotNo"},
                        {"data": "seedVariety"},
                        {"data": "noOfBags"},
                     
                        {"data": "last_season"},
                        {"data": "curr_season"},
                        {"data": "isBuffer"},
                        {"data": "second_inspect"}
                    ]
                });


        
    });
           
            

        

	
	
	
	
</script>

@stack('scripts')
