@extends('layouts.index')

@section('content')
<div>
    <div class="page-title">
        <div class="title_left">
            <h3>E-binhi DOP Management</h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title container">
                    <div class="row">
                        <div class="col-md-8">
                            <h2>Drop Off Points</h2>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-button btn-primary" id='addDOP'>Add DOP</button>

                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <table class="table table-striped table-bordered" id="dopTbl">
                        <thead>
                            <tr>
                                <th style="width: auto;">Cooperative</th>
                                <th style="width: auto;">Province</th>
                                <th style="width: auto;">Municipality</th>
                                <th style="width: auto;">Pickup Location</th>
                                <th style="width: auto;">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
<div id="addDOPModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <form class="form-horizontal" id="addDOPModalForm">
                <div class="container" style="padding: 30px">
                    <div class="row">
                        <div class="col-12">
                            <h4 style=" margin-bottom:20px">ADD DROP OFF POINT</h4>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="coop_name" class="control-label">Cooperative Name <strong
                                    style="color:red">*</strong></label>
                            <select id="coop_name" name="coop_name"
                                class="js-example-basic-single js-states select form-control"
                                style="width: 100% !important" required>
                                <option value="" disabled selected>Select Cooperative</option>

                                @foreach($coops as $c)

                                <option value="{{$c->coopName}}">{{$c->coopName}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="province" class="control-label">Province <strong
                                    style="color:red">*</strong></label>
                            <select id="province" name="province"
                                class="js-example-basic-single js-states select form-control"
                                style="width: 100% !important" required>
                                <option value="" disabled selected>Select Province</option>

                                @foreach($provinces as $k => $p)

                                <option value="{{$p->province}}">{{$p->province}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="municipality" class="control-label">Municipality <strong
                                    style="color:red">*</strong></label>
                            <select id="municipality" name="municipality"
                                class="js-example-basic-single js-states select form-control"
                                style="width: 100% !important" required>
                                <option value="" disabled selected>Select Municipality</option>

                            </select>
                        </div>
                    </div>

                    <div class="form-group row" style="">
                        <div class="col-12">
                            <label for="pickup_location" class="control-label">Drop Off Point / Pickup Location</label>
                            <input id="pickup_location" name="pickup_location" type="text"
                                class="js-example-basic-single js-states select form-control" required value="">
                        </div>
                    </div>

                    <div class="form-group row" style="margin-top: 20px">
                        <button name="submit" type="submit" class="btn btn-primary pull-right"
                            id="saveDOP">Save</button>
                        <button class="btn btn-light pull-right" id="dismissModal">cancel</button>
                    </div>
                </div>

            </form>
        </div>



    </div>
</div>

<div id="checkParti2" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div id="checkPartiContent2"></div>
        </div>
    </div>
</div>

<div id="checkParti" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:90%">
        <!-- Modal content-->
        <div class="modal-content">
            <div id="checkPartiContent"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$("#province").select2({
    width: 'resolve'
});
$("#coop_name").select2({
    width: 'resolve'
});
var dopTbl = $('#dopTbl').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{!! route('sra.paymaya.dop.datatable') !!}"
    },
    columns: [{
            data: 'coop_name',
            name: 'coop_name'
        },
        {
            data: 'province',
            name: 'province'
        },
        {
            data: 'municipality',
            name: 'municipality'
        },
        {
            data: 'pickup_location',
            name: 'pickup_location'
        },
        {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false
        }
    ]
});

$('#dopTbl tbody').on('click', 'tr td button.editFarmer', function(e) {
    var prv = $(this).data('id');
    var value = $(this).data('val');
    $.ajax({
        type: "POST",
        url: "{{url('ebinhi/municipality/edit')}}",
        data: {
            prv: prv,
            value: value,
            _token: "{{csrf_token()}}"
        },
        success: function(response) {
            muniTbl.ajax.reload(null, false);
        }
    });
});

$('#checkParti2').on('hidden.bs.modal', function(e) {
    dopTbl.ajax.reload(null, false);
});

$("#addDOP").click(function(e) {
    e.preventDefault();

    $('#addDOPModal').modal({
        backdrop: 'static',
        keyboard: false
    });

});
$("#dismissModal").on("click", function(e) {
    e.preventDefault();
    $('#addDOPModal').modal('hide');
});
$("#province").on('keyup change', function() {

    $('.help-block.text-danger').empty();
    if ($(this).val() == "") {
        $("#municipality").prop("disabled", true);
        $("#municipality").val("");
    } else {

        $.ajax({
            type: "POST",
            url: "{{url('sra/paymaya/municipality')}}",
            data: {
                _token: "{{ csrf_token() }}",
                province: $(this).val()
            },
            success: function(response) {
                $("#municipality").prop("disabled", false);
                obj = JSON.parse(response);

                $('#municipality').empty();
                $('#municipality').append($('<option>').val("").text(""));
                obj.forEach(data => {
                    // $('#municipality').append($('<option>').val(data.municipality).text(
                    //     data.municipality));
                    $('<option/>', {
                        text: data.municipality,
                        value: data.municipality,
                        data: {
                            prv: data.prv,
                        }
                    }).appendTo("#municipality");
                });
            }
        });
    }
});

$("#saveDOP").click(function(e) {
    e.preventDefault();
    var selected = $("#municipality").find('option:selected');
    var muni_code = selected.data('prv');
    $.ajax({
        type: "POST",
        url: "{{url('sra/paymaya/dop/save')}}",
        data: {
            _token: "{{ csrf_token() }}",
            province: $("#province").val(),
            municipality: $("#municipality").val(),
            coop_name: $("#coop_name").val(),
            pickup_location: $("#pickup_location").val(),
            prv_code: muni_code
        },
        success: function(response) {
            alert(response.message);
            if (response.status == 1) {
                $('#addDOPModal').modal('hide');
                location.reload();
            }
        }
    });
});
$('#dopTbl tbody').on('click', 'tr td button.editDOP', function (e) {
    var id = $(this).data('id');
    // var value = $(this).data('value');
    $.ajax({
        type: "POST",
        url: "{{url('sra/dop/view/edit')}}",
        data: {
            id: id,
            _token: "{{csrf_token()}}"
        },
        success: function (response) {
            $("#checkPartiContent2").html(response);
            $('#checkParti2').modal();
            farmersTbl.ajax.reload(null, false);
        }
    });	
});

$('#dopTbl tbody').on('click', 'tr td button.viewDOPFarmers', function (e) {
    var id = $(this).data('id');
    // var value = $(this).data('value');
    $.ajax({
        type: "POST",
        url: "{{url('sra/dop/view/edited/farmers')}}",
        data: {
            id: id,
            _token: "{{csrf_token()}}"
        },
        success: function (response) {
            $("#checkPartiContent").html(response);
            $('#checkParti').modal();
            farmersTbl.ajax.reload(null, false);
        }
    });	
});

</script>
@endpush