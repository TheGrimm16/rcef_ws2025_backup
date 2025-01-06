@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>RCEF ID Generation </h3>
            </div>
        </div>

            <div class="clearfix"></div>

        
            <div class="row">
                <div class="alert alert-danger alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong><i class="fa fa-info-circle"></i> Notice!</strong> The currently allowed station to download FAR are:
                     <ul>
                        @foreach($stations as $stat)
                        <li>{{strtoupper($stat)}} </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        <div class="card">
            <div class="col-md-12">

            <div class="col-md-4">
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <br>
                        <label for="utilProvince" id="label_province">Province  </label>
                        <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1">

                            <option value="0">Please select a province</option>
                            @foreach ($provinces as $provinces)
                            <option value="{{$provinces->province}}">{{$provinces->province}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
           
           
                <div class="form-group">
            
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label for="utilMunicipality" id="label_municipality">Municipality</label>
                        <select name="utilMunicipality" id="utilMunicipality" class="form-control" data-parsley-min="1" >
                            <option value="0">Please select a municipality</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
            
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label for="utilBrgy" id="label_brgy">Barangay</label>
                        <select name="utilBrgy" id="utilBrgy" class="form-control" data-parsley-min="1" >
                            <option value="0_no">Please select a Barangay</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    {{-- <div class="col-md-12" style="text-align:center; margin-top:5px; color: ">                        
                        <button type="button" name="open_stored_zip" id="open_stored_zip" class="btn btn-md" style=" width:200px; background-color: #e7e7e7; color: black;" ><i class="fa fa-file-archive-o" aria-hidden="true"></i> Download Stored ID ZIP</button>
                    </div> --}}

                    <div class="col-md-12" style="text-align:center; margin-top:5px; margin-bottom:10px;">                        
                        <button type="button" name="open_stored" id="open_stored" class="btn btn-md btn-warning" style=" width:200px;" ><i class="fa fa-folder-open" aria-hidden="true"></i> Download Stored ID </button>                    
                    </div>
                    

                    <div class="col-md-12" style="text-align:center; margin-top:5px; margin-bottom:10px;">
                         <button type="button" name="genId" id="genId" class="btn btn-md btn-primary" style=" width:200px;" ><i class="fa fa-print" aria-hidden="true"></i> GENERATE NEW ID </button>
                    </div>

                    <div class="col-md-12" style="text-align:center; margin-top:5px;">
                        <button  data-toggle='modal' data-target='#reprinting_modal' id="re_print"  type="button" class="btn btn-md btn-success" style="width:150px;margin: 5px;" ><i class="fa fa-repeat" aria-hidden="true"></i> Re-Print RCEF ID </button>
                    </div>

                </div>
            </div>
        

            <div class="col-md-4" style="text-align:center;"> 
                <img src="{{asset('public/images/id_front.png')}}" style="height:auto;width:70%;margin:20px; border:solid black 1px;"/>
            </div>

            <div class="col-md-4" >
                <img src="{{asset('public/images/id_back.png')}}" style=" height:auto;width:70%;margin:20px; border:solid black 1px;"/>
            </div>
            
            </div>

        </div>


        <div class="x_content form-horizontal form-label-left">
                




                            <div class="form-group">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                                            <thead>
                                                <th style="width: 100px;">Province</th>
                                                <th style="width: 100px;">Municipality</th>
                                                <th style="width: 100px;">Brgy</th>
                                                
                                                <th style="width: 100px;">Total Unique</th>
                                                
                                        
                                                <th style="width: 50px;">Total Assigned Id(s)</th>
                                                <th style="width: 50px;">Percentage (%)</th>
                                                
                                            </thead>
                                            <tbody id='databody'>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
        </div>
    </div>
   

    <div id="reprinting_modal" class="modal fade" role="dialog" >
        <div class="modal-dialog" style="width: 27%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">
                        <span>RCEF ID Re-Printing</span><br>
                    </h4>
                </div>
    
                <div class="modal-body">
                    
                    <div class="col-xs-12">
                        <label for="" class="col-md-12"  style="color: #f3a72d; font-size: 18px;"><strong>Fill up Farmer Information </strong> </label>
                        <label for="modal_rsbsa" class="col-xs-3" id="label_rsbsa">RSBSA #:</label>
                        <input type="text" style="width: 250px;" class="form-control" name="modal_rsbsa" id="modal_rsbsa" placeholder="RSBSA number"> <br>
                        <label for="modal_fname" class="col-xs-3" id="label_first">First Name:</label>
                        <input type="text" style="width: 250px;" class="form-control" name="modal_fname" id="modal_fname" placeholder="First Name"> <br>
                        <label for="modal_lname" class="col-xs-3" id="label_last">Last Name: </label>
                        <input type="text" style="width: 250px;" class="form-control" name="modal_lname" id="modal_lname" placeholder="Last Name"> <br> 
                       
    
                        <label for="modal_birthdate" class="col-xs-3" id="label_bday">Birthdate: </label>
                        <input type="text" style="width: 250px;" class="form-control" name="modal_birthdate" id="modal_birthdate" autocomplete="off" value="{{date("m/d/Y")}}"> <br> 
                         
                    </div>
    
                </div>
                <div class="modal-footer" id="modal_footer">      
                    <button class="btn btn-success btn-md" id="re_print" name="re_print"> <i class="fa fa fa-print" aria-hidden="true"></i> Re-Print ID</button>
                </div>
            </div>
        </div>
    </div>


    <div id="stored_ids" class="modal fade" role="dialog" >
        <div class="modal-dialog" style="width: 40%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">
                        <span>Generated ID</span><br>
                    </h4>
                </div>
    
                <div class="modal-body">
                    <div class="col-xs-12">
                        <label for="" class="col-md-12"  style="color: #f3a72d; font-size: 18px;" id="location"><strong></strong> </label>
                      
                        <table class="table table-hover table-striped table-bordered">
                            <tr>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                            <tbody id='rcef_id_body'>
                                                
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

    <script type="text/javascript">
              
        $("#re_print").hide("slow");

            $("#re_print").on('click', function() {
                    var rsbsa = $("#modal_rsbsa").val();
                    var first = $("#modal_fname").val();
                    var last = $("#modal_lname").val();
                    var bday = $("#modal_birthdate").val();
                    var find = 1;

                if(rsbsa === ""){
                    $("#label_rsbsa").attr("style", "color: red;");
                    $("#label_rsbsa").effect("bounce", "", 500);
                    find = 0;
                }else{
                    $("#label_rsbsa").removeAttr("style");
                }

                if(first === ""){
                    $("#label_first").attr("style", "color: red;");
                    $("#label_first").effect("bounce", "", 500);
                    find = 0;

                }else{
                    $("#label_first").removeAttr("style");
                }

                if(last === ""){
                    $("#label_last").attr("style", "color: red;");
                    $("#label_last").effect("bounce", "", 500);
                    find = 0;
                }else{
                    $("#label_last").removeAttr("style");
                }

                if(bday === '{{date("m/d/Y")}}'){
                    $("#label_bday").attr("style", "color: red;");
                    $("#label_bday").effect("bounce", "", 500);
                    find = 0;
                }else{
                    $("#label_bday").removeAttr("style");
                }

                
                if(find == 1){
                 

                    $.ajax({
                            type: 'POST',
                            url: "{{route('rcef.id.reprint')}}" ,
                            data: {
                                _token: "{{ csrf_token() }}",
                                rsbsa : rsbsa,
                                first : first,
                                last : last,
                                bday : bday,
                            },
                            dataType: 'json',
                            success: function(data){
                                if(data == "NO_DB"){
                                    $("#re_print").empty().text("No Records Found");

                                    $("#re_print").effect("shake", "", 500);

                                    setTimeout(reButton, 5000);

                                }else{
                                    var municipality = "1";
                                    var brgy_name = data["rcef_id"];
                                    var type = "reprint";

                                    var SITE_URL = "{{url('/')}}";
                                    window.open(SITE_URL+'/create/rcef/id/card/'+data["prv_code"]+'/'+municipality+'/'+brgy_name+'/'+type,"_blank");       
                                }

                 

                            },
                            error: function(data){
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });
                }else{
                    
                }
            });

           function reButton(){
            $("#re_print").empty().append('<i class="fa fa fa-print" aria-hidden="true"></i> Re-Print ID');
           }

            $('#reprinting_modal').on('show.bs.modal', function (e) {
                $("#modal_rsbsa").val("");
                $("#modal_fname").val("");
                $("#modal_lname").val("");
                $("#modal_birthdate").val('{{date("m/d/Y")}}');
                    
            });

            $("#open_stored").on('click', function() {
                var province = $("#utilProvince").val();
                var municipality = $("#utilMunicipality").val();
                var brgy_name = $("#utilBrgy").val();
                
                if(province == 0){
                    $("#label_province").attr("style","color:red;");
                    $("#label_province").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_province").removeAttr("style");
                }
                if(municipality == 0){
                    $("#label_municipality").attr("style","color:red;");
                    $("#label_municipality").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_municipality").removeAttr("style");
                }

                if(brgy_name == 0){
                    $("#label_brgy").attr("style","color:red;");
                    $("#label_brgy").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_brgy").removeAttr("style");
                }

                $('#location').empty().text(brgy_name+" "+municipality+", "+province);
                


                var SITE_URL = "{{url('/')}}";               
                var season = "{{$GLOBALS['season_prefix']}}";
                $.ajax({
                    type: 'GET',
                    url: SITE_URL +"/get/generated/id"+ "/"+province+"/"+municipality+"/"+brgy_name+"/"+season ,
                    data: {
                        _token: "{{ csrf_token() }}",
                      
                    },
                    dataType: 'json',
                success: function(data){
                    if(data === "NO DOWNLOADED IDS"){
                        $('#rcef_id_body').empty();
                        alert("NO PRE-GENERATED IDS");
                    }else{
                        var tab = "";
                    $.each(data, function (i, d) {
                    tab = tab+"<tr>";
                    tab = tab+"<td>"+d['name']+"</td>";
                    tab = tab+"<td>"+d['path']+"</td>";
                    tab = tab+"</tr>";
                    
                }); 
                
                $('#rcef_id_body').empty().append(tab);
                $("#stored_ids").modal("show");
                    }
                   
                },
                error: function(data){
                    
                }
                });






             

                
            });




            $("#open_stored_zip").on('click', function() {
                var province = $("#utilProvince").val();
                var municipality = $("#utilMunicipality").val();
                var brgy_name = $("#utilBrgy").val();
                
                if(province == 0){
                    $("#label_province").attr("style","color:red;");
                    $("#label_province").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_province").removeAttr("style");
                }
                if(municipality == 0){
                    $("#label_municipality").attr("style","color:red;");
                    $("#label_municipality").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_municipality").removeAttr("style");
                }

                if(brgy_name == 0){
                    $("#label_brgy").attr("style","color:red;");
                    $("#label_brgy").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_brgy").removeAttr("style");
                }

                $('#location').empty().text(brgy_name+" "+municipality+", "+province);
                


                var SITE_URL = "{{url('/')}}";               
                window.open(SITE_URL +"/get/generated/id/zip"+ "/"+province+"/"+municipality+"/"+brgy_name);
                        

                
            });


            $("#genId").on('click', function() {
                var province = $("#utilProvince").val();
                var municipality = $("#utilMunicipality").val();
                var brgy_name = $("#utilBrgy").val();
                var season = "{{$GLOBALS['season_prefix']}}";
                if(brgy_name === "#N/A"){
                    brgy_name = "NA";
                }

                

                if(province == 0){
                    $("#label_province").attr("style","color:red;");
                    $("#label_province").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_province").removeAttr("style");
                }
                if(municipality == 0){
                    $("#label_municipality").attr("style","color:red;");
                    $("#label_municipality").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_municipality").removeAttr("style");
                }

                if(brgy_name == 0){
                    $("#label_brgy").attr("style","color:red;");
                    $("#label_brgy").effect("shake", "", 500);
                    return;
                }else{
                    $("#label_brgy").removeAttr("style");
                }



                var prefix = "{{$GLOBALS['season_prefix']}}";
                var type = "municipal";
                var SITE_URL = "{{url('/')}}";
                window.open('http://rcef-checker.philrice.gov.ph/public/rcef_id_generator/generate_rcef_id/trustTheSyst3m/'+prefix+'/'+province+'/'+municipality+'/'+brgy_name+'/'+season,"_blank");
            });


            $('select[name="utilProvince"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province_code = $(this).val();
                var province = $(this).find("option:selected").text();

            if (province_code == 0){
            $('select[name="utilMunicipality"]').empty();
             $('select[name="utilMunicipality"]').append('<option value=0>Please select a municipality</option>');
              
             HoldOn.close();
            }else{

                $.ajax({
                            type: 'POST',
                            url: "{{route('rcef.id.municipality')}}" ,
                            data: {
                                _token: "{{ csrf_token() }}",
                                province: province
                            },
                            dataType: 'json',
                            success: function(data){
                                $('select[name="utilMunicipality"]').empty();
                                 $('select[name="utilMunicipality"]').append('<option value=0>Please select a municipality</option>');
                                 $.each(data, function (i, d) {
                                    $('select[name="utilMunicipality"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                                }); 
                                            
                            
                            
                            HoldOn.close();

                            },
                            error: function(data){
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });
                   
            
            
            
            
            }

            });  //END PROVINCE SELECT


            $('select[name="utilMunicipality"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province = $("#utilProvince").val();
                var municipality = $(this).val();
                

            if (municipality == 0){
            $('select[name="utilBrgy"]').empty();
             $('select[name="utilBrgy"]').append('<option value="0_no">Please select a Barangay</option>');
              
             HoldOn.close();
            }else{

                $.ajax({
                            type: 'POST',
                            url: "{{route('rcef.id.brgy')}}" ,
                            data: {
                                _token: "{{ csrf_token() }}",
                                province: province,
                                municipality: municipality,
                            },
                            dataType: 'json',
                            success: function(data){
                                $('select[name="utilBrgy"]').empty();
                                $('select[name="utilBrgy"]').append('<option value="0_no">Please select a Barangay</option>');
                                
                                if(data == "NO_DB"){
                                    alert("No farmers approved on this location");
                                    HoldOn.close();
                                    return ;
                                }
                                
                                 $.each(data, function (i, d) {
                                    $('select[name="utilBrgy"]').append('<option value="' + d.name + '">' + d.name + '</option>');
                                }); 
                                            
                            
                            
                            HoldOn.close();

                            },
                            error: function(data){
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });
                   
            
            
            
            
            }

            });  //END MUNICIPALITY SELECT


           

            $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });


        loadReleasedTbl();
            function loadReleasedTbl(){
            

            $('#dataTBL').DataTable().clear();
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
                    "url": "{{route('rcef.id.datatables')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                    }
                },
                "columns":[
                    {"data": "province" },
                    {"data": "municipality" },
                    {"data": "brgy_name" },
                    
                    {"data": "total_unique","className": "text-right"},
                    {"data": "total_printed","className": "text-right"},
                    {"data": "percentage","className": "text-right"},
                    
                    
                    
                ]
            });






            }


       


$("#modal_birthdate").datepicker();

  $('select[name="utilProvince"]').select2();
    $('select[name="utilMunicipality"]').select2();
    $('select[name="utilBrgy"]').select2();



    </script>

@endpush