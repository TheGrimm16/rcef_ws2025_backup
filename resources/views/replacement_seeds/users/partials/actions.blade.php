<div class="btn-group" role="group">

    {{-- Edit User Info --}}
    <button type="button" class="btn btn-xs btn-primary"
        data-toggle="modal"
        data-target="#changeInfo"
        data-id="{{ $user->userId }}"
        data-first_name="{{ $user->firstName }}"
        data-mid_name="{{ $user->middleName }}"
        data-last_name="{{ $user->lastName }}"
        data-ext_name="{{ $user->extName }}">
        Edit
    </button>

    {{-- Change Role --}}
    <button type="button" class="btn btn-xs btn-warning"
        data-toggle="modal"
        data-target="#changeRole"
        data-id="{{ $user->userId }}">
        Role
    </button>

    {{-- Reset Password --}}
    <button type="button" class="btn btn-xs btn-danger"
        data-toggle="modal"
        data-target="#reset_password_modal"
        data-id="{{ $user->userId }}">
        Reset Password
    </button>

    {{-- Assign Province --}}
    <button type="button" class="btn btn-xs btn-info"
        data-toggle="modal"
        data-target="#assignProvince"
        data-id="{{ $user->userId }}">
        Province
    </button>

    {{-- Assign Coop --}}
    <button type="button" class="btn btn-xs btn-success"
        data-toggle="modal"
        data-target="#assignModal"
        data-id="{{ $user->userId }}"
        data-name="{{ isset($user->fullName) ? $user->fullName : $user->name }}"
        data-coop="0">
        Coop
    </button>

    {{-- Update Accreditation --}}
    <button type="button" class="btn btn-xs btn-default"
        data-toggle="modal"
        data-target="#update_accre_modal"
        data-id="{{ $user->userId }}"
        data-name="{{ isset($user->fullName) ? $user->fullName : $user->name }}">
        Accre
    </button>

</div>
