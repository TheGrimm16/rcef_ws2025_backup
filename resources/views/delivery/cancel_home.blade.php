@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="page-title">
    <div class="title_left">
        <h3><span>Cancel Delivery</span> </h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12">

        @include('layouts.message')

        <div class="x_panel">
            <div class="x_title col-md-12">
                <h2 class="col-md-10">
                    <label for="batch_number">Enter Batch Ticket #: </label>
                    <input type="text" name="batch_number" id="batch_number" class="form-control" placeholder="Please enter a batch ticket number to search (Ex: 511-BCH-1583868172)">
                </h2>
                <h2 class="col-md-2" style="margin-top: 28px;">
                    <button class="btn btn-success form-control" id="search_btn"><i class="fa fa-search-plus"></i> SEARCH</button>
                </h2>

                <div class="clearfix"></div>
                <div class="col-md-12">
                    <table id="batch_tbl" class="table table-responsive-sm table-bordered" style="width:100%">
                        <thead> 
                            <th>Batch Ticket #</th>
                            <th>Seed Cooperative</th>
                            <th>Delivery Location</th>
                            <th>Delivery Details</th>
                            <th>Total Bags</th>
                            <th>Action</th>
                        </thead>				
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CANCEL DELIVERY VERIFICATION MODAL -->
<div id="cancel_verification_modal" class="modal fade " role="dialog">
    <div class="modal-dialog" style="width:70%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cancel Delivery <span id="batch_id_modalTitle">{BATCH_TICKET_NUMBER}</span></h4>
            </div>
            <div class="modal-body">
                <form method="post" action="{{route('cancel_delivery.update.flags')}}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <p><strong>Please state your reason, why do you want to cancel this seed delivery? (max of 255 characters.)</strong></p>
                        <textarea name="reason" id="reason" rows="5" class="form-control" required></textarea>
                        <input type="hidden" id="batch_number_update" name="batch_number_update" required>
                    </div>
                    <div class="form-group">
                        <input class="btn btn-danger" type="submit" value="CANCEL DELIVERY">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- CANCEL DELIVERY VERIFICATION MODAL -->

@endsection
@push('scripts')
<script>
    $("#batch_tbl").DataTable();

    $("#search_btn").on("click", function(e){
        var batch_number = $("#batch_number").val();
        $.fn.dataTable.ext.errMode = 'none';

        //check if batch number has inspection data or cancelled
        $.ajax({
            type: 'POST',
            url: "{{ route('deliver_cancel.batch.check') }}",
            data: {
                _token: "{{ csrf_token() }}",
                batch_number: batch_number
            },
            success: function(data){
                if(data == 'ok_for_cancel'){
                    $('#batch_tbl').DataTable().clear();
                    $('#batch_tbl').DataTable({
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "ajax": {
                            "url": "{{ route('deliver_cancel.batch.details') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                "batch_number": batch_number
                            }
                        },
                        "columns":[
                            {"data": "batchTicketNumber"},
                            {"data": "coop_name", orderable: false, searchable: false},
                            {"data": "delivery_address", orderable: false, searchable: false},
                            {"data": "delivery_details", orderable: false, searchable: false},
                            {"data": "total_bag_count", orderable: false, searchable: false},
                            {"data": "action", orderable: false, searchable: false},
                        ]
                    });

                }else if(data == 'already_inspected'){
                    alert('This batch ticket number has already been inspected...');
                
                }else if(data == 'no_batch_return'){
                    alert('{NO_BATCH_DELIVERY_FOUND} | This error is caused by one of the following: (1) You have entered an invalid input and (2) The batch delivery has already been cancelled');
                }
            },
            error: function(){
                alert('SQL Error encountered, please try again...');
            }
        });

        
    });

    $('#cancel_verification_modal').on('show.bs.modal', function (e) {
        var batch_number = $(e.relatedTarget).data('batch');
        $("#batch_id_modalTitle").empty().html("("+batch_number+")");
        $("#batch_number_update").val(batch_number);
    });
</script>
@endpush
