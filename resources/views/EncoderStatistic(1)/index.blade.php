@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1;
        }

        .tile_count .tile_stats_count .count {
            font-size: 30px;
        }
  </style>
@endsection

@section('content')

 <!-- UPLOAD PANEL -->
 <div class="x_panel">
    <div class="x_title">
        <h2>
            Search Filter
        </h2>
        <div class="clearfix"></div>
    </div>
    <div class="x_content form-horizontal form-label-left">
        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
        <div class="row">
            <div class="col-md-3">
                <select name="station" id="station" class="form-control">
                    <option value="0">Station</option>
                    @foreach ($stations as $row)
                        <option value="{{$row->stationId}}">{{$row->stationName}}</option>    
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="Encoder" id="Encoder" class="form-control">
                    <option value="0">Encoder</option>

                </select>
            </div>
            
            

            <div class="col-md-3">
                <button class="btn btn-success btn-block" id="filter_btn_statistic" name="filter_btn_statistic"><i class="fa fa-database"></i> FILTER TABLE</button>
            </div>
            
           {{--  <div class="col-md-3">
               
                    <button type="button" class="btn btn-success btn-block" id="filter_btn_pdf"><i class="fa fa-database"></i> Download PDF</button>
                               
            </div> --}}
        </div>
    </div>
</div><br>
<!-- UPLOAD PANEL -->

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><strong>RCEF Buffer & Inspection</strong></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
               
                <table class="table table-hover table-striped table-bordered" id="stocks_tbl">
                    <thead>
                        <th>Encoder</th>
                        <th>DS2020</th>   
                        <th>WS2020</th>     
                        <th>DS2021</th>                        
                        <th>WS2021</th>
                        <th>Total Encoded</th>
                    </thead>
                    <tbody id="tableBody">
                        
                    </tbody>
                </table>
            </div>
            </div>
            
        </div><br>






@endsection()

@push('scripts')
   

    <script>

        $('select[name="station"]').on('change', function () {
            HoldOn.open(holdon_options);
            var station = $(this).val();
            $('select[name="Encoder"]').empty();
            $.ajax({
                method: 'POST',
                url: 'encoderData',
                data: {                    
                    station: station
                },
                dataType: 'json',
                success: function (source) {
                  
                    $('select[name="Encoder"]').append('<option>--SELECT ASSIGNED ENCODER--</option>');
                    $('select[name="Encoder"]').append('<option value="ALL">ALL</option>');
                    source.forEach(element => {
                        console.log(element.users);
                        $('select[name="Encoder"]').append('<option value="' + element.username + '">' + element.username + '</option>');
                    });
                  
                }
            });
            HoldOn.close();
        });
        $("#filter_btn_statistic").on('click', function () {            
            HoldOn.open(holdon_options);
            var station = $('#station').val();
            var Encoder = $('#Encoder').val();

            $('#stocks_tbl').DataTable().clear();
            $("#stocks_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('StatisticDatLoad') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        station: station,
                        username:Encoder
                    }
                },
                "columns":[
                    {"data": "encoder"},                            
                    {"data":"ds2020"}, 
                    {"data":"ws2020"},
                    {"data":"ds2021"}, 
                    {"data":"ws2021"}, 
                    {"data":"totalData"}, 
                    
                ]
            });


            /* $.ajax({
                method: 'POST',
                url: 'StatisticDatLoad',
                data: {                    
                    station: station,
                    username:Encoder,
                },
                dataType: 'json',
                success: function (source) {
                  console.log(source.encoder);                
                 
                $('#tableBody').append("<tr><td>"+source.encoder+"</td><td>"+source.ds2020+"</td><td>"+source.ws2020+"</td><td>"+source.ds2021+"</td><td>"+source.ws2021+"</td><td>"+source.Total+"</td></tr>");
                $("#stocks_tbl").DataTable();
                }
            }); */
            HoldOn.close();
        });

        load_tbl('no_province', 'no_municipality', 'no_status');
         function load_tbl(region, provincebuffer, Municipalitybuffer){
            $('#stocks_tbl').DataTable().clear();
            if(region=="no_province" &&  provincebuffer =="no_municipality" && Municipalitybuffer =="no_status"){
                $("#stocks_tbl").DataTable();
            }else{
                $("#stocks_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('BufferIRDatatable') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        region:region,
                        provincebuffer:provincebuffer,
                        Municipalitybuffer:Municipalitybuffer
                    }
                },
                "columns":[
                    {"data": "origin"},                            
                    {"data":"DropOfPoint"}, 
                    {"data":"TotalBag"},
                    {"data":"DeliveryDate"}, 
                    
                ]
            });
            }
            
          
           
        }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    </script>
@endpush
