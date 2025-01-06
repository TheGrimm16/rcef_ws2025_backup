@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="page-title">
    <div class="title_left">
        <h3><span>Seed tag tracking</span> </h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title col-md-12">
                <h2 class="col-md-12">
                    <div class="form-group col-md-12">
                        <div class="col-md-6">
                            <select class="form-control select_class" id="moni_prov">
                                <option>Select Province</option>
                                @foreach ($delivery_provinces as $item)
                                <option value="{{$item->province}}">{{$item->province}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-control select_class" id="moni_muni">
                                <option>Select Municipality</option>
                            </select>
                        </div>
                    </div>
                </h2>

                <div class="clearfix"></div>
                <div class="col-md-12">
                    <table id="list" class="table table-responsive-sm table-bordered" style="width:100%">
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

<!-- IAR UPLOAD MODAL -->
<div id="details_modal" class="modal fade " role="dialog">
    <div class="modal-dialog" style="width:70%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Seed Tag Details</h4>
            </div>
            <div class="modal-body">
                
            </div>
        </div>
    </div>
</div>
<!-- IAR UPLOAD MODAL -->



@endsection
@push('scripts')
<script>

    $('#moni_muni').on('change', function () {
        let muni = $('#moni_muni').val();

        var myData = {muni: muni, _token: "{{ csrf_token() }}"};
//            HoldOn.open(holdon_options);
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


    $('#moni_prov').on('change', function () {
        let province = $('#moni_prov').val()
        $('#moni_muni').empty()
        // Get municipalities
        $.ajax({
            type: 'GET',
            url: 'seed/tracking/get_muni/' + province,
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
    $(".select_class").select2();
</script>
@endpush
