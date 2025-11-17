@extends('replacement_seeds.layouts.index')

@section('title', 'Request Management')

@section('content')
<div class="container" style="margin-top:20px">

    @php
        $passableRoles = ['rcef-programmer', 'branch-it'];
    @endphp

    <h3>Request Management</h3>

    {{-- Messages --}}
    @include('layouts.message')

    <div class="panel panel-default">
        <div class="panel-heading"><strong>Requests</strong></div>
        <div class="panel-body">

            {{-- Add Buttons --}}
            @if(!empty(array_intersect($passableRoles, $currentUserRoles)))
                @permission('user-create')
                    <a href="{{ route('users.create') }}" class="btn btn-success addBtn">
                        <i class="fa fa-plus"></i> Add New User
                    </a>
                @endpermission
            @endif

            @if(in_array('branch-it', $currentUserRoles))
                <a href="{{ route('users.create.request') }}" class="btn btn-success addBtn">
                    <i class="fa fa-plus"></i> Add New Branch User
                </a>
            @endif

            <table class="table table-striped table-bordered" id="usersTbl">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

    {{-- MODALS --}}
    @include('replacement_seeds.users.modals.reset_password_modal')
    @include('replacement_seeds.users.modals.assign_province_modal')
    @include('replacement_seeds.users.modals.change_info_modal')
    @include('replacement_seeds.users.modals.change_role_modal', ['roles' => $roles])
    @include('replacement_seeds.users.modals.assign_coop_modal')
    @include('replacement_seeds.users.modals.update_accre_modal')

</div>
@endsection

@push('scripts')
<script>
window.Laravel = {!! json_encode([
    'api_token' => $currentUser['api_token'],
    'csrf_token' => csrf_token(),
    'tableRoute' => route('replacement.datatable')
]) !!};

$(document).ready(function () {
    $('#usersTbl').DataTable({
        processing: true,
        serverSide: true,
        ajax: window.Laravel.tableRoute,
        columns: [
            { data: 'name' },
            { data: 'username' },
            { data: 'email' },
            { data: 'province' },
            { data: 'municipality' },
            { data: 'roles', orderable: false, searchable: false },
            { data: 'status', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });
});

function generateRandomPass(e) {
    e.preventDefault();
    const chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let result = '';
    for (let i = 0; i < 6; i++) result += chars[Math.floor(Math.random() * chars.length)];
    $("#reset_pass").val(result.toUpperCase());
}

// ------------------------------
// Modal Events
// ------------------------------
$('#reset_password_modal').on('show.bs.modal', function(e){
    $("#userID_reset").val($(e.relatedTarget).data('id'));
});

// (Add the rest of your modal JS here)
</script>
@endpush
