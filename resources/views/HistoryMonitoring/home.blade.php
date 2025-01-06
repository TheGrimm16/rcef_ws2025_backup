@extends('layouts.index')


@section('styles')
    <style>
        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
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
                <h2>Transfer Data List</h2>
                 <select name="transferType" id="transferType" class="form-control">
                    <option value=0>Previous Season to Current Season</option>
                     <option value=1>Current Season to Current Season</option>    
                 </select> <br>

                 <select name="cstocs" id="cstocs" class="form-control">
                    <option value=0>Whole</option>
                    <option value=1>Partial</option>   
                 </select>
                

                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
                    
                </div>
            </div>
        </div>
    </div>
</div>


  <!-- FILTER PANEL -->
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Select a Province and Municipality
                </h2>
                <input type="hidden" name="userId" id="userId" value="{{$userName}}">
                <select name="province_fg" id="province_fg" class="form-control">
                            <option value="0">Please select a Province</option>
                            @foreach ($municipal_list as $row)
                                <option value="{{$row->province}}">{{$row->province}}</option>
                            @endforeach
                </select>
                        <br>
                <select name="municipality_fg" id="municipality_fg" class="form-control">
                            <option value="0">Please select a Municipality</option>
                </select>
                         <div class="clearfix"></div>
        </div>
</div>
        <br>
        <!-- FILTER PANEL -->

<!-- DATA TABLE -->

 <div class="col-md-12 col-sm-12 col-xs-12">
<?php
//dd($userID->userId);
?>
    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
                TRANSFER DATA LIST
            </h2>
            <!--<button class="btn btn-success btn-sm" style="float:right;" id="excel_btn">
                Export to Excel (Statistics)
            </button>-->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th>Batch Ticket Number</th>
                    <th>Origin DOP</th>
                    <th>Destination DOP</th>
                    <th>Transfer Type</th>
                    <th>Bag Count</th>
                    <th>Date Created</th>
                   
                </thead>
                <tbody id='databody'>
                    
                </tbody>
            </table>
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>

 <div id="deleteHistoryData" class="modal fade" role="dialog">
     <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="province_export_title">
                        CANCEL TRANSFER
                    </h4>
                </div>

                <input type="hidden" id="hidden_id" value="">
                <input type="hidden" id="hiddenOrigin" value="">
                <input type="hidden" id="hiddenBatchNumber" value="">
                <input type="hidden" id="hiddenTransType" value="">
                <input type="hidden" id="hidden_seedtag" value="">
                <input type="hidden" id="hidden_transferlogid" value="">
                <input type="hidden" id="hidden_bcount" value="">



                <div class="modal-body">
                    <label for="" class="col-xs-3">Batch Ticket:</label>
                    <label id="modal_batch"></label> <br>
                    <label for="" class="col-xs-3">Origin:</label>
                    <label id="modal_origin"></label> <br>
                    <label for="" class="col-xs-3">Destination:</label>
                    <label id="modal_dest"></label> <br>
                    <label for="" class="col-xs-3 ">Bags:</label>
                    <label id="modal_bcount"></label> <br>


                    <button id="del_btn" type="button" class="btn btn-warning "><i class='fa fa-trash'> </i> CANCEL</button>

              </div>

            </div>
     </div>
 </div>



@endsection
@push('scripts')
    <script type="text/javascript">
        function ps_cancel(last_season, curr_season, seedtag){

            var yesno = confirm("Cancel Transfer?");

            if(yesno){
                $.ajax({
                method: 'POST',
                url: "{{route('generate.history.pstocs.cancel')}}",
                data: {
                    _token: _token,
                    last_season: last_season,
                    curr_season: curr_season,
                    seedtag: seedtag
                },
                dataType: 'json',
                success: function (source) {
                    alert(source);
                    return;
                }
                });
            }

          
        }












        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });


    var cstocs = document.getElementById("cstocs");
          cstocs.style.display = "none";
   
 $('select[name="cstocs"]').on('change', function () {
       HoldOn.open(holdon_options);
         var cstocs = document.getElementById("cstocs");
         var node = document.getElementById("databody");
            while (node.hasChildNodes()) {
            node.removeChild(node.lastChild);
            }
            $('select[name="municipality_fg"]').empty();
            $('select[name="province_fg"]').empty();
            $('select[name="municipality_fg"]').append('<option>Please select a Municipality</option>');
              var transType_2 = 1;
                if(cstocs.value == 0){
                    transType_2 = 1;
                }else{
                    transType_2 = 2;
                }


        $.ajax({
                method: 'GET',
                url: 'HistoryMonitoring/get_province/'+ transType_2,
                data: {
                    _token: _token,
                    transType_2: transType_2
                },
                dataType: 'json',
                success: function (source) {
        $('select[name="province_fg"]').empty();
       
            if(cstocs.value == 0){ //IF WHOLE
                    $('select[name="province_fg"]').append('<option>Please select a Province</option>');
                $.each(source, function (i, d) {
                    $('select[name="province_fg"]').append('<option value="' + d.destination_province + '">' + d.destination_province + '</option>');
                }); 
            }else{ //IF PARTIAL
         
         $('select[name="province_fg"]').append('<option>Please select a Province</option>');
            $.each(source, function (i, d) {
                $('select[name="province_fg"]').append('<option value="' + d.province + '">' + d.province + '</option>');
            }); 
                    }
        }
        });
    HoldOn.close();
});



  $('select[name="transferType"]').on('change', function () {
         HoldOn.open(holdon_options);
         var cstocs = document.getElementById("cstocs");
         var node = document.getElementById("databody");
            while (node.hasChildNodes()) {
            node.removeChild(node.lastChild);
            }
            //document.getElementById("province_fg").value=0;
            $('select[name="municipality_fg"]').empty();
            $('select[name="province_fg"]').empty();
            $('select[name="municipality_fg"]').append('<option>Please select a Municipality</option>');

        var transType = document.getElementById("transferType").value;
        var transType_2 = 0;
       // alert(transType);
        if(transType == 0 ){
          cstocs.style.display = "none";
           transType_2 = 0;
        }else{
          cstocs.style.display = "block";
            if(cstocs.value == 0){
             transType_2 = 1;
            }else{
             transType_2 = 2;
            }
        }

          $.ajax({
                method: 'GET',
                url: 'HistoryMonitoring/get_province/'+ transType_2,
                data: {
                    _token: _token,
                    transType_2: transType_2
                },
                dataType: 'json',
                success: function (source) {
                  
        $('select[name="province_fg"]').empty();
        if(transType == 1){ //FOR CSTOCS
            if(cstocs.value == 0){ //IF WHOLE
                                    $('select[name="province_fg"]').append('<option>Please select a Province</option>');
                    $.each(source, function (i, d) {
                                    $('select[name="province_fg"]').append('<option value="' + d.destination_province + '">' + d.destination_province + '</option>');
                    }); 
            }else{ //IF PARTIAL
                                    $('select[name="province_fg"]').append('<option>Please select a Province</option>');
                    $.each(source, function (i, d) {
                                    $('select[name="province_fg"]').append('<option value="' + d.province + '">' + d.province + '</option>');
                    }); 
            }
        }
        else{ //FOR PSTOCS
                                    $('select[name="province_fg"]').append('<option>Please select a Province</option>');
                    $.each(source, function (i, d) {
                                    $('select[name="province_fg"]').append('<option value="' + d.province + '">' + d.province + '</option>');
                    });
        }

             }
        });  //AJAX
      HoldOn.close();
    }); 
                   
        $('select[name="province_fg"]').on('change', function () {
            
            HoldOn.open(holdon_options);
        
         var node = document.getElementById("databody");
            while (node.hasChildNodes()) {
            node.removeChild(node.lastChild);
            }

        var provCode = document.getElementById("province_fg");
        var provName = provCode.options[provCode.selectedIndex].text;
        
       // alert(document.getElementById("cstocs").value);
        if(document.getElementById("transferType").value == 1){

            if(document.getElementById("cstocs").value==0){
                var linkMunicipalities = 'HistoryMonitoring/get_municipalities/cstocs/' + provName + '/ALL_SEEDS_TRANSFER';     
            }else{
                var linkMunicipalities = 'HistoryMonitoring/get_municipalities/cstocs/' + provName + '/PARTIAL_SEEDS_TRANSFER';
            }
           
        }else{
           var linkMunicipalities = 'HistoryMonitoring/get_municipalities/pstocs/' + provName ;
        }



        $('select[name="municipality_fg"]').empty();
        $('input[name="hidden_region"]').empty();
    //alert(provCode);
            $.ajax({
                method: 'GET',
                url: linkMunicipalities,
                data: {
                    _token: _token,
                    provName: provName
                },
                dataType: 'json',
                success: function (source) {

        if(document.getElementById("transferType").value == 1){ //FOR CSTOCS
            if(document.getElementById("cstocs").value==0){ //IF WHOLE
                    $('select[name="municipality_fg"]').append('<option>Please select a Municipality </option>');
                $.each(source, function (i, d) {
                    $('select[name="municipality_fg"]').append('<option value="' + d.destination_municipality + '">' + d.destination_municipality + '</option>');
                }); 
            }else{ //IF PARTIAL
         
         $('select[name="municipality_fg"]').append('<option>Please select a Municipality</option>');
            $.each(source, function (i, d) {
                $('select[name="municipality_fg"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
            }); 
                    }
        }
        else{ //FOR PSTOCS
            $('select[name="municipality_fg"]').append('<option>Please select a Municipality</option>');
            $.each(source, function (i, d) {
            $('select[name="municipality_fg"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
            });
        }

        }
        }); //AJAX GET MUNICIPALITY

        var node = document.getElementById("databody");
        while (node.hasChildNodes()) {
        node.removeChild(node.lastChild);
        }

    
        HoldOn.close();
    });  //END PROVINCE SELECT


$('select[name="municipality_fg"]').on('change', function () {
    HoldOn.open(holdon_options);

    var userId = $('input[name="userId"]').val();
    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var muniCode = document.getElementById("municipality_fg");
    var muniName = muniCode.options[muniCode.selectedIndex].text;
    var transfer_type = '';

    if(document.getElementById("transferType").value == 1){
          var linkTable = "{{ route('generate.history.list.cstocs') }}";

            if(document.getElementById("cstocs").value==0){ 
                var transfer_type = 'ALL_SEEDS_TRANSFER';     
            }else{
                var transfer_type = 'PARTIAL_SEEDS_TRANSFER';
            }
    }else{ 
           var linkTable = "{{ route('generate.history.list.pstocs') }}";
    }


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
                    "url": linkTable,
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        provName: provName,
                        muniName: muniName,
                        userId: userId,
                        transfer_type: transfer_type,
                    }
                },
                "columns":[
                    {"data": "batch_number", 'orderable': false},
                     {"data": "origin", 'searchable': false, 'orderable': false},
                    {"data": "destination", 'searchable': false, 'orderable': false},
                    {"data": "seed_variety", 'searchable': false, 'orderable': false},
                    {"data": "bags", 'searchable': false, 'orderable': false},
                    {"data": "date_created", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}
                  
                ]
            });
    HoldOn.close();
});  //END MUNICIPALITY SELECT


    







    $('#deleteHistoryData').on('show.bs.modal', function (e) {
            var rowid = $(e.relatedTarget).data('rowid');
            var destDOP = $(e.relatedTarget).data('dest');
            var originDOP = $(e.relatedTarget).data('origin');
            var batchNumber = $(e.relatedTarget).data('batch');
            var bcount = $(e.relatedTarget).data('bcount');
            var originID = $(e.relatedTarget).data('doporigin');
            var ttype = $(e.relatedTarget).data('ttype');
            //var municipality = $(e.relatedTarget).data('municipality');
            //alert(rowid + ' '+destDOP+ ' '+originDOP+' '+batchNumber+' '+bcount);

            if(ttype === "cstocs_partial"){
                var seedtag = $(e.relatedTarget).data('seedtag');
                var transferlogid = $(e.relatedTarget).data('transferlogid');
                $("#hidden_seedtag").val(seedtag);
                $("#hidden_transferlogid").val(transferlogid);
                $("#hidden_bcount").val(bcount);
                   
            }else if(ttype === "pstocs"){
                var transferlogid = $(e.relatedTarget).data('transferlogid');
                var user = $(e.relatedTarget).data('seedtag');

                $("#hidden_transferlogid").val(transferlogid);
                $("#hidden_bcount").val(bcount);
                $("#hidden_seedtag").val(user)
            }




            $("#hidden_id").val(rowid);
            $("#hiddenOrigin").val(originID);
            $("#hiddenBatchNumber").val(batchNumber);
            $("#hiddenTransType").val(ttype);
            
            if(ttype === "pstocs"){
            $('label[id="modal_batch"]').text(batchNumber);
            }else{
            $('label[id="modal_batch"]').text(batchNumber);
            }

            $('label[id="modal_origin"]').text(originDOP);
            $('label[id="modal_dest"]').text(destDOP);
            $('label[id="modal_bcount"]').text(bcount);
            

        });



//DELETE BUTTON

    $("#del_btn").on("click", function(e){
           var rowid = $("#hidden_id").val(); //BATCHNUMBER
           var dropofforigin = $("#hiddenOrigin").val(); //ORIGIN ID
           var batch_number = $("#hiddenBatchNumber").val(); //BATCHNUMBER
           var ttype = $("#hiddenTransType").val();
           var transferlogid =  $("#hidden_transferlogid").val();
           var seedtag =  $("#hidden_seedtag").val();
           var bcount =  $("#hidden_bcount").val();


    if(document.getElementById("transferType").value == 1){ //CSTOCS   
            if(document.getElementById("cstocs").value==0){
                var linkProcess = "{{ route('process.history.cancel.cstocs.all') }}";   
            }else{
                var linkProcess = "{{ route('process.history.cancel.cstocs.partial') }}";
            }
    }else{ //PSTOCS
           var linkProcess = "{{ route('process.history.cancel.pstocs') }}";
    }


         $.ajax({
        type: 'POST',
        url: linkProcess,
        data: {
             "_token": "{{ csrf_token() }}",
             rowid: rowid,
             dropofforigin: dropofforigin,
             batch_number: batch_number,
             seedtag: seedtag,
             transferlogid: transferlogid,
             bcount: bcount,
                
        },
        dataType: 'json',
        success: function (source) {
            alert("TRANSFER CANCELLED");
        }
        }); 
            alert("CANCELLED");
           $("#deleteHistoryData").modal("hide");



    //REFRESH TABLE


    var userId = $('input[name="userId"]').val();
    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var muniCode = document.getElementById("municipality_fg");
    var muniName = muniCode.options[muniCode.selectedIndex].text;
    var transfer_type = '';

    if(document.getElementById("transferType").value == 1){
          var linkTable = "{{ route('generate.history.list.cstocs') }}";

            if(document.getElementById("cstocs").value==0){ 
                var transfer_type = 'ALL_SEEDS_TRANSFER';     
            }else{
                var transfer_type = 'PARTIAL_SEEDS_TRANSFER';
            }
    }else{ 
           var linkTable = "{{ route('generate.history.list.pstocs') }}";
    }


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
                    "url": linkTable,
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        provName: provName,
                        muniName: muniName,
                        userId: userId,
                        transfer_type: transfer_type,
                    }
                },
                "columns":[
                    {"data": "batch_number", 'orderable': false},
                     {"data": "origin", 'searchable': false, 'orderable': false},
                    {"data": "destination", 'searchable': false, 'orderable': false},
                    {"data": "seed_variety", 'searchable': false, 'orderable': false},
                    {"data": "bags", 'searchable': false, 'orderable': false},
                    {"data": "date_created", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}
                  
                ]
            });
    HoldOn.close();
        });
    </script>

@endpush