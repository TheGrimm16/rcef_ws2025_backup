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

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="alert alert-success alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <strong><i class="fa fa-info-circle"></i> Reminder:</strong> This list contains farmer beneficiaries that have been given seeds for last season (DRY SEASON 2019)
        </div>


        <div class="x_panel" style="display: none;">
            <div class="x_title">
                <h2>
                    Select a file to upload
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <form enctype="multipart/form-data" method="post" action="{{route('farmers.list.upload')}}">
                        {{ csrf_field() }}
                        <div class="col-md-9">
                            <input class="form-control" name="file" type="file" id="fileInput" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                        <div class="col-md-3">
                            <input class="btn btn-success form-control" type="submit" value="UPLOAD FARMER LIST">
                        </div>
                    </form>
                </div>
            </div>
        </div><br>

        <div class="x_panel" id="sg_section">
            <div class="x_title">
                <h2 id="sg_table_title">
                    Seed Beneficiaries (DS 2019 LIST)
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="sg_table">
                    <thead>
                        <th>RSBSA #</th>
                        <th>Full Name</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Dropoff Point</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div><br>


        <!-- BLACKLIST MODAL -->
        <div id="list_details_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg" style="max-height: 560px;overflow: auto;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            EDIT PROFILE
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <ul class="nav nav-pills nav-stacked">
                                    <li role="presentation" class="active" id="ds2019_tab"><a href="#">DS 2019</a></li>
                                    <li role="presentation" id="ws2020_tab"><a href="#">WS 2020</a></li>
                                </ul><br>
                            </div>
                            <div class="col-md-9" style="border-left: 1px solid #73879C">
                                <!-- DS 2019 FARMER PORFILE -->
                                <div id="ds2019_farmerDetails" style="display: block">
                                    <h4><strong><u>FARMER DETAILS</u></strong> <button class="btn btn-warning btn-xs" id="ds2019_fields_control_btn"><i class="fa fa-unlock" id="ds2019_fa_icon"></i></button> </h4>
                                    <div class="form-horizontal form-label-left">
                                        <div class="form-group" id="no_photo_wrapper" style="display:none">
                                            <div class="alert alert-warning alert-dismissible fade in" role="alert" style="margin-bottom: 0;">
                                                <strong><i class="fa fa-times-circle"></i> Error:</strong> No matching documentation data found. Please check the RSBSA control #
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">RSBSA NUMBER:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ds2019_rsbsa" id="ds2019_rsbsa" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">FULL NAME:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ds2019_full_name" id="ds2019_full_name" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">PROVINCE:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ds2019_province" id="ds2019_province" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">MUNICIPALITY:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ds2019_municipality" id="ds2019_municipality" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">DROPOFF POINT:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ds2019_dop" id="ds2019_dop" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">BAGS CLAIMED:</label>
                                            <div class="col-md-6 col-sm-6 col-xs-6">
                                                <input type="text" class="form-control" name="ds2019_seed_variety" id="ds2019_seed_variety" disabled>
                                                <input type="hidden" id="ds2019_fld_check" value="1">
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-3">
                                                <input type="text" class="form-control" name="ds2019_bags_claimed" id="ds2019_bags_claimed" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <button class="btn btn-warning" id="ds2019_edit_btn" disabled><i class="fa fa-edit"></i> SAVE CHANGES</button>
                                            </div>
                                        </div>

                                        <input type="hidden" value="" id="ds2019_profileID">
                                    </div>
                                    <hr>
                                    <!-- PHOTO CONTAINER -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div id="photo_container"></div>
                                        </div>
                                        <div class="col-md-6" style="margin-left: -25px">
                                            <!--jumbotron-->
                                            <div class="jumbotron" style="padding:15px;">
                                                <div class="container">
                                                    <!--<h4>Bootstrap Tutorial</h4>-->      
                                                    <p style="font-size: 14px">
                                                        <strong>Reasons why the farmer photo was not displayed:</strong><br>
                                                        <span style="display: block;padding-left: 20px;">1). The actual photo is not yet uploaded to the server</span>
                                                        <span style="display: block;padding-left: 20px;">2). `Refresh the page` : necessary data amy not be fetced due to unstable internet connection. </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- PHOTO CONTAINER -->
                                </div>
                                <!-- DS 2019 FARMER PORFILE -->

                                <!-- WS 2020 FARMER PROFILE -->
                                <div id="ws2020_farmerDetails" style="display:none;">
                                    <div class="form-horizontal form-label-left">
                                        <h4><strong><u>FARMER DETAILS</u></h4>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">RSBSA NUMBER:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ws2020_rsbsa" id="ws2020_rsbsa" style="font-weight: 400;" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">FULL NAME:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ws2020_full_name" id="ws2020_full_name" style="font-weight: 400;" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">PROVINCE:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ws2020_province" id="ws2020_province" style="font-weight: 400;" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">MUNICIPALITY:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ws2020_municipality" id="ws2020_municipality" style="font-weight: 400;" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">DROPOFF POINT:</label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <input type="text" class="form-control" name="ws2020_dop" id="ws2020_dop" style="font-weight: 400;" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">BAGS CLAIMED:</label>
                                            <div class="col-md-6 col-sm-6 col-xs-6">
                                                <input type="text" class="form-control" name="ws2020_seed_variety" id="ws2020_seed_variety" style="font-weight: 400;" disabled>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-3">
                                                <input type="text" class="form-control" name="ws2020_bags_claimed" id="ws2020_bags_claimed" style="font-weight: 400;" disabled>
                                            </div>
                                        </div>
                                        <hr>

                                    </div>
                                </div>
                                <!-- WS 2020 FARMER PROFILE -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- BLACKLIST MODAL -->

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

        $("#ds2019_tab").on("click", function(e){
            $("#ds2019_tab").addClass("active");
            $("#ws2020_tab").removeClass("active");

            $("#ds2019_farmerDetails").css("display", "block");
            $("#ws2020_farmerDetails").css("display", "none");
        });

        $("#ws2020_tab").on("click", function(e){
            $("#ws2020_tab").addClass("active");
            $("#ds2019_tab").removeClass("active");

            $("#ws2020_farmerDetails").css("display", "block");
            $("#ds2019_farmerDetails").css("display", "none");

            //load ws2020 data...
            var rsbsa_number = $("#ds2019_rsbsa").val();
            var rsbsa_split = rsbsa_number.split("-");
            var prv_code = $GLOBALS['season_prefix']."prv_"+rsbsa_split[0]+rsbsa_split[1];

            $("#ws2020_rsbsa").val('Fetching data please wait...');
            $("#ws2020_full_name").val('Fetching data please wait...');
            $("#ws2020_province").val('Fetching data please wait...');
            $("#ws2020_municipality").val('Fetching data please wait...');
            $("#ws2020_dop").val('Fetching data please wait...');
            $("#ws2020_bags_claimed").val('Fetching data please wait...');
            $("#ws2020_seed_variety").val('Fetching data please wait...');

            $.ajax({
                type: 'POST',
                url: "{{ route('farmer.profile.ws2020') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    rsbsa_number: rsbsa_number,
                    prv_code: prv_code
                },
                success: function(data){
                    $("#ws2020_rsbsa").val(data["rsbsa_number"]);
                    $("#ws2020_full_name").val(data["full_name"]);
                    $("#ws2020_province").val(data["province"]);
                    $("#ws2020_municipality").val(data["municipality"]);
                    $("#ws2020_dop").val(data["dop"]);
                    $("#ws2020_bags_claimed").val(data["bags_claimed"]);
                    $("#ws2020_seed_variety").val(data["seed_variety"]);
                }
            });
        });

        $("#sg_table").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('farmers.list.table') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "rsbsa_number"},
                {"data": "full_name"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "dop"},
                {"data": "action", searchable: false}
            ]
        });

        $("#ds2019_fields_control_btn").on("click", function(e){

            if($("#ds2019_fld_check").val() == "1"){
                $("#ds2019_rsbsa").removeAttr("disabled");
                $("#ds2019_full_name").removeAttr("disabled");
                $("#ds2019_edit_btn").removeAttr("disabled");
                $("#ds2019_fld_check").val("2");

            }else if($("#ds2019_fld_check").val() == "2"){
                $("#ds2019_rsbsa").attr("disabled","");
                $("#ds2019_full_name").attr("disabled","");
                $("#ds2019_edit_btn").attr("disabled","");
                $("#ds2019_fld_check").val("1");
            }

            $("#ds2019_fa_icon").toggleClass('fa-unlock fa-lock');
        });


        function loadPhoto(rsbsa_number, prv_code, public_url, alt_img){
            var photo_str = '';

            $.ajax({
                type: 'POST',
                url: "https://rcef-seed.philrice.gov.ph/rcef/farmer_profile/get_photo",
                data: {
                    rsbsa_number: rsbsa_number,
                    prv_code: prv_code
                },
                success: function(data){
                    if(data == "sql_error"){
                        $("#no_photo_wrapper").css("display", "block");
                    }else{
                        $("#no_photo_wrapper").css("display", "none");
                        photo_str = photo_str + '<center><img style="width:250px;" src="'+public_url+'/'+data+'.jpg" class="rounded mx-auto d-block" alt="Image not found" onerror="this.src='+alt_img+'"></center><br>';
                        photo_str = photo_str + '<center><button style="margin-top: -10px;" class="btn btn-primary">SET / REPLACE FARMER IMAGE</button></center>';
                        $("#photo_container").append(photo_str);
                    }
                },
                error: function(){
                    alert("unable to load photo, please chaeck internet connection...");
                }
            });
        }

        $('#list_details_modal').on('show.bs.modal', function (e) {
            var profile_id = $(e.relatedTarget).data('id');
            $("#ds2019_profileID").val(profile_id);

            $("#ds2019_tab").addClass("active");
            $("#ws2020_tab").removeClass("active");
            $("#ds2019_farmerDetails").css("display", "block");
            $("#ws2020_farmerDetails").css("display", "none");

            $("#ds2019_rsbsa").attr("disabled","");
            $("#ds2019_full_name").attr("disabled","");
            $("#ds2019_edit_btn").attr("disabled","");
            $("#ds2019_fa_icon").removeClass("fa-lock");
            $("#ds2019_fa_icon").addClass("fa-unlock");

            $("#ds2019_rsbsa").val('Fetching data please wait...');
            $("#ds2019_full_name").val('Fetching data please wait...');
            $("#ds2019_province").val('Fetching data please wait...');
            $("#ds2019_municipality").val('Fetching data please wait...');
            $("#ds2019_dop").val('Fetching data please wait...');
            $("#ds2019_bags_claimed").val('Fetching data please wait...');
            $("#ds2019_seed_variety").val('Fetching data please wait...');
    
            $.ajax({
                type: 'POST',
                url: "{{ route('farmer.profile.ds2019') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    profile_id: profile_id
                },
                success: function(data){
                    $("#ds2019_rsbsa").val(data["rsbsa_number"]);
                    $("#ds2019_full_name").val(data["full_name"]);
                    $("#ds2019_province").val(data["province"]);
                    $("#ds2019_municipality").val(data["municipality"]);
                    $("#ds2019_dop").val(data["dop"]);
                    $("#ds2019_bags_claimed").val(data["bags_claimed"]);
                    $("#ds2019_seed_variety").val(data["seed_variety"]);
                }

            }).done(function(){
                //get list of photo name from the server;
                var rsbsa_number = $("#ds2019_rsbsa").val();
                var rsbsa_split = rsbsa_number.split("-");
                var prv_code = $GLOBALS['season_prefix']."prv_"+rsbsa_split[0]+rsbsa_split[1];
                $("#photo_container").empty();
                $("#no_photo_wrapper").css("display", "none");

                var public_url = "{{asset('public/farmer_images')}}";
                var alt_img = "'" + public_url + "/ALT/alt_img.png" + "'"
                loadPhoto(rsbsa_number, prv_code, public_url, alt_img)
            });
        });

        $("#ds2019_edit_btn").on("click", function(e){
            var profile_id = $("#ds2019_profileID").val();
            var rsbsa_number = $("#ds2019_rsbsa").val();
            var full_name = $("#ds2019_full_name").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('farmer_profile.update') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    profile_id: profile_id,
                    rsbsa_number: rsbsa_number,
                    full_name: full_name
                },
                success: function(data){
                    if(data == "update_profile_success"){
                        $("#ds2019_rsbsa").val('Fetching data please wait...');
                        $("#ds2019_full_name").val('Fetching data please wait...');
                        $("#ds2019_province").val('Fetching data please wait...');
                        $("#ds2019_municipality").val('Fetching data please wait...');
                        $("#ds2019_dop").val('Fetching data please wait...');
                        $("#ds2019_bags_claimed").val('Fetching data please wait...');
                        $("#ds2019_seed_variety").val('Fetching data please wait...');

                        $.ajax({
                            type: 'POST',
                            url: "{{ route('farmer.profile.ds2019') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                profile_id: profile_id
                            },
                            success: function(data){
                                $("#ds2019_rsbsa").val(data["rsbsa_number"]);
                                $("#ds2019_full_name").val(data["full_name"]);
                                $("#ds2019_province").val(data["province"]);
                                $("#ds2019_municipality").val(data["municipality"]);
                                $("#ds2019_dop").val(data["dop"]);
                                $("#ds2019_bags_claimed").val(data["bags_claimed"]);
                                $("#ds2019_seed_variety").val(data["seed_variety"]);

                                $("#ds2019_rsbsa").attr("disabled","");
                                $("#ds2019_full_name").attr("disabled","");
                                $("#ds2019_edit_btn").attr("disabled","");
                                $("#ds2019_fa_icon").removeClass("fa-lock");
                                $("#ds2019_fa_icon").addClass("fa-unlock");
                            }
                        }).done(function(e){
                            var rsbsa_split = rsbsa_number.split("-");
                            var prv_code = $GLOBALS['season_prefix']."prv_"+rsbsa_split[0]+rsbsa_split[1];
                            $("#photo_container").empty();
                            $("#no_photo_wrapper").css("display", "none");

                            var public_url = "{{asset('public/farmer_images')}}";
                            var alt_img = "'" + public_url + "/ALT/alt_img.png" + "'"
                            loadPhoto(rsbsa_number, prv_code, public_url, alt_img)
                        });

                    }else{
                        alert("The system encountered an error while updating the farmer profile, pleaase try again...")
                    }
                }
            });
        });
    </script>
@endpush
