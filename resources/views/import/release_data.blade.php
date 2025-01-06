@extends('layouts.index')

@section('styles')
<style>

    .badge{
        font-size: 20px;
        float: right;
        width: 10vw;
        text-align: center;
    }


    .slideFromRight3{
      animation: slideFromRight 0.6s 0.4s ease-in-out forwards;
    }

</style>
@endsection

@section('content')

<div class="col-md-12">
    <div class="x_panel">
      
        <div class="x_content form-horizontal form-label-left">
            <h2 style="font-size: 30px; float:left;" class="">
                Distribution Uploader
            </h2>
        </div>
    </div>

    
</div>


<form id="fileUpload" method="POST" action="{{ route('post.release_uploader') }}" enctype="multipart/form-data">
<div class="col-md-6">

    <!-- Import File -->
    <div class="x_panel">
      
        <div class="x_content form-horizontal form-label-left">
            <div class="row">
           
                <div class="row">
                    <div class="col-md-12">
                        <div class="_card1 slideFromRight3" style="padding:0;min-height:8vh;max-height:10vh;opacity: 1; display: flex; gap: 1.2em; background: rgba(112, 213, 137, 0.286); border-radius: 20px; ">
                            <div class="logo" style="margin:0; padding:0;width:100%; aspect-ratio: 1; font-weight: 600; font-size: 2rem; display: flex; align-items: center; justify-content: center; background:rgba(157, 225, 250, 0.308);  border-radius: 20px; ">
                                Select Drop Off Point
                            </div>
                         
                          </div>
                    </div>

                </div>
                
                <div class="row" style="margin-top:10px;">
                    <div class="col-md-3">
                        <label for="" class="badge badge-info badge-lg">Province</label>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control form-select" id="province_release" name="province_release"> 
                            <option value="0">Select a Province</option>
                          
                            @foreach($provinces as $prv)
                            <option value="{{$prv['province']}}">{{$prv['province']}}</option>


                              
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="" class="badge badge-info badge-lg">Municipality</label>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control form-select" id="municipality_release" name="municipality_release"> 
                            <option value="0">Select a Municipality</option>

                        </select>
                    </div>

                </div>


                <div class="row">
                    <div class="col-md-3">
                        <label for="" class="badge badge-info badge-lg">Drop off Point</label>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control form-select" id="dop_release" name="dop_release"> 
                            <option value="0">Select a Drop Off Point</option>

                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-md-6" id="file_div">
    <!-- Import File -->
    <div class="x_panel">
        
        <div class="x_content form-horizontal form-label-left">
            <div class="row">

                <div class="row">
                    <div class="col-md-6 ">
                        <div class="_card1 slideFromRight3 inbred" style="min-height:10vh;max-height:10vh;opacity: 1; display: flex; gap: 1.2em; background: rgba(112, 213, 137, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
                            <div class="logo" style="width:200px; aspect-ratio: 1; font-weight: 900; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(0, 183, 89, 0.308); padding:5px; border-radius: 20px;">
                              -
                            </div>
                            <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: center; flex-direction: column; overflow: hidden;">
                              <div style="font-size: 1.6rem; font-weight: 900;">Inbred Stocks</div>
                            </div>
                          </div>
                    
                    
                    </div>
                    <div class="col-md-6 ">
                        <div class="_card1 slideFromRight3 hybreed" style="min-height:10vh;max-height:10vh;opacity: 1; display: flex; gap: 1.2em; background: rgba(112, 213, 137, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
                            <div class="logo" style="width:200px; aspect-ratio: 1; font-weight: 900; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(0, 183, 89, 0.308); padding:5px; border-radius: 20px;">
                              -
                            </div>
                            <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: center; flex-direction: column; overflow: hidden;">
                              <div style="font-size: 1.6rem; font-weight: 900;">Hybrid Stocks</div>
                            </div>
                          </div>

                    </div>
                </div>

                <div class="row" style="margin-top: 20px; margin-bottom:20px;">
                    <div class="col-md-3">
                        
                    </div>
                    <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" name="inbred_count" id="inbred_count">
                                <input type="text" name="hybrid_count" id="hybrid_count">
                                
                                <input type="file" class="custom-file-input form-control" id="excel_file_up" name="excel_file_up" required>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" required>
                               

                            </div>
                            <a href="{{asset('public/templates/ds2024_distribution_format.xlsx')}}" download="format.xlsx" class="btn btn-info btn-md"> <i class="fa fa-cloud-download" aria-hidden="true"> Download Template</i></a>
                            <button class="btn btn-success btn-md" type="submit" id="submit"><i class="fa fa-cloud-upload" aria-hidden="true">Upload File</i></button>
                    </div>
                </div>

                

              



            </div>
        </div>
    </div>


</div>
</form>

@endsection()



@push('scripts')
<script src="{{ asset('public/js/loadingoverlay.js') }}"></script>
<script>
    $("#file_div").hide("fast");
   
    $("#province_release").on("change", function(){
        var province = $("#province_release").val();
            HoldOn.open(holdon_options);
            $.ajax({
                    method: 'POST',
                    url: "{{route('release_uploader.municipal_list')}}",
                    data: {
                        _token: _token,
                        province: province
                    },
                    dataType: 'json',
                    success: function (source) {
           
                    $('select[name="municipality_release"]').empty().append('<option value="0">Select a Municipality</option>');
                    $('select[name="dop_release"]').empty().append('<option value="0">Select a Drop Off Point</option>');
                    $("#dop_release").val(0).change();
                    $.each(source, function (i, d) {
                        
                        $('select[name="municipality_release"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                    }); 
                     HoldOn.close();   
            }
            });


    });

    $("#municipality_release").on("change", function(){
        var province = $("#province_release").val();
        var municipality = $("#municipality_release").val();
        
            HoldOn.open(holdon_options);
            $.ajax({
                    method: 'POST',
                    url: "{{route('release_uploader.dop_list')}}",
                    data: {
                        _token: _token,
                        province: province,
                        municipality: municipality
                    },
                    dataType: 'json',
                    success: function (source) {
           
                    $('select[name="dop_release"]').empty().append('<option value="0">Select a Drop Off Point</option>');
                    $("#dop_release").val(0).change();
                    $.each(source, function (i, d) {
                        if(municipality != "0"){
                            $('select[name="dop_release"]').append('<option value="' + d.dropOffPoint + '">' + d.dropOffPoint + '</option>');
                        }
                       
                    }); 
                     HoldOn.close();   
            }
            });


    });

    $("#dop_release").on("change", function(){
        var province = $("#province_release").val();
        var municipality = $("#municipality_release").val();
        var dop = $(this).val();
        if(dop != "0"){

            HoldOn.open(holdon_options);
            $.ajax({
                    method: 'POST',
                    url: "{{route('release_uploader.get_stocks')}}",
                    data: {
                        _token: _token,
                        province: province,
                        municipality: municipality,
                        dop: dop
                    },
                    dataType: 'json',
                    success: function (source) {
                        
                        
                        if(source.nrp_stocks){
                            var hybreed_stocks = source.nrp_stocks;
                        }else{
                            var hybreed_stocks = "-";
                        }

                        if(source.inbred_stocks){
                            var inbred_stocks = source.inbred_stocks;
                        }else{
                            var inbred_stocks = "-";
                        }

                        if(source.municipal_stocks > 0){
                            $("#excel_file_up").removeAttr('disabled');
                            $("#submit").removeAttr('disabled');
                           
                        }else{
                            $("#excel_file_up").attr('disabled','disabled');
                            $("#submit").attr('disabled','disabled');

                        }


                        // $(".inbred .card-title").empty().text("Inbred Stocks");
                        $(".inbred .logo").empty().text(inbred_stocks);
                        $(".hybreed .logo").empty().text(hybreed_stocks);

                        $("#inbred_count").val(inbred_stocks);
                        $("#hybrid_count").val(hybreed_stocks);
                                         

                        
                        
                     HoldOn.close();   
                    }   
            });



            $("#file_div").show("fast");
        }else{
            $("#file_div").hide("fast");
        }
    }); 

    $("#province_release").select2();
    $("#municipality_release").select2();
    $("#dop_release").select2();
    

</script>
@endpush