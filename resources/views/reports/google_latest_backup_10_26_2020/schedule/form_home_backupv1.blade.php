@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Filter Options
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Type:</label>
                        <div class="col-md-10 col-sm-10 col-xs-10" required>
                            <select name="seed_type" id="seed_type" class="form-control" required>
                                <option value="INVENTORY_DS">Inventory (DS2020)</option>
                                <option value="INVENTORY_WS">Inventory (WS2020)</option>
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

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
                        <div class="col-md-10 col-sm-10 col-xs-10" required>
                            <button class="btn btn-primary"><i class="fa fa-list-ol"></i> ADVANCED OPTIONS</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Seed Schedule
                </h2>
                <a href="{{route('rcep.google_sheet.schedule_form')}}" class="btn btn-success pull-right" style="border-radius:20px;"><i class="fa fa-plus-circle"></i> ADD SCHEDULE</a>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-striped table-bordered" id="schedule_tbl">
                    <thead>
                        <th>Transaction Code</th>
                        <th>Seed Type</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div><br>        

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
            "searching": false,
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
