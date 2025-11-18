		<div id="assignModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.assign.coopID') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="SG_name"></h4>
						</div>
						<div class="modal-body">
							<select name="seed_coop" id="seed_coop" class="form-control" style="width:100%;" required>
								
							</select>
							<input type="hidden" id="userID" name="userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Save Accreditation Number">
						</div>
					</div>
				</form>
			</div>
		</div>