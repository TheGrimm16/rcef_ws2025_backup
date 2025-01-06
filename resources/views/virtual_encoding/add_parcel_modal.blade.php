<div class="modal fade" id="add_parcel_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" >        
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="width:150%;" >
        <div class="modal-header">
           <h2> Add new Parcel </h2>
        </div>
        <div class="modal-body" >
            <div class="row">
                <div class="col-md-2">
                    <label for="parcel_province">Province</label>
                </div>
                <div class="col-md-10">
                    <select name="parcel_province" id="parcel_province" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Province</option>
                        @foreach($provinces as $province)
                            <option value="{{$province->province}}">{{$province->province}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
             
            <div class="row">
                <div class="col-md-2">
                    <label for="parcel_municipality">Municipal</label>
                </div>
                <div class="col-md-10">
                  
                    <select name="parcel_municipality" id="parcel_municipality" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Municipality</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <label for="parcel_brgy">Brgy</label>
                </div>
                <div class="col-md-10">
                    
                    <select name="parcel_brgy" id="parcel_brgy" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Barangay</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <label for="parcel_final_area">Area</label>
                </div>
                <div class="col-md-10">
                    <input type='number' id="parcel_final_area" name="parcel_final_area" value="0.00" class="form-control" style="width:100%;">
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-success btn-md" id="add_parcel_now">Add</button>
            <button class="btn btn-danger btn-md" data-dismiss="modal">Cancel</button>
            
        </div>
      </div>
    </div>
  </div>
