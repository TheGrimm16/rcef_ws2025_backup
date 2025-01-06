<div id="farm_performance_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Farm Performance</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal form-label-left">
                    <audio id="qr_audio">
                        <source src="{{asset('public/sounds/Beep.mp3')}}" type="audio/mpeg">
                    </audio>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">QR Code</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="qr_code" id="qr_code" placeholder="QR Code">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <video id="preview" style="width: 100%;"></video>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Area Planted (ha)</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="area_planted" id="area_planted" placeholder="Area Planted">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety Used</label>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <select name="variety_used_prefix" id="variety_used_prefix" class="form-control">
                                <option value="NSIC Rc">NSIC Rc</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="PSB Rc">PSB Rc</option>
                                <option value="IR">IR</option>
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-5 col-xs-5">
                            <input type="text" class="form-control" name="variety_used" id="variety_used" placeholder="Variety used last season">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Seed Usage</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="seed_usage" id="seed_usage" placeholder="Used seeds last season">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Yield</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="yield" id="yield" placeholder="Yield / Harvest">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-success" id="save_farm_performance"><i class="fa fa-check"></i> Save</button>
            </div>
        </div>

    </div>
</div>
