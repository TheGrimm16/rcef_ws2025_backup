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
                <h2>Stocks Monitoring</h2>
                 
                

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
							@foreach ($provinces_list as $row)
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
            <h2>
                Stocks Monitoring
            </h2>
            
            <!--
            <button class="btn btn-success btn-sm" style="float:right;" id="export_transfer_excel">
                Export to Excel
            </button> -->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th>Region</th>
                    <th>Province</th>
                    <th>Municipality</th>
                    <th>Drop Off Point</th>
                    <th>Seed Variety</th>
                    <th>Seed Tag</th>
                    <th>Inventory Information</th>
                    
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
        var province = $("#province_fg").val();
        $('select[name="municipality_fg"]').empty();
         $('select[name="dropOffPoint"]').empty();
          $('select[name="dropOffPoint"]').append('<option value="0">Please select a Drop off</option>');

         
            $.ajax({
                method: 'POST',
                url: "{{route('stocks.monitoring.location')}}",
                data: {
                    _token: _token,
                    province: province,
                    location: 'municipality'
                },
                dataType: 'json',
                success: function (source) {
                     $('select[name="municipality_fg"]').append('<option value="0">Please select a Municipality</option>');


                $.each(source, function (i, d) {
                    if(i == 0){
                         $('select[name="municipality_fg"]').empty();
                          $('select[name="municipality_fg"]').append('<option value="0">--ALL MUNICIPALITY--</option>');
                    }
                    $('select[name="municipality_fg"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                });
                     genTable(); 
                }
            }); //AJAX GET MUNICIPALITY  
        HoldOn.close();
    });  //END PROVINCE SELECT


    $('select[name="municipality_fg"]').on('change', function () {
            HoldOn.open(holdon_options);
        var municipality = $("#municipality_fg").val();
         $('select[name="dropOffPoint"]').empty();
         
            $.ajax({
                method: 'POST',
                url: "{{route('stocks.monitoring.location')}}",
                data: {
                    _token: _token,
                    municipality: municipality,
                    location: 'dropOffPoint'
                },
                dataType: 'json',
                success: function (source) {
                     $('select[name="dropOffPoint"]').append('<option value="0">Please select a Drop off</option>');

                $.each(source, function (i, d) {
                    if(i == 0){
                         $('select[name="dropOffPoint"]').empty();
                          $('select[name="dropOffPoint"]').append('<option value="0">--ALL Drop Off Point--</option>');
                    }
                    $('select[name="dropOffPoint"]').append('<option value="' + d.dropOffPoint + '">' + d.dropOffPoint + '</option>');
                });

                     genTable(); 
                }
            }); //AJAX GET MUNICIPALITY  
        HoldOn.close();
    });  //END PROVINCE SELECT

    $('select[name="dropOffPoint"]').on('change', function () {
        HoldOn.open(holdon_options);
            genTable(); 
        HoldOn.close();
    });  //END PROVINCE SELECT



    function genTable(){
                
            var province = $("#province_fg").val();
            var municipality = $("#municipality_fg").val();
            var dropOffPoint = $("#dropOffPoint").val();



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
                        "url": "{{route('stocks.monitoring.genTable')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            province: province,
                            municipality: municipality,
                            dropOffPoint: dropOffPoint,   
                        }
                    },
                    "columns":[
                        {"data": "batchTicketNumber"},
                        {"data": "province" },
                        {"data": "municipality"},
                        {"data": "dropOffPoint"},
                        {"data": "seed_variety"},
                        {"data": "seed_tag"},
                        {"data": "bags"}
                    ]
                });
    }





    </script>

@endpush