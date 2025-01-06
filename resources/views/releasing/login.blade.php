@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="row" style="margin-top: 15%;">
		<div class="col-md-12">
            <div class="col-md-4">&nbsp;</div>
            <div class="col-md-4">
			</div>
            <div class="col-md-4"> &nbsp;</div>
			
		</div>

		<div class="col-md-12">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Select Distribution Location</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div>
					<div class="alert alert-danger" role="alert">
					  Reminder: This is for distribution of <strong>Transferred Wet Season 2020 Seeds</strong>
					</div>
                        <div class="form-horizontal form-label-left">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-9">
                                    <select class="form-control" name="distribution_province" id="distribution_province">
                                        <option></option>
                                        @foreach($provinces_list as $item)
                                            <option value="{{$item->province}}">{{$item->province}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9">
                                    <select class="form-control" name="distribution_municipality" id="distribution_municipality">
                                        <option></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Dropoff Point</label>
                                <div class="col-md-9">
                                    <select class="form-control" name="dropoff_point" id="dropoff_point">
                                        <option></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" style="font-size: 20px;">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                                <div class="col-md-9">
                                    <div class="radio">
                                        <label>
                                            <input type="checkbox" name="rsbsa_checking" id="rsbsa_checking" value="yes" class="flat" style="-webkit-transform: scale(1.3, 1.3);"> RSBSA Checking
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    <button type="button" name="button" id="submit_distribution_location" class="btn btn-lg btn-primary" style="float: right;"><i class="fa fa-sign-in"></i> Continue</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" value="{{Auth::user()->province}}" id="prv_id">
            <div class="col-md-4"></div>
         </div>
        </div>
    </div>
@endsection
