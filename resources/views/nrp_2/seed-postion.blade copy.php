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
                    <h2>Seed Positioning</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">

                    <div class="form-group">

                        <div class="form-group">
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

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="municipalityNrp" id="municipalityNrp" class="form-control"
                                    data-parsley-min="1">
                                    <option value="0">Please select a Municipality</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">No of Bags:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="number" class="form-control" id="nrpVolume" name="nrpVolume" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Target Delivery Scehdule:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" autocomplete="off" name="deliveryDate"
                                    id="deliveryDate" placeholder="MM/DD/YYYY">
                            </div>
                        </div>

                        <div class="form-check">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <input class="form-check-input" type="checkbox" value="" id="withPo">
                            <label class="form-check-label" for="withPo">
                                with PO#
                            </label>
                        </div>

                        <br>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <button style="width: 10%" class="btn btn-success btn-sm" id="save-btn"><i
                                        class="fa fa-download"></i> Save</button>
                            </div>
                        </div>



                    </div>

                    <div class="form-group">
                        <table class="table table-striped table-bordered tbl" id="seedtbl">
                            <thead>
                                <tr>
                                    <th style="width: auto;">Province</th>
                                    <th style="width: auto;">Municipality</th>
                                    <th style="width: auto;">Total Bags</th>
                                    <th style="width: auto;">Confirm Delivery</th>
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


    <!-- Modal -->
<div class="modal fade" id="initailData" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Seed Packaging and Distribution Ratio</h5>
        </div>
        <div class="modal-body">
       

            <div class="form-group">
                <label>Specification</label>
                    <select name="specData" id="specData" class="form-control specData" data-parsley-min="1">
                        <option value="0">Please select a Spec</option>
                        <option value="15X5">1 Bag (15kg) / 3 Packs(5kg)</option>
                        <option value="15X3">1 Bag (15kg) / 5 Packs(3kg)</option>
                        <option value="20X5">1 Bag (20kg) / 4 Packs(5kg)</option>
                        <option value="18X6">1 Bag (18kg) / 3 Packs(6kg)</option>
                        <option value="18X3">1 Bag (18kg) / 6 Packs(3kg)</option>
                    </select>
            </div>
            <div class="form-group matrixLabel" style="display: none">
                    <h2>Seed Distribution Matrix</h2>
            </div>
           
            <div class="form-group ">
            
                <div class="row ratio">

                </div>
            </div>
           
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-save-setting">Save Settings</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="initailDataEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Seed Packaging and Distribution Ratio</h5>
        </div>
        <div class="modal-body">
       

            <div class="form-group">
                <label>Specification</label>
                    <select name="specData" id="specDataEdit" class="form-control specDataEdit" data-parsley-min="1">
                        <option value="0">Please select a Spec</option>
                        <option value="15X5">1 Bag (15kg) / 3 Packs(5kg)</option>
                        <option value="15X3">1 Bag (15kg) / 5 Packs(3kg)</option>
                        <option value="20X5">1 Bag (20kg) / 4 Packs(5kg)</option>
                        <option value="18X6">1 Bag (18kg) / 3 Packs(6kg)</option>
                        <option value="18X3">1 Bag (18kg) / 6 Packs(3kg)</option>
                    </select>
            </div>
            <div class="form-group matrixLabel" style="display: none">
                    <h2>Seed Distribution Matrix</h2>
            </div>
           
            <div class="form-group ">
            
                <div class="row ratioEdit">

                </div>
            </div>
           
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-save-setting">Save Settings</button>
        </div>
      </div>
    </div>
  </div>

  {{-- modal for initial --}}

     
  <div class="modal fade" id="ReviewData" tabindex="-1" role="dialog" aria-labelledby="ReviewData" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
         <center> <h3 class="modal-title" id="exampleModalLongTitle">Confirmation of Seed Packaging and <br> Distribution Ratio</h3></center>
        </div>
      
        <div class="modal-body">
            <center> <h2 id="headLabel">#####</h2></center>
            <br>
            <center> <strong> <p style="ont-size: 18px">Seed Distribution Matrix</p></strong></center>
            <div id="confrimForm">
               
            </div>
            
        </div>
        
        <div class="modal-footer">
            <center>
                <button type="button" class="btn btn-secondary editSpec">Edit Specification</button>
                <button type="button" class="btn btn-primary" onclick=" saveData()">Continue</button>
            </center>
          
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
        $("#deliveryDate").datepicker();
        var token = "{{ csrf_token() }}";
        tblLoad();
        initialData(); 
        var stateLoadData = 0;
        $('.editSpec').click(function() {

            let specDataInfo = localStorage.getItem("specDataInfo");
            let obj = JSON.parse(specDataInfo);

            $(".ratioEdit").empty();
            var x = 1;     
            
            $('#specDataEdit').val(obj[0]['seed_package']+"X"+obj[0]['seed_sub_package']).change();
            for (let index = 0; index < obj.length; index++) {
                var tmp = parseInt(obj.length);
                console.log(obj[index]);      
                if(index==0){
                    $(".ratioEdit").append(
                    '<div class="col-md-2"><input type="text" class="form-control initialInput spec'+index+'" value="0.01" readonly></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"; > ha</div>'+
                    '<div class="col-md-2"><input type="number" placeholder="0.0" dynamicInput data-id='+index+' class="form-control specInput " ></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+                
                    '<div class="col-md-2 range_volume" style="text-align: left; margin-top: 1%" data-id="'+obj[index]['range_volume']+'"> => '+obj[index]['range_volume']+' kg</div> <br> <br>');
                }else{
                    if (index != tmp) {
                    $(".ratioEdit").append(
                    '<div class="col-md-2"><input type="text" class="form-control initialInput  spec'+index+'" readonly></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+                    
                    '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id='+index+' class="form-control specInput"></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+
                    '<div class="col-md-2 range_volume" style="text-align: left; margin-top: 1%" data-id="'+obj[index]['range_volume']+'"> => '+obj[index]['range_volume']+' kg</div> <br> <br>');

                }else{
                    $(".ratioEdit").append(
                    '<div class="col-md-2"><input type="text" class="form-control initialInput  spec'+index+'" readonly></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+
                    '<div class="col-md-2"><input type="text" placeholder="0.0" data-id='+index+' class="form-control specInput" readonly  value = "1.0"></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+
                    '<div class="col-md-2 range_volume" style="text-align: left; margin-top: 1%" data-id="'+obj[index]['range_volume']+'"> => '+obj[index]['range_volume']+' kg</div> <br> <br>');

                }
                }                                
                x++;
            }


            $('#ReviewData').modal('toggle');          
            $('.matrixLabel').show();
            $('#initailDataEdit').modal('show');
        });
        $('#save-btn').click(function() {
                   $.ajax({
                type: "POST",
                url: "{{ url('reviewStatus') }}",
                data: {
                    _token: token,
                },
                success: function(response) {   
                    localStorage.setItem('specDataInfo',JSON.stringify(response))  ;
                    $('#confrimForm').empty();               
                    if(response.length > 0){     
                        $.each(response, function(i, d) {
                            $('#headLabel').text(d.specLabel)
                        $('#confrimForm').append(' <center> <p style="font-size: 18px">'+d.range_start+' ha to '+d.range_end+' ha  => '+d.range_volume+'kg</p> </center>');
                    });                  
                        $('#ReviewData').modal('show');
                        stateLoadData = 1;
                    }else{
                        saveData();
                    }
                }
            });
        
           
        });

        
        $('body').on('click', '.btn-save-setting', function() {
            HoldOn.open(holdon_options);
            var specDataText = $( "#specData option:selected" ).text();
            var specData = $('#specData').val();
            var initialInput = $('.initialInput');
            var specInput = $('.specInput');
            var range_volume = $('.range_volume');
            var arrayinitialInput = [];
            var arrayspecInput = [];
            var arrayrange_volume = [];

            for (let index = 0; index < specInput.length; index++) {
                   if($(specInput[index]).val()>0){
                    arrayinitialInput.push($(initialInput[index]).val());
                    arrayspecInput.push($(specInput[index]).val());
                    arrayrange_volume.push($(range_volume[index]).attr("data-id"));
                   }          
            }
            $.ajax({
                type: 'POST',
                url: "{{ route('get-save-ratio') }}",
                data: {
                    _token: token,
                    initialInput:arrayinitialInput,
                    specInput:arrayspecInput,
                    specData:specData,
                    specDataText:specDataText,
                    range_volume:arrayrange_volume,
                },                
                success: function(response) {
                    console.log(response);
                   if(response=="success"){
                      alert("Specification and distribution Matrix Successfully Saved")
                      HoldOn.close();
                      location.reload();
                   }else{
                    alert("Error!")
                   }
                },
                error: function(response) {
                    console.log(response);
                    HoldOn.close();
                }
            });

        });

        $('body').on('change', '.specData', function() {
            var data = $(this).val();
            var loop=0;           
            var multiplier = 0;
            if(data == "15X5"){
                loop = 3;
                multiplier=5;
            }
            if(data == "15X3"){
                loop = 5;
                multiplier=3;
            }
            if(data == "20X5"){
                loop = 4;
                multiplier=5;
            }
            if(data == "18X3"){
                loop = 6;
                multiplier=3;
            }
            if(data == "18X6"){
                loop = 3;
                multiplier=6;
            }
            $(".ratio").empty();
            var x = 1;            
            for (let index = 0; index < loop; index++) {
                var tmp = parseInt(loop)-1
                if(index==0){
                    $(".ratio").append(
                    '<div class="col-md-2"><input type="text" class="form-control initialInput spec'+index+'" value="0.01" readonly></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"; > ha</div>'+
                    '<div class="col-md-2"><input type="number" placeholder="0.0" dynamicInput data-id='+index+' class="form-control specInput " ></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+                
                    '<div class="col-md-2 range_volume" style="text-align: left; margin-top: 1%" data-id="'+multiplier*x+'"> => '+multiplier*x+' kg</div> <br> <br>');
                }else{
                    if (index != tmp) {
                    $(".ratio").append(
                    '<div class="col-md-2"><input type="text" class="form-control initialInput  spec'+index+'" readonly></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+                    
                    '<div class="col-md-2"><input type="number" placeholder="0.0"  data-id='+index+' class="form-control specInput"></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+
                    '<div class="col-md-2 range_volume" style="text-align: left; margin-top: 1%" data-id="'+multiplier*x+'"> => '+multiplier*x+' kg</div> <br> <br>');

                }else{
                    $(".ratio").append(
                    '<div class="col-md-2"><input type="text" class="form-control initialInput  spec'+index+'" readonly></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+
                    '<div class="col-md-2"><input type="text" placeholder="0.0" data-id='+index+' class="form-control specInput" readonly  value = "1.0"></div>'+
                    '<div class="col-md-1" style="text-align: left; margin-top: 1%"> ha</div>'+
                    '<div class="col-md-2 range_volume" style="text-align: left; margin-top: 1%" data-id="'+multiplier*x+'"> => '+multiplier*x+' kg</div> <br> <br>');

                }
                }                                
                x++;
            }
            $('.matrixLabel').show();
        });      
        $('body').on('keyup', '.specInput', function() {
            
            var data = $(this).attr("data-id");
            var value = $(this).val();
            var inputclass = parseInt(data)+1;
            var Nextvalue = parseFloat(value)+0.01;
            var specInputRowValue = $('.spec'+data).val();
            
            if(value!="" && value >0){            
                $('.spec'+inputclass).val(Nextvalue.toFixed(2));            
            }else{
                $('.spec'+inputclass).val("");

            }
            
           


        });
        

        function initialData(){
            $.ajax({
                type: 'POST',
                url: "{{ route('get-seed-ratio') }}",
                data: {
                    _token: token,
                },
                dataType: 'json',
                success: function(result) {
                   if(result==0){
                    $('#initailData').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                        $('#initailData').modal('show');
                   }else{
                    $('#initailData').modal('hide');
                   }
                },
                error: function(result) {
                    HoldOn.close();
                }
            });
        }
     
       
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

        function tblLoad() {
            $('#seedtbl').DataTable({
                processing: true,
                "bDestroy": true,
                "autoWidth": false,
                serverSide: true,
                //ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
                "ajax": {
                    "url": "{{ route('nrp-dop-list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {
                        "_token": "{{ csrf_token() }}",
                    }
                },
                columns: [{
                        data: 'province',
                        name: 'province'
                    },
                    {
                        data: 'municipal',
                        name: 'municipal'
                    },
                    {
                        data: 'volume',
                        name: 'volume'
                    },
                    {
                        data: 'confirmDelivery',
                        name: 'confirmDelivery'
                    },
                    {
                        data: 'delivery_date',
                        name: 'delivery_date'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }

     
        function saveData(){

            if(stateLoadData == 1){
                $.ajax({
                type: "POST",
                url: "{{ url('reviewStatusSave') }}",
                data: {
                    _token: token,
                },
                success: function(response) {

                }
            });
            }
            HoldOn.open(holdon_options);
            var provinceNrp = $('#provinceNrp').val();
            var municipalityNrp = $('#municipalityNrp').val();
            var nrpVolume = $('#nrpVolume').val();
            var deliveryDate = $('#deliveryDate').val();

            if (provinceNrp == 0) {
                alert("Province Required");
            } else if (municipalityNrp == 0) {
                alert("Municipal Required");
            } else if (nrpVolume <= 0 || nrpVolume == '') {
                alert("Volume Required");
            } else if (deliveryDate == "") {
                alert("Volume Required");
            } else {
                $.ajax({
                    type: "POST",
                    url: "{{ url('nrp-save-commitment') }}",
                    data: {
                        _token: token,
                        provinceNrp: provinceNrp,
                        municipalityNrp: municipalityNrp,
                        nrpVolume: nrpVolume,
                        deliveryDate: deliveryDate,
                    },
                    success: function(response) {
                        const arrayString = response.split("%%")
                        if (arrayString[0] == "success") {
                            alert("Transaction Completed");
                            if ($('#withPo').is(':checked')) {
                                window.location.href = "delivery-confrimation/" + arrayString[1];
                            } else {
                                $('#seedtbl').DataTable().clear();                                
                                location.reload();
                                HoldOn.close();
                            }
                        } else {
                            alert("Transaction Error!");
                            HoldOn.close();
                        }
                    }
                });
            }
            HoldOn.close();
        }
        $('body').on('click', '.delete-funtion', function() {

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
                    $.ajax({
                        type: "POST",
                        url: "{{ url('delete-dop') }}",
                        data: {
                            _token: token,
                            id:dataF,
                        },
                        success: function(response) {
                            if (response == "deleted") {
                                Swal.fire(
                                    'Deleted!',
                                    'successfully Deleted this data!',
                                    'success'
                                );
                            }
                            tblLoad();
                        }
                    });
                }
            });
        });
    </script>
@endpush
