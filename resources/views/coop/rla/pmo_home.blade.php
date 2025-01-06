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
    input[type=number]::-webkit-inner-spin-button {
        opacity: 1
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Participating Seed Cooperatives
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                @foreach ($coop_list as $row)
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0" style="margin:0">
                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                    {{$row->coopName}}
                                </button>
                                <a href="#" data-toggle="modal" data-target="#show_coop_rla" data-coop="{{$row->accreditation_no}}" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> VIEW RLA REQUESTS</a>
                            </h5>
                        </div>
                    </div>
                @endforeach
            </div>
        </div><br>        
    </div>


    <div id="show_coop_rla" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 3%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="coop_name">
                        {COOP_NAME}
                    </h4>
                    <span id="coop_accreditation">{COOP_ACCREDITATION}</span>
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Total RLA Requests</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_request"><i class="fa fa-folder-open"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Total Approved RLA</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_approved"><i class="fa fa-thumbs-up"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Total Rejected RLA</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_rejected"><i class="fa fa-thumbs-down"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>BPI-NSQCS (AUTOMATIC)</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0">
                                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count" id="total_bpi"><i class="fa fa-check-circle"></i> --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="rla_tbl">
                                <thead>
                                    <th>Seed Grower</th>
                                    <th>Seed Tag</th>
                                    <th>Seed Variety</th>
                                    <th>Bags Passed</th>
                                    <th>Certification Date</th>
                                    <th>Date Recorded</th>
                                    <th style="width: 150px;">Status</th>
                                </thead>
                            </table>
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

    <script>
        $("#rla_tbl").DataTable();


        $('#show_coop_rla').on('show.bs.modal', function (e) {
            var accreditation_no = $(e.relatedTarget).data('coop');
            
            $("#coop_name").empty().html("Fetching data please wait...");
            $("#coop_accreditation").empty().html("Fetching data please wait...");
            $("#total_request").empty().html('loading...');
            $("#total_approved").empty().html('loading...');
            $("#total_rejected").empty().html('loading...');
            $("#total_bpi").empty().html('loading...');

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.rla.pmo_get_coop') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    accreditation_no: accreditation_no
                },
                success: function(data){
                    $("#coop_name").empty().html(data["coop_name"]);
                    $("#coop_accreditation").empty().html("Accreditation Number: "+accreditation_no);

                    $("#total_request").empty().html('<i class="fa fa-folder-open"></i> '+data["total_requests"]);
                    $("#total_approved").empty().html('<i class="fa fa-thumbs-up"></i> '+data["total_passed"]);
                    $("#total_rejected").empty().html('<i class="fa fa-thumbs-down"></i> '+data["total_rejected"]);
                    $("#total_bpi").empty().html('<i class="fa fa-check-circle"></i> '+data["total_bpi"]);
                    //$("#total_request").empty().html(data["total_requests"]);
                    //$("#total_approved").empty().html(data["total_passed"]);
                    //$("#total_rejected").empty().html(data["total_rejected"]);
                }
            }).done(function(e){
                //override rla table
                $("#rla_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('coop.rla.pmo_tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            accreditation_no : accreditation_no
                        }
                    },
                    "columns":[
                        {data: 'sg_name'},
                        {data: 'seed_tag'},
                        {data: 'seed_variety'},
                        {data: 'no_of_bags'},
                        {data: 'certification_date'},
                        {data: 'date_recorded'},
                        {data: 'rla_status'},
                    ]
                });

            });
        });
    </script>
@endpush
