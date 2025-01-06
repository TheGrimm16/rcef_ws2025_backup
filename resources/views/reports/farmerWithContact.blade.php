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
                <h2>Farmer Profile w/ Contact Info</h2>
                 
                

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
                   Filter
                </h2>

                <select  name="province_fg" id="province_fg" class="form-control">
                            <option value="0">Please select a Province</option>
                    <!-- READY FOR DS2021 -->
							@foreach ($province_list as $row)
								<option value="{{$row->regCode}}{{$row->provCode}}">{{$row->province}}</option>
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
               Statistics
            </h2>
             <button class="btn btn-success btn-sm" style="float:right;" id="excel_btn" onclick="excel_btn();" disabled="true">
                Export to Excel
            </button>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th>Province</th>
                    <th>Municipality</th>
                    <th>Total Number of Farmer <br> w/ Contact Info. </th>
                    <th>Percentage</th>
                </thead>
                <tbody id='databody'>
                </tbody>
            </table>
        </div>
        </div><br>
        <!-- /distribution details -->
    </div>


@endsection
@push('scripts')

    <script type="text/javascript">

        function excel_btn(){
            $("#excel_btn").empty().html("Fetching data...");
            $("#excel_btn").attr("disabled","");

            var prv = $("#province_fg").val();
            var province = $("#province_fg option:selected").text();
            var municipality  = $("#municipality_fg").val();
           $.ajax({
            type: 'POST',
            url: "{{ route('farmer_profile.with.contact.export') }}",
            data: {
                _token: "{{ csrf_token() }}",
                prv: prv,
                province: province,
                municipality: municipality
            },
            success: function (response, textStatus, request) {
                var a = document.createElement("a");
                a.href = response.file; 
                a.download = response.name;
                document.body.appendChild(a);
                a.click();
                a.remove();

                $("#excel_btn").removeAttr('disabled');
                $("#excel_btn").empty().html('<i class="fa fa-table"></i> Export to Excel');
            }
        });
        }


         $('select[name="province_fg"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province_code  = $("#province_fg").val();
                var province = $("#province_fg option:selected").text();


                if(province_code!=="0"){
                    document.getElementById("excel_btn").removeAttribute("disabled");
                }else{
                   document.getElementById("excel_btn").setAttribute("disabled", "true");
                }

                $('select[name="municipality_fg"]').empty().append('<option value="0">Please select a Municipality</option>');
                    $.ajax({
                        method: 'POST',
                        url: "{{route('farmer_profile.contact.municipality')}}",
                        data: {
                            _token: _token,
                            province: province
                        },
                        dataType: 'json',
                        success: function (source) {
                         $('select[name="municipality_fg"]').empty().append('<option value="0">All Municipality</option>');
                        $.each(source, function (i, d) {
                            $('select[name="municipality_fg"]').append('<option value="' + d.munCode + '">' + d.municipality + '</option>');
                        });
                        }
                    }); //AJAX GET MUNICIPALITY

                    genTable();

                HoldOn.close();
        });  //END PROVINCE SELECT

          $('select[name="municipality_fg"]').on('change', function () {
            var province_code  = $("#province_fg").val();

            if(province_code!=="0"){
                document.getElementById("excel_btn").removeAttribute("disabled");
            }else{
               document.getElementById("excel_btn").setAttribute("disabled", "true");
            }


             genTable();
         });  //END PROVINCE SELECT

         function genTable(){
            var prv = $("#province_fg").val();
            var province = $("#province_fg option:selected").text();
            var municipality  = $("#municipality_fg").val();


            $('#dataTBL').DataTable().clear();
                $("#dataTBL").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{route('farmer_profile.contact.gentable')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            province: province,
                            municipality: municipality,
                            prv: prv,
                        }
                    },
                    "columns":[
                        {"data": "province"},
                        {"data": "municipality"},
                        {"data": "count"},
                        {"data": "percent"},
                    ]
                });
         }


    </script>

@endpush

