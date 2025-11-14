		<div id="assignProvince" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.province') }}" method="POST" data-parsley-validate>
				{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Change Tagged Address</h4>
						</div>
						<div class="modal-body">
							Select Province:
							<select name="changeProvince" id="changeProvince" class="form-control" style="width:100%;" required>
								
							</select>
							<input type="hidden" id="prv_userID" name="prv_userID" value="">

							Select Municipaliy:
							<select name="changeMunicipality" id="changeMunicipality" class="form-control" style="width:100%;" required>
								<option value="0">Select a Municipality</option>
							</select>
						</div>
						


						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Update Location">
						</div>
					</div>
				</form>
			</div>
		</div>