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
    .tab_link_active .x_content div span{
        border-bottom: 2px solid white;
    }
    span {
        cursor: pointer;
    }
    .coop-link:hover{
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
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is a scheduled report, please note that the values displayed based on live data are <b><u>updated everyday @ 12 MN</u></b> to eliminate or minimize loading time.
                </div>
            </div>
        </div>

        <div class="row" id="overall_div">

            

            <div class="col-md-12">
                <div class="x_panel">
                    {{-- <div class="x_title">
                        <h2 id="station_data_title">Overall Performance (All Branch Stations)</h2>
                        <div class="clearfix"></div>
                    </div> --}}
                    <div class="x_content form-horizontal form-label-left">
                        <iframe src="https://isd.philrice.gov.ph/rdmui/login" title="Station Dashboard" height="600px" width="100%" style="border:none;"></iframe>
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

    </script>
@endpush
