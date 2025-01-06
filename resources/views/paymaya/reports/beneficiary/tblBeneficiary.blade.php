@extends('layouts.index')


@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
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
                <h2>e-Binhi Beneficiary List</h2>
                 
                

                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
                    
                </div>
            </div>
        </div>
    </div>
</div>


  <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                   Filter
                </h2>

                <select name="province_fg" id="province_fg" class="form-control">
                            <option value="0">Please select a Province</option>
                    <!-- READY FOR DS2021 -->
							@foreach ($province as $row)
								<option value="{{$row->province}}">{{$row->province}}</option>
							@endforeach
                </select>
                        <br>
                <select name="municipality_fg" id="municipality_fg" class="form-control">
                            <option value="0">Please select a Municipality</option>
                </select>
                        <br>
                <select name="dropOffPoint" id="dropOffPoint" class="form-control">
                            <option value="0">Please select a Drop off</option>
                </select>


                         <div class="clearfix"></div>
        </div>
</div>
        <br>
        <!-- FILTER PANEL -->

<!-- DATA TABLE -->

 <div class="col-md-12 col-sm-12 col-xs-12">
<?php
//dd($userID->userId);
?>
    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
           
            
            
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
       
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th style="width: 150px;">Rsbsa</th>
                    <th style="width: 200px;">Name</th>
                    <th>Contact</th>
                    <th style="width: 100px;">Schedule Start</th>
                    <th style="width: 100px;">Schedule End</th>
                    <th style="width: 200px;">Pick Up Point</th>
                    <th >Brgy</th>
                    
                    <th>Gender</th>
                    <th>Area</th>
                    <th>Coop Name</th>
                    <th>Bags</th>
                    <th>Code</th>
                    <th>Action</th>
                    
                    
                </thead>
                <tbody id='databody'>
                    
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

 


@endsection
@push('scripts')

    <script type="text/javascript">
        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });


        $('select[name="province_fg"]').on('change', function () {
            HoldOn.open(holdon_options);
            var region = "%";
            var province = $(this).val();
      
        $('select[name="municipality_fg"]').empty();
        $('select[name="municipality_fg"]').append('<option value="0">--Please Select a Municipality--</option>');
          $('select[name="dropOffPoint"]').empty();
        $('select[name="dropOffPoint"]').append('<option value="0">--Please Select a DOP--</option>');

            $.ajax({
                method: 'POST',
                url: "{{route('FarGeneration.ebinhi.get_municipalities')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                },
                dataType: 'json',
                success: function (source) {
                    gentable();
                    $('select[name="municipality_fg"]').empty();
                    $('select[name="municipality_fg"]').append('<option value="0">--Please Select a Municipality--</option>');
                        $.each(source, function (i, d) {
                            $('select[name="municipality_fg"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                    }); 
                }
        }); //AJAX GET MUNCIPALITY
        HoldOn.close();
        });  //END PROVINCE SELECT



//FarGeneration.ebinhi.get_dop

    
        $('select[name="municipality_fg"]').on('change', function () {
            HoldOn.open(holdon_options);
            var region = "%";
            var province = $("#province_fg").val();
            var municipality = $(this).val();
      
        $('select[name="dropOffPoint"]').empty();
        $('select[name="dropOffPoint"]').append('<option value="0">--Please Select a DOP--</option>');

            $.ajax({
                method: 'POST',
                url: "{{route('FarGeneration.ebinhi.get_dop')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                    municipality: municipality
                },
                dataType: 'json',
                success: function (source) {
                    gentable();
                    $('select[name="dropOffPoint"]').empty();
                    $('select[name="dropOffPoint"]').append('<option value="0">--Please Select a DOP--</option>');
                        $.each(source, function (i, d) {
                            $('select[name="dropOffPoint"]').append('<option value="' + d.drop_off_point + '">' + d.drop_off_point + '</option>');
                    }); 
                }
        }); //AJAX GET MUNCIPALITY
        HoldOn.close();
        });  //END PROVINCE SELECT


        $('select[name="dropOffPoint"]').on('change', function () {
            HoldOn.open(holdon_options);
          gentable();
        HoldOn.close();
        });  //END PROVINCE SELECT

        function utility_change(code,item,type){
            $.ajax({
                method: 'POST',
                url: "{{route('paymaya.utility.update_area')}}",
                data: {
                    _token: _token,
                    code: code,
                    item: item,
                    type: type
                },
                dataType: 'json',
                success: function (source) {
                    alert(source);
                    gentable();
                    
                }
        }); //AJAX GET MUNCIPALITY
        }

       function gentable(){
        var province = $("#province_fg").val();
        var municipality = $("#municipality_fg").val();
        var dop = $("#dropOffPoint").val();

        if(municipality === "0"){
            municipality = "%";
        }

        if(dop === "0"){
            dop = "%";
        }



         $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "searching": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{route('paymaya.beneficiary.gentable')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        dop: dop,
                        
                    }
                },
                "columns":[
                    {"data": "rsbsa"},
                    {"data": "name"},
                    {"data": "contact"},
                    {"data": "sched_start"},
                    {"data": "sched_end"},
                    {"data": "dop"},
                    {"data": "brgy"},
                    
                    {"data": "gender"},
                    {"data": "area"},
                    {"data": "coop"},
                    {"data": "bags"},
                    {"data": "code"},
                    {"data": "action"},
                    
                ]
            });

       }



       function cancel_distri(code){

        var yesno = confirm("Cancel Distribution?");

                if(yesno){
                    $.ajax({
                        method: 'POST',
                        url: "{{route('paymaya.utility.cancel.delivery')}}",
                        data: {
                            _token: _token,
                            code: code,
                        },
                        dataType: 'json',
                        success: function (source) {
                            alert(source);
                            gentable();
                        }
                }); //AJAX GET MUNCIPALITY


                }

     
       }





    </script>

@endpush