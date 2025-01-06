@extends('layouts.index')

@section('content')
<style>
	body{
        padding-right: 0px !important; 
    }
    .modal {
		overflow-y:auto;
	}
</style>
	<div>
		<div id="verifyModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
                    <div class="container" style="padding: 30px">
                        <div class="row">
                            <div class="col-md-12"><h1>Please enter user code</h1></div>
                            <div class="col-md-12">
                                <label for="usercode" class="control-label">User Code</label>
                                <input id="usercode" name="usercode" type="text" class="form-control" value="" required>
                                <div class="form-group row" style="margin-top: 20px">
                                    <button name="submit" type="button" class="btn btn-primary pull-right" id="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
					
				</div>
			</div>
		</div>		
	</div>
@endsection

@push('scripts')
	<script>
       $('#verifyModal').modal({
            backdrop: 'static',
            keyboard: false
        });
        $("#submit").click(function (e) { 
            e.preventDefault();
            var usercode = $("#usercode").val();
            window.location.href = "{{url('sed/farmers/')}}"+ "/" + usercode + "/all";
        });
	</script>
@endpush
