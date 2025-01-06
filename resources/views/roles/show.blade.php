@extends('layouts.index')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <ol class="breadcrumb" style="font-family: Noto; font-size: 1vw;">
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active">View Role</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white font-weight-bold">
                    <h2 style="font-family: Fjalla_One; font-size: 3vw;">{{ $role->display_name  }}</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-borderless">
                                <tbody style="font-family: Noto; font-size: 1vw;">
                                    <tr>
                                        <td width="30%">Role Description:</td>
                                        <td>{{ $role->description }}</td>
                                    </tr>
                                    <tr>
                                        <td>Permissions:</td>
                                        <td>
                                            @if(!empty($rolePermissions))
                                                @foreach($rolePermissions as $v)
                                                    <span class="badge badge-primary">{{ $v->display_name }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
