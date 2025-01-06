@extends('layouts.index')

@section('content')

<style type="text/css">
	
	.tbl{
		color: black;
	}

</style>


	<div>
		<div class="page-title">
            <div class="title_left tbl">
              <h3>Palaysikatan</h3>
            </div>
        </div>
		<div>
		@if (Session::has('success_farmer'))
        	<li>{!! session('success_farmer') !!}</li>
   		@endif
		</div>

        <div class="clearfix"></div>

		<div class="row">
			
		</div>
		

	</div>
@endsection

@push('scripts')
	<script>

		$('#station').change(function(){
			if($(this).val() != ""){
				$('select[name="province_data"]').empty();
				$.ajax({
                type: 'POST',
                url: "{{ route('palaysikatan.farmers.get-station-province') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    station:$(this).val()
                },
                success: function(data){			
					$('select[name="province_data"]').append('<option alue="">Filter Per Province</option>');
					data.forEach(element => {                        
                        $('select[name="province_data"]').append('<option value="' + element
                            .province + '">' + element.province + '</option>');
                    });
					$('.province_div').show();


				  }				  

            });
			}else{
				$('.province_div').hide();
				
			}
		})
		loadData(1);
		function loadTable(){
			$('#farmersTbl').DataTable().clear();			
			loadData(2);
		}
		function deleteFca(id){
			
			var proceed = confirm("Are you sure you want to proceed?");
			if (proceed) {

			$.ajax({
                type: 'POST',
                url: "{{ route('palaysikatan.farmers.delete') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    fcaId: id
                },
                success: function(data){
                  if(data == "true" || data == true){
					$('#farmersTbl').DataTable().ajax.reload();
					//loadData();
				  }				  

                }
            });
			} else {
			  //don't proceed
			}
		
		}
		function export_excel_npk(){
			var station = $('#station').val();
			if(station==""){
				alert("Please Select Station");
			}else{
				window.open("export/report_matrix/"+station+"");
			}
			
		}

		function export_excel(){
			var station = $('#station').val();
			if(station==""){
				alert("Please Select Station");
			}else{
				window.open("export/report/"+station+"");
			}
		}

		function export_excel_table(){

			window.open("{{url($export_link_table)}}");

		}

		window.Laravel = {!! json_encode([
			'csrf_token' => csrf_token()
		]) !!};

		
		function loadData(state){
			console.log(state);
			if(state==1){

			let farmersTbl = $('#farmersTbl').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			columns: [
				{data: 'farmer_id', name: 'farmer_id' },
				{data: 'f_full_name', name: 'f_full_name'},
				{data: 'r_full_name', name: 'r_full_name'},
				{data: 'age', name: 'age'},
				{data: 'sex', name: 'sex'},				
				{data: 'org_membership', searchable: false},
				{data: 'techno_area', searchable: false},
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
			}else{
			var province = $('#province_data').val();
			if($('#station').val() == 0){
				province="%";
			}
			let farmersTbl = $('#farmersTbl').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			//ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			"ajax": {
                        "url": "{{ route('palaysikatan.farmers.datatable-filter') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            province: province,
                         
                        }
                    },
			columns: [
				{data: 'farmer_id', name: 'farmer_id' },
				{data: 'f_full_name', name: 'f_full_name'},
				{data: 'r_full_name', name: 'r_full_name'},
				{data: 'age', name: 'age'},
				{data: 'sex', name: 'sex'},				
				{data: 'org_membership',searchable: false},
				{data: 'techno_area',searchable: false},
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
			]
		});
			}


		}
		
	</script>
@endpush
