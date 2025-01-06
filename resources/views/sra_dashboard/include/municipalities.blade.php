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
            
            <div class="clearfix"></div>
            <div class="panel-group" id="accordion2" role="tablist" aria-multiselectable="true">
                <div class="panel panel-info">
                    <div class="panel-heading" role="tab" id="head_">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-5">Province <i class="fa fa-arrow-right" aria-hidden="true"></i> Municipality</div>
                                <div class="col-md-2">no. of Farmers</div>
                                <div class="col-md-2">Scheduled Farmers</div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-center"></div>
                                <!-- <div class="col-md-2">Distribution Date</div> -->
                                <div class="col-md-1 text-right">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($data as $d)
                <div class="panel panel-default" id="muni_panels">
                    <div class="panel-heading" role="tab" id="head_{{$d->claiming_prv}}">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-5">{{$d->province_name}} <i class="fa fa-arrow-right" aria-hidden="true"></i> <b>{{$d->municipality_name}}</b></div>
                                <div class="col-md-2">{{$d->farmer_count}}</div>
                                <div class="col-md-2">{{$d->farmer_scheduled}}</div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtnMuni" data-toggle="collapse" data-parent="#accordion2"
                                        href="#{{$d->claiming_prv}}" aria-expanded="true" aria-controls="{{$d->claiming_prv}}"
                                        data-muni="{{$d->claiming_prv}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="{{$d->claiming_prv}}" class="panel-collapse collapse municontainer" role="tabpanel"
                        aria-labelledby="head_{{$d->claiming_prv}}">
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

<script>
$(".collapseBtnMuni").on("click", function() {
    var muni = $(this).data('muni');
    let id = "#"+muni+" .panel-body";
    let heading_id  = "#head_" + muni;
    $(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
    $.ajax({
        type: "POST",
        url: "{{url('sra/paymaya/load/barangay')}}",
        data: {
            muni: muni,
            month: "{{$month}}",
            _token: "{{csrf_token()}}"
        },
        success: function(response) {  
            $(".panel-collapse.municontainer .panel-body").empty();
            if (!$(heading_id).hasClass("active_collapse")) {
                $('#muni_panels>.panel-heading').removeClass("active_collapse");
                $(heading_id).addClass("active_collapse");
            }else{
                $('#muni_panels>.panel-heading').removeClass("active_collapse");
            }
            $(id).append(response);
        }
    });
});
</script>
