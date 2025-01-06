<?php $registry_side = "active"; $registry_index="active"?>

@extends('layouts.index')

@section('styles')
    <style>
        .hr{
            margin-top: 0;
        }
        .hr-text {
            line-height: 1em;
            position: relative;
            outline: 0;
            border: 0;
            color: black;
            text-align: center;
            height: 1.5em;
            opacity: 0.5;
        }
        .hr-text:before {
            content: "";
            background: linear-gradient(to right, transparent, #818078, transparent);
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
        }
        .hr-text:after {
            content: attr(data-content);
            position: relative;
            display: inline-block;
            color: black;
            padding: 0 0.5em;
            line-height: 1.5em;
            color: #818078;
            background-color: #fcfcfa;
        }
    </style>
@endsection

@section('content')

    <div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Registered Farmers</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="registered_farmers">
                            <thead>
                                <tr>
                                    <th>IDq</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Last Name</th>
                                    <th>Suffix</th>
                                    <th>Address</th>
                                    <th style="width: 20%;">Details</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Large modal -->
        <div class="modal fade bs-example-modal" id="farm_details" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel"><b>Name: RSBSA ID</b></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <cener><hr class="hr-text" data-content="FARMER PROFILE" style="margin-top: 0"></center>
                                <table class="table table-bordered table-striped">
                                    <input type="hidden" value="" id="profileID">
                                    <tbody>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Full Name: </b></td>
                                            <td id="full_name_con"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Sex: </b></td>
                                            <td id="sex"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Contact Number: </b></td>
                                            <td id="contact"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Date of Birth: </b></td>
                                            <td id="birth_date"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <cener><hr class="hr-text" data-content="FARM DETAILS"></center>
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Farm Area: </b></td>
                                            <td id="farm_area"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Rice Area: </b></td>
                                            <td id="rice_area"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Tenurial Status: </b></td>
                                            <td id="tenurial_status"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Tenurial Type: </b></td>
                                            <td id="tenurial_type"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <cener><hr class="hr-text" data-content="AFFILIATIONS"></center>
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Affiliation Type: </b></td>
                                            <td id="affiliation_type"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Name: </b></td>
                                            <td id="affiliation_name"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:50%;text-align:right;"><b>Accreditation</b></td>
                                            <td id="farm_accreditation"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
@endsection

@section('scripts')
    <script>
        $("#registered_farmers").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('api.rcef.registered.farmers') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "farmerID"},
                {"data": "farmer_first_name"},
                {"data": "farmer_middle_name"},
                {"data": "farmer_last_name"},
                {"data": "farmer_suffix_name"},
                {"data": "address"},
                {"data":"action"}
            ]
        });

        $('#farm_details').on('show.bs.modal', function (e) {
            var profileID = $(e.relatedTarget).attr('data-id');
            
            $.ajax({
                type: 'POST',
                url: "{{ route('api.farmer.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    profileID: profileID
                },
                success: function(data){
                    $("#full_name_con").empty().append(data["full_name"]);
                    $("#sex").empty().append(data['sex']);
                    $("#contact").empty().append(data['contact_number']);
                    $("#birth_date").empty().append(data["birth_date"]);

                    $("#farm_area").empty().append(data["farm_area"]);
                    $("#rice_area").empty().append(data["rice_area"]);
                    $("#tenurial_status").empty().append(data["tenurial_status"]);
                    $("#tenurial_type").empty().append(data["tenurial_type"]);

                    $("#affiliation_type").empty().append(data["affiliation_type"]);
                    $("#affiliation_name").empty().append(data["affiliation_name"]);
                    $("#farm_accreditation").empty().append(data["farm_accreditation"]);
                }
            });
        });
    </script>
@endsection