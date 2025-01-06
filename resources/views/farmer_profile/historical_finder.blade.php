@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Historical Farmer Finder </h3>
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
                            <label for="season" id="season_label">Select Season</label>
                        <select name="season" id="season" class="form-control" data-parsley-min="1">
                            
                            @foreach ($season as $season)
                            <option value="{{$season->acronym}}">{{$season->name}}</option>
                            @endforeach
                        </select>
                    </div>



                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label for="utilProvince" id="label_province">Province  </label>
                        <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1">

                            <option value="0">Please select a province</option>
                            @foreach ($provinces as $provinces)
                            <option value="{{$provinces->province}}">{{$provinces->province}}</option>
                            @endforeach
                        </select>
                    </div>


                    <div id="dialog" title="Prompt">
                        <p>Please input search value</p>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-12"> 
                            <label for="rsbsa_search" id="label_rsbsa">RSBSA #  </label>
                            <input type="text" id="rsbsa_search" class="form-control" name="rsbsa_search" placeholder="RSBSA #">
                        </div>
                    </div>
                    
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-6"> 
                            <label for="last_search" id="label_last">Last Name   </label>
                            <input type="text" id="last_search" class="form-control" name="last_search" placeholder="Last Name">
                        </div>

                        <div class="col-md-6"> 
                            <label for="first_search" id="label_first">First Name   </label>
                            <input type="text" id="first_search" class="form-control" name="first_search" placeholder="First Name">
                        </div>


                        
                    </div>


                </div>

                <div class="form-group">
                    {{-- <div class="col-md-12" style="text-align:center; margin-top:5px; color: ">                        
                        <button type="button" name="open_stored_zip" id="open_stored_zip" class="btn btn-md" style=" width:200px; background-color: #e7e7e7; color: black;" ><i class="fa fa-file-archive-o" aria-hidden="true"></i> Download Stored ID ZIP</button>
                    </div> --}}


                    <div class="col-md-12" style="text-align:center; margin-top:5px;">
                        <button type="button" name="search" id="search" class="btn btn-md btn-success" style="width:150px;margin: 5px;" ><i class="fa fa-search" aria-hidden="true"></i> Find Farmer </button>
                    </div>

                </div>
            </div>
        

            </div>

        </div>


        <div class="x_content form-horizontal form-label-left">
                




                            <div class="form-group">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                                            <thead>
                                                <th style="width: 15%;">RSBSA # </th>
                                                <th style="width: 18%;">Name (Last, Ext, First, Middle) </th>
                                                <th style="width: 25%;">Address</th>
                                                <th style="width: 5%;">Final Area</th>


                                                <th style="width: 5%;">Sex</th>
                                                
                                                <th style="width: 5%;">Birthdate</th>
                                                
                                                <th style="width: 7%;">Contact Number</th>
                                                <th >Action</th>
                                                
                                                
                                            </thead>
                                            <tbody id='databody'>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
        </div>
    </div>
   


    <div id="modal_farmer_info" class="modal fade" role="dialog" >
        <div class="modal-dialog" style="width: 40%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">
                        <span>Farmer Information</span><br>
                    </h4>
                </div>
    
                <div class="modal-body">
                    <div class="col-md-12" style="color: #154c79; font-weight: bold; ">
                        <div class="row">
                            
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >RSBSA #</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="rsbsa_modal" ></label></div>
                        </div>
                        @if(Auth::user()->roles->first()->name == "rcef-programmer")
                            <div class="row">
                                <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-warning" >RCEF ID</label></div>
                                <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="rcef_id_modal" ></label></div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Last Name</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="last_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >First Name</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="first_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Middle Name</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="mid_modal" ></label></div>
                        </div>
                         <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Ext. Name</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="ext_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Sex</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="sex_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Province</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="province_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Municipality</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="municipality_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Brgy</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="brgy_modal" ></label></div>
                        </div>
                            
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Birthdate</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="bday_modal" ></label></div>
                        </div>
                            
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Contact</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="contact_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Final Area</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="final_modal" ></label></div>
                        </div>
                            
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-info" >Final Claimable</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="claimable_modal" ></label></div>
                        </div>

                        @if(Auth::user()->roles->first()->name == "rcef-programmer")
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-warning" >DATA SOURCE</label></div>
                            <div class="col-md-8" ><label class="btn btn-success" style="width:100%" id="data_source_modal" ></label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-warning" >CROP AREA</label></div>
                            <div class="col-md-6" ><label class="btn btn-success" style="width:100%" id="crop_area_modal" ></label></div>
                            <div class="col-md-2" id="crop_btn"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-warning" >RSMS AREA</label></div>
                            <div class="col-md-6" ><label class="btn btn-success" style="width:100%" id="rsms_area_modal" ></label></div>
                            <div class="col-md-2" id="rsms_btn"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: right;" ><label for="" style="width:100%" class="btn btn-warning" >WS2022 AREA</label></div>
                            <div class="col-md-6" ><label class="btn btn-success" style="width:100%" id="ws2022_area_modal" ></label></div>
                            <div class="col-md-2" id="ws22_btn"></div>
                        </div>
                    @endif
                         
                    </div>
    
                </div>
                <div class="modal-footer" id="modal_footer">      
                    
                </div>
            </div>
        </div>
    </div>



@endsection
@push('scripts')

    <script type="text/javascript">

            function push(prv, season, id){
                var yesno = confirm("Push This farmer to current season?");
                HoldOn.open(holdon_options);
                if(yesno){
                    $.ajax({
                        type: 'POST',
                        url: "{{route('history.farmer.push')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                           id: id,
                           season: season,
                           prv: prv
                        },
                        dataType: 'json',
                        success: function(result){
                            alert(result);
                          
                            HoldOn.close();
                        },
                        error: function(result){
                           
                          
                            HoldOn.close();
                        }
                        });


                }


                
            }





            function changeArea(id,rcef_id,area,prv){
                var yesno = confirm("Change reflected area of this farmer?");
                if(yesno){
                    HoldOn.open(holdon_options);
                    $.ajax({
                        type: 'POST',
                        url: "{{route('farmer.change.area')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                           id: id,
                           rcef_id: rcef_id,
                           prv: prv,
                           area: area
                        },
                        dataType: 'json',
                        success: function(result){
                            alert(result);
                            view_info(rcef_id,prv);
                            HoldOn.close();
                        },
                        error: function(result){
                           
                            view_info(rcef_id,prv);
                            HoldOn.close();
                        }
                        });
                }


              





            }


            function view_info(rcef_id,prv){
                $.ajax({
                        type: 'POST',
                        url: "{{route('farmer.finder.info')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                           rcef_id: rcef_id,
                           prv: prv
                        },
                        dataType: 'json',
                        success: function(data){
                         
                            $("#rsbsa_modal").empty().append(data.rsbsa_control_no);
                            $("#rcef_id_modal").empty().append(data.rcef_id);
                            $("#last_modal").empty().append(data.lastName);
                            $("#first_modal").empty().append(data.firstName);
                            $("#mid_modal").empty().append(data.midName);
                            $("#ext_modal").empty().append(data.extName);
                            $("#sex_modal").empty().append(data.sex);
                            $("#province_modal").empty().append(data.province);
                            $("#municipality_modal").empty().append(data.municipality);
                            $("#brgy_modal").empty().append(data.brgy_name);
                            $("#bday_modal").empty().append(data.birthdate);
                            $("#contact_modal").empty().append(data.tel_no);
                            $("#final_modal").empty().append(data.final_area+" (ha)");
                            $("#claimable_modal").empty().append(data.final_claimable+" bag(s)");
                            $("#data_source_modal").empty().append(data.data_source);
                            $("#crop_area_modal").empty().append(data.crop_area);
                            $("#rsms_area_modal").empty().append(data.rsms_actual_area);
                            $("#ws2022_area_modal").empty().append(data.ws2022_area);
                            
                            

                            if(data.crop_area > 0){
                                    var crop_btn = '<button class="btn btn-danger" onclick="changeArea('+data.id+','+"'"+data.rcef_id+"'"+','+"'"+data.crop_area+"'"+','+"'"+prv+"'"+');"><i class="fa fa-exchange" aria-hidden="true">Use this area</i> </button>';
                                
                            }else{
                                var crop_btn = '<button class="btn btn-danger" disabled><i class="fa fa-exchange" aria-hidden="true">Use this area</i> </button>';
                            }
                                $("#crop_btn").empty().append(crop_btn);

                            if(data.rsms_actual_area > 0){
                              
                                    var rsms_btn = '<button class="btn btn-danger" onclick="changeArea('+data.id+','+"'"+data.rcef_id+"'"+','+"'"+data.rsms_actual_area+"'"+','+"'"+prv+"'"+');"><i class="fa fa-exchange" aria-hidden="true">Use this area</i> </button>';
                                
                            }else{
                                var rsms_btn = '<button class="btn btn-danger" disabled><i class="fa fa-exchange" aria-hidden="true">Use this area</i> </button>';
                            }
                                $("#rsms_btn").empty().append(rsms_btn);

                            if(data.ws2022_area > 0){
                              
                                    var ws22_btn = '<button class="btn btn-danger" onclick="changeArea('+data.id+','+"'"+data.rcef_id+"'"+','+"'"+data.ws2022_area+"'"+','+"'"+prv+"'"+');"><i class="fa fa-exchange" aria-hidden="true">Use this area</i> </button>';
                                


                                
                            }else{
                                var ws22_btn = '<button class="btn btn-danger" disabled><i class="fa fa-exchange" aria-hidden="true">Use this area</i> </button>';
                            }
                            
                            
                                $("#ws22_btn").empty().append(ws22_btn);

                        
                        },
                        error: function(data){
                            
                        }
                        });
            }


            $('#modal_farmer_info').on('show.bs.modal', function (e) {
                var rcef_id = $(e.relatedTarget).data('rcef_id');
                var prv = $(e.relatedTarget).data('prv');
                
                view_info(rcef_id,prv);


            });


            $( "#dialog" ).dialog({
                autoOpen: false,
                show: {
                    effect: "blind",
                    duration: 400
                },
                hide: {
                    effect: "explode",
                    duration: 400
                }
                });
 

            function reprint_id(rcef_id,tbl){

                var yesno = confirm("Reprint ID?");
                if(yesno){
                    var municipality = "1";
                var type = "reprint";
                var SITE_URL = "{{url('/')}}";
                window.open(SITE_URL+'/create/rcef/id/card/'+tbl+'/'+municipality+'/'+rcef_id+'/'+type,"_blank"); 
                }

               
            }



            $("#search").on('click', function(){    
                
                var province = $("#utilProvince").val();
                var rsbsa = $("#rsbsa_search").val();
                var first = $("#first_search").val();
                var last = $("#last_search").val();
                

                if(province == "0"){
                    $("#label_province").attr("style", "color: red;");
                    $("#label_province").effect("bounce", "", 500);
                    return;
                }else{
                    $("#label_province").attr("style", "color: #154c79;");
                }

                if(rsbsa == "" && first == "" && last == "" ){
                    $("#label_first").attr("style", "color: red;");
                    $("#label_first").effect("bounce", "", 500);
                    $("#label_last").attr("style", "color: red;");
                    $("#label_last").effect("bounce", "", 500);
                    $("#label_rsbsa").attr("style", "color: red;");
                    $("#label_rsbsa").effect("bounce", "", 500);

                    $( "#dialog" ).dialog("open");
                    return;
                }else{
                    $("#label_first").attr("style", "color: #154c79;");
                    $("#label_last").attr("style", "color: #154c79;");
                    $("#label_rsbsa").attr("style", "color: #154c79;");
                    $( "#dialog" ).dialog("close");

                 
                }


                loadTable();



            });

        
      


        

           

            $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
             });


             function loadTable(){
                var province = $("#utilProvince").val();
                var rsbsa = $("#rsbsa_search").val();
                var lastname = $("#last_search").val();
                var firstname = $("#first_search").val();
                var season = $("#season").val();


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
                    "url": "{{route('history.farmer.finder.table')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        "province": province,
                        "rsbsa": rsbsa,
                        "lastname": lastname,
                        "firstname": firstname,
                        "season": season
                    }
                },
                "columns":[
                    {"data": "rsbsa" },
                    {"data": "name" },
                    {"data": "address" },
                    {"data": "final_area" },
                    
                    {"data": "sex","className": "text-center"},
                    {"data": "birthdate","className": "text-center"},
                    {"data": "contact_number","className": "text-right"},
                    {"data": "action" }
                ]
            });

             }

           





            


       


$("#modal_birthdate").datepicker();

  $('select[name="utilProvince"]').select2();
    $('select[name="utilMunicipality"]').select2();
    $('select[name="utilBrgy"]').select2();



    </script>

@endpush