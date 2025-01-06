<?php $insepction_side = "active"; $inspection_index="active"?>

@extends('layouts.index')

@section('content')

    <div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">

                @include('layouts.message')

                <div class="x_panel">
                    <div class="x_title">
                        <h2>Confirmed Delivery Schedule (Seed Growers & Farm Cooperatives)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="table1">
                            <thead>
                                <tr>
                                    <th>Ticket Number</th>
                                    <th style="width:30%">SG / COOP Details</th>
                                    <th>Delivery Date</th>
                                    <th>Recipient</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="verify_allocation_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="myModalLabel">Deisgnate Inspector</h4>
                    </div>
                    <form action="{{ route('rcef.inspection.designation', '') }}" methtod="GET" id="inspectionForm">    
                    <div class="modal-body">
                        <p>You are about to designate a seed inspector for this specific delivery, please note that by doing so, 
                            the delivery will be subject to evaluation and acceptance by the institute. Do you wish to proceed?
                            <input type="hidden" id="ticketNumber" value="">
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> Close</button>
                        <button type="submit" role="submit" class="btn btn-success"><i class="fa fa-share"></i> Proceed</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        
        
    </div>
@endsection

@push('scripts')
    <script>

        $(document).on("click", ".allocate-open-modal", function () {
            var getUrl = window.location;
            var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];

            var ticketNumber = $(this).data('id');
            $(".modal-body #ticketNumber").val( ticketNumber );
            $("#inspectionForm").attr("action", baseUrl+"/inspection/designation/"+ticketNumber);
            // As pointed out in comments, 
            // it is unnecessary to have to manually call the modal.
            // $('#addBookDialog').modal('show');
        });

        $("#table1").DataTable({
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "bFilter": false,
            "ajax": {
                "url": "{{ route('api.confirmed.delivery') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns":[
                {"data": "ticketNumber"},
                {"data": "seed_grower_fld"},
                {"data":"deliveryDate"},
                {"data":"deliverTo"},
                {"data":"action"},
            ]
        });
    </script>
@endpush