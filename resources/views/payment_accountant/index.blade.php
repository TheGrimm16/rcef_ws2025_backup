<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/dropzone.min.css') }}">


<meta name="csrf-token" content="{{ csrf_token() }}">
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


    /*  */
    .tab {
  overflow: hidden;
  border: 1px solid #ccc; 
  background-color: #f1f1f1;
  
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 12px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}

/* Style the close button */
.topright {
  float: right;
  cursor: pointer;
  font-size: 28px;
}

.topright:hover {color: red;}
    /*  */

/* select to coop*/

.btn-success.disabled, .btn-success[disabled]{
        background-color: #5cb85c;
        border-color: #4cae4c;
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

.total_container{
    font-size: 30px;
    font-weight: bold;
}

.groove {border-style: ridge;
    padding-left: 5px;
    padding-top: 2px;
    margin-bottom: 5px;      
}
.check_all{
    /* margin-bottom: 5px; */
    /* padding-left: 10px;   */
    vertical-align: top;    
    
    position: relative;
    top: 7px;
    left: 10px;
    /* overflow: hidden; */

}

.button_status{
    vertical-align: center;   
    position: relative;
    /* top: 7px; */
    left: 600px;
    /* align:right; */

}

.process_btn{
    
    margin-top: -200px;
    margin-bottom: -200px;  
}

.status_batch{
    margin-left: 10px;
}




  </style>


<style>

</style>
@endsection

@section('content')
  
    <div class="row">

        <div class="col-md-9">
            <div class="row" id="select2_filter">
                <div class="col-md-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <label for="">Select Seed Cooperative:</label>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div class="row"  id="select_coop1_fld">
                                <div class="col-md-10">
                                    <select name="coop" id="coop" style="width: 100%;" class="form-control">
                                        <option value= "0">Select Coop...</option>
                                       @foreach ($select2_data as $item)
            
                                           <option value="{{$item->current_moa}}">{{$item->coopName}}</option>
                                       @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"  id="add_submit_btn_fld">
                                     <button class="btn btn-success btn-block load_coop_data" disabled id="load_coop_btn"><i class="fa fa-database"></i> LOAD DATA</button>
                                     
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            {{--top 10 coop --}}

            <div class="row" id="10_coop">
                <div class="col-md-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Seed Cooperative</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                        
                            <div class="accordion">
                                @foreach ($coops as $coop)
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0" style="margin:0">
                                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                                    {{$coop->coopName}} ({{$coop->acronym}})
                                                </button>
                                            </h5>
                                            <button class="btn btn-success btn-sm view_coop" style="top: 10%;margin-right: 10px;position: absolute;right: 0%;" data-moa="{{$coop->current_moa}}" data-coop_accre="{{$coop->accreditation_no}}"><i class="fa fa-database  "></i> View Deliveries</button>
                                        </div>
                                    </div> 
                                @endforeach
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
            {{-- end --}}
            

            <div class="row"  id="cards_content" style="display:none;">
                <div class="x_panel cards_content" >  
                    
                    <div style="display: none" class="row process_btn" id="action_button" >
                        {{-- <div class="x_panel"> --}}
                            <div class="col-md-12">
                                <button class="btn btn-primary pull-left" id="btn_approved" data-selected=""> <i class="fa fa-check" ></i> Update status to DV Created Number Selected <span class="label label-info" id="selected_count"></span></button>
                                <button class="btn btn-danger pull-right" id="btn_failed" data-selected=""> <i class="fa fa-pencil" ></i> Failed/Reupload <span class="label label-warning" id="selected_count_reject"></span></button>
                            </div>
                        {{-- </div> --}}
                    </div>
                    <h2 id="coop_name_title" class="coop_name_title"></h2>
                    <div class="accordion accordion_data"></div>
                </div>    
            </div>
            <div style="display:none" id="alert_na">
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                   <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> -->
                    <strong><i class="fa fa-info-circle"></i> Notice!</strong><b><u> No Records Found</u></b>
                </div>
            </div>
    </div>

        <div class="col-md-3" id="" style="display:inline;">
            <div class="x_panel">
                <div class="x_title">
                    <h2>FIlters</h2>
                    <div class="clearfix"></div>
                </div>
                <button class="btn btn-warning form-control" id="reset_filter"><i class="fa fa-refresh"></i> Reset Filter</button><br>
                <div class="x_content form-horizontal form-label-left"> 
                
                        <label for="">Filter By Delivery Date:</label>
                        <button type="button" class="btn btn-default form-control select_class date2" disabled id="daterange-btn">
                            <span>
                                Select Date 
                            </span>
                            <i class="fa fa-calendar"></i>       
                        </button>
                        <hr>
     
                        <input type="hidden" id="start_date" name="start_date">
                        <input type="hidden" id="end_date" name="end_date">
                        <input type="hidden" id="fromDate" name="fromDate">
                        <input type="hidden" id="toDate" name="toDate">
    

                    <input type="hidden" id = "moa_hidden">
                    <label for="">Filter By Attachement Status:</label><br>
                    <div class="groove">
                        <input type="radio" name="attachement_status" class="form-group attachement_status" disabled id="is_rejected">
                        <label for="is_rejected" class=""> Rejected Attachement</label><br>
                    </div> 
                    <div class="groove">
                        <input type="radio" name="attachement_status" class="form-group attachement_status" disabled id="is_incomplete_attachement">
                        <label for="is_incomplete_attachement" class=""> Incomplete Attachement</label><br>
                    </div>
                    <div class="groove">
                        <input type="radio" name="attachement_status" class="form-group attachement_status" disabled id="is_complete_attachement">
                        <label for="is_complete_attachement" class=""> Complete Attachement</label><br>
                    </div>

                    <div class="groove">
                        <input type="radio" name="attachement_status" class="form-group attachement_status" disabled id="is_for_dv">
                        <label for="is_for_dv" class=""> For DV Processing</label><br>
                    </div>

                    <div class="groove">
                        <input type="radio" name="attachement_status" class="form-group attachement_status" disabled id="is_dv_created">
                        <label for="is_dv_created" class=""> DV Created</label><br>
                    </div>

                    <div class="groove">
                        <input type="radio" name="attachement_status" class="form-group attachement_status" disabled id="is_paid_attachement">
                        <label for="is_paid_attachement" class=""> Paid</label><br>
                    </div>
            
                    <hr>
                    <label for="">Filter By Location:</label>
                    <select class="form-control select_class" disabled id="region_select" name="region_select" style="margin-bottom:10px;">
                        <option value="">Please select a region</option>
                    </select>
    
                    <select class="form-control select_class" disabled id="province_select" name="province_select" style="margin-bottom:10px;">
                        <option value="">Please select a province</option>
                    </select>
    
                    <select class="form-control select_class" disabled id="municipality_select" name="municipality_select" style="margin-bottom:10px;">
                        <option value="">Please select a municipality</option>
                    </select>
                    
                    
                    <br><button class="btn btn-success form-control filter_data_btn" disabled id="filter_btn" style="display:;"><i class="fa fa-bar-chart-o"></i> Filter Data</button>
                </div>
            </div>
        </div>

    <!-- confirmation MODAL -->
    <div id="confirmation_modal" class="modal fade" role="dialog" >
        <div class="modal-dialog" style="height:5%;width:30%;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">
                      {{-- <span>Are you sure you want to Approve RLA?</span> --}}
                      <span id="title_confirmation"></span><br>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        
                        <div class="col pull-right">
                            <button class="btn btn-danger" data-dismiss="modal"> <i class="fa fa-thumbs-o-down" ></i> Cancel</button>
                            <button class="btn btn-success" name="" id="btn_confirm"> <i class="fa fa-thumbs-o-up" ></i> Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- confirmation MODAL END-->
 <!-- confirmation MODAL -->
 <div id="processor_confirmation_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width:30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                  <span id="status_1"></span>
                  {{-- <span id="title_confirmation"></span><br>          --}}
                </h4>
            </div>
            <div class="modal-body"> <br>
                {{-- <div class="row"> --}}
                    <div class="row pull-right" style="margin-right:5px;">
                        <button class="btn btn-danger" data-dismiss="modal"> <i class="fa fa-thumbs-o-down" ></i> Cancel</button>
                        <button class="btn btn-success" name="" id="yup"> <i class="fa fa-thumbs-o-up" ></i> Confirm</button>
                    </div>
                {{-- </div> --}}

                <br>
                <br>
            </div>
        </div>
    </div>
</div>
<!-- confirmation MODAL END-->

   {{--  1111111111111111--}}
   <div id="view_attachement_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 40%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span class="batch_ticket_title" id="batch_ticket_title"></span><br>
                    <span class="seed_title" id="seed_title"></span><br> 
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>View Attachemt/s</p>
                        <div class="row table_attachement"></div>
                        <hr>

                        <input type="hidden" id="batch_no">
                        <input type="hidden" id="seed_tag_no">
                        <input type="hidden" id="batch_type_no">
                        {{-- <div class="row">
                            <label class="col-xs-4" for="remarks1">Remarks(Optional):</label>
                            <textarea style='width:95%;margin-top: 5px; margin-left:10px;' class='form-control' id = 'processor_remarks' rows='3'></textarea>
                        </div> <br> --}}
                        {{-- <div class="row" style="margin-left:10px;">
                            <label>Please Select Attachement Status</label>
                            <div class="row">
                                <input type="checkbox" class="form-group checK_status" id="status_passed" name="">
                                <label for="status_passed" class="label label-success"> Passed</label><br>
                            </div>
                            <div class="row">
                                <input type="checkbox" class="form-group checK_status" id="status_failed" name="">
                                <label for="status_failed" class="label label-danger"> Failed</label><br>
                            </div>

                        </div> --}}
                            
                        {{-- <div class="row pull-right">
                            
                            <button class="btn btn-danger" data-dismiss="modal"> <i class="fa fa-ban" ></i> Cancel</button>
                            <button class="btn btn-success" name="" id="submit_remarks_btn" disabled> <i class="fa fa-check" ></i> Submit</button>
                            
                        </div>                             --}}
                    </div>
                </div>       
            </div>
        </div>
    </div>
</div>
{{--  --}}
         
    </div>

    

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src="{{ asset('public/js/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{ asset('public/js/dropzone2/dropzone.min.js')}}"></script>

    @yield('scripts')

    <script>

        $('body').on('click', '.Open_tab_preview', function() {
            var path =$(this).attr("data-id");
            window.open('../'+path);    
        });



        $('.checK_status').click(function(){
            $('.checK_status').each(function(){
                $(this).prop('checked', false);    
            }); 
            $(this).prop('checked', true);
            $('#submit_remarks_btn').removeAttr('disabled');

        }); 

        $("#coop").select2();

        $("#is_rejected").change(function() {
            if(this.checked) {
                $('.filter_data_btn').removeAttr('disabled');
            }else{
                $('.filter_data_btn').attr('disabled','disabled');
            }
        });

        $("#is_incomplete_attachement").change(function() {
            if(this.checked) {
                $('.filter_data_btn').removeAttr('disabled');
            }else{
                $('.filter_data_btn').attr('disabled','disabled');
            }
        });

        $("#is_complete_attachement").change(function() {
            if(this.checked) {
                $('.filter_data_btn').removeAttr('disabled');
            }else{
                $('.filter_data_btn').attr('disabled','disabled');
            }
        });

        $("#is_for_dv").change(function() {
            if(this.checked) {
                $('.filter_data_btn').removeAttr('disabled');
            }else{
                $('.filter_data_btn').attr('disabled','disabled');
            }
        });

        $("#is_dv_created").change(function() {
            if(this.checked) {
                $('.filter_data_btn').removeAttr('disabled');
            }else{
                $('.filter_data_btn').attr('disabled','disabled');
            }
        });

        $("#is_paid_attachement").change(function() {
            if(this.checked) {
                $('.filter_data_btn').removeAttr('disabled');
            }else{
                $('.filter_data_btn').attr('disabled','disabled');
            }
        });
        
        $('#coop').on('select2:select', function (e) {
            var coop_accre = $("#coop").val();
            if(coop_accre == 0){
                $("#formid input, #formid select").attr('disabled',true);
                $('.select').attr('disabled','disabled');
                $('.date2').attr('disabled','disabled');
                $('.filter_data_btn').attr('disabled','disabled');
                $('.form-group').attr('disabled','disabled'); 
                $('.load_coop_data').attr('disabled','disabled');
            }else{
                $('.load_coop_data').removeAttr('disabled');
            }
    
        });

        var count2 = 0;
        
        $('body').on('change', '.checkbox_all', function(){
            var ischecked= $(this).is(':checked');
    
            if(ischecked) {
                count2++;
                $("#action_button").css("display", "inline");
                $("#selected_count").text(count2); 
                $("#selected_count_reject").text(count2); 
                
                
            }else if(!ischecked){ 
                count2--;
                if(count2<1){
                $("#action_button").css("display", "none");
                }
                $("#selected_count").text(count2);
                $("#selected_count_reject").text(count2);
            }

    
        });


        $('body').on('click', '.plus_click', function(e){
            var batch_num =$(this).attr("data-id");
            forAssesment(batch_num,batch_num);
        });

        $("#load_coop_btn").on("click", function(e){
            var current_moa = $("#coop").val();
            load_coop_deliveries(current_moa);
        });

        $(".view_coop").on("click", function(e){
            var current_moa =$(this).attr("data-moa");
            load_coop_deliveries(current_moa);
        });

        $("#filter_btn").on("click", function(e){
            $("#alert_na").css("display", "none");
            $(".accordion_data").empty();
            // $("#cards_content").css("display", "none"); 
            HoldOn.open(holdon_options); 
            HoldOn.close(); 
            
            var current_moa =$('#coop').val();
            var region =$('#region_select').val();
            var province =$('#province_select').val();
            var municipality =$('#municipality_select').val();
            var start_date =$('#start_date').val();
            var end_date =$('#end_date').val();
            var attachement_status = 0; 
         
            if ($('#is_rejected').is(":checked")){ 
                var attachement_status = 5;
            }else if ($('#is_incomplete_attachement').is(":checked")){ 
                var attachement_status = 6;
            }else if ($('#is_complete_attachement').is(":checked")){ 
                var attachement_status = 4;
            }else if ($('#is_for_dv').is(":checked")){
                var attachement_status = 3;
            }else if ($('#is_dv_created').is(":checked")){
                var attachement_status = 2;
            }else if ($('#is_paid_attachement').is(":checked")){ 
                var attachement_status = 1; 
            }
     
            $.ajax({
            type: 'POST',
            url: "{{ route('load.sg.filter') }}",
            data: {
                current_moa,
                region,
                province,
                municipality,
                start_date,
                end_date,
                attachement_status,attachement_status,
                _token: "{{ csrf_token() }}"
            },
            success: function(data){

if(data.data_count == 0){
    // alert('no data');
    $("#alert_na").css("display", "inline");
     $("#cards_content").css("display", "none"); 
    
}else{
    $("#cards_content").css("display", "inline"); 
    jQuery.each(data.sg, function(index, array_value1){
       
        $('#coop_name_title').text(array_value1['coopName']);
        $('#moa_hidden').val(array_value1['current_moa']);
                                
    });
     var region_data ="";
    jQuery.each(data.data, function(index, array_value){
        var statustmp;
        if(array_value['batch_status'] == undefined){
            statustmp = '<span class="label label-warning status_batch"> Incomplete Attachements</span>';
        }if(array_value['batch_status'] == 0){
            statustmp= '<span class="label label-warning status_batch"> Incomplete Attachements</span>';
        } if(array_value['batch_status'] == 1){
            statustmp= '<span class="label label-success status_batch "> Paid</span>';
        } if(array_value['batch_status'] == 2){
            statustmp= '<span class="label label-success status_batch"> DV Created</span>';
        } if(array_value['batch_status'] == 3){
            statustmp= '<span class="label label-info status_batch"> For DV Processing</span>';
        } if(array_value['batch_status'] == 4){
            statustmp= '<span class="label label-primary status_batch"> Complete Attachements</span>';
        } if(array_value['batch_status'] == 5){
            statustmp= '<span class="label label-danger status_batch"> Failed /For Re-upload</span>';
        }

        $(".accordion_data").append('<div class="card" > '+                                          
                        '       <div class="card-header" id="headingOne">'+
                        '         <h5 class="mb-0" style="margin:0" >'+
                        '             <input type="checkbox" class="checkbox_all form-group check_all" id="check_'+array_value['batchTicketNumber']+'" name = "selected_batch" value="'+array_value['batchTicketNumber']+'" >'+
                        '             <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">'+
                        '                 <label for="check_'+array_value['batchTicketNumber']+'" class="pull-left">Batch Number: '+array_value['batchTicketNumber']+' > '+array_value['sum_total_bags']+' bags > Delivery Date: '+array_value['dateCreated_new']+' </label>'+statustmp+'<br>'+
                        '                 <p class="pull-left">Location:'+array_value['region']+' > '+array_value['province']+' > '+array_value['municipality']+' </p>'+
                        '             </button>'+
                        '             <i class="plus_click pull-right fa fa-plus "  data-id = "'+array_value['batchTicketNumber']+'" id="icon3_id_'+array_value['batchTicketNumber']+'" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse3'+array_value['batchTicketNumber']+'" aria-controls="'+array_value['batchTicketNumber']+'"></i>'+
                        '         </h5>'+
                        '     </div>'+
                        '     <div id="collapse3'+array_value['batchTicketNumber']+'" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">'+
                        '         <div class="card-body">'+
                        '             <div class="row">'+
                        '                 <div class="col-md-9">'+
                        '                     <ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="'+array_value['batchTicketNumber']+'"></ul>'+
                        '                 </div>'+
                        '                 <div class="col-md-3">'+
                        '                     <div class="row">'+
                        '                         <div class="col-md-11">'+
                        '                               <div class="row 3_button_'+array_value['batchTicketNumber']+'" style="display: none"></div><br><br><br>'+            
                        '                         </div>'+
                        '                    </div>     '+
                        '                 </div>'+
                        '             </div> '+
                        '         </div>'+
                        '     </div>'+
                        ' </div>');

     /* regioin */
     if(region_data != array_value['region']){           
                            region_data = array_value['region'];
                            var tmp=0;
                            $("#region_select option").each(function()
                            {
                            if($(this).val() == region_data){
                                tmp++;
                            }
                            });
                            if(tmp==0){
                                $('#region_select').append($('<option>', {value:region_data, text:region_data}));
                            }
                    }               
   
});   
}
}
});      
});


        $("#btn_approved").on("click", function(e){
            
            var current_moa = $("#moa_hidden").val();
            var checkboxValues = [];
            var batch_length = 0;
            $('input[name=selected_batch]:checked').map(function() {
                checkboxValues.push($(this).val());
            });
            $('#title_confirmation').text("Update the Status of these Batch/'s for DV Created?"); 
            $('#confirmation_modal').modal('show');

                $("#btn_confirm").click(function(e) {
                    console.log(checkboxValues);
                    console.log(moa_hidden);
                    HoldOn.open(holdon_options);
                    
                    $('#confirmation_modal').modal('hide');
                    
                    $.ajax({
                    type: 'POST',
                    url: "{{ route('processor.update.batch') }}",
                    data: {
                    _token: "{{ csrf_token() }}",
                    checkboxValues: checkboxValues,
                    current_moa: current_moa,
                    status: 2
                    },
                    success: function(data){
                        count2 =0;
                        $("#action_button").css("display", "none");
                        if(data>0){
                            alert('Status Successfully Updated');
                            
                            $('#confirmation_modal').modal('hide');
                        }else{
                            alert('Cannot update Records'); 
                        }
                        if(current_moa!=''){
                            $( "#load_coop_btn" ).trigger( "click" );
                        }else{
                            location.reload();
                        }
                        HoldOn.close();

                    }
                });
                })

        }); 

        $("#btn_failed").on("click", function(e){  
            var current_moa = $("#moa_hidden").val();
            var checkboxValues = [];
            var batch_length = 0;
            $('input[name=selected_batch]:checked').map(function() {
                checkboxValues.push($(this).val());
            });
            $('#title_confirmation').text("Update the Status of these Batch/'s to Failed/Reupload?"); 
            $('#confirmation_modal').modal('show');

                $("#btn_confirm").click(function(e) {
                    console.log(checkboxValues);
                    console.log(moa_hidden);
                    HoldOn.open(holdon_options);
                    
                    $('#confirmation_modal').modal('hide');
                    
                    $.ajax({
                    type: 'POST',
                    url: "{{ route('processor.update.batch') }}",
                    data: {
                    _token: "{{ csrf_token() }}",
                    checkboxValues: checkboxValues,
                    current_moa: current_moa,
                    status: 5
                    },
                    success: function(data){
                        count2 =0;
                        $("#action_button").css("display", "none");
                        if(data>0){
                            alert('Status Successfully Updated');
                            
                            $('#confirmation_modal').modal('hide');
                        }else{
                            alert('Cannot update Records'); 
                        }
                        if(current_moa!=''){
                            $( "#load_coop_btn" ).trigger( "click" );
                        }else{
                            location.reload();
                        }
                        HoldOn.close();

                    }
                });
                })

        }); 
 

        $('body').on('click', '.view_per_seed_modal', function(e){ 
            // $('#status_passed').attr('checked', true); 
            // $('.checK_status').attr('checked', false); 
            $("#batch_type_no").val("");
            $("#processor_remarks").val("");
            $('.table_attachement').empty();
            var seed_tag =$(this).attr("data-seed_tag");
            var batch_ticket =$(this).attr("data-batch_num");
            console.log(seed_tag);
            console.log(batch_ticket);

            $.ajax({
                    type: 'POST',
                    url: "{{ route('load.attachements') }}",
                    data: {
                    _token: "{{ csrf_token() }}",
                    seed_tag: seed_tag,
                    batch_ticket: batch_ticket  
                    },
                    success: function(data){
                    var attachement_num = 1;
                        
                        jQuery.each(data, function(index, array_value){
                            
                            $(".table_attachement").append("<ul class = 'col-xs-3'><a href='#'  title = '"+array_value['file_name']+"' data-id = '"+array_value['file_path']+"' class='Open_tab_preview btn btn-success btn-xs btn-block'><i class='fa fa-eye'></i> attachement "+attachement_num+" </a></ul>");
                        
                            attachement_num++;
                        });

                        $('#view_attachement_modal').modal('show');
                        $('.batch_ticket_title').text("Batch No.: "+ batch_ticket);
                        $('.seed_title').text("Seed Tag: "+ seed_tag);
                        $('#batch_no').val(batch_ticket);
                        $('#seed_tag_no').val(seed_tag);
        
                    }
                });
        });

        $('body').on('click', '.view_per_batch_modal', function(e){ 
            // $('#status_passed').attr('checked', true); 
            // $('.checK_status').attr('checked', false); 
            $('#seed_tag_no').val("");
            $("#processor_remarks").val("");
            $('.table_attachement').empty();
            $("#batch_type_no").val("");
            var batch_type =$(this).attr("batch-type");
            var batch_ticket =$(this).attr("data-id");
            var batch_name =$(this).attr("data-batch-type-name");
            
            $.ajax({
                    type: 'POST',
                    url: "{{ route('processor.attachements.batch') }}",
                    data: {
                    _token: "{{ csrf_token() }}",
                    batch_type: batch_type,
                    batch_ticket: batch_ticket  
                    },
                    success: function(data){
                    var attachement_num = 1;
                        
                        jQuery.each(data, function(index, array_value){
                            
                            $(".table_attachement").append("<ul class = 'col-xs-3'><a href='#'  title = '"+array_value['file_name']+"' data-id = '"+array_value['file_path']+"' class='Open_tab_preview btn btn-success btn-xs btn-block'><i class='fa fa-eye'></i> attachement "+attachement_num+" </a></ul>");
                        
                            attachement_num++;
                        });

                        $('#view_attachement_modal').modal('show');
                        $('.batch_ticket_title').text("Batch No.: "+ batch_ticket);
                        $('.seed_title').text(batch_name);
                        $('#batch_no').val(batch_ticket);
                        $('#batch_type_no').val(batch_type);
                        
                        // $('#seed_tag_no').val(seed_tag);

                    }
                });

        });

        function load_coop_deliveries(current_moa){
            $('#region_select').empty().append($('<option>', {value:"", text:"Please select a region"}));
            $('#province_select').empty().append($('<option>', {value:"", text:"Please select a province"}));
            $('#municipality_select').empty().append($('<option>', {value:"", text:"Please select a municipality"}));
            $('#daterange-btn').html('<span><i class="fa fa-calendar"></i> Select Date</span>');    
            $(".accordion_data").empty();
            $("#10_coop").css("display", "none");
            $('.form-group').removeAttr('disabled');
            $('.select_class').removeAttr('disabled');
            $("#cards_content").css("display", "inline");
            
            HoldOn.open(holdon_options); 
            HoldOn.close(); 

            $.ajax({
                type: 'POST',
                url: "{{ route('processor.load.sg.deliveries') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    current_moa: current_moa
                },
                success: function(data){
                    if(data.data.length < 1 ){
                            alert('no record found');
                        }
                    

                    jQuery.each(data.sg, function(index, array_value1){   
                        $('#coop_name_title').text(array_value1['coopName']);
                        $('#moa_hidden').val(array_value1['current_moa']);
                        $(".accordion_data").empty();                        
                    });
                    var region_data ="";
                    jQuery.each(data.data, function(index, array_value){  

                        var statustmp;
                        if(array_value['batch_status'] == undefined){
                            statustmp = '<span class="label label-warning status_batch"> Incomplete Attachements</span>';
                        }if(array_value['batch_status'] == 0){
                            statustmp= '<span class="label label-warning status_batch"> Incomplete Attachements</span>';
                        } if(array_value['batch_status'] == 1){
                            statustmp= '<span class="label label-secondary status_batch"> Paid</span>';
                        } if(array_value['batch_status'] == 2){
                            statustmp= '<span class="label label-success status_batch"> DV Created</span>';
                        } if(array_value['batch_status'] == 3){
                            statustmp= '<span class="label label-info status_batch"> For DV Processing</span>';
                        } if(array_value['batch_status'] == 4){
                            statustmp= '<span class="label label-primary status_batch"> Complete Attachements/Ready for Assesment</span>';
                        } if(array_value['batch_status'] == 5){
                            statustmp= '<span class="label label-danger status_batch"> Failed /For Re-upload</span>';
                        }
                    
                        $(".accordion_data").append('<div class="card" > '+                                          
                                        '       <div class="card-header" id="headingOne">'+
                                        '         <h5 class="mb-0" style="margin:0" >'+
                                        '             <input type="checkbox" class="checkbox_all form-group check_all" id="check_'+array_value['batchTicketNumber']+'" name = "selected_batch" value="'+array_value['batchTicketNumber']+'" >'+
                                        '             <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">'+
                                        '                 <label for="check_'+array_value['batchTicketNumber']+'" class="pull-left">Batch Number: '+array_value['batchTicketNumber']+' > '+array_value['sum_total_bags']+' bags > Delivery Date: '+array_value['dateCreated_new']+' </label>'+statustmp+'<br>'+
                                        '                 <p class="pull-left">Location:'+array_value['region']+' > '+array_value['province']+' > '+array_value['municipality']+' </p>'+
                                        '             </button>'+
                                        '             <i class="plus_click pull-right fa fa-plus "  data-id = "'+array_value['batchTicketNumber']+'" id="icon3_id_'+array_value['batchTicketNumber']+'" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse3'+array_value['batchTicketNumber']+'" aria-controls="'+array_value['batchTicketNumber']+'"></i>'+
                                        '         </h5>'+
                                        '     </div>'+
                                        '     <div id="collapse3'+array_value['batchTicketNumber']+'" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">'+
                                        '         <div class="card-body">'+
                                        '             <div class="row">'+
                                        '                 <div class="col-md-9">'+
                                        '                     <ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="'+array_value['batchTicketNumber']+'"></ul>'+
                                        '                 </div>'+
                                        '                 <div class="col-md-3">'+
                                        '                     <div class="row">'+
                                        '                         <div class="col-md-11">'+
                                        '                               <div class="row 3_button_'+array_value['batchTicketNumber']+'" style="display: none"></div><br><br><br>'+            
                                        // '                               <div class="row Send_to_Ces_btn" style="display:inline"></div>'+
                                        '                         </div>'+
                                        '                    </div>     '+
                                        '                 </div>'+
                                        '             </div> '+
                                        '         </div>'+
                                        '     </div>'+
                                        ' </div>');
                                    
                                    if(region_data != array_value['region']){           
                                                region_data = array_value['region'];
                                                var tmp=0;
                                                $("#region_select option").each(function()
                                                {
                                                if($(this).val() == region_data){
                                                    tmp++;
                                                }
                                                });
                                                if(tmp==0){
                                                    $('#region_select').append($('<option>', {value:region_data, text:region_data}));
                                                }
                                        } 
                
                });     
                }
            });  

        }

    function forAssesment(batchTicketNumber,list_id){
        $(".3_button_"+batchTicketNumber).empty().css("display", "inline");
        $("#icon3_id_"+batchTicketNumber).toggleClass('fa-plus fa-minus');
        $("#"+list_id).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
        $.ajax({
            type: 'POST',
            url: "{{ route('processor.load.for_assesment') }}",
            data: {
                _token: "{{ csrf_token() }}",
                batch_ticket: batchTicketNumber
            },
            success: function(data){
                $("#"+list_id).empty();
                //header
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>Seed Tag</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>Seed Variety</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><strong>Volume</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><strong>Image</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>Remarks(DRO)</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>Remarks(CES)</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>Status</strong></li>");

                var count = 0;
            
                jQuery.each(data.final, function(index, array_value){
                    count = count + 1;
                    //body
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>"+array_value['seed_tag']+"</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>"+array_value['variety']+" bag(s)</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><strong>"+array_value['volume']+" bag(s)</strong></li>");
                    if(array_value['path'] ==''){
                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><strong>n/a</strong></li>");
                    }else{
                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><strong><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-success btn-xs btn-block' ><i class='fa fa-eye'></i> </a> </strong></li>");
                    }
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>"+array_value['remarks_dro']+"</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong>"+array_value['remarks_CES']+"</strong></li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><strong><p style='text-align:center; ' class="+array_value['stat_color']+" > "+array_value['status']+" </p></strong></li>");

                });


                $(".3_button_"+batchTicketNumber).empty();

                if(data.batch_stat.attach1 == 1 && data.batch_stat.attach1_status == 1){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Inspection & Acceptance Report" batch-type = "1" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-success btn-xs btn-block" ><i class="fa fa-eye"> Inspection & Acceptance Report</i></a>'); 
                }
                if(data.batch_stat.attach1 == 1 && data.batch_stat.attach1_status == 2){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Inspection & Acceptance Report" batch-type = "1" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-danger btn-xs btn-block" ><i class="fa fa-eye"> Inspection & Acceptance Report</i></a>');
                }
                if(data.batch_stat.attach1 == 1 && data.batch_stat.attach1_status == 0){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Inspection & Acceptance Report" batch-type = "1" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-warning btn-xs btn-block" ><i class="fa fa-eye"> Inspection & Acceptance Report</i></a>');
                }
                // 
                if(data.batch_stat.attach2 == 1 && data.batch_stat.attach2_status == 1){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Seed Acknowledgement Report" batch-type = "2" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-success btn-xs btn-block" ><i class="fa fa-eye"> Seed Acknowledgement Report</i></a>');
                }
                if(data.batch_stat.attach2 == 1 && data.batch_stat.attach2_status == 2){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Seed Acknowledgement Report" batch-type = "2" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-danger btn-xs btn-block" ><i class="fa fa-eye"> Seed Acknowledgement Report</i></a>');
                }
                if(data.batch_stat.attach2 == 1 && data.batch_stat.attach2_status == 0){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Seed Acknowledgement Report" batch-type = "2" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-warning btn-xs btn-block" ><i class="fa fa-eye"> Seed Acknowledgement Report</i></a>');
                }
                //
                if(data.batch_stat.attach3 == 1 && data.batch_stat.attach3_status == 1){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Delivery Receipt/Sales Invoice" batch-type = "3" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-success btn-xs btn-block" ><i class="fa fa-eye"> Delivery Receipt/Sales Invoice</i></a>');
                }
                if(data.batch_stat.attach3 == 1 && data.batch_stat.attach3_status == 2){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Delivery Receipt/Sales Invoice" batch-type = "3" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-danger btn-xs btn-block" ><i class="fa fa-eye"> Delivery Receipt/Sales Invoice</i></a>');
                }
                if(data.batch_stat.attach3 == 1 && data.batch_stat.attach3_status == 0){
                    $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Delivery Receipt/Sales Invoice" batch-type = "3" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-warning btn-xs btn-block" ><i class="fa fa-eye"> Delivery Receipt/Sales Invoice</i></a>');
                }
            }
        });
    }
  
  
  $("#region_select").on("change", function(e){
        var region = $(this).val();
        if (region !=0){
            $('.filter_data_btn').removeAttr('disabled');
        }else{
            $('.filter_data_btn').attr('disabled','disabled');
        }

        $("#province_select").empty().append("<option value=''>Loading provinces please wait...</option>");
        $("#municipality_select").empty().append("<option value=''>Please select a municipality</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_summary.provinces') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region
            },
            success: function(data){
                $("#province_select").empty().append("<option value=''>Please select a province</option>");
                $("#province_select").append(data);
            }
        });
    });


    $("#province_select").on("change", function(e){
        var region = $("#region_select").val();
        var province = $(this).val();

        $("#municipality_select").empty().append("<option value=''>Loading municipalities please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_summary.municipalities') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region,
                province: province
            },
            success: function(data){
                $("#municipality_select").empty().append("<option value=''>Please select a municipality</option>");
                $("#municipality_select").append(data);
            }
        });
    });


    $('#month').change(function() {
        $('#daterange-btn').html('<span>' +
                            '<i class="fa fa-calendar"></i> Select Date'+
                            '</span>'+
                        '<i class="fa fa-caret-down"></i>');
        $('#start_date').val("");
        $('#end_date').val("");
        var month = $(this).val();
        
        search(myChart, json_url);
    });

    var year = new Date().getFullYear();
    //Select Date
    $('#daterange-btn').daterangepicker({
            /*ranges: {
            //     'Today': [moment(), moment()],
            //     'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            //     'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            //     'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            //     'This Month': [moment().startOf('month'), moment().endOf('month')],
            //     'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            //     'This Year': [moment().startOf('year'), moment().endOf('year')],
            //     'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            // },*/
            ranges: {
               'January': [moment(year + '-01').startOf('month'), moment(year + '-01').endOf('month')],
                'February': [moment(year + '-02').startOf('month'), moment(year + '-02').endOf('month')],
                'March': [moment(year + '-03').startOf('month'), moment(year + '-03').endOf('month')],
                'April': [moment(year + '-04').startOf('month'), moment(year + '-04').endOf('month')],
                'May': [moment(year + '-05').startOf('month'), moment(year + '-05').endOf('month')],
                'June': [moment(year + '-06').startOf('month'), moment(year + '-06').endOf('month')],
                'July': [moment(year + '-07').startOf('month'), moment(year + '-07').endOf('month')],
                'August': [moment(year + '-08').startOf('month'), moment(year + '-08').endOf('month')],
                'September': [moment(year + '-09').startOf('month'), moment(year + '-09').endOf('month')],
                'October': [moment(year + '-10').startOf('month'), moment(year + '-10').endOf('month')],
                'November': [moment(year + '-11').startOf('month'), moment(year + '-11').endOf('month')],
                'December': [moment(year + '-12').startOf('month'), moment(year + '-12').endOf('month')],
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function(start, end) {
            $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
            $('#start_date').val(start.format('YYYY-MM-DD'))
            $('#end_date').val(end.format('YYYY-MM-DD'))
            $('#fromDate').val(start.format('MMMM D'))
            $('#toDate').val(end.format('MMMM D'))

            if(start != ""){
                $('.filter_data_btn').removeAttr('disabled');
            }
        }
    )


    $("#submit_remarks_btn").on("click", function(e){
        var current_moa = $("#coop").val();
        var batch_num = $("#batch_no").val();
        var seed_tag = $("#seed_tag_no").val();
        var batch_type = $("#batch_type_no").val();
        var remarks = $("#processor_remarks").val();
        var status ;
        var status_word ;

        if ($('#status_failed').is(":checked"))
            {
            status = 2;
            status_word = 'Failed/Reupload';
            }
        if ($('#status_passed').is(":checked"))
            {
            status = 1;
            status_word = 'Passed';
            }
  
        $("#status_1").text("Update Attachement Status to " +status_word+ "?"); 
        $('#view_attachement_modal').modal('hide');
        $('#processor_confirmation_modal').modal('show');
            $("#yup").on("click", function(e){
                    HoldOn.open(holdon_options); 
                    $.ajax({
                    type: 'POST',
                    url: "{{ route('processor.update_status') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        seed_tag: seed_tag,
                        batch_num: batch_num,
                        remarks: remarks,
                        status: status,
                        batch_type: batch_type
                        },
                        success: function(data){
                            // alert('Status Successfully Updated')
                            $('#processor_confirmation_modal').modal('hide');
                                if(current_moa!=''){
                                    $( "#load_coop_btn" ).trigger( "click" );
                                }else{
                                    location.reload();
                                }
                            HoldOn.close();                           
                        }
                    });
                    
                });  

    });

    $("#reset_filter").on("click", function(e){
        location.reload();
    });


    </script>
  
@endpush
