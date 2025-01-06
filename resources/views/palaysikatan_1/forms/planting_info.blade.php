@extends('layouts.index')

@section('content')
{{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
<style>
.trans-label{
    font-weight: bolder;
}
.form-label {
    font-weight: bold;
    padding-top: 30px !important;
}

.form-label-2 {
    font-weight: bold;
    padding-top: 30px !important;
}

.control-label {
    font-weight: normal;
}

#submitForm {
    margin: 20px;
}

#top_info{
    font-size: 15px;
    font-weight: bold;
}

.fnt{
    color: black;
}


</style> 
      
        <link rel="stylesheet" href="{{asset('public/css/step_master/jquery.steps.css')}}" />
<div class="fnt">
    <div class="page-title">
        <div class="title_left">


            <h3>
                @if(isset($crop_establishment->input_desc))
                    {{$crop_establishment->input_desc }} Form
                @endif
            </h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <div class="row">
                <div class="col-sm-4 trans-label">NAME:</div>
                <div class="col-sm-8" id="top_info">{{$farmer->f_full_name}}</div>
            </div>
            <div class="row">
                <div class="col-sm-4 trans-label">ADDRESS:</div>
                <div class="col-sm-8" id="top_info">{{$address}}</div>
            </div>
            <div class="row">
                <div class="col-sm-4 trans-label">CONTACT #:</div>
                <div class="col-sm-8" id="top_info">{{$farmer->contact_no}}</div>
            </div>
            <div class="row">
                <div class="col-sm-6 trans-label">VARIETY USED:</div>
                <div class="col-sm-6" id="top_info">{{$farmer->variety_planted}}</div>
            </div>
            <div class="row">
                <div class="col-sm-6 trans-label">AREA PLANTED:</div>
                <div class="col-sm-6" id="top_info">{{$farmer->techno_area}} ha</div>
            </div>
            <div class="row">
                <div class="col-sm-6 trans-label">CROPPING SEASON/YR:</div>
                <div class="col-sm-6" id="top_info">{{$farmer->cropping_season}}</div>
            </div>
            <div class="row">
                <div class="col-sm-6 trans-label">METHOD OF CROP ESTABLISHMENT:</div>
                <div class="col-sm-6" id="top_info">{{$crop_establishment->input_desc}}</div>
            </div>
        </div>

        <?php
            $date_transplanted = $farmer->date_transplanted;
            $date_sown = $farmer->date_sown;
            if($farmer->date_sown == "1970-01-01"){
                $date_sown = "";
            }

            if($farmer->date_transplanted == "1970-01-01"){
                $date_transplanted = "";
            }            


        ?>


        <div class="col-lg-6 col-sm-12">
            <div class="row">
                <div class="col-sm-3 trans-label">DATE SOWN:</div>
                <div class="col-sm-3" id="top_info"> <input type="date" name="date_sown" id="date_sown" onchange="date_save()" class="form-control" value="{{$date_sown}}"></div>
            </div>
            <div class="row">
                <div class="col-sm-3 trans-label">DATE TRANSPLANTED:</div>
                <div class="col-sm-3" id="top_info"><input type="date" name="date_transplanted" id="date_transplanted" onchange="date_save()" class="form-control" value="{{$date_transplanted}}"></div>
            </div>
            <div class="row">
                <div class="col-sm-3 trans-label">Yield (ton/ha):</div>
                 @if( $farmer->area_harvested == 0)
                     <div class="col-sm-9" id="top_info">0</div>
                @else

                <?php $yield = ((($farmer->harvest_no_bags * $farmer->harvest_weight_bags)/$farmer->area_harvested)/1000); ?>

                     <div class="col-sm-9" id="top_info">{{$yield}}</div>
                @endif

               
            </div>
            <div class="row">
                <div class="col-sm-3 trans-label">No. of Bags:</div>
                <div class="col-sm-9" id="top_info">{{$farmer->harvest_no_bags}}</div>
            </div>
            <div class="row">
                <div class="col-sm-3 trans-label">Ave. Wt. per bag:</div>
                <div class="col-sm-9" id="top_info">{{$farmer->harvest_weight_bags}}</div>
            </div>
            <div class="row">
                <div class="col-sm-3 trans-label">Price/Kilo ({{$farmer->sold_as}} Paddy):</div>
                @if($farmer->sold_as == "Fresh")
                    <div class="col-sm-9" id="top_info">{{$farmer->fresh_palay_price}}</div>
                @else
                    <div class="col-sm-9" id="top_info">{{$farmer->dry_palay_price}}</div>
                @endif


                
            </div>
        </div>
    </div>

<br>
    <div class="clearfix"></div>

        <div class="row">

            <div class="col-md-12 col-sm-12 col-xs-12"  id="container" style="">
                  <input type="hidden" id="farmer_id" name="farmer_id" value="{{$id}}">
                  <?php $planting_id = ""; ?>
                  <?php $side_title = ""; ?>
                @foreach($activity_list as $key => $act)
                    <h1>{{strtoupper($act->activity)}}</h1>
                    <section style="overflow: auto; height: 100%; width: 100%;">

                            @foreach($form_data as $f)
                              
                              
                            @if(strtoupper($act->activity_head_code) != strtoupper($f->activity_head))
                                @continue
                            @endif

                            @if($f->particulars != null)
                                <div class="row">
                                    
                                    @if($side_title != $f->activities)
                                        <div class="col-lg-2 col-xs-12 form-label-2"><span class=""><strong> {{strtoupper($f->activities)}}</strong></span></div>
                                        <?php $side_title = $f->activities; ?>
                                    @else
                                        <div class="col-lg-2 col-xs-12 form-label-2"><span class=""></span></div>
                                    @endif

                                    @if(strtoupper($act->activity)=="FERTILIZER MANAGEMENT") 
                                    <div class="col-lg-1 col-xs-12 form-label-2"><span class="">{{$f->particulars}}</span></div>
                                    @else
                                    <div class="col-lg-2 col-xs-12 form-label-2"><span class="">{{$f->particulars}}</span></div>
                                    @endif
                                    <div class="col-lg-1 col-xs-3">
                                        <label for="qty_{{$f->planting_id}}"  class="control-label">Quantity</label>
                                        <input id="qty_{{$f->planting_id}}" 
                                        onchange="compute_cost('{{$f->planting_id}}');" 
                                        value="<?php if(isset($data_entry[$f->planting_id]['qty'])){ echo $data_entry[$f->planting_id]['qty'];}else{ echo "0";} ?>" 
                                        name="quantity" 
                                        placeholder="" 
                                        type="number"
                                        class="form-control">
                                        <div class="invalid-feedback d-block"></div>
                                    </div>
                                    <div class="col-lg-1 col-xs-3">
                                        <label for="unit_{{$f->planting_id}}" class="control-label">Unit</label>
                                        <input id="unit_{{$f->planting_id}}" 
                                        name="unit" 
                                        placeholder="" 
                                        type="text" 
                                        value="<?php if(isset($data_entry[$f->planting_id]['unit'])){ echo $data_entry[$f->planting_id]['unit'];}else{ echo "";} ?>"
                                        class="form-control">
                                        <div class="invalid-feedback d-block"></div>
                                    </div>
                                    <div class="col-lg-1 col-xs-3">
                                        <label for="cost_{{$f->planting_id}}" class="control-label">Unit Cost</label>
                                        <input id="cost_{{$f->planting_id}}"  
                                        onchange="compute_cost('{{$f->planting_id}}');" 
                                        value="<?php if(isset($data_entry[$f->planting_id]['cost'])){ echo $data_entry[$f->planting_id]['cost'];}else{ echo "0";} ?>" 
                                        name="unit_cost" 
                                        placeholder="" 
                                        type="number"
                                            class="form-control">
                                        <div class="invalid-feedback d-block"></div>
                                    </div>
                                    <div class="col-lg-1 col-xs-12">
                                        <label for="total_cost_{{$f->planting_id}}" class="control-label">Total Cost</label>
                                        <input id="total_cost_{{$f->planting_id}}"  
                                        name="total_cost" 
                                        placeholder="" 
                                        type="text"
                                        value="<?php if(isset($data_entry[$f->planting_id]['total_cost'])){ echo $data_entry[$f->planting_id]['total_cost'];}else{ echo "0";} ?>"
                                        class="form-control" 
                                        disabled="">
                                        <div class="invalid-feedback d-block"></div>
                                    </div>

                                        @if(strtoupper($act->activity)=="FERTILIZER MANAGEMENT")                                          
                                          <div class="col-lg-2 col-xs-2">
                                              <label for="fertilizer_{{$f->planting_id}}" class="control-label">Fertilizer Category</label>
                                             <select class="form-control" name="fertilizer_{{$f->planting_id}}" id="fertilizer_{{$f->planting_id}}">
                                              <option value="">Select Fertilizer</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Organic Fertilizer"){ echo "selected";}else{ echo "";} ?> value="Organic Fertilizer">Organic Fertilizer</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Inorganic Fertilizer"){ echo "selected";}else{ echo "";} ?> value="Inorganic Fertilizer">Inorganic Fertilizer</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Foliar Fertilizer"){ echo "selected";}else{ echo "";} ?>   value="Foliar Fertilizer">Foliar Fertilizer</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Herbicide"){ echo "selected";}else{ echo "";} ?>  value="Herbicide">Herbicide</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Insecticide"){ echo "selected";}else{ echo "";} ?>  value="Insecticide">Insecticide</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Fungicide"){ echo "selected";}else{ echo "";} ?> value="Fungicide">Fungicide</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Mollusiscide"){ echo "selected";}else{ echo "";} ?> value="Mollusiscide">Mollusiscide</option>
                                              <option <?php if(isset($data_entry[$f->planting_id]['fertilizer_category']) && $data_entry[$f->planting_id]['fertilizer_category']=="Rodenticide"){ echo "selected";}else{ echo "";} ?> value="Rodenticide">Rodenticide</option>
                                             </select>
                                              <div class="invalid-feedback d-block"></div>
                                          </div>
                                          @endif

                                    <div class="col-lg-2 col-xs-3">
                                        <label for="date_{{$f->planting_id}}" class="control-label">Date</label>
                                        <input id="date_{{$f->planting_id}}" 
                                        name="date" 
                                        placeholder="" 
                                        value="<?php if(isset($data_entry[$f->planting_id]['date'])){ echo $data_entry[$f->planting_id]['date'];}else{ echo "";} ?>" 
                                        type="date" class="form-control">
                                        <div class="invalid-feedback d-block"></div>
                                    </div>

                                    @if(strtoupper($act->activity)=="FERTILIZER MANAGEMENT") 
                                    <div class="col-lg-1 col-xs-9">
                                    @else
                                    <div class="col-lg-2 col-xs-9">
                                    @endif

                                   
                                        <label for="remarks_{{$f->planting_id}}" class="control-label">Remarks</label>
                                        <input id="remarks_{{$f->planting_id}}"
                                         name="remarks"
                                         placeholder="" 
                                         type="text" 
                                         value="<?php if(isset($data_entry[$f->planting_id]['remarks'])){ echo $data_entry[$f->planting_id]['remarks'];}else{ echo "";} ?>"
                                         class="form-control">
                                        <div class="invalid-feedback d-block"></div>
                                    </div>

                                </div>
                                <?php 
                                if($planting_id!="")$planting_id.=";";
                                $planting_id .= $f->planting_id; ?>

                                                            
                            @endif
                        @endforeach
                    </section>
                @endforeach
                 
            </div>
              <button class="btn btn-danger btn-sm" style="float:right; margin-right: 15px;" onclick="home_page();">Cancel</button>
        </div>
    </div>

   <input type='hidden' id="planting_list" name="planting_list" value="{{$planting_id}}">
@endsection

@push('scripts')


<script src="{{asset('public/js/step_master/jquery.steps.js')}}" ></script>


<script type="text/javascript">

        function date_save(){
            var date_sown = $("#date_sown").val();
            var date_transplanted = $("#date_transplanted").val();
            var farmer_id = $("#farmer_id").val();

             $.ajax({
                    method: 'POST',
                    url: "{{route('palaysikatan.save_date')}}",
                    data: {
                        _token: _token,
                        date_sown: date_sown,
                        date_transplanted: date_transplanted,
                        id: farmer_id
                    },
                    dataType: 'json',
                    success: function (source) {
                    }
            }); 


        }


        function home_page(){
            var yesNo = confirm("Back to Farmer list?");
            if(yesNo){
               window.location.replace("{{route('palaysikatan.farmers')}}"); 
            }


            
        }

        function compute_cost(id){
            var qty = $("#qty_"+id).val();
            var cost = $("#cost_"+id).val();
            var total_cost = parseFloat(qty) * parseFloat(cost);   

            $("#total_cost_"+id).val(total_cost.toFixed(2));
        }


        $("#container").steps({
            headerTag: "h1",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            enableAllSteps: true,
            autoFocus: true,
            labels: {
            finish: "Submit",
            next: "Next",
            previous: "Previous"
            },
            onFinished: function (event, currentIndex) {
                submitdata();
            },
            onStepChanged: function (event, currentIndex, newIndex)
            {       
                updatedata();

            }


        });

        function updatedata(){
              HoldOn.open(holdon_options);
                        var planting_list = $("#planting_list").val();
                        var farmer_id = $("#farmer_id").val();
                         
                        planting_list = planting_list.split(";");
                        var status = 0;
                        var ins_string = "";
                        var fertilizer="";
                        for (var i=0; i<planting_list.length; i++){
                            var qty = $("#qty_"+planting_list[i]).val();
                            var unit = $("#unit_"+planting_list[i]).val();
                            var cost = $("#cost_"+planting_list[i]).val();
                            var total_cost = $("#total_cost_"+planting_list[i]).val();
                            if($("#fertilizer_"+planting_list[i]).val() != undefined){
                                 fertilizer = $("#fertilizer_"+planting_list[i]).val();
                            }

                            if(total_cost!="" && total_cost != null){
                                total_cost=total_cost.replace(/\,/g,'');
                            }
                            var date = $("#date_"+planting_list[i]).val();
                            var remarks = $("#remarks_"+planting_list[i]).val();
                            var pid = planting_list[i];
                            
                            if(parseFloat(qty) != 0 && parseFloat(cost) != 0 ){
                                status = 1;
                                if(ins_string !== "") {ins_string = ins_string+";"};                      
                                ins_string = ins_string+pid+","+qty+","+unit+","+cost+","+total_cost+","+fertilizer+","+date+","+remarks;
                            }   
                        }

                        if(status == 1){
                             $.ajax({
                                    method: 'POST',
                                    url: "{{route('palaysikatan.insert.planting')}}",
                                    data: {
                                        _token: _token,
                                        ins_string: ins_string,
                                        farmer_id: farmer_id
                                    },
                                    dataType: 'json',
                                    success: function (source) {
                                        if(source === "true"){
                                            HoldOn.close();
                                        }else{
                                           alert(source);
                                        }
                                    }
                            }); 
                        }else{
                            HoldOn.close();
                        }

        }

        function submitdata(){
            var yesno = confirm("Submit Data?");
            if(yesno){
                HoldOn.open(holdon_options);
                        var planting_list = $("#planting_list").val();
                        var farmer_id = $("#farmer_id").val();
                         
                        planting_list = planting_list.split(";");
                        var status = 0;
                        var ins_string = "";
                        var fertilizer="";
                        for (var i=0; i<planting_list.length; i++){
                            var qty = $("#qty_"+planting_list[i]).val();
                            var unit = $("#unit_"+planting_list[i]).val();
                            var cost = $("#cost_"+planting_list[i]).val();
                            var total_cost = $("#total_cost_"+planting_list[i]).val();
                            if($("#fertilizer_"+planting_list[i]).val() != undefined){
                                 fertilizer = $("#fertilizer_"+planting_list[i]).val();
                            }

                            if(total_cost!="" && total_cost != null){
                                total_cost=total_cost.replace(/\,/g,'');
                                total_cost=parseInt(total_cost,10)
                            }
                            var date = $("#date_"+planting_list[i]).val();
                            var remarks = $("#remarks_"+planting_list[i]).val();
                            var pid = planting_list[i];
                            
                            if(parseFloat(qty) != 0 || parseFloat(cost) != 0 ){
                                status = 1;
                                if(ins_string !== "") {ins_string = ins_string+";"};                      
                                ins_string = ins_string+pid+","+qty+","+unit+","+cost+","+total_cost+","+fertilizer+","+date+","+remarks;  
                            }   
                        }

                        if(status == 1){
                             $.ajax({
                                    method: 'POST',
                                    url: "{{route('palaysikatan.insert.planting')}}",
                                    data: {
                                        _token: _token,
                                        ins_string: ins_string,
                                        farmer_id: farmer_id
                                    },
                                    dataType: 'json',
                                    success: function (source) {
                                        if(source === "true"){
                                            HoldOn.close();
                                           alert("Saving Success");
                                           window.location.replace("{{route('palaysikatan.farmers')}}");
                                        }else{
                                           alert(source);
                                        }
                                    }
                            }); 
                        }else{

                            alert("No Insert Data");
                            HoldOn.close();
                        }

            }
        }




</script>









<script>
$("#plantingForm").submit(function(e) {
    e.preventDefault();
    var form_data = $(this).serializeArray();
  
    $.ajax({
        type: "POST",
        url: "{{url('palaysikatan/add/planting')}}",
        data: {
            _token : "{{ csrf_token() }}", 
            form_data : form_data
        },
        success: function(response) {
           if(response['status'] == 'error_store'){
                alert(response['errors']);
            }else{
                alert("Planting info added successfully");
                window.location.replace("{{url('palaysikatan/farmers')}}");
            }
        }
    });
   

});

function objectifyForm(formArray) {
    //serialize data function
    var returnArray = {};
    
    for (var i = 0; i < formArray.length; i++){
        
        data[formArray[i]['name']] = formArray[i]['value'];
       
        if(formArray[i]['name'] == 'remarks'){
            returnArray[j] = data[0];
            data = [];
            j++;
        }
    }
    return returnArray;
}
</script>
@endpush