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
                Material Inputs (Applied in AREA PLANTED with RCEF seeds)
            </h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <div class="row">
                <div class="col-sm-4 trans-label">Farmer ID:</div>
                <div class="col-sm-8" id="top_info">{{$farmer->farmer_id}}</div>
            </div>
            <div class="row">
                <div class="col-sm-4 trans-label">NAME:</div>
                <div class="col-sm-8" id="top_info">{{$farmer->f_full_name}}</div>
            </div>
            <div class="row">
                <div class="col-sm-4 trans-label">CONTACT #:</div>
                <div class="col-sm-8" id="top_info">{{$farmer->contact_no}}</div>
            </div>
           
        </div>
      
    </div>

<br>
    <div class="clearfix"></div>

            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12"  id="container" style="">
                  <input type="hidden" id="farmer_id" name="farmer_id" value="{{$id}}">
                  <?php $material_ids = ""; ?>
                @foreach($activity_header as $key => $act)
                    <h1>{{strtoupper($act->material_desc)}}</h1>
                    <section style="overflow: auto; height: 100%; width: 100%;">
                            <div class="row">  
                                <div class="col-lg-2 col-xs-3" style="text-align: center; font-weight: bold; font-size: 15px;">
                                        ITEM       
                                </div>

                                <div class="col-lg-3 col-xs-12" style="text-align: center; font-weight: bold; font-size: 15px; ">
                                       <div class="col-lg-12 col-xs-12" style="text-align: center; font-weight: bold; font-size: 15px;">
                                        SEEDBED APPLICATION
                                       </div>                                       

                                       <div class="col-lg-4 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  QTY</div>                                       
                                       <div class="col-lg-4 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  UNIT</div>  
                                       <div class="col-lg-4 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  Kg/L per unit</div>  
                                            
                                </div>



                                <div class="col-lg-3 col-xs-12" style="text-align: center; font-weight: bold; font-size: 15px; ">
                                       <div class="col-lg-12 col-xs-12" style="text-align: center; font-weight: bold; font-size: 15px;">
                                        FIELD APPLICATION
                                       </div>                                       

                                       <div class="col-lg-4 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  QTY</div>                                       
                                       <div class="col-lg-4 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  UNIT</div>  
                                       <div class="col-lg-4 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  Kg/L per unit</div>  
                                            
                                </div>
                                

                                <div class="col-lg-3 col-xs-12" style="text-align: center; font-weight: bold; font-size: 15px; ">
                                       <div class="col-lg-12 col-xs-12" style="text-align: center; font-weight: bold; font-size: 15px;">
                                &nbsp;
                                       </div>                                       

                                       <div class="col-lg-6 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  PRICE/UNIT</div>                                       
                                       <div class="col-lg-6 col-xs-12" style="text-align: center; font-weight: bold; font-size: 12px;">  NOTES</div>  
                                            
                                </div>
                                <div class="col-lg-12 col-xs-3" style="text-align: center; font-weight: bold; font-size: 15px;">
                                        <hr style="height: 2px; width: 99%; background-color: #848484; padding: 0; margin:0;" />
                                </div>



                            </div>


                            @foreach($form_data as $f)
                              
                            @if(strtoupper($act->material_code) != strtoupper($f->material_header))
                                @continue
                            @endif

                            @if($f->with_data != 0)
                                <div class="row">
                                    <?php 

                                    if($f->is_editable == 1)
                                    {
                                        $value = ""; 
                                        $holder = $f->material_desc;
                                        $disabled = '';
                                    }else
                                    {
                                        $value = $f->material_desc;
                                        $holder = $f->material_desc;
                                        $disabled = "disabled=''";

                                            
                                    }



                                    if(isset($data_entry[$f->id]['item'])){
                                                $value = $data_entry[$f->id]['item'];
                                            }


                                    ?>                                    
 
                                  
                                    <div class="col-lg-2 col-xs-3">
                                       <br> 
                                        <input id="item_{{$f->id}}" 
                                        name="item_{{$f->id}}" 
                                        placeholder="{{$holder}}"
                                        value="{{$value}}" 
                                        type="text"
                                        class="form-control" {{$disabled}}>
                                        <div class="invalid-feedback d-block"></div>
                                    </div>
                                   

                                    <div class="col-lg-3 col-xs-3">
                                            <br>
                                           <div class="col-lg-4 col-xs-12" >  
                                                <input type="number"
                                                id = "qty_sa_{{$f->id}}"
                                                name = "qty_sa_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['qty_sa'])){ echo $data_entry[$f->id]['qty_sa'];}else{ echo "0";} ?>"
                                                placeholder="QTY"
                                                class="form-control" 
                                                >
                                           </div>                                       
                                           <div class="col-lg-4 col-xs-12" >  
                                                <input type="text"
                                                id = "unit_sa_{{$f->id}}"
                                                name = "unit_sa_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['unit_sa'])){ echo $data_entry[$f->id]['unit_sa'];}else{ echo "";} ?>"
                                                placeholder="UNIT"
                                                class="form-control" 
                                                >
                                           </div>  
                                         

                                            <div class="col-lg-4 col-xs-12" >  
                                                <input type="text"
                                                id = "kg_lg_sa_{{$f->id}}"
                                                name = "kg_lg_sa_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['kg_lg_sa'])){ echo $data_entry[$f->id]['kg_lg_sa'];}else{ echo "";} ?>"
                                                placeholder="KG/L"
                                                class="form-control" 
                                                >

                                           </div> 
                                    </div>


                                    <div class="col-lg-3 col-xs-3">
                                            <br>
                                           <div class="col-lg-4 col-xs-12" >  
                                                <input type="number"
                                                id = "qty_fa_{{$f->id}}"
                                                name = "qty_fa_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['qty_fa'])){ echo $data_entry[$f->id]['qty_fa'];}else{ echo "0";} ?>"
                                                placeholder="QTY"
                                                class="form-control" 
                                                >
                                           </div>                                       
                                           <div class="col-lg-4 col-xs-12" >  
                                                <input type="text"
                                                id = "unit_fa_{{$f->id}}"
                                                name = "unit_fa_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['unit_fa'])){ echo $data_entry[$f->id]['unit_fa'];}else{ echo "";} ?>"
                                                placeholder="UNIT"
                                                class="form-control" 
                                                >
                                           </div>  
                                         

                                            <div class="col-lg-4 col-xs-12" >  
                                                <input type="text"
                                                id = "kg_lg_fa_{{$f->id}}"
                                                name = "kg_lg_fa_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['kg_lg_fa'])){ echo $data_entry[$f->id]['kg_lg_fa'];}else{ echo "";} ?>"
                                                placeholder="KG/L"
                                                class="form-control" 
                                                >

                                           </div> 
                                    </div>

                                    <div class="col-lg-3 col-xs-3">
                                            <br>
                                           <div class="col-lg-6 col-xs-12" >  
                                                <input type="number"
                                                id = "price_{{$f->id}}"
                                                name = "price_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['price'])){ echo $data_entry[$f->id]['price'];}else{ echo "";} ?>"
                                                placeholder="PRICE/UNIT"
                                                class="form-control" 
                                                >
                                           </div>                                       
                                           <div class="col-lg-6 col-xs-12" >  
                                                <input type="text"
                                                id = "notes_{{$f->id}}"
                                                name = "notes_{{$f->id}}"
                                                value = "<?php if(isset($data_entry[$f->id]['notes'])){ echo $data_entry[$f->id]['notes'];}else{ echo "";} ?>"
                                                placeholder="NOTES"
                                                class="form-control" 
                                                style="width:300px;" 
                                                >
                                           </div>  
                                         

                                    </div>



                                </div>
                                <?php if($material_ids!="")$material_ids.=";";$material_ids .= $f->id; ?>            
                            @else
                                <div class="row">
                                    @if($act->material_desc != $f->material_desc)
                                    <div class="col-lg-12 col-xs-12 form-label-2"><span class="">{{strtoupper($f->material_desc)}}</span></div>
                                    @endif
                                </div>
                            @endif



                        @endforeach
                    </section>
                @endforeach
                 
            </div>
                 <button class="btn btn-danger btn-sm" style="float:right; margin-right: 15px;" onclick="home_page();">Cancel</button>
            </div>
    </div>

   <input type='hidden' id="material_ids" name="material_ids" value="{{$material_ids}}">
@endsection

@push('scripts')
    

<script src="{{asset('public/js/step_master/jquery.steps.js')}}" ></script>


<script type="text/javascript">

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
            autoFocus: true,
            labels: {
            finish: "Submit",
            next: "Next",
            previous: "Previous"
            },
            onFinished: function (event, currentIndex) {
                submitdata();
            }

        });

        function submitdata(){
            var yesno = confirm("Submit Data?");
            if(yesno){
                HoldOn.open(holdon_options);
                        var material_list = $("#material_ids").val();
                        var farmer_id = $("#farmer_id").val();
                         
                        material_list = material_list.split(";");
                        var status = 0;
                        var ins_string = "";

                        for (var i=0; i<material_list.length; i++){
                            var item = $("#item_"+material_list[i]).val();
                            var qty_sa = $("#qty_sa_"+material_list[i]).val();
                            var unit_sa = $("#unit_sa_"+material_list[i]).val();
                            var kg_lg_sa = $("#kg_lg_sa_"+material_list[i]).val();
                            var qty_fa = $("#qty_fa_"+material_list[i]).val();
                            var unit_fa = $("#unit_fa_"+material_list[i]).val();
                            var kg_lg_fa = $("#kg_lg_fa_"+material_list[i]).val();
                            var price = $("#price_"+material_list[i]).val();
                            var notes = $("#notes_"+material_list[i]).val();
                            var mid = material_list[i];
                            
                            if(item !== ""){
                                status = 1;
                                if(ins_string !== "") {ins_string = ins_string+";"};                      
                                ins_string = ins_string+mid+","+item+","+qty_sa+","+unit_sa+","+kg_lg_sa+","+qty_fa+","+unit_fa+","+kg_lg_fa+","+price+","+notes;                       
                            }   
                        }

                        if(status == 1){
                             $.ajax({
                                    method: 'POST',
                                    url: "{{route('palaysikatan.insert.material')}}",
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
                                           HoldOn.close();
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