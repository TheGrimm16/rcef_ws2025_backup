<?php $qr_side = "active"; $qr_home="active"?>

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
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>

        <div class="clearfix"></div>

            @include('layouts.message')

            <div class="col-md-12 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>Select Location (Provincial Report)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>

                        <div class="row">
                            <div class="col-md-3">
                                <select name="region" id="region" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Region</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->regCode }}">{{ $region->regDesc }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-block btn-success" id="summary_btn"><i class="fa fa-bar-chart"></i> Generate Summary</button>
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2 id="province_name">Region Summary</h2> 
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="registered_farmers">
                            <thead>
                                <th width="30%">Province</th>
                                <th>Farmer Beneficiaries</th>
                                <th>Estimated area planted (ha)</th>
                            </thead>
                            <tbody id="table_body">
                                <tr>
                                    <td id="dropoff_name">N/A</td>
                                    <td id="total_farmer_count">N/A</td>
                                    <td id="total_farm_area">N/A</td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

        $("#summary_btn").on("click", function(e){
            var province = $("#province").val();
            var region = $("#region").val();

            $("#province_name").html("Loading data please wait... <div class='fa fa-spinner fa-spin'></div>");
            
            $.ajax({
                type: 'POST',
                url: "{{ route('rcef.report.beneficiaries.result3') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    region: region
                },
                success: function(data){
                    //alert(data);
                    if(data != 'rsbsa_error'){
                        $("#table_body").empty().append(data);
                        $("#province_name").html($("#region option:selected").text());
                    }else{
                        $("#table_body").empty().append("<tr><td>N/A</td><td>N/A</td><td>N/A</td></tr>");
                        $("#province_name").html("Please specify a location");
                        alert("the RSBSA List for the selected area does not exist...");
                    }                    
                }
            });
        });
    </script>
@endpush
