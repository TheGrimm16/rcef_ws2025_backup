@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
        .panel-heading {
            padding: 20px 15px;
            border-bottom: 1px solid transparent;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12" style="min-height: 1800px;">
        
        <div class="row" style="margin-top: 10px;margin-bottom:10px;">
            <div class="col-md-8">
                <a href="{{route('rcep.google_sheet.schedule_form')}}" class="btn btn-success pull-left"><i class="fa fa-plus-circle"></i> ADD SCHEDULE</a>
                <a href="" data-toggle="modal" data-target="#filter_options_modal" class="btn btn-primary pull-left"><i class="fa fa-list-ol"></i> FILTER OPTIONS</a>  
            </div>

            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-8" style="padding-right: 0;">
                        <select name="filter_category" id="filter_category" class="form-control">
                            <option value="ALL">SHOW ALL</option>
                            <option value="SEED_COOP">SEED COOPERATIVE</option>
                            <option value="PHILRICE_WAREHOUSE">PHILRICE WAREHOUSE</option>
                            <option value="LGU_STOCKS">STOCKS IN LGU</option>
                            <option value="TRANSFERRED_SEEDS">TRANSFERRED SEEDS</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success form-control"><i class="fa fa-filter"></i> FILTER</button>
                    </div>
                </div>
            </div>
        </div>
        
        @foreach ($schedule_list as $row)
            @if($row->seed_type == "NEW" AND $row->source == "SEED_COOP" OR 
                $row->seed_type == "INVENTORY" AND $row->source == "SEED_COOP")
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <strong>{{$row->transaction_code}}</strong>
                            @if($row->edit_final_flag == 1)
                                <a href="{{route('rcep.google_sheet.actual', $row->transaction_code)}}" class="btn btn-danger btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;margin-left: 10px;"><i class="fa fa-send"></i> SUBMIT ACTUAL</a>
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @else
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @endif
                        </h3>
                    </div>
                    <div class="panel-body" style="background-color: white;">
                        <div class="panel-wrapper" style="font-size: 15px;font-family: inherit;">
                            <div><strong>SOURCE: </strong>{{$row->seed_type}}, {{$row->source}}</div>
                            <div><strong>SEED COOPERATIVE: </strong>{{$row->from_coop}}</div>
                            <div><strong>BAGS FOR DELIVERY: </strong>{{$row->total_bags_delivered}} bag(s)</div>
                            <div><strong>DESTINATION: </strong>{{strtoupper($row->to_province)}} < {{strtoupper($row->to_municipality)}} < {{strtoupper($row->to_dop)}}</div>
                            <div><strong>DELIVERY DATE: </strong>{{date("F j, Y", strtotime($row->to_delivery_date))}}</div>
                            <div><strong>DATE RECORDED: </strong>{{date("F j, Y g:i A", strtotime($row->date_recorded))}}</div>
                        </div>
                    </div>
                </div>

            @elseif($row->seed_type == "INVENTORY" AND $row->source == "PHILRICE_WAREHOUSE")
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <strong>{{$row->transaction_code}}</strong>
                            @if($row->edit_final_flag == 1)
                                <a href="{{route('rcep.google_sheet.actual', $row->transaction_code)}}" class="btn btn-danger btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;margin-left: 10px;"><i class="fa fa-send"></i> SUBMIT ACTUAL</a>
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @else
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @endif
                        </h3>
                    </div>
                    <div class="panel-body" style="background-color: white;">
                        <div class="panel-wrapper" style="font-size: 15px;font-family: inherit;">
                            <div><strong>SOURCE: </strong>{{$row->seed_type}}, {{$row->source}}</div>
                            <div><strong>SEED COOPERATIVE: </strong>{{$row->from_coop}}</div>
                            <div><strong>BAGS FOR DELIVERY: </strong>{{$row->total_bags_delivered}} bag(s)</div>
                            <div><strong>PLACE OF ORIGIN: </strong>{{strtoupper($row->from_province)}} < {{strtoupper($row->from_municipality)}} < {{strtoupper($row->from_dop)}}</div>
                            <div><strong>DESTINATION: </strong>{{strtoupper($row->to_province)}} < {{strtoupper($row->to_municipality)}} < {{strtoupper($row->to_dop)}}</div>
                            <div><strong>DELIVERY DATE: </strong>{{date("F j, Y", strtotime($row->to_delivery_date))}}</div>
                            <div><strong>DATE RECORDED: </strong>{{date("F j, Y g:i A", strtotime($row->date_recorded))}}</div>
                        </div>
                    </div>
                </div>

            @elseif($row->seed_type == "INVENTORY" AND $row->source == "LGU_STOCKS")
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <strong>{{$row->transaction_code}}</strong>
                            @if($row->edit_final_flag == 1)
                                <a href="{{route('rcep.google_sheet.actual', $row->transaction_code)}}" class="btn btn-danger btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;margin-left: 10px;"><i class="fa fa-send"></i> SUBMIT ACTUAL</a>
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @else
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @endif
                        </h3>
                    </div>
                    <div class="panel-body" style="background-color: white;">
                        <div class="panel-wrapper" style="font-size: 15px;font-family: inherit;">
                            <div><strong>SOURCE: </strong>{{$row->seed_type}}, {{$row->source}}</div>
                            <div><strong>BAGS REMAINING IN LGU: </strong>{{$row->total_bags_lgu}} bag(s)</div>
                            <div><strong>PLACE OF ORIGIN: </strong>{{strtoupper($row->from_province)}} < {{strtoupper($row->from_municipality)}} < {{strtoupper($row->from_dop)}}</div>
                            <div><strong>ASSIGNED PC: </strong>{{$row->from_assigned_pc}}</div>
                            <div><strong>DATE RECORDED: </strong>{{date("F j, Y g:i A", strtotime($row->date_recorded))}}</div>
                        </div>
                    </div>
                </div>

            @elseif($row->seed_type == "INVENTORY" AND $row->source == "TRANSFERRED_SEEDS")
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <strong>{{$row->transaction_code}}</strong>
                            @if($row->edit_final_flag == 1)
                                <a href="{{route('rcep.google_sheet.actual', $row->transaction_code)}}" class="btn btn-danger btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;margin-left: 10px;"><i class="fa fa-send"></i> SUBMIT ACTUAL</a>
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @else
                                <a href="{{route('rcep.google_sheet.view', $row->transaction_code)}}" class="btn btn-warning btn-sm pull-right" style="border-radius: 20px;margin-top: -7px;margin-right: -7px;"><i class="fa fa-eye"></i> VIEW SCHEDULE</a>
                            @endif
                        </h3>
                    </div>
                    <div class="panel-body" style="background-color: white;">
                        <div class="panel-wrapper" style="font-size: 15px;font-family: inherit;">
                            <div><strong>SOURCE: </strong>{{$row->seed_type}}, {{$row->source}}</div>
                            <div><strong>BAGS TRANSFERRED: </strong>{{$row->total_bags_transfer}} bag(s)</div>
                            <div><strong>PLACE OF ORIGIN: </strong>{{strtoupper($row->from_province)}} < {{strtoupper($row->from_municipality)}} < {{strtoupper($row->from_dop)}}</div>
                            <div><strong>DESTINATION: </strong>{{strtoupper($row->to_province)}} < {{strtoupper($row->to_municipality)}} < {{strtoupper($row->to_dop)}}</div>
                            <div><strong>ASSIGNED PC: </strong>{{$row->from_assigned_pc}}</div>
                            <div><strong>TRANSFER DATE: </strong>{{date("F j, Y", strtotime($row->to_transfer_date))}}</div>
                            <div><strong>DATE RECORDED: </strong>{{date("F j, Y g:i A", strtotime($row->date_recorded))}}</div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div id="filter_options_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        FILTER OPTIONS
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Type:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="seed_type" id="seed_type" class="form-control" required>
                                    <option value="INVENTORY">Inventory (DS2019/WS2020)</option>
                                    <option value="NEW" selected>New Seeds (DS2021)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Source</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="source" id="source" class="form-control" required>
                                    <option value="SEED_COOP">Seed Cooperative / Association</option>
                                    <option value="PHILRICE_WAREHOUSE">PhilRice Designated Warehouse</option>
                                    <option value="LGU_STOCKS">Stocks in LGU</option>
                                    <option value="TRANSFERRED_SEEDS">Transferred Seeds</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Status:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="APPROVED">Approved</option>
                                    <option value="RESCHEDULED">Re-Scheduled</option>
                                    <option value="CANCELLED">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="generate_flsar_btn_excel"><i class="fa fa-list-ol"></i> Generate FLSAR (EXCEL)</button>
                </div>
                </form>
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
        $('#schedule_tbl').DataTable().clear();
        $("#schedule_tbl").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('rcep.google_sheet.tbl') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "transaction_code"},
                {"data": "seed_type"},
                {"data": "source"},
                {"data": "status_str", searchable: false},
                {"data": "action", searchable: false}
            ]
        });
    </script>
@endpush
