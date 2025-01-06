<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    .tile_count .tile_stats_count .count {
        font-size: 30px;
    }

    #ws2021_block, #ds2021_block, #ws2020_block, #ds2020_block{
        background-color: red;
        color: white;
        cursor: pointer;
    }
  </style>
@endsection

@section('content')
 
 <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="clearfix"></div>
    @include('layouts.message')
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

    <div class="row">
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Filter:</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    Province:
                    <select name="select_province" id="select_province" class="form-control">
                        <option value="0">Please select a Province</option>
                    </select><br><p></p>
                    Municipality:
                    <select name="select_municipality" id="select_municipality" class="form-control">
                        <option value="0">Please select a Municipality</option>
                    </select><br><p></p>
                    RSBSA / NAME:
                    <input type="text" name="filter_text" id="filter_text" class="form-control" placeholder="Search RSBSA/NAME">
                        <br>
                    <button class="btn btn-success btn-block"  id="search_farmer" disabled="" onclick="search_tbl();"><i class="fa fa-search"></i> Search Farmer</button>
                    <!-- <button class="btn btn-success btn-block" disabled="" id="load_farmer" onclick="preload();"><i class="fa fa-database"></i> LOAD FARMERS</button> -->
                </div>
            </div>
        </div>        
        <div class="col-md-8">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmer List</h2> 
                   <!-- <button class="btn btn-success btn-sm" style="float:right;" onclick="pre_load_ten();" id="load_ten" name="load_ten" disabled=""> <i class="fa fa-plus-square-o" aria-hidden="true" ></i> Load 10 More</button> -->
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" id="card_container" style="height: 900px; overflow: auto;">
                </div>
            </div>
        </div> 




    </div>
      





    <input type="hidden" name="card_count" id="card_count" value="0">
    <input type="hidden" id="active_db" name="active_db" value="">


@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>





        $("#select_province, #select_municipality").select2();
        $("#farmer_tbl").DataTable();

        $("#ws2021_block").on("click", function(e){
            HoldOn.open(holdon_options);
            
            $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
             $("#load_farmer").removeAttr("disabled");
            $("#load_farmer").attr("disabled", "");
             $("#load_ten").removeAttr("disabled");
            $("#load_ten").attr("disabled", "");
             $("#search_farmer").removeAttr("disabled");
                    $("#search_farmer").attr("disabled", "");
                    $("#card_container").empty();

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
            
                            $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");

                            if(source["data"]["info"].length >0){
                                
                                    $.each(source["data"]["info"],function (i, d) {
                                    $('select[name="select_province"]').append('<option value="' + d.province + '">' + d.province+'</option>');
                                    });                                 
                            }

                    }else{
                        alert("Connection failed");
                        $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                        $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
                        $("#ws2021_block").css("background-color", "red");
                        $("#ws2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
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

             HoldOn.open(holdon_options);
              $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
             $("#load_farmer").removeAttr("disabled");
            $("#load_farmer").attr("disabled", "");
             $("#load_ten").removeAttr("disabled");
            $("#load_ten").attr("disabled", "");
             $("#search_farmer").removeAttr("disabled");
                    $("#search_farmer").attr("disabled", "");
                    $("#card_container").empty();

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

                        $("#active_db").empty().val(source["data"]["season"]);

                                $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                                $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");

                                if(source["data"]["info"].length >0){
                                    
                                        $.each(source["data"]["info"],function (i, d) {
                                        $('select[name="select_province"]').append('<option value="' + d.province + '">' + d.province+'</option>');
                                        });                                 
                                }
                    }else{
                         alert("Connection failed");
                          $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                        $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
                         $("#ds2021_block").css("background-color", "red");
                        $("#ds2021_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
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

            HoldOn.open(holdon_options);
             $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
             $("#load_farmer").removeAttr("disabled");
            $("#load_farmer").attr("disabled", "");
             $("#load_ten").removeAttr("disabled");
            $("#load_ten").attr("disabled", "");
             $("#search_farmer").removeAttr("disabled");
                    $("#search_farmer").attr("disabled", "");
                    $("#card_container").empty();


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
            
                            $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");

                            if(source["data"]["info"].length >0){
                                    $.each(source["data"]["info"],function (i, d) {
                                    $('select[name="select_province"]').append('<option value="' + d.province + '">' + d.province+'</option>');
                                    });                                 
                            }
                    }
                    else{
                         alert("Connection failed");
                          $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                        $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
                         $("#ws2020_block").css("background-color", "red");
                        $("#ws2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
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

            HoldOn.open(holdon_options);
            $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
            $("#load_farmer").removeAttr("disabled");
            $("#load_farmer").attr("disabled", "");
            $("#load_ten").removeAttr("disabled");
            $("#load_ten").attr("disabled", "");
            $("#search_farmer").removeAttr("disabled");
            $("#search_farmer").attr("disabled", "");
            $("#card_container").empty();
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
                            $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");

                            if(source["data"]["info"].length >0){
                                    $.each(source["data"]["info"],function (i, d) {
                                    $('select[name="select_province"]').append('<option value="' + d.province + '">' + d.province+'</option>');
                                    });                                 
                            }
                    }
                    else{
                         alert("Connection failed");
                          $('select[name="select_province"]').empty().append("<option value='0'>Please select a Province</option> ");
                        $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ");
                         $("#ds2020_block").css("background-color", "red");
                        $("#ds2020_status").empty().html("<i class='fa fa-power-off'></i> -- DISCONNECTED");
                    }
                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
            });

        });



        $('select[name="select_province"]').on('change', function () {
            HoldOn.open(holdon_options);
            var province = $(this).val();
            $("#load_farmer").removeAttr("disabled");
            $("#load_farmer").attr("disabled", "");
            $("#load_ten").removeAttr("disabled");
            $("#load_ten").attr("disabled", "");
            $("#search_farmer").removeAttr("disabled");
            $("#search_farmer").attr("disabled", "");

             $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.municipality')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    province: province,
                    season: $("#active_db").val()
                },
                dataType: 'json',
                success: function (source) {
                            $('select[name="select_municipality"]').empty().append("<option value='0'>Please select a Municipality</option> ")

                            $.each(source, function (i, d) {
                            $('select[name="select_municipality"]').append('<option value="' + d.municipality + '">' + d.municipality+'</option>');
                            }); 
                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
            }); //AJAX CONNECT
         });  //END PROVINCE SELECT

         $('select[name="select_municipality"]').on('change', function () {            
            var municipality = $(this).val();
            if(municipality === "0"){
                $("#load_farmer").removeAttr("disabled");
                $("#load_farmer").attr("disabled", "");
                $("#load_ten").removeAttr("disabled");
                $("#load_ten").attr("disabled", "");
                $("#search_farmer").removeAttr("disabled");
                $("#search_farmer").attr("disabled", "");
            }else{
                $("#load_farmer").removeAttr("disabled");
                $("#load_ten").removeAttr("disabled");
                $("#search_farmer").removeAttr("disabled");
                       
         
            }

         });  //END PROVINCE SELECT

         function preload(){
            $("#card_count").val("0");
            loadTable();
         }

         function pre_load_ten(){
            var card_count = $("#card_count").val();
            if(card_count === "0"){
                alert("Please Load Farmer First");
            }else{
               loadTable();
            }
         }
         function search_tbl(){
            var text_filter = $("#filter_text").val();
            if(text_filter === ""){
                alert("Please Fill up the input box");
                return "";
            }
            //var msg = confirm("Search Farmer ?");
            //if(msg){
                var season = $("#active_db").val();
                var province = $("#select_province").val();
                var municipality = $("#select_municipality").val();
                $("#card_count").val("0");

                if(season === ""){
                alert("Please Select a Season ");
                }else{       
                    HoldOn.open(holdon_options);
                $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.load_search')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality,
                    season: season,
                    text_filter: text_filter
                },
                dataType: 'json',
                success: function (source) {


                        if(source[0]["rsbsa_control_no"] == "NO_DATA"){
                            $("#card_container").empty();
                            $("#card_container").append("NO RESULT");
                        }else{
                            $("#card_container").empty();           
                        $.each(source, function (i, d) {
                            var current_count = i + 1;
                            var name = d.lastName + ', '+d.firstName+' '+d.midName;
                            var id = d.id;
                            var rsbsa_control_no = d.rsbsa_control_no;
                            var farmer_id = d.farmerID;
                            var province = d.province;
                            var municipality = d.municipality;
                            var prv_id = d.prv_id;
                            var firstName = d.firstName;
                            var midName = d.midName;
                            var lastName = d.lastName;

                            var kp1_label = d.kp1_label;
                            var kp2_label = d.kp2_label;
                            var kp3_label = d.kp3_label;
                            var ksl_label = d.ksl_label;
                            var cal_label = d.cal_label;



                            if (current_count % 2 == 0) {
                                var style = "box-shadow: 3px 3px 10px #f5b041 inset; font-size: 15px;";
                            }else{
                                var style = "box-shadow: 3px 3px 10px #82e0aa inset; font-size: 15px;";
                            } 

                            if(d.gender == "Male"){
                                var icon = "fa-male";
                             }else if(d.gender == "Female"){
                                var icon = "fa-female"
                             }
                             else{
                                var icon = "fa-genderless";
                             }


                             var card = '<div class="col-md-12"> <div class="x_panel" style="'+style+'"> <div class="x_title">';
                             card = card + '<font style="font-size: 15px; font-weight:bold;">' + rsbsa_control_no +'</font> <br>';
                             card = card + '<font style="font-size: 14px; font-weight:bold;">' + name  +'</font>';
                             card = card + '<div class="clearfix"></div>';
                          
                             card = card + ' <div class="x_content form-horizontal form-label-left" > <br>';

                             card = card + '<div class="row tile_count" style="margin: 0; ">';
                             card = card + '<div class="col-md-6 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-user" aria-hidden="true" style="font-size:14px;" > Mother:'+d.mother_name+'</i> <br>';
                             card = card + '<i class="fa '+icon+'" aria-hidden="true" style="font-size:14px;" > Gender: '+d.gender+'</i> <br>';                           
                             card = card + '</div>';
                             card = card + '<div class="col-md-2 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0; font-size:15px;">';
                             card = card + '<i class="fa fa-file-text" aria-hidden="true" id="kp1_label" > &nbsp; '+kp1_label+'</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                             //input kp1
                             card = card + '<input type="number" class="form-control" name="kp1_'+d.farmerID+'" id="kp1_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'kp1'"+', '+"'"+season+"'"+');" value="'+d.kp1+'"></div>';

                             card = card + '<div class="col-md-2 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0; font-size:15px;">';
                             card = card + '<i class="fa fa-file-text-o" aria-hidden="true" id="kp2_label"> &nbsp;'+kp2_label+'</i></div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                             //input kp2
                             card = card + '<input type="number" class="form-control" name="kp2_'+d.farmerID+'" id="kp2_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+' , '+"'kp2'"+', '+"'"+season+"'"+');" value="'+d.kp2+'"></div> </div>';




                             card = card + '<div class="row tile_count" style="margin: 0">';
                             card = card + '<div class="col-md-6 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-birthday-cake" aria-hidden="true" > Birthday: '+d.birthdate+'</i> <br> ';
                             card = card + '<i class="fa fa-gift" aria-hidden="true" > Age: '+d.age+'</i> <br>';
                             card = card + '</div>';
                              card = card + '<div class="col-md-2 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0; font-size:15px;">';
                             card = card + '<i class="fa fa-file-code-o" aria-hidden="true" id="kp3_label" > &nbsp;'+kp3_label+'</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<input type="number" class="form-control" name="kp3_'+d.farmerID+'" id="kp3_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'kp3'"+', '+"'"+season+"'"+');" value="'+d.kp3+'"></div>';
                             card = card + '<div class="col-md-2 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;  font-size:15px;">';
                             card = card + '<i class="fa fa-file-archive-o" aria-hidden="true" id="ksl_label"> &nbsp;'+ksl_label+'</i></div>';
                              card = card + '<div class="col-md-1 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                              card = card + '<input type="number" class="form-control" name="ksl_'+d.farmerID+'" id="ksl_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'ksl'"+', '+"'"+season+"'"+');" value="'+d.ksl+'"></div></div>';




                             card = card + '<div class="row tile_count" style="margin: 0">';
                             card = card + '<div class="col-md-6 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-mobile" aria-hidden="true" > Contact No.: '+d.contact+'</i> <br>';
                             card = card + '<i class="fa fa-spinner" aria-hidden="true" > Date Released: '+d.date_synced+'</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-2 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0; font-size:15px;">';
                             card = card + '<i class="fa fa-calendar-check-o" aria-hidden="true" id="cal_label" > &nbsp;'+cal_label+'</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">';
                              card = card + '<input type="number" class="form-control" name="cal_'+d.farmerID+'" id="cal_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'cal'"+', '+"'"+season+"'"+');" value="'+d.calendar+'"></div>';
                             card = card + '</div>';
                             card = card + '</div> </div> </div></div>';










                            
                            $("#card_container").append(card);
                        }); 
                            }
                          
                            HoldOn.close();
                        },
                        fail: function(xhr, textStatus, errorThrown){
                           alert('Connection Failed');
                           HoldOn.close();
                        }
                        }); //AJAX CONNECT  

                }





            //}

           






         }



         function loadTable(){

            var season = $("#active_db").val();
            var province = $("#select_province").val();
            var municipality = $("#select_municipality").val();
            var card_count = $("#card_count").val();
            var current_count = parseInt(card_count);
            if(season === ""){
                alert("Please Select a Season ");
            }else{       
                HoldOn.open(holdon_options);
                $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.load_card')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality,
                    season: season,
                    card_count: card_count
                },
                dataType: 'json',
                success: function (source) {


                        if(source[0]["rsbsa_control_no"] == "NO_DATA"){
                            $("#card_container").empty();
                            $("#card_container").append("NO RESULT");
                        }else{

                        if(card_count === "0" ){
                            $("#card_container").empty();
                        }
        


                        $.each(source, function (i, d) {
                            current_count = current_count +1;
                            var name = d.lastName + ', '+d.firstName+' '+d.midName;
                            var id = d.id;
                            var rsbsa_control_no = d.rsbsa_control_no;
                            var farmer_id = d.farmerID;
                            var province = d.province;
                            var municipality = d.municipality;
                            var prv_id = d.prv_id;
                            var firstName = d.firstName;
                            var midName = d.midName;
                            var lastName = d.lastName;

                            if ( current_count % 2 == 0) {
                                 var style = "box-shadow: 3px 3px 10px #f5b041 inset"
                            }else{
                                var style = "box-shadow: 3px 3px 10px #82e0aa inset";
                            } 

                             var card = '<div class="col-md-12" > <div class="x_panel" style="'+style+'" > <div class="x_title">';
                             card = card + '<font style="font-size: 15px; font-weight:bold;">' + rsbsa_control_no +'</font> <br>';
                             card = card + '<font style="font-size: 14px; font-weight:bold;">'+current_count +'.) ' + name  +'</font';
                             card = card + '<div class="clearfix"></div>';
                          
                             card = card + ' <div class="x_content form-horizontal form-label-left" >';

                             card = card + '<div class="row tile_count" style="margin: 0">';
                             card = card + '<div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-map-marker" aria-hidden="true" > Address: '+d.address+'</i>';
                             card = card + '</div>';


                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-file" aria-hidden="true" > KP</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             //input kp1
                             card = card + '<input type="number" class="form-control" name="kp1_'+d.farmerID+'" id="kp1_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'kp1'"+', '+"'"+season+"'"+');" value="'+d.kp1+'"></div>';

                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-file" aria-hidden="true"> KP</i></div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             //input kp2
                             card = card + '<input type="number" class="form-control" name="kp2_'+d.farmerID+'" id="kp2_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+' , '+"'kp2'"+', '+"'"+season+"'"+');" value="'+d.kp2+'"></div> </div>';


                             if(d.gender == "Male"){
                                var icon = "fa-male";
                             }else if(d.gender == "Female"){
                                var icon = "fa-female"
                             }
                             else{
                                var icon = "fa-genderless";
                             }

                             card = card + '<div class="row tile_count" style="margin: 0">';
                              card = card + '<div class="col-md-4 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa '+icon+'" aria-hidden="true" > Gender: '+d.gender+'</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-4 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-birthday-cake" aria-hidden="true" > Birthday: '+d.birthdate+'</i>';
                             card = card + '</div>';

                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-file" aria-hidden="true"> KP</i></div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             //input kp3
                             card = card + '<input type="number" class="form-control" name="kp3_'+d.farmerID+'" id="kp3_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'kp3'"+', '+"'"+season+"'"+');" value="'+d.kp3+'"></div>';

                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-file" aria-hidden="true"> KSL</i></div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             //input ksl
                             card = card + '<input type="number" class="form-control" name="ksl_'+d.farmerID+'" id="ksl_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'ksl'"+', '+"'"+season+"'"+');" value="'+d.ksl+'"></div></div>';

                            card = card + '<div class="row tile_count" style="margin: 0">';
                            card = card + '<div class="col-md-8 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-mobile" aria-hidden="true" > Contact No.: '+d.contact+'</i>';
                             card = card + '</div>';
                             card = card + '<div class="col-md-1 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             card = card + '<i class="fa fa-calendar" aria-hidden="true"> Calendar</i></div>';
                             card = card + '<div class="col-md-3 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">';
                             //input calendar
                             card = card + '<input type="number" class="form-control" name="cal_'+d.farmerID+'" id="cal_'+d.farmerID+'" onchange="updateData('+"'"+id+"'"+', '+"'"+rsbsa_control_no+"'"+', '+"'"+farmer_id+"'"+', '+"'"+province+"'"+', '+"'"+municipality+"'"+', '+"'"+prv_id+"'"+', '+"'"+firstName+"'"+', '+"'"+midName+"'"+', '+"'"+lastName+"'"+', '+"'cal'"+', '+"'"+season+"'"+');" value="'+d.calendar+'"></div>';
                             card = card + '</div>';
                             card = card + '</div> </div> </div></div>';
                            $("#card_container").append(card);
                        }); 
                    }
                    $("#card_count").val(current_count);
                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                   HoldOn.close();
                }
                }); //AJAX CONNECT   
            }
        


         }




         function updateData(id, rsbsa, farmerID, province, municipality, prv_id, firstName, midName, lastName, field, season){
            var val = $("#"+field+"_"+farmerID).val();
           // alert(val);
           // alert(id+rsbsa+ farmerID+ province+ municipality+ prv_id+ firstName+ midName+ lastName+ field+season);


           if(val === ""){
                return "";
           }



            if(parseInt(val)<0){
                alert("Negative Values Not Allowed");
                return "";

            }
           HoldOn.open(holdon_options);
                $("#"+field+"_"+farmerID).css("border", "2px solid orange");


             $.ajax({
                method: 'POST',
                url: "{{route('rcef.extension.insert.data')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    season: season,
                    ext_id: id,
                    farmer_id: farmerID,
                    rsbsa: rsbsa,
                    first_name: firstName,
                    middle_name: midName,
                    last_name: lastName,
                    province: province,
                    municipality: municipality,
                    prv: prv_id,
                    field: field,
                    val:val

                },
                dataType: 'json',
                success: function (source) {
                    if(source === "success"){
                         $("#"+field+"_"+farmerID).css("border", "2px solid green");
                    }else{
                         $("#"+field+"_"+farmerID).css("border", "2px solid red");
                    }



                    HoldOn.close();
                },
                fail: function(xhr, textStatus, errorThrown){
                   alert('Connection Failed');
                    $("#"+field+"_"+farmerID).css("border", "2px solid red");
                   HoldOn.close();
                }
            }); //AJAX CONNECT


         }










    </script>
@endpush
