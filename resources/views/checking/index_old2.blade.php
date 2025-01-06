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


        <div id="edit_check_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">EDIT FARMER DETAILS</h4>
                    </div>
                    <div class="modal-body form-horizontal form-label-left">
                        <input type="hidden" class="form-control" value="" id="rsbsa_number_old">

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"><u>RSBSA Control Number:</u></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="rsbsa_number_new" id="rsbsa_number_new">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">First Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="first_name" id="first_name" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"><u>Middle Name:</u></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="middle_name" id="middle_name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Last Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="last_name" id="last_name" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Extension Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="ext_name" id="ext_name" disabled>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Actual Area (ha):</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="actual_area" id="actual_area" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Distibution Area (ha):</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="dist_area" id="dist_area" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Seeds for claim (20kg/bag):</label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <input type="text" class="form-control" name="seed_variety" id="seed_variety" disabled>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-3">
                                <input type="text" class="form-control" name="seed_bags" id="seed_bags" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="edit_btn"><i class="fa fa-edit"></i> Edit Farmer Details</button>
                    </div>
                </div> 
            </div>
        </div>

    </div>
</div>
@endsection
@push('scripts')
<script>

    /**  JPALILEO CODE */

    function load_flds(){
        $("#rsbsa_number_new").val("loading please wait...");
        $("#first_name").val("loading please wait...");
        $("#middle_name").val("loading please wait...");
        $("#last_name").val("loading please wait...");
        $("#ext_name").val("loading please wait...");
        $("#actual_area").val("loading please wait...");
        $("#dist_area").val("loading please wait...");
        $("#seed_variety").val("loading please wait...");
        $("#seed_bags").val("loading please wait...");
    }

    function clear_flds(){
        $("#rsbsa_number_new").val("");
        $("#first_name").val("");
        $("#middle_name").val("");
        $("#last_name").val("");
        $("#ext_name").val("");
        $("#actual_area").val("");
        $("#dist_area").val("");
        $("#seed_variety").val("");
        $("#seed_bags").val("");
    }

    $('#edit_check_modal').on('show.bs.modal', function (e) {
        var current_rsbsa = $(e.relatedTarget).data('rsbsa');
        $("#rsbsa_number_old").val(current_rsbsa);
        load_flds();

        //load selected farmer details
        $.ajax({
            type: 'POST',
            url: "{{ route('rcef.checking.farmer_data') }}",
            data: {
                _token: _token,
                current_rsbsa: current_rsbsa
            },
            success: function (data) {
                $("#rsbsa_number_new").val(data["rsbsa_number"]);
                $("#first_name").val(data["first_name"]);
                $("#middle_name").val(data["middle_name"]);
                $("#last_name").val(data["last_name"]);
                $("#ext_name").val(data["ext_name"]);
                $("#actual_area").val(data["actual_area"]);
                $("#dist_area").val(data["dist_area"]);
                $("#seed_variety").val(data["seed_variety"]);
                $("#seed_bags").val(data["seed_bags"]);
            }
        });
    });

    $("#edit_btn").on("click", function(e){
        var old_rsbsa = $("#rsbsa_number_old").val();
        var new_rsbsa = $("#rsbsa_number_new").val();
        var middle_name = $("#middle_name").val();

        $("#edit_btn").attr("disabled","");
        $("#edit_btn").empty().html("Updating data please wait...");

        $.ajax({
            type: 'POST',
            url: "{{ route('rcef.checking.farmer_edit') }}",
            data: {
                _token: _token,
                old_rsbsa: old_rsbsa,
                new_rsbsa: new_rsbsa,
                middle_name: middle_name
            },
            success: function (data) {
                if(data == "insert_success"){
                    clear_flds();
                    $('#edit_check_modal').modal('toggle');

                    $("#edit_btn").removeAttr("disabled");
                    $("#edit_btn").empty().html('<i class="fa fa-edit"></i> Edit Farmer Details');

                    show_unrelease_table();
                }else if(data == "user_no_permission"){
                    alert("You are not allowed to edit this profile, you are not the author of the data-entry.");
                    $("#edit_btn").removeAttr("disabled");
                    $("#edit_btn").empty().html('<i class="fa fa-edit"></i> Edit Farmer Details');
                }else{
                    alert("There system encountered an error while executing this action, please try again...");
                    $("#edit_btn").removeAttr("disabled");
                    $("#edit_btn").empty().html('<i class="fa fa-edit"></i> Edit Farmer Details');
                }
            }
        });
    });
    /**  JPALILEO CODE */

    function show_unrelease_table(){
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
    }

    $("#show_unreleased").click(function () {
        show_unrelease_table();
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
