<style>
.active_collapse {
    background-color: #337ab7 !important;
    color: white !important;
}

.panel>.panel-heading2 {
    background-color: #337ab7;
    color: white;
    border-bottom: 0;
    padding: 10px 15px;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
}

.scheduling-contrainer {
    background-color: #F5F5F5;
    border: solid 1px #ddd;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    padding: 10px 15px;
}

.scheduling-title {
    font-size: 18px;
    font-weight: bolder;

}
</style>
<div class="container" style="padding: 3rem">
    <div class="row">
        <div class="col-md-12">

            <div class="clearfix"></div>
            <div class="panel-group" id="accordion3" role="tablist" aria-multiselectable="true">
                <div class="panel panel-success">
                    <div class="panel-heading" role="tab" id="head_">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-2">Batch Code</div>
                                <div class="col-md-3">Schedule Date</div>
                                <div class="col-md-1">Time</div>
                                <div class="col-md-2">no. of Farmers</div>
                                <div class="col-md-2">SMS Sent</div>
                                <div class="col-md-1 text-center"></div>
                                <!-- <div class="col-md-2">Distribution Date</div> -->
                                <div class="col-md-1 text-right">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($data as $d)
                <div class="panel panel-default" id="brgy_panels">
                    <div class="panel-heading" role="tab" id="head_{{$d->batch_code}}">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-2">{{$d->batch_code}}</b></div>
                                <?php
                                    $f = strtotime($d->date_from);
                                    $from = date("F j, Y", $f);
                                    $t = strtotime($d->date_to);
                                    $to = date("F j, Y", $t);
                                ?>
                                <div class="col-md-3">{{$from}} - {{$to}}</b></div>
                                <div class="col-md-1">{{$d->sched_time}}</b></div>
                                <div class="col-md-2">{{$d->farmer_count}}</div>
                                <div class="col-md-2">{{$d->sent_count}}</div>
                                <div class="col-md-1 text-center"></div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtnBrgy" data-toggle="collapse"
                                        data-parent="#accordion3" href="#{{$d->batch_code}}" aria-expanded="true"
                                        aria-controls="{{$d->batch_code}}" data-batch_code="{{$d->batch_code}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="{{$d->batch_code}}" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="head_{{$d->batch_code}}">
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
$(".collapseBtnBrgy").on("click", function() {
    var batch_code = $(this).data('batch_code');
    let id = "#" + batch_code + " .panel-body";
    let heading_id = "#head_" + batch_code;
    $(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
    $.ajax({
        type: "POST",
        url: "{{url('sra/paymaya/load/scheduled/farmers')}}",
        data: {
            batch_code: batch_code,
            muni: "{{$muni}}",
            _token: "{{csrf_token()}}"
        },
        success: function(response) {
            $("#" + batch_code + ".panel-collapse .panel-body").empty();
            if (!$(heading_id).hasClass("active_collapse")) {
                $('#brgy_panels>.panel-heading').removeClass("active_collapse");
                $(heading_id).addClass("active_collapse");
            } else {
                $('#brgy_panels>.panel-heading').removeClass("active_collapse");
            }
            $(id).append(response);
        }
    });
});
</script>