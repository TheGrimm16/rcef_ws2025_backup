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

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Inspector Schedule</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="inspectorTable">
                            <thead>
                                <th>Name</th>
                                <th>Region</th>
                                <th>Province</th>
                                <th>Municipality</th>
                                <th>bags</th>
                                <th>Inspection Date</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPROVE MODAL -->
        <div class="modal fade" id="inspectorModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="inspectorName"></h4>
                    </div>
                    <form action="{{ route('rcef.inspector.replace') }}" method="POST">
                        {!! csrf_field() !!}
                        <div class="modal-body">
                            <p>
                                You are about to change a designated inspector for a seed delivery. please be reminded that all accountability
                                and responsibilities in regards to the seed inspection process will also be passed to the newly assigned personnel.
                                please select a personnel and proceed.<br>
                            </p>

                            <table class="table">
                                <tr>
                                    <td id="batchTicket" align="right" width="50%" style="border-top:0;font-size: 16px;font-weight: 600;">Batch Ticket Number</td>
                                    <td id="totalBags" align="left" width="50%" style="border-top:0;font-size: 16px;font-weight: 600;">Total Bags</td>
                                </tr>
                            </table>

                            <label for="inspectorID">Please choose a new seed inspector: </label>
                            <select name="inspectorID" id="inspectorID" class="form-control">
                                @foreach($inspectors as $inspector_detail)
                                    @if($inspector_detail->middleName = '' || $inspector_detail->extName = '')
                                        <option value="{{ $inspector_detail->userId }}">{{ $inspector_detail->firstName }} {{ $inspector_detail->lastName }}</option>
                                    @else
                                        <option value="{{ $inspector_detail->userId }}">{{ $inspector_detail->firstName }} {{ $inspector_detail->middleName }} {{ $inspector_detail->lastName }} {{ $inspector_detail->extName }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" id="scheduleID" name="scheduleID" value="">                        
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> Cancel</button>
                            <button type="submit" role="submit" class="btn btn-success"><i class="fa fa-exchange"></i> Replace Inspector</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script>
        $('#inspectorModal').on('show.bs.modal', function (e) {
			var scheduleID = $(e.relatedTarget).data('id');
			var inspectorName = $(e.relatedTarget).data('name');
            var batchID = $(e.relatedTarget).data('batch');
            var bags = $(e.relatedTarget).data('bags');

			$("#scheduleID").val(scheduleID);
			$("#inspectorName").html("Current Seed Inspector - " + inspectorName);
            $("#batchTicket").html("Batch ID: " + batchID);
            $("#totalBags").html(bags + " bags (20kg/bags)");
		});

        $("#inspectorTable").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('rcef.inspector.table') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "full_name"},
                {"data": "region"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "bags"},
                {"data": "inspection_date"}
            ]
        });
    </script>
@endpush