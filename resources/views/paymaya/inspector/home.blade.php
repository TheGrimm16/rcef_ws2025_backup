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
                    PayMaya Inspector's Interface
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <form enctype="multipart/form-data" method="post" action="{{route('coop.rla.bpi_save_request')}}">
                {{ csrf_field() }}
                    <div class="form-horizontal form-label-left">
                        
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Tag</label>
                            <div class="col-md-10 col-sm-10 col-xs-10" required>
                                <select name="sg_name" id="sg_name" class="form-control" required></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span> QR Code</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" class="form-control" name="qr_code_paymaya" id="qr_code_paymaya" placeholder="Please scan / type the QR Code">
                            </div>
                        </div>

                        <video id="preview_paymaya" style="display:none;"></video>
                        <audio id="qr_audio_paymaya">
                            <source src="{{asset('public/sounds/Beep.mp3')}}" type="audio/mpeg">
                        </audio>
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
        // QR Code scanner
        let scanner_paymaya = new Instascan.Scanner({video: document.getElementById('preview_paymaya')});
        let beep_paymaya = document.getElementById("qr_audio_paymaya");
        scanner_paymaya.addListener('scan', function (content) {
            console.log(content);
            beep_paymaya.play()
            $('#qr_code_paymaya').val(content) // farm performance modal
            $('#farmer_registration_form #distribution_id').val(content) // farmer registration form
            $('#add_farmer_modal #qr_code2').val(content) // add farmer modal
        });
        Instascan.Camera.getCameras().then(function (cameras) {
            if (cameras.length > 0) {
                scanner_paymaya.start(cameras[0]);
            } else {
                console.error('No cameras found.');
            }
        }).catch(function (e) {
            console.error(e);
        });
    </script>
@endpush
