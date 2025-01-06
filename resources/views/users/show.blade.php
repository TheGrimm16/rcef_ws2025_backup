@extends('layouts.index')

@section('content')
    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>User Management</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ $name }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td width="30%">Username:</td>
                                    <td>{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td>Email:</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td>Secondary Email:</td>
                                    <td>{{ $user->secondaryEmail }}</td>
                                </tr>
                                <tr>
                                    <td>Sex:</td>
                                    <td>
                                        @if ($user->sex == 'M')
                                            {{ 'Male' }}
                                        @elseif ($user->sex == 'F')
                                            {{ 'Female' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Region:</td>
                                    <td>{{ (isset($region->regDesc)) ? $region->regDesc : "" }}</td>
                                </tr>
                                <tr>
                                    <td>Assigned Province:</td>
                                    <td>{{ (isset($province->provDesc)) ? $province->provDesc : "" }}</td>
                                </tr>
                                <tr>
                                    <td>Assigned Municipality:</td>
                                    <td>{{ (isset($municipality->citymunDesc)) ? $municipality->citymunDesc : "" }}</td>
                                </tr>
                                <tr>
                                    <td>Agency:</td>
                                    <td>
                                        @if (!$agency == NULL)
                                            {{ $agency->name }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Station:</td>
                                    <td>
                                        @if (!$station == NULL)
                                            {{ $station->stationName }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Position:</td>
                                    <td>{{ $user->position }}</td>
                                </tr>
                                <tr>
                                    <td>Designation:</td>
                                    <td>{{ $user->designation }}</td>
                                </tr>
                                <tr>
                                    <td>Date Registered:</td>
                                    <td>{{ date('F d, Y', strtotime($user->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <td>Status:</td>
                                    <td>
                                        @if ($user->isDeleted == 0)
                                            <span class="label label-success">Active</span>
                                        @else
                                            <span class="label label-danger">Disabled</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Roles:</td>
                                    <td>
                                        @foreach ($roles as $role)
                                            <span class="label label-primary">{{ $role->display_name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
