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
                <h2>Payment Dashboard (IAR, DV, etc.)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="accordion">
                    @foreach ($coops as $coop)
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0" style="margin:0">
                                    <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                        {{$coop->coopName}}
                                    </button>
                                </h5>
                                <button class="btn btn-warning btn-sm" style="top: 10%;margin-right: 10px;position: absolute;right: 0%;" data-toggle="modal" data-target="#show_coop_batches_modal" data-coop="{{$coop->coopId}}" data-coop_accre="{{$coop->accreditation_no}}"><i class="fa fa-eye"></i> VIEW IAR</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COOP BATCHES MODAL -->
<div id="show_coop_batches_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative;top:4%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="coop_name_modal">
                    <span>COOP NAME</span><br>
                </h4>
                <span id="coop_accreditation_modal"></span>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered" id="coop_batch_table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>IAR Number</th>
                            <th>Batch Code</th>
                            <th>Region</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Drop off Point</th>
                            <th>Delivery Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- COOP BATCHES MODAL -->


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
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="copy_btn" ddata-clipboard-target="#iar_particulars">Copy to clipboard</button>
            </div>
        </div>
    </div>
</div>
<!-- IAR PREVIEW MODAL -->


<!-- DV PREVIEW MODAL -->
 <div id="show_dv_info_modal" class="modal fade" role="dialog">
     <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="province_export_title">
                       Disbursement # <label id="modal_dv"></label>
                    </h4>
                </div>

                <div class="modal-body">
                    <label for="" class="col-xs-2">Batch number:</label>
                    <label id="modal_batch"></label> <br>
                    
                    <label for="" class="col-xs-2">IAR number:</label>
                    <label id="modal_iar"></label> <br>
                    
                    <label for="" class="col-xs-2">DV Date:</label>
                    <label id="modal_dvDate">data</label> <br>
                    
                    <label for="" class="col-xs-2">Project Code:</label>
                    <label id="modal_projectCode">data</label> <br>
                    
                    <label for="" class="col-xs-2">Program Code:</label>
                    <label id="modal_programCode">data</label> <br>
                    
                    <label for="" class="col-xs-2">BUR No:</label>
                    <label id="modal_burNo">data</label> <br>

                    <label for="" class="col-xs-2">Check No:</label>
                    <label id="modal_checkNo">data</label> <br>
                    
                    <label for="" class="col-xs-2">Check Date:</label>
                    <label id="modal_checkDate">data</label> <br>
                    
                    <label for="" class="col-xs-2">Particulars:</label>
                    <textarea id="modal_particulars" rows="3" disabled="" style="width: 100%;"></textarea>
                              


              </div>

            </div>
     </div>
 </div>
<!-- DV PREVIEW MODAL -->














@endsection

@push('scripts')

<script>


    $('#show_dv_info_modal').on('show.bs.modal', function (e) {
    
        //die();
        var batch = $(e.relatedTarget).data('batchlist');
        var iar = $(e.relatedTarget).data('iarlist');
        var dv = $(e.relatedTarget).data('dvno');
        var dvDate = $(e.relatedTarget).data('dvdate');
        var projectCode = $(e.relatedTarget).data('projectcode');
        var programCode = $(e.relatedTarget).data('programcode');
        var burNo = $(e.relatedTarget).data('burno');
        var particulars = $(e.relatedTarget).data('particulars'); 
        var checkNo = $(e.relatedTarget).data('checkno');
        var checkDate = $(e.relatedTarget).data('checkdate');
        var accountNo = $(e.relatedTarget).data('accountno');
        var accountCode = $(e.relatedTarget).data('accountcode');
        var earnAmount = $(e.relatedTarget).data('earnamount');
        var dedAmount = $(e.relatedTarget).data('dedamount');
        var netAmount = $(e.relatedTarget).data('netamount');

        if(checkNo === "" ){
            checkNo = "N/A"; 
        }
        if(checkDate === "" ){
            checkDate = "N/A"; 
        }
        if(burNo === "" ){
            burNo = "N/A"; 
        }

         $("#modal_dv").empty().html(dv);
         $("#modal_batch").empty().html(batch);
         $("#modal_iar").empty().html(iar);
         $("#modal_dvDate").empty().html(dvDate);
         $("#modal_projectCode").empty().html(projectCode);
         $("#modal_programCode").empty().html(programCode);
         $("#modal_burNo").empty().html(burNo);
         $("#modal_checkNo").empty().html(checkNo);
         $("#modal_checkDate").empty().html(checkDate);
         $("#modal_particulars").empty().html(particulars);
        
    });








    
    $("#coop_batch_table").DataTable();

    $('#show_coop_batches_modal').on('show.bs.modal', function (e) {
        var coop_id = $(e.relatedTarget).data('coop');
        var coop_accre = $(e.relatedTarget).data('coop_accre');

        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_dashboard.coop.name') }}",
            data: {
                _token: "{{ csrf_token() }}",
                coop_id: coop_id
            },
            success: function(data){
                $("#coop_name_modal").empty().html(data);
                $("#coop_accreditation_modal").empty().html("Accreditation Number: "+coop_accre);
            }
        });
    
        //get batch details of selected coop
        $('#coop_batch_table').DataTable().clear();
        $("#coop_batch_table").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('payment_dashboard.iar_tbl.home') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    "_token": "{{ csrf_token() }}",
                    "coop_accre": coop_accre
                }
            },
            "columns":[
                {"data": "iar_number"},
                {"data": "batch_code"},
                {"data": "region"},
                {"data": "province"},
                {"data": "municipality"},
                {"data": "dop"},
                {"data": "delivery_date"},
                {"data": "action"}
            ]
        });
    });


    $("#show_iar_modal").on('show.bs.modal', function (e) {
        var batch_code = $(e.relatedTarget).data('iar');
        var iar_code = $(e.relatedTarget).data('iar_code');
        
        $("#iar_particulars").empty().val("generating particulars...");
        $("#iar_fmis_msg").css("display", "none");

        $.ajax({
            type: 'POST',
            url: "{{ route('payment_dashboard.particulars') }}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                batch_code: batch_code,
                iar_code: iar_code
            },
            success: function(data){
                $("#iar_particulars").empty().val(data);
            }
        });
    });


    document.getElementById("copy_btn").addEventListener("click", function() {
        var copy_status = copyToClipboard(document.getElementById("iar_particulars"));
        if(copy_status == true){
            $("#iar_fmis_msg").css("display", "block");
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
