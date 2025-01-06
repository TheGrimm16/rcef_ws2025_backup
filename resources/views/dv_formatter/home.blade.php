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

.check_all{
    /* margin-bottom: 5px; */
    /* padding-left: 10px;   */
    vertical-align: top;    
    
    position: relative;
    /* top: 7px; */
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







  </style>


<style>

</style>
@endsection

@section('content')
  
    <div class="row">

        <div class="col-md-12">
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
                            <h2>Seed Cooperatives</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                        
                            <div class="accordion">
                                @foreach ($coop_view as $coop)
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0" style="margin:0">
                                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                                    {{$coop['coopName']}} ({{$coop['acronym']}})
                                                </button>
                                            </h5>
                                            <button class="btn btn-warning btn-sm view_coop" style="top: 10%;margin-right: 10px;position: absolute;right: 0%; width: 200px;" data-moa="{{$coop['current_moa']}}" data-coop_accre="{{$coop['accreditation']}}" data-coop_name="{{$coop['coopName']}} ({{$coop['acronym']}})" data-coop_arconym="{{$coop['acronym']}}" data-full_address = "{{$coop['address']}}" data-tagged = "{{$coop['tagged_batch_count']}}" data-untagged = "{{$coop['untag_batch_count']}}" data-with_no_iar = "{{$coop['with_no_iar']}}" data-inspected_bags = "{{$coop['inspected_bags']}}" data-total_deliveries = "{{$coop['total_deliveries']}}"><i class="fa fa-list-alt"></i> Untagged Deliveries: {{$coop['untag_batch_count']}}</button>

                                            

                                        </div>
                                    </div> 
                                @endforeach
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
            {{-- end --}}



            {{--show coop details--}}

            <div class="row" id="show_coop">
                {{-- <div class="col-md-12 col-sm-12 col-xs-12"> --}}

                    <!-- UPLOAD PANEL -->
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>
                                Search Filter
                            </h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content form-horizontal form-label-left">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="region_select" id="region_select" class="form-control">
                                        {{-- <option value="0">Please select a Region</option>
                                        @foreach ($regions as $row)
                                            <option value="{{$row->region}}">{{$row->region}}</option>    
                                        @endforeach --}}
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <select name="coop_select2" id="coop_select2" class="form-control">
                                        <option value="0">Please select a Coop</option>
                                    </select>
                                </div>
            
                               
            
                                 <div class="col-md-3">
                                    <button class="btn btn-success btn-block filter_btn" id="filter_btn"><i class="fa fa-database"></i> Search</button>
                                </div>
                            </div>
                        </div>
                    </div><br>

                    

                    <div class="x_panel">
                        <div class="x_title">

                            {{-- <div class="col-md-12"> --}}
                                <div class="x_panel">
                                    <h2>Coop Profile</h2><br><br>
                                    <h2 id="coop_name_txt"></h2>
                                
                                    
                                    <br><br>
                                    
                                    <p id="coop_accre_txt"></p>
                                    <p id="coop_moa_txt"></p>
                                    <p id="coop_address_txt"></p>
                                    
                                    <input type="hidden" name="" id="active_coop_accre">
                                    <input type="hidden" name="" id="active_coop_moa">
                                    
                                </div>
                                
                            {{-- </div> --}}


                            {{-- <div class="col-md-7">
                                <div class="x_panel">
                                    <div class="">
                                    <br><br> <h1>Regional and Varietal Statistics</h1><br><br><br>
                                    </div>
                                </div>
                               
                            </div> --}}

                            {{--  --}}
                            <div class="x_panel" id="chart_section" style="display: none">
                                <div class="x_title">
                                    <h2>
                                        Delivery Status: Delivery vs Inspected & Accepted <span id="seeds_total"></span>
                                    </h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div id="container" style="width:100%; height:300px;"></div>
                                         
                                        </div>
                                        <div class="col-md-2">
                                            <div class="row tile_count">
                                                <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                                    <span class="count_top">Total Delivered</span>
                                                    <div class="count" id="total_delivered_txt">--</div>
                                                </div>
                    
                                                <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                                    <span class="count_top">Total Inspected</span>
                                                    <div class="count" id="total_inspected_txt">--</div>
                                                </div>
                                                <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                                   <button onclick="loadRegionalData(this.value);" id="switchButton" value="1" class="btn btn-success"> Switch To Region Data </button>
                                                 
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                    
                                    {{-- <div class="row">
                                        <div class="col-md-12">
                                               <div id="regional_tbl" style="display: none;">
                                                <table class="table table-hover table-striped table-bordered" id="region_table">
                                                    <thead>
                                                        <th >Region</th>
                                                        <th >Variety</th>
                                                        <th>Delivered</th>
                                                        <th>Inspected</th>
                                                    </thead>
                                                </table>  
                                            </div>
                                        </div>
                                    </div> --}}
                    
                    
                                </div>
                            </div><br>
                         


                         
                                <div class="x_panel">
                                    <div class="">
                                     <center><h1 id="total_deliveries_txt"></h1></center>
                                    </div>
                                </div>
                       


                            

                            
                            


                            <div class="clearfix"></div>
                        </div>
                 
                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Tagged Deliveries</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0;">
                                        <div class="col-md-3 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count"><i class="fa fa-flag-checkered"></i> 
                                            </div>
                                        </div>
                
                                        
                                            
                                        <div class="col-md-4" style="padding-bottom: 0;padding-left: 0;">
                                            <h1 id="tagged_count_txt"></h1>
                                            
                                        </div>

                                        <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                            <!-- <h1 id="tagged_count_txt_per"> (100%)</h1> -->
                                        </div>
                
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Untagged Deliveries</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0;">
                                        <div class="col-md-3 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count"><i class="fa fa-exclamation-triangle"></i>
                                            </div>
                                        </div>
                
                                        {{-- <div class="col-md-7 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;"> --}}
                                        <div class="col-md-4" style="padding-bottom: 0;padding-left: 0;">
                                            <h1 id="untagged_count_txt"></h1>
                                            
                                        </div>

                                        <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                            <!-- <h1 id="untagged_count_txt_per"> (100%)</h1> -->
                                        </div>
                
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>No IAR</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0;">
                                        <div class="col-md-3 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count"><i class="fa fa-file"></i> 
                                            </div>
                                        </div>

                                        


                                        <div class="col-md-4" style="padding-bottom: 0;padding-left: 0;">
                                            <h1 id="with_no_iar_txt"></h1>
                                            
                                        </div>
                
                                        <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                            <!-- <h1 id="with_no_iar_txt_per"> (100%)</h1> -->
                                        </div>
                
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Inspected and Accepted (20kg/bag)</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content form-horizontal form-label-left">
                                    <div class="row tile_count" style="margin: 0;">
                                        <div class="col-md-3 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="count"><i class="fa fa-truck"></i>
                                            </div>
                                        </div>


                                        <div class="col-md-4" style="padding-bottom: 0;padding-left: 0;">
                                            <h1 id="inspected_bags_txt"></h1>
                                            
                                        </div>

                                        <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                            <!-- <h1 id="inspected_bags_txt_per"> (100%)</h1> -->
                                        </div>
                
                                        {{-- <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                            <div class="row ml-3">
                                                <div class="col-md-12 col-sm-4 col-xs-4">
                                                    <div class="sub-count" id=""> <i class="fa fa-eye"> Inspected: 00</i>
                                                      </div>
                                                </div>
                                                <div class="col-md-12 col-sm-4 col-xs-4">
                                                    <div class="sub-count" id=""> <i class="fa fa-refresh">   Transferred (PS): 00</i>
                                                      </div>
                                                </div>
                
                                                <div class="col-md-12 col-sm-4 col-xs-4">
                                                    <div class="sub-count" id="">
                                                        <i class="fa fa-cube">   e-Binhi: 00</i> </div>
                                                </div>
                                            </div>
                                        </div> --}}
                
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="x_content form-horizontal form-label-left">
                           
                        </div>
                    </div><br>


                    

                    

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>
                                Seed Deliveries 
                            </h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content form-horizontal form-label-left">

                            <div class="row process_btn" style="display: none" id="action_button">
                                    <div class="col-md-12">
                                        <button class="btn btn-warning pull-left" id="btn_undo"><i class="fa fa-undo" ></i><span class="label label-info" id=""></span></button>
                                        <button class="btn btn-primary pull-left" id="btn_copy"><i class="fa fa-clipboard" ></i> Copy IAR Number Selected: <span class="label label-info" id="selected_count"><span class="label label-info" id="selected_count2"></span></button>
                                    </div>
                            </div>  

                            
                            
                            <table class="table table-striped table-bordered" id="coop_batch_table" style="width: 100%;">
                                <thead>
                                    <th></th>
                                    <th>IAR Number</th>
                                    <th style="width:150px;">Batch Code</th>
                                    <th>Region</th>
                                    <th>Province</th>
                                    <th>Municipality</th>
                                    <th style="width:120px;">DOP</th>
                                    <th style="width:100px;">Total Bags inspected</th>
                                    <th>Delivery date</th>
                                </thead>
                            </table>
                        </div>
                    </div><br> 
             
            
                {{-- </div> --}}
              
            </div>
           

    </div>


<!-- IAR PREVIEW MODAL -->
<div id="show_iar_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>IAR-FMIS Generated Particulars</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success alert-dismissible fade in" role="alert" id="iar_fmis_msg" style="display: none;">
                    <strong><i class="fa fa-check-circle"></i> Success!</strong> IAR-FMIS Particulars copied to clipboard
                </div>
                
                <textarea maxlength='330' name="iar_particulars" id="iar_particulars" cols="30" rows="5" class="form-control" readonly></textarea>
                <button class="btn btn-success btn-xs pull-right" id="copy_btn" title="enter DV number to copy" data-clipboard-target="#iar_particulars" style="margin-right: -1px;"><i class="fa fa-clipboard"></i> Copy to clipboard</button>
                <br><br>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="dv_number" class=""> DV Control Number: </label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="dv_number" id="dv_number" class="form-control" placeholder="0000-00-000000">
                    </div>
                </div> 
                

               
            </div>



            <div class="modal-footer">
                <button class="btn btn-success" title= "copy to clipboard to submit" disabled id="btn_submit_dv">submit</button>
            </div>
        </div>
    </div>
</div>
<!-- IAR PREVIEW MODAL -->

         
    </div>

    

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    @yield('scripts')

    <script>

$('#show_coop').hide();
$("#coop_batch_table").DataTable();

$("#coop").select2();

Highcharts.setOptions({
            lang: {
                decimalPoint: '.',
                thousandsSep: ','
            },

            tooltip: {
                yDecimals: 2 // If you want to add 2 decimals
            }
        });

   

   
     
        
        $('#coop').on('select2:select', function (e) {
            var coop_accre = $("#coop").val();
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
             
                
            }else if(!ischecked){ 
                count2--;
             
                if(count2<1){
                $("#action_button").css("display", "none");
                }
                $("#selected_count").text(count2);
            }

                var checkboxValues = [];
                var iar_number = [];
 
                $('input[name=selected_batch]:checked').map(function() {
                    checkboxValues.push($(this).val());
                });

                $('input[name=selected_batch]:checked').map(function() {
                    iar_number.push($(this).data("id"));
                });

                check_char_count(checkboxValues,iar_number,count2);  

    
        });


      

        $("#load_coop_btn").on("click", function(e){
            HoldOn.open(holdon_options)
            $("#alert_na").css("display", "none");
            var current_moa = $("#coop").val();

                $.ajax({
                type: 'POST',
                url: "{{ route('dv_formatter.get_coop.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    current_moa: current_moa
                },
                success: function(data){
                  

                    load_region();

                    load_coop_deliveries(data.current_moa, data.accreditation, data.coopName, data.address, data.tagged_batch_count, data.untag_batch_count, data.acronym, data.with_no_iar, data.inspected_bags, data.total_deliveries);

                    load_table(data.current_moa);
                  
                }
                });
                
            HoldOn.close()  
        });



        $(".view_coop").on("click", function(e){
            HoldOn.open(holdon_options)
            var current_moa =$(this).attr("data-moa");
            var accreditation =$(this).attr("data-coop_accre");
            var coop_name =$(this).attr("data-coop_name");
            var full_address=$(this).attr("data-full_address");
            var tagged_batch_count=$(this).attr("data-tagged");
            var untagged_batch_count=$(this).attr("data-untagged");
            var coop_arconym =$(this).attr("data-coop_arconym");
            var with_no_iar =$(this).attr("data-with_no_iar");
            var inspected_bags =$(this).attr("data-inspected_bags");
            var total_deliveries =$(this).attr("data-total_deliveries");

            load_coop_deliveries(current_moa,accreditation,coop_name,full_address,tagged_batch_count,untagged_batch_count,coop_arconym,with_no_iar,inspected_bags,total_deliveries);

            load_table(current_moa);
            load_region();
           HoldOn.close() 
        });

      

        

        

        function load_coop_deliveries(current_moa,accreditation,coop_name,full_address,tagged_batch_count,untagged_batch_count,coop_arconym,with_no_iar,inspected_bags,total_deliveries){

            var accreditation = accreditation;
            var current_moa = current_moa;
            var coop_name = coop_name;
            var full_address = full_address;
            var tagged_batch_count = tagged_batch_count;
            var untagged_batch_count = untagged_batch_count;
            var coop_arconym = coop_arconym;
            var with_no_iar = with_no_iar;
            var inspected_bags = inspected_bags;
            var total_deliveries = total_deliveries;

            load_coop_stat(accreditation);

            // HoldOn.open(holdon_options)
            // $("#chart_section").css("display", "none");
            $('#10_coop').css("display", "none");
            $('#select2_filter').empty().hide();

            $("#coop_name_txt").empty().text(coop_name);
            $("#coop_arconym_txt").empty().text(coop_arconym);
            $("#coop_moa_txt").empty().text("MOA Number: "+current_moa);
            $("#coop_accre_txt").empty().text("Accreditaion No: "+accreditation);
            $("#coop_address_txt").empty().text("Address "+full_address);

            $("#untagged_count_txt").empty().text(untagged_batch_count);
            $("#tagged_count_txt").empty().text(tagged_batch_count);
            $("#with_no_iar_txt").empty().text(with_no_iar);
            $("#inspected_bags_txt").empty().text(inspected_bags);
            $("#total_deliveries_txt").empty().text("Total Seed Deliveries: "+total_deliveries+" bags");
            $("#active_coop_accre").empty().val(accreditation);
            $("#active_coop_moa").empty().val(current_moa);
            

            $('#show_coop').show();


            // HoldOn.close()


        }


        function loadRegionalData(val) {
        HoldOn.open(holdon_options);
        var coop_accre = $("#active_coop_accre").val();
        

        if(val==1){
            
         $.ajax({
                    type: 'POST',
                    url: "{{ route('dv_formatter.load.region_stat') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: coop_accre
                    },
                    success: function(data){
                        console.log(data);
                        $("#container").removeAttr("style");
                        $("#container").attr("style", "width:100%; height:400px;");

                        $("#regional_tbl").removeAttr("style");
                        $("#regional_tbl").attr("style", "width:100%; height:400px;")

                        populateRegionalChart(data['region_list'],data['variety_list'],data['delivered_list'], data['inspected_list'], data['series_arr']);
                        $("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        // $("#total_delivered_txt").empty().html(data["commitment_list"]);
                        // $("#total_inspected_txt").empty().html(data["total_delivered"]);
                        
                        $("#load_btn").removeAttr("disabled");
                        $("#load_btn").empty().html('<i class="fa fa-database"></i> LOAD DASHBOARD DATA');



                        
                        HoldOn.close();
                    }
                }); 
        
         $("#switchButton").val("0");
         $("#switchButton").text("Switch to seed variety");
        }else{
        $.ajax({
                    type: 'POST',
                    url: "{{ route('dv_formatter.coop_seeds_stat') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: coop_accre
                    },
                    success: function(data){
                        $("#container").removeAttr("style");
                        $("#container").attr("style", "width:100%; height:400px;");
                        populateChart(data['variety_list'], data['delivered_list'], data['inspected_list']);
                        $("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        $("#total_delivered_txt").empty().html(data["total_delivered"]);
                        $("#total_inspected_txt").empty().html(data["total_inspected"]);

                        
                        HoldOn.close();
                    }
                });

        $("#switchButton").val("1");
        $("#switchButton").text("Switch To Region Data");
          
     }



     
    }

    function check_char_count(checkboxValues,iar_number,count2){

        var checkboxValues = checkboxValues;
        var iar_number = iar_number;
        var count = count2;

        $.ajax({
                    type: 'POST',
                    url: "{{ route('dv_formatter.particulars') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        checkboxValues: checkboxValues,
                        iar_number: iar_number
                    },
                    success: function(data){
                        
                        var char_len = data.length;
                 
                        $("#action_button").css("display", "inline");
                        $("#selected_count").text(count2+' ('+char_len+' characters )'); 
                    
  
                    }
                });

    }
    

        function load_coop_stat(accreditation){

            var accreditation = accreditation;

            $.ajax({
                    type: 'POST',
                    url: "{{ route('dv_formatter.coop_seeds_stat') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        coop_accre: accreditation
                    },
                    success: function(data){
                        populateChart(data['variety_list'], data['delivered_list'], data['inspected_list']);
                        $("#chart_section").css("display", "inline-grid");
                        //$("#seeds_total").empty().html("["+data["total_commitment"]+" vs "+data["total_delivered"]+"]")
                        $("#total_delivered_txt").empty().html(data["total_delivered"]);
                        $("#total_inspected_txt").empty().html(data["total_inspected"]);
                        
                        $("#load_btn").removeAttr("disabled");
                        $("#load_btn").empty().html('<i class="fa fa-database"></i> LOAD DASHBOARD DATA');
                    }
                });

        }


        function reload_main(accreditation){

            $("#alert_na").css("display", "none");
            $("#dv_number").val('');
            
            var current_moa = $("#active_coop_moa").val();
        
                $.ajax({
                type: 'POST',
                url: "{{ route('dv_formatter.get_coop.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    current_moa: current_moa
                },
                success: function(data){
                  

                    load_region();

                    load_coop_deliveries(data.current_moa, data.accreditation, data.coopName, data.address, data.tagged_batch_count, data.untag_batch_count, data.acronym, data.with_no_iar, data.inspected_bags, data.total_deliveries);

                    load_table(data.current_moa);
                  
                }
                });

        }


        // 
        function populateRegionalChart(regionlist, varieties, commitment, delivered, series){

            // $('#region_table').DataTable( {
            //     "bDestroy": true,
            //     "autoWidth": false,
            //     "searchHighlight": true,
            //     data: series,
            //     columns: [
            //         { "data": "region" },
            //         { "data": "variety" },
            //         { "data": "delivered" },
            //         { "data": "inspected" },
            //     ]
            // } );

                var region_list = regionlist.filter((value, index) => {
                return regionlist.indexOf(value) === index;
                });  

                var varietylist = varieties.filter((value, index) => {
                return varieties.indexOf(value) === index;
                });  

                var series_data = [];

                for (var i=0; i < varietylist.length; i++){   
                var commit = [];
                var deliver = [];



                for (var b=0; b < varieties.length; b++){       
                    if (varietylist[i] === varieties[b] ){
                        commit.push([commitment[b]]);
                        deliver.push([delivered[b]]);
                    }
                }

                    series_data.push({name: varietylist[i]+"_delivered", data:commit, color:"#81F79F",  dataLabels: {
                        format: varietylist[i]+"_delivered: "+"{point.y}",
                    }});

                    series_data.push({name: varietylist[i]+"_inspected", data:deliver, color:"#F3E2A9", dataLabels: {
                        format: varietylist[i]+"_inspected: "+"{point.y}",
                    }});
                //series_data.push({name: 'NSIC2', data:[200]});

                }


                $('#container').highcharts({
                    chart: {
                        type: 'bar'
                    },
                    title:{
                        text:''
                    },
                    xAxis: {
                            categories: region_list,
                            title: {
                                text: null
                            }
                        },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    plotOptions: {
                        bar: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                inside: true
                            },
                            showInLegend: false
                        }
                    },
                    series:series_data
                });
        }
        // 

        function populateChart(varieties,delivered, inspected){
            $('#container').highcharts({
                chart: {
                    type: 'bar'
                },
                title:{
                    text:''
                },
                xAxis: {
                    categories: varieties
                },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                series: [{
                        name: 'Delivered',
                        data: delivered
                    }, {
                        name: 'Inspected & Accepted',
                        data: inspected
                    }]
            });
        }

        function load_table(current_moa){

            var current_moa = current_moa;
                $('#coop_batch_table').DataTable().clear();
                $("#coop_batch_table").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('dv_formatter.iar_tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "current_moa": current_moa
                        }
                    },
                    "columns":[
                        
                        {"data": "select"},
                        {"data": "iar_number"},
                        {"data": "batch_code"},
                        {"data": "region"},
                        {"data": "province"},
                        {"data": "municipality"},
                        {"data": "dop"},
                        // {"data": "total_bags_delivered"},
                        {"data": "total_bags_inspected"},
                        {"data": "delivery_date"},
                        // {"data": "action"}
                    ]
                });

            }


            function getRegionDetails(index){
            $("#icon_id_"+index).toggleClass('fa-plus fa-minus');
            var region = $("#region_title_"+index).html();
            var coop_accre = $("#coop").val();
            
            $("#table_"+index).DataTable().clear();
            $("#table_"+index).DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('load.deliveries.region') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "coop_accre": coop_accre,
                        "region": region
                    }
                },
                "columns":[
                    {"data": "batchTicketNumber"}, 
                    {"data": "province"},
                    {"data": "municipality"},
                    {"data": "dropOffPoint"},
                    {"data": "seedVariety"},
                    {"data": "date_inspected"},
                ]
            });
        }



            $("#btn_copy").on("click", function(e){
                if(count2 >= 14){
                    alert('Max Characters exceed 330. Try to select maximum of 13 Deliveries');
    
                }else{

                    // $('#show_iar_modal').modal('show');
                    $('#show_iar_modal').modal({backdrop: 'static', keyboard: false}, 'show');
                    
                   var dv_number = $('#dv_number').val();

                   $("#dv_number").keyup(function(){
                        $('#btn_submit_dv').removeAttr('disabled');
                        
                    });


                }

           
            });

           

            $("#btn_undo").on("click", function(e){
                count2 = 0;
                $("#action_button").css("display", "none");
                $('input[name=selected_batch]:checked').removeAttr('checked');   
                $("#selected_count").text(count2); 
            });

    

    $("#show_iar_modal").on('show.bs.modal', function (e) {
       

        var batch_code = $(e.relatedTarget).data('iar');
        var iar_code = $(e.relatedTarget).data('iar_code');
        
        $("#dv_number").val('');
        // $('#copy_btn').attr("disabled", true);
        $('#btn_submit_dv').attr("disabled", true);
        


            var coop_accre = $("#coop_accre_txt").text();
            var checkboxValues = [];
            var iar_number = [];
            var batch_length = 0;
            $('input[name=selected_batch]:checked').map(function() {
                checkboxValues.push($(this).val());
            });

            $('input[name=selected_batch]:checked').map(function() {
                iar_number.push($(this).data("id"));
            });


            $("#iar_particulars").empty().val("generating particulars...");
            $("#iar_fmis_msg").css("display", "none");


                $.ajax({
                    type: 'POST',
                    url: "{{ route('dv_formatter.particulars') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        checkboxValues: checkboxValues,
                        iar_number: iar_number
                    },
                    success: function(data){
                        console.log(data.length);
                        if(data.length>=328){
                            $('#show_iar_modal').modal('hide');
                            alert('Max Characters exceeds in 330. Try to select maximum of 12 Deliveries')
                        }else{
                            $("#iar_particulars").empty().val(data);
                        }

                        
                    }
                });

         
           
        
    });




    document.getElementById("copy_btn").addEventListener("click", function() {
        // $('#btn_submit_dv').removeAttr('disabled');
        var copy_status = copyToClipboard(document.getElementById("iar_particulars"));
        if(copy_status == true){
            $("#iar_fmis_msg").css("display", "block");
        }
    });

  
  //irwin
  
  $("#region_select").on("change", function(e){
    HoldOn.open(holdon_options)

        var region = $(this).val();
        if (region !=0){
            $('.filter_btn').removeAttr('disabled');
        }else{
            $('.filter_btn').attr('disabled','disabled');
        }

        $("#coop_select2").empty().append("<option value=''>Loading provinces please wait...</option>");
        $("#coop_select2").empty().append("<option value=''>Please select a Coop</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('dv_formatter.get_coops') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region
            },
            success: function(data){
                $("#coop_select2").empty().append("<option value='0'>Select Seed Cooperative</option>");
                $("#coop_select2").append(data);
            }
        });
        HoldOn.close()
    });


    $("#filter_btn").on("click", function(e){

        HoldOn.open(holdon_options)
  
        var region = $("#region_select").val();
        var coop_accre = $("#coop_select2").val();

        if(coop_accre == 0){
            alert('Please Select Cooperative');
            
        }else{
            $.ajax({
            type: 'POST',
            url: "{{ route('dv_formatter.coop.search') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region,
                coop_accre: coop_accre

            },
                success: function(data){
                    $("#btn_undo").click();
                    load_region();
                    load_coop_deliveries(data.current_moa, data.accreditation, data.coopName, data.address, data.tagged_batch_count, data.untag_batch_count, data.acronym, data.with_no_iar, data.inspected_bags, data.total_deliveries);
                    load_table(data.current_moa);
                    
                }
            });


        }
        HoldOn.close()
    });


    $("#btn_submit_dv").on("click", function(e){
        // var current_moa = $("#coop_moa_txt").text();
        var coop_accre = $("#coop_accre_txt").text();
        var checkboxValues = [];
        var dv_no = $("#dv_number").val();

        $('input[name=selected_batch]:checked').map(function() {
                checkboxValues.push($(this).val());
            });

        if(dv_no == ''){
            alert('Please Input DV control number');
        }else{
            HoldOn.open(holdon_options)


        $.ajax({
            type: 'POST',
            url: "{{ route('dv_formatter.update.dv_no') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_accre: coop_accre,
                checkboxValues: checkboxValues,
                dv_no: dv_no
            },
            success: function(data){
                reload_main();

                $("#btn_undo").click()
                
                if(data==1){
                    $('#show_iar_modal').modal('hide');
                    alert('Updated Suucessfully');
                    // load_table(current_moa);
                }else{
                    alert('Unable to update records.');
                } 
                
            }
        });
        HoldOn.close()

        }

    });

    




    $("#reset_filter").on("click", function(e){
        location.reload();
    });

    function copyToClipboard(elem) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
        var origSelectionStart, origSelectionEnd;
        if (isInput) {
            // can just use the original source element for the selection and copy
            target = elem;
            origSelectionStart = elem.selectionStart;
            origSelectionEnd = elem.selectionEnd;
        } else {
            // must use a temporary form element for the selection and copy
            target = document.getElementById(targetId);
            if (!target) {
                var target = document.createElement("textarea");
                target.style.position = "absolute";
                target.style.left = "-9999px";
                target.style.top = "0";
                target.id = targetId;
                document.body.appendChild(target);
            }
            target.textContent = elem.textContent;
        }
        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);
        
        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch(e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }
        
        if (isInput) {
            // restore prior selection
            elem.setSelectionRange(origSelectionStart, origSelectionEnd);
        } else {
            // clear temporary content
            target.textContent = "";
        }
        return succeed;
    }


    function load_region(){

        // $("#coop_select2").empty().append("<option value=''>Loading regions please wait...</option>");

        $.ajax({
            type: 'POST',
            url: "{{ route('dv_formatter.regions') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){

                $("#region_select").empty().append("<option value='0'>Please select region</option>");
                $("#region_select").append(data);
               
            }
        });

    }

    </script>
  
@endpush
