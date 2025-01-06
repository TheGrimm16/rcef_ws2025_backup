<style>
    .help-block.text-danger{
        color: #D9534F;
        font-style:italic;
    }
</style>
<form class="form-horizontal" id="verificationForm">
    <input type="hidden" name="_token" value="{{csrf_token()}}" />
    <input type="hidden" name="farmerid" value="{{$farmer->sed_id or ''}}" />
    <input type="hidden" name="prv_code" value="{{$farmer->prv_code or ''}}" />
    <input type="hidden" name="muni_code" value="{{$farmer->muni_code or ''}}" />
    <input type="hidden" name="contact_number" value="{{$farmer->contact_no or ''}}" />
    <div class="container" style="padding: 30px">
        <div class="row">
            <div class="col-12">
                <h4 style=" margin-bottom:20px">Are you sure?</h4>
            </div>
        </div>
        @if($farmer->enableEdit == 1)
        <div class="row">
            <div class="col-12">
                <!-- <div class="alert alert-info" role="alert"><b>REMARKS: </b>{{$farmer->edit_remarks}}</div> -->
            </div>
        </div>
        @endif
        <!-- <div class="form-group row">
            <div class="col-sm-7"> -->
                <!-- <label class="control-label">RSBSA Number: </label>  <br/> -->
                <!-- <label class="control-label">Farmer Name: </label> {{$farmer->fname." ".$farmer->midname." ".$farmer->lname }}<br/>
                <label class="control-label">Mother's Maiden Name: </label> {{$farmer->mother_fname." ".$farmer->mother_mname." ".$farmer->mother_lname }}<br/>
            </div>
            <div class="col-sm-5">                
                <label class="control-label">Province: </label> {{$farmer->provDesc }}<br/>
                <label class="control-label">Municipality: </label> {{$farmer->citymunDesc }}<br/>
            </div>
        </div> -->
        <!-- <div class="form-group row">
            <div class="col-12">
                <label for="contactno" class="control-label">Contact Number <strong style="color:red">*</strong></label>
                <input id="contactno" name="contactno" type="number" class="form-control" required value="{{$farmer->contact_no}}">
            </div>
        </div> -->
        <div class="form-group row">
            <!-- <div class="col-md-6">
                <label for="farm_area1" class="control-label">Actual Area (ha)</label>
                <div id="farm_area1"  style="margin-top:10px" >{{$farmer->farm_area_ws2021}}</div>
                <input type="hidden" name="actual_area" value="{{$farmer->farm_area_ws2021}}">
            </div> -->
            <!-- <div class="col-md-6">
                <label for="farm_area" class="control-label">Farm Area (ha)<strong style="color:red">*</strong></label>
                <input id="farm_area" name="farm_area" type="number" class="form-control" step="any" value="{{($farmer->committed_area == 0)? $farmer->farm_area_ws2021 : $farmer->committed_area }}" required>
            </div>    -->
        </div>
        <!-- <div class="form-group row">
            <div class="col-12">
                <label for="contactno" class="control-label">Yield (Wet Season 2021) </label>
            </div>
            <div class="col-sm-4">
                <label for="noofbags" class="control-label"># of Bags</label>
                <input id="noofbags" name="noofbags" type="number" class="form-control" value="{{($farmer->yield_no_bags == 0)?'' : $farmer->yield_no_bags }}" >
            </div>
            <div class="col-sm-4">
                <label for="weightofbags" class="control-label">ave. Weight/bag (kg)</label>
                <input id="weightofbags" name="weightofbags" type="number" class="form-control" value="{{($farmer->yield_weight_bags == 0)?'' : $farmer->yield_weight_bags }}" >
            </div>
            <div class="col-sm-4">
                <label for="areaharvested" class="control-label">Area Harvested (ha)</label>
                <input id="areaharvested" name="areaharvested" type="number" class="form-control" value="{{($farmer->yield_area == 0)?'' : $farmer->yield_area }}" >
            </div>
        </div> -->
        <!-- <div class="form-group row">
            <div class="col-sm-3">
                <label for="radio" class="control-label text-right">Variety Planted</label> 
            </div>
            <div class="col-sm-9">
                <label class="radio-inline">
                    <input type="radio" name="variety_planted" value="" checked>
                        none
                </label>
                <?php
                    $inbred = "";
                    $hybrid = "";
                    if($farmer->planted_variety == "inbred"){
                        $inbred = "checked";
                    }else if($farmer->planted_variety == "hybrid"){
                        $hybrid = "checked";
                    }
                ?>
                <label class="radio-inline">
                    <input type="radio" name="variety_planted" value="inbred" {{$inbred}}>
                        Inbred
                </label>
                <label class="radio-inline">
                    <input type="radio" name="variety_planted" value="hybrid" {{$hybrid}}>
                        Hybrid
                </label>
            </div>
        </div> -->
        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right" id="verifiedFarmerSave">Yes</button>
            @if($farmer->status == 2)
            <button class="btn btn-light pull-right" id="dismissModal">close</button>
            @endif
            @if($farmer->status == 4)
            <button class="btn btn-warning pull-right" id="cancelTrans">Cancel</button>
            @endif
        </div>
    </div>
</form>
<script>
    $("#variety1").select2({
        width: 'resolve'
    });
    $("#variety2").select2({
        width: 'resolve'
    });
    $("#dismissModal").on("click", function (e) {
        e.preventDefault();
        $('#verifyModal').modal('hide');
    });


    $("#verifiedFarmerSave").on("click", function (e) {
        e.preventDefault();
        // $('#verifyModal').modal('hide');
        var answer = window.confirm("Save data?");
        if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sed/verification/form/save/no')}}",
                data: $("#verificationForm").serialize(),
                success: function (response) {
                    
                    if(response.status == 4){
                        var arr = response.message;
                        let id = "";
                        $('.help-block.text-danger').empty();
                        for (let i = 0; i < arr.length; i++) {
                            id = "#" + arr[i].key;
                            // $("'"+id+"'").siblings('.help-block').empty();
                            $(id).after( '<span class="help-block text-danger"><small>'+ arr[i].value +'</small></span>' );
                        }
                    }else{
                        // alert(response.message);
                        $('#verifyModal').modal('hide');
                    }
                    
                }
            });
        }
        else {
            //some code
        }  
    });

    $("#cancelTrans").on("click", function (e) {
        e.preventDefault();
        var answer = window.confirm("Cancel transaction?");
        if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sed/verification/form/cancel')}}",
                data: {
                    verified_id: "{{$farmer->sed_id}}",
                    _token: "{{csrf_token()}}"
                },
                success: function (response) {
                    alert(response.message);
                    $('#verifyModal').modal('hide');
                }
            });
        }
        else {
            //some code
        }  
    });


</script>