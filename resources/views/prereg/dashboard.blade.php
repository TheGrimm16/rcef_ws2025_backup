<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <!-- <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}"> -->
  <style>
    .hidden{
        position: relative;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }

    .click_blocker{
        position: absolute;
        display: none;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        opacity: 1;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background: #00000030;
        z-index: 99999;
        transition: opacity 1s linear;
    }

    .dot:nth-of-type(1){
        width: 1rem;
        aspect-ratio: 1;
        background: #fff;
        border-radius: 100%;
        animation: dotLoading 1s ease-in-out infinite;
    }
    .dot:nth-of-type(2){
        width: 1rem;
        aspect-ratio: 1;
        background: #fff;
        border-radius: 100%;
        animation: dotLoading 1s 0.2s ease-in-out infinite;
    }
    .dot:nth-of-type(3){
        width: 1rem;
        aspect-ratio: 1;
        background: #fff;
        border-radius: 100%;
        animation: dotLoading 1s 0.4s ease-in-out infinite;
    }

    @keyframes dotLoading {
        0%, 100%{
            transform: scale(0.4);
        }
        50%{
            transform: scale(1);
        }
    }

    .gone{
        display: none;
        opacity: 0;
    }

    .vs{
        margin-top: 2rem;
    }

    .___content{
        padding: 6rem 4rem;
        /* color: black; */
    }

    .bold{
        font-weight: 700;
    }

    .omega_font{
        font-size: 4rem;
    }

    ._card{
        border-radius: 10px;
    }

    .mp{
        padding: 1rem 2rem;
    }

    ._grid{
        display: grid;
        grid-template: 
        'one two'
        'three two'
        'four two'
        ;
        gap: 2rem;
        grid-template-columns: 1fr 2fr;
    }

    ._grid_item:nth-of-type(1){
        grid-area: one;
    }
    ._grid_item:nth-of-type(2){
        grid-area: two;
    }
    ._grid_item:nth-of-type(3){
        grid-area: three;
    }
    ._grid_item:nth-of-type(4){
        grid-area: four;
    }

    .__grid{
        display: grid;
        grid-template: 
        'total reg'
        'total prv'
        'total mun'
        ;
        grid-template-columns: 2fr 3fr;
        column-gap: 4rem;
    }

    .__grid_item:nth-of-type(1){
        grid-area: total;
    }
    .__grid_item:nth-of-type(2){
        grid-area: reg;
        border-bottom: 1px solid #aaa;
    }
    .__grid_item:nth-of-type(3){
        grid-area: prv;
        border-bottom: 1px solid #aaa;
    }
    .__grid_item:nth-of-type(4){
        grid-area: mun;
        /* border-bottom: 1px solid #aaa; */
    }

    .___grid{
        position: relative;
        display: grid;
        grid-template: 
        'total female'
        'total male'
        ;
        grid-template-columns: 2fr 3fr;
        column-gap: 4rem;
    }

    .___grid_item:nth-of-type(1){
       grid-area: total; 
       width: 100%;
    }
    .___grid_item:nth-of-type(2){
       grid-area: female; 
       display: flex;
       align-items: center;
       justify-content: space-between;
       border-bottom: 1px solid #aaa;
    }
    .___grid_item:nth-of-type(3){
       grid-area: male; 
       display: flex;
       align-items: center;
       justify-content: space-between;
    }

    .grid_bottom{
        display: grid;
        grid-template: 
        'left right'
        ;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .grid_bottom_item:nth-of-type(1){
        grid-area: left;
    }
    .grid_bottom_item:nth-of-type(2){
        grid-area: right;
    }

    .footnote{
        color: #888;
        font-size: 1rem;
    }

    .fc{
        text-align: center;
    }

    /* My custom shadows */
    .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
    .shadow	    {box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
    .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
    .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
    .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
    .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
    .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
    .shadow-none	{box-shadow: 0 0 #0000;}

    *, *::before, *::after {
	 box-sizing: border-box;
    }
    :root {
        --select-border: #777;
        --select-focus: blue;
        --select-arrow: var(--select-border);
    }
    select {
        appearance: none;
        background-color: transparent;
        border: none;
        padding: 0 1em 0 0;
        margin: 0;
        width: 100%;
        font-family: inherit;
        font-size: inherit;
        cursor: inherit;
        line-height: inherit;
        z-index: 1;
        outline: none;
    }
    select::-ms-expand {
        display: none;
    }
    .select {
        display: grid;
        grid-template-areas: "select";
        align-items: center;
        position: relative;
        min-width: 15ch;
        max-width: 30ch;
        border: 1px solid var(--select-border);
        border-radius: 0.25em;
        padding: 0.25em 0.5em;
        font-size: 1.25rem;
        cursor: pointer;
        line-height: 1.1;
        background-color: #fff;
        background-image: linear-gradient(to top, #f9f9f9, #fff 33%);
    }
    .select select, .select::after {
        grid-area: select;
    }
    .select:not(.select--multiple)::after {
        content: "";
        justify-self: end;
        width: 0.8em;
        height: 0.5em;
        background-color: var(--select-arrow);
        clip-path: polygon(100% 0%, 0 0%, 50% 100%);
    }
    select:focus + .focus {
        position: absolute;
        top: -1px;
        left: -1px;
        right: -1px;
        bottom: -1px;
        border: 2px solid var(--select-focus);
        border-radius: inherit;
    }
    select[multiple] {
        padding-right: 0;
        /* * Safari will not reveal an option * unless the select height has room to * show all of it * Firefox and Chrome allow showing * a partial option */
        height: 6rem;
        /* * Experimental - styling of selected options * in the multiselect * Not supported crossbrowser */
    }
    select[multiple] option {
        white-space: normal;
        outline-color: var(--select-focus);
    }
    .select--disabled {
        cursor: not-allowed;
        background-color: #eee;
        background-image: linear-gradient(to top, #ddd, #eee 33%);
    }
    label {
        font-size: 1.125rem;
        font-weight: 500;
    }
    .select + label {
        margin-top: 2rem;
    }
    /* body {
        min-height: 100vh;
        display: grid;
        place-content: center;
        grid-gap: 0.5rem;
        font-family: "Baloo 2", sans-serif;
        background-color: #e9f2fd;
        padding: 1rem;
    } */

    .select_group{
        display: grid;
        grid-template:
        'one two three'
        ;
        grid-template-columns: 1fr 1fr 2fr;
        gap: 1rem;
    }

    .select_group .col:nth-of-type(1){
        grid-area: one;
    }
    .select_group .col:nth-of-type(2){
        grid-area: two;
    }
    .select_group .col:nth-of-type(3){
        grid-area: three;
    }

    ._loader{
        position: absolute;
        /* background: red; */
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 4rem 3rem;
    }

    ._loader2{
        position: absolute;
        /* background: red; */
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 4rem 3rem;
    }

    .select_skel, .content_skel{
        animation: skeleton 1s ease-in-out infinite;
    }

    .export_tooltip{
        display: none;
        position: absolute;
        line-height: 120%;
        right: 2rem;
        top: 1rem;
        opacity: 0;
        transform: translateY(-70%);
        z-index: 10;
        width: 20rem;
        background: rgb(210, 216, 255); 
        border-radius: 10px;
        padding: 1rem;
        transition: all 0.2s ease-in-out;
    }

    #export_data[disabled]:hover ~ .export_tooltip{
        opacity: 1;
        transform: translateY(-100%);
        display: block;
    }

    .notif_area{
        top: 0;
        left: 0;
        right: 0;
        position: fixed;
        width: 100%;
        display: flex;
        justify-content: center;
        z-index: 9999;
    }

    .notification_toast{
        position: absolute;
        background-color: #fbffaf;
        outline: 1px solid rgb(131, 255, 42);
        width: 20%;
        top: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.2rem;
        padding: 1rem;
        text-align: center;
        font-weight: 700;
        transform: translateY(-300%);
        transition: transform 0.5s cubic-bezier(0.68,-0.55, 0.36, 1.4);
        z-index: 999;
    }

    .notif_show{
        transform: translateY(50%);
    }

    @keyframes skeleton {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.2;
        }
    }
  </style>
@endsection

@section('content')

    <div class="___content">
        <div class="notif_area">
            <div class="notification_toast shadow-xl">
                Successfully processed! Downloading...
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="">
                    <h1 class="bold">Pre-registration Dashboard</h1>
                    <div class="row">
                        <div class="_card shadow-md mp">
                            <div class="_grid">
                                <div class="_grid_item shadow-md _card mp">
                                    <div class="__title">
                                        <h5 class="bold">Total Participating FCA(s)</h5>
                                    </div>
                                    <div class="__grid">
                                        <div class="__grid_item _card shadow">
                                           <center><h1 class="bold" style="font-size: 6rem;">{{$total_fca}}</h1></center>
                                            <center><h4 class="bold">FCA(s)</h4></center>
                                        </div>
                                        <div class="__grid_item">
                                            <h3><span class="bold" style="font-size: 3rem;">{{$total_fca_reg}}</span> <span style="font-size: 2rem;">region(s)</span> </h3>
                                        </div>
                                        <div class="__grid_item">
                                            <h3><span class="bold" style="font-size: 3rem;">{{$total_fca_prv}}</span> <span style="font-size: 2rem;">province(s)</span> </h3>
                                        </div>
                                        <div class="__grid_item">
                                            <h3><span class="bold" style="font-size: 3rem;">{{number_format($total_fca_muni)}}</span> <span style="font-size: 2rem;">municipalities</span> </h3>
                                        </div>
                                    </div>
                                    <span class="footnote fc">
                                        <i class="fc">
                                            *The number of regions, provinces & municipalities that participated in the pre-registration scheme. 
                                        </i>
                                    </span>
                                </div>
                                <div class="_grid_item shadow-md _card mp" style="position: relative; display: flex; justify-content: center; flex-direction: column;">
                                    <div class="card__content hidden" style="transition: opacity 1s ease-in-out;">
                                        <div class="__title" style="display: flex; justify-content: space-between;">
                                            <h5 class="bold">FCA Members Chart per Location</h5>
                                            <button id="export_data" type="button" class="btn btn-success">Export CSV</button>
                                            <span class="export_tooltip">Exporting of all regions is disabled due to heavy usage of resources. Please select at least one (1) region.</span>
                                        </div>
                                        <div class="row select_group">
                                            <div class="col">
                                                <label for="reg-select">Region</label>
                                                <div class="select">
                                                <select id="reg-select">
                                                    <option value="All">All Regions</option>
                                                    @foreach($chartReg as $reg)
                                                        <option value="{{$reg->regCode}}">{{$reg->regionName}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="focus"></span>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <label for="prv-select">Province</label>
                                                <div class="select">
                                                <select id="prv-select">
                                                    <option value="All">All Provinces</option>
                                                </select>
                                                <span class="focus"></span>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <label for="mun-select">Municipality [Export]</label>
                                                <div class="select">
                                                <select id="mun-select">
                                                    <option value="All">All Municipality</option>
                                                </select>
                                                <span class="focus"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="fca_stats" style="width:100%; height:500px;"></div>
                                    </div>
                                    <div class="_loader">
                                        <div class="header_sels" style="display: flex; gap: 2rem">
                                            <div class="select_skel" style="height: 3rem; width: 12rem; background: #ccc;"></div>
                                            <div class="select_skel" style="height: 3rem; width: 12rem; background: #ccc;"></div>
                                        </div>
                                        <div class="content_skel" style="background: #ccc; height: 90%; width: 100%; margin: 2rem 0rem;">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="_grid_item shadow-md _card mp">
                                    <div class="__title">
                                        <h5 class="bold">Estimated FCA Members</h5>
                                    </div>
                                    <div class="___grid">
                                        <div class="___grid_item _card shadow">
                                            <center><h1 class="bold omega_font">{{number_format($total_fca_prereg_ave)}}</h1></center>
                                            @IF($total_fca <= 1)
                                                <center><h4 class="bold">Total FCA Member(s)</h4></center>
                                            @ELSE
                                                <center><h4 class="bold">Average FCA Member(s)</h4></center>
                                            @ENDIF
                                        </div>
                                        <div class="___grid_item">
                                            <h3><span class="bold" style="font-size: 3rem;">{{$total_female_percent."%"}}</span> <span style="font-size: 2rem">female</span></h3><h3><i class="fa fa-venus"></i></h3>
                                        </div>
                                        <div class="___grid_item">
                                            <h3><span class="bold" style="font-size: 3rem;">{{$total_male_percent."%"}}</span> <span style="font-size: 2rem">male</span></h3><h3><i class="fa fa-mars"></i></h3>
                                        </div>
                                    </div>
                                    <span class="footnote fc">
                                        <i class="fc">
                                            *The overall number of members in every FCAs that participated in the pre-registration scheme.
                                        </i>
                                    </span>
                                </div>
                                <div class="_grid_item shadow-md _card mp">
                                    <div class="__title">
                                        <h5 class="bold">Distributed Bags</h5>
                                    </div>
                                    <div class="dist_bags">
                                        <center>
                                        <span class="bold omega_font">
                                            {{number_format($total_bags)}}
                                        </span>
                                        <span style="font-size: 2rem">bag(s)</span>
                                        </center>
                                    </div>
                                    <span class="footnote fc">
                                        <i class="fc">
                                            *The number of bags distributed to pre-registered FCA members.
                                        </i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row vs">
                        <div class="grid_bottom">
                            <div class="grid_bottom_item _card shadow-md mp" style="position: relative;">
                                <div id="click_blocker" class="click_blocker">
                                    <div class="dot"></div>
                                    <div class="dot"></div>
                                    <div class="dot"></div>
                                </div>
                                <div class="__title" style="display: flex; justify-content: space-between;">
                                    <h5 class="bold">Average Yield of Pre-registered Farmer</h5>
                                    <div id="forceSync" class="btn btn-success">Force resync</div>
                                </div>
                                <div class="avgyield_content" style="position: relative; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                    <!-- <h3 class="bold">3 charts to represent each age group, male & female yield statistics</h3> -->
                                    <!-- @php
                                        $tz  = new DateTimeZone('Europe/Brussels');
                                        $age = DateTime::createFromFormat('d/m/Y', '25/11/1998', $tz)
                                            ->diff(new DateTime('now', $tz))
                                            ->y;
                                        echo $age;
                                    @endphp -->
                                    <div id="avg_stats" style="width:100%; height:500px;"></div>
                                    <div class="_loader2">
                                        <div class="header_sels" style="display: flex; gap: 2rem">
                                            <div class="select_skel" style="height: 3rem; width: 12rem; background: #ccc;"></div>
                                            <div class="select_skel" style="height: 3rem; width: 12rem; background: #ccc;"></div>
                                        </div>
                                        <div class="content_skel" style="background: #ccc; height: 90%; width: 100%; margin: 2rem 0rem;">
                                            
                                        </div>
                                    </div>
                                    <span class="footnote" style="font-size: 1.2rem; font-style: italic; color: #888;">Data last synchronized as of <span class="bold lastSync">{{$lastSyncDate}}</span></span>
                                    <!-- <div id="female_stats" style="width:100%; height:500px;"></div> -->
                                </div>
                            </div>
                            <div class="grid_bottom_item _card shadow-md mp">
                                <div class="__title">
                                    <h5 class="bold">Crop Establishment & Ecosystem</h5>
                                </div>
                                <div class="cee_content" style="display: grid; grid-template-columns: 1fr 2fr;">
                                    <div id="cropping_stats" style="width:100%; height:500px;"></div>
                                    <div id="eco_stats" style="width:100%; height:500px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
        load_province_data();
        load_ce_data();
        load_eco_data();
        load_avgyield_data();
        regionChecker();
        checkLastSync();
        const genRanHex = size => [...Array(size)].map(() => Math.floor(Math.random() * 16).toString(16)).join('');


        function regionChecker(){
            $selected_region = $('#reg-select').find(":selected").val();
            if($selected_region == "All"){
                $("#export_data").attr("disabled", true);
                console.log($selected_region);
            }
            else {
                $("#export_data").removeAttr("disabled");
            }
        }

        $("#export_data").click(function() {
            $selected_region = $('#reg-select').find(":selected").val();
            $selected_prv = $('#prv-select').find(":selected").val();
            $selected_mun = $('#mun-select').find(":selected").val();
            $("#export_data").text("Processing...");
            $("#export_data").attr("disabled", true);
            $.ajax({
                type: 'POST',
                url: "{{ route('toCSV') }}", 
                data: {
                    _token: "{{ csrf_token() }}",
                    reg: $selected_region,
                    prv: $selected_prv,
                    mun: $selected_mun
                },
                success: function(data){
                    // console.log(window.location.hostname+'/'+data);
                    window.open(data, '_blank');
                    $(".notification_toast").addClass("notif_show");
                    $("#export_data").removeAttr("disabled");
                    $("#export_data").text("Export CSV");
                    setTimeout(() => {
                        $.ajax({
                        type: 'GET',
                        url: "{{ route('unlinking') }}", 
                        data: {
                            _token: "{{ csrf_token() }}",
                            uri: data
                        },
                        success: function(data){
                            // console.log(data);
                        }
                    });
                    setTimeout(() => {
                        $(".notification_toast").removeClass("notif_show");
                    }, 2000);
                    }, 1000);
                }
            });
        });

        function checkLastSync(){
            $lastSyncDate = ""+Date.parse($('.lastSync').text());
            $dateToday = ""+Date.now();
            
            $date1 = $lastSyncDate.substring(0, 5);
            $date2 = $dateToday.substring(0, 5);

            if($date1 != $date2){
                forceSync();
            }
        }

        $("#forceSync").click(function() {
            let c = confirm("Do you want to force resync data? This might take a while");
            if(c){
                forceSync();
            }
        });

        function forceSync(){
            $("#forceSync").text("Syncing...");
                $("#forceSync").attr("disabled", true);
                $("#click_blocker").css("display", "flex");
                $.ajax({
                type: 'GET',
                url: "{{ route('getPrv') }}", 
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    $("#forceSync").text("Force resync");
                    $("#forceSync").removeAttr("disabled");
                    $('.lastSync').empty();
                    $('.lastSync').text(data);
                    $("#click_blocker").css("display", "none");
                    $(".notification_toast").empty();
                    $(".notification_toast").text("Yield synchronization complete. Updating values...");
                    $(".notification_toast").addClass("notif_show");
                    load_avgyield_data();
                    setTimeout(() => {
                        $(".notification_toast").empty();
                        $(".notification_toast").text("Successfully processed! Downloading...");
                        $(".notification_toast").removeClass("notif_show");
                    }, 3000);
                }
            });
        }

        $('#reg-select').change(function(){
            regionChecker();
            $selected_region = $('#reg-select').find(":selected").val();
            $('#mun-select option:gt(0)').remove();
            $('#prv-select option:gt(0)').remove();
            if($selected_region == "All"){
                load_province_data();
                return;
            }
            $.ajax({
                type: 'POST',
                url: "{{ route('selRegChart') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    _region: $selected_region,
                },
                success: function(data){
                    // console.log(data);
                    load_province_chart(data.muni, data.memb);
                    $('#prv-select option:gt(0)').remove();
                    $counter = 0;
                    $.each(data.muni, function(){
                        $("#prv-select").append('<option value="'+data.muni[$counter]+'">'+data.muni[$counter]+'</option>');
                    $counter++;
                    });
                }
            });
        });

        $('#prv-select').change(function(){
            $selected_region = $('#reg-select').find(":selected").val();
            $selected_prv = $('#prv-select').find(":selected").val();
            if($selected_prv == "All"){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('selRegChart') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _region: $selected_region,
                    },
                    success: function(data){
                        load_province_chart(data.muni, data.memb);
                        $('#prv-select option:gt(0)').remove(); 
                        $counter = 0;
                        $.each(data.muni, function(){
                            $("#prv-select").append('<option value="'+data.muni[$counter]+'">'+data.muni[$counter]+'</option>');
                            $counter++;
                        });
                        $('#mun-select option:gt(0)').remove();
                    }
                });
            }else{
                $.ajax({
                type: 'POST',
                url: "{{ route('selProvChart') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: $selected_prv,
                },
                success: function(data){
                    load_province_chart(data.muni_name, data.muni_memb);
                    $('#mun-select option:gt(0)').remove();
                    $counter = 0; 
                    $.each(data.muni_name, function(){
                        $("#mun-select").append('<option value="'+data.muni_name[$counter]+'">'+data.muni_name[$counter]+'</option>');
                        $counter++;
                    });
                }
            });
            }
        });
        

        function load_province_chart(regions, members){
            var result = members.map(function (x) { 
                return parseInt(x, 10); 
            });
            $('#fca_stats').highcharts({
                chart: {
                        type: 'column',
                        backgroundColor:'#F7F7F7', 
                    },
                    title:{
                        text:'FCA Members Pre-registered per Region/Province/Municipality'
                    },
                    xAxis: {
                        categories: regions
                    },
                    yAxis: {
                        title: {
                            text: 'members'
                        }
                    },
                    series: [{
                        name: 'FCA Members',
                        data: result,
                        color: "#"+genRanHex(6)+"80"
                    }],
            });
        }

        function load_cee_chart(col, vals){
            var result = vals.map(function (x) { 
                return parseInt(x, 10); 
            });
            $('#cropping_stats').highcharts({
                chart: {
                        type: 'column',
                        backgroundColor:'#F7F7F7', 
                    },
                    title:{
                        text:'Crop Establishment'
                    },
                    xAxis: {
                        categories: col
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                        name: 'Crop Establishment',
                        data: result,
                        color: "#"+genRanHex(6)+"80"
                    }],
                    credits:{
                        enabled: false,
                    }
            });
        }

        function load_eco_chart(col, vals){
            var result = vals.map(function (x) { 
                return parseInt(x, 10); 
            });
            $('#eco_stats').highcharts({
                chart: {
                        type: 'bar',
                        backgroundColor:'#F7F7F7', 
                    },
                    title:{
                        text:'Ecosystem'
                    },
                    xAxis: {
                        categories: col
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                        name: 'Ecosystem',
                        data: result,
                        color: "#"+genRanHex(6)+"80"
                    }],
                    credits:{
                        enabled: false,
                    }
            });
        }

        function load_avg_yield_chart(overall, male, female){
            console.log(overall, male, female);
            $('#avg_stats').highcharts({
                chart: {
                        type: 'column',
                        backgroundColor:'#F7F7F7', 
                    },
                    title:{
                        text:'Average Yield per Age Group and Gender'
                    },
                    xAxis: {
                        categories: ['Overall', 'Female', 'Male']
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    series: [{
                            name: 'Age 18-29',
                            data: [overall.age_min, female.age_min, male.age_min],
                            color: "#"+genRanHex(6)+"80"
                        },{
                            name: 'Age 30-59',
                            data: [overall.age_mid, female.age_mid, male.age_mid],
                            color: "#"+genRanHex(6)+"80"
                        },{
                            name: 'Age 60+',
                            data: [overall.age_max, female.age_max, male.age_max],
                            color: "#"+genRanHex(6)+"80"
                        }
                    ],
                    credits:{
                        enabled: false,
                    }
            });
        }


        function load_province_data(){
            $.ajax({
                type: 'GET',
                url: "{{ route('regChart') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    load_province_chart(data.regArr, data.regVol);
                    $('.card__content').removeClass('hidden');
                    $('._loader').addClass('gone');
                }
            });
        }

        function load_ce_data(){
            $.ajax({
                type: 'GET',
                url: "{{ route('getCropEstab') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    load_cee_chart(data.ce_title, data.ce_stat);
                }
            });
        }

        function load_eco_data(){
            $.ajax({
                type: 'GET',
                url: "{{ route('getEcoSys') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    load_eco_chart(data.eco_title, data.eco_stat);
                }
            });
        }

        function load_avgyield_data(){
            let overAllArr;
            let maleArr;
            let femaleArr;
            $.ajax({
                type: 'GET',
                url: "{{ route('getAgeRangeView') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                    
                    parsed = JSON.parse(data);
                    overAllArr = parsed.overAllArray;
                    maleArr = parsed.maleArray;
                    femaleArr = parsed.femaleArray;
                    
                    load_avg_yield_chart(overAllArr, maleArr, femaleArr);
                    $('._loader2').addClass('gone');
                }
            });
        }
    </script>
@endpush