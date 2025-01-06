@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>RCEF SMS - Second Inspection Result</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province  </label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a province</option>
                                         @foreach ($province_list as $province)
                                                <option value="{{ $province->province }}">{{ $province->province}}</option>
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
                                    <!--<br>
                                     <button type="button" name="utilProcess" id="utilProcess" class="btn btn-lg btn-primary" disabled=""><i class="fa fa-sign-in"></i> Process </button> -->
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Result</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="result" id="result" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="all">All Result</option>
                                        <option value="G">For Distribution Seeds</option>
                                        <option value="T">For Retesting</option>
                                        <option value="R">Rejected</option>
                                        <option value="P">For Replacement</option>
                                        <option value="D">Donated</option>
                                    </select>
                                 </div>
                            </div>
       	</div>
    </div>
    <!-- TABLE TRANSFER DATA -->
        <div class="col-md-12 col-sm-12 col-xs-12">
    <!-- distribution details -->
        <div class="x_panel">
        <div class="x_title">
            <h2>
                SECOND INSPECTION RESULT
            </h2>

            <!--  <button class="btn btn-success btn-sm" style="float:right;" id="export_transfer_excel">
                Export to Excel 
            </button> -->
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th>Batch Ticket Number</th>
                    <th>Province</th>
                    <th>Municipality</th>
                    <th>Seed Tag</th>
                    <th>Bags</th>
                    <th>Result</th>
                    <th>Inspector</th>
                    <th>Remarks</th>
                    <th>Date</th>
                    <th>Action</th>
                </thead>
                <tbody id='databody'>
                </tbody>
            </table>

                
                
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>
    <!-- END TABLE -->



      <!-- CURRENT RLA MODAL -->
<div id="show_replacement" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Replacement Info</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Batch Number:</label>
                <label id="modal_batch_replacement"></label> <br>
                <label for="" class="col-xs-3">Seed Tag:</label>
                <label id="modal_seedTag_replacement"></label> <br>
           
                <label for="" class="col-xs-3">Total Bag Count: </label>
                <label id="modal_bags_replacement"></label> <br>
               
            </div>
            <div class="modal-footer" id="modal_footer_2">       
            </div>
        </div>
    </div>
</div>
<!-- CURRENT RLA MODAL END -->







    <!-- CURRENT RLA MODAL -->
<div id="change_category" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>Seed Tags List</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <label for="" class="col-xs-3">Batch Number:</label>
                <label id="modal_batch"></label> <br>
                <label for="" class="col-xs-3">Seed Tag:</label>
                <label id="modal_seedTag"></label> <br>
                <label for="" class="col-xs-3">Province: </label>
                <label id="modal_province"></label> <br>
                <label for="" class="col-xs-3">Municipality: </label>
                <label id="modal_municipality"></label> <br>
                <label for="" class="col-xs-3">Category: </label>
                <label id="modal_category"></label> <br>
                <label for="" class="col-xs-3">Inspector: </label>
                <label id="modal_inspector"></label> <br>
                <label for="" class="col-xs-3">Remarks: </label>
                <label id="modal_remarks"></label> <br>
                <label id="modal_deduct_title" class="col-xs-3"></label>
                <label id="modal_deduct_count"></label> <br>
                
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
    
    $('#show_replacement').on('show.bs.modal', function (e) {
            var batch_number = $(e.relatedTarget).data('batch');
            var province = $(e.relatedTarget).data('province');
            var municipality = $(e.relatedTarget).data('municipality');
            var seed_tag = $(e.relatedTarget).data('seed_tag');
            var category = $(e.relatedTarget).data('category');
            var inspector = $(e.relatedTarget).data('inspector');
            var remarks = $(e.relatedTarget).data('remarks');
            var bags = $(e.relatedTarget).data('bag');
     
            HoldOn.open(holdon_options);
                    $.ajax({
                        method: 'POST',
                        url:  "{{route('replacement.info.inspection')}}",
                        data: {
                            _token: _token,
                            batchTicketNumber: batch_number,
                            province: province,
                            municipality: municipality,
                            seedTag: seed_tag
                        },
                        dataType: 'json',
                        success: function (source) {
                      

                            $('#modal_batch_replacement').empty().text(source.batch);
                            $('#modal_seedTag_replacement').empty().text(source.seedtag);
                            $('#modal_bags_replacement').empty().text(source.bag);
                            

                        HoldOn.close();
                        }
                    });



        });



        $('#change_category').on('show.bs.modal', function (e) {
            var batch_number = $(e.relatedTarget).data('batch');
            var province = $(e.relatedTarget).data('province');
            var municipality = $(e.relatedTarget).data('municipality');
            var seed_tag = $(e.relatedTarget).data('seed_tag');
            var category = $(e.relatedTarget).data('category');
            var inspector = $(e.relatedTarget).data('inspector');
            var remarks = $(e.relatedTarget).data('remarks');
            var bags = $(e.relatedTarget).data('bag');
            $('#modal_deduct_title').empty();
            $('#modal_deduct_count').empty();

            if(remarks == ""){
                remarks = "N/A";
            }
        
               $('#modal_footer').empty();
            if(category === "T"){
                $('#modal_footer').append('<button class="btn btn-warning" style="float:right;" id="cancel" onclick="hide_modal()" >CANCEL</button><button class="btn btn-danger" style="float:right;" id="reject" onclick="category_process('+"'"+batch_number+"'"+','+"'"+seed_tag+"'"+','+"'"+'R'+"'"+','+bags+')" >REJECT</button><button class="btn btn-success" style="float:right;" id="good" onclick="category_process('+"'"+batch_number+"'"+','+"'"+seed_tag+"'"+','+"'"+'G'+"'"+',0'+')" >FOR DISTRIBUTION</button>');  
                $('#modal_deduct_count').append('<input type="number" name="deduct_count" id="deduct_count" value="'+bags+'" >');
                $('#modal_deduct_title').text("Bags");
                var category_name = "For Retesting";
            }else if (category === "R"){
                
                $('#modal_footer').append('<button class="btn btn-warning" style="float:right;" id="cancel" onclick="hide_modal()" >CANCEL</button><button class="btn btn-danger" style="float:right;" id="donate" onclick="category_process('+"'"+batch_number+"'"+','+"'"+seed_tag+"'"+','+"'"+'D'+"'"+','+"'"+bags+"'"+')" >DONATE</button><button class="btn btn-success" style="float:right;" id="replace" onclick="category_process('+"'"+batch_number+"'"+','+"'"+seed_tag+"'"+','+"'"+'P'+"'"+','+"'"+bags+"'"+')" >REPLACE</button>');
              
                $('#modal_deduct_count').append('<input type="number" name="deduct_count" id="deduct_count" value="'+bags+'" disabled>');
                $('#modal_deduct_title').text("Rejected Bags");
                 var category_name = "Rejected";
            }else if (category === "G"){
                $('#modal_footer').append('<button class="btn btn-warning" style="float:right;" id="cancel" onclick="hide_modal()" >CANCEL</button><button class="btn btn-danger" style="float:right;" id="donate" onclick="category_process_forDistribution('+"'"+batch_number+"'"+','+"'"+seed_tag+"'"+','+"'"+'R'+"'"+','+"'"+bags+"'"+')" >REJECT</button>');
                $('#modal_deduct_count').append('<input type="number" name="deduct_count" id="deduct_count" value="'+bags+'">');
                $('#modal_deduct_title').text("For Distribution Bags");
                 var category_name = "For Distribution";
            }

            $('#modal_batch').empty().text(batch_number);
            $('#modal_seedTag').empty().text(seed_tag);
            $('#modal_province').empty().text(province);
            $('#modal_municipality').empty().text(municipality);
            $('#modal_category').empty().text(category_name);
            $('#modal_inspector').empty().text(inspector);
            $('#modal_remarks').empty().text(remarks);
        });

        function category_process_forDistribution(ticket,seedtag,category_change,bags){   
            var yesNo = confirm("Update Result?");
        
            if(yesNo){
                var deduct_count = $("#deduct_count").val();
                
                    HoldOn.open(holdon_options);
                    $.ajax({
                        method: 'POST',
                        url:  "{{route('second.inspection.dseeds.change')}}",
                        data: {
                            _token: _token,
                            ticket: ticket,
                            seedtag: seedtag,
                            category_change: category_change,
                            deduct_count: deduct_count
                        },
                        dataType: 'json',
                        success: function (source) {
                                if(source == "SUCCESS"){
                                    alert("Category Changed");
                                    $('#change_category').modal("hide");
                                    genTable();
                                }else if(source == "FAILED"){
                                    alert("You have no priviledge for this process");
                                }else if (source == "ZERO BAGS"){
                                    alert("No more bags remaining");
                                }else if(source == "NO MATCH"){
                                    alert("Cannot Find seedTag");
                                }
                        HoldOn.close();
                        }
                    });
            }  
        }




        function category_process(ticket,seedtag,category_change,bags){  
          
            var yesNo = confirm("Update Result?");
            if(yesNo){
            
                var deduct_count = $("#deduct_count").val();
               
                if(category_change == "P" || category_change == "D" || category_change == "R"){
            
                    if(deduct_count == ""){
                        alert("Please Specify Rejected Bags");
                        return;
                    }else{
                        deduct_count = parseInt(deduct_count);
                        if(deduct_count <=0){
                            alert("Please Specify Rejected Bags");
                            return;
                        }

                        if(deduct_count > bags){
                             alert("Please Specify Correct Number of Bags");
                            return;
                        }


                    }
                
        
                }
                


             
                    HoldOn.open(holdon_options);
                    $.ajax({
                        method: 'POST',
                        url:  "{{route('second.inspection.change')}}",
                        data: {
                            _token: _token,
                            ticket: ticket,
                            seedtag: seedtag,
                            category_change: category_change,
                            deduct_count: deduct_count
                        },
                        dataType: 'json',
                        success: function (source) {
                                if(source == "SUCCESS"){
                                    alert("Category Changed");
                                    $('#change_category').modal("hide");
                                    genTable();
                                }
                        HoldOn.close();
                        }
                    }); //AJAX GET PROVINCE
              

                
            }

            
        }

        function hide_modal(){
            $('#modal_batch').val("");
            $('#modal_seedTag').val("");
            $('#modal_province').val("");
            $('#modal_municipality').val("");
            $('#modal_category').val("");
            $('#modal_inspector').val("");
            $('#modal_remarks').val("");
            $('#change_category').modal("hide");
        }

         $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
         });

            $('select[name="utilProvince"]').on('change', function () {
                        HoldOn.open(holdon_options);
                        var province = $(this).val();   
                    $('select[name="utilMunicipality"]').empty();
                    $('select[name="utilMunicipality"]').append('<option value="0">Please Select a Municipality</option>');

                     $('select[name="utilDop"]').empty();
                    $('select[name="utilDop"]').append('<option value="0">--Please Select a Drop off Point--</option>');
                        $.ajax({
                            method: 'GET',
                            url:  "breakdown/municipality/"+province,
                            data: {
                                _token: _token,
                                province: province,
                            },
                            dataType: 'json',
                            success: function (source) {
                                    $('select[name="utilMunicipality"]').empty();
                                    $('select[name="utilMunicipality"]').append('<option value="0">Please Select a Municipality</option>');
                                
                                    $.each(source, function (i, d) {
                                        if(i ==0){
                                            $('select[name="utilMunicipality"]').empty();
                                            $('select[name="utilMunicipality"]').append('<option value="all">--All Municipality--</option>');        
                                        }
                                    $('select[name="utilMunicipality"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                                }); 
                                    genTable();
                            }
                         }); //AJAX GET PROVINCE
                    HoldOn.close();
            });  //END PROVINCE SELECT


            $('select[name="utilMunicipality"]').on('change', function () {
                HoldOn.open(holdon_options);
                    genTable();
                HoldOn.close();
             });  //END Municipality SELECT




            $('select[name="result"]').on('change', function () {
            
                genTable();

            });  //END Drop off Point SELECT


            function genTable(){
                var province = $('select[name="utilProvince"]').val();
                var municipality = $('select[name="utilMunicipality"]').val();
                var result = $('select[name="result"]').val();

                if (province === "0"){
                    return;
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
                    "url": "{{route('genTable.report.break_down')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        result:result,
                    },
                    "dataSrc": function(res){
                    var count = res.data.length;
                        if (count > 0){
                            // document.getElementById("export_transfer_excel").style.display="block";
                         }else{
                           //  document.getElementById("export_transfer_excel").style.display="none";
                         }

                    return res.data;
                    }
                },
                "columns":[              
                    {"data": "batch_number", 'orderable': false},
                     {"data": "province", 'searchable': false, 'orderable': true},
                    {"data": "municipality", 'searchable': false, 'orderable': false},
                    {"data": "seed_tag", 'orderable': false},
                     {"data": "totalBagCount", 'orderable': false},
                    {"data": "result", 'searchable': false, 'orderable': false},
                    {"data": "inspector", 'searchable': false, 'orderable': false},
                    {"data": "remarks", 'searchable': false, 'orderable': false},
                    {"data": "date_created", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}  
                ]
            });
            }
            
          //  $('select[name="utilProvince"]').select2();
          //  $('select[name="utilMunicipality"]').select2();



	</script>

@endpush