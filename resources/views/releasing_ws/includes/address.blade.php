<div id="farm_address_confirmation_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Farm Address Confirmation/Update</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal form-label-left">
                    <input type="hidden" id="confirm_region" value="">

                    <div class="form-group">
                        <label class="control-label col-md-3">Province</label>
                        <div class="col-md-9">
                            <select class="form-control" name="confirm_province" id="confirm_province"></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">Municipality</label>
                        <div class="col-md-9">
                            <select class="form-control" name="confirm_province" id="confirm_municipality"></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">Barangay</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="confirm_barangay" id="confirm_barangay">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">Area (ha)</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="confirm_area" id="confirm_area">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-lg btn-success" id="save_new_address"><i class="fa fa-check"></i> Save New Farm Address</button>
            </div>
        </div>

    </div>
</div>
