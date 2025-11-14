<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Management</title>

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">

    <style>
        .addBtn { margin-bottom: 10px; }
        .label { margin-right: 3px; }
    </style>
</head>

<body>

<div class="container" style="margin-top:20px">

    @php
        $passableRoles = ['rcef-programmer', 'branch-it'];
    @endphp

    <h3>User Management</h3>

    {{-- Messages --}}
    @include('layouts.message')

    <div class="panel panel-default">
        <div class="panel-heading"><strong>Users</strong></div>
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


{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- Bootstrap JS --}}
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>


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

$('#assignProvince').on('show.bs.modal', function(e){
    const userID = $(e.relatedTarget).data('id');
    $("#prv_userID").val(userID);
    $("#changeProvince").html("<option>Loading...</option>");
    $.post("{{ route('user.province.list') }}", {_token: "{{ csrf_token() }}", userID}, function(data){
        $("#changeProvince").html("<option value='0'>Select Province</option>" + data);
        $.post("{{ route('user.municipality.list') }}", {_token: "{{ csrf_token() }}", userID, province: $("#changeProvince").val()}, function(mdata){
            $("#changeMunicipality").html(mdata);
        });
    });
});

$("#changeProvince").on("change", function(){
    $.post("{{ route('user.municipality.list') }}", {
        _token: "{{ csrf_token() }}",
        province: $(this).val(),
        userID: $("#prv_userID").val()
    }, function(data){ $("#changeMunicipality").html(data); });
});

$('#changeInfo').on('show.bs.modal', function(e){
    const btn = $(e.relatedTarget);
    $("#info_userID").val(btn.data('id'));
    $("#firstName").val(btn.data('first_name'));
    $("#midName").val(btn.data('mid_name'));
    $("#lastName").val(btn.data('last_name'));
    $("#extName").val(btn.data('ext_name'));
});

$('#changeRole').on('show.bs.modal', function(e){
    $("#role_userID").val($(e.relatedTarget).data('id'));
    $("#currentRole").text($(e.relatedTarget).closest('tr').find('.label-primary').text());
});

$('#assignModal, #update_accre_modal').on('show.bs.modal', function(e){
    const modal = $(this), btn = $(e.relatedTarget);
    modal.find('input[name*="userID"]').val(btn.data('id'));
    modal.find('.modal-title').html(btn.data('name'));
    const coop = btn.data('coop') || 0;
    modal.find('select').html("<option>Loading...</option>");
    $.post("{{ route('coop.list') }}", {_token: "{{ csrf_token() }}", coop}, function(data){
        modal.find('select').html("<option value='0'>Please select a seed cooperative</option>" + data);
    });
});
</script>

</body>
</html>
