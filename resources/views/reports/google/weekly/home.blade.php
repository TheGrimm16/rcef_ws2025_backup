@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    <div class="col-md-12 col-sm-12 col-xs-12" style="height: 1200px;">

        @include('layouts.message')

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0" style="margin:0">
                            <button style="color: #7387a8;text-decoration:none;font-weight: 600;font-size:20px;" class="btn btn-link">
                                TAGGED PHILRICE BRANCH STATION: <u>{{strtoupper($station_name)}}</u>
                            </button>
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Bags Distributed (20kg/bag)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-truck"></i> {{$total_bags_distributed}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Beneficiaries</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-users"></i> {{$total_farmer_beneficiaries}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Area Planted (ha)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-map-marker"></i> {{$total_area_planted}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{route('rcep.google_sheet.weeklySave')}}" method="POST" data-parsley-validate="" id="actual_form">
            {{ csrf_field() }}
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        GOOGLE SHEETS | WEEKLY REPORT
                    </h2>

                    <button role="submit" type="submit" class="btn btn-success pull-right" style="border-radius:20px;"><i class="fa fa-plus-circle"></i> ADD TO REPORT</button>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Province:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="province" id="province" class="form-control">
                                    <option value="0">Please select a province</option>
                                    @foreach ($provinces as $to_province)
                                        <option value="{{$to_province->province}}">{{$to_province->province}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Municipality:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="municipality" id="municipality" class="form-control">
                                    <option value="0">Please select a municipality</option>
                                </select>
                            </div>
                        </div>
                        <hr>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Date Range:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <input type="text" name="date_range" id="date_range" class="form-control" value="{{$filter_start}} - {{$filter_end}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Bags Distributed:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <input type="number" name="bags_distributed" id="bags_distributed" class="form-control" value="0" min="1" max="99999">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Farmer Beneficiaries:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <input type="number" name="farmer_beneficiaries" id="farmer_beneficiaries" class="form-control" value="0" min="1" max="99999">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Area planted (ha):</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <input type="number" name="area_planted" id="area_planted" class="form-control" value="0" min="1" max="99999" step=".01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </form>
    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#date_range").daterangepicker(null,function(a,b,c){
            //console.log(a.toISOString(),b.toISOString(),c)
        });

        $("#province").on("change", function(e){
            $("#municipality").empty().append("<option value='0'>Loading muicipalities please wait...</option>");
            var province = $(this).val();
            //load all municipalities
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.weekly.dop_municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append(data);
                }
            });
        });
    </script>
@endpush
