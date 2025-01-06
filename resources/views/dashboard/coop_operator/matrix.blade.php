@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
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
	.card-header {
		margin-bottom: 0;
		background-color: #ffffff;
		border-bottom: 1px solid rgb(90 90 90 / 13%);
	}
	.select2-container--default .select2-selection--single .select2-selection__rendered {
		color: #c1c1c1;
		line-height: 28px;
	}
	.sub-count{
		font-size: 20px;
	}
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">			
		<div class="row">
            <div class="col-md-7">
                <div class="x_panel">
                    <div class="x_title">
                        <h2 style="margin-top: 10px;">
							Participating Seed Growers
						</h2>
						<div class="clearfix"></div>
                    </div>
                    <div class="x_content">
						<table class="table table-bordered table-striped" id="sg_tbl">
							<thead>
								<th>First Name</th>
								<th>Last Name</th>
								<th>Accreditation #</th>
								<th>Registered Area (ha)</th>
								<th>Action</th>
							</thead>
						</table>
                    </div>
                </div>
            </div>
			<div class="col-md-5">
				<div id="load_varieties"></div>
			</div>
        </div>
    </div>

	
	
	<!-- BREAKDOWN PREVIEW MODAL -->
<div id="show_allocation_modal" class="modal fade" role="dialog" >
    <div class="modal-dialog" style="width: 400px;">
        <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title">
                    <span>Seed Grower Variety Allocation</span><br>
                </h4>
            </div>
   
            <div class="modal-body">
                <label for="" class="col-xs-5">Name:</label>
                <label id="modal_name"></label> <br>
                <label for="" class="col-xs-5">Accreditation #: </label>
                <label id="modal_accre"></label> <br>
                <label for="" class="col-xs-5">Unallocated Area: </label>
                <label id="modal_area"></label> <br>
                <input type="hidden" id="areaAlloc" name="areaAlloc">
		        	<div class="form-group" id="variety_loc">
		        	</div>  
		        	
        	</div>
    </div>
</div>
<!-- BREAKDOWN PREVIEW MODAL -->

	
	
	
	
	
	
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
	<script>
		   $('#show_allocation_modal').on('show.bs.modal', function (e) {
			 var id = $(e.relatedTarget).data('id');
             var fname = $(e.relatedTarget).data('fname');
			 var mname = $(e.relatedTarget).data('mname');
			 var lname = $(e.relatedTarget).data('lname');
             var accre = $(e.relatedTarget).data('accre');
             var area = $(e.relatedTarget).data('area');
			 var coopaccre = $(e.relatedTarget).data('coopaccre');
			 
			 var name = fname+' '+mname+' '+lname;
             $("#modal_name").empty().html(name);
             $("#modal_accre").empty().html(accre);
             $("#modal_area").empty().html(area+" (ha)");
			 $("#areaAlloc").val(area);

			 getVariety(id,fname,lname,mname,accre,area,coopaccre)

         });
		 load_varieties();
		 

		 function load_varieties(){
			$.ajax({
				type: "POST",
				url: "{{ route('coop_operator.sg_matrix.varieties') }}",
				data: { _token:  "{{ csrf_token() }}" },
				dataType: "HTML",
				success: function (response) {
					$("#load_varieties").html(response);
				}
			});
		 }
	
		
		function getVariety(id,fname,lname,mname,accre,area,coopaccre){
			 $.ajax({
					method: 'POST',
					url: "{{ route('coop_operator.sg_matrix.commitment') }}",
					dataType: 'json',
					data: {
						_token: "{{ csrf_token() }}",
						id: id,
						fname: fname,
						mname: mname,
						lname: lname,
						accre: accre,
						area: area,
						coopaccre: coopaccre
					},
					success: function (source) {
							//alert(source);
							$("#variety_loc").empty();

                            $.each(source, function (i, d) {
							
								if(d.withData){
									var act = 'deleteAllocation('+d.allocationId+','+id+',"'+fname+'","'+mname+'","'+lname+'","'+accre+'","'+coopaccre+'")' ;
									var btn = "<div class='x_panel' style='background-color:#A9F5BC; width:300px; height:50px;  border-radius: 20px 20px 20px 20px;' >";
									btn = btn+ "<div class='col-sm-6' style='cursor: pointer;' > "+d.seed_variety+"</div>";
									btn = btn+ "<div class='col-sm-4'>"+d.memberCommit+"</div>"; 
									btn = btn+ "<div class='col-sm-2'><button class='btn btn-warning btn-xs' onclick='"+act+"'><i class='fa fa-trash'></i></button> </div>";
									btn = btn+ "</div> ";
								}else{
									var act = 'specifyArea("'+d.seed_variety+'",'+d.availableBags+','+d.availableArea+','+id+',"'+fname+'","'+mname+'","'+lname+'","'+accre+'","'+coopaccre+'",'+d.commitment_id+')';
									//alert(act); 
									var btn = "<div class='x_panel' style='background-color:#BDBDBD; width:300px; height:50px;   border-radius: 20px 20px 20px 20px;  cursor: pointer;' ";
									btn = btn+ "onclick='"+act;
									btn = btn+"'>";
									btn = btn+ "<div class='col-sm-6' style='cursor: pointer;' >"+d.seed_variety+"</div>"
									btn = btn+ "</div> ";

								}
								$("#modal_area").empty().html(d.availableArea+" (ha)");
                                $("#variety_loc").append(btn);
								
                        });
						load_varieties();
					}
				});
		}

		function deleteAllocation(id,memberid,fname,mname,lname,memberaccre,coopaccre){
			var yesno = confirm("Remove Allocation?");
			if(yesno == true){
				HoldOn.open(holdon_options);
					$.ajax({
					        method: 'POST',
					        url: "{{route('coop_operator.sg_matrix.remove')}}",
					        data: {
					            _token:  "{{ csrf_token() }}",
					            id:id			            
					        },
					        dataType: 'json',
					        success: function (source) {
					  			if(source==="success"){
					  				area = $("#areaAlloc").val();
					  				alert("Allocation Successfully Removed");
					  				 getVariety(memberid,fname,lname,mname,memberaccre,area,coopaccre);
					  			}else{
					  				alert("Please Refresh the page, Cannot Find Record");
					  			}
					        }
					    });
				load_varieties();		
				HoldOn.close();
			}



		}


		function specifyArea(variety,available,area,memberid,fname,mname,lname,memberaccre,coopaccre,commitmentid){

				if(available <= 0){
					alert("Coop's commitment for this variety("+variety+") already exceeds");
					exit();
				}
				if(area <= 0){
					alert("Member's area allocation already exceeds");
					exit();
				}
				
				do{
				  var inputArea = 0
				  inputArea = prompt("Variety: "+variety+" \n Please Specify Area in Hectares", "");
				 	if(inputArea > area){
				 		alert("You Input more than your Unallocated area");
						 return;
				 	}
					var bags_ha = "<?php echo $bags_ha?>";
				  var bags = inputArea * parseInt(bags_ha); 
				 	if(bags > available){
				 		alert("You Input more than the available commitment for this variety ("+variety+")");
						return;
				 	}
				}while(isNaN(inputArea)==true || inputArea===""|| inputArea > area);
					

					if(bags > 0 || inputArea > 0){
					HoldOn.open(holdon_options);		
						 $.ajax({
					        method: 'POST',
					        url: "{{route('coop_operator.sg_matrix.confirm')}}",
					        data: {
					            _token:  "{{ csrf_token() }}",
					            variety: variety,
					            inputArea: inputArea,
					            bags: bags,
					            memberid: memberid,
					            fname: fname,
					            mname: mname,
					            lname: lname,
					            memberaccre: memberaccre,
					            coopaccre: coopaccre,
					            commitmentid: commitmentid,				            
					        },
					        dataType: 'json',
					        success: function (source) {
					  			if(source==="success"){
					  				area = $("#areaAlloc").val();
					  				alert("Allocation Successfully Added");
					  				 getVariety(memberid,fname,lname,mname,memberaccre,area,coopaccre);
					  			}else{
					  				alert("Please Refresh the page, Either Commitment / Area Exceeds");
					  			}


					  			
					        }
					    }); 
					  HoldOn.close();
					  load_varieties();
					}



		 }

	
		
	
	
	
	
	
	
		refresh_sg_matrix();
		function refresh_sg_matrix(){
			$('#sg_tbl').DataTable().clear();
			$("#sg_tbl").DataTable({
				"bDestroy": true,
				"autoWidth": false,
				"searchHighlight": true,
				"processing": true,
				"serverSide": true,
				"orderMulti": true,
				"order": [],
				"ajax": {
					"url": "{{ route('coop_operator.sg_matrix.tbl') }}",
					"dataType": "json",
					"type": "POST",
					"data":{
						"_token": "{{ csrf_token() }}",
						"coop_accreditation": "{{$tagged_accreditation}}"
					}
				},
				"columns":[
					{"data": "first_name"},
					{"data": "last_name"},
					{"data": "accreditation_number"},
					{"data": "area"},
					{"data": "action", searchable: false}
				]
			});
		}
	</script>
@endpush
