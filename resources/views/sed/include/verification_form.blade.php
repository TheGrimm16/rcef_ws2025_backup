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
    <?php
        $forEdit = "";
        if($farmer->enableEdit == 1){
            $forEdit = "display: none !important;";
        }
    ?>
    <div class="container" style="padding: 30px">
        <div class="row">
            <div class="col-12">
                <h4 style=" margin-bottom:20px">VERIFY FARMER</h4>
            </div>
        </div>
        @if($farmer->enableEdit == 1)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info" role="alert"><b>REMARKS: </b>{{$farmer->edit_remarks}}</div>
            </div>
        </div>
        @endif
        <div class="form-group row">
            <div class="col-sm-7">
                <!-- <label class="control-label">RSBSA Number: </label>  <br/> -->
                <label class="control-label">Farmer Name: </label> {{$farmer->fname." ".$farmer->midname." ".$farmer->lname }}<br/>
                <label class="control-label">Mother's Maiden Name: </label> {{$farmer->mother_fname." ".$farmer->mother_mname." ".$farmer->mother_lname }}<br/>
            </div>
            <div class="col-sm-5">                
                <label class="control-label">Province: </label> {{$farmer->provDesc }}<br/>
                <label class="control-label">Municipality: </label> {{$farmer->citymunDesc }}<br/>
            </div>
        </div>
        <div class="form-group row" style="{{$forEdit}}">
            <div class="col-12">
                <label for="contactno" class="control-label">Contact Number <strong style="color:red">*</strong><small> <i> valid format ex. 09586545826 ( <b>09</b> following by <b>9</b> digit number )</i></small></label>
                <input id="contactno" name="contactno" type="number" class="form-control" required value="{{$farmer->contact_no}}">
            </div>
        </div>
         <div class="form-group row" >
            <div class="col-md-8">
                <label for="" class="control-label">RSBSA Enrolled Area (ha)</label>
                <div class="row">
                    <div class="col-md-6">
                        <label for="farm_area1" class="control-label">Wet Season</label>
                        <div id="farm_area1"  style="margin-top:10px;padding-left:10px" >{{$farmer->farm_area_ws2021}}</div>
                        <!-- <input type="hidden" name="actual_area" value="{{$farmer->farm_area_ws2021}}"> -->
                    </div>
                    <div class="col-md-6">
                        <label for="farm_area2" class="control-label">Dry Season</label>
                        <div id="farm_area2"  style="margin-top:10px;padding-left:10px" >{{$farmer->farm_area_ds2021}}</div>
                        <!-- <input type="hidden" name="actual_area" value="{{$farmer->farm_area_ds2021}}"> -->
                    </div>
                </div> 
            </div>
            <div class="col-md-4" style="{{$forEdit}}">
                <label for="farm_area" class="control-label">Farm Area (ha)<strong style="color:red">*</strong></label>
                <input id="farm_area" name="farm_area" type="number" class="form-control" step="any" value="{{($farmer->committed_area == 0)? 0 : $farmer->committed_area }}" required>
            </div>   
        </div>
        <!--<div class="form-group row">
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
        <div class="form-group row">
            <div class="col-12">
                <label for="sowingdate" class="control-label">Expected Date of Sowing for the Incoming Season<strong style="color:red">*</strong></label>
            </div>
            <div class="col-sm-6">
                <label for="sowing_month" class="control-label">Month</label>
                <select id="sowing_month" name="sowing_month" class="select form-control" style="width: 100% !important" required>
                    <option value="" disabled selected>Sowing Month</option>
                    <option value=""></option>
                    @foreach($sowing_month as $sm)
                    <?php 
                        $sowing_month_s = "";

                        if(isset($farmer->sowing_month)){
                            if($farmer->sowing_month == $sm->season_month){
                                $sowing_month_s = "selected";
                            }
                        }
                    ?>
                    <option value="{{$sm->season_month}}" {{$sowing_month_s}}>{{$sm->season_month}} {{$sm->season_year}}</option>
                    @endforeach
                    @if($farmer->sowing_month === '0')
                    <!-- <option value="0" selected>Next Season</option> -->
                    @else
                    <!-- <option value="0" >Next Season</option> -->
                    @endif
                </select>
            </div>
            <div class="col-sm-6">
                <label for="sowing_week" class="control-label">Week</label>
                <select id="sowing_week" name="sowing_week" class="select form-control" style="width: 100% !important" required>
                    <option value="" disabled selected>Sowing Week</option>
                    <option value=""></option>
                    @foreach($sowing_week as $sw)
                    <?php 
                        $sowing_week_s = "";

                        if(isset($farmer->sowing_week)){
                            if($farmer->sowing_week == $sw->season_week){
                                $sowing_week_s = "selected";
                            }
                        }
                    ?>
                    <option value="{{$sw->season_week}}" {{$sowing_week_s}}>{{$sw->season_week}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row" style="{{$forEdit}}">
            <div class="col-12">
                <label for="sex" class="control-label">Sex</label>
                <select id="sex" name="sex" class="select form-control">
                    
                    <?php 
                    $male = "";
                    $female = "";

                    if(isset($farmer->ver_sex)){
                        if(strtolower($farmer->ver_sex) == "male"){
                            $male = "selected";
                        }else if(strtolower($farmer->ver_sex) == "female"){
                            $female = "selected";
                        }
                    }
                    
                     ?>
                    <option value="" disabled selected>Sex</option>
                    <option value="male" {{$male}}>Male</option>
                    <option value="female"  {{$female}}>Female</option>
                </select>
            </div>
        </div>
        <div class="form-group row" style="{{$forEdit}}">
            <div class="col-12">
                <label for="variety1" class="control-label">Preferred Seed Variety 1 <strong style="color:red">*</strong></label>
                <select id="variety1" name="variety1" class="js-example-basic-single js-states select form-control" style="width: 100% !important">
                    <option value="" disabled selected>Select Variety</option>
                    @foreach($variety as $kv1 => $v1)
                    <?php 
                        $selected_v1 = "";

                        if(isset($farmer->preffered_variety1)){
                            if($farmer->preffered_variety1 == $kv1){
                                $selected_v1 = "selected";
                            }
                        }
                    ?>
                    <option value="{{$kv1}}" {{$selected_v1}}>{{$kv1}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row" style="{{$forEdit}}">
            <div class="col-12">
                <label for="variety2" class="control-label">Preferred Seed Variety 2</label>
                <select id="variety2" name="variety2" class="js-example-basic-single js-states select form-control" style="width: 100% !important">
                    <option value="" disabled selected>Select Variety</option>
                    @foreach($variety as $kv2 => $v2)
                    <?php 
                        $selected_v2 = "";

                        if(isset($farmer->preffered_variety2)){
                            if($farmer->preffered_variety2 == $kv2){
                                $selected_v2 = "selected";
                            }
                        }
                    ?>
                    <option value="{{$kv2}}" {{$selected_v2}}>{{$kv2}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- <div class="form-group row">
            <div class="col-12">
                <label for="participate" class="control-label">Is the Farmer willing to participate in e-Binhi Phase
                    III<strong style="color:red">*</strong></label>
                <select id="participate" name="participate" class="select form-control" required>
                <?php 
                    $yes = "";
                    $no = "";

                    if(isset($farmer->isParticipating)){
                        if(strtolower($farmer->isParticipating) == "1"){
                            $yes = "selected";
                        }else if(strtolower($farmer->isParticipating) == "0"){
                            $no = "selected";
                        }
                    }
                    
                ?>
                    <option value="" disabled selected>Yes/No</option>
                    <option value="1" {{$yes}}>Yes</option>
                    <option value="0" {{$no}}>No</option>
                </select>
            </div>
        </div> -->
        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right" id="verifiedFarmerSave">Save</button>
            @if($farmer->status != 4)
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
                url: "{{url('sed/verification/form/save')}}",
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
                        alert(response.message);
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

    $("#sowing_month").on('change', function() {
        if ($(this).val() == "") {
            $("#sowing_week").prop("disabled", true);
            $("#sowing_week").val("");
        } else {

            $.ajax({
                type: "POST",
                url: "{{url('sed/season/weeks')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    month: $(this).val()
                },
                success: function(response) {
                    $("#sowing_week").prop("disabled", false);
                    obj = JSON.parse(response);

                    $('#sowing_week').empty();
                    $('#sowing_week').append($('<option>').val("").text(""));
                    obj.forEach(data => {
                        $('#sowing_week').append($('<option>').val(data.season_week).text(
                            data.season_week));
                    });
                }
            });
        }

    });

</script>