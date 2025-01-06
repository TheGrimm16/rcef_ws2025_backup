@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
    <style>
        .tab_link_active {
            color: #fff;
            background-color: #337ab7;
            border-color: #2e6da4;
            text-align: center;
        }

        .tab_link_active .x_content div span {
            border-bottom: 2px solid white;
        }

        span {
            cursor: pointer;
        }

        .coop-link:hover {
            color: blue;
            text-decoration: underline;
        }

        .tile_count .tile_stats_count:before {
            content: "";
            position: absolute;
            left: 0;
            height: 65px;
            border-left: 0;
            margin-top: 10px;
        }

        tr td,
        th {
            text-align: center
        }

        .text-left {
            text-align: left
        }

        .main-pb {
            width: 200px;
            margin: 0 auto;
        }

        .submain-pb {
            width: 100%;
            height: 28px;
            background-color: rgb(211, 210, 210);
        }

        .delivered-pb {
            /* text-align: left; */
            float: left;
            background-color: #28a745 !important;
            height: 100%;
        }

        .scheduled-pb {
            margin: 0px;
            float: left;
            background-color: #ffc107 !important;
            height: 100%;
        }

        .dangered-pb {
            margin: 0px;
            float: left;
            background-color: #dc3545 !important;
            height: 100%;
        }

        #tableBodyID td {
            height: 90px;
        }

        tbody {
            display: block;
            height: 60vh;
            overflow: auto;
        }

        thead,
        tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        /* width */
        ::-webkit-scrollbar {
            width: 2px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #888;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection

@section('content')
    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Delivery Calendar Dashboard</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-3">
                                    <h1><span id="MonthSelected">{{ $current_month }}</span></h1>
                                    <span>Legend: </span>
                                    <span style="color: #28a745">DELIVERED</span>
                                    <span style="color: #ffc107">PENDING</span>
                                    <span style="color: #dc3545">CANCELLED</span>
                                </div>
                                <div class="col-md-2" style="">
                                    Month: 
                                    <select id="MonthSelect" class="js-example-basic-single" name="state"
                                        style="width: 100%">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['value'] }}" data-label="{{ $m['label'] }}"
                                                {{ $current_month == $m['label'] ? 'selected' : '' }}>{{ $m['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7" style="">
                                    <div style="display: inline-block">
                                        Region:<br/>
                                        <select name="region" id="region" name="region" class="js-example-basic-single">
                                            <option value="0">Please select a region</option>
                                            @foreach ($regions as $row)
                                                <option value="{{$row->regionName}}">{{$row->regionName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="display: inline-block">
                                        Province:<br/>
                                        <select name="province" id="province" name="province" class="js-example-basic-single" style="width: 200px">
                                            <option value="0">Please select a province</option>
                                        </select>
                                    </div>
                                    <div style="display: inline-block">
                                        Cooperatives:<br/>
                                        <select name="coop" id="coop" class="js-example-basic-single">
                                            <option value="0">Please select a cooperative</option>
                                            @foreach ($coop as $c)
                                                <option value="{{$c->current_moa}}">{{$c->coopName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- TAB MODAL -->
            <div id="DeliverySchedModal" class="modal fade " role="dialog">
                <div class="modal-dialog" style="width:70%;">
                    <div class="modal-content">
                        <div class="modal-header">

                        </div>
                        <div class="modal-body">

                        </div>
                    </div>
                </div>
            </div>
            <!-- TAB MODAL -->

        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">

                    <div class="x_content form-horizontal form-label-left">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-bordered" id="tableBodyID">
                                        
                                    </table>

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
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script>
        $('.js-example-basic-single').select2();
        getData();
        $('#MonthSelect').on('change', function() {
            getData();
        });

        function getData() {
            $('#MonthSelected').text($('#MonthSelect').find(':selected').data('label'));
            $.ajax({
                type: "get",
                url: "{{ route('dashboard.delivery.calendar.data') }}",
                "data": {
                    "_token": "{{ csrf_token() }}",
                    "date": $('#MonthSelect').val(),
                    "region": $('#region').val(),
                    "province": $('#province').val(),
                    "coop": $('#coop').val(),
                },
                success: function(response) {
                    $('#tableBodyID').html(response);
                }
            });
        }

        $("#region").on("change", function(e){
            var region = $("#region").val();
            $("#province").empty().append("<option value='0'>Loading provinces...</option>");
            $("#municipality").empty().append("<option value='0'>Please select a municipality</option>");
            
            $.ajax({
                type: 'POST',
                url: "{{ route('report.variety.provinces_data') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#province").empty().append("<option value='0'>Please select a province</option>");
                    $("#province").append(data);
                    $("#filter_btn").removeAttr("disabled");
                    getData();
                }
            });
        });

        $("#province").on("change", function(e){
            var province = $("#province").val();
            getData();   
        });

        $("#coop").on("change", function(e){
            var coop = $("#coop").val();
            getData();   
        });
    </script>
@endpush
