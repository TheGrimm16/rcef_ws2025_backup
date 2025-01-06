
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


            <!-- <div class="page-title">
                <div class="title_left">
                    <h3>SED Controller (EDITING)</h3>
                </div>
            </div> -->
            <div class="clearfix"></div>
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                
                <div class="panel panel-info">
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
                <div class="panel panel-success" style="display:none">
                    <div class="panel-heading" role="tab" id="head_">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-7">Summary: </div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-center">{{$summary['answered_yes']}}</div>
                                <div class="col-md-1 text-center">{{$summary['answered_no']}}</div>
                                <!-- <div class="col-md-2 text-center">{{$summary['total_yield_all']}}</div>
                                <div class="col-md-1 text-center">{{$summary['answered_next']}}</div> -->
                                <div class="col-md-1 text-center">{{$summary['failed_calls']}}</div>
                               
                                <div class="col-md-1 text-right">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($data as $d)
                <div class="panel panel-default" id="municipality_panels">
                    <div class="panel-heading" role="tab" id="head_{{$d->id}}">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-3"><b>{{$d->municipality}}</b></div>
                                <div class="col-md-3">{{$d->sowing_min}} - {{$d->sowing_max}}</div>
                                <div class="col-md-2 text-center"><b>{{$d->respondents}}</b> / <small>{{$d->farmer_count}} ({{$d->percentage}}%)</small></div>
                                <div class="col-md-1 text-center">{{$d->yes_count}}</div>
                                <div class="col-md-1 text-center">{{$d->no_count}}</div>
                                <!-- <div class="col-md-2 text-center"><small> {{$d->with_yield}} ( {{$d->total_area}} - {{$d->total_yield}})</small></div>
                                <div class="col-md-1 text-center">{{$d->next_season}}</div> -->
                                <div class="col-md-1 text-center">{{$d->failed_call}}</div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtnMuni" data-toggle="collapse" data-parent="#accordion"
                                        href="#{{$d->id}}" aria-expanded="true" aria-controls="{{$d->id}}"
                                        data-municode="{{$d->muni_code}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="{{$d->id}}" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="head_{{$d->id}}">
                        <div class="panel-body panel_body_muni">
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
					<div id="verifyModalContent"></div>
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

<script>
$(".collapseBtnMuni").on("click", function() {
    var municode = $(this).data('municode');
    let id = "#prv_mun_"+municode+" .panel-body";
    let heading_id  = "#head_prv_" + municode;
    $(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
    $(".panel_body_muni").empty();
    $.ajax({
        type: "POST",
        url: "{{url('sed/verified/municipality/data')}}",
        data: {
            municode: municode,
            _token: "{{csrf_token()}}"
        },
        success: function(response) {  
            
            if (!$(heading_id).hasClass("active_collapse")) {
                $('#municipality_panels>.panel-heading').removeClass("active_collapse");
                $(heading_id).addClass("active_collapse");
            }else{
                $('#municipality_panels>.panel-heading').removeClass("active_collapse");
            }
            $(id).append(response);
        }
    });
});
</script>
