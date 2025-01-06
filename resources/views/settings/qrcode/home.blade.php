@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="clearfix"></div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                @include('layouts.message')
                
                <div class="x_panel">
                    <div class="x_content form-horizontal form-label-left">
                        <br/>
                        <div class="bs-example" data-example-id="simple-jumbotron">
                            
                            <div class="jumbotron">
                                <button id="edit_qr_btn" class="btn btn-default" style="position: absolute;right: 12px;top: 30px;" data-toggle="modal" data-target="#edit_qr_modal"><i class="fa fa-pencil"></i> Edit</button>
                                <h3 style="font-size: 41px;font-weight: 600;">Max QR Code: <span id="qr_count">{{$qr_code_max}} codes</span></h3>
                                <p>This value represents the total number of QR Codes that will be generated per batch that will be embedded in a PDF File available for download</p>
                            </div>
                        </div>
                    </div>
                </div><br>

                <div class="x_panel">
					<div class="x_title">
						<h2>Logs</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<table class="table table-striped table-bordered" id="log_tbl">
							<thead>
								<tr>
									<th style="width: 70%;">Description</th>
									<th style="width: 30%;">Date Recorded</th>
								</tr>
                            </thead>
                            <tbody>
                                @foreach ($qr_logs as $logs)
                                    <tr>
                                        <td>{{$logs->description}}</td>
                                        <td>{{date("F j, Y h:i:s A", strtotime($logs->date_recorded))}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
						</table>
					</div>
				</div>
                
            </div>

            <div class="modal fade" id="edit_qr_modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                            <h4 class="modal-title" id="myModalLabel">Maximum QR Codes</h4>
                        </div>
                        <form action="{{ route('system.settings.qrcode.update') }}" method="POST">
                            {!! csrf_field() !!}
                            <div class="modal-body">
                                <input type="number" class="form-control" id="qr_value" name="qr_value" value="{{$qr_code_max}}" placeholder="{{$qr_code_max}}">                        
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> Cancel</button>
                                <button type="submit" role="submit" class="btn btn-default"><i class="fa fa-pencil"></i> Edit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#log_tbl").DataTable();

    </script>
@endpush
