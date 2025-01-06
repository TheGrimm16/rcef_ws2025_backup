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
                <h2>DOP Transaction List</h2>
                 
                

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

                <select name="province_fg" id="province_fg" class="form-control">
                            <option value="0">Please select a Province</option>
                    <!-- READY FOR DS2021 -->
							@foreach ($municipal_list as $row)
								<option value="{{$row->province}}">{{$row->province}}</option>
							@endforeach
                </select>
                        <br>
                <select name="municipality_fg" id="municipality_fg" class="form-control">
                            <option value="0">Please select a Municipality</option>
                </select>
                        <br>
                <select name="dropOffPoint" id="dropOffPoint" class="form-control">
                            <option value="0">Please select a Drop off</option>
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
                DOP Transaction List
            </h2>
            
            
            <button class="btn btn-success btn-sm" style="float:right;" id="export_transfer_excel">
                Export to Excel
            </button>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <input type="text" style="float:right; width: 200px " class="form-control input-sm"  id="searchInput" onkeyup="searchFunction()" placeholder="Seach for Batch Number">
            
            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                <thead>
                    <th>Batch Ticket Number</th>
                    <th>Cooperative</th>
                    <th>Volume</th>
                    <th>Drop Off Point</th>
                    <th>Delivery Date</th>
                   <th>Action</th>
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
        $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 25
        });

     document.getElementById("export_transfer_excel").style.display="none";
     document.getElementById("searchInput").style.display="none";


    function searchFunction() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
         table = document.getElementById("dataTBL");
        tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                 td = tr[i].getElementsByTagName("td")[0];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                }
            }       
        }
    }




        $('select[name="province_fg"]').on('change', function () {
            HoldOn.open(holdon_options);
        
        var node = document.getElementById("databody");while (node.hasChildNodes()) {node.removeChild(node.lastChild);}
        var provCode = document.getElementById("province_fg");
        var provName = provCode.options[provCode.selectedIndex].text;
        
        if(provName==="Please select a Province"){
            document.getElementById("export_transfer_excel").style.display="none";
             document.getElementById("searchInput").style.display="none";
        }else{
            document.getElementById("export_transfer_excel").style.display="block";
             document.getElementById("searchInput").style.display="block";
        }


       
           var linkMunicipalities = 'pendingBatch/get_municipalities/' + provName ;
        
        $('select[name="municipality_fg"]').empty();
         $('select[name="dropOffPoint"]').empty();
          $('select[name="dropOffPoint"]').append('<option>--All Drop Off Point--</option>');
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
                 $('select[name="municipality_fg"]').append('<option>--ALL MUNICIPALITY--</option>');
                $.each(source, function (i, d) {
                    $('select[name="municipality_fg"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                });
     

                }
            }); //AJAX GET MUNICIPALITY

            var linkTable = "{{ route('generate.list.pending.province') }}";
               $('#dataTBL').DataTable().clear();
            $('#dataTBL').DataTable({
                "bDestroy": true,
                "autoWidth": true,
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
                    }
                },
                "columns":[
                    {"data": "batch_ticket", 'orderable': true},
                     {"data": "coop", 'searchable': false, 'orderable': false},
                    {"data": "volume", 'searchable': false, 'orderable': false},
                    {"data": "dropoff", 'searchable': false, 'orderable': false},
                    {"data": "delivery", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}
                ]});

       
        HoldOn.close();
    });  //END PROVINCE SELECT


$('select[name="municipality_fg"]').on('change', function () {
    HoldOn.open(holdon_options);
        var node = document.getElementById("databody");while (node.hasChildNodes()) {node.removeChild(node.lastChild);}
        var provCode = document.getElementById("province_fg");
        var provName = provCode.options[provCode.selectedIndex].text;
        var muniCode = document.getElementById("municipality_fg");
        var muniName = muniCode.options[muniCode.selectedIndex].text;
        
        
        document.getElementById("export_transfer_excel").style.display="block";
 document.getElementById("searchInput").style.display="block";
       
         $('select[name="dropOffPoint"]').empty();
         
    //alert(provCode);
            $.ajax({
                method: 'GET',
                url:'pendingBatch/get_dop/'+provName+'/'+muniName,
                data: {
                    _token: _token,
                    provName: provName,
                    muniName: muniName,
                },
                dataType: 'json',
                success: function (source) {
                    
                 $('select[name="dropOffPoint"]').append('<option>--All Drop Off Point--</option>');
                $.each(source, function (i, d) {
                    $('select[name="dropOffPoint"]').append('<option value="' + d.dropOffPoint + '">' + d.dropOffPoint + '</option>');
                });
     

                }
            }); //AJAX GET MUNICIPALITY     


               var linkTable = "{{ route('generate.list.pending.province.municipality') }}";
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
                        
                    }
                },
                "columns":[
                    {"data": "batch_ticket", 'orderable': true},
                     {"data": "coop", 'searchable': false, 'orderable': false},
                    {"data": "volume", 'searchable': false, 'orderable': false},
                    {"data": "dropoff", 'searchable': false, 'orderable': false},
                    {"data": "delivery", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}
                ]
            });








    HoldOn.close();
});  //END MUNICIPALITY SELECT

    $('select[name="dropOffPoint"]').on('change', function () {
    HoldOn.open(holdon_options);
        var node = document.getElementById("databody");while (node.hasChildNodes()) {node.removeChild(node.lastChild);}
        var provCode = document.getElementById("province_fg");
        var provName = provCode.options[provCode.selectedIndex].text;
        var muniCode = document.getElementById("municipality_fg");
        var muniName = muniCode.options[muniCode.selectedIndex].text;
        var dropOffCode = document.getElementById("dropOffPoint");
        var dropOffName = dropOffCode.options[dropOffCode.selectedIndex].text;
        
        
        document.getElementById("export_transfer_excel").style.display="block";
         document.getElementById("searchInput").style.display="block";
    
               var linkTable = "{{ route('generate.list.pending.province.municipality.dop') }}";
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
                        dropOffName: dropOffName,
                        
                    }
                },
                "columns":[
                    {"data": "batch_ticket", 'orderable': true},
                     {"data": "coop", 'searchable': false, 'orderable': false},
                    {"data": "volume", 'searchable': false, 'orderable': false},
                    {"data": "dropoff", 'searchable': false, 'orderable': false},
                    {"data": "delivery", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}
                ]
            });








    HoldOn.close();
});  //END MUNICIPALITY SELECT



        function cancelBatch(id){

var r = confirm("Delete?");
if (r == true) {
  var id = id;
    var linkProcess = "utility/cancel_delivery/process/"+id;
         $.ajax({
        type: 'GET',
        url: linkProcess,
        data: {
             "_token": "{{ csrf_token() }}",
             id: id, 
                
        },
        dataType: 'json',
        success: function (source) {
             alert("Batch Cancelled");
        





        var node = document.getElementById("databody");while (node.hasChildNodes()) {node.removeChild(node.lastChild);}
        var provCode = document.getElementById("province_fg");
        var provName = provCode.options[provCode.selectedIndex].text;
        var muniCode = document.getElementById("municipality_fg");
        var muniName = muniCode.options[muniCode.selectedIndex].text;
        var dropOffCode = document.getElementById("dropOffPoint");
        var dropOffName = dropOffCode.options[dropOffCode.selectedIndex].text;
        
        

        
        document.getElementById("export_transfer_excel").style.display="block";
         document.getElementById("searchInput").style.display="block";
    
            var linkTable = "{{ route('generate.list.pending.province.municipality.dop') }}";

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
                        dropOffName: dropOffName,
                        
                    }
                },
                "columns":[
                    {"data": "batch_ticket", 'orderable': true},
                     {"data": "coop", 'searchable': false, 'orderable': false},
                    {"data": "volume", 'searchable': false, 'orderable': false},
                    {"data": "dropoff", 'searchable': false, 'orderable': false},
                    {"data": "delivery", 'searchable': false, 'orderable': false},
                    {"data": "action", 'searchable': false , 'orderable': false}
                ]
            });

        }
        }); 
} else {
  return false;
} 





    
}











    $("#export_transfer_excel").on("click", function(e){
        var provCode = document.getElementById("province_fg");
        var provName = provCode.options[provCode.selectedIndex].text;
        var muniCode = document.getElementById("municipality_fg");
        var muniName = muniCode.options[muniCode.selectedIndex].text;
        var dropOffCode = document.getElementById("dropOffPoint");
        var dropOffName = dropOffCode.options[dropOffCode.selectedIndex].text;
        

//    alert(transfer_type+provName+muniName);


   window.open("pendingBatch/report/excel/"+provName+"/"+muniName+"/"+dropOffName, "_blank", 
        "toolbar=no,scrollbars=no,resizable=no,top=170,left=200,width=400,height=400");
   

        });









    </script>

@endpush