@foreach($required_commitments as $commitment)
<div class="x_panel">
    <div class="x_title">
        <h2>{{$commitment->commitment_variety}}</h2>
        <div class="clearfix"></div>
    </div>
    <div class="x_content form-horizontal form-label-left">
        <div class="row tile_count" style="margin: 0">
            <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                <span style="">Total Committed</span>
                <div class="count" id="total_sg_enrolled"><i class="fa fa-users"></i>
                    {{number_format($commitment->total_value)}}</div>

            </div>
            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                <div class="row ml-3">
                    <div class="col-md-12 col-sm-6 col-xs-6">
                        <span>Allocated</span>
                        <div class=" sub-count" id=""><i class="fa fa-users"></i>
                            {{number_format($commitment->allocated_seed)}}</div>
                    </div>
                    <div class="col-md-12 col-sm-6 col-xs-6">
                        <span>Unallocated</span>
                        <div class="sub-count" id=""><i class="fa fa-users"></i>
                            {{number_format($commitment->total_value - $commitment->allocated_seed)}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><br>
@endforeach


@push('scripts')

@endpush