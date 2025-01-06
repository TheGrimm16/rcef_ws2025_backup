@extends('layouts.index')

@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        .order-card {
            color: #fff;
        }
        
        .bg-c-blue {
            background: linear-gradient(45deg,#4099ff,#73b4ff);
            height: 18rem;
        }

        .bg-c-yellow {
            background: linear-gradient(45deg,#FFB64D,#ffcb80);
            height: 18rem;
        }
        .bg-c-green {
            background: linear-gradient(45deg,#2ed8b6,#59e0c5);
            height: 18rem;
        }
        .bg-c-pink {
            background: linear-gradient(45deg,#FF5370,#ff869a);
            height: 18rem;
        }

        .card {
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
            box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
            border: none;
            -webkit-transition: all 0.3s ease-in-out;
            transition: all 0.3s ease-in-out;
        }

        .card .card-block {
            padding: 20px;
        }

        .order-card i {
            font-size: 26px;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">




<div class="row">
  
  
            <div class="col-md-4">
                 
                    <div class="card bg-c-green order-card">
                        <div class="card-block">
                            <h2 class="m-b-20">Coop With Adjustment</h2>
                            <h1 class=""><i class="fa fa-users"></i><span>      {{$coop_edited}} SGC/A's</span></h1>
                            <p class="m-b-20"> <h5> Total Cooperatives:     {{$coop_count}} SGC/A's  </h5> </p>
                          
                        </div>
                    </div>
               
                    

            </div>

            {{-- <div class="col-md-4">

                <div class="card bg-c-pink order-card">
                    <div class="card-block">
                        <h2 class="m-b-20">Average Rejection Rate</h2>
                        <h1 class=""><i class="fa fa-thumbs-o-down"></i><span>      {{$rejection_rate}} %</span></h1>
                        <p class="m-b-20"> <h5> Total Bag Tested:  {{number_format($rejection_data->total_bags)}} bag(s)  </h5> </p>
                      
                    </div>
                </div>
                 
            </div> --}}
          
            {{-- <div class="col-md-4">

                <div class="card bg-c-yellow order-card">
                    <div class="card-block">
                        <h2 id="synced_date">Synced Date : {{date("F j, Y (g:i a)", strtotime($synced_data))}}</h2>
                        <button class="btn btn-success btn-sm " onclick="sync_rla();">
                            <i class="fa fa-refresh" aria-hidden="true" > Sync</i> 
                        </button> <br>
               
                        <button class="btn btn-dark btn-sm " disabled>
                            <i class="fa fa-file-excel-o" aria-hidden="true" > Export Results</i> 
                        </button>
               
                      
                    </div>
                </div>

             
            </div> --}}
          
    

</div>


<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Coop's Commitment Logs</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="col-md-4">
                    <label for="coop_name">Cooperative</label>
                    <select class="form-control form-select" name="coop_name" id="coop_name" >
                        <option value="0">Select Cooperative</option>
                        @foreach($coop_name as $coop_name)
                            <option value="{{$coop_name->coopId}}" >{{$coop_name->coopName}}</option>

                        @endforeach

                    </select>
                </div>

                <div class="col-md-4">
                    <label for="logs_date">Available Dates</label>
                    <select class="form-control form-select" name="logs_date" id="logs_date" onchange="genTable();">
                        <option value="0">Select Available Dates</option>
                    </select>
                </div>
              
                <div class="col-md-12"> 
                    <br>
                    <table class="table table-striped table-bordered" id="logs">
                        <thead>
                            <th>Cooperative</th>
                            <th>Region</th>
                            <th>Category</th>
                            <th>Seed Variety</th>
                            <th>Volume</th>
                            <th>User Updated</th>
                        </thead>
                    </table>
                </div>



            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
            $("#logs").DataTable();
            function genTable(){
                var coop_id = $("#coop_name").val();
                var logs_date = $("#logs_date").val();

                $('#logs').DataTable().clear();
                $("#logs").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{route('adjustment_logs.gen_table')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            coop_id: coop_id,
                            logs_date: logs_date
                        }
                    },
                    "columns":[
                        {"data": "coop_name"},
                        {"data": "region"},
                        {"data": "category"},
                        {"data": "seed_variety"},
                        {"data": "volume"},
                        {"data": "user_updated"},
                        
                    ]
                });
            }










	  $('select[name="coop_name"]').on('change', function () {
            HoldOn.open(holdon_options);
            var coop_name = $(this).val();

        $('select[name="logs_date"]').empty();
        $('select[name="logs_date"]').append('<option value=0>Select Available Dates</option>');
    

            $.ajax({
                method: 'POST',
                url: "{{route('adjustment.logs.dates')}}",
                data: {
                    _token: _token,
                    coop_id: coop_name,
                },
                dataType: 'json',
                success: function (source) {
                 
                    $('select[name="logs_date"]').empty();
                    $('select[name="logs_date"]').append('<option value=0>Select Available Dates</option>');
                        $.each(source, function (i, d) {
                            $('select[name="logs_date"]').append('<option value="' + d.date_created + '">' + d.date_name + '</option>');
                    }); 
                }
            }); //AJAX GET MUNCIPALITY
            HoldOn.close();

            genTable();
        });  
</script>
@endpush()
