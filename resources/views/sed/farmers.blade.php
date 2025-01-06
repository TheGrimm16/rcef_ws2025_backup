@extends('layouts.index')

@section('content')
<style>
	body{
        padding-right: 0px !important; 
    }
    .modal {
		overflow-y:auto;
	}
	.active_collapse{
        background-color: #337ab7 !important;
        color: white !important;
    }

    .panel > .panel-heading2 {
        background-color:  #337ab7;
        color: white;
        border-bottom: 0;
        padding: 10px 15px;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
    }
</style>
	<div style="">
		<div class="page-title">
            <div class="title_left">
              <h3>Farmer Verification</h3>
            </div>
        </div>

        <div class="clearfix"></div>
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                @foreach($query as $d)
                <div class="panel panel-default" id="municipality_panels">
                    <div class="panel-heading" role="tab" id="head_{{$d->muni_code}}">
                        <div class="panel-title container-fluid">
                            <div class="row">
                                <div class="col-md-9">{{$d->province_name}} > {{$d->municipality_name}}</div>
                                <div class="col-md-2"></div>
                                <div class="col-md-1 text-right">
                                    <a role="button" class="collapseBtn" data-toggle="collapse" data-parent="#accordion"
                                        href="#{{$d->muni_code}}" aria-expanded="true" aria-controls="{{$d->muni_code}}"
                                        data-municode="{{$d->muni_code}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="{{$d->muni_code}}" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="head_{{$d->muni_code}}">
                        <div class="panel-body">
                            <div class="text-center">Loading <i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

		
		
	</div>
@endsection

@push('scripts')
	<script>
		
		
		$(".collapseBtn").on("click", function() {
			var municode = $(this).data('municode');
			let id = "#"+municode+" .panel-body";
			let heading_id  = "#head_" + municode;
			$(id).html('<div class="text-center">Loading <i class="fas fa-spinner"></i></div>');
			$.ajax({
				type: "POST",
				url: "{{url('sed/callers/dashboard')}}",
				data: {
					municode: municode,
					_token: "{{csrf_token()}}"
				},
				success: function(response) {  
					$(".panel-collapse .panel-body").empty();
					if (!$(heading_id).hasClass("active_collapse")) {
						$('#municipality_panels>.panel-heading').removeClass("active_collapse");
						$(heading_id).addClass("active_collapse");
					}else{
						$('#municipality_panels>.panel-heading').removeClass("active_collapse");
					}
					$(id).append(response);
				}
			});
		});
	</script>
@endpush
