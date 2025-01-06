<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}"/>
    <link
    rel="stylesheet"
    href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}"/>
    <link href="public/css/HoldOn.min.css" rel="stylesheet" />
    <link
    rel="stylesheet"
    href="https://code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
    .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
    .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
    .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
    .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
    .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
    .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
    .shadow-none	{box-shadow: 0 0 #0000;}

    .x_panel{
        /* background: conic-gradient(from 35deg, #57d98b60, #35945b80); */
        background: #e0e0e0;
        color: black;
        position: absolute;
        border-radius: 2em;
        border: 2px solid #c3c6ce;
                -webkit-transition: 0.5s ease-out;
                transition: 0.5s ease-out;
                overflow: visible;
    }


    .x_title{
			margin: 10px;
            border: 1px #6b6b6b;
			box-sizing: border-box;
            background-color: transparent;
            border-radius: 10px;
            padding: 10px;
    }

    .x_title h1{
        font-weight: 900;
    }
    
    #containers{
			margin: 10px;
            border: 1px #6b6b6b;
			box-sizing: border-box;
            padding: 10px;
            border-radius: 10px;
            background-color: #faf5f580;
            backdrop-filter: blur(5px);
    }

    h4{
        font-weight: 700;
        margin: 0 0 0.4em 0;
        padding: 0;
    }

    hr {
        border: none;
        height: 1px;
        background-color: #6b6b6b;
        margin-top: 0.5px;
        margin-bottom: 10px;
    }

    select{
        border: 1px solid #888;
        border-radius: 0.6em;
        padding: 0.2em;
        font-size: 1.4em;
    }

    .selectors {
    display: inline-block;
    }

    .selector_cards {
        display: inline-block;
        margin-right: 10px;
        margin-left: 10px;
    }

    .submit{
        width: max-content;
        margin-left: 15px;
    }

    .group_selector{
        display: flex;
        gap: 20%;
    }

		.col-md-6 {
			width: 50%;
			float: left;
            margin-top: 10px;

		}

        .shadow {
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
        }

        .btn {
            display: inline-block;
            margin-top: 5px;
            margin-right: 5px;     
        }

        .boxes {
            width: 100%;
            height: 100%; 
            padding: 1em 2em;
            border-radius: 30px;
            background: #e0e0e0;
               border: 2px solid #c3c6ce;
                -webkit-transition: 0.5s ease-out;
                transition: 0.5s ease-out;
                overflow: visible;
		}

        .boxes:hover {
        border-color: #3ed655;
        background: #e0e0e0;
        /* -webkit-box-shadow: 10px 5px 18px 0 rgba(255, 255, 255, 0.877);
        box-shadow: 10px 5px 18px 0 rgba(255, 255, 255, 0.877); */
        }
		.col-md-6 {
			width: 50%;
			float: left;
            margin-top: 10px;

		}

        #page
        {
            padding-inline: 0em 1.5em;
        }


        .form-field {
            margin-bottom: 1em;
        }
        .form-field label {
            display: block;
            font-weight: bold;
        }
        .form-field input {
            width: calc(100% - 1em);
            padding: 0.5em;
            margin-top: 0.2em;
        }

        .info_list
        {
            list-style-type: none;
        }

        .profiles
        {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .container {
        
        }

        .container > input {
        display: none;
        }

        .container svg {
        overflow: visible;
        }

        .path {
        fill: none;
        stroke: black;
        stroke-width: 6;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke-dasharray 0.5s ease, stroke-dashoffset 0.5s ease;
        stroke-dasharray: 241 9999999;
        stroke-dashoffset: 0;
        }

        .container input:checked ~ svg .path {
        stroke-dasharray: 70.5096664428711 9999999;
        stroke-dashoffset: -262.2723388671875;
        }

        i.checked-add {
            color: green;
        }


        #beginButton {
            padding: 1em;
            margin-top: 0.5em;
            margin-left: 1em;
            border: unset;
            border-radius: 15px;
            color: #212121;
            z-index: 1;
            background: #e3e1e1;
            position: relative;
            font-weight: bold;
            font-size: 1em;
            -webkit-box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms;
            overflow: hidden;
        }

        #beginButton::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0;
            border-radius: 15px;
            background-color: #3ed655;
            z-index: -1;
            -webkit-box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms
        }

        #beginButton:hover::before {
            width: 100%;
        }

        #beginButton:disabled {
            color: #999;
            background: #ccc;
            cursor: not-allowed;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        #beginButton:disabled::before {
            background-color: #999;
            -webkit-box-shadow: none;
            box-shadow: none;
        }

        #beginButton:hover:disabled {
        color: #ffffff;
        }
       
        #submitButton {
            padding: 1em;
            margin-top: 0.5em;
            margin-left: 1em;
            border: unset;
            border-radius: 15px;
            color: #212121;
            z-index: 1;
            background: #e3e1e1;
            position: relative;
            font-weight: bold;
            font-size: 1em;
            -webkit-box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms;
            overflow: hidden;
        }

        #submitButton::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0;
            border-radius: 15px;
            background-color: #3ed655;
            z-index: -1;
            -webkit-box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms
        }

        #submitButton:hover::before {
            width: 100%;
        }

        #submitButton:disabled {
            color: #999;
            background: #ccc;
            cursor: not-allowed;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        #submitButton:disabled::before {
            background-color: #999;
            -webkit-box-shadow: none;
            box-shadow: none;
        }

        #submitButton:hover:disabled {
        color: #ffffff;
        }


        #skipButton {
            padding: 1em;
            border: unset;
            border-radius: 15px;
            color: #212121;
            z-index: 1;
            background: #e3e1e1;
            position: relative;
            font-weight: bold;
            font-size: 1em;
            -webkit-box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms;
            overflow: hidden;
        }

        #skipButton::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0;
            border-radius: 15px;
            background: #ffa600;
            z-index: -1;
            -webkit-box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms
        }

        #skipButton:hover::before {
            width: 100%;
        }

        #skipButton:disabled {
            color: #999;
            background: #ccc;
            cursor: not-allowed;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        
        #skipButton:disabled::before {
            background-color: #999;
            -webkit-box-shadow: none;
            box-shadow: none;
        }

        #skipButton:hover:disabled {
        color: #ffffff;
        }

        .profIcon
        {
            filter: saturate(60%);
        }

       
    </style>
@endsection

@section('content')
    
<div class="clearfix" id="page">
    
    @include('layouts.message')
        
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel shadow-2xl" style="padding-bottom: 3em;">
                <div class="x_title">
                    <h1>Farmer KYC</h1>
                    <div class="clearfix"></div>
                </div>

                <section class="group_selector">
                    <div class="selectors" id="selectors style="display: inline-block;>
                        <div class="provinces_container selector_cards" id="containers" style="display: inline-block; margin-right: 10px;">
                            <h4>Province:</h4>
                            <select name="provinces" id="provinces">
                                <option value="default">Select Province</option>
                                @foreach($provinces as $province)
                                <option value="{{$province->province}}">{{$province->regionName}} - {{$province->province}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="municipality_container selector_cards" id="containers" style="display: inline-block;">
                            <h4>Municipality:</h4>
                            <select name="municipality" id="municipality" disabled>
                                <option value="default">Select Municipality</option>
                            </select>
                        </div>

                        <div>
                            <i style="padding-inline: 1em">*Please select province and municipality to begin verification.</i>
                        </div>
                        <div>
                            <button type="button" id='beginButton' style="border-radius: 15px;" disabled>Begin Verification</button> 
                            <button type="button" id="reset" style="display:none">Reset</button> 
                        </div>
                            
                    </div>
                </section>
            
                <div id="statistics" style="padding-top: 1em; display:none;">
                    <div class="col-md-4">
                        <div class="boxes shadow-md" >
                            <h1 id="totalForValidation" style="font-weight: 700;"></h1>
                            <hr>
                            <h4>Total Farmer Beneficiary Records for validation</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="boxes shadow-md" >
                            <h1 id="totalValidated" style="font-weight: 700;"></h1>
                            <hr>
                            <h4>Total Farmer Beneficiary Profiles Validated</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="boxes shadow-md" >
                            <h1 id="totalPending" style="font-weight: 700;"></h1>
                            <hr>
                            <h4>Total Pending Records for Approval</h4>
                        </div>
                    </div>
                </div>

                <div id="customSearch" style="display:none;">
                    <div class="col-md-2"></div>
                    <div class="col-md-8" style="padding-top: 1em">
                        <div  class="" style="text-align: center;">
                            <div style="display:flex; justify-content: center;">
                                <i id="prevButton" class="fas fa-arrow-left" style="font-size: 3em; margin-right: 0.5em; margin-top: 0.2em; display: none;"></i>
                                <div style="display:flex; justify-content: center;">
                                    <h1 id="currentCluster">1</h1>
                                    <h1>/</h1>
                                    <h1 id="noOfClusters"></h1>
                                </div>
                                <i id="nextButton" class="fas fa-arrow-right" style="font-size: 3em; margin-left: 0.5em; margin-top: 0.2em"></i>
                            </div>
                            <h4>Clusters</h4>
                            <hr>
                            <button type="button" id='submitButton' style="border-radius: 15px;" disabled>Submit Verification</button> 
                            <button type="button" id='skipButton' style="border-radius: 15px;">Skip Verification</button> 
                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>
                
                <div id="profiles" style="display:none;"></div>
                <div id="suggested" style="display:none;"></div>



            </div>
        </div>
    </div>
</div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src="public/js/HoldOn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>

    var main_profile ='';
    var sub_profiles = [];
    var new_profiles = [];
    var all_profiles = [];
    var profileCount = 0;
    var tempProfile = 0;
    var onLoadData = [];
    var onLoadIndex = 0;
    var findCluster = '';

        $('#provinces').select2();
        $('#municipality').select2();

    

    $('#provinces').change(() => {
            onLoadIndex = 0;
            findCluster = '';
            $("#profiles").empty();
            $("#suggested").empty();
            $("#statistics").hide();
            $('#customSearch').hide();
            $("#profiles").hide();
            $("#suggested").hide();
            $('#municipality').removeAttr('disabled');
            $('#municipality').val('default').trigger('change');

            if ($('#provinces').val() !== 'default') {
            $('#provinces option[value="default"]').remove();
            }

            $prov = $('#provinces').val();
            var options = {
                theme:"custom",
                content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                message:'Please wait.',
                backgroundColor:"#494f5f",
                textColor:"white"
            };

            HoldOn.open(options);

            $.ajax({ 
                type: 'POST',
                url: "{{ route('farmerVerification.getMuni') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    prov: $prov
                },
                success: function(data){
                    HoldOn.close();
                    $processedMun = JSON.parse(data);
                    $('#municipality option:gt(0)').remove();
                    for(i = 0; i < $processedMun.length; i++){
                        $("#municipality").append('<option value="' + $processedMun[i].geocode + '">' + $processedMun[i].municipality + '</option>');
                    }
                }
            });


        });

        $('#municipality').change(() => { 
            $mun = $('#municipality').val();
            if($mun != 'default')
            {
                $('#beginButton').removeAttr('disabled');
            }
            else if($mun == 'default')
            {
                $('#beginButton').attr('disabled', 'disabled');
            }
        });

        $('#skipButton').on('click', () => {
            Swal.fire({
                title: 'Are you sure you want to skip verification for this profile?',
                text: 'This will be removed from the list to be subject for further verification of the RCEF-PMO or C/MLGU.',
                input: 'text',
                inputLabel: 'Please provide a reason for skipping:',
                inputPlaceholder: 'Enter your reason here...',
                showCancelButton: true,
                confirmButtonText: 'Yes, skip it!',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                preConfirm: (skipReason) => {
                    if (!skipReason) {
                        Swal.showValidationMessage('You need to provide a reason for skipping');
                    }
                    return skipReason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let skipReason = result.value;
                    $mun = $('#municipality').val();
                    let main_profileStr = main_profile.toString();

                    if (profileCount > 1) {
                        sub_profiles = all_profiles.filter(item => !new_profiles.includes(parseInt(item)) && item !== main_profileStr);
                    }

                //

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('farmerVerification.skipProfile') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            main_profile: main_profile,
                            sub_profiles: sub_profiles,
                            new_profiles: new_profiles,
                            mun: $mun,
                            profileCount: profileCount,
                            tempProfile: tempProfile,
                            all_profiles: all_profiles,
                            skipReason: skipReason // Send the reason to the server
                        },
                        success: function(data) {
                            Swal.fire('Success', 'Profile now submitted for further verification.', 'success');
                            onLoadIndex = 0;
                            tempProfile = 0;
                            findCluster = '';
                            $('#beginButton').click();
                        }
                    });
                }
            });
        });



        $('#prevButton').on('click', () =>{
            all_profiles = [];
            console.log(tempProfile, all_profiles);
            
            var options = {
                theme:"custom",
                content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                message:'Please wait.',
                backgroundColor:"#494f5f",
                textColor:"white"
            };
            HoldOn.open(options);
            $("#currentCluster").empty();
            $("#currentCluster").append(onLoadIndex);
            if(onLoadIndex -1 == 0)
            {
                $("#prevButton").hide();
                $("#nextButton").show();
            }
            if(onLoadIndex < onLoadData.length - 1)
            {
                $("#nextButton").show();
            }

            onLoadIndex = onLoadIndex - 1;
                            
            var options = {
                theme:"custom",
                content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                message:'Please wait.',
                backgroundColor:"#494f5f",
                textColor:"white"
            };

            $("#profiles").empty();
            $("#suggested").empty();

            findCluster = onLoadData[onLoadIndex].cluster_id;
            $.ajax({ 
                type: 'POST',
                url: "{{ route('farmerVerification.getProfiles2') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    prov: $prov,
                    mun: $mun,
                    findCluster: findCluster
                },
                success: function(data){
                    if(data == 'No data.')
                    {
                        alert(data);
                        HoldOn.close();
                    }
                    else
                    {
                        $('#profiles').show();

                        if(data[0].length == 1)
                        {
                             
                            HoldOn.open(options);
                            $('#profiles').hide();
                            profileCount = data[0].length;
                            tempProfile = data[0][0].id;
                            data_id = data[0][0].id;
                            data_firstName = data[0][0].firstName;
                            data_midName = data[0][0].midName;
                            data_lastName = data[0][0].lastName;
                            data_extName = data[0][0].extName;
                            data_rsbsa_control_no = data[0][0].rsbsa_control_no;
                            data_sex = data[0][0].sex;
                            data_birthdate = data[0][0].birthdate;
                            data_mother_name = data[0][0].mother_name;
                            data_province = data[0][0].province;
                            data_municipality = data[0][0].municipality;
                            data_season = data[0][0].season;
                                var cluster = data[0][0].cluster_id;
                                $.ajax({ 
                                    type: 'POST',
                                    url: "{{ route('farmerVerification.getSuggestions') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        cluster: cluster,
                                        mun: $mun
                                    },
                                    success: function(dataSuggest){
                                        if(data=='No suggested data.')
                                    {
                                 
                                    }
                                    else
                                    {
                                        if (data_sex.charAt(0) === 'M' || data_sex.charAt(0) === 'm') {
                                            profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        } else if (data_sex.charAt(0) === 'F' || data_sex.charAt(0) === 'f') {
                                            profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        } else {
                                            profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        }
                                        $("#profiles").append(`
                                            <div class="col-md-6" style="padding-top: 1em">
                                                <div class="boxes shadow-md profiles" id="box_${data_id}">
                                                    <div style="display:flex; gap: 0.5em;">
                                                        ${profile_icon}
                                                        <ul class="info_list">
                                                            <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${data_firstName} ${data_midName} ${data_lastName} ${data_extName}</li>
                                                            <li id="rsbsa"><strong>RSBSA Number:</strong> ${data_rsbsa_control_no}</li>
                                                            <li id="season"><strong>Claimed Seeds during:</strong> ${data_season}</li>
                                                            <li id="sex"><strong>Sex:</strong> ${data_sex}</li>
                                                            <li id="bday"><strong>Birthdate:</strong> ${data_birthdate}</li>
                                                            <li id="mother"><strong>Mother's Maiden Name:</strong> ${data_mother_name}</li>
                                                            <li id="province"><strong>Home Address - Province:</strong> ${data_province}</li>
                                                            <li id="municipality"><strong>Home Address - Municipality:</strong> ${data_municipality}</li>
                                                            <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">This is not the same person as the suggested profile(s)</li>
                                                        </ul>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 2em;">
                                                        <label class="container">
                                                            <input type="checkbox" class="profile-checkbox" id="${data_id}">
                                                            <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                                <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                            </svg>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        `);
                                        $("#profiles").show();
                                        $("#suggested").show();
                                        dataSuggest.forEach(proc => {
                                            all_profiles.push(proc.id);
                                            if (proc.sex.charAt(0) === 'M' || proc.sex.charAt(0) === 'm') {
                                                profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            } else if (proc.sex.charAt(0) === 'F' || proc.sex.charAt(0) === 'f') {
                                                profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            } else {
                                                profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            }
                                            $("#suggested").append(`
                                                <div class="col-md-6" style="padding-top: 1em">
                                                    <div class="boxes shadow-md profiles suggest" id="box_${proc.id}" style="background-color: #6bfffd">
                                                        <div style="display:flex; gap: 0.5em;">
                                                            ${profile_icon}
                                                            <ul class="info_list">
                                                            <i style="position: absolute; top: 2.4rem; right: 3rem; outline: 1px solid black; background: beige; padding: 0.2em 0.4em; border-radius: 1em;"><span>*Suggested Profile</span></i>
                                                            <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${proc.firstName} ${proc.midName} ${proc.lastName} ${proc.extName}</li>
                                                            <li id="rsbsa"><strong>RSBSA Number:</strong> ${proc.rsbsa_control_no}</li>
                                                            <li id="season"><strong>Claimed Seeds during:</strong> ${proc.season}</li>
                                                            <li id="sex"><strong>Sex:</strong> ${proc.sex}</li>
                                                            <li id="bday"><strong>Birthdate:</strong> ${proc.birthdate}</li>
                                                            <li id="mother"><strong>Mother's Maiden Name:</strong> ${proc.mother_name}</li>
                                                            <li id="province"><strong>Home Address - Province:</strong> ${proc.province}</li>
                                                            <li id="municipality"><strong>Home Address - Municipality:</strong> ${proc.municipality}</li>
                                                            <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">Current profile will be linked with this suggested profile</li>
                                                            </ul>
                                                        </div>
                                                        <div style="display: flex; align-items: center; gap: 2em;">
                                                            <label class="container">
                                                                <input type="checkbox" class="profile-checkbox" id="${proc.id}">
                                                                <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                                    <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                                </svg>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            `);
                                        }); 
    
                                        $('.profile-checkbox').on('change', function() {                                        
                                            if ($(this).is(':checked')) {
                                                main_profile = this.id;
                                                $('.mainLegend').hide();
                                                $(this).closest('.profiles').find('.mainLegend').show();
                                                $('#submitButton').prop('disabled',false);
                                                $('#skipButton').prop('disabled',true);                                            
                                                $('.profile-checkbox').prop('disabled', true);
                                                $('.profile-checkbox2').hide();
                                                $(this).closest('.profiles').find('.profile-checkbox2').show();
                                                $(this).prop('disabled', false);
                                                $(this).closest('.profiles').css('background-color', '#3ed655');
                                                $(".clickable-add").show();
                                                $(this).closest('.profiles').find(".clickable-add").hide();
                                                
                                            } else {
                                                $('.mainLegend').hide();
                                                main_profile = '';
                                                sub_profiles = [];
                                                new_profiles = [];
                                                $('#submitButton').prop('disabled',true);
                                                $('#skipButton').prop('disabled',false);                                            
                                                $('.profiles').css('background-color', '');
                                                $('.suggest').css('background-color', '#6bfffd');
                                                $(".clickable-add").hide();
                                                $(".clickable-add").removeClass("checked-add");
                                                $('.profile-checkbox').prop('disabled', false);
                                                $('.profile-checkbox2').show();
                                                
                                            }
                                        });
                                    }
    
                                    }
                                });
                                HoldOn.close();
                        }
                        else
                        {
                            profileCount = data[0].length;
                            data[0].forEach(proc => {
                                all_profiles.push(proc.id);
                                if (proc.sex.charAt(0) === 'M' || proc.sex.charAt(0) === 'm') {
                                    profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                } else if (proc.sex.charAt(0) === 'F' || proc.sex.charAt(0) === 'f') {
                                    profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                } else {
                                    profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                }
                                $("#profiles").append(`
                                    <div class="col-md-6" style="padding-top: 1em">
                                        <div class="boxes shadow-md profiles" id="box_${proc.id}">
                                            <div style="display:flex; gap: 0.5em;">
                                                ${profile_icon}
                                                <ul class="info_list">
                                                    <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${proc.firstName} ${proc.midName} ${proc.lastName} ${proc.extName}</li>
                                                    <li id="rsbsa"><strong>RSBSA Number:</strong> ${proc.rsbsa_control_no}</li>
                                                    <li id="season"><strong>Claimed Seeds during:</strong> ${proc.season}</li>
                                                    <li id="sex"><strong>Sex:</strong> ${proc.sex}</li>
                                                    <li id="bday"><strong>Birthdate:</strong> ${proc.birthdate}</li>
                                                    <li id="mother"><strong>Mother's Maiden Name:</strong> ${proc.mother_name}</li>
                                                    <li id="province"><strong>Home Address - Province:</strong> ${proc.province}</li>
                                                    <li id="municipality"><strong>Home Address - Municipality:</strong> ${proc.municipality}</li>
                                                    <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">This will be treated as the main profile</li>
                                                    <li class="subLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #3ed655; padding: 0.5em;">This will be linked to the main profile selected</li>
                                                    <li class="newLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #dbdbdb; padding: 0.5em;">This will be marked as a different person from the main profile</li>
                                                </ul>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 2em;">
                                                <label class="container">
                                                    <input type="checkbox" class="profile-checkbox" id="${proc.id}">
                                                    <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                        <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                    </svg>
                                                </label>
                                                <i data-id="${proc.id}" class="fa fa-user-plus clickable-add" aria-hidden="true" style="font-size: 2.2em; display:none; margin-block-end: 0.2em;" ></i>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                            HoldOn.close();
                        }
                        $('.profile-checkbox').on('change', function() {
                        if ($(this).is(':checked')) {
                            main_profile = this.id;
                            $(this).closest('.profiles').find('.mainLegend').show();
                            $('.subLegend').show();
                            $(this).closest('.profiles').find('.subLegend').hide();
                            $('#submitButton').prop('disabled',false);
                            $('#skipButton').prop('disabled',true);                        
                            $('.profile-checkbox').prop('disabled', true);
                            $('.profile-checkbox2').hide();
                            $(this).closest('.profiles').find('.profile-checkbox2').show();
                            $(this).prop('disabled', false);
                            $(this).closest('.profiles').css('background-color', '#3ed655');
                            $(".clickable-add").show();
                            $(this).closest('.profiles').find(".clickable-add").hide();
                            
                        } else {
                            $('.mainLegend').hide();
                            $('.subLegend').hide();
                            $('.newLegend').hide();
                            main_profile = '';
                            sub_profiles = [];
                            new_profiles = [];
                            $('#submitButton').prop('disabled',true);
                            $('#skipButton').prop('disabled',false);                        
                            $('.profiles').css('background-color', '');
                            $(".clickable-add").hide();
                            $(".clickable-add").removeClass("checked-add");
                            $('.profile-checkbox').prop('disabled', false);
                            $('.profile-checkbox2').show();
                            
                        }
                        
                    });
                    }

                }
            });
        });
        $('#nextButton').on('click', () =>{
            all_profiles = [];
            console.log(tempProfile, all_profiles);
            var options = {
                theme:"custom",
                content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                message:'Please wait.',
                backgroundColor:"#494f5f",
                textColor:"white"
            };
            HoldOn.open(options);
            $("#currentCluster").empty();
            $("#currentCluster").append(onLoadIndex + 2);
            $("#prevButton").show();
            if(onLoadIndex + 1 == onLoadData.length - 1)
            {
                onLoadIndex = onLoadIndex + 1;
                $("#nextButton").hide();
            }
            else
            {
                onLoadIndex = onLoadIndex + 1;            
            }                
            

            $("#profiles").empty();
            $("#suggested").empty();

            findCluster = onLoadData[onLoadIndex].cluster_id;
            $.ajax({ 
                type: 'POST',
                url: "{{ route('farmerVerification.getProfiles2') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    prov: $prov,
                    mun: $mun,
                    findCluster: findCluster
                },
                success: function(data){
                    if(data == 'No data.')
                    {
                        alert(data);
                        HoldOn.close();
                    }
                    else
                    {
                        $('#profiles').show();

                        if(data[0].length == 1)
                        {
                             
                            HoldOn.open(options);
                            $('#profiles').hide();
                            profileCount = data[0].length;
                            tempProfile = data[0][0].id;
                            data_id = data[0][0].id;
                            data_firstName = data[0][0].firstName;
                            data_midName = data[0][0].midName;
                            data_lastName = data[0][0].lastName;
                            data_extName = data[0][0].extName;
                            data_rsbsa_control_no = data[0][0].rsbsa_control_no;
                            data_sex = data[0][0].sex;
                            data_birthdate = data[0][0].birthdate;
                            data_mother_name = data[0][0].mother_name;
                            data_province = data[0][0].province;
                            data_municipality = data[0][0].municipality;
                            data_season = data[0][0].season;
                                var cluster = data[0][0].cluster_id;
                                $.ajax({ 
                                    type: 'POST',
                                    url: "{{ route('farmerVerification.getSuggestions') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        cluster: cluster,
                                        mun: $mun
                                    },
                                    success: function(dataSuggest){
                                        if(data=='No suggested data.')
                                    {
                                        $('#beginButton').click();
                                    }
                                    else
                                    {
                                        if (data_sex.charAt(0) === 'M' || data_sex.charAt(0) === 'm') {
                                            profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        } else if (data_sex.charAt(0) === 'F' || data_sex.charAt(0) === 'f') {
                                            profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        } else {
                                            profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        }
                                        $("#profiles").append(`
                                            <div class="col-md-6" style="padding-top: 1em">
                                                <div class="boxes shadow-md profiles" id="box_${data_id}">
                                                    <div style="display:flex; gap: 0.5em;">
                                                        ${profile_icon}
                                                        <ul class="info_list">
                                                            <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${data_firstName} ${data_midName} ${data_lastName} ${data_extName}</li>
                                                            <li id="rsbsa"><strong>RSBSA Number:</strong> ${data_rsbsa_control_no}</li>
                                                            <li id="season"><strong>Claimed Seeds during:</strong> ${data_season}</li>
                                                            <li id="sex"><strong>Sex:</strong> ${data_sex}</li>
                                                            <li id="bday"><strong>Birthdate:</strong> ${data_birthdate}</li>
                                                            <li id="mother"><strong>Mother's Maiden Name:</strong> ${data_mother_name}</li>
                                                            <li id="province"><strong>Home Address - Province:</strong> ${data_province}</li>
                                                            <li id="municipality"><strong>Home Address - Municipality:</strong> ${data_municipality}</li>
                                                            <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">This is not the same person as the suggested profile(s)</li>
                                                        </ul>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 2em;">
                                                        <label class="container">
                                                            <input type="checkbox" class="profile-checkbox" id="${data_id}">
                                                            <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                                <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                            </svg>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        `);
                                        $("#profiles").show();
                                        $("#suggested").show();
                                        dataSuggest.forEach(proc => {
                                            all_profiles.push(proc.id);
                                            if (proc.sex.charAt(0) === 'M' || proc.sex.charAt(0) === 'm') {
                                                profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            } else if (proc.sex.charAt(0) === 'F' || proc.sex.charAt(0) === 'f') {
                                                profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            } else {
                                                profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            }
                                            $("#suggested").append(`
                                                <div class="col-md-6" style="padding-top: 1em">
                                                    <div class="boxes shadow-md profiles suggest" id="box_${proc.id}" style="background-color: #6bfffd">
                                                        <div style="display:flex; gap: 0.5em;">
                                                            ${profile_icon}
                                                            <ul class="info_list">
                                                            <i style="position: absolute; top: 2.4rem; right: 3rem; outline: 1px solid black; background: beige; padding: 0.2em 0.4em; border-radius: 1em;"><span>*Suggested Profile</span></i>
                                                            <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${proc.firstName} ${proc.midName} ${proc.lastName} ${proc.extName}</li>
                                                            <li id="rsbsa"><strong>RSBSA Number:</strong> ${proc.rsbsa_control_no}</li>
                                                            <li id="season"><strong>Claimed Seeds during:</strong> ${proc.season}</li>
                                                            <li id="sex"><strong>Sex:</strong> ${proc.sex}</li>
                                                            <li id="bday"><strong>Birthdate:</strong> ${proc.birthdate}</li>
                                                            <li id="mother"><strong>Mother's Maiden Name:</strong> ${proc.mother_name}</li>
                                                            <li id="province"><strong>Home Address - Province:</strong> ${proc.province}</li>
                                                            <li id="municipality"><strong>Home Address - Municipality:</strong> ${proc.municipality}</li>
                                                            <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">Current profile will be linked with this suggested profile</li>
                                                            </ul>
                                                        </div>
                                                        <div style="display: flex; align-items: center; gap: 2em;">
                                                            <label class="container">
                                                                <input type="checkbox" class="profile-checkbox" id="${proc.id}">
                                                                <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                                    <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                                </svg>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            `);
                                        }); 
    
                                        $('.profile-checkbox').on('change', function() {                                        
                                            if ($(this).is(':checked')) {
                                                main_profile = this.id;
                                                $('.mainLegend').hide();
                                                $(this).closest('.profiles').find('.mainLegend').show();
                                                $('#submitButton').prop('disabled',false);
                                                $('#skipButton').prop('disabled',true);                                            
                                                $('.profile-checkbox').prop('disabled', true);
                                                $('.profile-checkbox2').hide();
                                                $(this).closest('.profiles').find('.profile-checkbox2').show();
                                                $(this).prop('disabled', false);
                                                $(this).closest('.profiles').css('background-color', '#3ed655');
                                                $(".clickable-add").show();
                                                $(this).closest('.profiles').find(".clickable-add").hide();
                                                
                                            } else {
                                                $('.mainLegend').hide();
                                                main_profile = '';
                                                sub_profiles = [];
                                                new_profiles = [];
                                                $('#submitButton').prop('disabled',true);
                                                $('#skipButton').prop('disabled',false);                                            
                                                $('.profiles').css('background-color', '');
                                                $('.suggest').css('background-color', '#6bfffd');
                                                $(".clickable-add").hide();
                                                $(".clickable-add").removeClass("checked-add");
                                                $('.profile-checkbox').prop('disabled', false);
                                                $('.profile-checkbox2').show();
                                                
                                            }
                                        });
                                    }
    
                                    }
                                });
                                HoldOn.close();
                        }
                        else
                        {
                            profileCount = data[0].length;
                            data[0].forEach(proc => {
                                all_profiles.push(proc.id);
                                if (proc.sex.charAt(0) === 'M' || proc.sex.charAt(0) === 'm') {
                                    profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                } else if (proc.sex.charAt(0) === 'F' || proc.sex.charAt(0) === 'f') {
                                    profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                } else {
                                    profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                }
                                $("#profiles").append(`
                                    <div class="col-md-6" style="padding-top: 1em">
                                        <div class="boxes shadow-md profiles" id="box_${proc.id}">
                                            <div style="display:flex; gap: 0.5em;">
                                                ${profile_icon}
                                                <ul class="info_list">
                                                    <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${proc.firstName} ${proc.midName} ${proc.lastName} ${proc.extName}</li>
                                                    <li id="rsbsa"><strong>RSBSA Number:</strong> ${proc.rsbsa_control_no}</li>
                                                    <li id="season"><strong>Claimed Seeds during:</strong> ${proc.season}</li>
                                                    <li id="sex"><strong>Sex:</strong> ${proc.sex}</li>
                                                    <li id="bday"><strong>Birthdate:</strong> ${proc.birthdate}</li>
                                                    <li id="mother"><strong>Mother's Maiden Name:</strong> ${proc.mother_name}</li>
                                                    <li id="province"><strong>Home Address - Province:</strong> ${proc.province}</li>
                                                    <li id="municipality"><strong>Home Address - Municipality:</strong> ${proc.municipality}</li>
                                                    <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">This will be treated as the main profile</li>
                                                    <li class="subLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #3ed655; padding: 0.5em;">This will be linked to the main profile selected</li>
                                                    <li class="newLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #dbdbdb; padding: 0.5em;">This will be marked as a different person from the main profile</li>
                                                </ul>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 2em;">
                                                <label class="container">
                                                    <input type="checkbox" class="profile-checkbox" id="${proc.id}">
                                                    <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                        <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                    </svg>
                                                </label>
                                                <i data-id="${proc.id}" class="fa fa-user-plus clickable-add" aria-hidden="true" style="font-size: 2.2em; display:none; margin-block-end: 0.2em;" ></i>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                            HoldOn.close();
                        }
                        $('.profile-checkbox').on('change', function() {
                        if ($(this).is(':checked')) {
                            main_profile = this.id;
                            $(this).closest('.profiles').find('.mainLegend').show();
                            $('.subLegend').show();
                            $(this).closest('.profiles').find('.subLegend').hide();
                            $('#submitButton').prop('disabled',false);
                            $('#skipButton').prop('disabled',true);                        
                            $('.profile-checkbox').prop('disabled', true);
                            $('.profile-checkbox2').hide();
                            $(this).closest('.profiles').find('.profile-checkbox2').show();
                            $(this).prop('disabled', false);
                            $(this).closest('.profiles').css('background-color', '#3ed655');
                            $(".clickable-add").show();
                            $(this).closest('.profiles').find(".clickable-add").hide();
                            
                        } else {
                            $('.mainLegend').hide();
                            $('.subLegend').hide();
                            $('.newLegend').hide();
                            main_profile = '';
                            sub_profiles = [];
                            new_profiles = [];
                            $('#submitButton').prop('disabled',true);
                            $('#skipButton').prop('disabled',false);                        
                            $('.profiles').css('background-color', '');
                            $(".clickable-add").hide();
                            $(".clickable-add").removeClass("checked-add");
                            $('.profile-checkbox').prop('disabled', false);
                            $('.profile-checkbox2').show();
                            
                        }
                        
                    });
                    }

                }
            });
        });
        
        $('#beginButton').on('click', () =>{
            
            $prv = $('#provinces').val();
            $mun = $('#municipality').val();
            onLoadIndex = 0;
            
            $("#statistics").hide();
            $('#customSearch').hide();
            $('#profiles').hide();
            $('#submitButton').prop('disabled',true);
            $('#skipButton').prop('disabled',false);
            main_profile ='';
            sub_profiles = [];
            new_profiles = [];
            all_profiles = [];
            profileCount = 0;
            tempProfile = 0;
            $("#profiles").empty();
            $("#suggested").empty();
            var options = {
                theme:"custom",
                content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                message:'Please wait.',
                backgroundColor:"#494f5f",
                textColor:"white"
            };

            if($prv == 'default')
            {
                alert("Please select a province.");
            }
            else if($mun == 'default')
            {
                alert("Please select municipality.");
            }
            else
            {
                HoldOn.open(options);

            $.ajax({ 
                type: 'POST',
                url: "{{ route('farmerVerification.getProfiles') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    prov: $prov,
                    mun: $mun
                },
                success: function(data){
                    if(data == 'No data.')
                    {
                        alert(data);
                        HoldOn.close();
                    }
                    else
                    {
                        
                        $("#statistics").show();
                        $('#customSearch').show();
                        $('#profiles').show();
                        $("#totalForValidation").empty();
                        $("#totalValidated").empty();
                        $("#totalPending").empty();
                        $("#totalForValidation").append(data[1]);
                        $("#totalValidated").append(data[2]);
                        $("#totalPending").append(data[3]);
                        $("#noOfClusters").empty();
                        $("#noOfClusters").append(data[4]);
                        $("#currentCluster").empty();
                        $("#currentCluster").append(data[5][0].index);
                        onLoadData = data[5];
                        if(data[4] < 2)
                        {
                            $("#prevButton").hide();   
                            $("#nextButton").hide();  
                        }
                        else
                        {
                            $("#nextButton").show(); 
                        }

                        if(data[0].length == 1)
                        {
                             
                            HoldOn.open(options);
                            $("#statistics").hide();
                            $('#customSearch').hide();
                            $('#profiles').hide();
                            profileCount = data[0].length;
                            tempProfile = data[0][0].id;
                            data_id = data[0][0].id;
                            data_firstName = data[0][0].firstName;
                            data_midName = data[0][0].midName;
                            data_lastName = data[0][0].lastName;
                            data_extName = data[0][0].extName;
                            data_rsbsa_control_no = data[0][0].rsbsa_control_no;
                            data_sex = data[0][0].sex;
                            data_birthdate = data[0][0].birthdate;
                            data_mother_name = data[0][0].mother_name;
                            data_province = data[0][0].province;
                            data_municipality = data[0][0].municipality;
                            data_season = data[0][0].season;
                                var cluster = data[0][0].cluster_id;
                                $.ajax({ 
                                    type: 'POST',
                                    url: "{{ route('farmerVerification.getSuggestions') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        cluster: cluster,
                                        mun: $mun
                                    },
                                    success: function(dataSuggest){
                                        if(data=='No suggested data.')
                                    {
                                        $('#beginButton').click();
                                    }
                                    else
                                    {
                                        if (data_sex.charAt(0) === 'M' || data_sex.charAt(0) === 'm') {
                                            profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        } else if (data_sex.charAt(0) === 'F' || data_sex.charAt(0) === 'f') {
                                            profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        } else {
                                            profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                        }
                                        $("#profiles").append(`
                                            <div class="col-md-6" style="padding-top: 1em">
                                                <div class="boxes shadow-md profiles" id="box_${data_id}">
                                                    <div style="display:flex; gap: 0.5em;">
                                                        ${profile_icon}
                                                        <ul class="info_list">
                                                            <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${data_firstName} ${data_midName} ${data_lastName} ${data_extName}</li>
                                                            <li id="rsbsa"><strong>RSBSA Number:</strong> ${data_rsbsa_control_no}</li>
                                                            <li id="season"><strong>Claimed Seeds during:</strong> ${data_season}</li>
                                                            <li id="sex"><strong>Sex:</strong> ${data_sex}</li>
                                                            <li id="bday"><strong>Birthdate:</strong> ${data_birthdate}</li>
                                                            <li id="mother"><strong>Mother's Maiden Name:</strong> ${data_mother_name}</li>
                                                            <li id="province"><strong>Home Address - Province:</strong> ${data_province}</li>
                                                            <li id="municipality"><strong>Home Address - Municipality:</strong> ${data_municipality}</li>
                                                            <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">This is not the same person as the suggested profile(s)</li>
                                                        </ul>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 2em;">
                                                        <label class="container">
                                                            <input type="checkbox" class="profile-checkbox" id="${data_id}">
                                                            <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                                <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                            </svg>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        `);
                                        $("#statistics").show();
                                        $('#customSearch').show();
                                        $("#profiles").show();
                                        $("#suggested").show();
                                        dataSuggest.forEach(proc => {
                                            all_profiles.push(proc.id);
                                            if (proc.sex.charAt(0) === 'M' || proc.sex.charAt(0) === 'm') {
                                                profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            } else if (proc.sex.charAt(0) === 'F' || proc.sex.charAt(0) === 'f') {
                                                profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            } else {
                                                profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                            }
                                            $("#suggested").append(`
                                                <div class="col-md-6" style="padding-top: 1em">
                                                    <div class="boxes shadow-md profiles suggest" id="box_${proc.id}" style="background-color: #6bfffd">
                                                    <div style="display:flex; gap: 0.5em;">
                                                            ${profile_icon}
                                                            <ul class="info_list">
                                                            <i style="position: absolute; top: 2.4rem; right: 3rem; outline: 1px solid black; background: beige; padding: 0.2em 0.4em; border-radius: 1em;"><span>*Suggested Profile</span></i>
                                                            <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${proc.firstName} ${proc.midName} ${proc.lastName} ${proc.extName}</li>
                                                            <li id="rsbsa"><strong>RSBSA Number:</strong> ${proc.rsbsa_control_no}</li>
                                                            <li id="season"><strong>Claimed Seeds during:</strong> ${proc.season}</li>
                                                            <li id="sex"><strong>Sex:</strong> ${proc.sex}</li>
                                                            <li id="bday"><strong>Birthdate:</strong> ${proc.birthdate}</li>
                                                            <li id="mother"><strong>Mother's Maiden Name:</strong> ${proc.mother_name}</li>
                                                            <li id="province"><strong>Home Address - Province:</strong> ${proc.province}</li>
                                                            <li id="municipality"><strong>Home Address - Municipality:</strong> ${proc.municipality}</li>
                                                            <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">Current profile will be linked with this suggested profile</li>
                                                            </ul>
                                                        </div>
                                                        <div style="display: flex; align-items: center; gap: 2em;">
                                                            <label class="container">
                                                                <input type="checkbox" class="profile-checkbox" id="${proc.id}">
                                                                <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                                    <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                                </svg>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            `);
                                        }); 
    
                                        $('.profile-checkbox').on('change', function() {                                        
                                            if ($(this).is(':checked')) {
                                                main_profile = this.id;
                                                $('.mainLegend').hide();
                                                $(this).closest('.profiles').find('.mainLegend').show();
                                                $('#submitButton').prop('disabled',false);
                                                $('#skipButton').prop('disabled',true);                                            
                                                $('.profile-checkbox').prop('disabled', true);
                                                $('.profile-checkbox2').hide();
                                                $(this).closest('.profiles').find('.profile-checkbox2').show();
                                                $(this).prop('disabled', false);
                                                $(this).closest('.profiles').css('background-color', '#3ed655');
                                                $(".clickable-add").show();
                                                $(this).closest('.profiles').find(".clickable-add").hide();
                                                
                                            } else {
                                                $('.mainLegend').hide();
                                                main_profile = '';
                                                sub_profiles = [];
                                                new_profiles = [];
                                                $('#submitButton').prop('disabled',true);
                                                $('#skipButton').prop('disabled',false);                                            
                                                $('.profiles').css('background-color', '');
                                                $('.suggest').css('background-color', '#6bfffd');
                                                $(".clickable-add").hide();
                                                $(".clickable-add").removeClass("checked-add");
                                                $('.profile-checkbox').prop('disabled', false);
                                                $('.profile-checkbox2').show();
                                                
                                            }
                                        });
                                    }
    
                                    }
                                });
                                HoldOn.close();
                        }
                        else
                        {
                            $("#totalForValidation").empty();
                            $("#totalValidated").empty();
                            $("#totalPending").empty();
                            $("#totalForValidation").append(data[1]);
                            $("#totalValidated").append(data[2]);
                            $("#totalPending").append(data[3]);
                            $("#noOfClusters").empty();
                            $("#noOfClusters").append(data[4]);

                            profileCount = data[0].length;
                            data[0].forEach(proc => {
                                all_profiles.push(proc.id);
                                if (proc.sex.charAt(0) === 'M' || proc.sex.charAt(0) === 'm') {
                                    profile_icon = '<img src="{{ asset('public/images/maleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                } else if (proc.sex.charAt(0) === 'F' || proc.sex.charAt(0) === 'f'){
                                    profile_icon = '<img src="{{ asset('public/images/femaleFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                } else {
                                    profile_icon = '<img src="{{ asset('public/images/undefinedFarmer.PNG') }}" alt="" class="profIcon" style="width: 12em; height: 12em; margin-top:15px; margin-left:20px;"/>';
                                }
                                $("#profiles").append(`
                                    <div class="col-md-6" style="padding-top: 1em">
                                        <div class="boxes shadow-md profiles" id="box_${proc.id}">
                                            <div style="display:flex; gap: 0.5em;">
                                                ${profile_icon}
                                                <ul class="info_list">
                                                    <li style="font-size: 1.5em; font-weight: 500;" id="profileName">${proc.firstName} ${proc.midName} ${proc.lastName} ${proc.extName}</li>
                                                    <li id="rsbsa"><strong>RSBSA Number:</strong> ${proc.rsbsa_control_no}</li>
                                                    <li id="season"><strong>Claimed Seeds during:</strong> ${proc.season}</li>
                                                    <li id="sex"><strong>Sex:</strong> ${proc.sex}</li>
                                                    <li id="bday"><strong>Birthdate:</strong> ${proc.birthdate}</li>
                                                    <li id="mother"><strong>Mother's Maiden Name:</strong> ${proc.mother_name}</li>
                                                    <li id="province"><strong>Home Address - Province:</strong> ${proc.province}</li>
                                                    <li id="municipality"><strong>Home Address - Municipality:</strong> ${proc.municipality}</li>
                                                    <li class="mainLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #e3bd00; padding: 0.5em;">This will be treated as the main profile</li>
                                                    <li class="subLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #3ed655; padding: 0.5em;">This will be linked to the main profile selected</li>
                                                    <li class="newLegend" style="display:none; border: 2px solid black; border-radius: 1em; background-color: #dbdbdb; padding: 0.5em;">This will be marked as a different person from the main profile</li>
                                                </ul>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 2em;">
                                                <label class="container">
                                                    <input type="checkbox" class="profile-checkbox" id="${proc.id}">
                                                    <svg viewBox="0 0 64 64" height="2em" width="2em" class="profile-checkbox2">
                                                        <path d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16" pathLength="575.0541381835938" class="path"></path>
                                                    </svg>
                                                </label>
                                                <i data-id="${proc.id}" class="fa fa-user-plus clickable-add" aria-hidden="true" style="font-size: 2.2em; display:none; margin-block-end: 0.2em;" ></i>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                            HoldOn.close();
                        }
                        $('.profile-checkbox').on('change', function() {
                        if ($(this).is(':checked')) {
                            main_profile = this.id;
                            $(this).closest('.profiles').find('.mainLegend').show();
                            $('.subLegend').show();
                            $(this).closest('.profiles').find('.subLegend').hide();
                            $('#submitButton').prop('disabled',false);
                            $('#skipButton').prop('disabled',true);                        
                            $('.profile-checkbox').prop('disabled', true);
                            $('.profile-checkbox2').hide();
                            $(this).closest('.profiles').find('.profile-checkbox2').show();
                            $(this).prop('disabled', false);
                            $(this).closest('.profiles').css('background-color', '#3ed655');
                            $(".clickable-add").show();
                            $(this).closest('.profiles').find(".clickable-add").hide();
                            
                        } else {
                            $('.mainLegend').hide();
                            $('.subLegend').hide();
                            $('.newLegend').hide();
                            main_profile = '';
                            sub_profiles = [];
                            new_profiles = [];
                            $('#submitButton').prop('disabled',true);
                            $('#skipButton').prop('disabled',false);                        
                            $('.profiles').css('background-color', '');
                            $(".clickable-add").hide();
                            $(".clickable-add").removeClass("checked-add");
                            $('.profile-checkbox').prop('disabled', false);
                            $('.profile-checkbox2').show();
                            
                        }
                        
                    });
                    }

                }
            });
                
                

            }
        });

        $(document).on("click", ".clickable-add", function() {
            if ($(this).closest('.profiles').find('.profile-checkbox').is(':checked')) {
            }
            else
            {
                $(this).toggleClass("checked-add");
                if ($(this).hasClass("checked-add")) {
                    $(this).closest('.profiles').css('background-color', '#ffa600');
                    $(this).closest('.profiles').find('.subLegend').hide();
                    $(this).closest('.profiles').find('.newLegend').show();
                    new_profiles.push($(this).data('id'));
                } else {
                    $(this).closest('.profiles').css('background-color', '');
                    $(this).closest('.profiles').find('.subLegend').show();
                    $(this).closest('.profiles').find('.newLegend').hide();
                    const index = new_profiles.indexOf($(this).data('id'));
                    if (index > -1) {
                        new_profiles.splice(index, 1);
                    }
                }
            }
        });

        $('#submitButton').on('click', () => {
            Swal.fire({
                title: 'Are you sure you want to submit verification?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    let $mun = $('#municipality').val();
                    let main_profileStr = main_profile.toString();

                    if (profileCount > 1) {
                        sub_profiles = all_profiles.filter(item => !new_profiles.includes(parseInt(item)) && item !== main_profileStr);
                    }
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('farmerVerification.updateProfiles') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            main_profile: main_profile,
                            sub_profiles: sub_profiles,
                            new_profiles: new_profiles,
                            mun: $mun,
                            profileCount: profileCount,
                            tempProfile: tempProfile
                        },
                        success: function(data) {
                            Swal.fire('Success', 'Profile successfully validated and submitted for approval.', 'success');
                            onLoadIndex = 0;
                            findCluster = '';
                            $('#beginButton').click();
                        }
                    });
                }
            });
        });


    </script>
@endpush
