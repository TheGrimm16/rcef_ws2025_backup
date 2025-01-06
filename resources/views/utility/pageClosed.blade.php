@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Page Closed</h3>
            </div>
        </div>

            <div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> {{$mss}}
            </div>
        </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">
                                    
                                    <i class="fa fa-hand-paper-o" aria-hidden="true">  PAGE CLOSED!</i>

                                    <br>
                                
                                </label>
                                <label class="control-label col-md-12" style="text-align: center; font-size: 30px;">
                                    {{$mss}}
                                </label>

                            </div>




        </div>
    </div>
@endsection
@push('scripts')

    
@endpush