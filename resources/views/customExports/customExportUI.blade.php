<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}"> --}}
  <style>
    /* My custom shadows */
    .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
    .shadow	    {box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
    .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
    .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
    .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
    .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
    .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
    .shadow-none	{box-shadow: 0 0 #0000;}

    .title_bar{grid-area: title}
    .card1{grid-area: card1}
    .card2{grid-area: card2}
    .card3{grid-area: card3}
    .cp{padding: 2rem 1rem;}
    .round-sm{border-radius: 10px;}

    .mt{
        margin-top: 2rem;
    }

    .mp{
        padding: 3rem 2rem;
    }

    .placeholder-text{
        width: 100%;
        height: 100%;
        font-size: 2rem;
        font-weight: 600;
        color: #888;
        font-style: italic;
        text-align-last: center;
    }

    .container-fluid{
        display: grid;
        gap: 2rem;
        grid-template: 
        'title title title'
        'card1 card2 card3';
        grid-template-columns: 1fr 1fr 1fr;
    }

    .selectPrv, .selectMun{
        border-radius: 5px!important;
        width: 35%!important;
    }

    .gone{
        display: none!important;
    }

    .uncl_selects{
        display: flex;
        gap: 1rem;
    }

    .invisible{
        display: none;
    }

    .error_msg_uncl{
        display: block;
        opacity: 1;
        transition: opacity 0.5s ease-in-out;
    }

    .title{
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .loadingIcon{
        animation: loading 2s ease-in-out infinite;
    }

    @keyframes loading{
        0%{
            transform: rotate(0deg);
        }
        100%{
            transform: rotate(720deg);
        }
    }
  </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="title_bar"><h1><strong>Excel Export Corner</strong></h1></div>
    <div class="card1 shadow-md round-sm mp">
        <div class="title">
            <h4><strong>Export Unclaimed (BeP)</strong></h4>
            {{-- <i class="fa fa-check" aria-hidden="true" style="color: green; font-size: 2rem;"></i> --}}
            <span class="badge badge-pill badge-muted"><i class="fa fa-circle" aria-hidden="true" style="color: rgb(0, 190, 44);"></i> online</span>
        </div>
        <div class="mt">
            <div class="uncl_selects">
                <select name="uncl_prv" id="uncl_prv" class="form-control selectPrv" data-parsley-min="1">
                    <option value="0">Select Province</option>
                    @foreach($prvArr as $row)
                        <option value="{{$row}}">{{$row}}</option>
                    @endforeach
                </select>
                <select name="uncl_mun" id="uncl_mun" class="form-control selectMun" data-parsley-min="1">
                    <option value="0">Select Municipality</option>
                </select>
            </div>
            <button id="btnUnclExport" type="button" class="btn btn-success mt" disabled="true" style="width: 100%;">Export</button>
        </div>
        <span class="error_msg_uncl invisible" style="color: #fdd72c; font-size: 1rem; font-style: italic;">Please select at least one (1) province/municipality.</span>
    </div>
    <div class="card2 shadow-md round-sm mp">
        <div class="title">
            <h4><strong>Export Unscheduled Beneficiaries (BeP)</strong></h4>
            <div id="unsc_loader" class="loadingIcon" style="border-top: 2px solid orange; border-bottom: 2px solid orange; width: 2rem; aspect-ratio: 1; border-radius: 100%;">
                
            </div>
            <div class="checkmark gone">
                {{-- <i class="fa fa-check" aria-hidden="true" style="color: green; font-size: 2rem;"></i> --}}
                <span class="badge badge-pill badge-muted"><i class="fa fa-circle" aria-hidden="true" style="color: rgb(0, 190, 44);"></i> online</span>
            </div>
        </div>
        <div class="mt">
            <div class="uncl_selects">
                <select name="unsched_reg" id="unsched_reg" class="form-control selectPrv" data-parsley-min="1">
                    <option value="0">Select Region</option>
                </select>
                <select name="unsched_prv" id="unsched_prv" class="form-control selectMun" data-parsley-min="1">
                    <option value="0">Select Province</option>
                </select>
                <select name="unsched_mun" id="unsched_mun" class="form-control selectMun" data-parsley-min="1">
                    <option value="0">Select Municipality</option>
                </select>
            </div>
            <button id="btnUnschedExport" type="button" class="btn btn-success mt" disabled="true" style="width: 100%;">Export</button>
        </div>
        <span class="error_msg_uncl invisible" style="color: #fdd72c; font-size: 1rem; font-style: italic;">Please select at least one (1) province/municipality.</span>
    </div>
    <div class="card3 shadow-md mp round-sm">
        <div class="title">
            <h4><strong>Export Scheduled Beneficiaries (BeP)</strong></h4>
            <div class="checkmark">
                {{-- <i class="fa fa-check" aria-hidden="true" style="color: green; font-size: 2rem;"></i> --}}
                <span class="badge badge-pill badge-muted"><i class="fa fa-circle" aria-hidden="true" style="color: rgb(0, 190, 44);"></i> online</span>
            </div>
        </div>
        <div class="mt">
            <div class="uncl_selects">
                <select name="sched_prv" id="sched_prv" class="form-control selectPrv" data-parsley-min="1">
                    <option value="0">Select Region</option>
                </select>
                <select name="sched_mun" id="sched_mun" class="form-control selectMun" data-parsley-min="1">
                    <option value="0">Select Province</option>
                </select>
            </div>
            <button id="btnSchedExport" type="button" class="btn btn-success mt" style="width: 100%;">Export</button>
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
        loadPrvUnsched();
        loadPrvSched();





        // Unclaimed BEP
        $('#uncl_prv').on('change', function() {
            $selprv = $('#uncl_prv').val();
            $.ajax({
                type: 'GET',
                url: 'getMun/'+$selprv,
                dataType: 'json',
                success: function(source){
                    $('#uncl_mun option:gt(0)').remove();
                    isSelectValid();
                    $counter = 0; 
                    $.each(source, function(){
                        $("#uncl_mun").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
                        $counter++;
                    });
                }
            });
        });

        $('#uncl_mun').on('change', function() {
            isSelectValid();
        });

        $('#btnUnclExport').on('click', function() {
            $prv = $('#uncl_prv').val();
            $mun = $('#uncl_mun').val();
            if($prv == 0 || $prv == "0" || $mun == 0 || $mun == "0"){
                $('.error_msg_uncl').removeClass('invisible');
            }else{
                $('.error_msg_uncl').addClass('invisible');

                window.open("./getUnclaimedBenef/"+$prv+"/"+$mun, "_blank").focus();
            }
        });

        function isSelectValid(){
            $prv = $('#uncl_prv').val();
            $mun = $('#uncl_mun').val();
            if($prv == 0 || $prv == "0" || $mun == 0 || $mun == "0"){
                $('#btnUnclExport').attr('disabled', true);
            }else{
                $('#btnUnclExport').removeAttr('disabled');
                $('.error_msg_uncl').addClass('invisible');
            }
        }
        // END UNCLAIMED BEP








        //UNSCHED BeP
        function loadPrvUnsched(){
            isValidValues();
            $.ajax({
                type: 'GET',
                url: "{{ route('getPrvUnsched') }}",
                dataType: 'json',
                success: function(source){
                    // console.log(source);
                    $('#unsched_reg option:gt(0)').remove();
                    isSelectValid();
                    $counter = 0; 
                    $.each(source, function(){
                        $("#unsched_reg").append('<option value="'+source[$counter].regCode+'">'+source[$counter].region+'</option>');
                        $counter++;
                    });
                    $('.checkmark').removeClass('gone');
                    $('#unsc_loader').addClass('gone');
                }
            });
        }

        function loadPrvSched(){
            isValidValues();
            $.ajax({
                type: 'GET',
                url: "{{ route('getScheduledProvinces') }}",
                dataType: 'json',
                success: function(source){
                    console.log('getSched');
                    $('#sched_prv option:gt(0)').remove();
                    $counter = 0; 
                    $.each(source, function(){
                        $("#sched_prv").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
                        $counter++;
                    });
                }
            });
        }

        $("#btnSchedExport").on('click', function(){
            if($("#sched_prv").val() != 0 && $("#sched_mun").val() != 0 ){
                window.open("./getScheduled/"+$("#sched_prv").val()+"/"+$("#sched_mun").val(), "_blank").focus();
            }
        });

        $("#sched_prv").on('change', function() {
            // $("#sched_prv").val();
            $.ajax({
                type: 'GET',
                url: "getScheduledMunicipalities/"+$("#sched_prv").val(),
                dataType: 'json',
                success: function(source){
                    console.log('getSched');
                    $('#sched_mun option:gt(0)').remove();
                    $counter = 0; 
                    $.each(source, function(){
                        $("#sched_mun").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
                        $counter++;
                    });
                }
            });
        });

        $('#unsched_reg').on('change', function() {
            isValidValues();
            $prv = $('#unsched_reg').val();
            $.ajax({
                type: 'GET',
                url: "getMunUnsched/" +$prv,
                dataType: 'json',
                success: function(source){
                    $('#unsched_prv option:gt(0)').remove();
                    isSelectValid();
                    $counter = 0; 
                    $.each(source, function(){
                        $("#unsched_prv").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
                        $counter++;
                    });
                }
            });
        });

        $('#unsched_prv').on('change', function() {
            isValidValues();
            $reg = $('#unsched_reg').val();
            $prv = $('#unsched_prv').val();
            $.ajax({
                type: 'GET',
                url: "getMunLevelUnsched/" +$reg+ "/" +$prv,
                dataType: 'json',
                success: function(source){
                    // console.log(source);
                    $('#unsched_mun option:gt(0)').remove();
                    isSelectValid();
                    $counter = 0; 
                    $.each(source, function(){
                        $("#unsched_mun").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
                        $counter++;
                    });
                }
            });
        });

        $('#unsched_mun').on('change', function() {
            isValidValues();
        });

        $("#btnUnschedExport").on('click', function() {
            $reg = $('#unsched_reg').val();
            $prv = $('#unsched_prv').val();
            $mun = $('#unsched_mun').val();
            
            window.open("./unschedExport/"+$reg+"/"+$prv+"/"+$mun, "_blank").focus();
        });

        function isValidValues(){
            $reg = $('#unsched_reg').val();
            $prv = $('#unsched_prv').val();
            $mun = $('#unsched_mun').val();
            if($reg == 0 || $reg == "0"){
                $("#btnUnschedExport").attr('disabled', true);
            }
            else if($prv == 0 || $prv == "0"){
                $("#btnUnschedExport").attr('disabled', true);
            }
            else if($mun == 0 || $mun == "0"){
                $("#btnUnschedExport").attr('disabled', true);
            }
            else{
                $("#btnUnschedExport").removeAttr('disabled'); 
            }
        }
        //END UNSCHED BeP




    </script>
@endpush