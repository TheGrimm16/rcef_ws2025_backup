@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>Farmer Profile Data Puller</h3>
            </div>
        </div>

            <div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

        <div class="row">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Data will pull from Wet season 2020
            </div>
        </div>                  



                           
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province  </label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="utilProvince" id="utilProvince" class="form-control" data-parsley-min="1" style="width: 500px">

                                        <option value="0">Please select a province</option>
                                        @foreach ($provinces as $provinces)
                                        <option value="{{$provinces->regCode}}{{$provinces->provCode}}"> {{$provinces->province}} </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>




                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <select name="utilMunicipality" id="utilMunicipality" class="form-control" data-parsley-min="1" style="width: 500px">
                                        <option value="0">Please select a municipality</option>
                                    </select>
                                    
                                    <br>
                                     <button type="button" name="utilProcess" id="utilProcess" class="btn btn-lg btn-primary" disabled=""><i class="fa fa-sign-in"></i> Farmer Data Pull </button>
                                     
                                     
                                                            

                                </div>
                            </div>


                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">UPDATED NOTES</label> <br>
                                
                                <div id="notes">

                                </div>
                            </div>

        </div>
    </div>
@endsection
@push('scripts')

    <script type="text/javascript">
            

            $('select[name="utilProvince"]').on('change', function () {
                HoldOn.open(holdon_options);
                var province = $(this).val();
            if (province == 0){
                  $('select[name="utilMunicipality"]').empty();
             $('select[name="utilMunicipality"]').append('<option value=0>Please select a municipality</option>');
                document.getElementById("utilProcess").disabled = true;   
            
            }else{
           
                  $('select[name="utilMunicipality"]').empty();
             $('select[name="utilMunicipality"]').append('<option value=0>ALL MUNICIPALITY</option>');
                document.getElementById("utilProcess").disabled = false;  
            }


            HoldOn.close();
            });  //END PROVINCE SELECT





               document.getElementById("utilProcess").addEventListener("click", function() {
                HoldOn.open(holdon_options);
                    var prv = document.getElementById("utilProvince").value;  
            


                  //  alert(prv+" "+pulldata);

//                    die();

                    if(prv == 0){
                            alert('Please Select a Municipality');
                            HoldOn.close();
                            die();
                    }else{
                           
                         $.ajax({
                            type: 'POST',
                            url: "{{ route('utility.process.pull') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                            
                                prv: prv
                            },
                            success: function(data){
                             $('#notes').empty();            
                            $('#notes').append(data);
                            HoldOn.close();

                            },
                            error: function(data){
                                alert("An error occured while processing your data, please try again.");
                                //alert(data);
                                HoldOn.close();
                            }
                            });
                   




                    }


                
            });  







    </script>

@endpush