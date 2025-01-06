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
                        <input type="hidden" class="form-control" value="" id="data_location">

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* RSBSA Control Number:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="rsbsa_number_new" id="rsbsa_number_new">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* First Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="first_name" id="first_name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* Middle Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="middle_name" id="middle_name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* Last Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="last_name" id="last_name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* Extension Name:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="ext_name" id="ext_name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* Birth Date:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="birth_date" id="birth_date">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* Sex:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="farmer_sex" id="farmer_sex" class="form-control">
                                    <option value="Male">Male</option>
                                    <option value="Femal">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">* Contact #:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="contact_no" id="contact_no">
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
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script>

    /**  JPALILEO CODE */
    $('#birth_date').mask('9999-99-99',{placeholder:"YYYY/MM/DD"});
    $('#contact_no').mask('9999-999-9999', {placeholder:"XXXX-XXX-XXXX"});

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
        $("#contact_no").val("loading please wait...")
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
        $("#contact_no").val("");
    }

    $('#edit_check_modal').on('show.bs.modal', function (e) {
        var current_rsbsa = $(e.relatedTarget).data('rsbsa');
        var location = $(e.relatedTarget).data('location');

        $("#rsbsa_number_old").val(current_rsbsa);
        $("#data_location").val(location);
        load_flds();

        //load selected farmer details
        $.ajax({
            type: 'POST',
            url: "{{ route('rcef.checking.farmer_data') }}",
            data: {
                _token: "{{ csrf_token() }}",
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
                $("#birth_date").val(data["birth_date"]);
                $("#farmer_sex").val(data["sex"]).change();
                $("#contact_no").val(data["contact_no"]);
            }
        });
    });

    function save_old(old_rsbsa,new_rsbsa, middle_name, first_name, last_name, ext_name, birth_date, sex, contact_no){
        $("#edit_btn").attr("disabled","");
        $("#edit_btn").empty().html("Updating data please wait...");

        var data_location = $("#data_location").val();

        $.ajax({
            type: 'POST',
            url: "{{ route('rcef.checking.old_farmer_edit') }}",
            data: {
                _token: "{{ csrf_token() }}",
                old_rsbsa: old_rsbsa,
                new_rsbsa: new_rsbsa,
                first_name: first_name,
                last_name: last_name,
                ext_name: ext_name,
                middle_name: middle_name,
                birth_date: birth_date,
                sex: sex,
                contact_no: contact_no
            },
            success: function (data) {
                $('#edit_check_modal').modal('toggle');
                $("#edit_btn").removeAttr("disabled");
                $("#edit_btn").empty().html('<i class="fa fa-edit"></i> Edit Farmer Details');
                
                if(data_location == "search_tbl"){
					alert("Successfully updated!");
                    show_search_table();
                }else{  
					alert("Successfully updated!");
                    show_unrelease_table();
                }
            }
        });
    }

    function save_edit(old_rsbsa,new_rsbsa,middle_name, data_category, first_name, last_name, ext_name, birth_date, sex, contact_no){
        $("#edit_btn").attr("disabled","");
        $("#edit_btn").empty().html("Updating data please wait...");

        var data_location = $("#data_location").val();

        $.ajax({
            type: 'POST',
            url: "{{ route('rcef.checking.farmer_edit') }}",
            data: {
                _token: "{{ csrf_token() }}",
                old_rsbsa: old_rsbsa,
                new_rsbsa: new_rsbsa,
                middle_name: middle_name,
                data_category: data_category,
                first_name: first_name,
                last_name: last_name,
                ext_name: ext_name,
                birth_date: birth_date,
                sex: sex,
                contact_no: contact_no
            },
            success: function (data) {
                if(data == "insert_success"){
                    clear_flds();
                    $('#edit_check_modal').modal('toggle');

                    $("#edit_btn").removeAttr("disabled");
                    $("#edit_btn").empty().html('<i class="fa fa-edit"></i> Edit Farmer Details');

                    if(data_location == "search_tbl"){
						alert("Successfully updated!");
                        show_search_table();
                    }else{
						alert("Successfully updated!");
                        show_unrelease_table();
                    }
                    
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
    }


    $("#edit_btn").on("click", function(e){
        var old_rsbsa = $("#rsbsa_number_old").val();
        var new_rsbsa = $("#rsbsa_number_new").val();
        
        var first_name = $("#first_name").val();
        var middle_name = $("#middle_name").val();
        var last_name = $("#last_name").val();
        var ext_name = $("#ext_name").val();
        var birth_date = $("#birth_date").val();
        var sex = $("#farmer_sex").val();
        var contact_no = $("#contact_no").val();
        
        $.ajax({
            type: 'POST',
            url: "{{ route('rcef.checking.check_rsbsa_status') }}",
            data: {
                _token: "{{ csrf_token() }}",
                new_rsbsa: new_rsbsa,
                old_rsbsa: old_rsbsa
            },
            success: function (data) {
                
                if(data["return_msg"] == "new_data_rsbsa_exists"){
                    alert("This RSBSA Control Number has already been used.");

                }else if(data["return_msg"] == "new_data_same_rsbsa" || data["return_msg"] == "new_data_new_rsbsa"){
                    save_edit(old_rsbsa,new_rsbsa,middle_name, "new_or_same_rsbsa", first_name, last_name, ext_name, birth_date, sex, contact_no)
                
                }else if(data["return_msg"] == "no_pending_data_old_data"){
                    if (confirm("Is this: "+data["farmer_name"]+"?")) {
                        save_old(old_rsbsa,new_rsbsa, middle_name, first_name, last_name, ext_name, birth_date, sex, contact_no);

                    } else {
                        alert("Action cancelled.");
                    }
                }

            }
        });
        
    });
    /**  JPALILEO CODE */

    function show_search_table(){
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
    }

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
       show_search_table();
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
