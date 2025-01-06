@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap');

        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .mother_content{
            overflow-y: hidden;
            background: white!important;
        }

        .rounded{
            border-radius: 1em;
            background: white;
        }

        .cp{
            padding: 3em 1em;
        }

        label{
            color: black;
        }
        
        th{
            color: black;
        }

        ._main_container{
            background: white;
            font-family: "DM Sans";
            display: grid;
            gap: 1em;
            grid-template-areas:
            'one two'!important;
            grid-template-columns: 1fr 3fr;
            grid-template-rows: 1fr;
            position: relative;
            height: calc(100vh - 150px)!important;
            /* max-height: calc(100vh - 150px)!important; */
        }

        .selectors{
            grid-area: one;
            height: max-content;
        }

        .main_table{
            grid-area: two;
            height: 95%;
            overflow-y: auto;
        }

        #databody{
            max-height: calc(100vh - 100px)!important;
            font-size: 0.9em;
        }

        .prvSel{
            border-radius: 1e;
        }

        .super_title{
            font-size: 2em;
            color: black;
            font-weight: 700;
        }

        input{
            border-radius: 1em!important;
        }

        input:focus, input:active{
            border: 2px black solid;
            background: #00000010;
        }

        #search{
            border-radius: 2em;
            outline: green 1px solid;
            background-color: white;
            color: green;
            font-weight: 700;
        }
    </style>
    <div class="_main_container">
        <div class="shadow-xl cp rounded selectors">
            <div class="super_title">New Farmer Encoded</div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <br>
                        <label for="utilProvince" id="label_province">Province  </label>
                        <select name="utilProvince" id="utilProvince" class="form-control prvSel" data-parsley-min="1">

                            <option value="0">Please select a province</option>
                            @foreach ($provinces as $provi)
                            <option value="{{$provi->province}}">{{$provi->province}}</option>
                            @endforeach
                        </select>
                    </div>


                    <div id="dialog" title="Prompt">
                        <p>Please input search value</p>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-12"> 
                            <legend></legend>
                            <label for="rsbsa_search" id="label_rsbsa">RSBSA #  </label>
                            <input type="text" id="rsbsa_search" class="form-control" name="rsbsa_search" placeholder="XX-XX-XX-XXX-XXXXXX">
                        </div>
                    </div>
                    
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-6"> 
                            <label for="last_search" id="label_last">Last Name   </label>
                            <input type="text" id="last_search" class="form-control" name="last_search" placeholder="e.g Dela Cruz">
                        </div>

                        <div class="col-md-6"> 
                            <label for="first_search" id="label_first">First Name   </label>
                            <input type="text" id="first_search" class="form-control" name="first_search" placeholder="e.g Juan">
                        </div>


                        
                    </div>


                </div>

                <div class="form-group">
       
                    <div class="col-md-12" style="text-align:center; margin-top:5px;">
                        <button type="button" name="search" id="search" class="btn btn-md btn-success" style="width:150px;margin: 5px;" ><i class="fa fa-search" aria-hidden="true"></i> Find Farmer </button>
                    </div>

                </div>
            </div>
        

            </div>

        <!-- </div> -->


        <div class="main_table x_content form-horizontal form-label-left shadow-xl cp rounded">
                            <div class="form-group cp">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered rounded" id="dataTBL">
                                        
                                            <thead>
                                                <tr>
                                                <th colspan="6">FARMER INFOMATION</th>
                                                <th colspan="4">DISTRIBUTION DATA</th>
                                               
                                                </tr>

                                                <tr>
                                                <th>RSBSA # </th>
                                                <th >Name (Last, Ext, First, Middle) </th>
                                                
                                                <th>Parcel Address</th>
                                                
                                                <th>Farm Area</th>
                                                <th>Sex</th>
                                                <th>Birthdate</th>
                                                
                                                <th >Claimed_Details</th>
                                                <th > Assessed By  </th>
                                                <th > Status  </th>
                                                
                                                <th >Action</th>
                                                </tr>
                                                
                                            </thead>
                                            <tbody id='databody' >
                                                
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
            </div>
    </div>
</div>
   




@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(".prvSel").select2();
        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 10
             });


        var auto_confirm = "";


        $("#search").on("click", function(){
            if($("#utilProvince").val()== "0"){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Select A Province First!',  });
                return;
            }


            var province = $('#utilProvince').val();
            var rsbsa_search = $("#rsbsa_search").val();
            var last_search = $("#last_search").val();
            var first_search = $("#first_search").val();
            
                $('#dataTBL').DataTable().clear();
                $('#dataTBL').DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 25,
                    "ajax": {
                        "url": "{{route('new_farmer_list')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "province": province,
                            "rsbsa_search": rsbsa_search,
                            "last_search": last_search,
                            "first_search": first_search
                        }
                    },
                    "columns":[
                        {"data": "rsbsa"},
                        {"data": "name"},
                        {"data": "parcel"},
                        {"data": "final_area"},
                        {"data": "sex","className": "text-center"},
                        {"data": "birthdate","className": "text-center"},
                        {"data": "claimed"},
                        {"data": "user"},
                        {"data": "status"},
                      
                        {"data": "action" }
                    ],
                    "fnDrawCallback": function() {
                        // Call the function after the columns have returned
                        if ($('#dataTBL').DataTable().rows().count() === 0) {
                        }
                    }
                });


        });

        
                function approve(id, prv_code){

                    if(auto_confirm == false || auto_confirm == "" ){
                        Swal.fire({
                        title: "Approve this new Farmer Encoded?",
                        text: "",
                        icon: "success",
                        showCancelButton: true,
                        confirmButtonText: "Approved",
                    }).then(function(result) {
                        if (result.value) {
                            $("#approve_"+id).empty().append("Processing");

                            if(auto_confirm == ""){
                            Swal.fire({
                            title: "Set Auto Confirm as Yes?",
                            text: "",
                            icon: "success",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Auto Complete",
                                }).then(function(result_ac) {
                                    if (result_ac.value) {
                                        auto_confirm = true;
                                    }else{
                                        auto_confirm = false
                                    }
                                });
                            }




                            $.ajax({
                                type: 'POST',
                                url: "{{route('approve_new_farmer')}}",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: id,
                                    prv_code: prv_code
                                },
                                dataType: 'json',
                                success: function(return_data){

                                    if(return_data != "success"){
                                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: return_data,  });
                                        return;
                                    }

                                    $("#approve_"+id).attr("disabled", "disabled");
                                    $("#approve_"+id).empty().append("APPROVED!");
                                    $("#status_"+id).attr("class", "badge badge-success");
                                    $("#status_"+id).empty().append("APPROVED")
                                    $("#disapprove_"+id).hide("fast");
                                    
                                    },
                                    error: function(return_data){
                                        $("#approve_"+id).empty().append("Confirm");
                                 
                                    }
                                });






                        }
                    });

                        
                    }else{
                        $("#approve_"+id).empty().append("Processing");
                                //AUTO YES
                            $.ajax({
                                type: 'POST',
                                url: "{{route('approve_new_farmer')}}",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: id,
                                    prv_code: prv_code
                                },
                                dataType: 'json',
                                success: function(return_data){

                                    if(return_data != "success"){
                                        Swal.fire({   icon: 'error',    title: 'Oops...',  text: return_data,  });
                                        return;
                                    }

                                    $("#approve_"+id).attr("disabled", "disabled");
                                    $("#approve_"+id).empty().append("APPROVED!");
                                    $("#status_"+id).attr("class", "badge badge-success");
                                    $("#status_"+id).empty().append("APPROVED")
                                    $("#disapprove_"+id).hide("fast");
                                    
                                    },
                                    error: function(return_data){
                                        $("#approve_"+id).empty().append("Confirm");
                                 
                                    }
                                });


                        
                    }

                
                }


                function disapprove(id, prv_code){


                    if(auto_confirm == false || auto_confirm == "" ){
                        Swal.fire({
                        title: "Disapproved This Farmer?",
                        text: "",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Reject",
                    }).then(function(result) {
                        if (result.value) {
                            
                            if(auto_confirm == ""){
                            Swal.fire({
                            title: "Set Auto Confirm as Yes?",
                            text: "",
                            icon: "success",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Auto Complete",
                                }).then(function(result_ac) {
                                    if (result_ac.value) {
                                        auto_confirm = true;
                                    }else{
                                        auto_confirm = false
                                    }
                                });
                            }

                            $("#disapprove_"+id).empty().append("Processing");

                            $.ajax({
                                type: 'POST',
                                url: "{{route('disapprove_new_farmer')}}",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: id,
                                    prv_code: prv_code
                                },
                                dataType: 'json',
                                success: function(return_data){
                                    $("#disapprove_"+id).attr("disabled", "disabled");
                                    $("#disapprove_"+id).empty().append("REJECTED!");
                                    $("#status_"+id).attr("class", "badge badge-warning");
                                    $("#status_"+id).empty().append("REJECTED")
                                    $("#approve_"+id).hide("fast");
                                    
                                    },
                                    error: function(return_data){
                                        $("#disapprove_"+id).empty().append("Reject");
                                 
                                    }
                                });



                        }
                    });

                        
                    }else{
                        $("#disapprove_"+id).empty().append("Processing");

                        $.ajax({
                                type: 'POST',
                                url: "{{route('disapprove_new_farmer')}}",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    id: id,
                                    prv_code: prv_code
                                },
                                dataType: 'json',
                                success: function(return_data){
                                    $("#disapprove_"+id).attr("disabled", "disabled");
                                    $("#disapprove_"+id).empty().append("REJECTED!");
                                    $("#status_"+id).attr("class", "badge badge-warning");
                                    $("#status_"+id).empty().append("REJECTED")
                                    $("#approve_"+id).hide("fast");
                                    
                                    },
                                    error: function(return_data){
                                        $("#disapprove_"+id).empty().append("Reject");
                                 
                                    }
                                });

                    }



              
                }

        

    </script>


@endpush