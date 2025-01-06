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
                <h2>History Monitoring</h2>
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
                <input type="hidden" name="hidden_region" id="hidden_region">
                <input type="hidden" name="userId" id="userId" value="{{$userID}}">
                <select name="province_fg" id="province_fg" class="form-control">
                            <option value="0">Please select a Province</option>
                            @foreach ($municipal_list as $row)
                                <option value="{{$row->provCode}}">{{$row->provDesc}}</option>
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
                HISTORY MONITORING
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
                    <th>Drop Off Point</th>
                    <th>Seed Variety</th>
                    <th>Total Bag Coun</th>
                    <th>Date Created</th>
                   
                </thead>
              
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
                        DELETE HISTORY
                    </h4>
                </div>

                <input type="text" id="hidden_id" value="">

                <div class="modal-body">
                    <label for="" class="col-xs-2">DOP:</label>
                    <label id="modal_dop"></label> <br>
                    <label for="" class="col-xs-2">Seed Variety:</label>
                    <label id="modal_seed"></label> <br>
                    <label for="" class="col-xs-2">Bag Count:</label>
                    <label id="modal_bcount"></label> <br>
            


                    <button id="del_btn" type="button" class="btn btn-warning "><i class='fa fa-trash'> </i> CONFIRM</button>
                       

              </div>

            </div>
     </div>
 </div>



@endsection
@push('scripts')

    <script type="text/javascript">
        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });



                    //FAR GENERATION LOAD MUNICIPALITIES
        $('select[name="province_fg"]').on('change', function () {
            

            HoldOn.open(holdon_options);
            var provCode = $(this).val();
        $('select[name="municipality_fg"]').empty();
        $('input[name="hidden_region"]').empty();
    //alert(provCode);
            $.ajax({
                method: 'GET',
                url: 'fargeneration/get_municipalities/' + provCode,
                data: {
                    _token: _token,
                    provCode: provCode
                },
                dataType: 'json',
                success: function (source) {
        $('select[name="municipality_fg"]').append('<option>--SELECT ASSIGNED MUNICIPALITY--</option>');
            $.each(source, function (i, d) {
                $('select[name="municipality_fg"]').append('<option value="' + d.citymunCode + '">' + d.citymunDesc + '</option>');
            }); 
        }
        }); //AJAX GET MUNICIPALITY


    $.ajax({
        method: 'GET',
        url: 'fargeneration/get_region/' + provCode,
        data: {
            _token: _token,
            provCode: provCode
        },
        dataType: 'json',
        success: function (source) {
            $('input[name="hidden_region"]').val(source.regDesc);
            
            }
        }); 
        HoldOn.close();
    });  //END PROVINCE SELECT


$('select[name="municipality_fg"]').on('change', function () {
    HoldOn.open(holdon_options);

    var regionName = $('input[name="hidden_region"]').val();
    var userId = $('input[name="userId"]').val();
    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var muniCode = document.getElementById("municipality_fg");
    var muniName = muniCode.options[muniCode.selectedIndex].text;
    //alert(muniName+" "+provName+" "+regionName);

            $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.history.list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        regionName: regionName,
                        provName: provName,
                        muniName: muniName,
                        userId: userId,
                    }
                },
                "columns":[
                    {"data": "batchTicketNumber"},
                     {"data": "dropOffPoint"},
                    {"data": "seedVariety"},
                    {"data": "totalBagCount"},
                    {"data": "dateCreated"},
                    {"data": "action", 'searchable': false }
                  
                ]
            });
    HoldOn.close();
});  //END MUNICIPALITY SELECT


    $('#deleteHistoryData').on('show.bs.modal', function (e) {
            var rowid = $(e.relatedTarget).data('rowid');
            var dop = $(e.relatedTarget).data('dop');
            var seed = $(e.relatedTarget).data('seed');
            var bcount = $(e.relatedTarget).data('bcount');
            
            //var municipality = $(e.relatedTarget).data('municipality');
           // alert(rowid + ' '+dop+ ' '+seed+' '+bcount);
            $("#hidden_id").val(rowid);
            $('label[id="modal_dop"]').text(dop);
            $('label[id="modal_seed"]').text(seed);
            $('label[id="modal_bcount"]').text(bcount);

        });


    

    $("#del_btn").on("click", function(e){
           var rowid = $("#hidden_id").val();
           alert(rowid);

                    
        //            "type": "POST",
         $.ajax({
        type: 'POST',
        url: "{{ route('process.history.cancel') }}",
        data: {
             "_token": "{{ csrf_token() }}",
             rowid: rowid
        },
        dataType: 'json',
        success: function (source) {
            alert("HISTORY DELETED");
        }
        }); 
    




           $("#deleteHistoryData").modal("hide");

    //REFRESH TABLE
      HoldOn.open(holdon_options);

    var regionName = $('input[name="hidden_region"]').val();
    var userId = $('input[name="userId"]').val();
    
    var provCode = document.getElementById("province_fg");
    var provName = provCode.options[provCode.selectedIndex].text;
    var muniCode = document.getElementById("municipality_fg");
    var muniName = muniCode.options[muniCode.selectedIndex].text;
    //alert(muniName+" "+provName+" "+regionName);

            $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('generate.history.list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        regionName: regionName,
                        provName: provName,
                        muniName: muniName,
                        userId: userId,
                    }
                },
                "columns":[
                    {"data": "batchTicketNumber"},
                     {"data": "dropOffPoint"},
                    {"data": "seedVariety"},
                    {"data": "totalBagCount"},
                    {"data": "dateCreated"},
                    {"data": "action", 'searchable': false }
                  
                ]
            });
    HoldOn.close();











        });






    </script>

@endpush