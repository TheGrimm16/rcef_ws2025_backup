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
                    <h2>Delivery</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">

                    <div class="form-group">
                        <div class="form-group">
                        <div class="col-md-9">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Province:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="provinceNrp" id="provinceNrp" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a province</option>
                                    @foreach ($provinces as $provinces)
                                        <option value="{{ $provinces->province }}">{{ $provinces->province }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        </div>

                        <div class="form-group">
                        <div class="col-md-9">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="municipalityNrp" id="municipalityNrp" class="form-control"
                                    data-parsley-min="1">
                                    <option value="0">Please select a Municipality</option>
                                </select>
                            </div>
                        </div>
                        </div>
                        <div class="form-group">
                        <div class="col-md-9">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">PO #:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="po" id="po" class="form-control"
                                    data-parsley-min="1">
                                    <option value="0">Please select a PO</option>
                                </select>
                            </div>
                        </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-9">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety Name:</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="variety" id="variety" class="form-control"
                                        data-parsley-min="1">
                                        <option value="0">Please select a Variety</option>
                                    </select>
                                </div>
                            </div>
                            </div>

                            <br>
                            <div class="formData" style="display: none">
                                <div class="form-group">
                                    <div class="col-md-12">   
                                    <h1 class="control-label col-md-3 col-sm-3 col-xs-3" id="availableBalance">Available Balance</h1>
                                    </div>
                                </div>
                                                             
                                    <div class="form-group">
                                        <div class="col-md-9">   
                                        <h2 class="control-label col-md-3 col-sm-3 col-xs-3">Delivery Details</h2>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-9">                                       
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Delivered Bags</label> 
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="deliver_volume" placeholder="0">
                                            </div>       
                                            <label class="control-label col-md-2" >Sub Package(bags)</label>    
                                            <div class="col-md-2 ">
                                                <input type="text" class="form-control" id="deliver_sub_package" readonly>
                                            </div>
                                            <br>
                                            <br>
                                            <br>
                                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                                            <div class="col-md-9 col-sm-9 col-xs-9">
                                                <button style="width: 20%" class="btn btn-success btn-sm" id="save-btn"><i
                                                        class="fa fa-download"></i> Save</button>            
                                            </div>
                                        </div>
                                        
                                        </div>

                            
                            </div>
                          
                    </div>

                    <div class="form-group">
                        <table class="table table-striped table-bordered tbl" id="seedtbl">
                            <thead>
                                <tr>
                                    <th style="width: auto;">Variety</th>
                                    <th style="width: auto;">total bags</th>                                  
                                    <th style="width: auto;">total Sub Package</th>
                                    <th style="width: auto;">Delivery Date</th>
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
        $('select[name="provinceNrp"]').select2();
        $('select[name="municipalityNrp"]').select2();
        $('select[name="po"]').select2();
        $('select[name="variety"]').select2();
        $("#deliveryDate").datepicker();
        $('#seedtbl').DataTable();
        localStorage.removeItem("volume");
        localStorage.removeItem("package");
        localStorage.removeItem("subPackage");
        localStorage.removeItem("confirmDeliveryId");
        localStorage.removeItem("fdata");
        
        var token = "{{ csrf_token() }}";


        $('#save-btn').click(function(){   
            var confirmDeliveryId =  localStorage.getItem("confirmDeliveryId");     
            var deliver_volume = $('#deliver_volume').val();   
            HoldOn.open(holdon_options); 
            $.ajax({
                type: "POST",
                url: "{{ url('nrp-save-delivery') }}",
                data: {
                    _token:token,
                    volume:deliver_volume,
                    id:confirmDeliveryId
                },
                success: function(response) {
                    if(response=="Exhausted"){
                    alert("Warning Balance Exhausted");
                    $('#volume').text(parseInt(volume));
                    $('#deliver_package').val(0);
                    $('#deliver_sub_package').val(0);
                }
                if(response == "added"){
                    $('#deliver_volume').val("")
                    $('#deliver_package').val(0);
                    $('#deliver_sub_package').val(0);
                    $('#variety').val(0).change()
                }
                HoldOn.close();
                }
            });
            HoldOn.close();
        });



        $('#provinceNrp').change(function() {        
            var province = $('#provinceNrp').val();
            $("#municipalityNrp").empty().append("<option value='0'>Please select a Municipality</option>");
            HoldOn.open(holdon_options);
            $.ajax({
                type: 'POST',
                url: "{{ route('nrp-get-muni') }}",
                data: {
                    _token: token,
                    province: province,                    
                },
                dataType: 'json',
                success: function(result) {
                    $.each(result, function(i, d) {
                        $('select[id="municipalityNrp"]').append('<option value="' + d
                            .municipality + '">' + d.municipality + '</option>');
                    });
                    HoldOn.close();

                },
                error: function(result) {
                    HoldOn.close();
                }
            });
        });

        $('#municipalityNrp').change(function() {        
            var province = $('#provinceNrp').val();
            var municipal = $('#municipalityNrp').val();
            $('.formData').hide();
            $("#po").empty().append("<option value='0'>Please select a PO</option>");
            HoldOn.open(holdon_options);
            $.ajax({
                type: 'POST',
                url: "{{ route('nrp-get-po') }}",
                data: {
                    _token: token,
                    province: province, 
                    municipal:municipal,                   
                },
                dataType: 'json',
                success: function(result) {
                    
                    $.each(result, function(i, d) {
                        $('select[id="po"]').append('<option value="' + d
                            .po + '">' + d.po + '</option>');
                    });
                    HoldOn.close();

                },
                error: function(result) {
                    HoldOn.close();
                }
            });
        });


        $('#po').change(function() {        
            var province = $('#provinceNrp').val();
            var municipal = $('#municipalityNrp').val();
            var po = $('#po').val();
            $('.formData').hide();
            $("#variety").empty().append("<option value='0'>Please select a Variety</option>");
            HoldOn.open(holdon_options);
            $.ajax({
                type: 'POST',
                url: "{{ route('nrp-get-variety') }}",
                data: {
                    _token: token,
                    province: province, 
                    municipal:municipal,                   
                    po:po,                   
                },
                dataType: 'json',
                success: function(result) {
                    
                    $.each(result, function(i, d) {
                        $('select[id="variety"]').append('<option value="' + d
                            .variety + '">' + d.variety + '</option>');
                    });
                    HoldOn.close();

                },
                error: function(result) {
                    HoldOn.close();
                }
            });
                    $('#deliver_volume').val("")
                    $('#deliver_package').val(0);
                    $('#deliver_sub_package').val(0);
        });


        $('#variety').change(function() {      
            $('.formData').hide();  
            var province = $('#provinceNrp').val();
            var municipal = $('#municipalityNrp').val();
            var po = $('#po').val();
            var variety = $('#variety').val();
            
            HoldOn.open(holdon_options);
            $.ajax({
                type: 'POST',
                url: "{{ route('nrp-get-delivery-details') }}",
                data: {
                    _token: token,
                    province: province, 
                    municipal:municipal,                   
                    po:po,                   
                    variety:variety,                   
                },
                dataType: 'json',
                success: function(result) {
                 
                        localStorage.setItem("volume", result.volume);
                        localStorage.setItem("package", result.package);
                        localStorage.setItem("subPackage", result.sub_package);
                        localStorage.setItem("confirmDeliveryId", result.id);
                        $('#availableBalance').text("Available Balance :"+result.volume);
                        $('.formData').show();

                        
                        tblLoad(province,municipal,po,variety);
                    
                    HoldOn.close();

                },
                error: function(result) {
                    HoldOn.close();
                }
            });
        });

        $( "#deliver_volume" ).keyup(function() {
            var bags =  localStorage.getItem("volume");
            var package =  localStorage.getItem("package");
            var subPackage =  localStorage.getItem("subPackage");
            var deliver_volume= $('#deliver_volume').val();
            var baseSubpackage = parseInt(package)/parseInt(subPackage);

            if(parseInt(deliver_volume)>0 || $('#deliver_volume').val() !=""){
                var subBags = parseInt(bags)-parseInt(deliver_volume);
                $('#availableBalance').text("Available Balance :"+subBags);  
                $('#deliver_sub_package').val(parseInt(deliver_volume)*parseInt(baseSubpackage));  
                             
            }else{
                $('#availableBalance').text("Available Balance :"+parseInt(bags));
                $('#deliver_package').val(0);
                $('#deliver_sub_package').val(0);              
            }

            if((bags)-parseInt(deliver_volume)<0){
                    alert("Warning Balance Exhausted");
                    $('#availableBalance').text("Available Balance :"+parseInt(bags));
                    $('#deliver_volume').val("");
                    $('#deliver_sub_package').val(0);
                }
        });

    function tblLoad(province,municipal,po,variety){
        $('#seedtbl').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,			
			"ajax": {
                        "url": "{{ route('actual-delivery-list') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",                         
                            "province": province,                         
                            "municipal": municipal,                         
                            "po": po,                         
                            "variety": variety,                         
                        }
                    },
			columns: [
				{data: 'seed_variety', name: 'seed_variety'},
				{data: 'package_bags', name: 'package_bags'},
				{data: 'sub_package_bags', name: 'sub_package_bags'},					
				{data: 'delivery_date', name: 'delivery_date'},					
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
    }
           
    </script>
@endpush
