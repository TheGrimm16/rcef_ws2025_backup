@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
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
    input[type=number]::-webkit-inner-spin-button {
        opacity: 1
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Please fill-up all the required fields on the form.
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <form enctype="multipart/form-data" method="post" action="{{route('coop.rla.confirm_edit')}}">
                {{ csrf_field() }}
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" id="rla_id" name="rla_id" value="{{$rla_details->rlaId}}">
                        <input type="hidden" id="rla_old_seedtag" name="rla_old_seedtag" value="{{$rla_details->labNo}}/{{$rla_details->lotNo}}">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Cooperative:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                @if ($hasDelivery == 1)
                                    @foreach ($coop_list as $row)
                                        @if ($rla_details->coopAccreditation == $row->accreditation_no)
                                        <div style="height: 3em; display: flex; align-items: center;">{{$row->coopName}}</div>
                                        @endif
                                    @endforeach
                                @else                                    
                                    <select name="coop" id="coop" class="form-control" required>
                                        <option value="0">Please select a seed cooperative</option>
                                        @foreach ($coop_list as $row)
                                            @if ($rla_details->coopAccreditation == $row->accreditation_no)
                                                <option value="{{$row->accreditation_no}}" selected>{{$row->coopName}}</option>
                                            @else
                                                <option value="{{$row->accreditation_no}}">{{$row->coopName}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Grower:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                @if ($hasDelivery == 1)
                                    <div style="height: 3em; display: flex; align-items: center;">{{$rla_details->sg_name}}</div>
                                @else
                                    <select name="sg_name" id="sg_name" class="form-control" required>
                                        <option value="{{$rla_details->sg_name}}">{{$rla_details->sg_name}}</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Certification Date:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="date" name="certification_date" id="certification_date" class="form-control" value="{{$rla_details->certificationDate}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Lab Number:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" class="form-control" name="lab_number" id="lab_number" value="{{$rla_details->labNo}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Lot Number:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" class="form-control" name="lot_number" value="{{$rla_details->lotNo}}" id="lot_number" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Variety:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                @if ($hasDelivery == 1)
                                    <div style="height: 3em; display: flex; align-items: center;">{{$rla_details->seedVariety}}</div>
                                @else
                                    <select name="variety" id="variety" class="form-control" required>
                                        <option value="0">Please select a seed variety</option>
                                        @foreach ($variety_list as $row)
                                            @if ($rla_details->seedVariety == $row->variety)
                                                <option value="{{$row->variety}}" selected>{{$row->variety}}</option>
                                            @else
                                                <option value="{{$row->variety}}">{{$row->variety}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"># of Bags:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                @if ($hasDelivery == 1)
                                <div style="height: 3em; display: flex; align-items: center;">{{$rla_details->noOfBags}}</div>
                                @else
                                <input type="number" class="form-control" name="bags" id="bags" min="1" max="240" value="{{$rla_details->noOfBags}}" required>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"></label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input class="btn btn-warning pull-right" type="submit" value="PROCEED TO EDIT RLA">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div><br>        

    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#variety").select2();
        $("#sg_name").select2({
            "tags": true
        });
        $("#coop").select2();

        $("#coop").on("change", function(e){
            var coop = $(this).val();
            
            $("#sg_name").empty().append("<option value='0'>loading seed grower list...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('coop.rla.manual_sgList') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop: coop,
                },
                success: function (data) {
                    $("#sg_name").empty().append(data);
                }
            });
        });

        
    </script>
@endpush
