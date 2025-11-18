<div class="modal fade" id="createRequestModal" tabindex="-1" role="dialog" aria-labelledby="createRequestLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="createRequestLabel">New Seed Replacement Request</h4>
            </div>

            <form id="createRequestForm" method="POST">
                {{-- CSRF token --}}
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="modal-body">
                    <div id="modal-errors" class="alert alert-danger" style="display: none;"></div>

                    <div class="form-group">
                        <label>User ID</label>
                        <input type="number" name="user_id" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>New Released ID</label>
                        <input type="number" name="new_released_id" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Geo Code</label>
                        <input type="text" name="geo_code" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Purpose ID</label>
                        <input type="number" name="purpose_id" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Attachment Directory</label>
                        <input type="text" name="attachment_dir" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Request</button>
                </div>
            </form>

        </div>
    </div>
</div>
