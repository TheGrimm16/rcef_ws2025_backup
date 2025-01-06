@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
      /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    /* Firefox */
    input[type=number] {
    -moz-appearance: textfield;
    }
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
    .x_content {
        padding: 0 5px 6px;
        float: left;
        clear: both;
        margin-top: 0;
    }
    .custom-header-span{
        font-size: 15px;
        font-family: inherit;
    }
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="clearfix"></div>
        <div class="col-md-7 col-sm-7 col-xs-7">
            @include('layouts.message')
            
            <div class="x_panel">
                <div class="x_title">
                    <h2>{{$coop_name}}</h2>               
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="bs-example" data-example-id="simple-jumbotron">     
                        <div class="jumbotron" style="padding-top: 20px;padding-bottom: 20px;">
                            <h3 style="font-size: 41px;font-weight: 600;">{{ number_format($total_commitment) }} | 20kg/bag</span></h3>
                            <p>
                                @foreach ($commitment_breakdown as $item)
                                    {{strtoupper($item->commitment_variety)}} - {{number_format($item->commitment_value)}} bag(s) <br>
                                @endforeach
                            </p>
                        </div><hr>

                        <label for="region">Region *</label>
                        <select name="region" id="region" class="form-control">
                            <option value="0">Please select a region</option>
                            @foreach ($regions as $row)
                                <option value="{{$row->region}}">{{$row->region}}</option>
                            @endforeach
                        </select><br>

                        <div class="row">
                            <div class="col-md-7">
                                <label for="seed_variety">Seed Variety *</label>
                                <select name="seed_variety" id="seed_variety" class="form-control">
                                    <option value="0">Please select a seed variety</option>
                                    @foreach ($commitment_breakdown as $item)
                                        <option value="{{strtoupper($item->commitment_variety)}}">{{strtoupper($item->commitment_variety)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="seed_volume">Volume (bags) - <span id="current_seeds">N/A available</span></label>
                                <input type="number" class="form-control" value="0" min="1" max="20000" name="seed_volume" id="seed_volume"><br>
                            </div>
                        </div>
                        <br>

                        <button id="add_commit_to_region_btn" class="btn btn-success"><i class="fa fa-plus-circle"></i> ADD COMMITMENT TO SELECTED REGION</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 col-sm-5 col-xs-5">
            <div class="x_panel">
                <div class="x_title">
                    <span id="2box_header"><h2>CURRENT ALLOCATION</h2></span>
                    <span class="pull-right"><i id="2box_search_btn" class="fa fa-search" style="font-size: 25px;cursor: pointer;position: absolute;top:2%;right:5%"></i></span>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="max-height:600px;overflow:auto;" id="allocation_list_div">
                    @foreach ($current_allocations as $card_row)
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                        <span style="float:left;">{{$card_row->seed_variety}} - {{$card_row->volume}} bag(s)</span> <br> <span style="float:left">{{$card_row->station_name}} / {{$card_row->region_name}}</span>
                                    </button><br>
                                    <a href="{{route('coop.regional_commitment.delete', $card_row->id)}}" style="margin-left: 10px;margin-top: -8px;margin-bottom: 10px;" class="btn btn-danger btn-sm"><i class="fa fa-times-circle"></i> REMOVE ALLOCATION</a>
                                </h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection()

@push('scripts')
    <script>
        $("#add_commit_to_region_btn").on("click", function(e){
            var region = $("#region").val();
            var seed_variety = $("#seed_variety").val();
            var seed_volume = $("#seed_volume").val();

            if(region != "0" && seed_variety != "0" && seed_volume != 0){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.regional_commitment.save') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coopID: "{{ $coopID }}",
                        region: region,
                        seed_variety: seed_variety,
                        seed_volume: seed_volume
                    },
                    success: function(data){
                        if(data == "Allocation Saved!"){
                            alert("You have successfully allocated the seeds for the selected region");
                            location.reload();
                        }else if(data == "Allocation Exceeds"){
                            alert("The volume of seeds to be allocated now exceeds the commitment specified by the seed cooperative, please enter a different amount and try again.")
                        }else{
                            alert("The system encountered an unexpected error while processing your request, please try again.");
                        }
                    }
                });
            }else{
                alert("Please select a region & seed variety");
            }
        });

        $("#seed_variety").on("change", function(e){
            //get current available seeds
            var seed_variety = $("#seed_variety").val();
            $("#current_seeds").empty().append("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.regional_commitment.check_seeds') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: "{{ $coopID }}",
                    seed_variety: seed_variety,
                },
                success: function(data){
                    $("#current_seeds").empty().append(data + " bag(s)");
                }
            });
        });

        $("#2box_search_btn").on("click", function(e){
            $("#2box_header").empty().append("<input onkeyup='search_keyword()' type='text' class='form-control' name='2box_search_keyword' id='2box_search_keyword' style='width:90%;border-radius: 30px;' placeholder='Please enter a keyword...'>");
        });


        function search_keyword(){
            var keyboard = $("#2box_search_keyword").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('coop.regional_commitment.search_allocations') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coopID: "{{ $coopID }}",
                    keyboard: keyboard,
                },
                success: function(data){
                    $("#allocation_list_div").empty();
                    var alocation_str = "";
                
                    jQuery.each(data, function(index, item) {
                        let url = "{{ route('coop.regional_commitment.delete', ['regional_commitment_ID' => ':id']) }}".replace(':id', item['allocation_id']);
                        alocation_str = alocation_str + "<div class='card'>";
                        alocation_str = alocation_str + "<div class='card-header' id='headingOne'>";
                        alocation_str = alocation_str + "<h5 class='mb-0' style='margin:0'>";
                        alocation_str = alocation_str + "<button style='color: #7387a8;text-decoration:none;'' class='btn btn-link'>";    
                        alocation_str = alocation_str + "<span style='float:left;'>"+item['seed_variety']+" - "+item['volume']+" bag(s)</span> <br> <span style='float:left'>"+item['station']+" / "+item['region']+"</span>";
                        alocation_str = alocation_str + "</button><br>";
                        alocation_str = alocation_str + "<a href='"+url+"' style='margin-left: 10px;margin-top: -8px;margin-bottom: 10px;' class='btn btn-danger btn-sm'><i class='fa fa-times-circle'></i> REMOVE ALLOCATION</a>";
                        alocation_str = alocation_str + "</h5>";
                        alocation_str = alocation_str + "</div>";
                        alocation_str = alocation_str + "</div>";
                    });
                    $("#allocation_list_div").append(alocation_str);
                }
            });
        }
    </script>
@endpush
