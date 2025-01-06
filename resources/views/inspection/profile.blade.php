<?php $inspection_side = "active"; $inspection_profile="active"?>

@extends('layouts.index')

@section('styles')
    <style>
        .progress {
            overflow: hidden;
            height: 20px;
            margin-bottom: 20px;
            background-color: #d5ccf5;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
            box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        }

        #inspectors tbody tr td{
           vertical-align: inherit; 
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
                        <h2>Inspector Profiles</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="inspectors">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Last Name</th>
                                    <th>Ext.</th>
                                    <th>Action.</th>
                                </tr>
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
        $("#inspectors").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('api.inspector.profile') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "firstName"},
                {"data": "middleName"}, 
                {"data":"lastName"},
                {"data":"extName"},
                {"data":"action"}
            ]
        });
    </script>
@endpush