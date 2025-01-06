@extends('layouts.index')
@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Seed Packaging and Distribution Ratio</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">

                    <div class="form-group">

            

                        <div class="form-group">
                            <label class="control-label col-md-1 col-sm-1 col-xs-1">Specification:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                
                                <select name="specData" id="specData" class="form-control specData" data-parsley-min="1">
                                    <option value="0">Please select a Spec</option>
                                    <option value="15">15kg</option>
                                    <option value="18">18kg</option>
                                    <option value="20">20kg</option>
                                </select>
                            </div>
                        </div>



                        <br>

                        <div class="form-group matrixLabel" style="display: none">
                            <h2>Seed Distribution Matrix</h2>
    
                      
    
                        <div class="form-group ">
    
                            <div class="row ratio">
                               
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary btn-save-setting">Save Settings</button>
                    </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Regional Specification List</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">                
                    <div class="form-group">
                        <div class="form-group">
                            <table class="table table-striped table-bordered tbl" id="specDataList">
                                <thead>
                                    <tr>
                                        <th style="width: auto;">Label</th>
                                        <th style="width: auto;">Package(Kg)</th>
                                        <th style="width: auto;">Packets(Kg)</th>                                                                       
                                        <th style="width: auto;">Date Created</th>
                                        <th style="width: auto;">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script type="text/javascript">
        var token = "{{ csrf_token() }}";
        var framData = 0;

        tblLoad();
        function tblLoad(){
        $('#specDataList').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			//ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			"ajax": {
                        "url": "{{ route('spectDataList') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",                         
                        }
                    },
			columns: [
				{data: 'specLabel', name: 'specLabel' },
				{data: 'seed_package', name: 'seed_package'},
				{data: 'seed_sub_package', name: 'seed_sub_package'},
				{data: 'date_created', name: 'date_created'},				
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
    }
    

    $('body').on('click', '.edit-btn', function() {
        var data = $(this).attr("data-id");
        $('#specData').val(data).change();
        
    });
        $('body').on('click', '.btn-save-setting', function() {

            var psi_req = $('.requiredData');
            var count_null = 0;
            for (var i = 0; i < psi_req.length; i++) {
                var data = $(psi_req[i]).val();
                if (data == "") {
                    $(psi_req[i]).css("border", "1px solid red");
                    count_null += 1;
                } else {
                    $(psi_req[i]).removeAttr("style");
                }
            }

            if(count_null > 0){
                if (confirm('Some field are blank want to contine?')) {
                    SaveSetting()            
            } else {
              // Do nothing!            
            }
            }else{
                SaveSetting();
            }
        });

        function SaveSetting(){
            HoldOn.open(holdon_options);
                    var specDataText = $("#specData option:selected").text();
                    var specData = $('#specData').val();
                    var initialInput = $('.initialInput');
                    var specInput = $('.specInput');
                    var threeKg = $('.3kg');
                    var fiveKg = $('.5kg');
                    var arrayinitialInput = [];
                    var arrayspecInput = [];
                    var arraythreeKg = [];
                    var arrayfiveKg = [];

                    for (let index = 0; index < specInput.length; index++) {
                        if ($(specInput[index]).val() > 0) {
                            arrayinitialInput.push($(initialInput[index]).val());
                            arrayspecInput.push($(specInput[index]).val());
                            if($(threeKg[index]).val() ==""){
                                arraythreeKg.push(0);
                            }else{
                                arraythreeKg.push($(threeKg[index]).val());
                            }

                            if($(fiveKg[index]).val() ==""){
                                arrayfiveKg.push(0);
                            }else{
                                arrayfiveKg.push($(fiveKg[index]).val());
                            }
                        
                            
                        }
                    }
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-save-ratio') }}",
                        data: {
                            _token: token,
                            initialInput: arrayinitialInput,
                            specInput: arrayspecInput,
                            specData: specData,
                            specDataText: specDataText,
                            threeKg: arraythreeKg,
                            fiveKg: arrayfiveKg,
                        },
                        success: function(response) {
                            console.log(response);
                            if (response == "success") {
                                alert("Specification and distribution Matrix Successfully Saved")
                                HoldOn.close();
                                location.reload();
                            } else {
                                alert("Error!")
                                HoldOn.close();
                            }
                        },
                        error: function(response) {
                            console.log(response);
                            HoldOn.close();
                        }
                    });
        }  
        $('body').on('change', '.specData', function() {
             framData = 1;
            var data = $(this).val();
            var loop = 0;
            var multiplier = 0;
            $(".ratio").empty();
            $.ajax({
                type: 'POST',
                url: "{{ route('getSpec') }}",
                data: {
                    _token: token,
                    spec:data
                },
                dataType: 'json',
                success: function(result) {
                    if(result.specInfo1.length >0 || result.specInfo2.length >0 ){
                    if (data == "15") {
                                var roundData=0;
                                if(result.specInfo1.length >0){
                                    $.each(result.specInfo1, function(i, d) {

                                        var dataTmp = 0;
                                        if(result.specInfo1.length == 0){
                                           
                                        }else{
                                            dataTmp = result.specInfo2[i].range_volume;
                                        }

                                        
                                    if(roundData == 0){
                                            $(".ratio").append(' <div class="col-md-2"></div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                            '<div class="col-md-2"></div>'+
                                            '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div>'+
                                            '<div class="col-md-2 range_volume" style="text-align: center; margin-top: 1%" data-id=""> 5 Kg</div> <br> <br>'+
                                            '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0" value="'+d.range_end+'"></div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg1"></div>'+
                                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+dataTmp+'" class="form-control 5kg 5kg1"></div>'+
                                            '<br> <br>');
                                        }else if(roundData == 1){
                                            $(".ratio").append(
                                            '<div class="dynamicFormInput">'+
                                            '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                            '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+'<br> <br>' +
                                            '</div>');
                                            framData++;
                                        }else if(roundData >= 2 && roundData != parseInt(result.specInfo1.length)-1){
                                            $(".dynamicFormInput").append(
                                            '<div class="dynamicFormInputRatio'+framData+'">'+
                                            '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                            '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+
                                            '    <button  data-id="' + framData +
                                            '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn'+ framData +'" id="delete-clone-btn"><i' +
                                            '               class="fa fa-times"></i></button>' +
                                            '<br> <br>' +
                                            '</div>');
                                            framData++;
                                        } else if(roundData == parseInt(result.specInfo1.length)-1){
                                            $(".ratio").append(
                                            '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" readonly data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                        
                                            '<br> <br>');
                                        
                                        }
                                        roundData++;
                                
                                    });
                                }else{
                                    $.each(result.specInfo2, function(i, d) {
                                        var dataTmp = 0;
                                        if(result.specInfo1.length == 0){
                                                                                                                                   
                                        }else{
                                            dataTmp = result.specInfo1[i].range_volume
                                        }  
                                    if(roundData == 0){
                                            $(".ratio").append(' <div class="col-md-2"></div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                            '<div class="col-md-2"></div>'+
                                            '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div>'+
                                            '<div class="col-md-2 range_volume" style="text-align: center; margin-top: 1%" data-id=""> 5 Kg</div> <br> <br>'+
                                            '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0" value="'+d.range_end+'"></div>'+
                                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '<div class="col-md-2"> <input type="number" placeholder="Bags"   value="'+dataTmp+'" class="form-control 3kg 3kg1"></div>'+
                                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 5kg 5kg1"></div>'+
                                            '<br> <br>');
                                        }else if(roundData == 1){
                                            $(".ratio").append(
                                            '<div class="dynamicFormInput">'+
                                            '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+dataTmp+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+d.range_volume+'"  class="form-control 5kg 5kg'+ framData +'"></div>'+
                                            '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+'<br> <br>' +
                                            '</div>');
                                            framData++;
                                        }else if(roundData >= 2 && roundData != parseInt(result.specInfo2.length)-1){
                                            $(".dynamicFormInput").append(
                                            '<div class="dynamicFormInputRatio'+framData+'">'+
                                            '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+dataTmp+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags"   value="'+d.range_volume+'"  class="form-control 5kg 5kg'+ framData +'"></div>'+
                                            '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+
                                            '    <button  data-id="' + framData +
                                            '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn'+ framData +'" id="delete-clone-btn"><i' +
                                            '               class="fa fa-times"></i></button>' +
                                            '<br> <br>' +
                                            '</div>');
                                            framData++;
                                        } else if(roundData == parseInt(result.specInfo2.length)-1){
                                            $(".ratio").append(
                                            '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" readonly data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+dataTmp+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+d.range_volume+'"  class="form-control 5kg 5kg'+ framData +'"></div>'+
                                        
                                            '<br> <br>');
                                        
                                        }
                                        roundData++;
                                
                                    });
                                }
                               
                            
                        }else if(data == "18"){
                            var roundData=0;

                            if(result.specInfo1.length >0){
                                 $.each(result.specInfo1, function(i, d) {

                                    var dataTmp = 0;
                                        if(result.specInfo1.length == 0){
                                           
                                        }else{
                                            dataTmp = result.specInfo2[i].range_volume;
                                        }
                                        
                                    if(roundData == 0){
                                        $(".ratio").append(' <div class="col-md-2"></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                        '<div class="col-md-2"></div>'+
                                        '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div>'+
                                        '<div class="col-md-2 range_volume" style="text-align: center; margin-top: 1%" data-id=""> 5 Kg</div> <br> <br>'+
                                        '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0" value="'+d.range_end+'"></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg1"></div>'+
                                        '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+dataTmp+'" class="form-control 5kg 5kg1"></div>'+
                                        '<br> <br>');
                                    }else if(roundData == 1){
                                        $(".ratio").append(
                                        '<div class="dynamicFormInput">'+
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                        '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+'<br> <br>' +
                                        '</div>');
                                        framData++;
                                    }else if(roundData >= 2 && roundData != parseInt(result.specInfo1.length)-1){
                                        $(".dynamicFormInput").append(
                                        '<div class="dynamicFormInputRatio'+framData+'">'+
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                        '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+
                                        '    <button  data-id="' + framData +
                                        '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn'+ framData +'" id="delete-clone-btn"><i' +
                                        '               class="fa fa-times"></i></button>' +
                                        '<br> <br>' +
                                        '</div>');
                                        framData++;
                                    } else if(roundData == parseInt(result.specInfo1.length)-1){
                                        $(".ratio").append(
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" readonly data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                    
                                        '<br> <br>');
                                    
                                    }
                                    roundData++;
                                
                                });
                            }else{
                                $.each(result.specInfo2, function(i, d) {
                                    var dataTmp = 0;
                                        if(result.specInfo1.length == 0){
                                           
                                        }else{
                                            dataTmp = result.specInfo1[i].range_volume;
                                        }
                                        
                                    if(roundData == 0){
                                        $(".ratio").append(' <div class="col-md-2"></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                        '<div class="col-md-2"></div>'+
                                        '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div>'+
                                        '<div class="col-md-2 range_volume" style="text-align: center; margin-top: 1%" data-id=""> 5 Kg</div> <br> <br>'+
                                        '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0" value="'+d.range_end+'"></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg1"></div>'+
                                        '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+dataTmp+'" class="form-control 5kg 5kg1"></div>'+
                                        '<br> <br>');
                                    }else if(roundData == 1){
                                        $(".ratio").append(
                                        '<div class="dynamicFormInput">'+
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                        '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+'<br> <br>' +
                                        '</div>');
                                        framData++;
                                    }else if(roundData >= 2 && roundData != parseInt(result.specInfo2.length)-1){
                                        $(".dynamicFormInput").append(
                                        '<div class="dynamicFormInputRatio'+framData+'">'+
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                        '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+
                                        '    <button  data-id="' + framData +
                                        '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn'+ framData +'" id="delete-clone-btn"><i' +
                                        '               class="fa fa-times"></i></button>' +
                                        '<br> <br>' +
                                        '</div>');
                                        framData++;
                                    } else if(roundData == parseInt(result.specInfo2.length)-1){
                                        $(".ratio").append(
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" readonly data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+dataTmp+'" class="form-control 5kg 5kg'+ framData +'"></div>'+
                                    
                                        '<br> <br>');
                                    
                                    }
                                    roundData++;
                                
                                });
                            }
                              
                        }else if(data == "20"){
                            var roundData=0;
                                $.each(result.specInfo1, function(i, d) {
                                    if(roundData == 0){
                                        $(".ratio").append(' <div class="col-md-2"></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                                        '<div class="col-md-2"></div>'+
                                        '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div><br> <br>'+
                                        '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0" value="'+d.range_end+'"></div>'+
                                        '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg1"></div>'+

                                        '<br> <br>');
                                    }else if(roundData == 1){
                                        $(".ratio").append(
                                        '<div class="dynamicFormInput">'+
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        
                                        '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+'<br> <br>' +
                                        '</div>');
                                        framData++;
                                    }else if(roundData >= 2 && roundData != parseInt(result.specInfo1.length)-1){
                                        $(".dynamicFormInput").append(
                                        '<div class="dynamicFormInputRatio'+framData+'">'+
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+
                                        
                                        '    <button class="btn btn-success btn-sm clone-btn" data-id="'+ framData +'" id="clone-btn"><i class="fa fa-plus-square"></i></button>'+
                                        '    <button  data-id="' + framData +
                                        '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn'+ framData +'" id="delete-clone-btn"><i' +
                                        '               class="fa fa-times"></i></button>' +
                                        '<br> <br>' +
                                        '</div>');
                                        framData++;
                                    } else if(roundData == parseInt(result.specInfo1.length)-1){
                                        $(".ratio").append(
                                        '    <div class="col-md-2"><input type="text" class="form-control spec'+ framData +' initialInput"  value="'+d.range_start+'" value="0" readonly></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"><input type="number" placeholder="0.0"  value="'+d.range_end+'" readonly data-id="1" class="form-control specInput specInput'+ framData +'"></div>'+
                                        '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                                        '    <div class="col-md-2"> <input type="number" placeholder="Bags" readonly value="'+d.range_volume+'" class="form-control 3kg 3kg'+ framData +'"></div>'+

                                    
                                        '<br> <br>');
                                    
                                    }
                                    roundData++;
                                
                                });
                        }
                    }else{
                        if (data == "15") {
                $(".ratio").append(' <div class="col-md-2"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                            '<div class="col-md-2"></div>'+
                            '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div>'+
                            '<div class="col-md-2 range_volume" style="text-align: center; margin-top: 1%" data-id=""> 5 Kg</div> <br> <br>'+

                            '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 3kg 3kg1"></div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 5kg 5kg1"></div>'+
                            '<br> <br>'+

                            '<div class="dynamicFormInput">'+
                            '    <div class="col-md-2"><input type="text" class="form-control spec1 initialInput" value="0" readonly></div>'+
                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  data-id="1" class="form-control specInput specInput1"></div>'+
                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 3kg 3kg1"></div>'+
                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 5kg 5kg1"></div>'+
                            '    <button class="btn btn-success btn-sm clone-btn" data-id="0" id="clone-btn"><i class="fa fa-plus-square"></i></button><br> <br>'+
                            '</div>'+

                            '<div class="col-md-2"><input type="text" class="form-control spec2 initialInput" placeholder="0" readonly></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"><input type="number" placeholder="0.0" data-id="2" value="1" readonly class="form-control specInput2 specInput"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags" value="'+data/3+'" readonly class="form-control 3kg 3kg2"></div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags" value="'+data/5+'" readonly class="form-control 5kg 5kg2"></div> <br> <br>');
            }else if(data == "18"){
                        $(".ratio").append(' <div class="col-md-2"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                            '<div class="col-md-2"></div>'+
                            '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 3 Kg</div>'+
                            '<div class="col-md-2 range_volume" style="text-align: center; margin-top: 1%" data-id=""> 6 Kg</div> <br> <br>'+

                            '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  class="form-control 3kg 3kg1"></div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  class="form-control 5kg 5kg1"></div>'+
                            '<br> <br>'+

                            '<div class="dynamicFormInput">'+
                            '    <div class="col-md-2"><input type="text" class="form-control spec1 initialInput" value="0" readonly></div>'+
                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  data-id="1" class="form-control specInput specInput1"></div>'+
                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 3kg 3kg1"></div>'+
                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 5kg 5kg1"></div>'+
                            '    <button class="btn btn-success btn-sm clone-btn" data-id="0" id="clone-btn"><i class="fa fa-plus-square"></i></button><br> <br>'+
                            '</div>'+

                            '<div class="col-md-2"><input type="text" class="form-control spec2 initialInput" placeholder="0" readonly></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"><input type="number" placeholder="0.0" data-id="2" value="1" readonly class="form-control specInput2 specInput"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+data/3+'" readonly class="form-control 3kg 3kg2"></div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags"  value="'+data/6+'" readonly class="form-control 5kg 5kg2"></div> <br> <br>');
            }else if(data == "20"){
                $(".ratio").append(' <div class="col-md-2"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> </div>'+
                            '<div class="col-md-2"></div>'+
                            '<div class="col-md-2" style="text-align: center; margin-top: 1%"> 5 Kg</div> <br> <br>'+

                            '<div class="col-md-2"><input type="text" class="form-control spec0 initialInput" value="0.01" readonly></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="0" class="form-control specInput specInput0"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 3kg 3kg1"></div>'+
                            
                            '<br> <br>'+

                            '<div class="dynamicFormInput">'+
                            '    <div class="col-md-2"><input type="text" class="form-control spec1 initialInput" value="0" readonly></div>'+
                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '    <div class="col-md-2"><input type="number" placeholder="0.0"  data-id="1" class="form-control specInput specInput1"></div>'+
                            '    <div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '    <div class="col-md-2"> <input type="number" placeholder="Bags" class="form-control 3kg 3kg1"></div>'+
                            
                            '    <button class="btn btn-success btn-sm clone-btn" data-id="0" id="clone-btn"><i class="fa fa-plus-square"></i></button><br> <br>'+
                            '</div>'+

                            '<div class="col-md-2"><input type="text" class="form-control spec2 initialInput" placeholder="0" readonly></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"><input type="number" placeholder="0.0" data-id="2" value="1" readonly class="form-control specInput2 specInput"></div>'+
                            '<div class="col-md-1" style="text-align: left; margin-top: 1%";> ha</div>'+
                            '<div class="col-md-2"> <input type="number" placeholder="Bags" value="'+data/5+'" readonly class="form-control 3kg 3kg2"></div><br> <br>');
            }
            
            
           
            $('.matrixLabel').show();
                    }
                    


           
                $('.matrixLabel').show();
                },
                error: function(result) {
                    HoldOn.close();
                }
            });


        
          
        });
       

        $('body').on('keyup', '.specInput', function() {
            
            var data = $(this).attr("data-id");
            var value = $(this).val();
            var inputclass = parseInt(data) + 1;
            var Nextvalue = parseFloat(value) + 0.01;
            var specInputRowValue = $('.spec' + data).val();

            if (value != "" && value > 0) {
                $('.spec' + inputclass).val(Nextvalue.toFixed(2));
            } else {
                $('.spec' + inputclass).val("");

            }




        });


        $('body').on('click', '.clone-btn', function() {
            
            
           var tmpFromData =  framData;
           framData++;
                      
            
            $(".spec"+tmpFromData).addClass("spec"+framData);
            $(".specInput"+tmpFromData).addClass("specInput"+framData);
            $(".3kg"+tmpFromData).addClass("3kg"+framData);
            $(".5kg"+tmpFromData).addClass("5kg"+framData);


            $(".spec"+framData).removeClass("spec"+tmpFromData);
            $(".specInput"+framData).removeClass("specInput"+tmpFromData);
            $(".3kg"+framData).removeClass("3kg"+tmpFromData);
            $(".5kg"+framData).removeClass("5kg"+tmpFromData);
            framData--;
            if($(".specData").val() == "20"){
                $('.dynamicFormInput').append(
                '<div class="dynamicFormInputRatio' + framData + '">' +
                '<div class="col-md-2"><input type="text" class="form-control initialInput spec'+framData+'"  value="0" readonly></div>' +
                '<div class="col-md-1" style="text-align: left; margin-top: 1%"; > ha</div>' +
                '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="'+framData+'"  class="form-control specInput specInput'+framData+'" ></div>' +
                '<div class="col-md-1" style="text-align: left; margin-top: 1%"; > ha</div>' +
                '<div class="col-md-2" > <input type="number" placeholder="Bags"   class="form-control 3kg 3kg'+framData+'" ></div>' +                
                '<button class="btn btn-success btn-sm clone-btn cloneBtn' + framData +'" data-id="' + framData +'" id="clone-btn"><i' +
                '    class="fa fa-plus-square"></i></button>' +
                '    <button  data-id="' + framData +
                '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn' + framData +'" id="delete-clone-btn"><i' +
                '               class="fa fa-times"></i></button>' +
                '<br> <br>' +
                '</div>'
            );
            }else{
                $('.dynamicFormInput').append(
                '<div class="dynamicFormInputRatio' + framData + '">' +
                '<div class="col-md-2"><input type="text" class="form-control initialInput spec'+framData+'"  value="0" readonly></div>' +
                '<div class="col-md-1" style="text-align: left; margin-top: 1%"; > ha</div>' +
                '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id="'+framData+'"  class="form-control specInput specInput'+framData+'" ></div>' +
                '<div class="col-md-1" style="text-align: left; margin-top: 1%"; > ha</div>' +
                '<div class="col-md-2" > <input type="number" placeholder="Bags"   class="form-control 3kg 3kg'+framData+'" ></div>       ' +
                '<div class="col-md-2" > <input type="number" placeholder="Bags"  class="form-control 5kg 5kg'+framData+'" ></div> ' +
                '<button class="btn btn-success btn-sm clone-btn cloneBtn' + framData +'" data-id="' + framData +'" id="clone-btn"><i' +
                '    class="fa fa-plus-square"></i></button>' +
                '    <button  data-id="' + framData +
                '" class="btn btn-danger btn-sm delete-clone-btn deleteCloneBtn' + framData +'" id="delete-clone-btn"><i' +
                '               class="fa fa-times"></i></button>' +
                '<br> <br>' +
                '</div>'
            );
            }
            
            framData++;
            $("#delivery_date" + framData).datepicker({
                dateFormat: 'yy-mm-dd'
            });
        });

        $('body').on('click', '.delete-clone-btn', function() {
           
            var dataF = $(this).attr("data-id");
        
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var token = $('#_token').val();
                    var rmvData = dataF;
                    $(this).closest('.dynamicFormInputRatio' + dataF + '').remove();                    
                   
                    while(rmvData !=framData){
                       
                        if(rmvData>20){
                            break;
                        }
                       
                        var tmpRmData0 = rmvData; //2
                        var tmpRmData = rmvData++; 
                        tmpRmData++;//3
                        
                       var tmpRmData2 = tmpRmData; //3 

                        $(".spec"+tmpRmData).addClass("spec"+tmpRmData0);
                        $(".specInput"+tmpRmData).addClass("specInput"+tmpRmData0);
                        $(".3kg"+tmpRmData).addClass("3kg"+tmpRmData0);
                        $(".5kg"+tmpRmData).addClass("5kg"+tmpRmData0);
                        $(".cloneBtn"+tmpRmData).addClass("cloneBtn"+tmpRmData0);
                        $(".deleteCloneBtn"+tmpRmData).addClass("deleteCloneBtn"+tmpRmData0);
                        $('.cloneBtn'+tmpRmData).attr("data-id",tmpRmData0)
                        $('.specInput'+tmpRmData).attr("data-id",tmpRmData0)                        
                        $('.deleteCloneBtn'+tmpRmData).attr("data-id",tmpRmData0)
                        $(".dynamicFormInputRatio"+tmpRmData).addClass("dynamicFormInputRatio"+tmpRmData0);

                        
                        
                        tmpRmData--;
                        
                        $(".spec"+tmpRmData2).removeClass("spec"+tmpRmData2);
                        $(".specInput"+tmpRmData2).removeClass("specInput"+tmpRmData2);
                        $(".3kg"+tmpRmData2).removeClass("3kg"+tmpRmData2);
                        $(".5kg"+tmpRmData2).removeClass("5kg"+tmpRmData2);
                        $(".cloneBtn"+tmpRmData2).removeClass("cloneBtn"+tmpRmData2);
                        $(".deleteCloneBtn"+tmpRmData2).removeClass("deleteCloneBtn"+tmpRmData2);
                        $(".dynamicFormInputRatio"+tmpRmData2).removeClass("dynamicFormInputRatio"+tmpRmData2);
                        
                    }
                    framData--;
                    console.log(framData);
                }
                var checkerData = 0;
                while(checkerData < framData){
                    $('.specInput'+checkerData).keyup();
                    checkerData++;
                }
            });



        });
    </script>
@endpush
