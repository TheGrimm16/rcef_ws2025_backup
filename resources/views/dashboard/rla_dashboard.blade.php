@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="page-title">
    <div class="title_left">
        <h3> RLA Monitoring Dashboard </h3>
    </div>



</div>

<div class="clearfix"></div>

<div class="x_panel">               
            <a class="btn btn-lg" style="background-color: #FF8000;" disabled> <font style="color:white;">Certification is more than 90 days </font> </a>
            <a class="btn btn-lg" style="background-color: #088A29;" disabled> <font style="color:white;">Duplicate RLA on Library </font> </a>
            <a class="btn btn-lg" style="background-color: red;" disabled> <font style="color:white;">RLA already used </font> </a>
</div>


<div class="x_panel">
            <div class="x_title">
                <h2>
                   Select Cooperative
                </h2>

                <select name="cooperatives_rla" id="cooperatives_rla" class="form-control">
                            <option value="all">All Cooperative</option>
                    @foreach($coop_list as $coop_list)
                        <option value="{{$coop_list->accreditation_no}}">{{$coop_list->coopName}}</option>
                    @endforeach
                </select>
                     


                         <div class="clearfix"></div>
        </div>
</div>


<div class="row" >
    <div class="col-md-12"  >

                 <table class="table table-hover table-striped table-bordered" id="dataTBL" width="100%">
                <thead>
                    <th >Seed Coop</th>
                    <th  >Seed Grower</th>
                    <th  >Accreditation #</th>
                    <th width="10%" >MOA #</th>
                    <th  >Laboratory #</th>
                    <th  >Lot #</th>
                    <th  >Certification Date</th>
                    <th  >Seed Variety</th>
                    <th  >Volume</th>
                    <th  >Action</th>
                    
                                                         
                </thead>
         
                
                <tbody id='databody'>
                </tbody>
            </table>

    </div>  
</div>




<div class="row">
    <div class="col-md-12">
                <center>
                    <div class="x_content form-horizontal form-label-left">
                        <div id="graph_container" style="width: auto; height: 1500px;"></div>
                    </div>
               </center>
    </div>    
</div>








<!-- DATA MODAL -->
<div id="info_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>DATA INFORMATION</span><br>
                </h4>
            </div>
            <div class="modal-body" id ="bodyModal">
            </div>
            <div class="modal-footer">      
            </div>
        </div>
    </div>
</div>
<!--DATA MODAL -->







@endsection
@push('scripts')
     <script src=" {{ asset('public/js/highcharts.js') }} "></script>
<script>
    
    function openDeleteModal($id){
        $("<div>").html(`
                        <span class="fw-700">Are you sure you want to delete this RLA?</span>
                        `).dialog({
                        title: "Confirm Deletion",
                        modal: true,
                        closeOnEscape: false,
                        buttons: {
                            Delete: function(){
                                deleteRLA($id);
                                $(this).dialog("close");
                            },
                            Cancel: function() {
                                $(this).dialog("close");
                            }
                        },
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                        }
                    });
    }

    function deleteRLA($id) {
        $.ajax({
            type: 'POST',
            url: "{{ route('coop.rla.confirmDeleteRLA') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id: $id
            },
            success: function(data) {
                if(data == 1)
                {
                    $("<div>").html(`
                        <span class="fw-700">RLA successfully deleted</span>
                    `).dialog({
                        title: "Notice",
                        modal: true,
                        closeOnEscape: false,
                        buttons: {
                            Close: function() {
                                $(this).dialog("close");
                                location.reload();
                            }
                        },
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        }
                    });
                }
            }
        });
    }


    function graph(){
        var accre = $("#cooperatives_rla").val();

        if(accre === "all"){
            $("#graph_container").removeAttr("style");
            $("#graph_container").attr("style", "width: auto; height: 1500px;");
        }else{
            $("#graph_container").removeAttr("style");
            $("#graph_container").attr("style", "width: auto; height: 300px;");
        }



        $.ajax({
                type: 'POST',
                url: "{{ route('rla.monitoring.graph') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    accre: accre,
                   // area_id: area_id
                },
                success: function(data){
                  //  alert(data);
               
                populateTransferChart(data);
                }
            });
  
    }



    graph();
    loadList();



                  //plot data to seed chart
        function populateTransferChart(chart_data){
            //alert(chart_data);
            Highcharts.chart('graph_container', {
                chart: {
                    backgroundColor: null,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'bar'
                },
                title: {
                    text: "Cooperatives RLA"
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: '',
                    pointFormat: '{point.name}: <b>{point.y:,.0f}</b>'
                },
                plotOptions: {
                    bar: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: <b>{point.y} </b>'
                        },
                        showInLegend: true
                    }
                },
              
                series: [{
                    showInLegend: false, 
                    colorByPoint: true,
                    data: chart_data
                }]
            });
        


        }


      

        $('#info_modal').on('show.bs.modal', function (e) { 
            var dat =  $(e.relatedTarget).data('type_modal');

            if(dat === "sg"){
            var coop_accre = $(e.relatedTarget).data('coop_accre');
            var coop_name = $(e.relatedTarget).data('coop');
            var sg_name = $(e.relatedTarget).data('full');

            $("#bodyModal").empty().append('<label  class="col-xs-3">Seed Grower: </label>');
            $("#bodyModal").append('<label>'+sg_name+'</label> <br>');
            $("#bodyModal").append('<label  class="col-xs-3">Coop Accreditation: </label>');
            $("#bodyModal").append('<label>'+coop_accre+'</label> <br>');
            $("#bodyModal").append('<label  class="col-xs-3">Coop Name: </label>');
            $("#bodyModal").append('<label>'+coop_name+'</label> <br>');
               
            }else if(dat == "coop"){
                var coop_accre = $(e.relatedTarget).data('coop_accre');
                var coop_name = $(e.relatedTarget).data('coop');
                var coop_moa = $(e.relatedTarget).data('moa');
            $("#bodyModal").empty().append('<label  class="col-xs-3">Coop Name: </label>');
            $("#bodyModal").append('<label>'+coop_name+'</label> <br>');
            $("#bodyModal").append('<label  class="col-xs-3">Coop Accreditation: </label>');
            $("#bodyModal").append('<label>'+coop_accre+'</label> <br>');
            $("#bodyModal").append('<label  class="col-xs-3">Coop MOA: </label>');
            $("#bodyModal").append('<label>'+coop_moa+'</label> <br>');
            }
              

        });



$("#stocks_tbl").DataTable({
     "searching": false,
});
        
    $('select[name="cooperatives_rla"]').on('change', function () {
        HoldOn.open(holdon_options);
            loadList();
            graph();
        HoldOn.close();
    });  //END MUNICIPALITY SELECT

    $("#dataTBL").DataTable();

        function loadList(){
           var accre = $("#cooperatives_rla").val();

                HoldOn.open(holdon_options);
                $('#dataTBL').DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{route('rla.monitoring.table')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",   
                            "accre" : accre
                        }
                    },
                    "columns":[
                        {"data": "coop_name"},
                        {"data": "sg_id"},
                        {"data": "coopAccreditation"},
                        {"data": "moaNumber"},
                        {"data": "labNo"},
                        {"data": "lotNo"},
                        {"data": "certificationDate"},
                        {"data": "seedVariety"},
                        {"data": "noOfBags"},
                        {"data": "action", 'searchable': false, 'orderable': false},
                           
                    ]
                });

            
            HoldOn.close();
            }
        
    

       





</script>
@endpush
