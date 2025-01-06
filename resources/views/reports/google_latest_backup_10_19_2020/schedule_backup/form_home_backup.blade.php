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

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Panel title</h3>
                    </div>
                    <div class="panel-body">
                        Panel content
                    </div>
                </div>
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
