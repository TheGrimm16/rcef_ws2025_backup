@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Database Checker</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            <!--
        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Please avoid processing large amount of rows. <b><u>[ Maximum of 1000 rows per process ]</u></b> this is to eliminate or minimize loading time.
            </div>
        </div> -->

             <div class="x_panel">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Select Table</label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <select name="cmbTable" id="cmbTable" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a Table</option>

                                        @foreach ($db_list as $db_list)
                                                <option value="{{ $db_list->TABLE_NAME }}">{{ $db_list->TABLE_NAME}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

               </div>       

               <div class="x_panel">
                    <div class="x_content form-horizontal form-label-left">
                        <div id="tbl_view"> 
                            <table class="table table-hover table-striped table-bordered" id="dataTBL">
                                <thead id = "tbl_head">
                                  
                                    <th>Please Select Table to view Data</th>
                                
                                </thead>
                                <tbody id='databody'>
                                </tbody>
                            </table>
                        </div>
                    </div>



               </div>



       	</div>
    </div>





@endsection
@push('scripts')

	<script type="text/javascript">



         $('select[name="cmbTable"]').on('change', function () {
            HoldOn.open("sk-cube-grid");
            var table = $('select[name="cmbTable"]').val();
           
                $.ajax({
                        method: 'POST',
                        url: "{{route('moet.get.field_column')}}",
                        data: {
                            _token: _token,
                            table: table,
                        },
                        dataType: 'json',
                        success: function (source) {
                            var tbl = '<table class="table table-hover table-striped table-bordered" id="dataTBL"> <thead id = "tbl_head">';
                            var column = source["data"];



                            $.each(source["field"], function (i, d) {
                                 tbl= tbl+ "<th>";
                                    tbl = tbl+d.COLUMN_NAME;
                                tbl = tbl+"</th>";
                            }); 

                            tbl = tbl+" </thead> <tbody id='databody'></tbody></table>";

                                $("#tbl_view").empty().append(tbl);

                                loadTable(column)
                            HoldOn.close();
                        }
                }); //AJAX GET MUNICIPALITY 


            HoldOn.close();
            });  //END MUNICIPALITY SELECT


     



        function loadTable(column){
            var table = $('select[name="cmbTable"]').val();

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
                    "url": "{{route('moet.load.db_table')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        table: table,
                        
                    }
                },
                "columns":column 
            });

        }




        $('select[name="cmbTable"]').select2();
    
        



	</script>

@endpush