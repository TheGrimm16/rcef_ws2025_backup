@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>MOET APP REGISTATION</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Please use your updated and active email address.
            </div>
        </div>
        					<input type="hidden" name="hidden_region" id="hidden_region" >

        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Employee Id:</label>
                                <div class="col-md-2 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="emp_id" id="emp_id" placeholder="Employee Id">
                                </div>
       						</div>


                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">First Name:</label>
                                <div class="col-md-3 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="emp_first" id="emp_first" placeholder="First Name">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Middle Name:</label>
                                <div class="col-md-3 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="emp_mid" id="emp_mid" placeholder="Middle Name">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Last Name:</label>
                                <div class="col-md-3 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="emp_last" id="emp_last" placeholder="Last Name">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Email Address:</label>
                                <div class="col-md-3 col-sm-9 col-xs-9">
                                    <input type="email" class="form-control" name="emp_email" id="emp_email" placeholder="Email">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Position:</label>
                                <div class="col-md-3 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="emp_pos" id="emp_pos" placeholder="Position">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Office/Division:</label>
                                <div class="col-md-3 col-sm-9 col-xs-9">
                                    <input type="text" class="form-control" name="emp_division" id="emp_division" placeholder="Office/Division">
                                </div>
                            </div>
















                            <div class="form-group">
                                <div class="col-md-7">
                                    <button type="button" name="download_flsar_a3" id="download_flsar_a3" class="btn btn-lg btn-success" style="float: right;" disabled=""><i class="fa fa-file-pdf-o"></i> Download PDF (A3)</button>
                                </div>
                            </div>





       	</div>
    </div>
@endsection
@push('scripts')

@endpush