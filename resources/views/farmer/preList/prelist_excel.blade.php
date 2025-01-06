@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
        <div class="page-title">
            <div class="title_left">
              <h3>FARMER PRE LIST DATA</h3>
            </div>
        </div>

        	<div class="clearfix"></div>

        <div class="x_content form-horizontal form-label-left">

            
        <div class="row">
            <div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> Notice!</strong> Farmer list data as of  <b><u>[ June 2024 ]</u></b> 
            </div>
        </div> 

             <div class="x_panel">
                           
        					<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Province</label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <select name="cmbProvince" id="cmbProvince" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a province</option>
                                        @foreach($province as $prv)
                                        <option value="{{$prv->province}}">{{$prv->province}}</option>

                                        @endforeach
                                        
                                    
                                    </select>
                                </div>
       						</div>

       						<div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality</label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <select name="cmbMunicipality" id="cmbMunicipality" class="form-control" data-parsley-min="1">
                                        <option value="0">Please select a municipality</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
                                <div class="col-md-5 col-sm-9 col-xs-9">
                                    <button class='btn btn-success' id="export_btn"> Export Excel</button>

                                </div>
                            </div>
        
               </div>       

           


       	</div>
    </div>




@endsection
@push('scripts')

	<script type="text/javascript">

        $('#export_btn').on('click', function () {
                var prv = $('select[name="cmbProvince"]').val();
                var muni = $('select[name="cmbMunicipality"]').val();
                var SITE = "{{url('/')}}";


                if(prv == "0" || muni == "0"){
                       
                }else{
                    window.open(SITE+"/prelist_farmer/"+prv+"/"+muni, "blank_");
                }


        });



            $('select[name="cmbProvince"]').on('change', function () {
            HoldOn.open("sk-cube-grid");
            var provCode = $('select[name="cmbProvince"]').val();
            $('select[name="cmbMunicipality"]').empty().append("<option value='0'>Please select a municipality</option>");
                $.ajax({
                    method: 'POST',
                    url: "{{route('nrp.municipal_list')}}",
                    data: {
                        _token: _token,
                        provCode: provCode
                    },
                    dataType: 'json',
                    success: function (source) {
                        $('select[name="cmbMunicipality"]').empty().append("<option value='all'>All Municipality</option>");
                        $.each(source, function (i, d) {
                            $('select[name="cmbMunicipality"]').append('<option value="' + d.municipality + '">' + d.municipality + '</option>');
                        }); 
                        HoldOn.close();
                    }
                }); //AJAX GET MUNICIPALITY 
            });  //END MUNICIPALITY SELECT









        $('select[name="cmbProvince"]').select2();
        $('select[name="cmbMunicipality"]').select2();
        // $('select[name="prvVar"]').select2();
        // $('select[name="nxtVar"]').select2();
        



	</script>

@endpush