@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
  
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
            <div class="super_title">Upload Paid Farmers Excel</div>
            <div class="col-md-12">
                <form action="{{ route('ui.ebinhi.payment.upload') }}" method="POST" enctype="multipart/form-data" >
                <div class="form-group">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">


                  
                        
                        <input type="file" name="excel_file" id="excel_file" class="form-control">
                     


                </div>

                <div class="form-group">
       
                    <div class="col-md-12" style="text-align:center; margin-top:5px;">
                        <button type="submit" class="btn btn-md btn-success" style="width:150px;margin: 5px;" ><i class="fa fa-download" aria-hidden="true"></i> Upload Excel </button>
                    </div>

                </div>
                  
            </form>
            </div>
        

            </div>

        <!-- </div> -->

        
        <div class="main_table x_content form-horizontal form-label-left shadow-xl cp rounded">
        
            <div class="super_title">Excel Results</div>
            <div class="form-group cp">
                            <div class="x_content form-horizontal form-label-left">
                                        <table class="table table-hover table-striped table-bordered rounded" id="dataTBL">
                                        
                                            <thead>
                                                
                                                <th>Date Checked </th>
                                                <th >Excel Name</th>
                                                <th >Action</th>
                                        
                                            </thead>
                                            <tbody id='databody' >
                                                {{-- @foreach($files as $file)
                                                    <tr>
                                                        <td>{{$file["date_generated"]}}</td>
                                                        <td>{{$file["file_name"]}}</td>
                                                        <td>
                                                            <button value='{{$file["file_name"]}}'  class="btn btn-success btn-sm download_file"> <i class="fa fa-cloud-download" aria-hidden="true"> Download File</i> </button>

                                                        </td>
                                                        
                                                    </tr>
                                                @endforeach --}}
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
         $("#dataTBL").DataTable({
            "order": [],
            "pageLength": 10
             });
             
       
    </script>


@endpush