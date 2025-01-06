@extends('layouts.index')
@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap');

        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .mother_content{
            overflow-y: hidden;
            background: white!important;
        }

        .rounded{
            border-radius: 1em;
            background: white;
        }

        .cp{
            padding: 3em 1em;
        }

        label{
            color: black;
        }
        
        th{
            color: black;
        }

        ._main_container{
            background: white;
            font-family: "DM Sans";
            display: grid;
            gap: 1em;
            grid-template-areas:
            'one two'!important;
            grid-template-columns: 1fr 3fr;
            grid-template-rows: 1fr;
            position: relative;
            height: calc(100vh - 150px)!important;
            /* max-height: calc(100vh - 150px)!important; */
        }

        .selectors{
            grid-area: one;
            height: max-content;
        }

        .main_table{
            grid-area: two;
            height: 95%;
            overflow-y: auto;
        }

        #databody{
            max-height: calc(100vh - 100px)!important;
            font-size: 0.9em;
        }

        .prvSel{
            border-radius: 1e;
        }

        .super_title{
            font-size: 2em;
            color: black;
            font-weight: 700;
        }

        input{
            border-radius: 1em!important;
        }

        input:focus, input:active{
            border: 2px black solid;
            background: #00000010;
        }

        #search{
            border-radius: 2em;
            outline: green 1px solid;
            background-color: white;
            color: green;
            font-weight: 700;
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 350px;
            background-color: #fff;
            padding: 20px;
            border-radius: 20px;
            position: relative;
            }

            .title {
            font-size: 28px;
            color: #00b300;
            font-weight: 600;
            letter-spacing: -1px;
            position: relative;
            display: flex;
            align-items: center;
            padding-left: 30px;
            }

            .title::before,.title::after {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            border-radius: 50%;
            left: 0px;
            background-color: #00b300;
            }

            .title::before {
            width: 18px;
            height: 18px;
            background-color: #00b300;
            }

            .title::after {
            width: 18px;
            height: 18px;
            animation: pulse 1s linear infinite;
            }

            
            .flex {
            display: flex;
            width: 100%;
            gap: 6px;
            }

            .form label {
            position: relative;
            }

            .form label .input {
            width: 100%;
            padding: 8px 8px 12px 8px;
            outline: 0;
            border: 1px solid rgba(105, 105, 105, 0.397);
            border-radius: 10px;
            }

            .form label .input + span {
            position: absolute;
            left: 10px;
            top: 15px;
            color: grey;
            font-size: 0.9em;
            cursor: text;
            transition: 0.3s ease;
            }

            .form label .input:placeholder-shown + span {
            top: 15px;
            font-size: 0.9em;
            }

            .form label .input:focus + span,.form label .input:valid + span {
            top: 0px;
            font-size: 0.7em;
            font-weight: 600;
            }

            .form label .input:valid + span {
            color: #00b300;
            }

            @keyframes pulse {
            from {
                transform: scale(0.9);
                opacity: 1;
            }

            to {
                transform: scale(1.8);
                opacity: 0;
            }
            }
               
            .image {
                border: 2px solid #80ff80; /* Set border properties (2px width, blue color) */
                border-radius: 50%; /* Make the border a circle (for a circular profile image) */
                width: 150px; /* Set the width of the image */
                height: 150px; /* Set the height of the image */
            }

            /* table */
            .card {
                width: 100%;
                background-color: #fff;
                border: 1px solid #ccc;
                border-radius: 5px;
                box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
              
                }

                .chat-header {
                background-color:  #00b300;
                color: #fff;
                padding: 10px;
                font-size: 18px;
                border-top-left-radius: 5px;
                border-top-right-radius: 5px;
                }

                .chat-window {
                height: 320px;  
                padding: 4em 2em;
                /* overflow-y: scroll; */
                }

                /* inputs */
                
            #rounded-image {
                border-radius: 50%;
                width: 150px;
                height: 150px;
                border: 5px solid rgba(0, 0, 0, 0.4);
                overflow: hidden;
            }           

            #rounded-image:before {
                content: "";
                /* background-image: url(https://i.ytimg.com/vi/7xWxpunlZ2w/maxresdefault.jpg) center; */
                background-size: cover;
                width: 100%;
                height: 100%;
                display: block;
                overflow: hidden;
            }
           
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel" style="height: 100%">
                <div class="x_title">
                    <h2>DA-PhilRice Verifier</h2><br><br>
                </div>
                  <!-- UPLOAD PANEL -->
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <center>
                    <h2 class="title" style="padding-top: 1%">
                        Search Farmer
                    </h2>
                    </center>
                    <div class="clearfix"></div>
                </div>            
                    <div class="col-md-12">
                      {{--   <p class="title"> Search Farmer</p> --}}
                        <div class="form-group"> 
                            <div style="display: none" class="profilePicture">
                                <center>
                                    <img src="" id="rounded-image" class="profilePic">
                                </center>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="cluster">Search Option</label>
                            </div>                          
                            <div class="col-md-12">
                             
                                <div class="col-md-6">
                                    <input type="radio" name="cluster" id="rsbsa" value="yes"> <label class="" for="rsbsa">RSBSA</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" name="cluster" id="details" value="no"> <label class="" for="details">Personal Details</label>
                                </div>
          
          
                            </div>
                        <form class="form">
                      
        
                        <div class="column">
                            <div style="display: none" id="rsbsaForm">
                                <div class="input-box"> 
                                    <label>RSBSA #</label>
                                    <input type="text" id="rsbsa_search" class="form-control" name="rsbsa_search" placeholder="XX-XX-XX-XXX-XXXXXX">
                                </div>
                            </div>
                           
                            <div style="display: none" id="detailsOption">
                                <div class="input-box"> 
                                    <label>First name</label>
                                    <input type="text" id="fname" class="form-control" name="rsbsa_search" placeholder="Juan">
                                </div>
    
                                <div class="input-box"> 
                                    <label>Middle Name</label>
                                    <input type="text" id="mname" class="form-control" name="rsbsa_search" placeholder="Cruz">
                                </div>
    
                                <div class="input-box"> 
                                    <label>Last Name</label>
                                    <input type="text" id="lname" class="form-control" name="rsbsa_search" placeholder="Dela Cruz">
                                </div>
    
                                <div class="input-box"> 
                                    <label>Extension Name</label>
                                    <input type="text" id="ename" class="form-control" name="rsbsa_search" placeholder="Ext. Name">
                                </div>
                                <div class="input-box"> 
                                    <label>Sex</label>
                                    <select id="sex" class="form-control">
                                        <option value="MALE">MALE</option>
                                        <option value="FERMALE">FEMALE</option>
                                    </select>
                                </div>
    
                                <div class="input-box"> 
                                    <label>Birtday</label>
                                    <input type="text" id="bday" class="form-control" name="rsbsa_search" placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                           


                        </div>                                   
                        <div class="form-group buttonSearch" style="display: none">                       
                            <div class="col-md-12" style="text-align:center; margin-top:5px;">
                                <button type="button" name="search" id="search" class="btn btn-md btn-success" style="width:150px;margin: 5px;" ><i class="fa fa-search" aria-hidden="true"></i> Find Farmer </button>
                            </div>
        
                        </div>
                        </form>
                        </div>            
    
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row" id="form" style="color: black">
                
            </div>
           
            
        </div>

        

            </div>
        </div>
    </div>

  
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script type="text/javascript">
        var token = "{{ csrf_token() }}";

        $("#rsbsa").on("click", function(){
            $('#rsbsaForm').show();
            $('#detailsOption').hide();

            $('#rsbsa_search').val("");
            $('#fname').val("");
            $('#mname').val("");
            $('#lname').val("");
            $('#ename').val("");
            $('#bday').val("");
            
            $('.buttonSearch').show();
        
        
    });

    $("#details").on("click", function(){
        $('#rsbsaForm').hide();
        $('#detailsOption').show();    
        $('#rsbsa_search').val("");
        $('#fname').val("");
        $('#mname').val("");
        $('#lname').val("");
        $('#ename').val("");
        $('#bday').val("");
        $('.buttonSearch').show();

    });


        $('#search').click(function(){
            var rsbsa_search = $('#rsbsa_search').val();
            var fname = $('#fname').val();
            var mname = $('#mname').val();
            var lname = $('#lname').val();
            var ename = $('#ename').val();
            var sex = $('#sex').val();
            var bday = $('#bday').val();

            $.ajax({
            url: "https://da-nrp.philrice.gov.ph/da-philrice-verifier/icts-farmer-verifier?rsbsaData="+rsbsa_search+"&birthday="+bday+"&lastname="+lname+"&middlename="+mname+"&firstname="+fname+"&extname="+ename+"&sex="+sex+"",
            type: "GET",
            dataType: 'json',
           /*  beforeSend: function (xhr) {
                xhr.setRequestHeader("Authorization", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbWFpbCI6InRlc3QiLCJpYXQiOjE2OTAyNDc5MzIsImV4cCI6MTY5MDI0ODA1Mn0.pMISrZddadM_DQm-d26Ru01HSMwWU45_hEuWY_3N29o"); // Include your authorization token here
            }, */
            success: function (data) {   
                rsbsa_search = data[0].rsbsa_no;
            var statusRecord = "No record found"    
            $.ajax({
            url: "status-current-flist",
            type: "post",
            data:{
                rsbsa:rsbsa_search,
                _token:token
            },        
            success: function (recordData) { 
            $('.profilePicture').hide();
                if(parseInt(recordData)>0){
                statusRecord = recordData+" record found";
            }

            
                $('.profilePicture').show();
                $(".profilePic").attr("src",data[0].file_picture);

                $('#parceDiv').empty();
                $('#form').empty();
                $('#profileDiv').empty();  
                
                var Parcel = data[0].parcels;
                var x = 1;

                $('#form').append(
                    '<div class="col-md-6">'+
                    '<div class="x_panel" id="profileDiv">'+
                    '</div>'+
                    '</div>'+
                    '<div class="col-md-6">'+
                    '<div class="x_panel" id="parceDiv">'+
                    '</div>'+
                    '</div>'
                );                       
                $('#profileDiv').append(
                    '<div class="x_title" style="color:#00b300">'+
                    '<h2>'+
                    '    Profile'+
                    '</h2>'+
                    '<div class="clearfix"></div>'+
                    '</div>            '+
                    '    <div class="col-md-12">'+
                    '        <div class="container">'+
                    '            <div id="profile"  class="card-body">'+                                        
                    '                <div class="row">'+
                    '              <p><strong> Full Name: </strong>'+data[0].fname+', '+data[0].mname+', '+data[0].lname+', '+data[0].ext_name+' </p>'+                
                    '                        <p id="homeAddress"><strong>Home Address: </strong>'+data[0].reg_name+', '+data[0].prov_name+', '+ data[0].mun_name +', '+data[0].bgy_name +'</p>'+
                    '                        <p id="sex"><strong>Sex: </strong> '+data[0].sex+'</p>'+
                    '                        <p id="birthday"><strong>Birthday: </strong> '+data[0].birthday+'</p>'+
                    '                        <p id="birthday"><strong>Birth Place: </strong> '+data[0].birth_place+'</p>'+
                    
                    
                    '                        <p id="cnum"><strong>Contact Number: </strong> '+data[0].contact_num+'</p>'+
                    '                        <p id="spouseName"><strong>Spouse:</strong> '+data[0].spouse+'</p>'+
                    '                        <p id="motherName"><strong>Mother maiden name: </strong> '+data[0].mother_maiden_name+'</p>'+
                    '                        <p id="motherName"><strong>Profile Status: </strong> '+statusRecord+'</p>'+
                    
                    '                </div>'+
                    '                <hr>'+                                
                    '                '+
                    '            </div>'+
                    '        </div>   '+
                    '</div>    '
                );
                $.each( Parcel, function( key, value ) {
                    var commoditiesData = JSON.stringify(value.commodities);
                    var checker = commoditiesData.includes("Rice/Palay");
                    var commodities = "";
                    if(checker){
                        $.each( value.commodities, function( key, value2 ) {
                            if(value2.cropname == "Rice/Palay"){
                                commodities =     commodities+'    <div style="margin-left: 5%">'+
                                          '        <p id="p1Cropname"><strong>Crop Name: </strong> '+value2.cropname+'</p>'+
                                          '        <p id="p1CSize"><strong>Size: </strong> '+value2.crop_size+'</p>'+
                                          '        <p id="farmtype"><strong>farm type: </strong> '+value2.farm_type+'</p>'+
                                          '    </div> <hr style="height:2px;border-width:0;color:#00b300;background-color:#00b300">';
                            }
                       
                    });
                    if(x ==1){
                        $('#parceDiv').append(
                            '<div class="x_title" style="color:#00b300"> '+
                            '<h2>'+
                            '    Pracel Profile'+
                            '</h2>'+
                            '<div class="clearfix"></div>'+
                            '</div>' 
                        );
                    }
                    $('#parceDiv').append(   
                    '<div style="color:#00b300"> '+    
                        '<h4>Parcel '+x+'</h4>'+
                    '</div>'+                   
                                    ' <h2>Parcel Detail</h2>'+
                                    '<div style="margin-left: 5%">'+
                                    '    <p id="p1Address">'+value.parcel_region+','+value.parcel_province+','+value.parcel_municipality+','+value.parcel_barangay+'</p>'+
                                    '    <p id="p1Size">Parcel size: '+value.parcel_area+'</p>'+
                                    '    <p id="p1Size">Parcel Owner: '+value.parcel_owner_firstname+' '+value.parcel_owner_lastname+'</p>'+
                                    '    <p id="p1Size">Document Presented: '+value.presented_doc+'</p>'+
                                    '    <p><strong>Commodities</strong> </p>'+  
                                    commodities+                              
                                    '</div>');
                                    x++;
                    }
                   
                   
                });
            
             
            }
            });
           

              
               
                // Handle the response data here
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors here
            }
});


        });

        
    </script>
@endpush
