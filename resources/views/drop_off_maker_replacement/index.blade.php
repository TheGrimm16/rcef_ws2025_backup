@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Drop Off Maker for Replacement Seeds</h3>
            </div>
        </div>

            <div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> This is only for the creation of Drop Off Point for Replacement Seeds.
            </div>  
        </div>
                            <div class="form-group">
                                <label id="prv_id" class="control-label col-md-4 col-sm-3 col-xs-3 col-md-offset-3" style="text-align: left; font-size: 20px;">PRV: 000000</label>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Region</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="utilRegion" id="utilRegion" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a Region</option>
                                      @foreach ($regional_list as $region)
                                                <option value="{{ $region->regCode }}">{{ $region->regionName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province  </label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a province</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="utilMunicipality" id="utilMunicipality" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a municipality</option>
                                    </select>                                
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Cooperatives</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                   <select name="utilCoop" id="utilCoop" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a Cooperatives</option>
                                      @foreach ($coop_list as $coop)
                                                <option value="{{ $coop->accreditation_no }}">{{ $coop->coopName}}</option>
                                        @endforeach
                                    </select>     
                                    <br>
                                       <button style="border-radius: 100px; margin-top: 5px;" type="button" name="utilAdd" id="utilAdd" class="btn btn-lg btn-success" disabled=""><i class="fa fa-plus-circle"></i> Add New  </button>                        
                                </div>
                            </div>
        </div>

           <div class="col-md-12">
        
            <div class="x_panel">
                <div class="x_title">
                    <h2>DOP LIST</h2>
                    <div class="clearfix"></div>
                </div>
            
                <table class="table table-striped table-bordered" id="batch_tbl">
                    <thead>
                        <th>Cooperatives</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Drop Off Point</th>
                        <th style="width: 100px;">Action</th>
                    </thead>
                </table>
            </div>
            </div>
        
     
    </div>

    </div>



<!-- NEW DOP MODAL -->
<div id="new_dop_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Create new Drop Off Point</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Cooperative:</label>
                <label id="modal_coop"></label> <br>
                <label for="" class="col-xs-3">Region</label>
                <label id="modal_region"></label> <br>
                <label for="" class="col-xs-3">Province: </label>
                <label id="modal_province"></label> <br>
                <label for="" class="col-xs-3">Municipality: </label>
                <label id="modal_municipality"></label> <br>
                                
                <input type="hidden" name="modal_coop_accre" id="modal_coop_accre">
        <div class="form-group">

                <div style="">
                 <input type="text" style="width: 80%;" class="form-control" name="newDropOffPoint" id="newDropOffPoint" placeholder="Drop Off Point Name">
                </div>
        </div>  
                
            </div>
            <div class="modal-footer">      
                <button class="btn btn-success" style="float:right;" id="save_dop" >Save</button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL NEW -->


<!-- NEW DELIVERY MODAL -->
<div id="new_delivery_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Create Delivery</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Cooperative:</label>
                <label id="modal_coop_delivery"></label> <br>
                <label for="" class="col-xs-3">Province</label>
                <label id="modal_province_delivery"></label> <br>
                <label for="" class="col-xs-3">Municipality: </label>
                <label id="modal_municipality_delivery"></label> <br>
                <label for="" class="col-xs-3">Drop Off Point: </label>
                <label id="modal_dop_delivery"></label> <br>      
                <input type="hidden" name="modal_coop_accre" id="modal_coop_accre">
                <input type="hidden" name="modal_prv_dropoff_id" id="modal_prv_dropoff_id">
                <input type="hidden" name="modal_region_delivery" id="modal_region_delivery">
                <input type="hidden" name="modal_moa_delivery" id="modal_moa_delivery">
                
                
                <label for="" class="col-xs-3"><i class="fa fa-calendar"> Delivery Date:</i></label>
                <label>
                <input type="text" style="width: 50%; text-align: center;" class="form-control" name="deliveryDate" id="deliveryDate" placeholder="Delivery Date">
                </label><br>                
                <label for="" class="col-xs-3">Volume:</label>
                <label><input type="number" style="width: 50%; text-align: right;" class="form-control" name="volume" id="volume" placeholder="Volume"></label>    <br>  

                <label for="" class="col-xs-3">Replacement For:</label>
                <label> 
                        <select style="width: 300px;" class="form-control col-xs-9" id="replacement" name="replacement">
                            <option value="0">Select For Replacement Seeds</option>
                        </select>
                </label>     

            </div>

            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>DOP LIST</h2>
                        <div class="clearfix"></div>
                    </div>
                
                    <table class="table table-striped table-bordered" id="batch_tbl_list">
                        <thead>
                            <th>Batch Ticket</th>
                            <th>Delivery Date</th>
                            <th>Volume</th>
                            <th>Status</th>
                        </thead>
                    </table>
                </div>
            </div>


            <div class="modal-footer">      
                <button class="btn btn-success" style="float:right;" id="add_delivery" disabled="">Create Delivery</button>
            </div>
        </div>



    </div>
</div>
<!-- MODAL NEW -->

@endsection
@push('scripts')
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script type="text/javascript">
          $("#deliveryDate").datepicker();


           $('select[name="replacement"]').on('change', function () {
                var replacement = $(this).val();
                if(replacement === "0"){
                    $("#add_delivery").removeAttr("disabled");
                    $("#add_delivery").attr("disabled", "true");
                }else{
                    $("#add_delivery").removeAttr("disabled");
                }
                
           });


         $('select[name="utilRegion"]').on('change', function () {
            HoldOn.open(holdon_options);
            var region = $(this).val();
            document.getElementById("utilAdd").disabled = true;
 
        $('select[name="utilProvince"]').empty();
        $('select[name="utilProvince"]').append('<option value=0>--Please Select a Province--</option>');
        $('select[name="utilMunicipality"]').empty();
        $('select[name="utilMunicipality"]').append('<option value=0>--Please Select a Municipality--</option>');
         $("#prv_id").empty().text("PRV: 000000");

            $.ajax({
                method: 'POST',
                url: "{{route('web.dop.maker.replacement.province')}}",
                data: {
                    _token: _token,
                    region: region,
                },
                dataType: 'json',
                success: function (source) {
                 
                    $('select[name="utilProvince"]').empty();
                    $('select[name="utilProvince"]').append('<option value=0>--Please Select a Province--</option>');
                        $.each(source, function (i, d) {
                            $('select[name="utilProvince"]').append('<option value="' + d.provCode + '">' + d.province + '</option>');
                    }); 
                }
        }); //AJAX GET PROVINCE
        HoldOn.close();
        });  //END REGIONAL SELECT

        $('select[name="utilProvince"]').on('change', function () {
            HoldOn.open(holdon_options);
            var region = $("#utilRegion").val();
            var province = $(this).val();
            document.getElementById("utilAdd").disabled = true;

        $('select[name="utilMunicipality"]').empty();
        $('select[name="utilMunicipality"]').append('<option value=0>--Please Select a Municipality--</option>');
         $("#prv_id").empty().text("PRV: 000000");

            $.ajax({
                method: 'POST',
                url: "{{route('web.dop.maker.replacement.municipality')}}",
                data: {
                    _token: _token,
                    region: region,
                    province: province,
                },
                dataType: 'json',
                success: function (source) {
                 
                    $('select[name="utilMunicipality"]').empty();
                    $('select[name="utilMunicipality"]').append('<option value=0>--Please Select a Municipality--</option>');
                        $.each(source, function (i, d) {
                            $('select[name="utilMunicipality"]').append('<option value="' + d.munCode + '">' + d.municipality + '</option>');
                    }); 
                }
        }); //AJAX GET MUNCIPALITY
        HoldOn.close();
        });  //END PROVINCE SELECT

        $('select[name="utilMunicipality"]').on('change', function () {
            var region = $("#utilRegion").val();
            var province = $("#utilProvince").val();
            var municipality = $(this).val();
            var coopAccre = $("#utilCoop").val();
           // alert(region+province+municipality);

 //          dd($municipality);
            if(municipality === "0"){
                 $("#prv_id").empty().text("PRV: 000000");
                  document.getElementById("utilAdd").disabled = true;
            }else{
                 $("#prv_id").empty().text("PRV: "+region+province+municipality);
                 if(coopAccre === "0"){
                     document.getElementById("utilAdd").disabled = true;
                 }else{
                     document.getElementById("utilAdd").disabled = false;
                     genTable();
                 }
            }

         });  //END PROVINCE SELECT


            $('select[name="utilCoop"]').on('change', function () {
           
            var region = $("#utilRegion").val();
            var province = $("#utilProvince").val();
            var municipality = $("#utilMunicipality").val();
            var coopAccre = $("#utilCoop").val();
           // alert(region+province+municipality);
            if(municipality === "0"){
                 $("#prv_id").empty().text("PRV: 000000");
                  document.getElementById("utilAdd").disabled = true;
            }else{
                 $("#prv_id").empty().text("PRV: "+region+province+municipality);
                 if(coopAccre === "0"){
                     document.getElementById("utilAdd").disabled = true;
                 }else{
                     document.getElementById("utilAdd").disabled = false;
                     genTable();
                 }
            }
            //document.getElementById("utilProcess").disabled = true;
        });  //END PROVINCE SELECT







        $('#utilAdd').on('click', function () {
            var region = $("#utilRegion").val();
            var province = $("#utilProvince").val();
            var municipality = $("#municipality").val();
            var coopAccre = $("#utilCoop").val();


            var region_name = $( "#utilRegion option:selected" ).text();
            var province_name = $( "#utilProvince option:selected" ).text();
            var municipality_name = $( "#utilMunicipality option:selected" ).text();
            var coop_name = $( "#utilCoop option:selected" ).text();

           // alert(region+province+municipality);

 //          dd($municipality);
            
            $("#modal_coop").empty().text(coop_name);
            $("#modal_region").empty().text(region_name);
            $("#modal_province").empty().text(province_name);
            $("#modal_municipality").empty().text(municipality_name);
            $("#modal_coop_accre").val(coopAccre);
            $("#newDropOffPoint").val("");
            $('#new_dop_modal').modal("show"); 
         });  //END PROVINCE SELECT


        $('#save_dop').on('click', function () {

            var yesNO =  confirm('Create New Drop Off Point?');
            if(yesNO){
                    var region_name = $("#modal_region").text();
                    var province_name =$("#modal_province").text();
                    var municipality_name = $("#modal_municipality").text();
                    var coop_name = $("#modal_coop").text();
                    var dropOffPoint = $("#newDropOffPoint").val();
                    var coop_accre = $("#modal_coop_accre").val();

                    if(dropOffPoint === ""){
                        alert("Please Input Drop Off Name");
                        exit();
                    }else{
        //                  alert(region_name+province_name+coop_name+municipality_name+dropOffPoint);
                        
                        //add new DOP
                         $.ajax({
                        method: 'POST',
                        url: "{{route('web.dop.maker.replacement.insert')}}",
                        data: {
                            _token: _token,
                            region_name: region_name,
                            province_name: province_name,
                            municipality_name: municipality_name,
                            coop_accre: coop_accre,
                            coop_name: coop_name,
                            dropOffPoint: dropOffPoint,
                        },
                        dataType: 'json',
                        success: function (source) {
                            alert(source);
                            genTable();
                        }
                        }); //CREATE NEW DOP
                    }
                    $('#new_dop_modal').modal("hide"); 
            }else{
            }
          
         });  //create DOP

         $('#add_delivery').on('click', function () {

            var yesNO =  confirm('Add New Delivery?');
            if(yesNO){
                HoldOn.open(holdon_options);
                    var region = $("#modal_region_delivery").val();
                    var province_name =$("#modal_province_delivery").text();
                    var municipality_name = $("#modal_municipality_delivery").text();
                    var coop_name = $("#modal_coop_delivery").text();
                    var dropOffPoint = $("#modal_dop_delivery").val();
                    var coop_accre = $("#modal_coop_accre").val();
                    var prv_dropoff_id = $("#modal_prv_dropoff_id").val();
                    var deliveryDate = $("#deliveryDate").val();
                    var volume = $("#volume").val();

                    var moa_number = $("#modal_moa_delivery").val();

                    var replacement_id = $("#replacement").val();
                    
                    if(replacement_id === "0"){
                        HoldOn.close();
                        alert("Please seedtag that will be replace");
                        return;
                    }


                    if(volume === "0"){
                        HoldOn.close();
                        alert("Please Input volume");
                        return;
                    }else{
                        var data_id = replacement_id.split("|");
                        var remaining_bags = data_id[1];
                        var rep_id = data_id[0];

                        remaining_bags =  parseInt(remaining_bags);
                        if(volume > remaining_bags){
                            alert("Delivery Volume is more than the remaining replacement count ");
                            HoldOn.close();
                            return;
                        }


        //                  alert(region_name+province_name+coop_name+municipality_name+dropOffPoint);
                        
                        //add new DOP
                         $.ajax({
                        method: 'POST',
                        url: "{{route('web.dop.maker.delivery.insert')}}",
                        data: {
                            _token: _token,
                             instructed_delivery_volume: volume,
                             region: region,
                             prv_dropoff_id: prv_dropoff_id,
                             accreditation_no: coop_accre,
                             delivery_date: deliveryDate,
                             moa_number: moa_number,
                             date_created: "{{date('Y-m-d H:i:s')}}",
                            // batchTicketNumber: "{{Auth::user()->userId}}"+"-BCH-"+"{{time()}}",
                             user_id: "{{Auth::user()->userId}}",
                             isBuffer: "9",
                             replacement_id: rep_id
                            
                        },
                        dataType: 'json',
                        success: function (source) {
                            alert(source);
                            HoldOn.close();
                        }
                        }); //CREATE NEW DOP
                    }
                    $('#new_delivery_modal').modal("hide");
            }

            
         });  //create DOP

             $("#batch_tbl").DataTable();
            function genTable(){
                var region = $("#utilRegion").val();
                var province = $("#utilProvince").val();
                var municipality = $("#utilMunicipality").val();
                var coop_accre = $("#utilCoop").val();
                var coop_name = $( "#utilCoop option:selected" ).text();

                $('#batch_tbl').DataTable().clear();
                $("#batch_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{route('web.dop.maker.replacement.gentable')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            region: region,
                            province: province,
                            municipality: municipality,
                            coop_accre: coop_accre,
                            coop_name: coop_name
                        }
                    },
                    "columns":[
                        {"data": "coop"},
                        {"data": "province"},
                        {"data": "municipality"},
                        {"data": "dropOffPoint"},
                        {"data": "action", searchable: false},
                    ]
                });
            }


    $("#batch_tbl_list").DataTable();       
    $('#new_delivery_modal').on('show.bs.modal', function (e) {

        var coop_name = $(e.relatedTarget).data('coop_name');
        var coop_accre = $(e.relatedTarget).data('coop_accre');
        var region =$(e.relatedTarget).data('region');
        var province = $(e.relatedTarget).data('province');
        var municipality = $(e.relatedTarget).data('municipality');
        var prv_dropoff_id = $(e.relatedTarget).data('prv_id');
        var dop = $(e.relatedTarget).data('dop');
        var moa = $(e.relatedTarget).data('moa');

        $("#modal_coop_delivery").empty().text(coop_name);
        $("#modal_province_delivery").empty().text(province);
        $("#modal_municipality_delivery").empty().text(municipality);
        $("#modal_dop_delivery").empty().text(dop);
        $("#modal_coop_accre").val(coop_accre);
        $("#deliveryDate").val("{{date('m/d/Y')}}");
        $("#volume").val(0);
        $("#modal_region_delivery").val(region);
        $("#modal_moa_delivery").val(moa);
        $("#modal_prv_dropoff_id").val(prv_dropoff_id);


                $('#batch_tbl_list').DataTable().clear();
                $("#batch_tbl_list").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{route('web.dop.maker.replacement.gentableList')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            province: province,
                            municipality: municipality,
                            coop_accre: coop_accre,
                            coop_name: coop_name,
                            prv_dropoff_id: prv_dropoff_id
                        }
                    },
                    "columns":[
                        {"data": "batchTicketNumber"},
                        {"data": "delivery_date"},
                        {"data": "volume"},
                        {"data": "status"},
                    ]
                });



            $.ajax({
                method: 'POST',
                url: "{{route('web.dop.maker.replacement.list')}}",
                data: {
                    _token: _token,
                    province: province,
                    coop_accre: coop_accre,
                    municipality: municipality
                },
                dataType: 'json',
                success: function (source) {
                 
                    $('select[name="replacement"]').empty();
                    $('select[name="replacement"]').append('<option value="0">Select For Replacement Seeds</option>');
                        $.each(source, function (i, d) {
                            $('select[name="replacement"]').append('<option value="'+d.id+'|'+d.remaining+'" '+d.disable+'>' + d.batchTicketNumber+" ("+d.seedTag+")"  +" "+d.remaining+" bag(s)"+ '</option>');
                    }); 
                }

            });






    });






         $("#utilRegion").select2();
          $("#utilProvince").select2();
           $("#utilMunicipality").select2();
            $("#utilCoop").select2();
                $("#replacement").select2();
    </script>
@endpush