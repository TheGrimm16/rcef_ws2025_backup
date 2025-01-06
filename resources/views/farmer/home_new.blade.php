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

        <form action="{{ route('farmer.qr.generate') }}" method="POST" id="registryForm" data-parsley-validate>
        {!! csrf_field() !!}
        <div class="clearfix"></div>

            @include('layouts.message')

            <div class="col-md-6 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>Generate ID</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Region:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="region" id="region" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Region</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->regCode }}">{{ $region->regDesc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Province:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="province" id="province" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a Province</option>
                                </select>
                            </div>
                        </div>

                        <div class="ln_solid"></div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"># of ID:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="number" class="form-control" name="QRLimit" id="QRLimit" value="100">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="submit" class="btn btn-block btn-success" value="Generate QR Codes">
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>

            <div class="col-md-6 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>View Area Profiles</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-striped table-bordered" id="registered_farmers">
                            <thead>
                                <th>Address</th>
                                <th>Count</th>
                            </thead>
                            <tbody>
                                @foreach ($areas as $area)
                                    <tr>
                                        <td>
                                            <b>Region:</b> {{ $area->regDesc }}<br>
                                            <b>Province:</b> {{ $area->provDesc }}
                                        </td>
                                        <td>
                                            {{ $area->currentCount }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                </div>
                </div><br>
                <!-- /delivery details -->
            </div>
        </form>



    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

        $("#registered_farmers").DataTable();

        $("#region").on("change", function(e){
            var region = $(this).val();
            $("#province").empty().append("<option value='0'>Loading provinces please wait...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.province') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    region: region
                },
                success: function(data){
                    $("#province").empty().append("<option value='0'>Please select a province</option>");
                    $("#province").append(data);
                }
            });
        });
    </script>
@endpush
