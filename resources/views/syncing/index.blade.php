@extends('layouts.index')

@section('content')
    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Syncing</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success" role="alert">
                        {{ $message }}
                    </div>
                @endif
                @if ($message = Session::get('warning'))
                    <div class="alert alert-warning" role="alert">
                        {{ $message }}
                    </div>
                @endif
                @if ($message = Session::get('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ $message }}
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Releasing Database</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <a href="{{route('syncing.send_pending_release')}}" type="button" name="button" class="btn btn-primary"><i class="fa fa-cloud-upload"></i> Send Pending Release Data to Server</a>
                        <a href="{{route('syncing.send_released')}}" type="button" name="button" class="btn btn-primary"><i class="fa fa-cloud-upload"></i> Send Released Data to Server</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Distribution List Database</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <a href={{route('syncing.download_distribution_list')}} type="button" name="button" class="btn btn-success"><i class="fa fa-cloud-download"></i> Download Distribution List</a>
                        <a href="{{route('syncing.send_distribution_list_new')}}" type="button" name="button" class="btn btn-primary"><i class="fa fa-cloud-upload"></i> Send Distribution List New to Server</a>
                        <a href="{{route('syncing.send_distribution_list_updates')}}" type="button" name="button" class="btn btn-primary"><i class="fa fa-cloud-upload"></i> Send Farmer Profile Updates to Server</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Delivery Inspection Database</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <a href="{{route('syncing.send_actual_delivery')}}" type="button" name="button" class="btn btn-primary"><i class="fa fa-cloud-upload"></i> Send Actual Delivery Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
