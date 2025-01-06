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
                                                <option value="" disabled selected>Select Municipality</option>
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
                    <div class="x_title">
                        <h2>Result Table</h2>
                        <div class="clearfix"></div>
                        <button class="btn btn-success" id="approved_all_btn">approve all</button>
                    </div>
                    <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                                <table class="table table-striped table-bordered wrap" id="farmersTbl_2">
                                    <thead>
                                        <tr>
                                            <th></th>
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
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('public/assets/daterangepicker/daterangepicker.js') }}"></script>
    <script>


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
                    alert('Thing was saved to the database.');
                    $('#farmersTbl_2').DataTable().clear();	
                    DateRepo();
                }
            }
        });            
        } else {
        
        }



        });



        $('body').on('click', '.validating_btn', function() {

            if (confirm('Are you sure this is Validated?')) {
                $.ajax({
                type:"POST",
                url: "validating_data",
                data:{
                    "_token": "{{csrf_token()}}",
                    "id": $(this).attr("data-id"),
                },
                success: function(response){
                    if(response=="validated"){
                        alert('Farmer Details Valicated.');
                        $('#farmersTbl_2').DataTable().clear();	
                        DateRepo();
                    }
                }
            });            
            } else {
              
            }


                      
        });
        $('#farmersTbl_2').DataTable();

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
            columns: [{
                    data: 'checkbox',
                    name: 'checkbox',
                    "orderable": false
                },{
                    data: 'ffrs_rsbsa_no',
                    name: 'ffrs_rsbsa_no',
                    class:'text-wrap width-200'
                },
                {
                    data: 'rsbsa_control_no',
                    name: 'rsbsa_control_no',
                    class:'text-wrap width-200'
                },
                {
                    data: 'ffrs_first_name',
                    name: 'ffrs_first_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'ffrs_middle_name',
                    name: 'ffrs_middle_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'ffrs_last_name',
                    name: 'ffrs_last_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'ffrs_ext_name',
                    name: 'ffrs_ext_name',
                    class:'text-wrap width-200'
                },
                {
                    data: 'ffrs_gender',
                    name: 'ffrs_gender'
                }, {
                    data: 'ffrs_parcel_area',
                    name: 'ffrs_parcel_area',
                    class:'text-wrap width-200'
                }, {
                    data: 'ffrs_parcel_address_prv',
                    name: 'ffrs_parcel_address_prv',
                    class:'text-wrap width-200'
                }, {
                    data: 'ffrs_parcel_address_mun',
                    name: 'ffrs_parcel_address_mun',
                    class:'text-wrap width-200'
                }, {
                    data: 'ffrs_parcel_address_bgy',
                    name: 'ffrs_parcel_address_bgy',
                    class:'text-wrap width-200'
                }, {
                    data: 'ffrs_mother_maiden_name',
                    name: 'ffrs_mother_maiden_name',
                    class:'text-wrap width-200'
                }, {
                    data: 'action',
                    name: 'action'
                }
            ]
        });
       }

       $('#approved_all_btn').click(function(){
        alert();
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
