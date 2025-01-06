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

.status_batch{
    margin-left: 5px;
}


  </style>


<style>

</style>
@endsection

@section('content')


    {{-- <div class="row">
        <div class="page-title">
            <div class="title_left">
                <h3> DRO </h3>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-9">
            
            <div class="tab">
                <button class="tablinks" onclick="openTab(event, '1')" >Not yet delivered</button>
                <button class="tablinks" onclick="openTab(event, '2')">No inspection report</button>
                <button class="tablinks" onclick="openTab(event, '3')" id="defaultOpen">For assessment</button>
                <button class="tablinks" onclick="openTab(event, '4')">For DV Processing</button>
                <button class="tablinks" onclick="openTab(event, '5')">Payments made</button>
            </div>
            <div id="1" class="tabcontent">
                <div class="row">
                    <div class="col-md-12">
                            <div class="x_content">
                               {{-- content --}}
                               {{-- <div class="accordion">
                                @foreach ($data as $row)
                                    <div class="card" >
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0" style="margin:0" >
                                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                    <label class="pull-left">Batch Number: {{$row->batchTicketNumber}} > {{$row->totalBagCount}} bags > Delivery Date:  {{$row->dateCreated_new}} > </label>
                                                        @if(!isset($row->status))
                                                            <span class="label label-warning status_batch" id=""> Incomplete Attachements</span>
                                                        @elseif($row->status == 0)
                                                            <span class="label label-warning status_batch" id=""> Incomplete Attachements</span>
                                                        @elseif($row->status == 1)
                                                            <span class="label label-secondary status_batch" id=""> Paid</span>
                                                        @elseif($row->status == 2)
                                                            <span class="label label-success status_batch" id="">DV Created</span>
                                                        @elseif($row->status == 3)
                                                            <span class="label label-info status_batch" id=""> For DV Processing</span>
                                                        @elseif($row->status == 4)
                                                            <span class="label label-primary status_batch" id="">  Complete Attachements</span>
                                                        @elseif($row->status == 5)
                                                            <span class="label label-danger status_batch" id=""> Rejected/For Re-upload</span>
                                                        
                                                        @endif<br>
                                                    <p class="pull-left">Coop Name: {{$row->coopName}}</p><br><br>
                                                    <p class="pull-left">Location: {{$row->region}} > {{$row->province}} > {{$row->municipality}}</p>
                                                </button>
                            
                                                <i class="fa fa-plus pull-right" id="icon3_id1_{{$row->batchTicketNumber}}" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse3{{$row->batchTicketNumber}}" aria-controls="{{$row->batchTicketNumber}}" onclick="forAssesment('{{$row->batchTicketNumber}}','list_{{$row->batchTicketNumber}}')"></i>
                                            </h5>
                                        </div>
                                        <div id="collapse3{{$row->batchTicketNumber}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="list_{{$row->batchTicketNumber}}"></ul>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="row">
                                                            <div class="col-md-9">
                                                                <button target="_blank" data-toggle='modal' data-id = "{{$row->batchTicketNumber}}" class="Inspection_acceptance btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Inspection & Acceptance Report</button>
                                                                <button target="_blank" data-toggle='modal' data-id = "{{$row->batchTicketNumber}}" class="Seed_Acknowledgement btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Seed Acknowledgement Report&nbsp;</button>
                                                                <button target="_blank" data-toggle='modal' data-id = "{{$row->batchTicketNumber}}" class="Delivery_Receipt_Invoice btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Delivery Receipt/Sales Invoice&nbsp;&nbsp;&nbsp;</button>
                                                            </div>
                            
                                                            <div class="col-md-3 3_button1_{{$row->batchTicketNumber}}" style="display: none"></div>
                                          
                                                       </div> 
                                                       <br><br><br><br><br>
                                                       
                                                       <div class="row">
                                                        <div class="col-md-9 Send_to_Ces_btn1_{{$row->batchTicketNumber}}" style="display: none"></div>
                                                    </div>    
                                                    </div>
                                                    
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div> --}}

                               {{-- content --}}
                            </div>
                    </div>
                </div>
            </div>
            
            <div id="2" class="tabcontent">
                <div class="row">
                    <div class="col-md-12">
                            <div class="x_content">
                                {{-- content --}}
                            </div>
                    </div>
                </div> 
            </div>

            
            
            <div id="3" class="tabcontent">
                
                <div class="row"  id="cards_content" style="display:none;">
                    <div class="x_panel cards_content" >
                        <h2 id="coop_name_title" class="coop_name_title"></h2>
                        <div class="accordion accordion_data"></div>
                    </div>    
                </div>

                
                
                <div class="row tab_content_row" style="display: inline">

                
                    <div class="col-md-12">
                            <div class="x_content">
                                
                                <div class="accordion">
                                    @foreach ($data as $row)
                                        <div class="card" >
                                            <div class="card-header" id="headingOne">
                                                <h5 class="mb-0" style="margin:0" >
                                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                        <label class="pull-left">Batch Number: {{$row->batchTicketNumber}} > {{$row->sum_total_bags}} bags > Delivery Date:  {{$row->dateCreated_new}} > </label>
                                                            @if(!isset($row->batch_status))
                                                                <span class="label label-warning status_batch" id=""> Incomplete Attachements</span>
                                                            @elseif($row->batch_status == 0)
                                                                <span class="label label-warning status_batch" id=""> Incomplete Attachements</span>
                                                            @elseif($row->batch_status == 1)
                                                                <span class="label label-secondary status_batch" id=""> Paid</span>
                                                            @elseif($row->batch_status == 2)
                                                                <span class="label label-success status_batch" id="">DV Created</span>
                                                            @elseif($row->batch_status == 3)
                                                                <span class="label label-info status_batch" id=""> For DV Processing</span>
                                                            @elseif($row->batch_status == 4)
                                                                <span class="label label-primary status_batch" id="">  Complete Attachements</span>
                                                            @elseif($row->batch_status == 5)
                                                                <span class="label label-danger status_batch" id=""> Rejected/For Re-upload</span>
                                                            
                                                            @endif<br>
                                                        <p class="pull-left">Coop Name: {{$row->coopName}}</p><br><br>
                                                        <p class="pull-left">Location: {{$row->region}} > {{$row->province}} > {{$row->municipality}}</p>
                                                    </button>

                                                    <i class="fa fa-plus pull-right" id="icon3_id_{{$row->batchTicketNumber}}" style="margin-top: 12px;margin-right: 10px;" data-toggle="collapse" data-target="#collapse3{{$row->batchTicketNumber}}" aria-controls="{{$row->batchTicketNumber}}" onclick="forAssesment('{{$row->batchTicketNumber}}','list_{{$row->batchTicketNumber}}')"></i>
                                                </h5>
                                            </div>
                                            <div id="collapse3{{$row->batchTicketNumber}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin-top: .5vw;">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-9">
                                                            <ul class="list-group row" style="width: 97%;margin-left: 1vw;" id="list_{{$row->batchTicketNumber}}"></ul>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="row">
                                                                <div class="col-md-9">
                                                                    <button target="_blank" data-toggle='modal' data-id = "{{$row->batchTicketNumber}}" class="Inspection_acceptance btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Inspection & Acceptance Report</button>
                                                                    <button target="_blank" data-toggle='modal' data-id = "{{$row->batchTicketNumber}}" class="Seed_Acknowledgement btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Seed Acknowledgement Report&nbsp;</button>
                                                                    <button target="_blank" data-toggle='modal' data-id = "{{$row->batchTicketNumber}}" class="Delivery_Receipt_Invoice btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Delivery Receipt/Sales Invoice&nbsp;&nbsp;&nbsp;</button>
                                                                </div>

                                                                <div class="col-md-3 3_button_{{$row->batchTicketNumber}}" style="display: none"></div>
                                              
                                                           </div> 
                                                           <br><br><br><br><br>
                                                           
                                                           <div class="row">
                                                            <div class="col-md-9 Send_to_Ces_btn_{{$row->batchTicketNumber}}" style="display: none"></div>
                                                        </div>    
                                                        </div>
                                                        
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <!--  -->
            <div style="display:none" id="alert_na">
                    <div class="alert alert-warning alert-dismissible fade in" role="alert">
                       <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> -->
                        <strong><i class="fa fa-info-circle"></i> Notice!</strong><b><u> No Records Found</u></b>
                    </div>
                </div>
                <!--  -->
            <div id="4" class="tabcontent">
                <div class="row">
                    <div class="col-md-12">
                            <div class="x_content">
                                {{-- content  --}}
                            </div>
                    </div>
                </div>
            </div>
            <div id="5" class="tabcontent">
                <div class="row">
                    <div class="col-md-12">
                            <div class="x_content">
                                {{-- content --}}
                            </div>
                    </div>
                </div>
            </div>
   
        </div>

        <div class="col-md-3"  style="display:inline;">
            <div class="x_panel">
                <div class="x_title">
                    <h2>FIlters</h2><br><p>(Please Select Coop First)</p><br>
 
                        <button class="btn btn-warning form-control" id="reset_filter"><i class="fa fa-refresh"></i> Reset Filter</button><br>

                    <div class="clearfix"></div>
                    
                </div>
                <div class="x_content form-horizontal form-label-left"> 

                    <label for="">Filter By Seed Cooperative:</label>
                    <select name="coop" id="coop" style="width: 100%;" class="form-control">
                        
                        <option value= "">*Select Coop...</option>
                       @foreach ($select2_data as $item)
    
                           <option value="{{$item->current_moa}}">{{$item->coopName}}</option>
                       @endforeach
                    </select>
                    <hr>
                    {{-- <div class="input-group"> --}}
                        <label for="">Filter By Delivery Date:</label>
                        <button type="button" class="btn btn-default form-control select_class date2" id="daterange-btn" disabled>
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
                    {{-- </div> --}}

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
                    <select class="form-control select_class" id="region_select" name="region_select" style="margin-bottom:10px;" disabled>
                        <option value="">Please select a region</option>
                    </select>
    
                    <select class="form-control select_class" id="province_select" name="province_select" style="margin-bottom:10px;" disabled>
                        <option value="">Please select a province</option>
                    </select>
    
                    <select class="form-control select_class" id="municipality_select" name="municipality_select" style="margin-bottom:10px;" disabled> 
                        <option value="">Please select a municipality</option>
                    </select>
                    
                    
                    <br><button class="btn btn-success form-control" id="filter_btn" disabled><i class="fa fa-bar-chart-o"></i> Filter Data</button>
                </div>
            </div>
        </div>

        <!-- Upload MODAL batch 1  Inspection & Acceptance Report----------------------------------------------------->
            <div id="uplaod_image_batch" class="modal fade" role="dialog" >
                <div class="modal-dialog" style="width: 60%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                            <h4 class="modal-title">
                                <span id="is_batch_type_code"></span><br>
                                <span id="batchDataMod"></span><br> 
                            </h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <form action="" method="POST" id="upload_form" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="document">Documents</label>
                                            <div class="needsclick dropzone" id="document-dropzone2"></div>
                                        </div>
                                        <div>
                                            <input type="hidden" name="" id="batch_number_upload1" >
                                            <input type="hidden" name="" id="is_batch_type" >
                                            <input type="hidden" name="" id="is_batch" >
                                            <input type="hidden" name="" id="seed_tag_number_field" >
                                            <input type="hidden" name="" id="is_seed_tag" ><br>
                                            <label class="col-xs-4" for="remarks1">Remarks(Optional):</label>
                                            <textarea style='width:100%;margin-top: 10px;' class='form-control' id = 'remarks1' rows='3'></textarea>
                                            <br><br>
                                            <div class="row pull-right">
                                                <button class="btn btn-warning" name="" data-dismiss="modal"> <i class="fa fa-ban" ></i> cancel</button>
                                                <button type = "button" class="btn btn-success" name="" id="btn_upload"> <i class="fa fa-check" ></i> submit</button>
                                            </div>
                                            
                                        </div>
                                    </form>
                                </div>
                            </div>       
                        </div>
                    </div>
                </div>
            </div>
        <!-- Upload MODAL END------------------------------------------------------------------------------------->


        <div id="send_to_ces_modal" class="modal fade" role="dialog" >
            <div class="modal-dialog" style="width: 30%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 id="batch_submit" class="" >
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="" method="POST" id="upload_form" enctype="multipart/form-data">
                                    <div>
                                        <input type="hidden" name="" id="batch_number_update_stat" >
                                    </div>
                                    <div class="pull-right">
                                        <button class="btn btn-warning" name="" data-dismiss="modal"> <i class="fa fa-ban" ></i> cancel</button>
                                        <button type = "button" class="btn btn-success" name="" id="updated_batch_status"> <i class="fa fa-check" ></i> submit</button>
                                        
                                    </div>
                                </form>
                            </div>
                        </div>       
                    </div>
                </div>
            </div>
        </div>
        
         <div id="view_attachement_modal" class="modal fade" role="dialog" >
            <div class="modal-dialog" style="width: 20%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">
                            <span class="batch_ticket_title"></span><br>
                            <span class="seed_title"></span><br> 
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table_attachement">
                                     
                                </div><br>                              
                            </div>
                        </div>       
                    </div>
                </div>
            </div>
        </div>

        <!-- per batch -->
        <div id="view_attachement_modal_batch" class="modal fade" role="dialog" >
            <div class="modal-dialog" style="width: 50%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">
                            <span class="batch_ticket_title"></span><br>
                            <span class="seed_title"></span><br> 
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table_attachement">
                                     
                                </div><br>                              
                            </div>
                        </div>       
                    </div>
                </div>
            </div>
        </div>
 

        <!-- Preview MODAL -->
        <div id="preview_seedtag_pic" class="modal fade" role="dialog" >
            <div class="modal-dialog" style="height:100%;width:70%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">
                            <span></span><br>
                            
                            <span id="tittle_main"></span><br>
                            <span id="batch_num_title"></span><br>
                            <span id="seed_tag_num_title"></span><br> 
                           
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            
                            <div class="col-md-12">
                                <input type="text" name="" id="batch_number_upload1_preview" >
                                <input type="text" name="" id="seed_tag_number_field_preview" >
                                <iframe src="" id="iframe_view" style="height:700px;width:100%;" title=""> </iframe>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- Preview MODAL END-->

    <!-- confirmation MODAL -->
    <div id="confirmation_modal" class="modal fade" role="dialog" >
        <div class="modal-dialog" style="height:5%;width:30%;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">
                      <span id="title_confirmation"></span><br>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7"></div>
                        <div class="col-md-5">
                            <button class="btn btn-danger" data-dismiss="modal"> <i class="fa fa-thumbs-o-down" ></i> Cancel</button>
                            <button class="btn btn-success" name="" id="btn_confirm"> <i class="fa fa-thumbs-o-up" ></i> Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- confirmation MODAL END-->
         
    </div>

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>

    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src="{{ asset('public/js/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{ asset('public/js/dropzone2/dropzone.min.js')}}"></script>

    @yield('scripts')

    <script>
      var CSRF_TOKEN = "{{csrf_token()}}";
       

    $("#reset_filter").on("click", function(e){
        location.reload();
    });


        $("#coop").select2();

        $('#coop').on('select2:select', function (e) {
            $('#filter_btn').removeAttr("disabled");
            $('#start_date').val('');
            $('#end_date').val('');
            $('#daterange-btn').html('<span><i class="fa fa-calendar"></i> Select Date</span>');
            $('#region_select').empty().append("<option value=''>Please select a region</option>"); 
            $("#province_select").empty().append("<option value='''>Please select a province</option>");
            $("#municipality_select").empty().append("<option value=''>Please select a municipality</option>"); 
            $('#is_incomplete_attachement').attr('checked', false);
            $('#is_rejected').attr('checked', false);
            $('#is_complete_attachement').attr('checked', false); 
            $('#is_for_dv').attr('checked', false);
            $('#is_dv_created').attr('checked', false);
            $('#is_paid_attachement').attr('checked', false);
            $('.attachement_status').removeAttr("disabled");
            $('.select_class').removeAttr("disabled");
            $('.date2').removeAttr("disabled");

            });

        $('body').on('click', '.plus_click', function(e){
            var batch_num =$(this).attr("data-id");
            forAssesment(batch_num,batch_num);
        });

        $("#filter_btn").on("click", function(e){
            $("#alert_na").css("display", "none");
            $(".tab_content_row").css("display", "none");
            $(".accordion_data").empty();
            
            $("#cards_content").css("display", "inline"); 
            HoldOn.open(holdon_options); 
            HoldOn.close(); 
            var current_moa = $("#coop").val();
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
            url: "{{ route('load.batch.filter') }}",
            data: {
                current_moa,
                region,
                province,
                municipality,
                start_date,
                end_date,
                attachement_status,
                _token: "{{ csrf_token() }}"
            },
            success: function(data){

                if(data.data_count == 0){
                
                    $("#cards_content").css("display", "none");
                    $("#alert_na").css("display", "inline");
                
                    
                }else{
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

                    $(".accordion_data").append('<div class="card" >  '+                                          
                                        '     <div class="card-header" id="headingOne">'+
                                        '         <h5 class="mb-0" style="margin:0" >'+
                                        '             <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align:left">'+
                                        '                 <label class="pull-left">Batch Number: '+array_value['batchTicketNumber']+' > '+array_value['sum_total_bags']+' bags > Delivery Date: '+array_value['dateCreated_new']+'</label>'+statustmp+'<br>'+
                                        '                 <p class="pull-left">Coop Name: '+array_value['coopName']+'</p><br><br>'+
                                        '                 <p style="text-align: left;" class="pull-left">Location:'+array_value['region']+' > '+array_value['province']+' > '+array_value['municipality']+' </p>'+
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
                                        '<div class="row">'+
                                        '                        <div class="col-md-9">'+
                                        '                            <button target="_blank" data-id = "'+array_value['batchTicketNumber']+'" class="Inspection_acceptance btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Inspection & Acceptance Report</button>'+
                                        '                            <button target="_blank" data-id = "'+array_value['batchTicketNumber']+'" class="Seed_Acknowledgement btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Seed Acknowledgement Report&nbsp;</button>'+
                                        '                            <button target="_blank" data-id = "'+array_value['batchTicketNumber']+'" class="Delivery_Receipt_Invoice btn btn-warning btn-xs btn-block" ><i class="fa fa-upload"></i> Delivery Receipt/Sales Invoice&nbsp;&nbsp;&nbsp;</button>'+
                                        '                        </div>'+
                                        '                        <div class="col-md-3 3_button_'+array_value['batchTicketNumber']+'" style="display: none"></div>'+
                                        '                   </div>'+
                                        '<br><br><br><br>'+
                                                        '<div class="row">'+
                                                        '    <div class="col-md-9 Send_to_Ces_btn_'+array_value['batchTicketNumber']+'" style="display: none"></div>'+
                                                        '</div>'+
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
            
                $( ".seed_tag2" ).select2({
                    ajax: { 
                    url: 'home/get-rla',
                    type: 'post',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                        _token: CSRF_TOKEN,
                        search: params.term, // search term
                        batch:batchTicketNumber
                        };
                    },
                    processResults: function (response) {
                        return {
                        results: response
                        };
                    },
                    cache: true
                    }
                }); 

                }
            });
            
            
        });

$('body').on('click', '.upload_per_seed_modal', function(e){
    $('#upload_form').trigger("reset");
    $('#document-dropzone2').trigger("reset");
    $('#is_batch_type').val("");
    $('#is_batch').val(""); 
    var seed_tag =$(this).attr("data-id");
    var batch_num =$(this).attr("data-batch_num");
    $('#batchDataMod').text("Seed Tag: "+ seed_tag); 
    $('#seed_tag_number_field').val(seed_tag);
    $('#batch_number_upload1').val(batch_num);  
    $('#is_seed_tag').val("1"); 
    $('#is_batch_type_code').text("Upload Scanned Copy of RLA");  
    $('#uplaod_image_batch').modal('show');
});

$('body').on('click', '.view_per_seed_modal', function(e){ 
    $('.table_attachement').empty();
    var seed_tag =$(this).attr("data-seed_tag");
    var batch_ticket =$(this).attr("data-batch_num");
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
                    $(".table_attachement").append(" <a href='#'  title = '"+array_value['file_name']+"' data-id = '"+array_value['file_path']+"' class='Open_tab_preview btn btn-success btn-sm btn-block'><i class='fa fa-eye'></i> view attachement "+attachement_num+" </a><br>");
                    attachement_num++;
                });

                $('#view_attachement_modal').modal('show');
                $('.batch_ticket_title').text("Batch No.: "+ batch_ticket);
                $('.seed_title').text("Seed Tag: "+ seed_tag);

   
            }
        });

});

$('body').on('click', '.view_per_batch_modal', function(e){ 
    $('.table_attachement').empty();
    var batch_type =$(this).attr("batch-type");
    var batch_ticket =$(this).attr("data-id");
    var seed_title='';
    if(batch_type == 1){
        seed_title ="Inspection & Acceptance Report";
    }if(batch_type == 2){
        seed_title ="Seed Acknowledgement Report";
    }if(batch_type == 3){
        seed_title ="Delivery Receipt Invoice";
    }

    $.ajax({
            type: 'POST',
            url: "{{ route('load.attachements.batch') }}",
            data: {
            _token: "{{ csrf_token() }}",
            batch_ticket: batch_ticket,
            batch_type: batch_type  
            },
            success: function(data){
               var attachement_num = 1;

                    $(".table_attachement").append("<li class = 'list-group-item col-xs-4' style='height:50px;'><strong>Attachement/s</strong></li>");
                    $(".table_attachement").append("<li class = 'list-group-item col-xs-4' style='height:50px;'><strong>Remarks DRO</strong></li>");
                    $(".table_attachement").append("<li class = 'list-group-item col-xs-4' style='height:50px;'><strong>Remarks Processor</strong></li>");

                     jQuery.each(data, function(index, array_value){
                    $(".table_attachement").append("<li class = 'list-group-item col-xs-4' style='height:70px;'> <a href='#'  title = '"+array_value['file_name']+"' data-id = '"+array_value['file_path']+"' class='Open_tab_preview btn btn-success btn-sm btn-block'><i class='fa fa-eye'></i> view attachement "+attachement_num+" </a></li>");
                    $(".table_attachement").append("<li class = 'list-group-item col-xs-4' style='height:70px;'>"+array_value['remarks_dro']+"</li>");
                    $(".table_attachement").append("<li class = 'list-group-item col-xs-4' style='height:70px;'>"+array_value['remarks_ces']+"</li>");
                
                    attachement_num++;
                });

                $('#view_attachement_modal_batch').modal('show');
                $('.batch_ticket_title').text("Batch No.: "+ batch_ticket);
                $('.seed_title').text(seed_title);

                
          

   
            }
        });

});
        

$('body').on('click', '.Open_tab_preview', function() {
    var path =$(this).attr("data-id");
    window.open('../'+path);    
});

$('body').on('click', '.Open_tab_preview_batch', function() {
    var path =$(this).attr("data-id");
    window.open('../'+path);    
});

$('body').on('click', '.Inspection_acceptance', function() {
    $('#is_seed_tag').val("");
    $('#seed_tag_number_field').val("");
    $('#upload_form').trigger("reset");
    $('#document-dropzone2').trigger("reset");
    var batch =$(this).attr("data-id");
    $('#batchDataMod').text("Batch No.:"+ batch); 
    $('#batch_number_upload1').val(batch); 
    $('#is_batch_type').val("1");
    $('#is_batch').val("1");  
    $('#is_batch_type_code').text("Upload: Inspection & Acceptance Report(IAR)");  
    $('#uplaod_image_batch').modal('show');
}); 

$('body').on('click', '.Seed_Acknowledgement', function() {
    $('#is_seed_tag').val("");
    $('#seed_tag_number_field').val("");
    $('#upload_form').trigger("reset");
    $('#document-dropzone2').trigger("reset");
    var batch =$(this).attr("data-id");
    $('#batchDataMod').text("Batch No.: "+ batch); 
    $('#batch_number_upload1').val(batch); 
    $('#is_batch_type').val("2");
    $('#is_batch').val("1"); 
    $('#is_batch_type_code').text("Upload: Seed Acknowledgement Report(SAR)");  
    $('#uplaod_image_batch').modal('show');

});

$('body').on('click', '.Delivery_Receipt_Invoice', function() {
    $('#is_seed_tag').val("");
    $('#seed_tag_number_field').val("");
    $('#upload_form').trigger("reset");
    $('#document-dropzone2').trigger("reset");
    var batch =$(this).attr("data-id");
    $('#batchDataMod').text("Batch No.: "+ batch); 
    $('#batch_number_upload1').val(batch); 
    $('#is_batch_type').val("3");
    $('#is_batch').val("1"); 
    $('#is_batch_type_code').text("Upload: Delivery Receipt/Sales Invoice(DR/SI)");  
    $('#uplaod_image_batch').modal('show');

});


var doc_icon = '../public/images/icons/doc.png';
var pdf_icon = '../public/images/icons/pdf.png';
var ppt_icon = '../public/images/icons/ppt.png';
var pub_icon = '../public/images/icons/pub.png';
var rar_icon = '../public/images/icons/rar.png';
var xls_icon = '../public/images/icons/xls.png';


$('body').on('click', '.Sent_to_ces', function(e){ 
    var current_moa = $("#coop").val();
    var batch_number =$(this).attr("data-id");
    var status = 4;
    $('#batch_submit').text("Submit Batch No: "+ batch_number + " for evaluation?");
    $('#batch_number_update_stat').val(batch_number); 
    $('#send_to_ces_modal').modal('show');
    $("#updated_batch_status").click(function(e) {
        HoldOn.open(holdon_options);
            $.ajax({
            type: 'POST',
            url: "{{ route('dro.update.status.overall') }}",
            data: {
            _token: "{{ csrf_token() }}",
            batch_number: batch_number,
            status: status
            },
            success: function(data){

                if(data == 1){
                    $('.Sent_to_ces').attr('disabled','disabled');
                    $('#send_to_ces_modal').modal('hide');
                
                    if(current_moa!=''){
                        $( "#filter_btn" ).trigger( "click" );
                    }else{
                        location.reload();
                    }
                alert('Status Successfully Updated');
                

                }else{
                    alert('Batch already summitted')
                    $('.Sent_to_ces').attr('disabled','disabled');
                    $('#send_to_ces_modal').modal('hide');
                }

                HoldOn.close();
      

            }
        });
    });



});
     
    var myDZ = new Dropzone("#document-dropzone2",{
        url: '{{ route('dro.storeMedia') }}',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        autoProcessQueue: false,
        addRemoveLinks: true,
        uploadMultiple: true,
        parallelUploads: 100,
        maxFiles: 100,
        acceptedFiles: '.pdf, .pub, .jpeg, .jpg, .png, .gif, .PUB, .JPEG, .JPG, .PDF, .PNG, .GIF, .tif, .TIF, .ai, .AI, .docx, .DOCX, .rar, .RAR, .xlsx',
        maxFilesize: 500000000,
        paramName: "file",
        init: function() {
                $("#btn_upload").click(function(e) {
                    myDZ.processQueue();
                    // HoldOn.open(holdon_options);
                })

                this.on("processing", function(file) {
                   
                })

                this.on('addedfile', function(file) {
                    
                    var ext = file.name.split('.').pop();
                    
                    if (ext == "pdf") {
                        $(file.previewElement).find(".dz-image img").attr("src", pdf_icon);
                    } else if (ext.indexOf("docx") != -1 || ext.indexOf("doc") != -1) {
                        $(file.previewElement).find(".dz-image img").attr("src", doc_icon);
                    } else if (ext.indexOf("xlsx") != -1 || ext.indexOf("xls") != -1) {
                        $(file.previewElement).find(".dz-image img").attr("src", xls_icon);
                    } else if (ext.indexOf("pptx") != -1 || ext.indexOf("ppt") != -1) {
                        $(file.previewElement).find(".dz-image img").attr("src", ppt_icon);
                    } else if (ext.indexOf("pub") != -1) {
                        $(file.previewElement).find(".dz-image img").attr("src", pub_icon);
                    }else if (ext.indexOf("rar") != -1) {
                        $(file.previewElement).find(".dz-image img").attr("src", rar_icon);
                    }
                  
                })
                this.on("sendingmultiple", function(data, xhr, formData) {                    
                    formData.append("remarks_dro", $("#remarks1").val());
                    formData.append("batch_number", $("#batch_number_upload1").val());
                    formData.append("is_batch_type", $("#is_batch_type").val());

                    formData.append("is_batch", $("#is_batch").val());
                    formData.append("seed_tag_number_field", $("#seed_tag_number_field").val());
                    formData.append("is_seed_tag", $("#is_seed_tag").val());

                    
                });
                this.on("successmultiple", function(files, response) {
                    var current_moa = $("#coop").val();
                    this.removeAllFiles(true); 
                    $('#upload_form').trigger("reset");
                    $('#document-dropzone2').trigger("reset");
                    alert(" successfully saved");
                    $('#uplaod_image_batch').modal('hide');
                    if(current_moa!=''){
                        $( "#filter_btn" ).trigger( "click" );

                        // $("#collapse3"+batch_number+"").load("#collapse3"+batch_number+"");

                        
                       



                        // var batch_num =$(this).attr("data-id");
                            // forAssesment(batch_number,batch_number);
                    }else{
                        // $("#collapse3"+batch_number+"").load("#collapse3"+batch_number+"");

                        location.reload();
                        // $(".accordion_data").load(location.href + ".accordion_data");
                        
                    }
                    HoldOn.close();
                
                });
                this.on("errormultiple", function(files, response) {
                    $('.send-loading').hide();
                    myDZ.removeAllFiles();
                    alert("Error: Server encountered an error. Please try again or contact your system administrator.ss");
  
                });


            }

    });


            function openTab(evt, num) {
                var i, tabcontent, tablinks;
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                document.getElementById(num).style.display = "block";
                evt.currentTarget.className += " active";
            }
            document.getElementById("defaultOpen").click();




    function forAssesment(batchTicketNumber, list_id){
        var batchTicketNumber = batchTicketNumber;
        $('#active_ticket').val(batchTicketNumber);
        $(".3_button_"+batchTicketNumber).empty().css("display", "inline");
        $(".Send_to_Ces_btn_"+batchTicketNumber).empty().css("display", "inline");
        $("#icon3_id_"+batchTicketNumber).toggleClass('fa-plus fa-minus');
        $("#icon3_id1_"+batchTicketNumber).toggleClass('fa-plus fa-minus');
        $("#"+list_id).empty().append("<li class = 'list-group-item col-xs-12'><strong>Loading data please wait...</strong></li>");
        $.ajax({
            type: 'POST',
            url: "{{ route('dro.home.for_assesment') }}",
            data: {
                _token: "{{ csrf_token() }}",
                batch_ticket: batchTicketNumber
            },
            success: function(data){

                $("#"+list_id).empty();  

                $("#"+list_id).append('<div class="row"> '+  
                    '<div class="col-xs-4"> '+ 
                        '    <select name="" id="seed_tag_val_'+batchTicketNumber+'" style="width: 100%;" class="form-control seed_tag2_'+batchTicketNumber+'">    '+
                    '        <option value= "">*Select seedtag...</option>'+
                    '    </select>'+ 
                    '</div>'+
                    '<div class="col-xs-2"> '+ 
                        '<button class="btn btn-success form-control search_seed_tag" id="btn_search_seed"><i class="fa fa-search"></i> search</button>'+
                    '</div>'+
                '</div>'+
                '<br>');
               
                $( ".seed_tag2_"+batchTicketNumber+"").select2({

                       ajax: { 
                       url: 'home/get-rla',
                       type: 'post',
                       dataType: 'json',
                       delay: 250,
                       data: function (params) {
                           return {
                           _token: CSRF_TOKEN,
                           search: params.term, // search term
                           batch:batchTicketNumber
                           };
                       },
                       processResults: function (response) {
                           return {
                           results: response
                           };
                       },
                       cache: true
                       }
   
                   });

                   $('body').on('click', '.search_seed_tag', function(e){
                      
                    var seed_tag =$('#seed_tag_val_'+batchTicketNumber+'').val();
                    // alert(seed_tag);
                    // if(seed_tag ==""){
                    //     alert('select seedtag to search');
                    // }
                    // else{
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('seedtag-search') }}",
                            data: {
                            _token: "{{ csrf_token() }}",
                            seed_tag: seed_tag,
                            batchTicketNumber: batchTicketNumber  
                            },
                            success: function(data){
                            // var attachement_num = 1;
                            $("#"+list_id).empty();
                            $("#seed_tag_val").val('');

                            $("#"+list_id).append('<div class="row"> '+  
                                '<div class="col-xs-4"> '+ 
                                    '    <select name="coop" id="seed_tag_val_'+batchTicketNumber+'" style="width: 100%;" class="form-control seed_tag2_'+batchTicketNumber+'">    '+
                                '        <option value= "">*Select seedtag...</option>'+
                                '    </select>'+ 
                                '</div>'+
                                '<div class="col-xs-2"> '+ 
                                    '<button class="btn btn-success form-control search_seed_tag" id="btn_search_seed"><i class="fa fa-search"></i> search</button>'+
                                '</div>'+
                            '</div>'+
                            '<br>');

                            $( ".seed_tag2" ).select2({
                                
                                ajax: { 
                                url: 'home/get-rla',
                                type: 'post',
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                    _token: CSRF_TOKEN,
                                    search: params.term, // search term
                                    batch:batchTicketNumber
                                    };
                                },
                                processResults: function (response) {
                                    return {
                                    results: response
                                    };
                                },
                                cache: true
                                }
            
                            });

                            //header
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Seed Tag</strong></li>");
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Seed Variety</strong></li>");
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:50px;'><strong>Volume</strong></li>");
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:50px;'><strong>Image</strong></li>");
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Remarks(DRO)</strong></li>");
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Remarks(CES)</strong></li>");
                                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Status</strong></li>");
                              
                                var count = 0;
                                    jQuery.each(data, function(index, array_value){
                                        count = count + 1;
                                    
                                        $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['seed_tag']+"</li>");
                                        $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['variety']+"</li>");
                                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'>"+array_value['volume']+" bag(s)</li>");
                                        if(array_value['path'] ==''){
                                            $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'> <a title='upload attachements' href='#' target='_blank' data-toggle='modal' data-batch_num = "+array_value['batch_number']+" data-id = "+array_value['seed_tag']+" class='upload_per_seed_modal btn btn-warning btn-xs btn-block' ><i class='fa fa-upload'></i></a></li>");
                                        }else if (array_value['path'] !='' && array_value['status'] == 'Passed') {
                                            $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-success btn-xs btn-block' ><i class='fa fa-eye'></i> </a></li>"); 
                                        
                                        }else if (array_value['path'] !='' && array_value['status'] == 'Failed') {
                                            $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-danger btn-xs btn-block' ><i class='fa fa-eye'></i> </a> <a title='upload attachements' 'href='#' target='_blank' data-toggle='modal' data-batch_num = "+array_value['batch_number']+" data-id = "+array_value['seed_tag']+" class='upload_per_seed_modal btn btn-danger btn-xs btn-block' ><i class='fa fa-upload'></i></a></li>"); 
                                        }
                                        else{
                                            $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-warning btn-xs btn-block' ><i class='fa fa-eye'></i> </a> <a title='upload attachements' 'href='#' target='_blank' data-toggle='modal' data-batch_num = "+array_value['batch_number']+" data-id = "+array_value['seed_tag']+" class='upload_per_seed_modal btn btn-warning btn-xs btn-block' ><i class='fa fa-upload'></i></a></li>");
                                        }
                                        $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['remarks_dro']+"</li>");
                                        $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['remarks_CES']+"</li>");
                                        $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><p style='text-align:center; ' class="+array_value['stat_color']+" > "+array_value['status']+" </p></li>");
                                        
                                    });

                               

                
                            }
                        });

                    // }
                    

                    });
                


                //header
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Seed Tag</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Seed Variety</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:50px;'><strong>Volume</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:50px;'><strong>Image</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Remarks(DRO)</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Remarks(CES)</strong></li>");
                $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:50px;'><strong>Status</strong></li>");

                var count = 0;
                jQuery.each(data.final, function(index, array_value){
                    count = count + 1;
                   
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['seed_tag']+"</li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['variety']+"</li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'>"+array_value['volume']+" bag(s)</li>");
                    if(array_value['path'] ==''){
                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'> <a title='upload attachements' href='#' target='_blank' data-toggle='modal' data-batch_num = "+array_value['batch_number']+" data-id = "+array_value['seed_tag']+" class='upload_per_seed_modal btn btn-warning btn-xs btn-block' ><i class='fa fa-upload'></i></a></li>");
                    }else if (array_value['path'] !='' && array_value['status'] == 'Passed') {
                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-success btn-xs btn-block' ><i class='fa fa-eye'></i> </a></li>"); 
                    
                    }else if (array_value['path'] !='' && array_value['status'] == 'Failed') {
                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-danger btn-xs btn-block' ><i class='fa fa-eye'></i> </a> <a title='upload attachements' 'href='#' target='_blank' data-toggle='modal' data-batch_num = "+array_value['batch_number']+" data-id = "+array_value['seed_tag']+" class='upload_per_seed_modal btn btn-danger btn-xs btn-block' ><i class='fa fa-upload'></i></a></li>"); 
                    }
                    else{
                        $("#"+list_id).append("<li class = 'list-group-item col-xs-1' style='height:70px;'><a title='view attachements' href='#' target='_blank' data-toggle='modal' data-image = "+array_value['path']+" data-batch_num = "+array_value['batch_number']+" data-seed_tag = "+array_value['seed_tag']+" class='view_per_seed_modal btn btn-warning btn-xs btn-block' ><i class='fa fa-eye'></i> </a> <a title='upload attachements' 'href='#' target='_blank' data-toggle='modal' data-batch_num = "+array_value['batch_number']+" data-id = "+array_value['seed_tag']+" class='upload_per_seed_modal btn btn-warning btn-xs btn-block' ><i class='fa fa-upload'></i></a></li>");
                    }
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['remarks_dro']+"</li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'>"+array_value['remarks_CES']+"</li>");
                    $("#"+list_id).append("<li class = 'list-group-item col-xs-2' style='height:70px;'><p style='text-align:center; ' class="+array_value['stat_color']+" > "+array_value['status']+" </p></li>");
                    
                });
                  
                    if(data.batch_stat.attach1 == 1 && data.batch_stat.attach1_status == 1 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Inspection & Acceptance Report" batch-type = "1" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-success btn-xs btn-block" ><i class="fa fa-eye"> </i></a>'); 
                    }
                    if(data.batch_stat.attach1 == 1 && data.batch_stat.attach1_status == 2 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Inspection & Acceptance Report" batch-type = "1" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-danger btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    if(data.batch_stat.attach1 == 1 && data.batch_stat.attach1_status == 0 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Inspection & Acceptance Report" batch-type = "1" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-warning btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    // 
                    if(data.batch_stat.attach2 == 1 && data.batch_stat.attach2_status == 1 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Seed Acknowledgement Report" batch-type = "2" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-success btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    if(data.batch_stat.attach2 == 1 && data.batch_stat.attach2_status == 2 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Seed Acknowledgement Report" batch-type = "2" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-danger btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    if(data.batch_stat.attach2 == 1 && data.batch_stat.attach2_status == 0 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Seed Acknowledgement Report" batch-type = "2" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-warning btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    //
                    if(data.batch_stat.attach3 == 1 && data.batch_stat.attach3_status == 1 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Delivery Receipt/Sales Invoice" batch-type = "3" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-success btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    if(data.batch_stat.attach3 == 1 && data.batch_stat.attach3_status == 2 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Delivery Receipt/Sales Invoice" batch-type = "3" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-danger btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }
                    if(data.batch_stat.attach3 == 1 && data.batch_stat.attach3_status == 0 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".3_button_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-batch-type-name="Delivery Receipt/Sales Invoice" batch-type = "3" data-id = "'+data.batch_stat.batch_ticket+'" class="view_per_batch_modal btn btn-warning btn-xs btn-block" ><i class="fa fa-eye"> </i></a>');
                    }

                    if(data.batch_stat.attach1 == 1 && data.batch_stat.attach2 == 1 && data.batch_stat.attach3 == 1 && data.batch_stat.batch_ticket == batchTicketNumber){
                        $(".Send_to_Ces_btn_"+batchTicketNumber).append('<a href="#" target="_blank" data-toggle="modal" data-id = "'+data.batch_stat.batch_ticket+'" class="Sent_to_ces btn btn-primary btn-xs btn-block" ><i class="fa fa-paper-plane"></i> Send to CES for Evaluation</a>');
                    }

            }
        });
    }
  

    $("#region_select").on("change", function(e){
        var region = $(this).val();

        $("#province_select").empty().append("<option value=''>Loading provinces please wait...</option>");
        $("#municipality_select").empty().append("<option value=''>Loading municipalities please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_summary.provinces') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region
            },
            success: function(data){
                $("#province_select").empty().append("<option value=''>Please select a province</option>");
                $("#municipality_select").empty().append("<option value=''>Please select a municipality</option>");
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
        // $('#filter_btn').removeAttr("disabled");
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
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            },*/
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
        }
    )

    </script>
  
@endpush
