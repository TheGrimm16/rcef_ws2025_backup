@extends('layouts.index')

@section('content')
    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Delivery Summary</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Confirmed Delivery vs Actual Delivery</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin-bottom: 20px;">
                            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                                <span class="count_top">Total Confirmed Delivery (bags)</span>
                                <div class="count">{{number_format($confirmed->total_bag_count)}}</div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                                <span class="count_top">Total Actual Delivery (bags)</span>
                                <div class="count">{{number_format($actual->total_bag_count)}}</div>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped" id="delivery_summary_table">
                            <thead>
                                <tr>
                                    <th>Region</th>
                                    <th>Province</th>
                                    <th>Municipality</th>
                                    <th>Dropoff Point</th>
                                    <th>Confirmed Delivery (bags)</th>
                                    <th>Actual Delivery (bags)</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('summary.scripts')
@endpush
