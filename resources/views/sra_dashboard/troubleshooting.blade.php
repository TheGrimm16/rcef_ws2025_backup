@extends('layouts.index')

@section('content')
<link rel="stylesheet" href="{{ asset('public/assets/boxed-check/css/boxed-check.css') }}">
<style>
.title_count {
    height: 70px;
}
.boxed-check-group{
    padding-top: 10px;
    margin-bottom: 20px;
}
.boxed-check-group .boxed-check .boxed-check-label{
    padding: 5px;
    font-weight: normal;
    margin:2px;
}
.boxed-check-group .boxed-check{
    display: unset;
    margin-bottom: 5px;
}
.boxed-check-input:checked + .boxed-check-label{
    background-color: #198754 !important;
    color: white !important;
}

.d-flex {
    display:flex;
}
.d-flex>div {
    float: none;
}
.col-auto {
    flex: 0 0 auto;
    width: auto;
    max-width: none;
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Area Selection</h2>
                    <div class="clearfix"></div>
                </div>
                <form method="post" id="generateData">
                    <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="province" class="control-label">Province </label><br />
                                        <select id="province" name="province"
                                            class="js-example-basic-single js-states select form-control"
                                            style="width: 100% !important">
                                            <option value="" disabled selected>Select Province</option>

                                            @foreach($provinces as $k => $p)
                                            <option value="{{$k}}">{{$p}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="municipality" class="control-label">Municipality </label><br />
                                        <select id="municipality" name="municipality"
                                            class="js-example-basic-single js-states select form-control"
                                            style="width: 100% !important">
                                            <option value="" disabled selected>Select Municipality</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="claim_loc" class="control-label">Claim Location </label><br />
                                        <select id="claim_loc" name="claim_loc"
                                            class="js-example-basic-single js-states select form-control"
                                            style="width: 100% !important">
                                            <option value="" disabled selected>Select Claim Location</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success" style="margin-top:20px;">GENERATE
                                        DATA</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <div class="x_title">
                    <h2>Filters</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label for="barangay" class="control-label">Barangay </label><br />
                            <div class="boxed-check-group row d-flex" id="barangay" style="overflow-y: auto">
                                <!-- <label class="boxed-check">
                                    <input class="boxed-check-input" type="checkbox" name="checkbox-overview">
                                    <div class="boxed-check-label">Normal</div>
                                </label> -->
                            </div>
                            <label for="varieties" class="control-label">Varieties </label><br />
                            <div class="boxed-check-group row d-flex"  id="varieties" style="overflow-y: auto">
                                
                            </div>
                            <label for="claim_loc" class="control-label">AM-PM Schedule </label><br />
                            <select id="claim_loc" name="claim_loc"
                                class="js-example-basic-single js-states select form-control"
                                style="width: 100% !important">
                                <option value="AM">AM Schedule (8AM - 12NN)</option>
                                <option value="PM">PM Schedule (1PM - 5PM)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
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
                                        <th style="width: 20%;">Farmer Name</th>
                                        <th style="width: 40%;">Claim Location</th>
                                        <th style="width: 20%;">Preferred Seed Variety</th>
                                        <th style="width: 10%;">Area</th>
                                        <th style="width: 10%;">Bags to Claim</th>
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
<script>
$("#province").select2({
    width: 'resolve'
});
$("#municipality").select2({
    width: 'resolve'
});
$("#claim_loc").select2({
    width: 'resolve'
});
$("#province").on('change', function() {
    if ($(this).val() == "") {
        $("#municipality").prop("disabled", true);
        $("#municipality").val("");
    } else {

        $.ajax({
            type: "POST",
            url: "{{url('palaysikatan/municipality')}}",
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

$('#farmersTbl').DataTable({
    "serverSide": true,
    "ajax": {
        "url": "{!! route('sra.troubleshooting.paymaya.datatable') !!}",
        "type": "POST",
        "data": function(d) {
            d._token = "{{csrf_token()}}";
            d.province = $("#province").val();
            d.municipality = $("#municipality").val();
        }
    },
    columns: [{
            data: 'fullname',
            name: 'fullname'
        },
        {
            data: 'drop_off_point',
            name: 'drop_off_point'
        },
        {
            data: 'preferred_variety',
            name: 'preferred_variety'
        },
        {
            data: 'area',
            name: 'area'
        },
        {
            data: 'bags',
            name: 'bags'
        },
    ]
});

$("#generateData").on("submit", function(e) {
    e.preventDefault();
    $('#farmersTbl').DataTable().ajax.reload();
    var mun = $("#municipality").val();
    var prov = $("#province").val();
    if(mun != ""){
        $.ajax({
            type: "POST",
            url: "{!! route('sra.load.baranggay') !!}",
            data: {
                province: prov,
                municipality: mun,
                _token: "{{csrf_token()}}"
            },
            success: function (response) {
                obj = JSON.parse(response);
                $('#baranggay').empty();
                obj.forEach(data => {
                    $('#barangay').append('<label class="boxed-check col-auto" ><input class="boxed-check-input" type="checkbox" name="barangay" value="'+ data.barangay +'">'+
                                    '<div class="boxed-check-label">'+ data.barangay +'</div></label>');
                });
            }
        });
    }

    $.ajax({
        type: "POST",
        url: "{!! route('sra.load.varieties') !!}",
        data: {
            province: prov,
            municipality: mun,
            _token: "{{csrf_token()}}"
        },
        success: function (response) {
            obj = JSON.parse(response);
            $('#varieties').empty();
            obj.forEach(data => {
                if(data.preferred_variety != ""){
                    $('#varieties').append('<label class="boxed-check col-auto" ><input class="boxed-check-input" type="checkbox" name="varieties" value="'+ data.preferred_variety +'">'+
                                '<div class="boxed-check-label">'+ data.preferred_variety +'</div></label>');
                } 
            });
        }
    });

});
$('input[name="barangay"]').change(function(e){
        console.log($('input[name="barangay"]:checked').serialize());
        console.log("sadasdsad");
    });
</script>
@endpush