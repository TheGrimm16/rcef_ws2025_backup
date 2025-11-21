@extends('seed_replacement.layouts.index')

@section('title', 'Create Request')

@section('content')
<div class="container" style="margin-top:20px">

    <h3>Create New Seed Replacement Request</h3>

    {{-- Messages --}}
    @include('layouts.message')

    <div class="panel panel-default">
        <div class="panel-heading"><strong>New Request Form</strong></div>

        <div class="panel-body">

            <form id="createRequestForm" method="POST" action="{{ route('replacement.request.store') }}">
                @csrf

                {{-- Error Display --}}
                <div id="form-errors" class="alert alert-danger" style="display:none;"></div>

                <div class="form-group">
                    <label>Region</label>
                    <select name="region_code" id="regionSelect" class="form-control" style="width:100%" required>
                        <option value="">Select Region</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Province</label>
                    <select name="province_code" id="provinceSelect" class="form-control" style="width:100%" required>
                        <option value="">Select Province</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Municipality</label>
                    <select name="municipality_code" id="municipalitySelect" class="form-control" style="width:100%" required>
                        <option value="">Select Municipality</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>RSBSA</label>
                    <input type="text" name="rsbsa" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>New Released ID</label>
                    <input type="number" name="new_released_id" class="form-control">
                </div>

                <div class="form-group">
                    <label>Geo Code</label>
                    <input type="text" name="geo_code" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Purpose ID</label>
                    <input type="number" name="purpose_id" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Attachment Directory</label>
                    <input type="text" name="attachment_dir" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Save Request</button>
            </form>

        </div>
    </div>

</div>
@endsection


@push('scripts')
<script>
window.geoRoutes = {
    regions: "{!! route('geo.regions') !!}",
    provinces: "{!! route('geo.provinces', ['regionCode' => '__REGION__']) !!}",
    municipalities: "{!! route('geo.municipalities', ['provinceCode' => '__PROVINCE__']) !!}"
};
</script>

<script src="{{ asset('public/js/geo_select2.js') }}"></script>

<script>
$(document).ready(function() {

    initGeoSelect2({
        region: '#regionSelect',
        province: '#provinceSelect',
        municipality: '#municipalitySelect'
    });


    // Form submission
    $('#createRequestForm').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ route('replacement.request.store') }}", $(this).serialize())
         .done(function() {
             alert('Request created successfully!');
             window.location.href = "{{ route('replacement.request.index') }}";
         })
         .fail(function(xhr) {
             if(xhr.status === 422) {
                 let html = "<ul>";
                 $.each(xhr.responseJSON.errors, function(k,v){
                     html += "<li>" + v[0] + "</li>";
                 });
                 html += "</ul>";
                 $('#form-errors').show().html(html);
             } else {
                 alert('An unexpected error occurred.');
             }
         });
    });
});
</script>
@endpush

