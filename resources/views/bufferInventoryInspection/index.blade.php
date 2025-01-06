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
                <select name="region" id="region" class="form-control">
                    <option value="0">Please select a Region</option>
                    @foreach ($regions as $row)
                        <option value="{{$row->region}}">{{$row->region}}</option>    
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <select name="provincebuffer" id="provincebuffer" class="form-control">
                    <option value="0">Please select a Province</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="Municipalitybuffer" id="Municipalitybuffer" class="form-control">
                    <option value="2" selected>Please select a Municipality</option>                    
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-success btn-block" id="filter_btn"><i class="fa fa-database"></i> FILTER TABLE</button>
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
                        <th>Batch Ticket Number (ORIGIN):</th>
						<th>Batch Ticket Number (REPLACEMENT):</th>
                        <th>Drop Of Point</th>   
                        <th>Total Bag</th>     
                        <th>Delivery Date</th>                        
                        <th>Action</th>
                    </thead>
                </table>
            </div>
            </div>
            
        </div><br>






@endsection()

@push('scripts')
   

    <script>
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
					{"data": "batchTicket"},  					
                    {"data":"DropOfPoint"}, 
                    {"data":"TotalBag"},
                    {"data":"DeliveryDate"}, 
                    {"data": "action", searchable: false}
                ]
            });
            }
            
          
           
        }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
      $('select[name="region"]').on('change', function () {
            HoldOn.open(holdon_options);
            var provCode = $(this).val();
            var _token = $('#_token').val();
            $('select[name="provincebuffer"]').empty();
            $('select[name="Municipalitybuffer"]').empty();
            //$('input[name="region"]').empty();
         
            $.ajax({
                method: 'POST',
                url: 'provinceBufferData',
                data: {                    
                    provCode: provCode
                },
                dataType: 'json',
                success: function (source) {
                  
                    $('select[name="provincebuffer"]').append('<option>--SELECT ASSIGNED PROVINCE--</option>');
                    source.forEach(element => {
                        console.log(element.province);
                        $('select[name="provincebuffer"]').append('<option value="' + element.province + '">' + element.province + '</option>');
                    });
                  
                }
            });

            $.ajax({
                method: 'POST',
                url: 'MunicipalitybufferData',
                data: {                    
                    provCode: provCode
                },
                dataType: 'json',
                success: function (source) {
                  
                    $('select[name="Municipalitybuffer"]').append('<option>--SELECT ASSIGNED MUNICIPALITY--</option>');
                    source.forEach(element => {
                        console.log(element.province);
                        $('select[name="Municipalitybuffer"]').append('<option value="' + element.municipality + '">' + element.municipality + '</option>');
                    });
                  
                }
            });
        
            
        
            HoldOn.close();
        });
		
		$('select[name="provincebuffer"]').on('change', function () {
            HoldOn.open(holdon_options);
            var provCode = $(this).val();
            var _token = $('#_token').val();            
            $('select[name="Municipalitybuffer"]').empty();
            
            $.ajax({
                method: 'POST',
                url: 'MunicipalitybufferData',
                data: {                    
                    provCode: provCode
                },
                dataType: 'json',
                success: function (source) {
                  
                    $('select[name="Municipalitybuffer"]').append('<option>--SELECT ASSIGNED MUNICIPALITY--</option>');
                    source.forEach(element => {
                        console.log(element.province);
                        $('select[name="Municipalitybuffer"]').append('<option value="' + element.municipality + '">' + element.municipality + '</option>');
                    });
                  
                }
            });
        
            
        
            HoldOn.close();
        });
		
	

        $( "#filter_btn" ).click(function() {
        var region = $('#region').val();
        var provincebuffer = $('#provincebuffer').val();
        var Municipalitybuffer = $('#Municipalitybuffer').val();
        load_tbl(region, provincebuffer, Municipalitybuffer);
        /*     $.ajax({
                method: 'POST',
                url: 'bufferInventoryInspectionResult',
                data: {                    
                    region:region,
                    provincebuffer:provincebuffer,
                    Municipalitybuffer:Municipalitybuffer
                },
                dataType: 'json',
                success: function (source) {
                   
                }
            }); */
        });
        function downloadFile(response) {
          var blob = new Blob([response], {type: 'application/pdf'})
          var url = URL.createObjectURL(blob);
          location.assign(url);
        } 
        $('body').on('click', '.filter_btn_pdf_btn', function() {
            var id = $(this).attr("data-id")
        var region = $('#region').val();
        var provincebuffer = $('#provincebuffer').val();
        var Municipalitybuffer = $('#Municipalitybuffer').val();
           
        window.open('bufferInventory/'+region+'/'+provincebuffer+'/'+Municipalitybuffer+'/'+id).focus();
        });
       /*  $(".filter_btn_pdf_btn").click(function() {
            alert("joe");
        var id = $(this).attr("data-id")
        var region = $('#region').val();
        var provincebuffer = $('#provincebuffer').val();
        var Municipalitybuffer = $('#Municipalitybuffer').val();
           
        window.open('bufferInventory/'+region+'/'+provincebuffer+'/'+Municipalitybuffer+'/'+id).focus();
         
        
        }); */
    </script>
@endpush
