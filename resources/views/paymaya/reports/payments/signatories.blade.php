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

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">

        

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    E-Binhi Payment Signatories
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                   
                  {{-- <div class="col-md-2">
                        <button class="btn btn-success" id="add_new"><i class="fa fa-plus"></i> Add new</button>
                    </div> --}}
                    {{-- <hr> --}}
                </div>
                <div class="x_content" id="container_div" >

                        <table class="table table-hover table-striped table-bordered" id="payment_tbl">
                            <thead>
                                <th>signatory id</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Action</th>
                              
                            </thead>
                        </table>
                        <div id = "dl1" style="display: none">
                            <a href="#" target="_blank" data-toggle='modal' data-target='#download_modal_dbp' class="btn btn-success btn-sm" ><i class="fa fa-download"></i> Download Payment Form</a>
                        </div>
                </div>
            </div>
        </div><br>

<!-- add MODAL -->
<div id="add_new_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Add New Signatories</span><br>  
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control signatory_id_n" name="" id="signatory_id">
                
                    {{-- <label for="">Select:</label>
                    <select name="signatory" id="signatory" style="width: 100%;" class="form-control">
                        <option value= "0">*Select Signatory Type</option>
                        <option value="1">Prepared by:</option>
                        <option value="2">Noted by:</option>
                        <option value="3">Approved by:</option>
                   
                    </select> --}}

                    <label class="">Full Name:</label>
                    <input type="text" class="form-control full_name" name="" id="full_name">

                    <label class="l">Designation:</label>
                    <input type="text" class="form-control designation" name="" id="designation">

                    <br>
                <button class="btn btn-success" name="" id="btn_update"> <i class="fa fa-pencil" ></i> Update</button>

            </div>
        </div>
    </div>
</div>
<!-- DOWNLOAD MODAL END-->


    </div>

@endsection()

@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

        load_data_signatories();
        $("#payment_tbl").DataTable();  

        $("#btn_update").on("click", function(e){

            var signatory_id = $("#signatory_id").val();
            var full_name = $("#full_name").val();
            var designation = $("#designation").val();
            
            if(signatory_id==0 || full_name ==""||designation==""){
                alert('Please Complete Information');
            }else{

                $.ajax({
            type: 'POST',
            url: "{{ route('paymaya.signatories.update') }}",
            data: {
                signatory_id,
                full_name,
                designation,
                _token: "{{ csrf_token() }}"
            },

            success: function(data){
                alert('successfully Updated');

                $('#add_new_modal').modal('toggle');
                load_data_signatories();
                $("#payment_tbl").DataTable();  
            }
            });

            }       
    });
    
        $('body').on('click', '#edit_modal', function(e){
            $('#add_new_modal').modal('toggle');
            var signatory_id =$(this).attr("data-id");
            $('.signatory_id_n').val(signatory_id);

            $.ajax({
            type: 'POST',
            url: "{{ route('paymaya.signatories.get') }}",
            data: {
                signatory_id,
                _token: "{{ csrf_token() }}"
            },

                success: function(data){
                    $('.full_name').val(data[0].full_name);
                    $('.designation').val(data[0].designation);

                }
            })   
          
        });
    


        function load_data_signatories(){
            HoldOn.open(holdon_options);
            
            $('#payment_tbl').DataTable().clear();
            $("#payment_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('paymaya.signatories_tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}", 
                    }
                },
                "columns":[
                    {"data": "signatory_id"},
                    {"data": "full_name"},
                    {"data": "designation"},
                    {"data": "status"},
                   
                ]
            });

            HoldOn.close();
            
        }
            

    </script>
@endpush
