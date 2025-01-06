@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
    .x_content {
        padding: 0 5px 6px;
        float: left;
        clear: both;
        margin-top: 0; 
    }
  </style>
@endsection

@section('content')

    <div>
        <div class="clearfix"></div>
            @include('layouts.message')
            <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="row tile_count">
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-truck"></i> <= 2ha > 1.5 ha</span>
                            <div class="count" id="box1_count">30%</div>
                            <span class="count_bottom">Total count of: <i class="green">300,000 </i></span>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-truck"></i> <= 1.5ha > 1 ha</span>
                            <div class="count">20%</div>
                            <span class="count_bottom">Total count of: <i class="green">200,000 </i></span>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-truck"></i> <= 1 ha > 0.5 ha</span>
                            <div class="count">30%</div>
                            <span class="count_bottom">Total count of: <i class="green">300,000 </i></span>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-truck"></i> <= 0.5 ha > 0 ha</span>
                            <div class="count">20%</div>
                            <span class="count_bottom">Total count of: <i class="green">200,000 </i></span>
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

    <script>
        //$(".tile_count .tile_stats_count #box1_count").html("sadasd");

        $.ajax({
            type: 'POST',
            url: "{{ route('api.summary.regions') }}",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(data){
               alert(data["area_cat1_res"]);
            }
        });
    </script>
@endpush
