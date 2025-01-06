<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}"/>
    <link
    rel="stylesheet"
    href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}"/>
    <link href="public/css/HoldOn.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        #overallData_tbl tbody tr {
            background-color: white !important;
            font-weight: normal !important;
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
                <h1>Farmer KYC Monitoring</h1>
            </div>
            <div class="x_content">
                <div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap:1em; position:relative">
                    <div class="boxes shadow-sm;" style="height: 75%; display:flex; flex-direction:column; padding-bottom: 4em">
                        <h2 id="totalValidated" style="font-size: 2.5em; margin:0; padding: 0"><strong>{{$totalClustersVerified}}</strong> out of {{$totalClusters}} ({{$clustersPercentage}}%)</h2>
                        <h4 style="margin:0; padding: 0">Total Clusters Validated</h4>
                    </div>
                    <div class="boxes shadow-sm;" style="height: 75%; display:flex; flex-direction:column; padding-bottom: 4em">
                        <h2 id="totalValidated" style="font-size: 2.5em; margin:0; padding: 0"><strong>{{$totalProfilesVerified}}</strong> out of {{$totalProfiles}} ({{$profilesPercentage}}%)</h2>
                        <h4 style="margin:0; padding: 0">Total Farmer Profiles Validated</h4>
                    </div>
    
                    <div class="boxes shadow-sm;" style="height: 75%; padding-bottom: 4em">
                            <label for="date">Select Date:</label>
                            <div class="form-group" style="display:flex; gap:1em">
                                <input type="text" id="datepicker" name="date" class="form-control" placeholder="Click here to select date" style="width: max-content;height: 2.5em ">
                                <button type="submit" id="submitDate" class="btn btn-primary mt-3" style="height: 2.5em" autocomplete="off">Submit</button>
                            </div>
                    </div>
                </div>


                <div id="overallData">
                        <div>
                            <h2>Overall Data</h2>
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="overallData_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>User</th>
                                    <th id="titleTotalVerified">Total Verified for Today</th>
                                    <th>Total Verified</th>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $("#date").daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month')
        });


        window.onload = function () {
            $("#datepicker").attr("autocomplete", "off");
            var options = {
                    theme:"custom",
                    content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                    message:'Please wait.',
                    backgroundColor:"#494f5f",
                    textColor:"white"
                };
            HoldOn.open(options);
            $("#overallData_tbl").DataTable().clear();
            $("#overallData_tbl").DataTable({
                bDestroy: true,
                autoWidth: false,
                searchHighlight: true,
                processing: true,
                serverSide: true,
                orderMulti: true,
                order: [],
                pageLength: 25,
                stripeClasses: [],
                ajax: {
                    url: "{{ route('getOverallData_kyc') }}",
                    dataType: "json",
                    type: "POST",
                    data: {
                    _token: "{{ csrf_token() }}",
                    },
                    complete: function() {
                        HoldOn.close();
                    }
                },
                columns: [
                    { data: "updatedBy" },
                    { data: "totalToday", defaultContent: "0", render: $.fn.dataTable.render.number(',', '.', 0)},
                    { data: "totalVerified", defaultContent: "0", render: $.fn.dataTable.render.number(',', '.', 0)}
                ]
            });

        };

        $("#datepicker").datepicker({
            dateFormat: "yy-mm-dd",
            maxDate: 0
        });

        $('#submitDate').on('click', () => {
            date = $('#datepicker').val();
            if(date.length > 1)
            {
                document.getElementById("titleTotalVerified").innerText = "Total Verified For Selected Date";
                var options = {
                    theme:"custom",
                    content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                    message:'Please wait.',
                    backgroundColor:"#494f5f",
                    textColor:"white"
                };

                HoldOn.open(options);
                $("#overallData_tbl").DataTable().clear();
                $("#overallData_tbl").DataTable({
                    bDestroy: true,
                    autoWidth: false,
                    searchHighlight: true,
                    processing: true,
                    serverSide: true,
                    orderMulti: true,
                    order: [],
                    pageLength: 25,
                    stripeClasses: [],
                    ajax: {
                        url: "{{ route('getDatedOverallData_kyc') }}",
                        dataType: "json",
                        type: "POST",
                        data: {
                        _token: "{{ csrf_token() }}",
                        date: date
                        },
                        complete: function() {
                            HoldOn.close();
                        }
                    },
                    columns: [
                        { data: "updatedBy" },
                        { data: "totalToday", defaultContent: "0", render: $.fn.dataTable.render.number(',', '.', 0)},
                        { data: "totalVerified", defaultContent: "0", render: $.fn.dataTable.render.number(',', '.', 0)}
                    ]
                });
            }
            else
            {
                Swal.fire({
                    title: 'Warning',
                    text: 'Please select a date',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    showCancelButton: false
                });
            }
        });
    </script>
@endpush
