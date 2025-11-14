		<div id="reset_password_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.reset') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Reset Password</h4>
						</div>
						<div class="modal-body">
							<input type="text" class="form-control" name="reset_pass" id="reset_pass" value="P@ssw0rd" required>
							<button class="btn btn-info" style="margin-top:5px;" onclick="generateRandomPass(event)"><i class="fa fa-power-off"></i> GENERATE RANDOM PASSWORD (6-CHARACTERS)</button>
										
							</select>
							<input type="hidden" id="userID_reset" name="userID_reset" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="RESET PASSWORD">
						</div>
					</div>
				</form>
			</div>
		</div>