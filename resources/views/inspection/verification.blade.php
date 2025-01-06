<?php $inspection_side = "active"; $inspection_verification="active"?>

@extends('layouts.index')

@section('styles')

@endsection

@section('content')

    <div>
        <div class="clearfix"></div>

        @include('layouts.message')

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Submitted Inspector Profiles</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="inspectorTable">
                            <thead>
                                <tr>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPROVE MODAL -->
        <div class="modal fade" id="verify_approve_inspector_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="myModalLabel">Approve Inspector Profile</h4>
                    </div>
                    <form action="{{ route('rcef.inspector.approve') }}" method="POST">
                        {!! csrf_field() !!}
                        <div class="modal-body">
                            <p>
                                You are about to approve a profile that will be tagged as an "Delivery Inspector", this will grant him/her access
                                to the dashboard and the inspection app (mobile). Do you wish to proceeed?
                            </p>
                            <input type="text" id="id_number_approve" name="id_number_approve" value="">                        
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> Cancel</button>
                            <button type="submit" role="submit" class="btn btn-success"><i class="fa fa-thumbs-up"></i> Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="verify_reject_inspector_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="myModalLabel">Reject Inspector Profile</h4>
                    </div>
                    <form action="{{ route('rcef.inspector.reject') }}" method="POST">
                        {!! csrf_field() !!}
                        <div class="modal-body">
                            <p>
                                You are about to reject an inspector profile, please be reminded that this person can still re-apply for the role of "Delivery Inspector"
                            </p>
                            <input type="text" id="id_number_reject" name="id_number_reject" value="">                        
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times-circle"></i> Cancel</button>
                            <button type="submit" role="submit" class="btn btn-danger"><i class="fa fa-thumbs-down"></i> Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        
    </div>
@endsection

@push('scripts')
    <script>

        $(document).on("click", ".verify-approve-open-modal", function () {
            var id_number = $(this).data('id');
            $(".modal-body #id_number_approve").val( id_number );
        });

        $(document).on("click", ".verify-reject-open-modal", function () {
            var id_number = $(this).data('id');
            $(".modal-body #id_number_reject").val( id_number );
        });

        $("#inspectorTable").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('rcef.inspectors.submitted') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "philriceID"},
                {"data": "full_name"},
                {"data": "position"},
                {"data": "ins_status"},
                {"data": "action"}
            ]
        });
    </script>
@endpush