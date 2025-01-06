@extends('layouts.index')

@section('styles')
    <style>
        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

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
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h1>DV Preparation</h1>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
                <div class="col-md-5">
                            <div id="delivery_details">
                                <div style="justify-content: center; align-items: center;">
                                        <!-- card 1 -->
                                        <div class="card" style="padding-top: 1em; padding-bottom: 2em;">
                                            <div class="col-md-12">
                                              <center>  <h2>Delivery Details</h2> </center>
                                            </div>
                                            <div class="col-md-12" style="padding-left: 2em; padding-right: 2em">
                                                    <!-- <label for="scan_IAR">Scan IAR QR</label> -->
                                                    <button class='btn btn-success form-control' title='Scan IAR QR' id='scan_IAR' name='scan_IAR'>
                                                        <i class="fa fa-qrcode" aria-hidden="true"> Scan IAR QR </i></button>
                                            </div>
                                            <div style="padding: 1em">
                                                <div class="col-md-12">
                                                    <label for="rcef_id">Cooperative Name</label>
                                                    <input  class='form-control' style="text-wrap: wrap;" id='coopName' name='coopName' disabled>
                                                </div>
                
                                                <div class="col-md-12">
                                                    <label for="rsbsa_number">Province</label>
                                                    <input type="text" class='form-control' id='province' name='province' disabled>
                                                </div>
                                                <div class="col-md-12">
                                                    <label>Municipality</label>
                                                    <input  type="text" class='form-control' id='municipality' name='municipality' disabled>
                                                    
                                                </div>                                   
                                                <div class="col-md-12">
                                                    <label>Batch Ticket Number</label>
                                                    <input  type="text" class='form-control' id='batchTicketNumber' name='batchTicketNumber' disabled>
                                                    
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <label>Drop-off Point</label>
                                                    <input  type="text" class='form-control' id='dropOffPoint' name='dropOffPoint' disabled>
                                                </div>
                                            </div>

                                            <div style="padding: 1em;">
                                                <div class="col-md-12">                                  
                                                    <div class="col-md-6">
                                                        <label>Expected Bags</label>
                                                        <input  type="text" class='form-control' id='expectedBags' name='expectedBags' disabled>
                                                    </div>                                   
                                                    <div class="col-md-6">
                                                        <label>Accepted Bags</label>
                                                        <input  type="text" class='form-control' id='acceptedBags' name='acceptedBags' disabled>
                                                    </div>                                   
                                                </div>                                   
                                                <div class="col-md-12">
                                                    <div class="col-md-6">
                                                        <label>Delivery Status</label>
                                                        <input  type="text" class='form-control' id='deliveryStatus' name='deliveryStatus' disabled>
                                                    </div>     
                                                    <div class="col-md-6">
                                                        <label>Date of Delivery</label>
                                                        <input  type="text" class='form-control' id='deliveryDate' name='deliveryDate' disabled> 
                                                    </div> 
                                                </div> 

                                                <div class="col-md-12">
                                                    <div class="col-md-6">
                                                        <label>Payment Status</label>
                                                        <input  type="text" class='form-control' id='paymentStatus' name='paymentStatus' disabled>
                                                    </div>                                   
                                                    <div class="col-md-6">
                                                        <label>Has Delivery Receipt?</label>
                                                        <input  type="text" class='form-control' id='hasDR' name='hasDR' disabled>
                                                    </div>  
                                                </div>
                                            </div>                             
                                            
                                        </div>

                                    </div>
                                </div>
                                
                            </div>

                            <!-- card 2 -->
                            <div class="col-md-7">
                                <div class="card" style="padding-top: 1em; padding-bottom: 2em;">
                                    <div class="col-md-12">
                                    <center>  <h2>DV Batch</h2> </center>
                                    </div>
                                    
                                    <div class="col-md-12" style="padding-left: 2em; padding-right: 2em">
                                                    <!-- <label for="scan_IAR">Scan IAR QR</label> -->
                                                    <button class='btn btn-success form-control' title='Save and Generate' id='generateParticulars' name='generateParticulars'>
                                                        <i class="fa fa-save" aria-hidden="true"> Save and Generate FMIS Particulars </i></button>
                                    </div> 
                                    <div class="col-md-12" style="padding-left: 2em; padding-right: 2em">
                                                    <button class='btn btn-success form-control' style="display:none" id='viewGeneratedParticulars' name='viewGeneratedParticulars'>
                                                        <i class="fa fa-search" aria-hidden="true"> View Generated FMIS Particulars </i></button>
                                    </div> 
                                    <div class="row" style="padding: 2em; display:flex; gap:1em" id="iarContainer">
              
                                        <!-- <div class="col-md-4 shadow-xl" style="border-radius: 2em; padding: 2em;">
                                            <div>
                                                <h4>IAR Number:</h4>
                                                <h4>Location:</h4>
                                                <h4>Total Bags:</h4>
                                                <button class="btn btn-danger">Remove</button>
                                            </div>
                                        </div> -->
            
                                    </div>
                                    
                                </div>                    
                            </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCANNER IAR --}}
  <div class="modal fade" id="IAR_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">        
    <div class="modal-dialog">
      <div class="modal-content" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            Scan IAR QR

        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <center>
                    <video style="width: 50%;" id="IAR_preview" class="IAR_preview"></video> 
                    </center>
                </div>
            </div>
                   
        </div>
      </div>
    </div>
  </div>


<!-- IAR PREVIEW MODAL -->
<div id="show_iar_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>IAR-FMIS Generated Particulars</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success alert-dismissible fade in" role="alert" id="iar_fmis_msg" style="display: none;">
                    <strong><i class="fa fa-check-circle"></i> Success!</strong> IAR-FMIS Particulars copied to clipboard
                </div>
                <textarea name="iar_particulars" id="iar_particulars" cols="30" rows="5" class="form-control" readonly></textarea>
                <br>

                <div id="travelCostArea">

                </div>
                <button class="btn btn-success" id="addTranspo">Save Transportation Costs</button>

                <br>

                <div id="enterDV" style="display:none">
                    <label>DV Control Number</label>
                    <input type="text" class="form-control" id="dvNumber">
                    <br>
                    <button class="btn btn-success" id="addDV">Add DV Control #</button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="copy_btn" data-clipboard-target="#iar_particulars">Copy to clipboard</button>
            </div>
        </div>
    </div>
</div>
<!-- IAR PREVIEW MODAL -->


<!-- Particulars PREVIEW MODAL -->
<div id="show_particulars_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>IAR-FMIS Generated Particulars</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success alert-dismissible fade in" role="alert" id="particulars_fmis_msg" style="display: none;">
                    <strong><i class="fa fa-check-circle"></i> Success!</strong> IAR-FMIS Particulars copied to clipboard
                </div>
                <textarea name="view_particulars" id="view_particulars" cols="30" rows="5" class="form-control" readonly></textarea>
                <br>

                <div id="travelCostArea2">

                </div>
                <button class="btn btn-success" id="addTranspo2">Save Transportation Costs</button>

                <br>
                <div id="enterDV">
                    <label id="dvLabel2">DV Control Number</label>
                    <input type="text" class="form-control" id="dvNumber2">
                    <br>
                    <button class="btn btn-success" id="addDV2">Add DV Control #</button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="copy_particulars" data-clipboard-target="#view_particulars">Copy to clipboard</button>
            </div>
        </div>
    </div>
</div>
<!-- Particulars PREVIEW MODAL -->

@endsection

@push('scripts')

<script>

$("#scan_IAR").on("click", function(){
    Instascan.Camera.getCameras().then(function (cameras){
        if(cameras.length>0){
            IAR_scanner.start(cameras[0]);
            $('[name="options"]').on('change',function(){
                if($(this).val()==1){
                    if(cameras[0]!=""){
                        IAR_scanner.start(cameras[0]);
                    }else{
                        alert('No Front camera found!');
                    }
                }else if($(this).val()==2){
                    if(cameras[1]!=""){
                        IAR_scanner.start(cameras[1]);
                    }else{
                        alert('No Back camera found!');
                    }
                }
            });
        }else{
            console.error('No cameras found.');
            alert('No cameras found.');
        }
        }).catch(function(e){
            console.error(e);
            alert(e);
        });
    $('#IAR_modal').modal('show');
});

var IAR_scanner = new Instascan.Scanner({ video: document.getElementById('IAR_preview'), scanPeriod: 5, mirror: false });
var iarArray = [];
var generatedCode = '';
IAR_scanner.addListener('scan',function(content){  
    if(content !="" &&content != null && content != undefined )      
        // console.log(content);
        
        $.ajax({
                type: 'POST',
                url: "{{ route('checkIAR') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    content: content
                },
                success: function(data){
                    // console.log(data);
                    if(data==0||data=='0'){
                        $('#viewGeneratedParticulars').hide();
                        $('#generateParticulars').prop('disabled', false);
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('getIARdetails') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                content: content
                            },
                            success: function(data){
                                // console.log(data);
                                if(!data){
                                    alert('IAR cannot be found. Please try again.');
                                }else{
                                    $proc = JSON.parse(JSON.stringify(data));
                                    $('#coopName').val($proc[0].coopName);
                                    $('#province').val($proc[0].province);
                                    $('#municipality').val($proc[0].municipality);
                                    $('#batchTicketNumber').val($proc[0].batchTicketNumber);
                                    $('#dropOffPoint').val($proc[0].dropOffPoint);
                                    $('#expectedBags').val($proc[0].expectedBags);
                                    $('#acceptedBags').val($proc[0].acceptedBags);
                                    $('#deliveryStatus').val($proc[0].deliveryStatus);
                                    $('#deliveryDate').val($proc[0].deliveryDate);
                                    $('#paymentStatus').val($proc[0].paymentStatus);
                                    $('#hasDR').val($proc[0].hasDR);

                                    
                                    if(iarArray.length>0){
                                        if(iarArray[0]["iarNo"]==$proc[0].iarNo){
                                            alert('IAR already added. Please scan another IAR QR code.');
                                        }
                                        else{
                                            // $("#iarContainer").empty();
                                            if(iarArray[0]["deliveryType"]==$proc[0].deliveryType || iarArray[0]["coopName"]==$proc[0].coopName || iarArray[0]["province"]==$proc[0].province){
                                                if(iarArray[0]["deliveryType"]!=$proc[0].deliveryType){
                                                    alert('Please choose only deliveries of the same delivery type.');
                                                }
                                                else if(iarArray[0]["coopName"]!=$proc[0].coopName){
                                                    alert('Please choose only deliveries of the same cooperative.');
                                                }
                                                else if(iarArray[0]["province"]!=$proc[0].province){
                                                    alert('Please choose only deliveries of the same province.');
                                                }
                                                else{
                                                    iarArray.push(data[0]);
                                                    displayIars(data[0]);
                                                }
                                            }   
                                        }   
                                    }
                                    else{
                                        // console.log(data);
                                        $("#iarContainer").empty();
                                            iarArray.push(data[0]);
                                            displayIars(data[0]);
                                        }
                                    // console.log(iarArray.length);
                                    if(iarArray.length >= 5){
                                        $("#scan_IAR").css("display", "none");
                                    }else{
                                        $("#scan_IAR").css("display", "block");
                                    }
                                }
                                    
                            }
                        });
                    }
                    
                    else if(data==1||data=='1'){
                        alert('IAR already has particulars generated');
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('getParticulars') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                content: content
                            },
                            success: function(data){
                                iarArray = [];
                                $("#iarContainer").empty();
                                $('#generateParticulars').prop('disabled', true);
                                $('#viewGeneratedParticulars').show();
                                for(i=0;i<data.length;i++){
                                    displayIarsViewOnly(data[i]);
                                }
                                generatedCode = data[0].iarNo;

                            }
                        });
                    }
                    else if(data==9||data=='9'){
                        alert('IAR was not found. Please try again');
                        return;
                    }
                    else{
                        switch (data) {
                            case "to_rcv":
                                alert('IAR is still pending.');
                                break;
                            // case "to_prp":
                            //     alert('IAR is already for preparation.');
                            //     break;
                            case "to_prc":
                                alert('IAR is already prepared and for processing.');
                                break;
                            case "to_pay":
                                alert('IAR  is already awaiting payment.');
                                break;
                            case "accomplished":
                                alert('IAR is already paid.');
                                break;
                            case "returned":
                                alert('IAR is still pending.');
                                break;
                        }
                    }

                }
            });

        
            mode = "scan";
            $('#IAR_modal').modal('hide');
            IAR_scanner.stop();
            //getFarmerData(content);
        }); 
        
        $("#generateParticulars").on("click", function(){
            if(iarArray.length>0){
                $("#show_iar_modal").modal('show');
            }
            else{
                // e.preventDefault();
                alert("No IAR added. Please scan at least one IAR QR code.");
            }
        });

        $("#viewGeneratedParticulars").on("click", function(){
            // console.log(data);
                $("#show_particulars_modal").modal('show');
        });

        $("#show_iar_modal").on('show.bs.modal', function (e) {
            // console.log(iarArray);
            var particularsBatch = '';
            if(iarArray.length>0){
                $("#iar_particulars").empty().val("generating particulars...");
                $("#iar_fmis_msg").css("display", "none");

                
                temp = iarArray;
                for(i=0;i<iarArray.length;i++){
                    addTranspoCosts(iarArray[i], i);
                }
        
                $.ajax({
                    type: 'POST',
                    url: "{{ route('particularsPreview') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        iarArray: iarArray
                    },
                    success: function(data){
                        $("#iar_particulars").empty().val(data.particulars);
                        particularsBatch = data.particularsBatch;
                        iarArray = [];
                        $("#iarContainer").empty();
                    }
                });

                
                
                $("#addTranspo").on("click", function(){
                    // console.log(temp);
                    tempdata = [];
                    for(i=0;i<temp.length;i++){
                    tempdata.push({
                        "iar_number": temp[i].iarNo,
                        "batchTicketNumber": temp[i].batchTicketNumber,
                        "transpo_cost_per_bag": $("#cost" + i).val()
                    });
                }
                    
                    // console.log(tempdata);
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('saveTranspoCost') }}",
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}",
                            tempdata: tempdata,
                        },
                        success: function(data){
                            console.log(data);
                            alert("Transportation cost successfully updated.");
                            $("#iar_particulars").empty().val(data.particulars);
                        }
                    });


                });

                $("#addDV").on("click", function(){
                var dv = $('#dvNumber').val();
                if(dv.length>0){
                    $.ajax({
                    type: 'POST',
                    url: "{{ route('addDVnumber') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        particularsBatch: particularsBatch,
                        dv: dv
                    },
                    success: function(data){
                        if(data>0 && data < 9)
                        {
                            alert("DV number added successfully.");
                            $('#addDV').prop("disabled", true);
                            $('#dvNumber').prop("disabled", true);
                        }
                        else if(data == 9)
                        {
                            alert("DV number already assigned.");
                            $('#dvNumber').empty();
                        }
                        
                        }
                    });


                }
                else{
                    alert('Please enter DV number.');
                }
                });
            }
            else{
                e.preventDefault();
                alert("No deliveries are selected. Please select at least one delivery.");
            }
        });

        $("#iarContainer").on("click", ".removeIar", function(){
            var toDelete = $(this).data("iar-no");
            // console.log(toDelete);
            var toDelete2 = iarArray.find((element) => element.iarNo == toDelete);
            iarArray.splice(toDelete2, 1);
            // console.log(iarArray);
            $("#"+toDelete).remove();
            $("#scan_IAR").css("display", "block");
        });

        function displayIars(iar){
            $("#iarContainer").append(
                `
                <div class="col-md-4 shadow-xl" id="${iar.iarNo}" style="border-radius: 2em; padding: 2em;">
                    <div>
                        <h4>IAR Number: ${iar.iarNo}</h4>
                        <h4>Location: ${iar.dropOffPoint? iar.dropOffPoint : ''}${iar.municipality? ", "+iar.municipality : ""}${iar.province? ", "+iar.province : ""}</h4>
                        <h4>Total Bags: ${iar.acceptedBags}</h4>
                        <button class="btn btn-danger removeIar remove${iar.iarNo}" data-iar-no="${iar.iarNo}">Remove</button>
                    </div>
                </div>
                `
            );
        }

        function displayIarsViewOnly(iar){
            
            $("#iarContainer").append(
                `
                <div class="col-md-4 shadow-xl" id="${iar.iarNo}" style="border-radius: 2em; padding: 2em;">
                    <div>
                        <h4>IAR Number: ${iar.iarNo}</h4>
                        <h4>Location: ${iar.dropOffPoint? iar.dropOffPoint : ''}${iar.municipality? ", "+iar.municipality : ""}${iar.province? ", "+iar.province : ""}</h4>
                        <h4>Total Bags: ${iar.acceptedBags}</h4>
                    </div>
                </div>
                `
            );
        }

        function displayTranspoCosts(iar){
            // console.log(iar);
            $("#travelCostArea2").append(`
            <div id="cost${iar.iar_number}">
                    <div>
                        <h5>Transportation Cost for IAR Number: ${iar.iar_number}</h5>
                        <h5>Location: ${iar.dropOffPoint? iar.dropOffPoint : ''}${iar.municipality? ", "+iar.municipality : ""}${iar.province? ", "+iar.province : ""}</h5>
                        <h5>Total Bags: ${iar.acceptedBags}</h5>
                        <input type="text" class="form-control" id="transpoCost${iar.iar_number}" oninput="this.value = this.value.replace(/[^0-9]/g, '');" value="${iar.transpo_cost_per_bag}">
                        <br>
                    </div>
                </div>
            `);
        }

        function addTranspoCosts(iar,index){
            // console.log(iar);
            $("#travelCostArea").append(`
            <div id="cost${iar.iarNo}">
                    <div>
                        <h5>Transportation Cost for IAR Number: ${iar.iarNo}</h5>
                        <h5>Location: ${iar.dropOffPoint? iar.dropOffPoint : ''}${iar.municipality? ", "+iar.municipality : ""}${iar.province? ", "+iar.province : ""}</h5>
                        <h5>Total Bags: ${iar.acceptedBags}</h5>
                        <input type="text" class="form-control" id="cost${index}" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <br>
                    </div>
                </div>
            `);
        }


        $("#show_particulars_modal").on('show.bs.modal', function (e) {
            // displayTranspoCosts('iar');
            // console.log(generatedCode);
            $.ajax({
                type: 'POST',
                url: "{{ route('getGeneratedParticulars') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    generatedCode: generatedCode,
                },
                success: function(data){
                    $("#view_particulars").empty().val(data);
                    let tempdata = [];
                    //Transpo Cost
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('getTranspoCost') }}",
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}",
                            generatedCode: generatedCode,
                        },
                        success: function(data){
                            // console.log(data);
                            $("#travelCostArea2").empty();
                            for(i=0;i<data.length;i++){
                                displayTranspoCosts(data[i]);
                                } 
                                $("#addTranspo2").on("click", function(){
                                    tempdata = [];
                                    for (var i = 0; i < data.length; i++) {
                                        tempdata.push({
                                            "iar_number": data[i].iar_number,
                                            "batchTicketNumber": data[i].batchTicketNumber,
                                            "transpo_cost_per_bag": $("#transpoCost" + data[i].iar_number).val()
                                        });
                                    }
                                    // console.log(tempdata);
                                    $.ajax({
                                        type: 'POST',
                                        url: "{{ route('saveTranspoCost') }}",
                                        dataType: "json",
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            tempdata: tempdata,
                                        },
                                        success: function(data){
                                            alert("Transportation cost successfully updated.");
                                            $("#view_particulars").empty().val(data.particulars);
                                        }
                                    });
                                });
                        }
                        
                    });

                    //DV Number
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('hasDVnumber') }}",
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}",
                            generatedCode: generatedCode,
                        },
                        success: function(data){
                            if(data == 9)
                            {
                                $('#dvNumber2').val('');
                                $('#addDV2').prop("disabled", false);
                                $('#dvNumber2').prop("disabled", false)
                            }
                            else
                            {
                                $('#addDV2').prop("disabled", true);
                                $('#dvNumber2').prop("disabled", true);
                                $('#dvNumber2').val(data[0].dv_number);
                            }
                        }
                    });
                }
            });

            $("#addDV2").on("click", function(){
                var dv2 = $('#dvNumber2').val();
                if(dv2.length>0){
                    $.ajax({
                    type: 'POST',
                    url: "{{ route('addDVnumber2') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        generatedCode: generatedCode,
                        dv2: dv2
                    },
                    success: function(data){
                        if(data>0 && data < 9)
                        {
                            alert("DV number added successfully.");
                            $('#addDV2').prop("disabled", true);
                            $('#dvNumber2').prop("disabled", true);
                        }
                        else if(data == 9)
                        {
                            alert("DV number already assigned.");
                            $('#dvNumber').empty();
                        }
                        
                    }
                });
                }
                else{
                    alert('Please enter DV number.');
                }
            });
        
        });

        document.getElementById("copy_btn").addEventListener("click", function() {
        var copy_status = copyToClipboard(document.getElementById("iar_particulars"));
        if(copy_status == true){
            $("#iar_fmis_msg").css("display", "block");
            $('#enterDV').show();
        }
    });
    
    document.getElementById("copy_particulars").addEventListener("click", function() {
        var copy_status = copyToClipboard(document.getElementById("view_particulars"));
        if(copy_status == true){
            $("#particulars_fmis_msg").css("display", "block");
        }
    });


    


        function copyToClipboard(elem) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
        var origSelectionStart, origSelectionEnd;
        if (isInput) {
            // can just use the original source element for the selection and copy
            target = elem;
            origSelectionStart = elem.selectionStart;
            origSelectionEnd = elem.selectionEnd;
        } else {
            // must use a temporary form element for the selection and copy
            target = document.getElementById(targetId);
            if (!target) {
                var target = document.createElement("textarea");
                target.style.position = "absolute";
                target.style.left = "-9999px";
                target.style.top = "0";
                target.id = targetId;
                document.body.appendChild(target);
            }
            target.textContent = elem.textContent;
        }
        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);
        
        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch(e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }
        
        if (isInput) {
            // restore prior selection
            elem.setSelectionRange(origSelectionStart, origSelectionEnd);
        } else {
            // clear temporary content
            target.textContent = "";
        }
        return succeed;
    }

</script>
@endpush()
