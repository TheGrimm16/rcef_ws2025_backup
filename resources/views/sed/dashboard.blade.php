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
                    <h3>SED Controller (PLANNING)</h3>
                </div>
            </div>

            <div class="container">
                <div class="row">
                    <div class="col-md-2">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>no. of Province</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['prov_count']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>no. of Municipality</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['muni_count']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>Total Respondents</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['res_count']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>no. of Male</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['male_count']}}</b></h1></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading2"><h4><b>no. of Female</b></h4></div>
                            <div class="panel-body"><h1><b>{{$summary['fem_count']}}</b></h1></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-primary">
                    <div class="panel-heading" role="tab" id="head_">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-3">Location</div>
                                <div class="col-md-3">Previous Delivery Sched</div>
                                <div class="col-md-1 text-center">Respondents</div>
                                <div class="col-md-1 text-center"># of Male</div>
                                <div class="col-md-1 text-center"># of Female</div>
                                <!-- <div class="col-md-2">Distribution Date</div> -->
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
                                <div class="col-md-3">{{$d->province}} > {{$d->municipality}}</div>
                                <div class="col-md-3">{{$d->min_delivery_sched}} - {{$d->max_delivery_sched}}</div>
                                <div class="col-md-1 text-center">{{$d->respondents}}</div>
                                <div class="col-md-1 text-center">{{$d->male_count}}</div>
                                <div class="col-md-1 text-center">{{$d->female_count}}</div>
                                <div class="col-md-2"></div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtn" data-toggle="collapse" data-parent="#accordion"
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
@endsection

@push('scripts')
<script>
$(".collapseBtn").on("click", function() {
    var municode = $(this).data('municode');
    let id = "#prv_"+municode+" .panel-body";
    let heading_id  = "#head_prv_" + municode;
    $(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
    $.ajax({
        type: "POST",
        url: "{{url('sed/dashboard/municipality/data')}}",
        data: {
            municode: municode,
            _token: "{{csrf_token()}}"
        },
        success: function(response) {  
            $(".panel-collapse .panel-body").empty();
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
@endpush