@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Distribution Reset </h3>
            </div>
        </div>

            <div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong>  Use this only for duplicated released data / wrong input
            </div>
        </div>                  



                           
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Category </label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                   

                                        <select name="category" id="category" class="form-control" data-parsley-min="1" style="width: 500px">
                                            <option value="INBRED">Please select a Category</option>
                                            <option value="INBRED">INBRED</option>
                                            <option value="HYBRID">HYBRID</option>
                                        </select>
                                   
                                </div>
                            </div>




                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                     <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1" style="width: 500px">
                                    <option value="0">Please select a province</option>
                                    @foreach ($provinces as $provinces)
                                    <option value="{{substr($provinces->prv, 0,4)}}">{{$provinces->province}}</option>
                                    @endforeach

                                    </select>
                                  
                               

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">  
                                    
                                    <select name="utilMunicipality" id="utilMunicipality" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a municipality</option>
                                    </select>

                                  
                              
                                        <div class="row">

                                            <div class="col-md-4">
                                                <br>  
                                                <label for="search_name">Filter</label>
                                                <input type="text" class="form-control tm-10" name="search_name" id="search_name" placeholder="Input Name,RCEF ID or RSBSA Here">
                                            </div>
                                        </div>
                                       
                                    <br>
                                     <button  type="button" name="utilProcess_find" id="utilProcess_find" class="btn btn-lg btn-primary" ><i class="fa fa-sign-in"></i> Get distribution Data </button>
                                     
                                     
                                                            

                                </div>
                            </div>


                            <div class="form-group">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                                            <thead>
                                                <th   style="width: 100px;">RCEF ID</th>
                                                <th   style="width: 100px;">Rsbsa</th>
                                                <th style="width: 100px;">Last Name</th>
                                                <th style="width: 100px;">First Name</th>
                                                <th style="width: 100px;">Middle Name</th>
                                                {{-- <th style="width: 100px;">Brgy</th> --}}
                                                
                                                <th  style="width: 50px;">Actual Area</th>
                                                <th>Variety</th>
                                                <th style="width: 50px;">Bags</th>
                                               <th style="width: 50px;">Claimed Area</th>
                                                <th style="width: 80px;">Released Date</th>
                                                 <th style="width: 60px;">Released By</th>
                                                <th>Action</th>
                                                
                                            </thead>
                                            <tbody id='databody'>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
        </div>
    </div>







      <!-- CURRENT RLA MODAL -->
<div id="update_farmer_info" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Farmer Profile Updating</span><br>
                </h4>
            </div>

            <div class="modal-body">
                
                <div class="col-xs-6">
                    <label for="" class="col-xs-12"  style="color: #f3a72d;"><strong> <big> Farmer Information </big> </strong> </label>
                    <label for="modal_rsbsa" class="col-xs-3">RSBSA #:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_rsbsa" id="modal_rsbsa" disabled> <br>
                    <label for="modal_fname" class="col-xs-3">First Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_fname" id="modal_fname" disabled> <br>
                    <label for="modal_mname" class="col-xs-3">Middle Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_mname" id="modal_mname" disabled> <br>
                    <label for="modal_lname" class="col-xs-3">Last Name: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_lname" id="modal_lname" disabled> <br> 
                    <label for="modal_farmer_ext" class="col-xs-3">Ext. Name: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_farmer_ext" id="modal_farmer_ext" disabled> <br> 
                    <label for="modal_sex" class="col-xs-3">Sex: </label>
                    
                    <div class="col-md-9">
                        <input type="radio" id="male" value="Male" name="modal_sex" disabled> <label for='male'> Male</label> 
                        <input type="radio" id="female" value="Female" name="modal_sex" disabled> <label for='female'> Female</label>  
                    </div>
                    



                    <label for="modal_birthdate" class="col-xs-3">Birthdate: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_birthdate" id="modal_birthdate" disabled> <br> 
                    <label for="modal_tel_number" class="col-xs-3">Contact Number: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_tel_number" id="modal_tel_number" disabled> <br> 
                </div>

                 <div class="col-xs-6">
                    <label for="" class="col-xs-12"  style="color: #f3a72d;"><strong> <big> Other Information </big> </strong> </label>
                    
                    <label for="modal_m_fname" class="col-xs-3">Mother First Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_fname" id="modal_m_fname" disabled> <br>
                    <label for="modal_fname" class="col-xs-3">Mother Middle Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_mname" id="modal_m_mname" disabled> <br>
                    <label for="modal_mname" class="col-xs-3">Mother Last Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_lname" id="modal_m_lname" disabled> <br>
                    <label for="modal_lname" class="col-xs-3">Mother Suffix: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_suffix" id="modal_m_suffix" disabled> <br>


                    <label for="" class="col-xs-12" style="color: #f3a72d;"><strong> <big> Yield Information </big> </strong> </label>
                  
                    <label for="modal_production" class="col-xs-3">Production: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_production" id="modal_production" > <br>

                    <label for="modal_ave_weight" class="col-xs-3">Average Weight per Bag: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_ave_weight" id="modal_ave_weight" > <br>

                    <label for="modal_area_harvested" class="col-xs-3">Area Harvested: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_area_harvested" id="modal_area_harvested" > <br>

                </div>



                <input type="hidden" name="release_id" id="release_id">
                <input type="hidden" name="orig_rsbsa" id="orig_rsbsa">
                <input type="hidden" name="farmer_id" id="farmer_id">
                <input type="hidden" name="orig_fname" id="orig_fname">
                <input type="hidden" name="orig_m_fname" id="orig_m_fname">
                <input type="hidden" name="prv" id="prv">

                @if(Auth::user()->roles->first()->name == "rcef-programmer")
                <input type="hidden" name="is_programmer" id="is_programmer" value="1">
                @else
                <input type="hidden" name="is_programmer" id="is_programmer" value="0">
                
                @endif
                

            </div>
            <div class="modal-footer" id="modal_footer">      
                <button class="btn btn-success btn-lg" onclick="updateFarmer();"> <i class="fa fa-floppy-o" aria-hidden="true"></i> Update</button>
            </div>
        </div>
    </div>
</div>
<!-- CURRENT RLA MODAL END -->

@endsection
@push('scripts')

    <script type="text/javascript">
                $("#modal_birthdate").datepicker();
            function updateFarmer(){
                var release_id = $("#release_id").val();
                var orig_rsbsa = $("#orig_rsbsa").val();
                var farmer_id = $("#farmer_id").val();
                var orig_fname = $("#orig_fname").val();
                var prv = $("#prv").val();
                 var orig_m_fname = $("#orig_m_fname").val();


                var farmer_fname = $("#modal_fname").val();
                var farmer_mname = $("#modal_mname").val();
                var farmer_lname = $("#modal_lname").val();
                var farmer_ext = $("#modal_farmer_ext").val();
                var sex = $('input[name="modal_sex"]:checked').val();
                var birthdate = $("#modal_birthdate").val();
                var contact = $("#modal_tel_number").val();

                var mother_fname = $("#modal_m_fname").val();
                var mother_mname = $("#modal_m_mname").val();
                var mother_lname = $("#modal_m_lname").val();
                var mother_suffix = $("#modal_m_suffix").val();

                var total_production = $("#modal_production").val();
                var ave_weight_per_bag = $("#modal_ave_weight").val();
                var area_harvested = $("#modal_area_harvested").val();


                var is_programmer = $("#is_programmer").val();

                $.confirm({
                        title: 'Farmer Information Update',
                        content: 'Are you sure to update farmer information?',
                        buttons: {
                            confirm: function () {
                               HoldOn.open(holdon_options);
                                 $.ajax({
                                        type: 'POST',
                                        url: "{{route('farmer.distributed.update')}}",
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            prv: prv,
                                            release_id: release_id,
                                            orig_rsbsa: orig_rsbsa,
                                            farmer_id: farmer_id,
                                            orig_fname: orig_fname,
                                            orig_m_fname: orig_m_fname,
                                            farmer_fname:farmer_fname,
                                            farmer_mname: farmer_mname,
                                            farmer_lname: farmer_lname,
                                            farmer_ext: farmer_ext,
                                            sex: sex,
                                            birthdate: birthdate,
                                            contact: contact,  
                                            mother_fname: mother_fname,
                                            mother_mname: mother_mname,
                                            mother_lname: mother_lname,
                                            mother_suffix: mother_suffix,
                                            total_production: total_production,
                                            ave_weight_per_bag: ave_weight_per_bag,
                                            area_harvested: area_harvested,
                                        },
                                        dataType: 'json',
                                        success: function(data){
                                            
                                            if(data["status"] == 1){
                                                if(is_programmer === "1"){
                                                 alert(data["log"]);
                                                }else{
                                                   $.alert('Farmer Updated'); 
                                                }

                                                 

                                            }else{
                                               
                                                $.alert(data["log"]);
                                            }     
                                            
                                           loadReleasedTbl();
                                              $('#update_farmer_info').modal("hide");
                                        HoldOn.close();

                                        },
                                        error: function(data){

                                             $.alert('Error Occured on processing');
                                            //alert(data);
                                            HoldOn.close();
                                        }
                                        });









                            },
                            cancel: function () {
                                    
                            }
                        }
                    });
       


            }            






            $('#update_farmer_info').on('show.bs.modal', function (e) {
            var release_id = $(e.relatedTarget).data('release_id');
            var rsbsa = $(e.relatedTarget).data('rsbsa');
            var farmer_id = $(e.relatedTarget).data('farmer_id');
            var farmer_fname = $(e.relatedTarget).data('farmer_fname');
            var farmer_mname = $(e.relatedTarget).data('farmer_mname');
            var farmer_lname = $(e.relatedTarget).data('farmer_lname');
            var prv = $(e.relatedTarget).data('prv');
            var farmer_ext = $(e.relatedTarget).data('farmer_ext');
            var sex = $(e.relatedTarget).data('sex');
            var birthdate = $(e.relatedTarget).data('birthdate');
            var tel_number = $(e.relatedTarget).data('tel_number');
            

            var mother_fname = $(e.relatedTarget).data('mother_fname');
            var mother_mname = $(e.relatedTarget).data('mother_mname');
            var mother_lname = $(e.relatedTarget).data('mother_lname');
            var mother_suffix = $(e.relatedTarget).data('mother_suffix');
            var total_production = $(e.relatedTarget).data('total_production');
            var ave_weight_per_bag = $(e.relatedTarget).data('ave_weight_per_bag');
            var area_harvested = $(e.relatedTarget).data('area_harvested');
         
            $('#release_id').empty().val(release_id);
            $('#orig_rsbsa').empty().val(rsbsa);
            $('#farmer_id').empty().val(farmer_id);
            $('#orig_fname').empty().val(farmer_fname);
            $('#orig_m_fname').empty().val(mother_fname);
            
            $('#prv').empty().val(prv);

            var initial_Sex = sex.charAt(0).toUpperCase();
        
            if(initial_Sex === "M"){
                $("#male").attr('checked', 'checked');
            }else{
                $("#female").attr('checked', 'checked');
            }



            $('#modal_rsbsa').empty().val(rsbsa);
            $('#modal_fname').empty().val(farmer_fname);
            $('#modal_mname').empty().val(farmer_mname);
            $('#modal_lname').empty().val(farmer_lname);

            $('#modal_farmer_ext').empty().val(farmer_ext);
            

            $('#modal_birthdate').empty().val(birthdate);
            $('#modal_tel_number').empty().val(tel_number);

            $('#modal_m_fname').empty().val(mother_fname);
            $('#modal_m_mname').empty().val(mother_mname);
            $('#modal_m_lname').empty().val(mother_lname);
       

            $('#modal_m_suffix').val("");
            $('#modal_production').empty().val(total_production);
            $('#modal_ave_weight').empty().val(ave_weight_per_bag);
            $('#modal_area_harvested').empty().val(area_harvested);

        });




            $('select[name="utilProvince"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province_code = $(this).val();
                var province = $(this).find("option:selected").text();
                var category = $('#category').val();
            if (province_code == 0){
            $('select[name="utilMunicipality"]').empty();
             $('select[name="utilMunicipality"]').append('<option value=0>Please select a municipality</option>');
              
             HoldOn.close();
            }else{

                $.ajax({
                            type: 'POST',
                            url: "{{route('util.get.municipality')}}" ,
                            data: {
                                _token: "{{ csrf_token() }}",
                                province: province,
                                category:category
                            },
                            dataType: 'json',
                            success: function(data){
                                $('select[name="utilMunicipality"]').empty();
                                 $('select[name="utilMunicipality"]').append('<option value=0>Please select a municipality</option>');
                                 $.each(data, function (i, d) {
                                    $('select[name="utilMunicipality"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                                }); 
                                            
                            
                            
                            HoldOn.close();

                            },
                            error: function(data){
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });
                   
            
            
            
            
            }

            });  //END PROVINCE SELECT

           

            $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });
            function loadReleasedTbl(){
               var province_code =  $('select[name="utilProvince"]').val();
               var municipality = $('select[name="utilMunicipality"]').val();

               var search_name = $('#search_name').val();
               var category = $('#category').val();
               
                if(province_code == "0"){
                    return alert("Please select a province");
                }

               

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
                    "url": "{{route('released.data.tbl.util')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        prv: province_code,
                        municipality: municipality,
                        search_name: search_name,
                        category:category,
                    }
                },
                "columns":[
                    {"data": "rcef_id"},
                    
                    {"data": "rsbsa_control_no"},
                    {"data": "lastName" },
                    {"data": "firstName"},
                    {"data": "midName"},
                    
                    // {"data": "brgy_name"},
                    {"data": "actual_area",  "className": "text-right"},
                    {"data": "rel_seed_variety"},
                    {"data": "rel_bags_claimed", "className": "text-right"},
                    {"data": "rel_claimed_area" ,  "className": "text-right"},
                  
                    {"data": "rel_date_released", 'searchable': false, 'orderable': false},
                    {"data": "rel_released_by"},
                    {"data": "action", 'searchable': false, 'orderable': false}
                  
                ]
            });
          

            }


            function reset(released_id, rsbsa, fid, fname,prv){
                

                var msg = confirm("Reset Distribution Data? \n"+ rsbsa + ": "+fname);
     
                if(msg){
                    var db_bu = "no";
                    HoldOn.open(holdon_options);
                    $.ajax({
                            type: 'POST',
                            url: "{{route('farmer.distridata.released')}}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                released_id: released_id,
                                rsbsa: rsbsa,
                                fid: fid,
                                fname: fname,
                                prv: prv,
                                db_bu: db_bu
                            },
                            dataType: 'json',
                            success: function(data){
                              
                             if(data == "success"){
                                 alert("Successfully Reset");
                                 loadReleasedTbl();
                             }else{
                                 alert("ERROR ON RESETTING");
                                 loadReleasedTbl();
                             }
                            HoldOn.close();

                            },
                            error: function(data){
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });


                  
                }



            }

            $("#utilProcess_find").on("click", function(){
                loadReleasedTbl();

            })


           


  $('select[name="utilProvince"]').select2();
    $('select[name="utilMunicipality"]').select2();




    </script>

@endpush