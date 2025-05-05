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
    
    .form {
    --input-focus: #2d8cf0;
    --font-color: #323232;
    --font-color-sub: #666;
    --bg-color: #fff;
    --main-color: #323232;
    padding: 20px;
    background: lightgrey;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 20px;
    border-radius: 5px;
    border: 2px solid var(--main-color);
    box-shadow: 4px 4px var(--main-color);
    width: max-content;
    }
    
    .input {
    width: 100%;
    height: 40px;
    border-radius: 5px;
    border: 2px solid var(--main-color);
    background-color: var(--bg-color);
    box-shadow: 4px 4px var(--main-color);
    font-size: 15px;
    font-weight: 600;
    color: var(--font-color);
    padding: 5px 10px;
    outline: none;
    }

    .input::placeholder {
    color: var(--font-color-sub);
    opacity: 0.8;
    }

    .input:focus {
    border: 2px solid var(--input-focus);
    }

    .button-confirm {
    margin: 50px auto 0 auto;
    width: 120px;
    height: 40px;
    border-radius: 5px;
    border: 2px solid var(--main-color);
    background-color: var(--bg-color);
    box-shadow: 4px 4px var(--main-color);
    font-size: 17px;
    font-weight: 600;
    color: var(--font-color);
    cursor: pointer;
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
                    <h1>Remove Pre-Registered Farmer</h1>
                    <div class="clearfix"></div>
                </div>

                <div id="farmerForm">
                    <form class="form">
                        <div class="dropDown">
                                <select name="provMuni" id="provMuni">
                                    <option value="default">Select Province and Municipality</option>
                                    @foreach($provMuni as $muni)
                                    <option value="{{$muni['claiming_prv']}}">{{$muni['province']}} - {{$muni['municipality']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        <input type="text" placeholder="RSBSA Control No." id="rsbsaNo" class="input">
                        <input type="text" placeholder="First Name" id="firstName" class="input" required>
                        <input type="text" placeholder="Middle Name" id="middleName" class="input">
                        <input type="text" placeholder="Last Name" id="lastName" class="input" required>
                        <input type="text" placeholder="Extension" id="extName" class="input">
                        <button class="button-confirm" id="submit">Submit</button>
                    </form>
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

        $("#provMuni").select2();

        $("#submit").click(function (event) {
            provMuni = $("#provMuni").val();
            rsbsaNo = $("#rsbsaNo").val();
            firstName = $("#firstName").val();
            middleName = $("#middleName").val();
            lastName = $("#lastName").val();
            extName = $("#extName").val();
        
            if(provMuni == "default")
            {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select a province and municipality!',
                })
                return false;
            }

            if (!firstName.trim() || !lastName.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'First and Last Names are required!',
                });
                return false;
            }


            
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to delete this farmer's pre-registration?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({ 
                        type: 'POST',
                        url: "{{ route('deletePreRegProfile') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            provMuni: provMuni,
                            rsbsaNo: rsbsaNo,
                            firstName: firstName,
                            middleName: middleName,
                            lastName: lastName,
                            extName: extName
                        },
                        success: function(data){
                            if (data == 0)
                            {
                                Swal.fire({
                                    icon:'success',
                                    title: 'Success!',
                                    text: 'Successfully deleted farmer pre-registration!',
                                    confirmButtonText: 'Close',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.reload();
                                    }
                                });
                            }
                            else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data,
                                });
                            }
                        }
                    });
                } else {
                    console.log("Deletion cancelled by user.");
                }
            });

            
        });
   
    </script>
@endpush
