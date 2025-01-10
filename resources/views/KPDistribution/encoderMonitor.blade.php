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
                <h1>KP-IEC Encoder Monitoring</h1>
            </div>
            <div class="x_content">
                <div>

                <div id="overallData">
                        <div>
                            <h2>Overall Data</h2>
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="overallData_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>Season</th>
                                    <th>Total Encoded</th>
                                </thead>
                                <tbody>
                              
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="overallStats">
                        <div>
                            <div>
                                <h2>Overall Statistics
                                <a
                                href="#"
                                target="_blank"
                                data-toggle="modal"
                                data-target="#download_modal"
                                id="extractData"
                                class="btn btn-success btn-sm"
                                ><i class="fa fa-download"></i> Extract data</a>
                                </h2>
                            </div>
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="overall_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>Full Name</th>
                                    <th>User ID</th>
                                    <th>Overall Encoded</th>
                                    <th>Quota(As of previous contract)</th>
                                    <th>Quota(As of current contract)</th>
                                    <th>First Contract</th>
                                </thead>
                                <tbody>
                                @foreach ($overallData as $data)
                                    <tr>
                                    <td>{{ $data["Full_Name"] }}</td>
                                    <td>{{ $data["Encoder"] }}</td>
                                    <td style="background-color: 
                                        {{ $data['Total_Encoded'] < $data['QuotaPrev'] ? '#ff0000aa' : 
                                            ($data['Total_Encoded'] >= $data['QuotaPrev'] && $data['Total_Encoded'] >= $data['Quota'] ? '#33bf00' : 
                                                ($data['Total_Encoded'] >= $data['QuotaPrev'] ? '#00000000' : '#00000000')
                                            )
                                        }}; 
                                        color: 
                                        {{ $data['Total_Encoded'] < $data['QuotaPrev'] ? 'white' : 
                                            ($data['Total_Encoded'] >= $data['QuotaPrev'] && $data['Total_Encoded'] >= $data['Quota'] ? 'white' : 
                                                ($data['Total_Encoded'] >= $data['QuotaPrev'] ? 'inherit' : 'inherit')
                                            )
                                        }}">
                                        {{ $data["Total_Encoded"] }}
                                    </td>

                                    <td>{{ $data["QuotaPrev"] }}</td>
                                    <td>{{ $data["Quota"] }}</td>
                                    <td>{{ $data["First_Contract"] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="breakdown">
                        <div>
                            <h2>Breakdown</h2>
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="breakdown_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>Full Name</th>
                                    <th>User ID</th>
                                    <th>Season</th>
                                    <th>Total Encoded</th>
                                    <th>Month Encoded</th>
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

<div id="download_modal" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 20%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title">
          <span>Extract KP-IEC Data</span><br />
        </h4>
      </div>
      <div class="modal-body">
        <label for="season">Season:</label> 
          <select name="season" id="season">
              <option value="default">Select Season</option>
            </select>
        <br>
        <br>
        <label for="encoder">Encoder:</label> 
          <select name="encoder" id="encoder">
              <option value="default">Select Encoder</option>
            </select>
        <br>
        <br>

        <label for="date">Date Range:</label> 
        <input type="text" id="date" />

        <br>
        <br>
        <button
            type="button"
            id="download"
            class="btn btn-success submit">
            Download
        </button>

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
        $("#date").daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month')
        });


        window.onload = function () {
            $("#overallData_tbl").DataTable().clear();
            $("#overallData_tbl").DataTable({
            bDestroy: true,
            autoWidth: false,
            searchHighlight: true,
            processing: true,
            serverSide: true,
            orderMulti: true,
            order: [],
            ajax: {
                url: "{{ route('getOverallData') }}",
                dataType: "json",
                type: "POST",
                data: {
                _token: "{{ csrf_token() }}",
                },
            },
            columns: [
                { data: "Season" },
                { data: "Total_Encoded" }
            ],
            });

            $("#breakdown_tbl").DataTable().clear();
            $("#breakdown_tbl").DataTable({
            bDestroy: true,
            autoWidth: false,
            searchHighlight: true,
            processing: true,
            serverSide: true,
            orderMulti: true,
            order: [],
            ajax: {
                url: "{{ route('loadKpEncoderBreakdown') }}",
                dataType: "json",
                type: "POST",
                data: {
                _token: "{{ csrf_token() }}",
                },
            },
            columns: [
                { data: "Full_Name" },
                { data: "Encoder" },
                { data: "Season" },
                { data: "Total_Encoded" },
                { data: "Month_Encoded" }
            ],
            });
        };
        




        $("#download_modal").on("shown.bs.modal", function (e) {
            $('#season').empty();
            $('#encoder').empty();
            $("#date").daterangepicker({
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month')
            });

            $.ajax({
                url: "{{ route('getSeasons') }}",
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data) {
                    var $seasonSelect = $('#season');
                    $seasonSelect.append('<option value="default">Select Season</option>');
                    $.each(data, function (index, season) {
                        var optionText = season.season + ' ' + season.season_year;
                        $seasonSelect.append('<option value="' + season.season_code + '">' + optionText + '</option>');
                    });
                }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
            $.ajax({
                url: "{{ route('getEncoders') }}",
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data) {
                    var $encoderSelect = $('#encoder');
                    $encoderSelect.append('<option value="default">Select Encoder</option>');
                    $.each(data, function (index, userData) {
                        $encoderSelect.append('<option value="' + userData.userName + '">' + userData.fullName + '</option>');
                    });
                }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });

        $("#download").on("click", function (e) {
            var season = $("#season").val();
            var encoder = $("#encoder").val();
            var date = $("#date").val();
            var date1 = date.substr(0,10);
            var date2 = date.substr(13,10);

            date1 = date1.replace(/\//g, '-');
            date2 = date2.replace(/\//g, '-');
            console.log(date1,date2);
            if(season != 'default' && encoder != 'default')
            {
                $('#download_modal').modal('hide');
                window.open(`https://rcef-seed.philrice.gov.ph/rcef_ws2025/KPEncoderMonitoring/exportStatistics/${season}/${encoder}/${date1}/${date2}`);
   
            }
            else if(season == 'default' )
            {
                alert('Please select season.');
            }
            else if(encoder == 'default' )
            {
                alert('Please select encoder.');
            }
        });



    </script>
@endpush
