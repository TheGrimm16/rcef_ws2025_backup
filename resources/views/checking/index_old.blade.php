@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div>
    <div class="page-title">
        <div class="title_left">
            <h3>Data Checking</h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row tile_count">

    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title col-md-12">
                    <h2>Check Farmer profile</h2>
                    <h2 class="col-md-6">
                        <div class="form-group col-md-12">
                            <div class="col-md-6">
                                <select class="form-control" id="drop_id">
                                    @foreach ($dropoff as $item)
                                    <option value="{{$item['prv_dropoff_id']}}">{{$item['dropOffPoint']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <a class="btn btn-info btn-xs" id="show_unreleased"><i class="fa fa-eye"></i> Show pending / unreleased data </a>
                            </div>
                        </div>
                    </h2>

                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-2">Keyword ( RSBSA #/ Name / QR code ): </label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" id="search_data" placeholder="Search...." autocomplete="off" autofocus value="">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-round btn-primary" id="_search">Search</button>
                        </div>
                    </div>
                    <table id="list" class="table table-responsive-sm table-bordered" style="width:100%">
                        <thead>
                        <th style="text-align: center !important;">#</th>
                        <th>RSBSA Control Number</th>
                        <th>QR Code</th>
                        <th>Full name</th>
                        <th>Seed variety</th>
                        <th>Total bags</th>
                        <th>Actual Area</th>
                        <th>Distributed Area</th>
                        <th>Date created</th>
                        <th>Action</th>
                        </thead>				
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
@push('scripts')
<script>
    $("#show_unreleased").click(function () {

        var drop_id = $("#drop_id").val();
        var myData = {drop_id: drop_id, _token: "{{ csrf_token() }}"};
//            HoldOn.open(holdon_options);
        $('#list').dataTable().fnDestroy();
        $('#list').DataTable({
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "order": [[0, "asc"]],
            "fixedHeader": {
                "header": false,
                "footer": false
            },
            searchDelay: 1000,
            oLanguage: {sProcessing: "<img src='public/images/processing.gif' />"},
            "ajax": {
                "url": "{{ route('rcef.checking.showunreleased') }}",
                "dataType": "json",
                "method": "POST",
                "data": myData
            },
            "drawCallback": function (settings) {
            },
            "columns": [
                {"data": "number"},
                {"data": "rsbsa"},
                {"data": "qr"},
                {"data": "full_name"},
                {"data": "variety"},
                {"data": "bags"},
                {"data": "actual_area"},
                {"data": "area"},
                {"data": "date", orderable: false, searchable: false},
                {"data": "action", orderable: false, searchable: false}
            ],
            "fnInitComplete": function () {
                $(".deleteDatacheck").click(function () {
                    deleteData($(this).attr("for"));
                });
                HoldOn.close();

            }

        });

    });
    $("#_search").click(function () {
        var data = $("#search_data").val();
        var drop_id = $("#drop_id").val();
        var initial_dt = $("#initial_dt").val();
        var myData = {drop_id: drop_id, search_data: data, _token: "{{ csrf_token() }}"};
        if (data != '') {
//            HoldOn.open(holdon_options);
            $('#list').dataTable().fnDestroy();
            $('#list').DataTable({
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                "order": [[0, "asc"]],
                "fixedHeader": {
                    "header": false,
                    "footer": false
                },
                searchDelay: 1000,
                oLanguage: {sProcessing: "<img src='public/images/processing.gif' />"},
                "ajax": {
                    "url": "{{ route('rcef.checking.search') }}",
                    "dataType": "json",
                    "method": "POST",
                    "data": myData
                },
                "drawCallback": function (settings) {
                },
                "columns": [
                    {"data": "number"},
                    {"data": "rsbsa"},
                    {"data": "qr"},
                    {"data": "full_name"},
                    {"data": "variety"},
                    {"data": "bags"},
                    {"data": "actual_area"},
                    {"data": "area"},
                    {"data": "date", orderable: false, searchable: false},
                    {"data": "action", orderable: false, searchable: false}
                ],
                "fnInitComplete": function () {
                    $(".deleteDatacheck").click(function () {
                        deleteData($(this).attr("for"));
                    });
                    HoldOn.close();
//            $(".actionButtons").tooltip({
//                'selector': '',
//                'placement': 'top',
//                'width': '20px'
//            });
                }

            });
        }

    });
    function deleteData(rsbsa) {
        const _token = "{{ csrf_token() }}";
        if (confirm('Are you sure you want to delete? This cannot be undone.')) {
            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.checking.delete_farmer_data') }}",
                data: {
                    _token: _token,
                    rsbsa: rsbsa
                },
                dataType: 'json',
                success: function (source) {
                    alert("Successfully deleted!");
                    location.reload();
                }
            });
        }
    }
    $("#search_data").keydown(function (e) {
        if (e.which == 13) {
            $("#_search").trigger("click");
        }
    });
 
        $('#drop_id').select2();
</script>
@endpush
