@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Area Troubleshooting </h3>
            </div>
        </div>

            <div class="clearfix"></div>

        
    

        <div class="card">
            <div class="col-md-12">

            <div class="col-md-4">

            </div>


            <div class="col-md-4">
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <br>
                        <label for="utilProvince" id="label_province">Province  </label>
                        <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1">

                            <option value="0">Please select a province</option>
                            @foreach ($provinces as $provinces)
                            <option value="{{$provinces->prv_code}}">{{$provinces->province}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
           
           
                <div class="form-group">
            
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label for="utilMunicipality" id="label_municipality">Municipality</label>
                        <select name="utilMunicipality" id="utilMunicipality" class="form-control" data-parsley-min="1" >
                            <option value="0">Please select a municipality</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
            
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label for="utilBrgy" id="label_brgy">Search RSBSA, Last Name, First Name, RCEF ID</label>
                    
                        <input type='text' name='search' id='search' class="form-control">


                    </div>
                </div>
                <div class="form-group">

                    <div class="col-md-12" style="text-align:center; margin-top:5px;">
                         <button type="button" name="genTable" id="genTable" class="btn btn-md btn-primary" style=" width:200px;"  disabled="disabled"> GENERATE TABLE </button>
                    </div>
                </div>
            </div>
        
   <div class="col-md-4">
                
            </div>

          

            
            </div>

        </div>


        <div class="x_content form-horizontal form-label-left">
       



                            <div class="form-group">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                                            <thead>
                                                <th >RSBSA</th>
                                                <th >RCEF ID</th>
                                                <th >Last Name</th>
                                                <th >First Name</th>
                                                <th >Middle Name</th>
                                                <th >BRGY</th>
                                                <th >FFRS AREA</th>
                                                <th >RSMS AREA</th>
                                                <th >Claimable Bags</th>
                                                
                                                <th >Active Area</th>
                                                
                                                
                                            </thead>
                                            <tbody id='databody'>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
        </div>
    </div>
   







@endsection
@push('scripts')

    <script type="text/javascript">
            $("#genTable").on("click", function (){
                loadReleasedTbl();
            });


            $('select[name="utilProvince"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province_code = $(this).val();
                var province = $(this).find("option:selected").text();

            if (province_code == 0){
            $('select[name="utilMunicipality"]').empty();
             $('select[name="utilMunicipality"]').append('<option value=0>Please select a municipality</option>');
              
             HoldOn.close();
            }else{

                $.ajax({
                            type: 'POST',
                            url: "{{route('rcef.id.municipality')}}" ,
                            data: {
                                _token: "{{ csrf_token() }}",
                                province: province
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


            $('select[name="utilMunicipality"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province = $("#utilProvince").val();
                var municipality = $(this).val();
                

            if (municipality == 0){
                $("#genTable").removeAttr("disabled");
                $("#genTable").attr("disabled");
             HoldOn.close();
            }else{
                $("#genTable").removeAttr("disabled");
                
                
            HoldOn.close(); 
            
            }

            });  //END MUNICIPALITY SELECT


           

            $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });


  
            function loadReleasedTbl(){
            var province = $("#utilProvince").val();
            var municipality = $("#utilMunicipality").val();
            var search = $("#search").val();

            if(search === ""){
                search = 'all';
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
                    "url": "{{route('area.troubleshoot.ui')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "prov_code": province,
                        "municipality": municipality,
                        "search" : search
                    }
                },
                "columns":[
                    {"data": "rsbsa_control_no" },
                    {"data": "rcef_id" },
                    
                    {"data": "lastName" },
                    {"data": "firstName" },
                    {"data": "middleName" },
                    {"data": "brgy" },
                    {"data": "ffrs","className": "text-right" },
                    {"data": "rsms","className": "text-right" },
                    {"data": "final_claimable","className": "text-right" },
                    
                    {"data": "action" }
                ]
            });






            }


       
            function change_area(id,prv_code,value){
       

                var yesNo = confirm("Update Final Area of Farmer?");
                if(yesNo){
                    HoldOn.open(holdon_options);
                    $.ajax({
                            type: 'POST',
                            url: "{{route('area.troubleshoot.change')}}" ,
                            data: {
                                _token: "{{ csrf_token() }}",
                                id : id,
                                prv_code: prv_code,
                                value: value
                            },
                            dataType: 'json',
                            success: function(data){
                                loadReleasedTbl();
                               alert("success!");
                         
                            HoldOn.close();
                            },
                            error: function(data){
                                loadReleasedTbl();
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });

                }else{
                    loadReleasedTbl();
                }

            }


    </script>

@endpush