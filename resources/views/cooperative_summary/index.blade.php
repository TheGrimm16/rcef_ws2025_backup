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
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{route('ReportController.coop_sg_count_excel')}}"><button class="btn btn-primary" style="margin-bottom: 20px;"><i class="fa fa-download"></i> Download Excel </button></a>
                    </div>
                    <div class="col-md-3" >
                        TOTAL COOPERATIVES: <b><span id="coop_total"></span></b>
                    </div>
                    <div class="col-md-3" >
                        TOTAL SEED GROWERS: <b><span id="sg_total"></span></b>
                    </div>
                    <div class="col-md-3" >
                        TOTAL COMMITED AREA: <b><span id="ha_total"></span></b>
                    </div>
                </div>
                <table class="table table-striped table-bordered" id="coop_table_summary">
                    <thead>
                        <tr>
                            <th>Cooperative Name</th>
                            <th>No. of Seed Growers</th>
                            <th>Commited Area ( ha )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection()

@push('scripts')
<script>

</script>
@endpush()
