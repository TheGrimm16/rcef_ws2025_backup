@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="page-title">
    <div class="title_left">
        <h3><span>Inspection Monitoring</span> <button class="btn btn-success btn-sm" id="excel_btn"><i class='fa fa-cloud-download'></i> DOWNLOAD INSPECTION DATA</button></h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="alert alert-success alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
    <strong><i class="fa fa-info-circle"></i> Notice: </strong> Clicking the "DOWNLOAD INSPECTION DATA" button will generate an excel file that contains all inspection data of deliveries including transferred seeds
</div>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title col-md-12">
                <h2 class="col-md-12">
                    <div class="form-group col-md-12">
                        <div class="col-md-4">
                            <select class="form-control select_class" id="moni_prov">
                                <option>Select Province</option>
                                @foreach ($inspected_provinces as $item)
                                <option value="{{$item->province}}">{{$item->province}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control select_class" id="moni_muni">
                                <option>Select Municipality</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control select_class" id="moni_dropoff">
                                <option>Select Dropoffpoint</option>
                            </select>
                        </div>
                    </div>
                </h2>

                <div id="coopData" style="display:none;">
                        <div>
                            <h2>List of SGC/A & their Regional Commitments</h2>
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="coopData_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>SGC/A Name</th>
                                    <th>Accreditation No.</th>
                                    <th>Total Commitment in Region: <span id="region1" style="color: inherit"></span></th>
                                    <th>Total Seeds delivered in Region: <span id="region2" style="color: inherit"></span></th>
                                </thead>
                                <tbody>
                              
                                </tbody>
                            </table>
                        </div>
                    </div>

                <div class="clearfix"></div>
                <div class="col-md-12" id="inspData" style="display:none;">
                    <h2>Inspection Data</h2>
                    <table id="list" class="table table-responsive-sm table-bordered" style="width:100%;">
                        <thead>
                        <th style="text-align: center !important;">#</th>
                        <th>Batch Ticket #</th>
                        <th>Status</th>
                        <th>Seed Cooperative</th>
                        <th>Varieties</th>
                        <th>Total bags confirmed</th>
                        <th>Total bags inspected</th>
                        <th>Date confirmed</th>
                        <th>Date inspected</th>
                        <th>Assigned Inspector</th>
                        <th>Action</th>
                        </thead>				
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INSPECTION SAMPLES MODAL -->
<div id="inspection_details_modal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-lg" style="width:100%;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="inspection_modal_title">{BATCH_TICKET_NUMBER}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6" id="sampling_col" style="display:block">
                        <h2><u>SAMPLING DATA</u></h2>
                        <table id="sample_list" class="table table-responsive-sm table-bordered" style="width:100%">
                            <thead>
                                <th>Batch Ticket #</th>
                                <th>Seed Tag</th>
                                <th>Weight (kg)</th>
                                <th>Date Sampled</th>
                            </thead>				
                        </table>
                    </div>
                    <div class="col-md-6" id="actual_del_col" style="display:none">
                        <h2 id="actual_del_title"><u>ACTUAL DELIVERY DATA</u></h2>
                        <table id="actual_delivery_tbl" class="table table-responsive-sm table-bordered" style="width:100%">
                            <thead>
                                <th>Batch Ticket #</th>
                                <th>Seed Tag</th>
                                <th>Seed Variety</th>
                                <th>Bags (20kg/bag)</th>
                                <th>Remarks</th>
                            </thead>				
                        </table>
                    </div>
                </div>
            </div>
            <!--<div class="modal-footer"></div>-->
        </div>
    </div>
</div>
<!-- INSPECTION SAMPLES MODAL -->


<!-- IAR UPLOAD MODAL -->
<div id="inspection_uploadIAR_modal" class="modal fade " role="dialog">
    <div class="modal-dialog" style="width:70%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Signed IAR</h4>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" action="{{route('inspector.iar.upload')}}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <input class="form-control" name="input_img" type="file" id="imageInput" accept="application/pdf, image/gif, image/jpeg, image/png">
                        <input type="hidden" id="batch_iar_number" name="batch_iar_number">
                    </div>
                    <div class="form-group">
                        <input class="btn btn-success" type="submit" value="UPLOAD SIGNED IAR">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- IAR UPLOAD MODAL -->


<!-- EDIT IAR UPLOAD MODAL -->
<div id="inspection_ReUploadIAR_modal" class="modal fade " role="dialog">
    <div class="modal-dialog" style="width:70%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Re-Upload Signed IAR</h4>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" action="{{route('inspector.iar.re_upload')}}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <input class="form-control" name="re_input_img" type="file" id="re_imageInput" accept="application/pdf, image/gif, image/jpeg, image/png">
                        <input type="hidden" id="re_batch_iar_number" name="re_batch_iar_number">
                    </div>
                    <div class="form-group">
                        <input class="btn btn-danger" type="submit" value="RE-UPLOAD SIGNED IAR">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- EDIT IAR UPLOAD MODAL -->

@endsection
@push('scripts')
<script>

    function resetDelivery(batchTicketNumber){
        var yesno = confirm("Reset "+ batchTicketNumber+" to inspect again?");
        if(yesno){
            $.ajax({
            type: 'GET',
            url: "utility/reset_delivery/"+batchTicketNumber,
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function (data) {
                    alert(data); 
            }
            });
        }



    }



    $('#moni_dropoff').on('change', function () {
        let prv_dropoff_id = $('#moni_dropoff').val();
        var prv_name = $('#moni_dropoff').find("option:selected").text();

        var myData = {prv_name:prv_name,drop_id: prv_dropoff_id, _token: "{{ csrf_token() }}"};
        var options = {
                    theme:"custom",
                    content:'<img style="width:8em; transform:translateY(-1em) translateX(-1.5em)" src="{{ asset('public/images/farmerLoading.gif') }}" class="center-block">',
                    message:'Please wait.',
                    backgroundColor:"#494f5f",
                    textColor:"white"
                };
           HoldOn.open(options);
            $("#coopData").show();
            $("#inspData").show();

            $("#coopData_tbl").DataTable().clear();
            $("#coopData_tbl").DataTable({
                bDestroy: true,
                autoWidth: false,
                searchHighlight: true,
                processing: true,
                serverSide: true,
                orderMulti: true,
                order: [],
                pageLength: 10,
                stripeClasses: [],
                ajax: {
                    url: "{{ route('rcef.insp_monitoring.coop_data') }}",
                    dataType: "json",
                    type: "POST",
                    data: {
                    _token: "{{ csrf_token() }}",
                    myData: myData,
                    },
                    complete: function(response) {
                        HoldOn.close();
                        $("#coopData").show();
                        $("#region1").text(response.responseJSON.data[0].regionName);
                        $("#region2").text(response.responseJSON.data[0].regionName);
                
                    }
                },
                columns: [
                    { data: "coopName" },
                    { data: "coopAccred" },
                    { data: "totalCommitments", defaultContent: "0", render: $.fn.dataTable.render.number(',', '.', 0)},
                    { data: "totalInspected", defaultContent: "0", render: $.fn.dataTable.render.number(',', '.', 0)}
                ],
                
            });

        $('#list').dataTable().fnDestroy();
        $('#list').DataTable({
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "order": [[0, "asc"]],
            "fixedHeader": {
                "header": false,
                "footer": false
            },
            searchDelay: 1000,
            oLanguage: {sProcessing: "<img src='public/images/processing.gif' />"},
            "ajax": {
                "url": "{{ route('rcef.insp_monitoring.table_data') }}",
                "dataType": "json",
                "method": "POST",
                "data": myData
            },
            "drawCallback": function (settings) {
            },
            "columns": [
                {"data": "number"},
                {"data": "batch"},
                {"data": "status"},
                {"data": "coop", orderable: false, searchable: false},
                {"data": "variety"},
                {"data": "confirmed"},
                {"data": "inspected", orderable: false, searchable: false},
                {"data": "date_confirmed"},
                {"data": "date_inspected", orderable: false, searchable: false},
                {"data": "inspector", orderable: false, searchable: false},
                {"data": "action", orderable: false, searchable: false}
            ],
            /*"fnInitComplete": function () {
                $(".view_batchdetails").click(function () {
                    $('#inspection_details_modal').modal({
                        keyboard: false
                    })
                });
                HoldOn.close();

            }*/
        });
    });

    //download inspection data as excel
    $("#excel_btn").on("click", function(e){
        $("#excel_btn").empty().html("<div class='fa fa-spinner fa-spin'></div> Generating Excel file please wait...");
        $("#excel_btn").attr("disabled", "");
        $.ajax({
            type: 'POST',
            url: "{{ route('inspector.excel.data') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function (response, textStatus, request) {
                    var a = document.createElement("a");
                    a.href = response.file; 
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                $("#excel_btn").empty().html("<i class='fa fa-cloud-download'></i> DOWNLOAD INSPECTION DATA");
                $("#excel_btn").removeAttr("disabled");
            }
        });
    });

    //upload iar number
    $('#inspection_uploadIAR_modal').on('show.bs.modal', function (e) {
        var batch_number = $(e.relatedTarget).data('id');
        $("#batch_iar_number").empty().val(batch_number);
    });

    //re-upload iar number
    $('#inspection_ReUploadIAR_modal').on('show.bs.modal', function (e) {
        var batch_number = $(e.relatedTarget).data('id');
        $("#re_batch_iar_number").empty().val(batch_number);
    });

    //show details
    $('#inspection_details_modal').on('show.bs.modal', function (e) {
        var batch_number = $(e.relatedTarget).data('id');

        $("#actual_del_col").css("display", "none");
        $("#sampling_col").css("display","block");
        $("#sampling_col").removeClass("col-md-6");
        $("#sampling_col").addClass("col-md-12");

        $.ajax({
            type: 'POST',
            url: "{{ route('inspector.monitoring.details') }}",
            data: {
                _token: "{{ csrf_token() }}",
                batch_number: batch_number,
            },
            success: function(data){
                $("#inspection_modal_title").empty().html("BATCH_TICKET_NUMBER: "+ data["batch_number"]);

                if(data["actual_delivery_status"] != "no_actual_delivery"){        
                    $("#actual_del_col").css("display", "block");
                    $("#actual_del_title").empty().html("<u>ACTUAL DELIVERY DATA: "+data["actual_delivery_status"]+" bag(s)</u>")

                    $("#sampling_col").css("display","block");
                    $("#sampling_col").removeClass("col-md-12");
                    $("#sampling_col").addClass("col-md-6");

                    //display all actual delivery
                    $("#actual_delivery_tbl").DataTable({
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "ajax": {
                            "url": "{{ route('inspector.monitoring.actual_delivery') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                "batch_number": batch_number,
                            }
                        },
                        "columns":[
                            {"data": "batchTicketNumber"},
                            {"data": "seedTag"},
                            {"data": "seedVariety"},
                            {"data": "totalBagCount"},
                            {"data": "remarks"},
                        ]
                    });

                }else{
                    $("#actual_del_col").css("display", "none");
                    $("#sampling_col").css("display","block");
                    $("#sampling_col").removeClass("col-md-6");
                    $("#sampling_col").addClass("col-md-12");
                }
            }
        }).done(function(e){
            $("#sample_list").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('inspector.monitoring.samples_tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "batch_number": batch_number,
                    }
                },
                "columns":[
                    {"data": "batchTicketNumber"},
                    {"data": "seedTag"},
                    {"data": "seed_weight_value"},
                    {"data": "dateSampled"}
                ]
            });
        });
    });

    $('#moni_prov').on('change', function () {
        let province = $('#moni_prov').val()
        $('#moni_dropoff').empty()
        $('#moni_muni').empty()
        // Get municipalities
        $.ajax({
            type: 'GET',
            url: 'insp_monitoring/get_muni/' + province,
            dataType: 'json',
            success: function (source) {
                let options = "<option></option>";
                source.forEach(function (item) {
                    options += "<option value='" + item['municipality'] + "'>" + item['municipality'] + "</option>";
                })
                $('#moni_muni').append(options)
            }
        })
    })
    $('#moni_muni').on('change', function () {
        let province = $('#moni_prov').val()
        let municipality = $('#moni_muni').val()
        $('#moni_dropoff').empty()
        // Get dropoff points
        $.ajax({
            type: 'GET',
            url: 'insp_monitoring/get_dropoff/' + province + '/' + municipality,
            dataType: 'json',
            success: function (source) {
                let options = "<option></option>"
                source.forEach(function (item) {
                    options += "<option value='" + item['prv_dropoff_id'] + "'>" + item['dropOffPoint'] + "</option>"
                });
                $('#moni_dropoff').append(options)
            }
        })
    });
    $(".select_class").select2();
</script>
@endpush
