@extends('layouts.index')


@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        #category_text{
            font-size: 15px;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">



<div class="row">
    {{-- Seed Cooperatives Table --}}
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Encoder Yield Form</h2>
                 
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion"  id="category_checkbox" style="display:none;">
                <h2>
                   Category Check Box
                </h2>

                <table>
                    <tr> 
                        <td width="10px;"><input type="checkbox" id="category_1" name="category_1" value="1" disabled=""></td><td id="category_text"><strong>Category 1 </strong> - Yield is equal to  0 (zero) </td>
                    </tr>
                    <tr>
                        <td width="10px;"><input type="checkbox" id="category_2" name="category_2" value="2" checked="true" ></td><td id="category_text"><strong>Category 2 </strong> - Total Bags Claimed is equal to any of the ff: (Total Production, Ave. Weight per bag, Area Harvested) </td>
                    </tr>
                    <tr>
                        <td width="10px;"><input type="checkbox" id="category_3" name="category_3" value="3" checked="true" ></td><td id="category_text"><strong>Category 3 </strong> - Yield is less than or equal to 1 (one) </td>
                    </tr>
                    <tr>    
                       <td width="10px;"><input type="checkbox" id="category_4" name="category_4" value="4" checked="true" ></td> <td id="category_text"><strong>Category 4 </strong> - Yield is greater than 1 (one) but less than or equal to 2 (two)  </td>
                    </tr>
                    <tr>    
                        <td width="10px;"><input type="checkbox" id="category_5" name="category_5" value="5" checked="true" ></td><td id="category_text"><strong>Category 5 </strong> - Yield is greater than 13 (thirteen) </td>
                    </tr>
                    <tr>    
                        <td width="10px;"><input type="checkbox" id="category_6" name="category_6" value="6" checked="true" ></td><td id="category_text"><strong>Category 6 </strong> - Updated Farmer's Yield </td>
                    </tr>

                </table>


                </div>
            </div>
        </div>
    </div>
</div>


  <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                   Filter Province and Municipality
                </h2>

                <select name="province_fg" id="province_fg" class="form-control">
                            <option value="0">Please select a Province</option>
                            @foreach($province as $province)
                                <option value="{{$province->province}}">{{$province->province}}</option>

                            @endforeach


                </select>
                        <br>
                <select name="municipality_fg" id="municipality_fg" class="form-control">
                            <option value="0">Please select a Municipality</option>
                </select>

                <br>
                <select name="contact_info" id="contact_info" class="form-control">
                            <option value="all">WITH & WITHOUT CONTACT INFO.</option>
                            <option value="with">WITH CONTACT INFO.</option>
                            <option value="without">WITHOUT CONTACT INFO.</option>  
                </select>
            


                



                         <div class="clearfix"></div>
        </div>
</div>
        <br>
        <!-- FILTER PANEL -->




<!-- DATA TABLE -->

 <div class="col-md-12 col-sm-12 col-xs-12" >

    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
              Farmer Beneficiary List &nbsp; &nbsp;
            </h2>

             <label class="btn btn-info btn-sm" id="msg" style="display:none;" disabled> </label>
            



            <button class="btn btn-warning btn-sm" style="float:right; display: none;" id="update_history">
               <i class="fa fa-history" aria-hidden="true"></i> View Update History
            </button>


            <button class="btn btn-success btn-sm" style="float:right; display: none;" id="export_excel">
               <i class="fa fa-file-excel-o" aria-hidden="true"></i> Export List to Excel
            </button>


            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th style="width: 40px;">Province</th>
                    <th style="width: 100px;">Rsbsa No.</th>
                    <th style="width: 200px;">Name</th>
                    <th style="width: 80px;">Claimed Variety</th>
                    <th style="width: 40px;">Claimed Bag(s)</th>
                    <th style="width: 60px;">Date Claimed</th>
                    <th style="width: 60px;">Contact Info</th>
                    
                    <th style="width: 60px;">Season</th>

                   <th style="width: 30px;">Total Production</th>
                   <th style="width: 30px;">Wt. per bag</th>
                   <th style="width: 30px;">Area Harvested</th>
                   <th style="width: 15px;">Yield</th>
                   <th style="width: 15px;">Category</th>
                   <th style="width: 10px;">Action</th>
                   
                </thead>
                <tbody id='databody'>
                    
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

 


 








<!-- CURRENT HISTORY MODAL -->
<div id="history_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Update History List</span><br>
                     
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">User: </label>
                <label >
                    <select onchange="load_history_table($('#farmer_id').val());" class="form-control" id="user_name" name="user_name">
                        <option value="all">All User</option>
                    </select>
                </label> <br>
                <label for="" class="col-xs-3">Date From: </label>
                <label ><input onchange="load_history_table($('#farmer_id').val());" type="date" id="date_from" id="date_from"></label> <br>
                <label for="" class="col-xs-3">Date To: </label>
                <label ><input type="date" onchange="load_history_table($('#farmer_id').val());" id="date_to" id="date_to"></label> <br>
                <input type="hidden" id="farmer_id" name="farmer_id" value="%">

                <label for="" class="col-xs-3">Province: </label>
                <label id="modal_province"></label> <br>
                              
        <div class="form-group">

                <div>
                    
                     <table class="table table-hover table-striped table-bordered" id="history_table">
                        <thead >
                            <tr id="head_transfer_seedtag">
                                <th >Rsbsa #</th>
                                <th>Name</th>
                                <th>Field Updated</th>
                                <th>Update Info.</th>
                                <th>Date Updated</th>
                                <th>Author</th>
                                <th>Category</th>
                                
                            </tr>
                        </thead>
                        <tbody id="history_table_body">
                        </tbody>
                    </table>
                </div>
        </div>  
                
            </div>
            <div class="modal-footer">    
            <button class="btn btn-success btn-sm" style="float:right;" id="export_excel_history">
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i> Export to Excel
            </button>  
            </div>
        </div>
    </div>
</div>
<!-- CURRENT HISTORY MODAL END -->






@endsection
@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script type="text/javascript">
  
        <?php 
            $server = $_SERVER['SERVER_NAME'];
            $web_base = basename(getcwd());
            $link = "http://".$server."/".$web_base; 
        ?>






    $("input[type='checkbox']").on("click", function (){
      loadTBl();

    });

  $("#export_excel").on('click', function () {
         HoldOn.open(holdon_options);
        var province = $('select[name="province_fg"]').val();
        var municipality = $('select[name="municipality_fg"]').val();

        if(province === "0"){
            HoldOn.close();
            exit();
        }

        window.open("{{$link}}"+"/encoder/yield/export/excel/"+province+"/"+municipality, "_blank");
    
        HoldOn.close();
        });  //EXCEL DOWNLOAD


   $("#export_excel_history").on('click', function () {
         HoldOn.open(holdon_options);
        var province = $('select[name="province_fg"]').val();
        var municipality = $('select[name="municipality_fg"]').val();
    
        var user_name = $('select[name="user_name"]').val();
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var farmer_id = $("#farmer_id").val();

        if(farmer_id==="%"){
            farmer_id = "all";
        }

        if(province === "0"){
            HoldOn.close();
            exit();
        }

        window.open("{{$link}}"+"/encoder/yield/export/history/"+province+"/"+municipality+"/"+date_from+"/"+date_to+"/"+user_name+"/"+farmer_id, "_blank");
    
        HoldOn.close();
        });  //EXCEL HISTORY DOWNLOAD




    function checkCategory(id){
        var yieldValue = parseFloat($("#yield_"+id).val());
        var wtperbagValue = parseFloat($("#wtperbag_"+id).val());
        var area_harvestValue = parseFloat($("#area_harvest_"+id).val());
        var bags = parseFloat($("#bags_"+id).val());

        var tons = (parseFloat(yieldValue)*parseFloat(wtperbagValue)) / parseFloat(area_harvestValue);
        var tons = tons / 1000;

        if(yieldValue <= 0 || wtperbagValue <= 0 || area_harvestValue <= 0){
            $("#category_"+id).empty().text("Category 1");
        }else if(yieldValue == bags || wtperbagValue == bags || area_harvestValue == bags){
            $("#category_"+id).empty().text("Category 2");
        }else if(tons <= 1){
            $("#category_"+id).empty().text("Category 3");
        }else if(tons > 1 && tons <= 2){
            $("#category_"+id).empty().text("Category 4");
        }else if(tons > 13){
            $("#category_"+id).empty().text("Category 5");
        }else{
            $("#category_"+id).empty().text("Category 6");
        }

    }
    
    function inputs_notice(){
         var province = $('select[name="province_fg"]').val();
         var category = "all";
         $.ajax({
                method: 'POST',
                url: "{{route('encoder.yield.lib.inputs')}}",
                data: {
                    _token: _token,
                   province:province,
                    category: category
                },
                dataType: 'json',
                success: function (source) {
                    if(source === "None"){
                         $("#msg").removeAttr("style");
                         $("#msg").attr("style", "display:none;");
                         
                     }else{
                        $("#msg").removeAttr("style");
                        $("#msg").empty().append(source);
                     }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) { 
                   
                }  
            }); //AJAX GET MUNICIPALITY




    }




    function updateData(value,id,yieldType,db,oldvalue){
        var yieldValue = $("#yield_"+id).val();
        var seasonValue = $("#season_"+id).val();
        
        var wtperbagValue = $("#wtperbag_"+id).val();
        var area_harvestValue = $("#area_harvest_"+id).val();

        var tons = (parseFloat(yieldValue)*parseFloat(wtperbagValue)) / parseFloat(area_harvestValue);
        var tons = tons / 1000;


        $("#tons_"+id).empty().text(tons.toFixed(2));

        var category = $("#category_"+id).text();
        $("#"+yieldType+"_"+id).css("border", "2px solid orange");
         $.ajax({
                method: 'POST',
                url: "{{route('encoder.yield.update')}}",
                data: {
                    _token: _token,
                    value: value,
                    id: id,
                    yieldType: yieldType,
                    db:db,
                    oldvalue: oldvalue,
                    category: category

                },
                dataType: 'json',
                success: function (source) {
                    if(source === "Success"){
                         $("#"+yieldType+"_"+id).css("border", "2px solid green"); 
                         $("#"+yieldType+"_"+id).removeAttr("title");
                     }else if(source === "Failed_season"){
                         $("#"+yieldType+"_"+id).attr("title", "Invalid Season Input");
                         $("#"+yieldType+"_"+id).css("border", "2px solid red");
                         $("#"+yieldType+"_"+id).tooltip({ content: "Invalid Season Input" }).tooltip("open");
                         setTimeout(function () { $("#"+yieldType+"_"+id).tooltip("close");}, 5000);
                     }
                     else{
                         $("#"+yieldType+"_"+id).attr("title", "Farmer Not Exist on Database, Please refresh page ");
                         $("#"+yieldType+"_"+id).css("border", "2px solid red");
                         $("#"+yieldType+"_"+id).tooltip({ content: "Farmer Not Exist on Database, Please refresh page " }).tooltip("open");
            setTimeout(function () { $("#"+yieldType+"_"+id).tooltip("close");}, 5000);
                     }

                     checkCategory(id);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) { 
                    $("#"+yieldType+"_"+id).css("border", "2px solid red"); 
                    alert("Status: " + textStatus +"\n" +"Error: " + errorThrown); 
                }  
            }); //AJAX GET MUNICIPALITY
    }


    function loadTBl(){
        var province = $('select[name="province_fg"]').val();
        var municipality = $('select[name="municipality_fg"]').val();
        var contactInfo = $('select[name="contact_info"]').val();
        
        var c1 =$("input[name='category_1']:checked");
        var c2 =$("input[name='category_2']:checked");
        var c3 =$("input[name='category_3']:checked");
        var c4 =$("input[name='category_4']:checked");
        var c5 =$("input[name='category_5']:checked");
        var c6 =$("input[name='category_6']:checked");
                



        //alert(province+" "+municipality);
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
                    "url": "{{route('encoder.yield.load_table')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        contactInfo: contactInfo,
                        c1:c1.length,
                        c2:c2.length,
                        c3:c3.length,
                        c4:c4.length,
                        c5:c5.length,
                        c6:c6.length
                    }
                },
                "columns":[
                    {"data": "province"},
                    {"data": "rsbsa"},
                    {"data": "name"},
                    {"data": "variety", 'searchable': false, 'orderable': false},
                    {"data": "bags", 'searchable': false, 'orderable': false},
                    {"data": "dateclaimed", 'searchable': false, 'orderable': false},
                    {"data": "contact_info"},
                    {"data": "season", 'searchable': false , 'orderable': false},
                    
                    {"data": "yield1", 'searchable': false , 'orderable': false},
                    {"data": "yield2", 'searchable': false , 'orderable': false},
                    {"data": "yield3", 'searchable': false , 'orderable': false},
                    {"data": "tons", 'searchable': false , 'orderable': false},
                    {"data": "category"},
                    {"data": "action", 'searchable': false , 'orderable': false},
                    
                    
            ]});

           

    }



        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });


        $('select[name="province_fg"]').on('change', function () {
            HoldOn.open(holdon_options);        
        var province = $('select[name="province_fg"]').val();
            $.ajax({
                method: 'POST',
                url: "{{route('encoder.yield.muncipality')}}",
                data: {
                    _token: _token,
                    province: province
                },
                dataType: 'json',
                success: function (source) {
                    $('select[name="municipality_fg"]').empty().append('<option value="0">Please select a Municipality</option>');
                $.each(source, function (i, d) {
                    if(i == 0){
                        $('select[name="municipality_fg"]').empty().append('<option value="all">--ALL MUNICIPALITY--</option>');
                    }

                    $('select[name="municipality_fg"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                });
     
                loadTBl();
                inputs_notice();
                }
            }); //AJAX GET MUNICIPALITY

             $.ajax({
                method: 'POST',
                url: "{{route('encoder.yield.history')}}",
                data: {
                    _token: _token,
                    province: province,
                     req: "button",
                     farmer_id: "%"
                },
                dataType: 'json',
                success: function (source) {
                    if(source.length <= 0){
                            $("#update_history").removeAttr("style");
                            $("#update_history").removeAttr("data-toggle");
                            $("#update_history").removeAttr("data-target");
                            $("#update_history").attr("style", "float:right;display: none;");         
                   }else{
                        $("#update_history").removeAttr("style");
                        $("#update_history").removeAttr("data-toggle");
                        $("#update_history").removeAttr("data-target");
                        $("#update_history").attr("style", "float:right;");
                        $("#update_history").attr("data-toggle", "modal");
                        $("#update_history").attr("data-target", "#history_modal");
                        $("#update_history").attr("data-id", "0");
                        
                        $('#modal_province').empty().text(province);
                   }
                }
            }); //AJAX FOR HISTORY


             if(province ==="0"){
                $("#category_checkbox").removeAttr("style");
                $("#category_checkbox").attr("style", "display: none;"); 
                $("#export_excel").removeAttr("style");
                $("#export_excel").attr("style", "float:right;display: none;"); 
             }else{
                $("#category_checkbox").removeAttr("style");
                $("#export_excel").removeAttr("style");
                $("#export_excel").attr("style", "float:right;"); 
             }





        HoldOn.close();
    });  //END PROVINCE SELECT


        $('select[name="municipality_fg"]').on('change', function () {
            HoldOn.open(holdon_options);
                loadTBl();
            HoldOn.close();
        });  //END MUNICIPALITY SELECT

        $('select[name="contact_info"]').on('change', function () {
            HoldOn.open(holdon_options);
            
        var province = $('select[name="province_fg"]').val();
            if(province !== "0"){
                loadTBl();
            }


            HoldOn.close();
        });  //END MUNICIPALITY SELECT

        $('#history_modal').on('show.bs.modal', function (e) {
              $('#date_from').val("{{date('Y-m-01')}}");
            $('#date_to').val("{{date('Y-m-d')}}");
            var province = $('select[name="province_fg"]').val();
              var farmer_id = $(e.relatedTarget).data('id');

                if(farmer_id > 0){
                    farmer_id = farmer_id;
                }else{
                    farmer_id ="%";
                }

            $.ajax({
                method: 'POST',
                url: "{{route('encoder.yield.username')}}",
                data: {
                    _token: _token,
                    province: province,
                     farmer_id: farmer_id
                },
                dataType: 'json',
                success: function (source) {
                        $("#user_name").empty().append("<option value='all' >All User</option>");
                      $.each(source, function (i, d) {
                        $("#user_name").append("<option value='"+d.user_updated+"' >"+d.user_updated+"</option>");
                      });
                }
            }); //AJAX FOR HISTORY

            $("#farmer_id").val(farmer_id);
            load_history_table(farmer_id);
        });


        function load_history_table(farmer_id){
            var province = $('select[name="province_fg"]').val();
            var user_name = $('select[name="user_name"]').val();
          
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();

            $('#history_table').DataTable().clear();
            $('#history_table').DataTable({
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
                    "url": "{{route('encoder.yield.history')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        req: "table",
                        farmer_id: farmer_id,
                        user_name: user_name,
                        date_from: date_from,
                        date_to: date_to
                    }
                },
                "columns":[
                    {"data": "rsbsa"},
                     {"data": "full_name"},
                    {"data": "field_updated", 'searchable': false, 'orderable': false},
                    {"data": "info", 'searchable': false, 'orderable': false},
                    {"data": "date_updated", 'searchable': false, 'orderable': false},
                    {"data": "author", 'searchable': false , 'orderable': false},
                    {"data": "category", 'searchable': false , 'orderable': false},
                        
            ]});
        }






    </script>

@endpush