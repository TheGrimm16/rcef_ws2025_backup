{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<table id="list" class="table table-responsive-sm table-bordered" style="width:100%">
    <thead>
    <th style="text-align: center !important;">#</th>
    <th>RSBSA Control Number</th>
    <th>Full name</th>
    <th>Seed variety</th>
    <th>Total bags</th>
    <th>Area</th>
    <th>Pending release data</th>
    <th>Release data</th>
    <th>Action</th>
</thead>				
</table>
<button id="hidden_trigger">hidden</button>
@push('scripts')
<script>
    $("#hidden_trigger").click(,function () {
        var data = $("#search_data").val();
        var search_categ = $("#search_categ").val();
        alert(data)
        $('#list').DataTable({
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "order": [[0, "desc"]],
            "fixedHeader": {
                "header": false,
                "footer": false
            },
            searchDelay: 1000,
            oLanguage: {sProcessing: "<img src='public/images/processing.gif' />"},
            "ajax": {
                "url": "{{ route('rcef.checking.search') }}",
                "dataType": "json",
                "method": "POST",
                "data": {search_data: data, _token: "{{ csrf_token() }}", search_categ: search_categ}
            },
            "drawCallback": function (settings) {

            },
            "columns": [
                {"data": "number"},
                {"data": "rsbsa"},
                {"data": "full_name"},
                {"data": "variety"},
                {"data": "bags"},
                {"data": "area"},
                {"data": "pending", orderable: false, searchable: false},
                {"data": "release", orderable: false, searchable: false},
                {"data": "action", orderable: false, searchable: false}
            ],
            "fnInitComplete": function () {
//            $(".actionButtons").tooltip({
//                'selector': '',
//                'placement': 'top',
//                'width': '20px'
//            });
            }

        });
    });
    $("#hidden_trigger").trigger("click");
</script>
@endpush
