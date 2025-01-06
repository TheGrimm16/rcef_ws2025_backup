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

        .tile_count .tile_stats_count .count {
            font-size: 30px;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12" style="min-height: 1200px;">

        <div class="x_panel">
            <div class="x_title">
                <h2><strong>RCEF-PMO GOOGLE SHEETS DATA SUMMARY</strong></h2>
				<a href="{{route('rcep.google_sheet.summary_export')}}" target="_blank" class="btn btn-success btn-sm pull-right" id="export_summary_btn"><i class="fa fa-table"></i> EXPORT SUMMARY</a>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-bordered table-striped" id="summary_tbl">
                    <thead>
                        <th>Station</th>
                        <th>Region</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>DS2021 Allocated</th>
                        <th>Delivered/Transferred</th>
                        <th>Distributed</th>
                        <th>Ongoing Distribution</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        @foreach ($seed_array as $row)
                            <tr>
                                <td>{{$row["station"]}}</td>
                                <td>{{$row["region"]}}</td>
                                <td>{{$row["province"]}}</td>
                                <td>{{$row["municipality"]}}</td>
                                <td>{{$row["ds2021_allocated"]}}</td>
                                <td>{{$row["schedule_delivered"]}}</td>
                                <td>{{$row["actual_distributed"]}}</td>
                                <td>{{$row["ongoing_distribution"]}}</td>
                                <td>
                                    <a href="#" data-toggle="modal" data-target="#show_more_modal" data-id="{{$row['balance_id']}}" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i> VIEW MORE</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div id="show_more_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 10%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="show_more_title">
                        [PROIVINCE] < [MUNICIPALITY]
                    </h4>
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <input type="text" value="" name="balance_id" id="balance_id" style="display: none;">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Station</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="station_name">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Region</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="region_name">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Region</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="province_name">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Region</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="municipality_name">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>DS2021 Allocated</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="ds2021_allocated">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Delivered/Transferred</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="shcedule_value">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Distributed</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="actual_schedule_value">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Ongoing Distribution</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="delivery_data_value">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Farmer Beneficiaries</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="sms_beneficiaries">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Area Planted (ha)</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="sms_bags">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Delivered/Transferred</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="sms_area">--</div>
                                        </div>
                                    </div>
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
        $("#summary_tbl").DataTable({
            "pageLenght": 50
        });

        $('#show_more_modal').on('show.bs.modal', function (e) {
            var balance_id = $(e.relatedTarget).data('id');
            $("#balance_id").val(balance_id);
            $.ajax({
                type: 'POST',
                url: "{{ route('rcep.google_sheet.show_more') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    balance_id: balance_id
                },
                success: function(data){
                    $("#show_more_title").empty().html(data["province"]+" < "+data["municipality"]);
                    $("#station_name").empty().html(data["station"]);
                    $("#region_name").empty().html(data["region"]);
                    $("#province_name").empty().html(data["province"]);
                    $("#municipality_name").empty().html(data["municipality"]);
                    $("#ds2021_allocated").empty().html(data["ds2021_allocated"]);
                    $("#shcedule_value").empty().html(data["schedule_delivered"]);
                    $("#actual_schedule_value").empty().html(data["actual_distributed"]);
                    $("#delivery_data_value").empty().html(data["ongoing_distribution"]);
                    $("#sms_beneficiaries").empty().html(data["sms_beneficiaries"]);
                    $("#sms_bags").empty().html(data["sms_bags"]);
                    $("#sms_area").empty().html(data["sms_area"]);
                }
            });
        });
    </script>
@endpush
