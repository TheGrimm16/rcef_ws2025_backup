@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Paymaya Beneficiary CSV Parsing</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Check Data Alignment first before importing
            </div>
        </div>



            <form class="form-horizontal" method="POST" action="{{ route('upload.paymaya.import_process') }}">
            {{ csrf_field() }}
            
           
             <input type="text" name="withHeader" id="withHeader" value="{{$withHeader}}">


                <table class="table" border="1">
                    @foreach ($csv_data as $index => $row)
                        <tr>
                        @foreach ($row as $key => $value)
                             @if($index==0)
                             <th> {{ $value }} </th>
                             @else
                             <td>  {{ $value }} </td>
                             
                             @endif
                        @endforeach
                        </tr>
                    @endforeach
                  
                </table>

                <button type="submit" class="btn btn-primary" >
                    Import Data
                </button>
            </form>



        					
       	</div>
    </div>
@endsection
@push('scripts')

	<script type="text/javascript">
     
	</script>

@endpush