@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Farmer View </h3>
            </div>
        </div>

            <div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong>  Use this only for updating farmer information
            </div>
        </div>                  



                           
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province  </label>
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
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Search Field (RSBSA/LAST,FIRST,MID NAME)</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                   <input type="text" name = "search_text" id="search_text" class='form-control' disabled>
                                    
                                    <br>    <br>
                                     <button type="button" name="utilProcess" id="utilProcess" class="btn btn-lg btn-primary" ><i class="fa fa-sign-in"></i> Search Farmer </button>
                                     
                                     
                                                            

                                </div>
                            </div>


                            <div class="form-group">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                                            <thead>
                                                <th   style="width: 300px;">Rsbsa</th>
                                                <th   style="width: 300px;">Farmer ID</th>
                                                
                                                <th style="width: 150px;">Last Name</th>
                                                <th style="width: 150px;">First Name</th>
                                                <th style="width: 150px;">Middle Name</th>
                                                <th  style="width: 100px;">Birthdate</th>
                                                <th  style="width: 50px;">Actual Area</th>
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
<div id="pre_reg_farmerinfo" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Farmer Profile Updating</span><br>
                </h4>
            </div>

            <div class="modal-body">
                
                <div class="col-xs-12">
                    <label for="" class="col-xs-12"  style="color: #f3a72d;"><strong> <big> Farmer Information </big> </strong> </label>
                    <label for="modal_rsbsa" class="col-xs-3">RSBSA #:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_rsbsa" id="modal_rsbsa" disabled> <br>
                    <label for="modal_fname" class="col-xs-3">First Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_fname" id="modal_fname" > <br>
                    <label for="modal_mname" class="col-xs-3">Middle Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_mname" id="modal_mname" > <br>
                    <label for="modal_lname" class="col-xs-3">Last Name: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_lname" id="modal_lname" > <br>  
                    <label for="modal_lname" class="col-xs-3">Ext Name: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_ename" id="modal_ename" > <br>  
                    <label for="modal_lname" class="col-xs-3">Actual Area: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_actual_area" id="modal_actual_area" disabled> <br>  
                    <label for="modal_sex" class="col-xs-3">Sex: </label>
                   
                    <div class="col-md-9">
                        <input type="radio" id="male" value="Male" name="modal_sex" disabled> <label for='male'> Male</label> 
                        <input type="radio" id="female" value="Female" name="modal_sex" disabled> <label for='female'> Female</label>  
                    </div>

                    <label for="modal_birthdate" class="col-xs-3">Birthdate: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_birthdate" id="modal_birthdate" > <br> 
                    <label for="modal_tel_number" class="col-xs-3" >Contact Number: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_tel_number" id="modal_tel_number" > <br> 
                </div>

                 <!-- <div class="col-xs-6">
                    <label for="" class="col-xs-12"  style="color: #f3a72d;"><strong> <big> Other Information </big> </strong> </label>
                    
                    <label for="modal_m_fname" class="col-xs-3">Mother First Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_fname" id="modal_m_fname" > <br>
                    <label for="modal_fname" class="col-xs-3">Mother Middle Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_mname" id="modal_m_mname" > <br>
                    <label for="modal_mname" class="col-xs-3">Mother Last Name:</label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_lname" id="modal_m_lname" > <br>
                    <label for="modal_lname" class="col-xs-3">Mother Suffix: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_m_suffix" id="modal_m_suffix" > <br>


                    <label for="" class="col-xs-12" style="color: #f3a72d;"><strong> <big> Yield Information </big> </strong> </label>
                  
                    <label for="modal_production" class="col-xs-3">Production: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_production" id="modal_production" > <br>

                    <label for="modal_ave_weight" class="col-xs-3">Average Weight per Bag: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_ave_weight" id="modal_ave_weight" > <br>

                    <label for="modal_area_harvested" class="col-xs-3">Area Harvested: </label>
                    <input type="text" style="width: 250px;" class="form-control" name="modal_area_harvested" id="modal_area_harvested" > <br>

                </div>
 -->

                <input type="hidden" name="id" id="id">
                <input type="hidden" name="oth_id" id="oth_id">
                
                <input type="hidden" name="rsbsa" id="rsbsa">
                <input type="hidden" name="farmer_id" id="farmer_id">
               
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
                var id = $("#id").val();
                var oth_id = $("#oth_id").val();

                var prv = $("#prv").val();
            
                var birthdate = $("#modal_birthdate").val();
                var contact = $("#modal_tel_number").val();

                var is_programmer = $("#is_programmer").val();

                var rsbsa = $("#rsbsa").val();
                var fname = $("#modal_fname").val();
                var lname = $("#modal_lname").val();
                var mname = $("#modal_mname").val();
                var ename = $("#modal_ename").val();
                var farmer_id = $("#farmer_id").val();
                $.confirm({
                        title: 'Farmer Information Update',
                        content: 'Are you sure to update farmer information?',
                        buttons: {
                            confirm: function () {
                               HoldOn.open(holdon_options);
                                 $.ajax({
                                        type: 'POST',
                                        url: "{{route('pre_reg.update.farmer')}}",
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            prv: prv,
                                            is_programmer: is_programmer,
                                            contact: contact,
                                            prv: prv,
                                            oth_id: oth_id,
                                            id: id,
                                            birthdate:birthdate,
                                            fname: fname,
                                            lname: lname,
                                            mname: mname,
                                            ename: ename,
                                            farmer_id: farmer_id,
                                            rsbsa: rsbsa
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
                                              $('#pre_reg_farmerinfo').modal("hide");
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






            $('#pre_reg_farmerinfo').on('show.bs.modal', function (e) {

           
            var id = $(e.relatedTarget).data('id');
            var oth_id = $(e.relatedTarget).data('oth_id');
            
            var rsbsa = $(e.relatedTarget).data('rsbsa');
            var farmer_id = $(e.relatedTarget).data('farmer_id');
            var farmer_fname = $(e.relatedTarget).data('farmer_fname');
            var farmer_mname = $(e.relatedTarget).data('farmer_mname');
            var farmer_lname = $(e.relatedTarget).data('farmer_lname');
            var farmer_ename = $(e.relatedTarget).data('farmer_ename');
            
            var actual_area = $(e.relatedTarget).data('actual_area');
            
            var prv = $(e.relatedTarget).data('prv');
            var sex = $(e.relatedTarget).data('sex');
            var birthdate = $(e.relatedTarget).data('birthdate');
            var tel_number = $(e.relatedTarget).data('contact');
          
          
      
            $('#id').empty().val(id);
            $('#oth_id').empty().val(oth_id);
            
            $('#rsbsa').empty().val(rsbsa);
            $('#farmer_id').empty().val(farmer_id);
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
            $('#modal_ename').empty().val(farmer_ename);
            $('#modal_actual_area').empty().val(actual_area);

            $('#modal_birthdate').empty().val(birthdate);
            $('#modal_tel_number').empty().val(tel_number);
          
           

        });




            $('select[name="utilProvince"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province_code = $(this).val();
                var province = $(this).find("option:selected").text();

            if (province_code == 0){
          
              
             $("#search_text").removeAttr("disabled");
             $("#search_text").attr("disabled", "disabled");
             HoldOn.close();
            }else{

                $("#search_text").removeAttr("disabled");
                HoldOn.close();
                
                   
            
         
            
            
            }

            });  //END PROVINCE SELECT

           

            $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });
            function loadReleasedTbl(){
               var province_code =  $('select[name="utilProvince"]').val();
               var search_text = $('#search_text').val();

                if(province_code == "0"){
                    return alert("Please select a province");
                }

                if(search_text == ""){
                    return alert("Please input search Value");
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
                    "url": "{{route('pre_reg.load.farmer')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        prv: province_code,
                        search_text: search_text,
                    }
                },
                "columns":[
                    {"data": "rsbsa_control_no"},
                    {"data": "rcef_id"},
                    
                    {"data": "lastName" },
                    {"data": "firstName"},
                    {"data": "midName"},
                    {"data": "birthdate"},
                    {"data": "actual_area",  "className": "text-right"},
                    
                    
                  
                    {"data": "action", 'searchable': false, 'orderable': false}
                  
                ]
            });






            }


            function reset(released_id, rsbsa, fid, fname,prv){
                

                var msg = confirm("Reset Distribution Data? \n"+ rsbsa + ": "+fname);
     
                if(msg){

                    var backup = confirm("BACKUP DATABASE?");
               
                if(backup){
                    var db_bu = "yes";
                }else{
                    var db_bu = "no";
                }





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


            document.getElementById("utilProcess").addEventListener("click", function() {
                    loadReleasedTbl();
            });  


  $('select[name="utilProvince"]').select2();




    </script>

@endpush