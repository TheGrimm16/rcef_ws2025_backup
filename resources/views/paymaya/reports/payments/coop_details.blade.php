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
                    E-Binhi Cooperatives
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

                    <button class="btn btn-success" id="add_coop_new" ><i class="fa fa-plus"></i> Add Coop</button>
                    <br><br>

                        <table class="table table-hover table-striped table-bordered" id="payment_tbl">
                            <thead>
                                <th>Coop Name</th>
                                <th>acronym</th>
                                <th>Address</th>
                                <th>branch</th>
                                <th>Account no</th>
                                <th>Form Type</th>
                                <th>action</th>
                              
                            </thead>
                        </table>
                      
                </div>
            </div>
        </div><br>

<!-- update MODAL -->
<div id="add_new_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Update Coop Details</span><br>  
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" name="" id="cood_id">

                    <label class="">Coop Name:</label>
                    <input type="text" class="form-control" name="" id="coop_name">

                    <label class="l">Acronym:</label>
                    <input type="text" class="form-control" name="" id="acronym">

                    <label class="l">Address:</label>
                    <input type="text" class="form-control" name="" id="address">

                    <label class="l">Branch:</label>
                    <input type="text" class="form-control" name="" id="branch">

                    <label class="l">Account No. :</label>
                    <input type="text" class="form-control" name="" id="account_no">

                    <label class="l">DBP :</label>
                    <select class="form-control" name="" id="is_dbp">
                        <option value="">..</option>
                        <option value="1">Yes</option>
                        <option value="0">NO</option>

                    </select>
                    
                    <label class="l">Accreditation NO:</label>
                    <input type="text" class="form-control" name="" id="accreditation_no" readonly>

                    <label class="l">Form Type</label>
                    <select class="form-control" name="" id="form_type">
                        <option value="">..</option>
                        <option value="FUND TRANSFER FORM">FUND TRANSFER FORM</option>
                        <option value="ADVICE TO DEBIT/CREDIT">ADVICE TO DEBIT/CREDIT</option>

                    </select>

                    <br>
                <button class="btn btn-success" name="" id="btn_update"> <i class="fa fa-pencil" ></i> Update</button>

            </div>
        </div>
    </div>
</div>
<!-- DOWNLOAD MODAL END-->

<!-- add MODAL -->
<div id="add_coop" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Update Coop Details</span><br>  
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" name="" id="cood_id">

                    <label class="">Coop Name:</label>
                    <input type="text" class="form-control" name="" id="coop_name">

                    <label class="l">Acronym:</label>
                    <input type="text" class="form-control" name="" id="acronym">

                    <label class="l">Address:</label>
                    <input type="text" class="form-control" name="" id="address">

                    <label class="l">Branch:</label>
                    <input type="text" class="form-control" name="" id="branch">

                    <label class="l">Account No. :</label>
                    <input type="text" class="form-control" name="" id="account_no">

                    <label class="l">DBP :</label>
                    <select class="form-control" name="" id="is_dbp">
                        <option value="">..</option>
                        <option value="1">Yes</option>
                        <option value="0">NO</option>

                    </select>
                    
                    <label class="l">Accreditation NO:</label>
                    <input type="text" class="form-control" name="" id="accreditation_no">

                    <label class="l">Form Type</label>
                    <select class="form-control" name="" id="form_type">
                        <option value="">..</option>
                        <option value="FUND TRANSFER FORM">FUND TRANSFER FORM</option>
                        <option value="ADVICE TO DEBIT/CREDIT">ADVICE TO DEBIT/CREDIT</option>

                    </select>

                    <br>
                <button class="btn btn-success" name="" id="btn_update"> <i class="fa fa-pencil" ></i> save</button>

            </div>
        </div>
    </div>
</div>

<!-- add MODAL END-->


    </div>

@endsection()

@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>


    <script>

        load_data_signatories();
        $("#payment_tbl").DataTable();  

        $("#btn_update").on("click", function(e){

            var coop_id = $("#cood_id").val();
            var coop_name = $("#coop_name").val();
            var acronym = $("#acronym").val();
            var address = $("#address").val();
            var branch = $("#branch").val();
            var account_no = $("#account_no").val();
            var is_dbp = $("#is_dbp").val();
            var accreditation_no = $("#accreditation_no").val();
            var form_type = $("#form_type").val();

            // console.log(coop_id);
            // console.log(coop_name);
            // console.log(acronym);
            // console.log(address);
            // console.log(branch);
            // console.log(account_no);
            // console.log(is_dbp);
            // console.log(accreditation_no);
            // console.log(form_type);
            
            if(coop_id==0 || coop_name ==""){
                alert('Please Complete Information');
            }else{

            $.ajax({
            type: 'POST',
            url: "{{ route('ebinhi.coops.update') }}",
            data: {
                coop_id,
                coop_name,
                acronym,
                address,
                branch,
                account_no,
                is_dbp,
                accreditation_no,
                form_type,
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
            var coop_id =$(this).attr("data-id");
            $('#cood_id').val(coop_id);

            $.ajax({
            type: 'POST',
            url: "{{ route('ebinhi.coops.get') }}",
            data: {
                coop_id,
                _token: "{{ csrf_token() }}"
            },

                success: function(data){
             
                     $('#coop_name').val(data[0].coop_name);
                     $('#acronym').val(data[0].acronym);
                     $('#address').val(data[0].address_1);
                     $('#branch').val(data[0].branch);
                     $('#account_no').val(data[0].account_no);
                     $('#is_dbp').val(data[0].is_dbp);
                     $('#accreditation_no').val(data[0].coop_ref);
                     $('#form_type').val(data[0].form_type);

                }
            })   
          
        });


        $('body').on('click', '#add_modal', function(e){
            $('#add_new_coop_modal').modal('toggle');
            var signatory_id =$(this).attr("data-id");
            $('.signatory_id_n').val(signatory_id);

            $.ajax({
            type: 'POST',
            url: "{{ route('ebinhi.coops.get') }}",
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
                    "url": "{{ route('ebinhi.coops.tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}", 
                    }
                },
                "columns":[
                    {"data": "coop_name"},
                    {"data": "acronym"},
                    {"data": "address_1"},
                    {"data": "branch"},
                    {"data": "account_no"},
                    {"data": "form_type"},
                    {"data": "id"},
                   
                ]
            });

            HoldOn.close();
            
        }


        $('body').on('click', '#add_coop_new', function(e){
            $('#add_coop').modal('toggle');
            // var coop_id =$(this).attr("data-id");
            // $('#cood_id').val(coop_id);

            $.ajax({
            type: 'POST',
            url: "{{ route('ebinhi.coops.get') }}",
            data: {
                coop_id,
                _token: "{{ csrf_token() }}"
            },

                success: function(data){
             
              
                }
            })   
          
        });
            

    </script>
@endpush
