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
                    <h2>Delivery Acceptance</h2>
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
                                    <select name="po" id="po" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a PO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- 
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
                            </div> --}}

                        <br>
                        <div class="formData" style="display: block">
                            <div id="formVariety">

                            </div>


                            <label class="control-label col-md-1 col-sm-1 col-xs-1"></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <button style="width: 20%" class="btn btn-success btn-sm" id="save-btn"><i
                                        class="fa fa-download"></i> Save</button>
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
        localStorage.removeItem("dataset");


        var token = "{{ csrf_token() }}";


        $('#save-btn').click(function() {
            var confirmDeliveryId =$('.confirmDeliveryId');
            var deliver_volume = $('.deliver_volume');
            var podata = $('#po').val();
            var confirmDeliveryIdArray = [];
            var deliver_volumeArray = [];

            for (let index = 0; index < deliver_volume.length; index++) {
                   if($(deliver_volume[index]).val()>0){
                    confirmDeliveryIdArray.push($(confirmDeliveryId[index]).val())
                    deliver_volumeArray.push($(deliver_volume[index]).val())
                   }          
            }

             var province = $('#provinceNrp').val();
            var municipal = $('#municipalityNrp').val();
            var po = $('#po').val();
            HoldOn.open(holdon_options);
            $.ajax({
                type: "POST",
                url: "{{ url('nrp-save-delivery') }}",
                data: {
                    _token: token,
                    volume: deliver_volumeArray,
                    id: confirmDeliveryIdArray
                },
                success: function(response) {
                    if (response == "Exhausted") {
                        alert("Warning Balance Exhausted");                       
                    }
                    if (response == "added") {
                        $('#deliver_volume').val("")
                        $('#deliver_package').val(0);
                        $('#deliver_sub_package').val(0);
                        $('#po').val(0).change()
                        $('#po').val(podata).change();
                        tblLoad(province, municipal, podata);
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
                    municipal: municipal,
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
            $("#formVariety").empty();
            var province = $('#provinceNrp').val();
            var municipal = $('#municipalityNrp').val();
            var po = $('#po').val();
            tblLoad(province, municipal, po);
            HoldOn.open(holdon_options);
            $.ajax({
                type: 'POST',
                url: "{{ route('nrp-get-variety-no-date') }}",
                data: {
                    _token: token,
                    province: province,
                    municipal: municipal,
                    po: po,
                },
                dataType: 'json',
                success: function(result) {
                     localStorage.setItem("dataset", JSON.stringify(result));

                    //1 Bag (15kg) / 3 Packs(5kg)
                    //1 Bag (15kg) / 5 Packs(3kg)
                    //1 Bag (20kg) / 4 Packs(5kg)
                    //1 Bag (18kg) / 3 Packs(6kg)
                    //1 Bag (18kg) / 6 Packs(3kg)
                    var specDatainfo = "";
                    $.each(result, function(i, d) {
                    if(d.package == 15 && d.sub_package == 5){
                        specDatainfo =  "1 Bag (15kg) / 3 Packs(5kg)";
                    } else if(d.package == 15 && d.sub_package == 3){
                        specDatainfo =  "1 Bag (15kg) / 5 Packs(3kg)";
                    }else if(d.package == 20 && d.sub_package == 5){
                        specDatainfo =  "1 Bag (20kg) / 4 Packs(5kg)";
                    }else if(d.package == 18 && d.sub_package == 16){
                        specDatainfo =  "1 Bag (18kg) / 3 Packs(6kg)";
                    }else if(d.package == 18 && d.sub_package == 3){
                        specDatainfo =  "1 Bag (18kg) / 6 Packs(3kg)";
                    }
                        $('#formVariety').append('<div class="form-group">' +
                            '<input type="hidden" class="batchNumber" value="'+d.batchNumber+'">'+
                            '<input type="hidden" class="confirmDeliveryId" value="'+d.id+'">'+
                            '<div class="col-md-9">' +
                            '        <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety : ' +
                            d.variety + '</label>' +
                            '        <label class="control-label col-md-2' + d
                            .id + '"> '+specDatainfo+'</label>' +
                            '    </div>' +
                            
                            '</div>' +
                            '<div class="form-group">' +
                            '    <div class="col-md-9">' +
                            '        <label class="control-label col-md-3 col-sm-3 col-xs-3">Delivered Bags</label>' +
                            '        <div class="col-md-2">' +
                            '            <input type="number" class="form-control volumeData'+d.id+' deliver_volume" data-id="' +
                            d.id + '" id="deliver_volume" placeholder="0">' +
                            '        </div>' +                           
                            '        <label class="control-label col-md-2 balanceData' + d
                            .id + '">Undelivered Bags : ' + d.volume + ' </label>' +

                            '        <label class="control-label col-md-2 totalNoPackets' + d
                            .id + '">Total No. of Packets : 0 </label>' +
                            '        <label class="control-label col-md-2 totalVolume' + d
                            .id + '">Total Volume: 0 kg</label>' +

                            
                            

                            '        <br>' +
                            '        <br>' +
                            '        <br>' +
                            '    </div>' +
                            '</div>');
                    });
                    $('.formData').show();
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



        $('body').on('keyup', '.deliver_volume', function() {
            var formSet = $(this).attr("data-id");
            var dataset = localStorage.getItem("dataset");
            var deliver_volume = $(this).val();
           
            let obj = JSON.parse(dataset);
            $.each(obj, function(i, d) {
                if (d.id == formSet) {
                    var bags = d.volume;
                    var package = d.package;
                    var subPackage = d.sub_package;                   
                     var baseSubpackage = parseInt(package) / parseInt(subPackage);
                  
                    if (parseInt(deliver_volume) > 0 || deliver_volume != "") {
                        var subBags = parseInt(bags) - parseInt(deliver_volume);
                        $('.balanceData'+d.id).text("Undelivered Bags : " + subBags);
                        $('.deliver_sub_package'+d.id).val(parseInt(deliver_volume) * parseInt(baseSubpackage));

                        $('.totalNoPackets'+d.id).text("Total No. of Packets : " +parseInt(deliver_volume)*baseSubpackage);
                        
                        $('.totalVolume'+d.id).text("Total Volume: "+ parseInt(deliver_volume)*package +" kg");

                    } else {
                        $('.balanceData'+d.id).text("Undelivered Bags : " + parseInt(bags));
                        $('.deliver_sub_package'+d.id).val(0);
                        $('.totalNoPackets'+d.id).text("Total No. of Packets : 0");
                        $('.totalVolume'+d.id).text("Total Volume: 0 kg");
                    }
                    if ((bags) - parseInt(deliver_volume) < 0) {
                        $('.balanceData'+d.id).text("Undelivered Bags : " + parseInt(bags));
                        $('.deliver_sub_package'+d.id).val(0);
                        $('.volumeData'+d.id).val("");
                        $('.totalNoPackets'+d.id).text("Total No. of Packets : 0");
                        $('.totalVolume'+d.id).text("Total Volume: 0 kg");
                        
                        alert("Warning Balance Exhausted");
                       
                    }
                }
            });

        });

        function tblLoad(province, municipal, po, variety) {
            $('#seedtbl').DataTable({
                processing: true,
                "bDestroy": true,
                "autoWidth": false,
                serverSide: true,
                "ajax": {
                    "url": "{{ route('actual-delivery-list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {
                        "_token": "{{ csrf_token() }}",
                        "province": province,
                        "municipal": municipal,
                        "po": po,
                    }
                },
                columns: [{
                        data: 'seed_variety',
                        name: 'seed_variety'
                    },
                    {
                        data: 'package_bags',
                        name: 'package_bags'
                    },
                    {
                        data: 'sub_package_bags',
                        name: 'sub_package_bags'
                    },
                    {
                        data: 'date_created',
                        name: 'date_created'
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

        $('body').on('click', '.delete-btn', function() {        
        
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
                            url: "{{ url('delete-delivered') }}",
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
                               location.reload();
                            }
                        });
                }
            });  
         });
    </script>
@endpush
