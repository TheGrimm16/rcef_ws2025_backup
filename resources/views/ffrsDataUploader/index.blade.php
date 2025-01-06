<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}"/>
    <link
    rel="stylesheet"
    href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}"/>
    <link href="public/css/HoldOn.min.css" rel="stylesheet" />
    <link
    rel="stylesheet"
    href="https://code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
    .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
    .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
    .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
    .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
    .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
    .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
    .shadow-none	{box-shadow: 0 0 #0000;}

    .x_panel{
        /* background: conic-gradient(from 35deg, #57d98b60, #35945b80); */
        background: #e0e0e0;
        color: black;
        position: absolute;
        border-radius: 2em;
        border: 2px solid #c3c6ce;
                -webkit-transition: 0.5s ease-out;
                transition: 0.5s ease-out;
                overflow: visible;
    }


    .x_title{
			margin: 10px;
            border: 1px #6b6b6b;
			box-sizing: border-box;
            background-color: transparent;
            border-radius: 10px;
            padding: 10px;
    }

    .x_title h1{
        font-weight: 900;
    }
    
    #containers{
			margin: 10px;
            border: 1px #6b6b6b;
			box-sizing: border-box;
            padding: 10px;
            border-radius: 10px;
            background-color: #faf5f580;
            backdrop-filter: blur(5px);
    }

    h4{
        font-weight: 700;
        margin: 0 0 0.4em 0;
        padding: 0;
    }

    hr {
        border: none;
        height: 1px;
        background-color: #6b6b6b;
        margin-top: 0.5px;
        margin-bottom: 10px;
    }

    select{
        border: 1px solid #888;
        border-radius: 0.6em;
        padding: 0.2em;
        font-size: 1.4em;
    }

    .selectors {
    display: inline-block;
    }

    .selector_cards {
        display: inline-block;
        margin-right: 10px;
        margin-left: 10px;
    }

    .submit{
        width: max-content;
        margin-left: 15px;
    }

    .group_selector{
        display: flex;
        gap: 20%;
    }

		.col-md-6 {
			width: 50%;
			float: left;
            margin-top: 10px;

		}

        .shadow {
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
        }

     
       
    </style>
@endsection

@section('content')
    
<div class="clearfix" id="page">
    
    @include('layouts.message')
        
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel shadow-2xl" style="padding-bottom: 3em;">
                <div class="x_title">
                    <h1>FFRS Data Uploader</h1>
                    <div class="clearfix"></div>
                </div>

                <div class="row">
                    <div class="col-auto pt-1 pl-5">
                        <h5>Import FFRS data</h5>
                    </div>
                    <div class="col-auto">
                    <form id="csvUploadForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" class="custom-file-input form-control" id="inputFolder" name="inputFile" required>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" required>
                            <input type="hidden" name="function" value="rla" required>
                            <span class="input-group-btn">
                                <button class="btn btn-outline-primary" type="submit" id="uploadFFRSdata">Upload</button>
                            </span>
                        </div>
                    </form>
                </div>
                </div>
        

            </div>
        </div>
    </div>
</div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src="public/js/HoldOn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
     $('#uploadFFRSdata').on('click', function(e) {
        e.preventDefault();

        if ($('#inputFolder').val() === '') {
        // return;
        Swal.fire({
                title: 'Please select a file first',
                showCancelButton: false,
                confirmButtonText: 'Okay',
                allowOutsideClick: false,
            });
        }
        else {
            var options = {
                theme: "sk-rect",
                message: 'Uploading data. Please wait.',
                backgroundColor: "#494f5f",
                textColor: "white"
            };
            HoldOn.open(options);
        
            var formData = new FormData($('#csvUploadForm')[0]);
    
            $.ajax({
                type: 'POST',
                url: "{{ route('ffrsDataUploader.upload') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    HoldOn.close();
                    Swal.fire({
                        title: 'File Uploaded Successfully',
                        showCancelButton: false,
                        confirmButtonText: 'Okay',
                        allowOutsideClick: false,
                    });
                    $('#csvUploadForm')[0].reset();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Unexpected Error Occurred',
                        showCancelButton: false,
                        confirmButtonText: 'Okay',
                        allowOutsideClick: false,
                    });
                    HoldOn.close();
                
                }
            });
        }


    });

    </script>
@endpush
