		<div id="changeInfo" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form action="{{ route('users.update.info') }}" method="POST" data-parsley-validate>
					{!! csrf_field() !!}
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title" id="">Information Update</h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-3">First Name</div>
								<div class="col-md-9">
									<input type="text" name="firstName" id="firstName" class="form-control" placeholder="First Name">
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">Middle Name</div>
								<div class="col-md-9">
									<input type="text" name="midName" id="midName" class="form-control" placeholder="Middle Name">
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">Last Name</div>
								<div class="col-md-9">
									<input type="text" name="lastName" id="lastName" class="form-control" placeholder="Last Name">
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">Extension Name</div>
								<div class="col-md-9">
									<input type="text" name="extName" id="extName" class="form-control" placeholder="Ext Name">
								</div>
							</div>

							
							
							
							<input type="hidden" id="info_userID" name="info_userID" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="submit" role="submit" class="btn btn-success" value="Update Information">
						</div>
					</div>
				</form>
			</div>
		</div>