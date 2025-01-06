@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="text" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Paymaya Beneficiary Upload</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Same Beneficiary ID will update the data and paymaya code may change
            </div>
        </div>

        @if($succ != "")

        <div class="row">
           <?php echo $succ; ?>
        </div>
        @endif



        					 <div class="container">
                                <div class="row">
                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">CSV Import</div>

                                            <div class="panel-body">
                                                <form class="form-horizontal" method="POST" action="{{route('upload.paymaya.import_parse')}}" onsubmit="confirm('upload?');"   enctype="multipart/form-data">
                                                    {{ csrf_field() }}

                                                    <div class="form-group{{ $errors->has('csv_file') ? ' has-error' : '' }}">
                                                        <label for="csv_file" class="col-md-4 control-label">CSV file to import</label>

                                                        <div class="col-md-6">
                                                            <input id="csv_file" type="file" class="form-control" name="csv_file" required >

                                                            @if ($errors->has('csv_file'))
                                                                <span class="help-block">
                                                                <strong>{{ $errors->first('csv_file') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-md-6 col-md-offset-4">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input type="checkbox" name="header" id="header" checked> File contains header row?
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-md-4 col-md-offset-4">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <a href="{{dirname($_SERVER['PHP_SELF']).'/public/templates/tbl_beneficiaries.csv'}}" class="btn-dark btn-xs" style="" >Download CSV Format</a>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <div class="col-md-8 col-md-offset-4">
                                                            <button type="submit" id="uploadCsv" name="uploadCsv" class="btn btn-primary" >
                                                                Parse CSV
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
       
       	</div>
    </div>
@endsection
@push('scripts')


@endpush