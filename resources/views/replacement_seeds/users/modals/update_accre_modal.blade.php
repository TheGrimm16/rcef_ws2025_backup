		<div id="update_accre_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.coopID') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="SG_name_update"></h4>
						</div>
						<div class="modal-body">
							<select name="seed_coop_update" id="seed_coop_update" class="form-control" style="width:100%;">
								
							</select>
							<input type="hidden" id="userID_update" name="userID_update" value="">
						</div>
						<div class="modal-footer">
							<input type="submit" role="submit" class="btn btn-default" value="Edit tagged Seed Cooperative">
						</div>
					</div>
				</form>
			</div>
		</div>