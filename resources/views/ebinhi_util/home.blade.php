@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

  <style>
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
    .x_content {
        padding: 0 5px 6px;
        float: left;
        clear: both;
        margin-top: 0; 
    }
    .btn-secondary, .btn-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        cursor: auto;
        opacity: 0.8;
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="row">
        <div class="col-md-3">
            <div class="x_panel">
                <div class="x_title"> <br>
                    <h2>
                        Search Location and DOP
                    </h2>
                    <div class="clearfix"></div>
                </div><br>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row">
                        <select name="province_select" id="province_select" class="form-control">
                            <option value="0" selected>Please select Province</option>
                            @foreach ($provinces as $row)
                                <option value="{{$row->province}}">{{$row->province}}</option>    
                            @endforeach
                        </select>
                    </div><br>
                    
                    <div class="row">
                        <select name="municipality" id="municipality" class="form-control">
                            <option value="0">Please select a Municipality</option>
                        </select>
                    </div><br>

                    <div class="row">
                        <select name="dop_name" id="dop_name" class="form-control province_select">
                            <option value="0">Please select a DOP</option>
                        </select>
                    </div> <br> 

                    <div class="row">
                        <select name="select2_coop" id="select2_coop" style="width: 100%;" class="form-control">
                            <option value= "0">Please select Cooperative</option>

                        </select>
                    </div><br> 

                    <div class="row">
                        <button class="btn btn-success btn-block" id="filter_btn"><i class="fa fa-search"></i> Filter</button>
                    </div>
                   
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        Ebinhi Tools
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <div class="row">
                        <div class="col-md-10">
                            <select name="select2_farmer" id="select2_farmer" style="width: 100%;" class="form-control">
                                <option value= "0">search by rsbsa/name/qrcode...</option>
         
                            </select>
                        </div>
    
                        <div class="col-md-2">
                            <button class="btn btn-success btn-block" id="search_farmer"><i class="fa fa-search"></i> Search</button>
                        </div>
                    </div>
                    <div class="row">
                        <div id="card_data"></div>

                    </div>
                </div>
          </div>

            <div class="seed_tag_add" style="display: none">
                <div class="x_panel" >
                    <div class="x_title">
                        <h2>
                            Update Bags Claim
                        </h2>
                   
                        <div class="clearfix"></div>
                    </div>
                    <br>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="seed_tags" id="seed_tags" class="form-control" style="width: 100%;">
                                    <option value="0" selected>Select Seedtag</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="seed_variety" id="seed_variety" class="form-control" style="width: 100%;">
                                    <option value="0">Select seed variety</option>
                                </select>
                            </div> 
                            <div class="col-md-3">
                                <select name="qr_code" id="qr_code" class="form-control" style="width: 100%;">
                                    <option value="0">Select QR code</option>
                                </select>
                            </div>
        
                            
        
                            <div class="col-md-2">
                                <button class="btn btn-success btn-block" id="btn_add"><i class="fa fa-plus"></i> Add</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="" id="card_data2"></div>
                        
                        <br> 
                       
                        </div>
                    </div>
                    {{-- <div class="col-md-2">
                        <button class="btn btn-success btn-block" id="save_all"><i class="fa fa-save"></i> save</button>
                    </div> --}}
                </div>
                
            </div>
            
        </div>
    </div>


        {{-- <div class="col-md-9"> --}}
            
        {{-- </div> --}}

 

   

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

   $("#select2_farmer").select2();
   $("#seed_tags").select2();
   $("#seed_variety").select2();
   $("#qr_code").select2();
   $("#select2_coop").select2();


   
   

   

        $("#province_select").on("change", function(e){          
            var province = $("#province_select").val();
            $("#municipality").empty().append("<option value='0'>loading municipalities...</option>");
            $("#dop_name").empty().append("<option value='0'>loading dop...</option>");
            $("#select2_coop").empty().append("<option value='0'>loading seed coop...</option>");
            

            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province
                },
                success: function(data){
                    $("#municipality").empty().append("<option value='0'>Select a municipality</option>");
                    $("#dop_name").empty().append("<option value='0'>Select DOP</option>");
                    $("#select2_farmer").empty().append("<option value='0'>search by rsbsa/name/qrcode...</option>");
                    $("#select2_coop").empty().append("<option value='0'>Select Seed Cooperative</option>");
                    $("#municipality").append(data);
                }
            });
        });

        $("#municipality").on("change", function(e){

            var province = $("#province_select").val();
            var municipality = $("#municipality").val();
            $("#dop_name").empty().append("<option value='0'>loading DOP's...</option>");
            $("#select2_coop").empty().append("<option value='0'>loading seed coop...</option>");
    
            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.dop') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality
                },
                success: function(data){
                    $("#dop_name").empty().append("<option value='0'>Please select DOP</option>");
                    $("#select2_farmer").empty().append("<option value='0'>search by rsbsa/name/qrcode...</option>");
                    $("#select2_coop").empty().append("<option value='0'>Select Seed Cooperative</option>");
                    $("#dop_name").append(data);
                }
            });
        });


        $("#dop_name").on("change", function(e){
           var province = $("#province_select").val();
           var municipality = $("#municipality").val();
           var dop_name = $("#dop_name").val();

            $("#select2_coop").empty().append("<option value='0'>loading seed coop...</option>");

            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.coops') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality,
                    dop_name: dop_name
                },
                success: function(data){
                    $("#select2_coop").empty().append("<option value='0'>Select Seed Cooperative</option>");
                    $("#select2_coop").empty().append(data);
                    // HoldOn.close()
                    
                }
            });
           
        });
        

        $("#filter_btn").on("click", function(e){
            HoldOn.open(holdon_options) 
           var province = $("#province_select").val();
           var municipality = $("#municipality").val();
           var dop_name = $("#dop_name").val();
           var coop = $("#select2_coop").val();

           if(province == "0" || municipality == "0" || dop_name == "0" || coop == "0"){
               alert("Please fill-up all the required parameters.");
               HoldOn.close()
           }else{

            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.select2') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province: province,
                    municipality: municipality,
                    dop_name: dop_name,
                    coop: coop
                },
                success: function(data){
                    $("#select2_farmer").empty().append("<option value='0'>search by rsbsa/name/qrcode...</option>");
                    $("#select2_farmer").append(data.return_str);
                    // $("#card_data").empty().append(data.return_card);
                    $("#card_data").empty();
                    $("#card_data2").empty();
                    $(".seed_tag_add").css("display", "none");
                    
                    HoldOn.close()
                    
                }
            });

            // load_seedtags(province,municipality,coop);    
           }
        });


        $("#search_farmer").on("click", function(e){

           HoldOn.open(holdon_options) 
           var province = $("#province_select").val();
           var municipality = $("#municipality").val();
           var dop_name = $("#dop_name").val();
           var coop = $("#select2_coop").val();

            var farmer_data = $("#select2_farmer").val();
            
           if(farmer_data == "0"){
               alert("Please fill-up all the required parameters.");
           }else{

            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.search_farmer') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    farmer_data: farmer_data

                },
                success: function(data){
                    // $("#select2_farmer").empty().append("<option value='0'>search by rsbsa/name/qrcode...</option>");
                    // $("#select2_farmer").append(data.return_str);
                    $("#card_data").empty().append(data.return_card);
                    $("#card_data2").empty().append(data.return_card_detailed);
                    $(".seed_tag_add").css("display", "inline");

                    load_seedtags(province,municipality,coop);  
  
                    HoldOn.close()
                    
                }
            });
           }
        });


        function load_seedtags(province, municipality, coop){            
            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.load_seedtags') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    province:province,
                    municipality:municipality,
                    coop:coop 
                },
                success: function(data){
                    $("#seed_tags").empty().append("<option value='0'>Select seedtag</option>");
                    $("#qr_code").empty().append("<option value='0'>Select QR code</option>");
                    $("#seed_variety").empty().append("<option value='0'>Select seed variety</option>");
                    $("#seed_tags").append(data);     
                }
            });
        };



        $("#seed_tags").on("change", function(e){
            var province = $("#province_select").val();
            var municipality = $("#municipality").val();
            var seed_tag = $("#seed_tags").val();
            var coop = $("#select2_coop").val();
            $("#seed_variety").empty().append("<option value='0'>loading seed vairiety...</option>");
            $("#qr_code").empty().append("<option value='0'>loading QR code...</option>");
            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.seed_variety') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    seed_tag: seed_tag,
                    province: province,
                    municipality: municipality,
                    coop:coop 
                },
                success: function(data){
                    $("#seed_variety").empty().append("<option value='0'>Select seed variety</option>");
                    $("#qr_code").empty().append("<option value='0'>Select QR code</option>");
                    $("#seed_variety").append(data);
                }
            });
        });

        $("#seed_variety").on("change", function(e){
            HoldOn.open(holdon_options)
            var seed_variety = $("#seed_variety").val();
            var province = $("#province_select").val();
            var municipality = $("#municipality").val();
            var seed_tag = $("#seed_tags").val();
            var coop = $("#select2_coop").val();

            if(seed_variety==0){
                alert('Select Seed Variety');
            }else{

                $("#qr_code").empty().append("<option value='0'>loading QR code...</option>");
                $.ajax({
                    type: 'POST',
                    url: "{{ route('ebinhi.utility.qr_code') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        seed_tag: seed_tag,
                        seed_variety: seed_variety,
                        province: province,
                        municipality: municipality,
                        coop:coop
                    },
                    success: function(data){
                        $("#qr_code").empty().append("<option value='0'>Select Qr code...</option>");
                        $("#qr_code").append(data);  
                        HoldOn.close() 
                    }
                

                });

            }

            
        });

        $("#btn_add").on("click", function(e){
            HoldOn.open(holdon_options)
            
            var province = $("#province_select").val();
            var municipality = $("#municipality").val();
            var coop = $("#select2_coop").val();
            var dop_name = $("#dop_name").val();
            var seed_tag = $("#seed_tags").val();
            var seed_variety = $("#seed_variety").val();
            var qr = $("#qr_code").val();
            var paymayacode = $("#select2_farmer").val();


            if(seed_tag == "0" || seed_variety == "0" || qr == "0" || paymayacode == "0"){
               alert("Please fill-up all the required parameters.");
               HoldOn.close()
           }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('ebinhi.utility.add_tbl_claim') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        province: province,
                        municipality: municipality,
                        dop_name:dop_name,
                        coop:coop,
                        seed_tag: seed_tag,
                        seed_variety: seed_variety,
                        qr:qr,
                        paymayacode:paymayacode
                    },
                    success: function(data){
                        if(data == 0){
                            alert('Cannot update record Bags claimed is equal to Claimable bags');
                        }if(data == 1){
                            alert('Claim bags Successfully Updated');
                            reload(paymayacode);   
                        }if(data == 2){
                            alert('QR code already exist');
                        }else{
                            reload(paymayacode);
                            load_seedtags(province,municipality,coop);  
                        }
                        
                        HoldOn.close() 
                    }         
                });

           }
            
        });


        function reload(paymayacode){            
            $.ajax({
                type: 'POST',
                url: "{{ route('ebinhi.utility.reload') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    paymayacode:paymayacode    
                },
                success: function(data){
                    $("#card_data").empty().append(data.return_card);
                    $("#card_data2").empty().append(data.return_card_detailed);
                    $(".seed_tag_add").css("display", "inline");   
                }
            });
        };

        $('body').on('click', '.btn_delete', function(e){
            alert('Please contact System Administrator to perform this action');
            // HoldOn.open(holdon_options)
            
            var province = $("#province_select").val();
            var municipality = $("#municipality").val();
            var coop = $("#select2_coop").val();
            var dop_name = $("#dop_name").val();
            var seed_tag = $("#seed_tags").val();
            var seed_variety = $("#seed_variety").val();
            var qr = $("#qr_code").val();

            // $.ajax({
            //     type: 'POST',
            //     url: "{{ route('ebinhi.utility.add_tbl_claim') }}",
            //     data: {
            //         _token: "{{ csrf_token() }}",
            //         province: province,
            //         municipality: municipality,
            //         dop_name:dop_name,
            //         coop:coop,
            //         seed_tag: seed_tag,
            //         seed_variety: seed_variety,
            //         qr:qr
            //     },
            //     success: function(data){
            //         // $("#card_data2").empty().append(data.return_card_detailed);

            //         // HoldOn.close() 
            //     }
             

            // });
        });


  
    </script>
@endpush
