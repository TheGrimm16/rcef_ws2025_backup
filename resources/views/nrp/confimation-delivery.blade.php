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
                    <h1>Seed Positioning</h1>
                    <strong><p >Province: {{ $commitmentData->province }} </p></strong>
                    <strong><p >Municipality: {{ $commitmentData->municipal }} </p></strong>
                    
                    <h2 id="balanceData">Balance: {{ $commitmentData->remainingVolume }} Bags</h2>                  
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">

                    <div class="form-group">
        
                        <br>
                        <div class="form_insecticides">
                            <div class="row">

                                <div class="form-group">
                                    <label class="control-label col-xs-1">PO #:</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control  po po0" id="po" name="po" placeholder="PO #">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-1">Supplier Name:</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control supplierName supplierName0" id="supplierName" name="supplierName" placeholder="Supplier">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-1">Regional Specification:</label>
                                    <div class="col-md-6">
                                        <select name="specData" id="specData" class="form-control specData">
                                            <option value="">Please Select a Specification</option>
                                            @foreach ($specInfo as $item)
                                            <option value="{{$item->seed_package."X".$item->seed_sub_package}}">{{$item->specLabel}}</option>                                                
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                               


                                <label class="control-label col-xs-1">Variety:</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control seed_variety seed_varietyData0" id="seed_variety"
                                        name="seed_variety" placeholder="Variety">
                                </div>
                      
                                <label class="control-label col-xs-1">Bags</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control volumeinfo volumeData0 deliver_volume" data-id="0" id="volume" name="volume"
                                        placeholder="0">
                                </div> 
                                
                                
                                <label class="control-label col-xs-1">Delivery Date</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control delivery_date delivery_date0" id="delivery_date0"
                                    autocomplete="off" name="deliveryDate" id="deliveryDate" placeholder="MM-DD-YYYY">
                                </div> 

                                <button class="btn btn-success btn-sm clone-btn" data-id="0" id="clone-btn"><i
                                        class="fa fa-plus-square"></i></button>
                            </div>
                        </div>

                        <br>
                        <div class="form-group pull-right">
                            <label class="control-label col-xs-1"></label>

                            <button class="btn btn-success btn-sm" data-id="0" id="save-btn"><i class="fa fa-download"></i>
                                Save</button>
                            <a href= "../seed-postion" class="btn btn-danger btn-sm" id="cancel-btn"><i class="fa fa-ban"></i> Cancel</a>

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
        localStorage.setItem("fdata", "{{ $commitmentData->remainingVolume }}");
        var framData = 0;
        var packageDataSpec = 0;
        var FdataVolume = localStorage.getItem("fdata")

        $('body').on('keyup', '.deliver_volume', function() {
            resetter();
        });
        function resetter(){
            var volume = $('.volumeinfo'); 
            var finalData = 0;                       
            for (let index = 0; index < volume.length; index++) {              
                var baseVolume = $(volume[index]).val();
                if(baseVolume=='' || baseVolume ==0){
                    baseVolume = 0;
                }
                finalData =     parseInt(finalData)+parseInt(baseVolume);       
                
            }
            var totalvolume  = parseInt(FdataVolume)-parseInt(finalData);
            $('#balanceData').text("Balance: "+ totalvolume+" Bags");
        }
        
        $('#save-btn').click(function() {
            HoldOn.open(holdon_options);
            var commitmentId = window.location.pathname.split("/").pop();
            var commimentinfo = [];
            var chekerData = 0;
            var provinceNrp = $('.provinceNrp').val();
            var po = $(".po");
            var supplierName = $(".supplierName");
            var seed_variety = $('.seed_variety');
            var volume = $('.volumeinfo');
            var delivery_date = $('.delivery_date');
            var package = $('.package');
            var sub_package = $('.sub_package');
            var specDataValue = $('.specData');
            var totaldata = 0;
            for (let index = 0; index < seed_variety.length; index++) {
                if($(volume[index]).val() ==0 || $(volume[index]).val() ==""){
                    alert("ALl fiels are required!");
                }else{
                var specData = $(specDataValue[index]).val();
                var obj = new Object();
                obj.po = $(po[index]).val();;
                obj.commitmentId = commitmentId;
                obj.province = '{{ $commitmentData->province }}';
                obj.municipality = '{{ $commitmentData->municipal }}';
                obj.supplierName = $(supplierName[index]).val();
                obj.seed_variety = $(seed_variety[index]).val();
                obj.volume = $(volume[index]).val();
                obj.delivery_date = $(delivery_date[index]).val();
            
                
                if(specData == "15X5"){
                    obj.package = 15;
                    obj.sub_package = 5;
                }
                if(specData== "15X3"){
                    obj.package = 15;
                    obj.sub_package = 3;
                }
                if(specData == "20X5"){
                    obj.package = 20;
                    obj.sub_package = 5;
                }
                if(specData== "18X3"){
                    obj.package = 18;
                    obj.sub_package = 3;
                }
                if(specData== "18X6"){
                    obj.package = 18;
                    obj.sub_package = 6;
                }
                commimentinfo.push(obj);
                totaldata = parseInt(totaldata) + parseInt($(volume[index]).val());
                }
               
            }
            if(chekerData == 0){
                if (totaldata > localStorage.getItem("fdata")) {
                alert("Volume Exhausted")
            } else {
                $.ajax({
                    type: "POST",
                    url: "{{ url('delivery-confrimation-save') }}",
                    data: {
                        _token: token,
                        commimentinfo: commimentinfo
                    },
                    success: function(response) {
                        if (response == "success") {
                            alert("Transaction Completed");
                            HoldOn.close();
                            window.location.href = "../seed-postion"
                        } else {
                            alert("Transaction Error!");
                            HoldOn.close();
                        }
                    }
                });

            }
            }
            HoldOn.close();
        });


        $("#delivery_date0").datepicker({
            dateFormat: 'yy-mm-dd'
        });
        
      
     /*    $('body').on('change', '.specData', function() {
            var data = $(this).val()           
            CheckerData(data)
        }); */
        function CheckerData(data){
            
                if(data == "15X5"){
                packageDataSpec = 15;
                }
                if(data == "15X3"){
                    packageDataSpec = 15;
                }
                if(data == "20X5"){
                    packageDataSpec = 20;
                }
                if(data == "18X3"){
                    packageDataSpec = 18;
                }
        }

       
        $('body').on('click', '.clone-btn', function() {

            var datastate = $(this).attr("data-id")
            var formdata = $('.specClass'+datastate).val();                   
            CheckerData(formdata)           
            var data = $('.volumeData'+datastate).val()%packageDataSpec;            
          /*   if(data>0){
                alert("Volume is not valid your data must divisible by "+packageDataSpec);
                $('.volumeData'+datastate).val("");
            }else if($('.seed_varietyData'+datastate).val() == ""){
                alert("Variety Required");
            }else if($('.specClass'+datastate).val() == 0){
                alert("Specification Required");
            }else if($('.volumeData'+datastate).val() == 0){
                alert("Volume Required");
            }else{ */
                framData++;
            $('.form_insecticides').append(
                '<div class="varietyForm' + framData + '">' +
                '<br><div class="row">' +

                '<div class="form-group">'+
                    '<label class="control-label col-xs-1">PO #:</label>'+
                '<div class="col-md-6">'+
                '    <input type="text" class="form-control  po p'+framData+'" id="po" name="po" placeholder="PO #">'+
                '</div>'+
                '</div>'+
                '<div class="form-group">'+
                '<label class="control-label col-xs-1">Supplier Name:</label>'+
                '<div class="col-md-6">'+
                '    <input type="text" class="form-control supplierName supplierName'+framData+'" id="supplierName" name="supplierName" placeholder="Supplier">'+
                '</div>'+
                '</div>'+

                '<div class="form-group">'+
                '    <label class="control-label col-xs-1">Regional Specification:</label>'+
                '    <div class="col-md-6">'+
                '        <select name="specData" id="specData" class="form-control specData">'+
                '            <option value="">Please Select a Specification</option>'+
                '            @foreach ($specInfo as $item)'+
                '            <option value="{{$item->seed_package."X".$item->seed_sub_package}}">{{$item->specLabel}}</option>                                                '+
                '            @endforeach'+
                '        </select>'+
                '    </div>'+
                '</div>'+
                '<label class="control-label col-xs-1">Variety:</label>' +
                '<div class="col-md-2">' +
                '    <input type="text" class="form-control seed_variety seed_varietyData'+framData+'" id="seed_variety" name="seed_variety"' +
                '        placeholder="Variety">' +
                '</div>' +                
                '<label class="control-label col-xs-1">volume(Kg)</label>' +
                '<div class="col-md-2">' +
                '    <input type="number" class="form-control volume volumeinfo volumeData'+framData+' deliver_volume" id="volume"  data-id="'+framData+'" name="volume"' +
                '        placeholder="0">' +
                '</div>' +    
                '<label class="control-label col-xs-1">Delivery Date</label>'+
                '    <div class="col-md-2">'+
                '        <input type="text" class="form-control delivery_date delivery_date'+framData+'" id="delivery_date'+framData+'"'+
                '        autocomplete="off" name="deliveryDate" id="deliveryDate" placeholder="MM-DD-YYYY">'+
                '</div>     '+
                '<button class="btn btn-success btn-sm clone-btn"  data-id="'+framData+'"  id="clone-btn"><i' +
                '        class="fa fa-plus-square"></i></button>' +
                '<button  data-id="' + framData + '" class="btn btn-danger btn-sm" id="delete-clone-btn"><i' +
                '        class="fa fa-times"></i></button>' +
                '</div>' +
                '</div>'
            );

            $("#delivery_date"+framData).datepicker({
                dateFormat: 'yy-mm-dd'
            });


           /*  }  */           
          
        });
        $("#dateData" + framData).datepicker({
                dateFormat: 'yy-mm-dd'
            });
        $('body').on('click', '#delete-clone-btn', function() {

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
                    $(this).closest('.varietyForm' + dataF + '').remove();

                    resetter();

                }
            });



        });
    </script>
@endpush
