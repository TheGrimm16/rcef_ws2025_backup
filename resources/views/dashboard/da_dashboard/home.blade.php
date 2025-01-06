@extends('layouts.index')

@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        .provinceList.hover{
            background-color: #5cb85c;
        }


    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Seed Beneficiary Report <strong></strong></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
                    @foreach ($regions as $region)
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                        {{$region->regDesc}} - ({{$regionName[$region->regCode]}})
                                    </button>
                                    <i class="fa fa-plus pull-right" id="icon_id_{{$region->id}}" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse{{$region->id}}" aria-controls="{{$region->id}}" onclick="getProvinceList({{$region->regCode}}, {{$region->id}})"></i>
                                </h5>
                            </div>
                            <div id="collapse{{$region->id}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                <div class="card-body">
                                    <ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="list_{{$region->id}}">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COOP TRANSFER MODAL -->
<div id="show_municipality_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 700px; margin: auto; position: relative; top: 4%; left: 1%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="coop_name_trans_modal">
                     <h4 class="modal-title">
                    <span>Download Seed Beneficiary List</span><br>
                    </h4>
                
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-2">Province:</label>
                <label id="modal_province"></label> <br>


                <table class="table table-striped table-bordered" id="municipality_list_tbl" style="width: 100%;">
                    <thead> 
                        <tr>
                            <th style="">Region</th>  
                            <th style="">Province</th>  
                            <th style="">Municipality</th>                 
                            <th style="">Action</th>
                        </tr>
                    </thead>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- COOP TRANSFER MODAL -->

@endsection

@push('scripts')
<script>
    function getProvinceList(region_id,list_id){
        //alert(region_id);
        $("#icon_id_"+list_id).toggleClass('fa-plus fa-minus');

        $("#list_"+list_id).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
        $.ajax({
            type: 'POST',
            url: "{{ route('da.dashboard.province.list') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region_id: region_id
            },
            success: function(data){
                $("#list_"+list_id).empty();

                     $("#list_"+list_id).append("<div class='x_content'> <div class='accordion'>");
                var count = 0;
                jQuery.each(data, function(index, array_value){

                          $("#list_"+list_id).append("<div class='card'><div class='card-body' id='provinceList' ><h5 class='mb-0' style='margin:0'> <button style='color: #7387a8;text-decoration:none;' class='btn btn-link'  onclick='showMunicipality(this.value)' value='"+array_value['provDesc']+"'>"+array_value['provDesc']+"</button></h5> <button class='btn btn-warning btn-sm' style='top: 10%;margin-right: 10px;position: absolute;right: 0%;'  onclick='showMunicipality(this.value)' value='"+array_value['provDesc']+"'><i class='fa fa-eye'></i> View Municipality</button></div></div>");
            

                });
            
                  $("#list_"+list_id).append("</div></div>");


            }
        });
    }





    function showMunicipality(province){
        //alert(province);
        $("#modal_province").empty().html(province);

        $('#show_municipality_modal').modal("show"); 
    }











    $("#municipality_list_tbl").DataTable();

    $('#show_municipality_modal').on('show.bs.modal', function (e) {
        var province = $("#modal_province").text();
        //alert(province);


        //get batch details of selected coop
        $('#municipality_list_tbl').DataTable().clear();
        $("#municipality_list_tbl").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('da.dashboard.municipality.list') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "province": province
                }
            },
            "columns":[
                {"data": "region"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "action"}
                
            ]
        }); 






/*
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.coop.name') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_id: coop_id
            },
            success: function(data){
                $("#coop_name_modal").empty().html(data);
            }
        });
        $("#committed_volume_modal").empty().html(total_commit+" bag(s)");
        $("#confirmed_volume_modal").empty().html(total_confirmed+" bag(s)");
        $("#inspected_volume_modal").empty().html(total_inspected+" bag(s)");
    

        //get batch details of selected coop
        $('#coop_batch_table').DataTable().clear();
        $("#coop_batch_table").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('delivery_dashboard.batch.list') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "coop_accre": coop_accre
                }
            },
            "columns":[
                {"data": "batchTicketNumber"},
                {"data": "seedVariety"},
                {"data": "deliveryDate"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "dropOffPoint"},
                {"data": "confirmed"},
                {"data": "inspected"},
                {"data": "batch_status"},
                {"data": "action"}
            ]
        }); 

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.batch.list') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accre: coop_accre
            },
            success: function(data){
               // $("#coop_name_modal").empty().html(data);
            }
        });*/
		
		
		
		
		
    });
</script>
@endpush()
