@extends('seed_replacement.layouts.index')

@section('title', 'Request Management')

@section('content')
<div class="container" style="margin-top:20px">

    @php
        $passableRoles = ['rcef-programmer', 'branch-it'];
    @endphp

    <h3>Request Management</h3>

    {{-- New Request Button --}}
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createRequestModal">
        <i class="fa fa-plus"></i> New Request
    </button>

    {{-- Messages --}}
    @include('layouts.message')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Requests</strong>
        </div>
        <div class="card-body">
            {{-- Requests Table --}}
            <table class="table table-bordered" id="requests-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Geo Code</th>
                        <th>Purpose ID</th>
                        <th>Attachment</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Include Modal --}}
    @include('seed_replacement.requests.modals.create_modal')

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ---------------------------------------------------------
    // DATATABLE INITIALIZATION
    // ---------------------------------------------------------
    var table = $('#requests-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('replacement.request.datatable') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_id', name: 'user_id' },
            { data: 'geo_code', name: 'geo_code' },
            { data: 'purpose_id', name: 'purpose_id' },
            { data: 'attachment_dir', name: 'attachment_dir' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // ---------------------------------------------------------
    // AJAX: CREATE NEW REQUEST (MODAL FORM)
    // ---------------------------------------------------------
    $('#createRequestForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: "{{ route('replacement.request.create') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                $('#modal-errors').hide().html("");
                $('#createRequestForm')[0].reset();
                $('#createRequestModal').modal('hide');
                table.ajax.reload(null, false);
                alert('Request created successfully!');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorHtml = "<ul>";
                    $.each(errors, function(key, value) {
                        errorHtml += "<li>" + value[0] + "</li>";
                    });
                    errorHtml += "</ul>";
                    $('#modal-errors').show().html(errorHtml);
                } else {
                    alert('An unexpected error occurred.');
                }
            }
        });
    });

    // ---------------------------------------------------------
    // AJAX: APPROVE REQUEST
    // ---------------------------------------------------------
    $(document).on('click', '.approve-btn', function() {
        var btn = $(this);
        var approveUrl = btn.data('url');

        if (!confirm('Are you sure you want to approve this request?')) return;

        $.ajax({
            url: approveUrl,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success){
                    // Replace the entire action cell with "Approved" display
                    btn.closest('td').html('<button class="btn btn-sm btn-success" disabled>Approved</button>');
                } else {
                    alert(response.message || 'Failed to approve request.');
                }
            },
            error: function(xhr) {
                if(xhr.status === 403) alert('You do not have permission to approve this request.');
                else alert('An error occurred. Please try again.');
            }
        });
    });

    // ---------------------------------------------------------
    // AJAX: DECLINE REQUEST
    // ---------------------------------------------------------

    $(document).on('click', '.decline-btn', function() {
        var btn = $(this);
        var declineUrl = btn.data('url');

        if (!confirm('Are you sure you want to decline this request?')) return;

        $.ajax({
            url: declineUrl,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success){
                    // Replace the entire action cell with "Declined" display
                    btn.closest('td').html('<button class="btn btn-sm btn-danger" disabled>Declined</button>');
                } else {
                    alert(response.message || 'Failed to decline request.');
                }
            },
            error: function(xhr) {
                if(xhr.status === 403) alert('You do not have permission to decline this request.');
                else alert('An error occurred. Please try again.');
            }
        });
    });


});
</script>
@endpush
