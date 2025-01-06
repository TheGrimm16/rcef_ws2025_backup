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
                <h2>Cancelled Batch List</h2>    

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
                    Pick Date Range
                </h2>
                    <div style="width: 200px;"> <br> <br>
                        <b> From: </b> <input type="text" name="date1" id="date1" class="form-control" value="{{date('m/d/Y', $filterFrom)}}" />
                        <b> To: </b> <input type="text" name="date2" id="date2" class="form-control" value="{{date('m/d/Y', $filterTo)}}" />
                        
                    </div>   

                    
                
                <div class="clearfix"></div>
        </div>
</div>
        <br>
        <!-- FILTER PANEL -->

<!-- DATA TABLE -->

 <div class="col-md-12 col-sm-12 col-xs-12">
    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
                CANCELLED BATCH LIST
            </h2> <br> <br>
			@if(Auth::user()->roles->first()->name == "system-admin")
             <a href="#" data-toggle="modal" data-target="#utilDel_modal" class="btn btn-warning btn-sm"> CANCEL DELIVERY </a>
             @endif
           

		   <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th>Batch Ticket Number</th>
                    <th>Coop Name</th>
                    <th>Volume</th>
                    <th>Drop off</th>
                    <th>Delivery Date</th>
                    <th>Date Cancelled</th>
                    <th>User</th>
                   
                </thead>
                <tbody id='databody'>
                            @foreach ($cancel_list as $row)
                                <tr>
                                    <td>{{$row->batch_ticket}}</td>
                                    <td>{{$row->coop}}</td>
                                    <td> {{$row->volume}}</td>
                                    <td>{{$row->point}}  </td>
                                    <td>{{$row->dateDeliver}} </td>
                                    <td>{{$row->dateRecorded}} </td>
                                    <td> {{$row->user}} </td>
                                  
                                </tr>
                            @endforeach
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>



@endsection
@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
   

    <script type="text/javascript">
        $("#date1").datepicker();
        $("#date2").datepicker();


        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });


$("#date1").on('change', function () {
    var d1 =  new Date(document.getElementById("date1").value);
    var d2 =  new Date(document.getElementById("date2").value);
    if(d1>d2){
        alert("Date Incorrect, Date To need to be higher than Date From");
        document.getElementById("date1").value =  document.getElementById("date2").value;
    }
        generateData();
    
});

$("#date2").on('change', function () {
    var d1 =  new Date(document.getElementById("date1").value);
    var d2 =  new Date(document.getElementById("date2").value);
    if(d1>d2){
        alert("Date Incorrect, Date To need to be higher than Date From");
        document.getElementById("date2").value =  document.getElementById("date1").value;
    }
        generateData();
    

});


function generateData(){
    HoldOn.open(holdon_options);
    d1 = document.getElementById("date1").value;
    d2 = document.getElementById("date2").value;
           var linkTable = "{{ route('generate.cancelled.list') }}";
            $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": true,
                "searchHighlight": true,
                "searching": false,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": linkTable,
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        datefrom: d1,
                        dateto: d2,
                    }
                },
                "columns":[
                    {"data": "batch_ticket", 'orderable': false},
                     {"data": "coop", 'searchable': true, 'orderable': false},
                    {"data": "volume", 'searchable': true, 'orderable': false},
                    {"data": "point", 'searchable': true, 'orderable': false},
                    {"data": "dateDeliver", 'searchable': true, 'orderable': false},
                    {"data": "dateRecorded", 'searchable': true, 'orderable': false},
                    {"data": "user", 'searchable': true, 'orderable': false},
                   
                ]
            });
    HoldOn.close();
}


function redoMe(id){
    var id = id;
    var linkProcess = "{{ route('process.cancelled.redo') }}";
         $.ajax({
        type: 'POST',
        url: linkProcess,
        data: {
             "_token": "{{ csrf_token() }}",
             id: id, 
                
        },
        dataType: 'json',
        success: function (source) {
            alert("BATCH RETRIEVE");
        }
        }); 


           alert("BATCH RETRIEVE");
         generateData(); //REFRESH


}





    </script>

@endpush