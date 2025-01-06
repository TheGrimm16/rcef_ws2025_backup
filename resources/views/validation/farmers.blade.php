@extends('layouts.index')

@section('content')
    <link rel="stylesheet" href="{{ asset('public/assets/boxed-check/css/boxed-check.css') }}">


    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/daterangepicker/daterangepicker.css') }}" />
    <style>
        .title_count {
            height: 70px;
        }

        span.label {
            font-size: 11px !important;
        }

        .text-wrap{
    white-space:normal;
}
.width-200{
    width:200px;
}


    </style>
    <style>
        .lds-facebook {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-facebook div {
            display: inline-block;
            position: absolute;
            left: 8px;
            width: 16px;
            background: #26B99A;
            animation: lds-facebook 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite;
        }

        .lds-facebook div:nth-child(1) {
            left: 8px;
            animation-delay: -0.24s;
        }

        .lds-facebook div:nth-child(2) {
            left: 32px;
            animation-delay: -0.12s;
        }

        .lds-facebook div:nth-child(3) {
            left: 56px;
            animation-delay: 0;
        }

        @keyframes lds-facebook {
            0% {
                top: 8px;
                height: 64px;
            }

            50%,
            100% {
                top: 24px;
                height: 32px;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Filters</h2>
                        <div class="clearfix"></div>
                    </div>
                    <form method="post" id="generateData_2">
                        <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                            <div class="row tile_count" style="margin: 0">
                                <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label for="province_2" class="control-label">Province </label><br />
                                            <select id="province_2" name="province_2"
                                                class="js-example-basic-single js-states select form-control"
                                                style="width: 100% !important">
                                                <option value="" disabled selected>Select Province</option>                                               
                                                @foreach ($provinces as $k => $p)
                                                    <option value="{{ $p->ffrs_parcel_address_prv }}">{{ $p->ffrs_parcel_address_prv }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="municipality" class="control-label">Municipality </label><br />
                                            <select id="municipality" name="municipality"
                                                class="js-example-basic-single js-states select form-control"
                                                style="width: 100% !important">
                                              
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="status" class="control-label">RSBSA/ FARMER's NAME </label><br />
                                            <input type="text" name="rsbsa" class="form-control" id="rsbsa">
                                        </div>

                                        <div class="col-sm-3">                                            
                                                <label for="claim_loc" class="control-label" style="margin-top: 10px">&nbsp;
                                                </label>
                                                <button type="submit" class="form-control btn btn-success"
                                                    style="margin-top:20px;">FILTER</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12">
                <div class="x_panel">
                    <h2>Result Table</h2>
                   {{--  <div class="x_title">
                        <h2>Result Table</h2>
                        <div class="clearfix"></div>
                        @if (Auth::user()->roles->first()->name == "Coordinator" ||Auth::user()->roles->first()->name == "seed-inspector" ||Auth::user()->roles->first()->name == "dro" ||Auth::user()->roles->first()->name == "branch-it" ||Auth::user()->roles->first()->name == "rcef-programmer" )
                        <div class="row">
                            <div class="col" id="approvRejectBtn" style="display: flex">
                                <button class="btn btn-success" id="approved_all_btn">Approv Selected</button>
                                <button class="btn btn-warning" id="redjected_all_btn">Reject Selected</button><br>
                               
                            </div>
                            <div class="col" id="approvRejectBtn" style="display: flex">
                                <label for="remarks"></label>
                                <textarea class="form-control" name="remarks" id="remarks" cols="30" rows="10" placeholder="Remarks for Rejected farmers"></textarea>
                            </div>
                           
                        </div>
                        

                        
                            
                        @endif
                    </div> --}}
                    <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                <table class="table table-striped table-bordered wrap" id="farmersTbl_2">
                                    <thead>
                                        <tr>
                                           
                                            <th style="width: auto;">RSBSA NEW</th>
                                            <th style="width: auto;">RSBSA OLD</th>
                                            <th style="width: auto;">First Name</th>
                                            <th style="width: auto;">Middle Name</th>
                                            <th style="width: auto;">Last Name</th>
                                            <th style="width: auto;">Ext Name</th>
                                            <th style="width: auto;">Sex</th>
                                            <th style="width: auto;">Area</th>
                                            <th style="width: auto;">Province</th>
                                            <th style="width: auto;">Municipality</th>
                                            <th style="width: auto;">Barangay</th>
                                            <th style="width: auto;">Mother's Maiden Name</th>
                                            <th style="width: auto;">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('validation.farmersModal') 
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('public/assets/daterangepicker/daterangepicker.js') }}"></script>
    <script>



$('body').on('submit', '#ProductForm', function (event) {

    event.preventDefault();
    console.log('Product Add submitting...');
    $.ajax({
        url: "{{ url('inventory/insert-product') }}",
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
           
        },
        success: function (response) {
        
            
        
        },
        error: function (error) {

        }
    });
    });


        $('body').on('click', '.approving_btn', function() {
        
        if (confirm('Are you sure you want?')) {
            $.ajax({
            type:"POST",
            url: "validating_data",
            data:{
                "_token": "{{csrf_token()}}",
                "id": $(this).attr("data-id"),
            },
            success: function(response){
                if(response=="validated" || response=="approved"){
                    alert('Farmer Details Approved.');
                    $('#farmersTbl_2').DataTable().clear();	
                    DateRepo();
                }
            }
        });            
        } else {
        
        }



        });

        
        $('body').on('click', '.invalid_btn', function() {
            $.ajax({
                type:"POST",
                url: "getFarmerData",
                data:{
                    "_token": "{{csrf_token()}}",
                    "id": $(this).attr("data-id"),
                    "province": $("#province_2").val(),
                    "municipality": $("#municipality").val(),
                },
                success: function(response){

                    $("#rsbsa_control_no").val(response[0].rsbsa_control_no);
                    $("#old_rsbsa").val(response[0].old_rsbsa);
                    $("#first_name").val(response[0].first_name);
                    $("#middle_name").val(response[0].middle_name);
                    $("#last_name").val(response[0].last_name);
                    $("#ext_name").val(response[0].ext_name);
                    $("#sex").val(response[0].sex);
                    $("#parcel_area").val(response[0].parcel_area);
                    $("#farmer_prv").val(response[0].farmer_prv);
                    $("#farmer_muni").val(response[0].farmer_muni);
                    $("#farmer_brgy_name").val(response[0].farmer_brgy_name);
                    $("#m_fname").val(response[0].m_fname);
                    $("#m_mname").val(response[0].m_mname);
                    $("#m_lname").val(response[0].m_lname);
                    $("#m_ename").val(response[0].m_ename);
                    $("#data_id").val(response[0].id);
                    
                    $('#farmersValidationForm').modal('show');
                }
            });
        });
        $('body').on('click', '.validating_btn', function() {

            if (confirm('Are you sure this is Validated?')) {
                $.ajax({
                type:"POST",
                url: "validating_data",
                data:{
                    "_token": "{{csrf_token()}}",
                    "id": $(this).attr("data-id"),
                    "province": $("#province_2").val(),
                    "municipality": $("#municipality").val(),
                },
                success: function(response){
                    if(response=="ok"){
                        alert('Farmer Details Valicated.');
                        $('#farmersTbl_2').DataTable().clear();	
                        DateRepo();
                    }else{
                        alert("Error! Please Contact Your IT Officer!");
                    }
                }
            });            
            } else {
              
            }


                      
        });
        $('#farmersTbl_2').DataTable({
            "columnDefs": [ {
          "targets": 0,
          "orderable": false,
            }],
        });

        $('input[name="daterange"]').daterangepicker();
        $("#province_2").select2({
            width: 'resolve'
        });
        $("#municipality").select2({
            width: 'resolve'
        });
        $("#status").select2({
            width: 'resolve'
        });
        $("#users").select2({
            width: 'resolve'
        });
        $("#participants").select2({
            width: 'resolve'
        });
        $("#province_2").on('change', function() {
            if ($(this).val() == "") {
                $("#municipality").prop("disabled", true);
                $("#municipality").val("");
                $("#municipality").empty();
                
            } else {

                $.ajax({
                    type: "POST",
                    url: "{{ route('validation.municipality') }}",
                    data: {
                        "_token": "{{csrf_token()}}",
                        "provCode": $(this).val()
                    },
                    success: function(response) {
                        $("#municipality").prop("disabled", false);
                        obj = JSON.parse(response);

                        $('#municipality').empty();
                        
                        $('#municipality').append($('<option>').val("").text("Select Municipality"));
                        $('#municipality').append($('<option>').val("all").text("All"));
                        obj.forEach(data => {
                            $('#municipality').append($('<option>').val(data.ffrs_parcel_address_mun).text(
                                data.ffrs_parcel_address_mun));
                        });
                    }
                });
            }
        });

       function DateRepo(){
        $('#farmersTbl_2').DataTable({
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{!! route('farmers.data.validation.datatable') !!}",
                "type": "POST",
                "data": function(d) {
                    d._token = "{{csrf_token()}}";
                    d.province = $("#province_2").val();
                    d.municipality = $("#municipality").val();
                    d.rsbsa = $("#rsbsa").val();
                }
            },
            "aoColumnDefs": [
            { "bSortable": false, "aTargets": [0] },            
            ],
            columns: [{
                    data: 'rsbsa_control_no',
                    name: 'rsbsa_control_no',
                    class:'text-wrap width-200'
                },
                {
                    data: 'old_rsbsa',
                    name: 'old_rsbsa',
                    class:'text-wrap width-200'
                },
                {
                    data: 'first_name',
                    name: 'first_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'middle_name',
                    name: 'middle_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'last_name',
                    name: 'last_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'ext_name',
                    name: 'ext_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'sex',
                    name: 'sex'
                }, {
                    data: 'parcel_area',
                    name: 'parcel_area',
                    class:'text-wrap width-200'
                }, {
                    data: 'farmer_prv',
                    name: 'farmer_prv',
                    class:'text-wrap width-200'
                }, {
                    data: 'farmer_muni',
                    name: 'farmer_muni',
                    class:'text-wrap width-200'
                }, {
                    data: 'farmer_brgy_name',
                    name: 'farmer_brgy_name',
                    class:'text-wrap width-200'
                }, {
                    data: 'm_fullname',
                    name: 'm_fullname',
                    class:'text-wrap width-200'
                }, {
                    data: 'action',
                    name: 'action'
                }
            ]
        });
       }

       $(".checkBoxAll").click(function(){
        
    $('input:checkbox').not(this).prop('checked', this.checked);
    showBtn();
});

        $('body').on('click', '.checkBoxSelected', function() {
            showBtn();
        });  
        
        function showBtn(){
            var checkBoxSelected = $('.checkBoxSelected');
            var FarmersSelected = [];
            for(var i = 0; i < checkBoxSelected.length; i++){           
            if($(checkBoxSelected[i]).prop("checked")){
                var id=$(checkBoxSelected[i]).attr("data-id");
                FarmersSelected.push(id)
            }  
            if(FarmersSelected.length >1){
                approvRejectBtn
                $('#approvRejectBtn').show();
            }else{
                $('#approvRejectBtn').hide();
            }                                                                        
        }
        }
       $('#approved_all_btn').click(function(){       
        var checkBoxSelected = $('.checkBoxSelected');
        var FarmersSelected = [];    
        for(var i = 0; i < checkBoxSelected.length; i++){           
            if($(checkBoxSelected[i]).prop("checked")){
                var id=$(checkBoxSelected[i]).attr("data-id");
                FarmersSelected.push(id)
            }                                                                           
        }
        console.log(FarmersSelected);

        $.ajax({
                type:"POST",
                url: "../../approvedAllFarmer",
                data:{
                    '_token': "{{csrf_token()}}",
                    'ids': FarmersSelected,
                },
                success: function(response){
                    if(response=="validated" || response=="approved"){
                        alert('Farmer Details Valicated.');
                        $('#farmersTbl_2').DataTable().clear();	
                        DateRepo();
                    }
                }
            });

       });

       $('#redjected_all_btn').click(function(){       
        var checkBoxSelected = $('.checkBoxSelected');
        var FarmersSelected = [];    
        for(var i = 0; i < checkBoxSelected.length; i++){           
            if($(checkBoxSelected[i]).prop("checked")){
                var id=$(checkBoxSelected[i]).attr("data-id");
                FarmersSelected.push(id)
            }                                                                           
        }
        console.log(FarmersSelected);

       });
        $("#generateData_2").on("submit", function(e) {
            e.preventDefault();
          
           $('#farmersTbl_2').DataTable().clear();	
            if( $("#province_2").val() !="" && $("#municipality").val() !=""){
            
            DateRepo();
            //$('#farmersTbl_2').DataTable().ajax.reload();
           // summaryContent();
            var mun = $("#municipality").val();
            var prov = $("#province_2").val();
            }else{alert("Input Data from Filter Secion")}
            

        });
        

        function summaryContent() {
            $("#summaryContent").empty().append("<div class='lds-facebook'><div></div><div></div><div></div></div>");
            $.ajax({
                type: "POST",
                url: "{!! route('farmers.data.validation.datatable') !!}",
                data: function(d) {
                    d._token = "{{csrf_token()}}";
                    d.province = $("#province_2").val();
                    d.municipality = $("#municipality").val();
                    d.rsbsa = $("#rsbsa").val();
                },
                success: function(response) {
                    $("#summaryContent").empty().append(response);
                }
            });
        }
    </script>
@endpush
