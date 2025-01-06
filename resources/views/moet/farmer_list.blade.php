@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Farmer List</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            <!--
        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Please avoid processing large amount of rows. <b><u>[ Maximum of 1000 rows per process ]</u></b> this is to eliminate or minimize loading time.
            </div>
        </div> -->

             <div class="x_panel">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Region</label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <select name="cmbRegion" id="cmbRegion" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a Region</option>

                                        @foreach ($region_list as $region)
                                                <option value="{{ $region->regCode }}">{{ $region->regDesc}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <select name="cmbProvince" id="cmbProvince" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                    
                                    </select>
                                </div>
       						</div>

       						<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <select name="cmbMunicipality" id="cmbMunicipality" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a municipality</option>
                                    </select>
                                </div>
                            </div>
        
               </div>       

               <div class="x_panel">
                    <div class="x_content form-horizontal form-label-left">
                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                            <thead>
                                <th style="width: 200px;">Name</th>
                                <th>Gender</th>
                                <th>Province</th>
                                <th>Municipality</th>
                                <th>Brgy</th>
                                <th>Contact Number</th>
                                <th style="width: 150px;">Variety</th>
                                <th>Establishment</th>
                                <th>Soil Type</th>
                                
                                <th>Date Synced</th>
                                
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
<div id="edit_farmer" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Edit Farmer Information:</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Farmer Name:</label>
                <label id="modal_name_edit"></label> <br>
                <label for="" class="col-xs-3">Contact #: </label>
                <label id="modal_contact_edit"></label> <br>
                <label for="" class="col-xs-3">Establishment: </label>
                <label id="modal_establishment_edit"></label> <br>
                <label for="" class="col-xs-3">Soil Texture: </label>
                <label id="modal_texture_edit"></label> <br>
                <label for="" class="col-xs-3">Cropping Pattern: </label>
                <label id="modal_pattern_edit"></label> <br>
                <label for="" class="col-xs-3">Eco system: </label>
                <label id="modal_eco_edit"></label> <br>
                
                <label for="" class="col-xs-3">Province: </label>
                <label class="col-xs-9"> <select id="cmbProvince_edit" name="cmbProvince_edit" class="form-control" style="width: 300px; margin-bottom: 10px;">
                </select> </label> 
                <label for="" class="col-xs-3">Municipality: </label>
                <label class="col-xs-9">
                    <select id="cmbMunicipality_edit" name="cmbMunicipality_edit" class="form-control" style="width: 300px; margin-bottom: 10px;">
                    </select>    
                </label>
                
                <label for="" class="col-xs-3">Brgy: </label>
                <label class="col-xs-9">
                <input id="brgy_edit" name="brgy_edit" class="form-control" style="width: 300px; margin-bottom: 10px;" type="text" placeholder="Barangay">
                </label>


                <label for="" class="col-xs-3">Next Variety: </label>
                <label class="col-xs-9">
                <select id="nxtVar" name="nxtVar" class="form-control" style="width: 300px; margin-bottom: 10px;">
                </select>
                </label>
                <label for="" class="col-xs-3">Previous Variety: </label>
                <label class="col-xs-9">
                <select id="prvVar" name="prvVar" class="form-control" style="width: 300px;">
                </select>
            </label>


                  


                <input type="hidden" name="farmerid_edit" id="farmerid_edit">
            </div>
            <div class="modal-footer" id="modal_footer">      
                <button class="btn btn-success btn-sm" onclick="update_farmer()"><i class="fa fa-floppy-o" aria-hidden="true"></i> Update</button>
            </div>
        </div>
    </div>
</div>
<!-- CURRENT RLA MODAL END -->














        <!-- CURRENT RLA MODAL -->
<div id="farmer_info" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Farmer Information:</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Farmer Name:</label>
                <label id="modal_name"></label> <br>
                <label for="" class="col-xs-3">Location:</label>
                <label id="modal_location"></label> <br>
                <label for="" class="col-xs-3">Contact #: </label>
                <label id="modal_contact"></label> <br>
                <label for="" class="col-xs-3">Next Variety: </label>
                <label id="modal_nvar"></label> <br>
                <label for="" class="col-xs-3">Previous Variety: </label>
                <label id="modal_pvar"></label> <br>
                <label for="" class="col-xs-3">Establishment: </label>
                <label id="modal_establishment"></label> <br>
                <label for="" class="col-xs-3">Yield (tons/ha): </label>
                <label id="modal_yield"></label> <br>
                <label for="" class="col-xs-3">Ave. Wgt/bag: </label>
                <label id="modal_weight"></label> <br>
                <label for="" class="col-xs-3">Straw: </label>
                <label id="modal_straw"></label> <br>
                <label for="" class="col-xs-3">Planting Date: </label>
                <label id="modal_planting"></label> <br>
                <label for="" class="col-xs-3">Size: </label>
                <label id="modal_size"></label> <br>
                <label for="" class="col-xs-3">Soil Texture: </label>
                <label id="modal_texture"></label> <br>
                <label for="" class="col-xs-3">Cropping Pattern: </label>
                <label id="modal_pattern"></label> <br>
                <label for="" class="col-xs-3">Eco system: </label>
                <label id="modal_eco"></label> <br>

                <input type="hidden" name="farmerid" id="farmerid">
            </div>
            <div class="modal-footer" id="modal_footer">      
            </div>
        </div>
    </div>
</div>
<!-- CURRENT RLA MODAL END -->
@endsection
@push('scripts')

	<script type="text/javascript">

        function update_farmer(){
            var yesno = confirm("Update Farmer Profile?");

            if(yesno){
                  HoldOn.open("sk-cube-grid");
                var farmerid = $("#farmerid_edit").val();
                var province =  $('select[name="cmbProvince_edit"]').val();
                var municipality =  $('select[name="cmbMunicipality_edit"]').val();
                var brgy =  $("#brgy_edit").val();
                var nvar = $('select[name="nxtVar"]').val();
                var pvar =  $('select[name="prvVar"]').val();


               

                 $.ajax({
                    method: 'POST',
                    url: "{{route('moet.update.farmer.info')}}",
                    data: {
                        _token: _token,
                        farmerid: farmerid,
                        province: province,
                        municipality: municipality,
                        brgy: brgy,
                        nvar: nvar,
                        pvar: pvar
                    },
                    dataType: 'json',
                    success: function (source) {
                       if(source == "success"){
                        alert("Farmer Info Updated");
                        $('#edit_farmer').modal("hide");
                        loadTable();
                       }else{
                         alert("Failed to Update");
                       }


                         HoldOn.close();
                    },error: function (request, status, error) {
                                alert("Failed to Update");
                                HoldOn.close();
                            }


                }); //AJAX GET MUNICIPALITY 



  
            }

            
          


        }


         $('#edit_farmer').on('show.bs.modal', function (e) {
            HoldOn.open("sk-cube-grid");
            var name = $(e.relatedTarget).data('name');
            var location = $(e.relatedTarget).data('location');
            var contact = $(e.relatedTarget).data('contact');
            var nvar = $(e.relatedTarget).data('nvar');
            var pvar = $(e.relatedTarget).data('pvar');
            var establishment = $(e.relatedTarget).data('establishment');
            var yieldz = $(e.relatedTarget).data('yield');
            var weight = $(e.relatedTarget).data('weight');
            var straw = $(e.relatedTarget).data('straw');
            var planting = $(e.relatedTarget).data('planting');
            var size = $(e.relatedTarget).data('size');
            var texture = $(e.relatedTarget).data('texture');
            var pattern = $(e.relatedTarget).data('pattern');
            var eco = $(e.relatedTarget).data('eco');
            var farmerid = $(e.relatedTarget).data('farmerid');
            
            var province = $(e.relatedTarget).data('province');
            var municipality = $(e.relatedTarget).data('municipality');
            var brgy = $(e.relatedTarget).data('brgy');
            var provcode = $(e.relatedTarget).data('provcode');
            var municode = $(e.relatedTarget).data('municode');


             $.ajax({
                    method: 'POST',
                    url: "{{route('moet.get.province_list')}}",
                    data: {
                        _token: _token,
                        provcode: provcode,
                        type: "all"
                    },
                    dataType: 'json',
                    success: function (source) {
                        var selected = "";
                        $('select[name="cmbProvince_edit"]').empty();
                        $.each(source, function (i, d) {
                                if(provcode === d.provCode){
                                    selected = "selected";
                                }else{
                                    selected = "";
                                }

                            $('select[name="cmbProvince_edit"]').append('<option value="' + d.provCode + '" '+selected+ '>' + d.provDesc + '</option>');
                        }); 
                            $.ajax({
                                method: 'POST',
                                url: "{{route('moet.get.municipality_list')}}",
                                data: {
                                    _token: _token,
                                    provCode: provcode,
                                    type: "all"
                                },
                                dataType: 'json',
                                success: function (source) {
                                    var muni_selected = "";
                                     $('select[name="cmbMunicipality_edit"]').empty();
                                    $.each(source, function (i, d) {
                                        if(municode === d.citymunCode){
                                                muni_selected = "selected";
                                        }else{
                                            muni_selected = "";
                                        }


                                        $('select[name="cmbMunicipality_edit"]').append('<option value="' + d.citymunCode + '" '+muni_selected+'>' + d.citymunDesc + '</option>');
                                    }); 
                                  
                                        $("#brgy_edit").empty().val(brgy);


                                        $.ajax({
                                            method: 'POST',
                                            url: "{{route('moet.get.seed_list')}}",
                                            data: {
                                                _token: _token,
                                                var: nvar
                                            },
                                            dataType: 'json',
                                            success: function (source) {
                                           
                                                 $('select[name="nxtVar"]').empty();
                                                $.each(source, function (i, d) {
                                                    $('select[name="nxtVar"]').append('<option value="' + d.variety + '" '+d.selected+'>' + d.variety + '</option>');
                                                }); 
                                                        $.ajax({
                                                    method: 'POST',
                                                    url: "{{route('moet.get.seed_list')}}",
                                                    data: {
                                                        _token: _token,
                                                        var: pvar
                                                    },
                                                    dataType: 'json',
                                                    success: function (source) {
                                                       
                                                         $('select[name="prvVar"]').empty();
                                                        $.each(source, function (i, d) {
                                                            $('select[name="prvVar"]').append('<option value="' + d.variety + '" '+d.selected+'>' + d.variety + '</option>');
                                                        }); 
                                                         HoldOn.close();
                                                    }
                                                }); //AJAX GET MUNICIPALITY 
                                            }
                                        }); //AJAX GET MUNICIPALITY 
                                }
                            }); //AJAX GET MUNICIPALITY 
                    }
                }); //AJAX GET PROVINCE







            $("#modal_name_edit").empty().text(name);
            $("#modal_contact_edit").empty().text(contact);
            $("#modal_establishment_edit").empty().text(establishment);
            $("#modal_texture_edit").empty().text(texture);
            $("#modal_eco_edit").empty().text(eco);
            $("#modal_pattern_edit").empty().text(pattern);
            $("#farmerid_edit").val(farmerid);









             
        });




        $('#farmer_info').on('show.bs.modal', function (e) {
            var name = $(e.relatedTarget).data('name');
            var location = $(e.relatedTarget).data('location');
            var contact = $(e.relatedTarget).data('contact');
            var nvar = $(e.relatedTarget).data('nvar');
            var pvar = $(e.relatedTarget).data('pvar');
            var establishment = $(e.relatedTarget).data('establishment');
            var yieldz = $(e.relatedTarget).data('yield');
            var weight = $(e.relatedTarget).data('weight');
            var straw = $(e.relatedTarget).data('straw');
            var planting = $(e.relatedTarget).data('planting');
            var size = $(e.relatedTarget).data('size');
            var texture = $(e.relatedTarget).data('texture');
            var pattern = $(e.relatedTarget).data('pattern');
            var eco = $(e.relatedTarget).data('eco');
            var farmerid = $(e.relatedTarget).data('farmerid');
            

            $("#modal_name").empty().text(name);
            $("#modal_location").empty().text(location);
            $("#modal_contact").empty().text(contact);
            $("#modal_nvar").empty().text(nvar);
            $("#modal_pvar").empty().text(pvar);
            $("#modal_establishment").empty().text(establishment);
            $("#modal_yield").empty().text(yieldz);
            $("#modal_weight").empty().text(weight);
            $("#modal_straw").empty().text(straw);
            $("#modal_planting").empty().text(planting);
            $("#modal_size").empty().text(size);
            $("#modal_texture").empty().text(texture);
            $("#modal_eco").empty().text(eco);
            $("#modal_pattern").empty().text(pattern);
            $("#farmerid").val(farmerid);
             
        });


         $('select[name="cmbProvince_edit"]').on('change', function () {
            HoldOn.open("sk-cube-grid");
            var provCode = $('select[name="cmbProvince_edit"]').val();
            $('select[name="cmbMunicipality_edit"]').empty().append("<option value='0'>Please select a municipality</option>");
                $.ajax({
                    method: 'POST',
                    url: "{{route('moet.get.municipality_list')}}",
                    data: {
                        _token: _token,
                        provCode: provCode,
                        type : "all"
                    },
                    dataType: 'json',
                    success: function (source) {
                        $.each(source, function (i, d) {
                            $('select[name="cmbMunicipality_edit"]').append('<option value="' + d.citymunCode + '">' + d.citymunDesc + '</option>');
                        }); 
                        HoldOn.close();
                    }
                }); //AJAX GET MUNICIPALITY 
            });  //END MUNICIPALITY SELECT



         $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });



         $('select[name="cmbRegion"]').on('change', function () {
            HoldOn.open("sk-cube-grid");
            var regCode = $('select[name="cmbRegion"]').val();
            $('select[name="cmbProvince"]').empty().append("<option value='0'>Please select a province</option>");
            $('select[name="cmbMunicipality"]').empty().append("<option value='0'>Please select a municipality</option>");
                $.ajax({
                    method: 'POST',
                    url: "{{route('moet.get.province_list')}}",
                    data: {
                        _token: _token,
                        regCode: regCode,
                        type: "filter_region"
                    },
                    dataType: 'json',
                    success: function (source) {
                        $.each(source, function (i, d) {
                            $('select[name="cmbProvince"]').append('<option value="' + d.provCode + '">' + d.provDesc + '</option>');
                        }); 
                        HoldOn.close();
                    }
                }); //AJAX GET MUNICIPALITY 
            });  //END MUNICIPALITY SELECT


            $('select[name="cmbProvince"]').on('change', function () {
            HoldOn.open("sk-cube-grid");
            var provCode = $('select[name="cmbProvince"]').val();
            $('select[name="cmbMunicipality"]').empty().append("<option value='0'>Please select a municipality</option>");
                $.ajax({
                    method: 'POST',
                    url: "{{route('moet.get.municipality_list')}}",
                    data: {
                        _token: _token,
                        provCode: provCode,
                        type: "with_data"
                    },
                    dataType: 'json',
                    success: function (source) {
                        $.each(source, function (i, d) {
                            $('select[name="cmbMunicipality"]').append('<option value="' + d.citymunCode + '">' + d.citymunDesc + '</option>');
                        }); 
                        HoldOn.close();
                    }
                }); //AJAX GET MUNICIPALITY 
            });  //END MUNICIPALITY SELECT




            $('select[name="cmbMunicipality"]').on('change', function () {
                loadTable();

            });  //END MUNICIPALITY SELECT



        function loadTable(){
            var provCode = $('select[name="cmbProvince"]').val();
            var muniCode = $('select[name="cmbMunicipality"]').val();


            $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "searching": false,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{route('moet.load_table')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        provCode: provCode,
                        muniCode: muniCode,
                    }
                },
                "columns":[
                    {"data": "name"},
                    {"data": "gender"},
                     {"data": "province"},
                    {"data": "municipality"},
                    {"data": "brgy"},
                    {"data": "contact"},
                    {"data": "variety"},
                    {"data": "Establishment"},
                    {"data": "soil"},
                    {"data": "date_synced"},
                    
                    {"data": "action"}
                ]
            });

        }




        $('select[name="cmbProvince_edit"]').select2();
        $('select[name="cmbMunicipality_edit"]').select2();
        $('select[name="prvVar"]').select2();
        $('select[name="nxtVar"]').select2();
        



	</script>

@endpush