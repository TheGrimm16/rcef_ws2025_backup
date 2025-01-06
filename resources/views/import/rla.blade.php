@extends('layouts.index')

@section('styles')

@endsection

@section('content')
<div class="col-md-12 col-sm-12 col-xs-12">

    <!-- Import File -->
    <div class="x_panel">
        <div class="x_title">
            <h2>
                Import RLA
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <div class="row">
                <div class="col-auto pt-1 pl-5">
                    <h5>Import new data set </h5>
                </div>
                <div class="col-auto">
                    <form id="fileUpload" method="POST" action="{{ route('util.import_csv.rla') }}" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" class="custom-file-input form-control" id="inputFile" name="inputFile" required>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" required>
                            <input type="hidden" name="function" value="rla" required>
                            <span class="input-group-btn">
                                <button class="btn btn-outline-primary" type="submit">Upload</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div><br>
    <!-- Import File -->
    <div class="x_panel">
        <div class="x_title">
            <h2>
                Import Seed Beneficiaries
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <div class="row">
                <div class="col-auto pt-1 pl-5">
                    <h5>Import new data set </h5>
                </div>
                <div class="col-auto">
                    <table id="sg_commitment" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>                      
                                <th>Seed Grower</th>      
                                <th>Coop Name</th>        
                                <th>Accreditation no.</th>      
                                <th>MOA no.</th>      
                                <th>Certification Date</th>      
                                <th>Lab #</th>      
                                <th>Lot #</th>      
                                <th># of bags</th>      
                                <th>Seed Variety</th>      
                            </tr>
                            <thead>
                            <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><br>

</div>

@endsection()

@push('scripts')
<script src="{{ asset('public/js/loadingoverlay.js') }}"></script>
<script>

    $("#inputFile").change(function(e) {
        e.preventDefault();
        var fileName = e.target.files[0].name;
        $("#inputFileLabel").html(fileName);
    });

   var sg_commitment = $("#sg_commitment").DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [],
            "ajax": {
                "url": "{{ route('import.table.rla') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    _token: "{{ csrf_token() }}",
                }
            },
            "columns": [{
                        data: "sg_name",
                    },
                    {
                        data: "coop_name"
                    },
                    {
                        data: "coopAccreditation"
                    }, {
                        data: "moaNumber"
                    },{
                        data: "certificationDate"
                    },{
                        data: "labNo"
                    },{
                        data: "lotNo"
                    },{
                        data: "noOfBags"
                    },{
                        data: "seedVariety"
                    }
                ]
        });

</script>
@endpush