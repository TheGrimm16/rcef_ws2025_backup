@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Seed Cooperatives</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-striped table-bordered" id="coop_table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th style="width: 200px;padding-right:0px;">Commited</th>
                            <th style="width: 200px;padding-right:0px;">Confirmed</th>
                            <th style="width: 200px;padding-right:0px;">Inspected</th>
                            <th style="width: 200px;padding-right:0px;">Distributed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


@include('DeliveryDashboard.includes.seed_grower_details_modal')
@include('DeliveryDashboard.includes.batch_details_modal')
@endsection()

@push('scripts')
<script>

</script>
@endpush()
