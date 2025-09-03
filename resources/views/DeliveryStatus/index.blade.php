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
    <style>
        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        .stationMonitoring .boxes{
            margin-bottom: 1em;
        }
/* 
        .notification{
            padding: 2px;
            border-radius: 10px;
        } */

        .boxes {
            width: 100%;
            height: 100%; 
            padding: 1em 2em;
            /* border: 1px #6b6b6b;
            box-sizing: border-box;
            border-radius: 1em;
            padding: 1em 1em !important;
            backdrop-filter: blur(5px); */
            border-radius: 30px;
            background: #e0e0e0;
            box-shadow: 20px 20px 60px #bebebe,
               -20px -20px 60px #ffffff;
               border: 2px solid #c3c6ce;
                -webkit-transition: 0.5s ease-out;
                transition: 0.5s ease-out;
                overflow: visible;
        }

        .boxes:hover {
        border-color: #3ed655;
        -webkit-box-shadow: 10px 5px 18px 0 rgba(255, 255, 255, 0.877);
        box-shadow: 10px 5px 18px 0 rgba(255, 255, 255, 0.877);
        }

        .grid-container > div:nth-of-type(1) {
            grid-area: one;
        }

        .grid-container > div:nth-of-type(2) {
            grid-area: two;
        }

        .grid-container > div:nth-of-type(3) {
            grid-area: thr;
        }

        .grid-container > div:nth-of-type(4) {
            grid-area: fou;
        }

        .mini-flex{
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .mini-item{
            display: flex;
            justify-content: space-between;
            border-bottom: dotted 2px black;
            padding: 0;
        }

        .mini-item *{
            margin: 0.4em 0;
            padding: 0 0.4em;
            background: #e0e0e0;
            transform: translateY(0.7em);
        }


        .grid-container {
            display: grid;
            gap: 2em;
            /* padding: 10px; */
            grid-template-areas:
            "one one one"
            "two fou fou"
            "thr fou fou";
            grid-template-columns: 30fr 22fr 22fr;
        }

        .stationMonitoring {
            /* display: grid; */
            gap: 10px;
            padding: 10px;
        }

        .italicized {
            font-style: italic;
            color: black !important;
        }

        #overallData_tbl tbody tr:last-child {
            background-color: #CCCCCC;
            font-weight: bold;
        }

        @media screen and (max-width: 760px) {
            .grid-container {
                grid-template-areas:
                "one one"
                "two thr"
                "fou fou";
            }
        }
    </style>
@endsection

@section('content')

{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">
    
<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h1>Cooperative Delivery Status</h1>
            </div>
            <div class="x_content">
                <div>
                    <div style="padding-bottom: 1em">
                        <label for="coop">Seed Cooperative</label>
                        <select name="coop" id="coop">
                            <option value=""></option>
                            @foreach($coops as $coop)
                            <option value="{{ $coop->accreditation_no }}">{{ $coop->coopName }}</option>
                            @endforeach
                        </select>
                    </div>
                <div id="coopData">
                        <div>
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="coopData_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>Seed Cooperative</th>
                                    <th>Accreditation Number</th>
                                    <th>MOA Number</th>
                                    <th>Region</th>
                                    <th>Total Commitments</th>
                                    <th>Pending Deliveries</th>
                                    <th>Confirmed Deliveries</th>
                                    <th>Inspected Deliveries</th>
                                </thead>
                                <tbody>
                              
                                </tbody>
                            </table>
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
    <!-- <script src=" {{ asset('public/js/highcharts.js') }} "></script> -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="public/js/HoldOn.min.js"></script>

    <script>

        $('#coop').select2({
            placeholder: "Select Seed Cooperative",
            allowClear: true,
            width: '100%'
        });
        
        $('#coop').on('change', function(){
            var coop = $(this).val();
            if(coop){
                $("#coopData_tbl").DataTable().clear();
                $("#coopData_tbl").DataTable({
                    bDestroy: true,
                    autoWidth: false,
                    searchHighlight: true,
                    processing: true,
                    serverSide: true,
                    orderMulti: true,
                    order: [],
                    ajax: {
                        url: "{{ route('deliveryStatus.getCoopData') }}",
                        dataType: "json",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            coop: coop
                        },
                    },
                    columns: [
                        { data: "coopName" },
                        { data: "accreditation_no" },
                        { data: "moa_no" },
                        { data: "region" },
                        { data: "totalCommitments"},
                        { data: "pendingDeliveries" },
                        { data: "confirmedDeliveries" },
                        { data: "inspectedDeliveries" }
                    ],
                });
            } else {
                $("#coopData_tbl").DataTable().clear();
            }
        });

    </script>
@endpush
