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
                            <table class="table table-striped table-bordered tbl" id="seedtbl">
                                <thead>
                                    <tr>
                                        <th style="width: auto;">Province</th>
                                        <th style="width: auto;">Municipality</th>
                                        <th style="width: auto;">PO #</th>
                                        <th style="width: auto;">Supplier Name</th>
                                        <th style="width: auto;">Variety</th>
                                        <th style="width: auto;">Confirm Bags</th>                                  
                                        <th style="width: auto;">Delivered Bags</th>                                  
                                        <th style="width: auto;">Delivery Schedule</th>
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
var token = "{{ csrf_token() }}";
     

    tblLoad();
        function tblLoad(){
        $('#seedtbl').DataTable({
			processing: true,
			"bDestroy": true,
			"autoWidth": false,				
			serverSide: true,
			//ajax: "{!! route('palaysikatan.farmers.datatable') !!}",
			"ajax": {
                        "url": "{{ route('delivery-confrimation-data-list') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",                         
                        }
                    },
			columns: [
				{data: 'province', name: 'province' },
				{data: 'municipality', name: 'municipality'},
				{data: 'po', name: 'po'},
				{data: 'supplierName', name: 'supplierName'},
				{data: 'seed_variety', name: 'seed_variety'},
				{data: 'confirmDelivery', name: 'confirmDelivery'},									
				{data: 'remainingVolume', name: 'remainingVolume'},									
				{data: 'delivery_date', name: 'delivery_date'},					
				{data: 'actions', name: 'actions', orderable: false, searchable: false}
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
                        url: "{{ url('delete-confimDelivered') }}",
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
