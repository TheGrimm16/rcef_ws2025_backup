@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
	
	<span style="font-size: 20px;font-weight: 700;font-style: italic;"><u>Blank IAR for Seed Inspectors</u></span>

    <div>
        <div class="row" style="margin-top: 10%;">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Select Delivery Location</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div>
                        <div class="form-horizontal form-label-left">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9">
                                    <select class="form-control" name="distribution_province_new" id="distribution_province_new">
                                        <option></option>
                                        @foreach($provinces_list as $item)
                                            <option value="{{$item->province}}">{{$item->province}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9">
                                    <select class="form-control" name="distribution_municipality_new" id="distribution_municipality_new">
                                        <option></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Dropoff Point</label>
                                <div class="col-md-9">
                                    <select class="form-control" name="dropoff_point_new" id="dropoff_point_new">
                                        <option></option>
                                    </select>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-md-12">
                                    <button type="button" name="button" id="submit_iar_location_new" class="btn btn-lg btn-primary" style="float: right;"><i class="fa fa-sign-in"></i> Continue</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
    @include('DeliveryDashboard.includes.iarlist')


@endsection()

@push('scripts')
    <script>
        $("#distribution_province_new").on("change", function(e){
            var province = $(this).val();
            $("#distribution_municipality_new").empty().append("<option value='0'>Loading municipalities...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('iar.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#distribution_municipality_new").empty().append("<option value='0'></option>");
                    $("#distribution_municipality_new").append(data);
                }
            });
        });

        $("#distribution_municipality_new").on("change", function(e){
            var province = $("#distribution_province_new").val();
            var municipality = $("#distribution_municipality_new").val();
            
            $("#dropoff_point_new").empty().append("<option value='0'>Loading dropoff points...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('iar.dropoff') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality
                },
                success: function(data){
                    $("#dropoff_point_new").empty().append("<option value='0'></option>");
                    $("#dropoff_point_new").append(data);
                }
            });
        });

        $('#submit_iar_location_new').on('click', function () {
            let province = $('#distribution_province_new').val()
            let municipality = $('#distribution_municipality_new').val()
            let dropoff_point = $('#dropoff_point_new').val()
            HoldOn.open(holdon_options)

            $('#iar_table').DataTable({
                columns: [
                    {data: 'batchno', name: 'batchno'},
                    {data: 'date', name: 'date'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'iar_list',
                    method: 'POST',
                    datatype: "json",
                    data: {
                        _token: _token,
                        province: province,
                        municipality: municipality,
                        dropoff_point: dropoff_point,
                    }
                },

                initComplete: function (settings, json) {
                    $('#iar_modal').modal('toggle')
                    HoldOn.close()
                }
            })
        });
    </script>
@endpush