@extends('layouts.index')

@section('content')
<link rel="stylesheet" href="{{ asset('public/assets/boxed-check/css/boxed-check.css') }}">


<link rel="stylesheet" type="text/css" href="{{ asset('public/assets/daterangepicker/daterangepicker.css') }}" />
<style>
.title_count {
    height: 70px;
}
span.label{
    font-size: 11px !important;
}
</style>
<style>
.lds-facebook {
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.lds-facebook div {
  display: inline-block;
  position: absolute;
  left: 8px;
  width: 16px;
  background: #26B99A;
  animation: lds-facebook 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite;
}
.lds-facebook div:nth-child(1) {
  left: 8px;
  animation-delay: -0.24s;
}
.lds-facebook div:nth-child(2) {
  left: 32px;
  animation-delay: -0.12s;
}
.lds-facebook div:nth-child(3) {
  left: 56px;
  animation-delay: 0;
}
@keyframes lds-facebook {
  0% {
    top: 8px;
    height: 64px;
  }
  50%, 100% {
    top: 24px;
    height: 32px;
  }
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Filters</h2>
                    <div class="clearfix"></div>
                </div>
                <form method="post" id="generateData">
                    <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                <div class="form-group row">
                                    <div class="col-sm-2">
                                        <label for="province" class="control-label">Province </label><br />
                                        <select id="province" name="province"
                                            class="js-example-basic-single js-states select form-control"
                                            style="width: 100% !important">
                                            <option value="" disabled selected>Select Province</option>
                                            <option value="">All</option>
                                            @foreach($provinces as $k => $p)
                                            <option value="{{$k}}">{{$p}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="municipality" class="control-label">Municipality </label><br />
                                        <select id="municipality" name="municipality"
                                            class="js-example-basic-single js-states select form-control"
                                            style="width: 100% !important">
                                            <option value="" disabled selected>Select Municipality</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="users" class="control-label">Enumerators </label><br />
                                        <select id="users" name="users"
                                            class="js-example-basic-single js-states select form-control"
                                            style="width: 100% !important">
                                            <option value="" disabled selected>Select Enumerators</option>
                                            <option value="">All</option>

                                            @foreach($users as $uk => $u)
                                            <option value="{{$u->userId}}">{{$u->fullname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="municipality" class="control-label">Date </label><br />
                                        <input type="text" name="daterange" class="form-control" value="{{$datedefault}}" />
                                    </div>
                                   
                                    <div class="col-sm-2">
                                        <div class="text-center">
                                            <label for="claim_loc" class="control-label" style="margin-top: 10px">&nbsp; </label>
                                            <button type="submit" class="btn btn-success" style="margin-top:20px;">FILTER</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <div class="row">
                        <div class="col-md-2">
                            <h2>Summary</h2>
                        </div>
                        <div class="col-md-10 text-right">
                            <button class="btn btn-button btn-success" id='downloadSummary'><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Detailed Summary</button>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="x_content form-horizontal form-label-left" >
                        <div id="summaryContent"></div>
                    </div>
                </div>
            </div>
         </div>
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Result Table</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <table class="table table-striped table-bordered wrap"  id="farmersTbl">
                                <thead>
                                    <tr>
                                        <th style="width: auto;">Farmer Name</th>
                                        <th style="width: auto;">Province</th>
                                        <th style="width: auto;">Municipality</th>
                                        <th style="width: auto;">Contact no.</th>
                                        <th style="width: auto;">Sex</th>
                                        <th style="width: auto;">Verified Area</th>
                                        <th style="width: auto;">Variety 1</th>
                                        <th style="width: auto;">Variety 2</th>
                                        <th style="width: auto;">Expected Sowing</th>
                                        <th style="width: auto;">Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ asset('public/assets/daterangepicker/daterangepicker.js') }}"></script>
<script>
$('input[name="daterange"]').daterangepicker();
$("#province").select2({
    width: 'resolve'
});
$("#municipality").select2({
    width: 'resolve'
});
$("#status").select2({
    width: 'resolve'
});
$("#users").select2({
    width: 'resolve'
});
$("#participants").select2({
    width: 'resolve'
});
$("#province").on('change', function() {
    if ($(this).val() == "") {
        $("#municipality").prop("disabled", true);
        $("#municipality").val("");
    } else {

        $.ajax({
            type: "POST",
            url: "{{url('sed/municipality')}}",
            data: {
                _token: "{{ csrf_token() }}",
                provCode: $(this).val()
            },
            success: function(response) {
                $("#municipality").prop("disabled", false);
                obj = JSON.parse(response);

                $('#municipality').empty();
                $('#municipality').append($('<option>').val("").text(""));
                obj.forEach(data => {
                    $('#municipality').append($('<option>').val(data.citymunCode).text(
                        data.citymunDesc));
                });
            }
        });
    }
});



$("#generateData").on("submit", function(e) {
    e.preventDefault();
    $("#summaryContent").empty().append("<div class='lds-facebook'><div></div><div></div><div></div></div>");
    $('#farmersTbl').DataTable().ajax.reload();
    summaryContent();
    var mun = $("#municipality").val();
    var prov = $("#province").val();
});
summaryContent();
function summaryContent(){
    $("#summaryContent").empty().append("<div class='lds-facebook'><div></div><div></div><div></div></div>");
    $.ajax({
        type: "POST",
        url: "{!! route('sed.farmers.manage.summary') !!}",
        data: {
            _token : "{{csrf_token()}}",
            province : $("#province").val(),
            municipality : $("#municipality").val(),
            status : $("#status").val(),
            users : $("#users").val(),
            daterange : $('input[name="daterange"]').val()
        },
        success: function (response) {
            $("#summaryContent").empty().append(response);
        }
    });
}

$("#downloadSummary").click(function (e) { 
    e.preventDefault();
    var date = $('input[name="daterange"]').val();
    date = date.split(" - ");
    var datefrom = date[0].replace("/", "-")
     datefrom = datefrom.replace("/", "-")
    var dateto = date[1].replace("/", "-")
     dateto = dateto.replace("/", "-")

    window.open("{{url('sed/excel/summary/')}}" + "/" + $("#province").val() + "/" + $("#municipality").val() + "/" + $("#status").val() + "/" + $("#users").val() + "/" + datefrom + "/" + dateto);
});
</script>
@endpush