@extends('layouts.index')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <ol class="breadcrumb" style="font-family: Noto; font-size: 1vw;">
                <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
                <li class="breadcrumb-item active">Show Permission</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-white font-weight-bold">
                    <h2 style="font-family: Fjalla_One; font-size: 3vw;">{{ $permission->display_name  }}</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-borderless">
                                <tbody style="font-family: Noto; font-size: 1vw;">
                                    <tr>
                                        <td width="30%">Name:</td>
                                        <td>{{ $permission->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Description:</td>
                                        <td>{{ $permission->description }}</td>
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
