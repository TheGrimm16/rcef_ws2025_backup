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

            <div class="col-md-12 col-sm-12 col-xs-12">
                @include('layouts.message')

                <button class="btn btn-primary" id="archive_btn">Archive Database</button>
                
                <!-- delivery details -->
                <div class="x_panel">

                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <table class="table table-hover table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Provincial Databases (PRV DATA)</strong></td>
                                    <td><center>Pending</center></td>
                                </tr>
                                <tr>
                                    <td><strong>Delivery and Inspection Data (RCEP_DELIVERY_INSPECTION)</strong></td>
                                    <td><center>Pending</center></td>
                                </tr>
                                <tr>
                                    <td><strong>Seed Cooprative Data (RCEP_SEED_COOPERATIVES)</strong></td>
                                    <td><center>Pending</center></td>
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
        $("#seasons_tbl").DataTable();

        $("#archive_btn").on("click", function(e){
            $("#archive_btn").empty().html("Archiving has started please wait...");
            $.ajax({
                type: 'POST',
                url: "{{ route('system.settings.archive.update') }}",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(data){
                    $("#archive_btn").empty().html("Archive Database");
                }
            });
        });
    </script>
@endpush
