		<div id="changeRole" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.role') }}" method="POST" data-parsley-validate>
					{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Change Role</h4>
						</div>
						<div class="modal-body">
							
							<b>Current Role: </b><span id="currentRole"></span><br>
							Select Role:
							<select name="changeRoleSelect" id="changeRoleSelect" class="form-control" style="width:100%;" required>
								<option value="">Select Role</option>
								@foreach ($roles as $k => $r)
									<option value="{{$k}}">{{$r}}</option>
								@endforeach
							</select>
							
							<input type="hidden" id="role_userID" name="role_userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Change Role">
						</div>
					</div>
				</form>
			</div>
		</div>