@extends('layouts.index')

@section('content')

    <style type="text/css">
            .topStatus{
                width: 200px;
                height: 50px;
            }

            .modal_title{
                font-size: 13px;
                font-weight: bold;
            }
    </style>



    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>National Delivery and Distribution Schedule</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">
                    <div class="form-group">
                               
                                <div class="col-md-12 col-sm-12 col-xs-12" style="margin-bottom:10px;"> <center>
                                    <button id="status_all" class="btn btn-success btn-lg topStatus" > ALL STATUS</button>
                                    <button id="status_pending"  class="btn btn-dark btn-lg topStatus" > PENDING</button>
                                    <button id="status_confirmed"  class="btn btn-dark btn-lg topStatus" > CONFIRMED</button>
                                    <button id="status_inspected"  class="btn btn-dark btn-lg topStatus" > INSPECTED</button></center>
                                </div>
                   
                                <input type="hidden" name="status" id="status" value="all">

                    </div>




            
        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Region</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="region_ops" id="region_ops" class="form-control" data-parsley-min="1">
                                        <option value="all">All Region</option>
                                      @foreach ($regionList as $region)
                                                <option value="{{ $region->regionName }}">{{ $region->regionName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="province_ops" id="province_ops" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                    </select>
                                </div>
       						</div>

       						<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="municipality_ops" id="municipality_ops" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a municipality</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Drop Off Point</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="dop_ops" id="dop_ops" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a drop off point</option>
                                    </select>
                                </div>
                            </div>
       	</div>
    </div>







     <div class="col-md-12 col-sm-12 col-xs-12">
    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">       
        <a href="#" style="float:right;"  class="btn btn-success btn-md" id="exportToExcel"> <i class="fa fa-file-excel-o" aria-hidden="true"></i> Export </a> 
    <a href="#" style="float:right;"  data-toggle="modal" data-target="#add_edit_modal" data-eventtarget="add" class="btn btn-success btn-md"> <i class="fa fa-plus-square" aria-hidden="true"></i> Schedule </a>
    
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th> Region </th>
                    <th> Province </th>
                    <th> Municipality </th>
                    <th> DOP </th>
                    <th> Seed Coop </th>
                    <th> Bags </th>
                    <th> Delivery Date </th>
                    <th> Distribution Date </th>
                    <th> Inspector </th>
                    <th> Assigned PC </th>
                    <th> Status </th>
                    <th> Remarks </th>
                    <th> Action </th>
                </thead>
                <tbody id='databody'>
                </tbody>
            </table>
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>




        <!-- IAR PREVIEW MODAL -->
<div id="add_edit_modal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 80%;">
        <div class="modal-content" >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span> Schedule Form</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                               
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="col-md-4 col-sm-12 col-xs-12">
                                        <select name="region_ops_modal" id="region_ops_modal" class="form-control" data-parsley-min="1">
                                            <option value="0">Please Select Region</option>
                                          @foreach ($regionList as $region)
                                                    <option value="{{ $region->regionName }}">{{ $region->regionName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-12 col-xs-12">
                                        <select name="province_ops_modal" id="province_ops_modal" class="form-control" data-parsley-min="1">
                                            <option value="0">Please select a Province</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-12 col-xs-12">
                                        <select name="municipality_ops_modal" id="municipality_ops_modal" class="form-control" data-parsley-min="1">
                                            <option value="0">Please select a Municipality</option>
                                        </select>
                                               <br>
                                    </div>
                                </div>
                </div>
            
                <div class="form-group">

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                          <label class="modal_title">  Seed Cooperative: </label>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="coop_list" id="coop_list" class="form-control" data-parsley-min="1" >
                                <option value="0">Please select a Cooperative</option>
                            </select>
                                   <br>
                         </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                           <label class="modal_title"> Drop-off Point: </label>
                        </div>
                        <div class="col-md-9 col-sm-12 col-xs-12">
                           <select name="dop_list" id="dop_list" onchange="dop_list(this.value);" class="form-control" data-parsley-min="1">
                                <option value="0">New Drop off Point</option>
                            </select>   
                            <input type="text" placeholder="Input Drop off Point"  class="form-control" name="dropoff_point" id="dropoff_point">
                                   <br>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                         <label class="modal_title">  Bags: </label>
                        </div>
                        <div class="col-md-3 col-sm-12 col-xs-12">
                            <input type="number"  class="form-control" name="bags" id="bags" style="text-align:right;">
                                   <br>
                        </div>

                        <div class="col-md-2 col-sm-12 col-xs-12">
                          <label class="modal_title"> Status: </label>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <select name="status_modal" id="status_modal" class="form-control" data-parsley-min="1">
                                <option value="0">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="inspected">Inspected</option>
                            </select>
                                   <br>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                           <label class="modal_title"> Date of Delivery: </label>
                        </div>
                        <div class="col-md-9 col-sm-12 col-xs-12">
                            <input type="date"  class="form-control" name="delivery_date" id="delivery_date" style="width: 140px;">
                               <br>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                           <label class="modal_title"> Date of Distribution: </label>
                        </div>
                        <div class="col-md-9 col-sm-12 col-xs-12">
                            <input type="date"  class="form-control" name="distribution_date" id="distribution_date" style="width: 140px;">
                              <br>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                            <label class="modal_title"> Delivery Inspector: </label>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="inspector_modal" id="inspector_modal" class="form-control" data-parsley-min="1">
                                <option value="0">Please select Inspector</option>
                            </select>
                        <br>
                         </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                           <label class="modal_title"> Assigned PC: </label>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <select name="pc_modal" id="pc_modal" class="form-control" data-parsley-min="1">
                                <option value="0">Please select PC</option>
                            </select>
                            <br>
                         </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12"> 
                        <div class="col-md-3 col-sm-12 col-xs-12">
                           <label class="modal_title"> Remarks: </label>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <textarea class="form-control" id="remarks" name="remarks"></textarea>
                            <br>
                         </div>
                    </div>

                </div>





            </div>

            <div class="modal-footer">  
                <button class="btn btn-success" style="float:right;" id="save_schedule" >Submit Schedule</button>
                <input type="hidden" name="id" id="id">
            </div>
        </div>
    </div>
</div>
<!-- IAR PREVIEW MODAL -->













@endsection
@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script type="text/javascript">

    function deleteThis(value){

        var yesno = confirm("Delete This Schedule?");

        if(yesno){
          $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.delSchedule')}}",
                data: {
                    _token: _token,
                    id: value
                },
                dataType: 'json',
                success: function (source) {
                    alert(source);
                    loadTbl();
                }
             }); //AJAX     
        }


        
    }



    function dop_list(value) {
        if(value === "0"){
             $("#dropoff_point").removeAttr("style");
        }else{
            $("#dropoff_point").removeAttr("style");
             $("#dropoff_point").attr("style", "display:none;");
        }
    }



    $('#save_schedule').on("click", function() {
        var eventTarget = $("#save_schedule").text();



       var region = $("#region_ops_modal").val();
       var province = $("#province_ops_modal").val();
       var municipality = $("#municipality_ops_modal").val();
       var coop_id = $("#coop_list").val();
      var dropoff_point =  $("#dop_list").val();
        if(dropoff_point === "0"){
            dropoff_point = $("#dropoff_point").val();
        }
       var bags = $("#bags").val();
       var status = $("#status_modal").val();
       var delivery_date = $("#delivery_date").val();
       var distribution_date = $("#distribution_date").val();

       var inspector = $("#inspector_modal").val();
       var pc = $("#pc_modal").val();
       var remarks = $("#remarks").val();

       if(region === "0" || province === "0" || municipality === "0" || coop_id === "0" || dropoff_point === "" || parseInt(bags) <= 0 || status === "0" || delivery_date === "" || distribution_date === "" || inspector === "0" || pc === "0" ){
            alert("Please Complete the Form Inputs");
            return "";
       }else{


        if(eventTarget === "Submit Schedule"){
            $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.addSchedule')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                    municipality: municipality,
                    coop_id: coop_id,
                    dropoff_point: dropoff_point,
                    bags: bags,
                    status: status,
                    delivery_date: delivery_date,
                    distribution_date: distribution_date,
                    inspector: inspector,
                    pc: pc,
                    remarks: remarks
                },
                dataType: 'json',
                success: function (source) {
                    alert(source);
                    $('#add_edit_modal').modal("hide");
                    loadTbl();
                }
             }); //AJAX  
        }else if(eventTarget === "Update Schedule"){

           var id = $("#id").val();

             $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.updateSchedule')}}",
                data: {
                    _token: _token,
                    id:id,
                    region: region,
                    province: province,
                    municipality: municipality,
                    coop_id: coop_id,
                    dropoff_point: dropoff_point,
                    bags: bags,
                    status: status,
                    delivery_date: delivery_date,
                    distribution_date: distribution_date,
                    inspector: inspector,
                    pc: pc,
                    remarks: remarks
                },
                dataType: 'json',
                success: function (source) {
                    alert(source);
                    $('#add_edit_modal').modal("hide");
                    loadTbl();
                }
             }); //AJAX  
        } 
       }
    });



    $('#add_edit_modal').on('show.bs.modal', function (e) {
         var eventTarget = $(e.relatedTarget).data('eventtarget');

         if(eventTarget === "add"){
             $("#region_ops_modal").removeAttr("disabled");
              $("#province_ops_modal").removeAttr("disabled");
               $("#municipality_ops_modal").removeAttr("disabled");


            $("#region_ops_modal").val("0").change();
            $('select[name="coop_list"]').empty().append('<option value="0">Please Select Cooperative</option>');
            $('select[name="inspector_modal"]').empty().append('<option value="0">Please Select Inspector</option>');
            $('select[name="pc_modal"]').empty().append('<option value="0">Please Select PC</option>');
            $("#dropoff_point").val("");
            $("#bags").val("0");
            $("#status_modal").val("0").change();
            //$("#delivery_date").val("{{date('Y-m-d')}}");
            //$("#distribution_date").val("{{date('Y-m-d')}}");
            $("#remarks").val("");   
            dop_list("0");
            $("#dop_list").empty().append('<option value="0">New Drop Off Point</option>'); 
            $("#save_schedule").empty().text("Submit Schedule");  
         }else if(eventTarget === "edit"){
            
        var id = $(e.relatedTarget).data('id');
        var region = $(e.relatedTarget).data('region');
        var province = $(e.relatedTarget).data('province');
        var municipality = $(e.relatedTarget).data('municipality');
        var dropoffpoint = $(e.relatedTarget).data('dropoffpoint');
        var coop_id = $(e.relatedTarget).data('coop_id');
        var bags = $(e.relatedTarget).data('bags');
        var status = $(e.relatedTarget).data('status');
        var delivery = $(e.relatedTarget).data('delivery');
        var distribution = $(e.relatedTarget).data('distribution');
        var inspector = $(e.relatedTarget).data('inspector');
        var pc = $(e.relatedTarget).data('pc');
        var remarks = $(e.relatedTarget).data('remarks');

         $("#id").val(id);

        $("#region_ops_modal").val(region);
            $("#region_ops_modal").removeAttr("disabled");
            $("#region_ops_modal").attr("disabled", "true");
        $("#province_ops_modal").empty().append('<option value="' + province + '">' + province + '</option>');
            $("#province_ops_modal").removeAttr("disabled");
            $("#province_ops_modal").attr("disabled", "true");
        $("#municipality_ops_modal").empty().append('<option value="' + municipality + '">' + municipality + '</option>');    
            $("#municipality_ops_modal").removeAttr("disabled");
            $("#municipality_ops_modal").attr("disabled", "true");
        modal_data(coop_id,inspector,pc,dropoffpoint); 
        
        $("#bags").val(bags);
        $("#status_modal").val(status).change();
        $("#delivery_date").val(delivery);
        $("#distribution_date").val(distribution);
        $("#remarks").val(remarks);
        dop_list(dropoffpoint);

        $("#save_schedule").empty().text("Update Schedule");





         }
    });




    $("#region_ops_modal").on('change', function () {
     
       var region = $("#region_ops_modal").val();
       $('select[name="province_ops_modal"]').empty().append('<option value="0">Please Select province</option>');
       $('select[name="municipality_ops_modal"]').empty().append('<option value="0">Please Select municipality</option>');
         $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.province')}}",
                data: {
                    _token: _token,
                    region: region
                },
                dataType: 'json',
                success: function (source) {
             $('select[name="province_ops_modal"]').empty().append('<option value="0">Please Select province</option>');
            $.each(source, function (i, d) {
        $('select[name="province_ops_modal"]').append('<option value="' + d.province + '">' + d.province + '</option>');
                    }); 
        
            
        }

        }); //AJAX
    });





    $("#province_ops_modal").on('change', function () {
       var region = $("#region_ops_modal").val();
       var province = $("#province_ops_modal").val();
       
       $('select[name="municipality_ops_modal"]').empty().append('<option value="0">Please Select municipality</option>');
         $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.municipality')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province
                },
                dataType: 'json',
                success: function (source) {
             $('select[name="municipality_ops_modal"]').empty().append('<option value="0">Please Select municipality</option>');
            $.each(source, function (i, d) {
        $('select[name="municipality_ops_modal"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                    }); 
        }

        }); //AJAX
    });

   
    $("#municipality_ops_modal").on('change', function () {
      modal_data("","","",""); 
    });



    function modal_data(coop_id, inspector, pc, dop){
       var region = $("#region_ops_modal").val();
       var province = $("#province_ops_modal").val();
       var municipality = $("#municipality_ops_modal").val();
       
       if(region === "0" || province === "0" || municipality === "0"){

       }else{
            $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.modal_data')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                    municipality: municipality
                },
                dataType: 'json',
                success: function (data) {
                    $('select[name="coop_list"]').empty().append('<option value="0">Please Select Cooperative</option>');
                    $.each(data["coop_list"], function (i,d){
                        if(coop_id === d.coopId){
                            var sel = "selected = selected";
                        }else{
                            var sel ="";
                        }


                        $('select[name="coop_list"]').append('<option value="' + d.coopId + ' " '+sel+' >' + d.coopName + '</option>');
                    });

                    $('select[name="inspector_modal"]').empty().append('<option value="0">Please Select Inspector</option>');
                    $.each(data["inspector_list"], function (i,d){
                        if(inspector === d.username){
                            var sel = "selected = selected";
                        }else{
                            var sel ="";
                        }


                        $('select[name="inspector_modal"]').append('<option value="' + d.username + '"  '+sel+' >' + d.lastName +', '+ d.firstName+ ' '+ d.middleName + ' ('+d.username+ ') </option>');
                    });

                    $('select[name="pc_modal"]').empty().append('<option value="0">Please Select PC</option>');
                    $.each(data["pc_list"], function (i,d){
                         if(pc === d.username){
                            var sel = "selected = selected";
                        }else{
                            var sel ="";
                        }

                        $('select[name="pc_modal"]').append('<option value="' + d.username + '"  '+sel+' >' + d.lastName +', '+ d.firstName+ ' '+ d.middleName + ' ('+d.username+ ') </option>');
                    });

                    $('select[name="dop_list"]').empty().append('<option value="0">New Drop off Point</option>');
                    $.each(data["dop_list"], function (i,d){
                         if(dop === d.dropOffPoint){
                            var sel = "selected = selected";
                        }else{
                            var sel ="";
                        }

                        $('select[name="dop_list"]').append('<option value="' + d.dropOffPoint + '"  '+sel+' >' + d.dropOffPoint +' </option>');
                    });


                }
            }); //AJAX
       }

    }


    </script>



    <?php 
$curr_link =  "http://".$_SERVER ['SERVER_NAME'].'/'.basename(getcwd());
    ?>
	<script type="text/javascript">
	   
           $("#exportToExcel").on("click", function(){
            //    window.open("")
//ops/Planning/exportToExcel/{status}/{region}/{province}/{municipality}/{dop}
            var region = $("#region_ops").val();
            var province = $("#province_ops").val();
            var municipality = $("#municipality_ops").val();
            var dop = $("#dop_ops").val();
            var status = $("#status").val();
            window.open("{{$curr_link}}"+"/ops/Planning/exportToExcel/"+status+"/"+region+"/"+province+"/"+municipality+"/"+dop,"_blank");







           });





        $("#status_all").on("click", function() {
           $("#status_all").removeAttr("class");
           $("#status_pending").removeAttr("class");
           $("#status_confirmed").removeAttr("class");
           $("#status_inspected").removeAttr("class");
           $("#status_all").attr("class", "btn btn-success btn-lg topStatus");
           $("#status_pending").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_confirmed").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_inspected").attr("class", "btn btn-dark btn-lg topStatus");

            $("#status").val("all");
            loadTbl();

        });

        $("#status_pending").on("click", function() {
           $("#status_all").removeAttr("class");
           $("#status_pending").removeAttr("class");
           $("#status_confirmed").removeAttr("class");
           $("#status_inspected").removeAttr("class");
           $("#status_all").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_pending").attr("class", "btn btn-success btn-lg topStatus");
           $("#status_confirmed").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_inspected").attr("class", "btn btn-dark btn-lg topStatus");

            $("#status").val("pending");
            loadTbl();
        });

        $("#status_confirmed").on("click", function() {
           $("#status_all").removeAttr("class");
           $("#status_pending").removeAttr("class");
           $("#status_confirmed").removeAttr("class");
           $("#status_inspected").removeAttr("class");
           $("#status_all").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_pending").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_confirmed").attr("class", "btn btn-success btn-lg topStatus");
           $("#status_inspected").attr("class", "btn btn-dark btn-lg topStatus");

            $("#status").val("confirmed");
            loadTbl();
        });

        $("#status_inspected").on("click", function() {
           $("#status_all").removeAttr("class");
           $("#status_pending").removeAttr("class");
           $("#status_confirmed").removeAttr("class");
           $("#status_inspected").removeAttr("class");
           $("#status_all").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_pending").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_confirmed").attr("class", "btn btn-dark btn-lg topStatus");
           $("#status_inspected").attr("class", "btn btn-success btn-lg topStatus");

            $("#status").val("inspected");
            loadTbl();
        });


    $("#region_ops").on('change', function () {
       var region = $("#region_ops").val();
       $('select[name="province_ops"]').empty().append('<option value="0">Please Select province</option>');
       $('select[name="municipality_ops"]').empty().append('<option value="0">Please Select municipality</option>');
       $('select[name="dop_ops"]').empty().append('<option value="0">Please Select drop off point</option>');
     

         $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.province')}}",
                data: {
                    _token: _token,
                    region: region
                },
                dataType: 'json',
                success: function (source) {
             $('select[name="province_ops"]').empty().append('<option value="0">Please Select province</option>');
            $.each(source, function (i, d) {
                if(i == 0){
                   $('select[name="province_ops"]').empty().append('<option value="all">All Province</option>');  
                }
        $('select[name="province_ops"]').append('<option value="' + d.province + '">' + d.province + '</option>');
                    }); 


        }
        }); //AJAX
         loadTbl();
    });



    $("#province_ops").on('change', function () {
       var region = $("#region_ops").val();
       var province = $("#province_ops").val();
       
       $('select[name="municipality_ops"]').empty().append('<option value="0">Please Select municipality</option>');
       $('select[name="dop_ops"]').empty().append('<option value="0">Please Select drop off point</option>');
         $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.municipality')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                      
                },
                dataType: 'json',
                success: function (source) {
             $('select[name="municipality_ops"]').empty().append('<option value="0">Please Select municipality</option>');
            $.each(source, function (i, d) {
                if(i == 0){
                   $('select[name="municipality_ops"]').empty().append('<option value="all">All Municipality</option>');  
                }
        $('select[name="municipality_ops"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                    }); 
        }
        }); //AJAX

         loadTbl();
    });


    $("#municipality_ops").on('change', function () {
       var region = $("#region_ops").val();
       var province = $("#province_ops").val();
       var municipality = $("#municipality_ops").val();
    
       $('select[name="dop_ops"]').empty().append('<option value="0">Please Select drop off point</option>');
         $.ajax({
                method: 'POST',
                url: "{{route('ops.planning.dop')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                    municipality: municipality,
                },
                dataType: 'json',
                success: function (source) {
             $('select[name="dop_ops"]').empty().append('<option value="0">Please Select drop off point</option>');
            $.each(source, function (i, d) {
                if(i == 0){
                   $('select[name="dop_ops"]').empty().append('<option value="all">All Drop Off Point</option>');  
                }
        $('select[name="dop_ops"]').append('<option value="' + d.dropOffPoint + '">' + d.dropOffPoint + '</option>');
                    }); 
        }
        }); //AJAX


         loadTbl();
    });


    $("#municipality_ops").on('change', function () {
        loadTbl();
    });







        function loadTbl(){
            HoldOn.open(holdon_options);
                var region = $("#region_ops").val();
                var province = $("#province_ops").val();
                var municipality = $("#municipality_ops").val();
                var dop = $("#dop_ops").val();
                var status = $("#status").val();

                    $('#dataTBL').DataTable().clear();
                    $('#dataTBL').DataTable({
                        "bDestroy": true,
                        "autoWidth": true,
                        "searchHighlight": true,
                        "searching": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "pageLength": 25,
                        "ajax": {
                            "url": "{{route('ops.planning.loadTable')}}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}",
                                region: region,
                                province: province,
                                municipality: municipality,
                                dop: dop,
                                status: status
                                   
                            }
                        },
                        "columns":[
                            {"data": "region"},
                            {"data": "province"},
                            {"data": "municipality"},
                            {"data": "dropOffPoint"},
                            {"data": "seed_coop"},
                            {"data": "bags"},
                            {"data": "delivery_date"},
                            {"data": "distribution_date"},
                            {"data": "inspector"},
                            {"data": "assigned_pc"},
                            {"data": "status"},
                            {"data": "remarks"},
                            {"data": "action", 'searchable': false},
                        ]
                    });
            HoldOn.close();
        }


        loadTbl();
	</script>

@endpush