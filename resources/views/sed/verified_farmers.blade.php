@extends('layouts.index')

@section('content')
<style>
    .active_collapse{
        background-color: #337ab7 !important;
        color: white !important;
    }

    .panel > .panel-heading2 {
        background-color:  #337ab7;
        color: white;
        border-bottom: 0;
        padding: 10px 15px;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
    }
    
</style>
<div class="container" style="padding: 3rem">
    <div class="row">
        <div class="col-md-12">


            <div class="page-title">
                <div class="title_left">
                    <h3>SED Controller (EDITING)</h3>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>ANSWERED YES</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['answered_yes']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>ANSWERED NO</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['answered_no']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-2" style="display: none">
                        <div class="panel panel-default text-center" >
                            <div class="panel-heading2"><h4><b>NEXT SEASON</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['answered_next']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>FAILED CALLS</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['failed_calls']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-2" style="display: none">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>TOTAL AREA (ha)</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['total_area_all']}} </b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-2" style="display: none">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>Ave. Yield (kg/ha)</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['total_yield_all']}}</b></h1></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="panel-group" id="accordionParent" role="tablist" aria-multiselectable="true">
                <div class="panel panel-primary">
                    <div class="panel-heading" role="tab" id="head_">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-3">Location</div>
                                <div class="col-md-3">Expected Sowing</div>
                                <div class="col-md-2 text-center">Respondents</div>
                                <div class="col-md-1 text-center">Yes</div>
                                <div class="col-md-1 text-center">No</div>
                                <!-- <div class="col-md-2 text-center">Farmers w/ Yield <br/><small>(Total Area (ha) - Ave. Yield (kg/ha))</small></div>
                                <div class="col-md-1 text-center">Next Season</div> -->
                                <div class="col-md-1 text-center">Call Failed</div>
                               
                                <div class="col-md-1 text-right">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($data as $d)
                <div class="panel panel-default" id="municipalities">
                    <div class="panel-heading" role="tab" id="head_{{$d->id}}">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-3"><b>{{$d->province}}</b></div>
                                <div class="col-md-3">{{$d->sowing_min}} - {{$d->sowing_max}}</div>
                                <div class="col-md-2 text-center"><b>{{$d->respondents}}</b> / <small>{{$d->farmer_count}} ({{$d->percentage}}%)</small></div>
                                <div class="col-md-1 text-center">{{$d->yes_count}}</div>
                                <div class="col-md-1 text-center">{{$d->no_count}}</div>
                                <!-- <div class="col-md-2 text-center"><small> {{$d->with_yield}} ( {{$d->total_area}} - {{$d->total_yield}})</small></div>
                                <div class="col-md-1 text-center">{{$d->next_season}}</div> -->
                                <div class="col-md-1 text-center">{{$d->failed_call}}</div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtn" data-toggle="collapse" data-parent="#accordionParent"
                                        href="#{{$d->id}}" aria-expanded="true" aria-controls="{{$d->id}}"
                                        data-prv_code="{{$d->prv_code}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="{{$d->id}}" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="head_{{$d->id}}">
                        <div class="panel-body">
                            <div class="text-center">Loading <i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
        <div id="verifyModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div id="verifyModalContent">asdasdasd</div>
				</div>
			</div>
		</div>

		<div id="checkParti" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div id="checkPartiContent"></div>
				</div>
			</div>
		</div>
@endsection

@push('scripts')
<script>
$(".collapseBtn").on("click", function() {
    var prv_code = $(this).data('prv_code');
    let id = "#prv_"+prv_code+" .panel-body";
    let heading_id  = "#head_prv_" + prv_code;
    $(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
    $.ajax({
        type: "POST",
        url: "{{url('sed/verified/municipalities')}}",
        data: {
            prv_code: prv_code,
            _token: "{{csrf_token()}}"
        },
        success: function(response) {  
            $(id).empty();
            if (!$(heading_id).hasClass("active_collapse")) {
                $('#municipalities>.panel-heading').removeClass("active_collapse");
                $(heading_id).addClass("active_collapse");
            }else{
                $('#municipalities>.panel-heading').removeClass("active_collapse");
            }
            $(id).append(response);
        }
    });
});
</script>
@endpush