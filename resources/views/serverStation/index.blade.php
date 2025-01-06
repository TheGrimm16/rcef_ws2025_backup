@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

    <style>
        .btn-success[disabled]{
            background: #26B99A;
            border: 1px solid #169F85;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="col-md-12">

            @foreach ($arrayData as $data)
           
            <div class="col-md-4">
				<div class="x_panel">
					<div class="x_title">
						<h2>Station name: {{$data['StationName']}}</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count" @if ($data['Status'] =="ONLINE")
                                style="color: green"
                                @else
                                style="color: red"
                                @endif><i class="fa fa-users"></i> {{$data['Status']}}</div>
							</div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                         <i class="fa fa-cubes"> <a href="//{{$data['ServerAddress']}}" target="_blank">Site</a>  </i> </div>
                                    </div>

                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                          <i class="fa fa-cube">  Code: {{$data['code']}} </i></div>
                                    </div>
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                            <i class="fa fa-brands fa-github"><a href="#" class="gitAction" data-link="{{$data['ServerAddress']."/rcef_station/git-pull-ds24"}}"> Git pull</a> </i> </div>
                                    </div>
                                   <!--  @if ($data['StationName'] == "CES")
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                            <i class="fa fa-brands fa-github"><a href="#" data-link="{{$data['ServerAddress']."/rcef_station/git-push"}}" class="gitAction"> Git push</a> </i> </div>
                                    </div>
                                    @endif -->
                                </div>
                            </div>
						</div>
					</div>
				</div>
		    </div>
       
            @endforeach
			
    </div>

@endsection()

@push('scripts')
@endpush

@push('scripts')
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script src=" {{ asset('public/js/select2.min.js') }} "></script>
<script src=" {{ asset('public/js/parsely.js') }} "></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

<script>
   
    $('.gitAction').click(function(){
        var link = $(this).attr("data-link");        
        $.ajax({
            type: 'GET',
            url: "//"+link,
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){		  
                alert(data);
            }
        });


    });


    
</script>
@endpush