@extends('layouts.index')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2 class="mb-4 pageHeader">Permissions</h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 margin-tb">
            @if (Auth::user()->can('permission-create'))
                <a class="btn btn-lg btn-success btn-pill addBtn" href="{{ route('permissions.create') }}" style="margin-bottom: 10px;"><i class="fa fa-plus"></i> Create New Permission</a>
            @endif
        </div>
    </div>

    @if ($message = Session::get('success'))
		<div class="alert alert-success alert-dismissible" role="alert" style="font-family: Noto; font-size: 1.5vw;">
			{{ $message }}
			<button type="button" class="close" data-dismiss="alert" aria-label="close" style="font-size: 2vw;">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif

	@if ($message = Session::get('error'))
		<div class="alert alert-danger alert-dismissible" role="alert" style="font-family: Noto; font-size: 1.5vw;">
			{{ $message }}
			<button type="button" class="close" data-dismiss="alert" aria-label="close" style="font-size: 2vw;">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-striped" id="permissionsTbl" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Permission</th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 25%;">Status</th>
                                <th style="width: 25%;">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script src="{{ asset('public/js/PermissionsTable.js') }}" type="module"></script> --}}

    <script>
        window.Laravel = {!! json_encode([
            'api_token' => $data['api_token'],
            'csrf_token' => csrf_token(),
            'tableRoute' => route('permissions.datatable')
        ]) !!};
    </script>
@endpush
