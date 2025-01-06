@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap');

        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .mother_content{
            overflow-y: hidden;
            background: white!important;
        }

        .rounded{
            border-radius: 1em;
            background: white;
        }

        .cp{
            padding: 3em 1em;
        }

        label{
            color: black;
        }
        
        th{
            color: black;
        }

        ._main_container{
            background: white;
            font-family: "DM Sans";
            display: grid;
            gap: 1em;
            grid-template-areas:
            'one two'!important;
            grid-template-columns: 1fr 3fr;
            grid-template-rows: 1fr;
            position: relative;
            height: calc(100vh - 150px)!important;
            /* max-height: calc(100vh - 150px)!important; */
        }

        .selectors{
            grid-area: one;
            height: max-content;
        }

        .main_table{
            grid-area: two;
            height: 95%;
            overflow-y: auto;
        }

        #databody{
            max-height: calc(100vh - 100px)!important;
            font-size: 0.9em;
        }

        .prvSel{
            border-radius: 1e;
        }

        .super_title{
            font-size: 2em;
            color: black;
            font-weight: 700;
        }

        input{
            border-radius: 1em!important;
        }

        input:focus, input:active{
            border: 2px black solid;
            background: #00000010;
        }

        #search{
            border-radius: 2em;
            outline: green 1px solid;
            background-color: white;
            color: green;
            font-weight: 700;
        }
    </style>
    <div class="_main_container">
        <div class="shadow-xl cp rounded selectors">
            <div class="super_title">Export Excel Municipal Level</div>
            <div class="col-md-12">
                <div class="form-group">
                   


                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-12"> 
                            <label for="date_1" id="label_rsbsa">Date From  </label>
                            <input type="date" id="date_1" class="form-control" name="date_1" >
                        </div>
                        <div class="col-md-12"> 
                            <label for="date_2" id="label_rsbsa">Date To  </label>
                            <input type="date" id="date_2" class="form-control" name="date_2" >
                        </div>
                    </div>
          


                </div>

                <div class="form-group">
       
                    <div class="col-md-12" style="text-align:center; margin-top:5px;">
                        <button type="button" name="download" id="download" class="btn btn-md btn-success" style="width:150px;margin: 5px;" ><i class="fa fa-download" aria-hidden="true"></i> Download Excel </button>
                    </div>

                </div>
            </div>
        

            </div>

        <!-- </div> -->

        
        <div class="main_table x_content form-horizontal form-label-left shadow-xl cp rounded">
            
                <div class="super_title">Available Excels for Download</div>
                <div class="form-group cp">
                                <div class="x_content form-horizontal form-label-left">
                                            <table class="table table-hover table-striped table-bordered rounded" id="dataTBL">
                                            
                                                <thead>
                                                    
                                                    <th>Date Generated </th>
                                                    <th > Excel Name</th>
                                                    <th > Action</th>
                                            
                                                </thead>
                                                <tbody id='databody' >
                                                    
                                                        <tr>
                                                            <td></td>
                                                            
                                                            <td></td>
                                                            
                                                            <td>
                                                                
                                                                

                                                            </td>
                                                            
                                                        </tr>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                </div>
    </div>
</div>
   




@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

         window.onload = function () {
            $("#dataTBL").DataTable().clear();
            $("#dataTBL").DataTable({
            bDestroy: true,
            autoWidth: false,
            searchHighlight: true,
            processing: true,
            serverSide: true,
            orderMulti: true,
            order: [],
            ajax: {
                url: "{{ route('ui.export.municipal.getFiles') }}",
                dataType: "json",
                type: "GET",
                data: {
                _token: "{{ csrf_token() }}",
                },
            },
            columns: [
                { data: "date_generated" },
                { data: "file_name" },
                { data: "action" },
            ],
            });
        };
             
        $(".download_file").on("click", function(){
            var file_name = $(this).val();
            var SITE_URL = "{{url('/')}}";
            window.open(SITE_URL+"/public/reports/excel_export/"+file_name, "_blank");


        });


        $("#download").on("click", function(){
            var date_1 = $("#date_1").val();
            var date_2 = $("#date_2").val();

            if(date_1 == "" || date_2 == ""){
                Swal.fire({   icon: 'error',    title: 'Oops...',  text: 'Please Select Date Range!',  });
                return;
            }

            var SITE_URL = "{{url('/')}}";

                    Swal.fire({
                        title: "Generate Excel File",
                        text: "Please note that even the process stop, the process is still running, Avoid Re Running Generation",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Generate",
                    }).then(function(result) {
                        if (result.value) {
                     
                            window.open(SITE_URL+"/report/export/municipal/statistics/"+date_1+"/"+date_2+"/all", "_blank");
                        }
                    });


        

        });
    </script>


@endpush