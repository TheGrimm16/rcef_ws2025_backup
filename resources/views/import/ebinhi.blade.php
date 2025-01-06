@extends('layouts.index')

@section('styles')

@endsection

@section('content')
<div class="col-md-12 col-sm-12 col-xs-12">

    <!-- Import File -->
    <div class="x_panel">
        <div class="x_title">
            <h2>
                Import EBinhi farmers survey
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content form-horizontal form-label-left">
            <div class="row">
                <div class="col-auto pt-1 pl-5">
                    <h5>Import new data set </h5>
                </div>
                <div class="col-auto">
                    <form id="fileUpload" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" class="custom-file-input form-control" id="inputFile" name="file" required>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" required>
                            <input type="hidden" name="function" value="ebinhi" required>
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
                Import Farmers
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
                                <th>First Name</th>      
                                <th>Middle Name</th>        
                                <th>Last Name</th>      
                                <th>Extension Name</th>      
                                <th>RSBSA</th>      
                                <th>Region</th>      
                                <th>Province</th>      
                                <th>Municipality</th>      
                                <th>Province Code</th>      
                                <th>Municipality Code</th>      
                                <th>Barangay Code</th>      
                                <th>Contact Number</th>      
                                <th>Wet Season</th>      
                                <th>Dry Season</th>      
                                <th>Commited Area</th>      
                                <th>Sex</th>      
                                <th>Mother Last Name</th>      
                                <th>Mother First Name</th>      
                                <th>Mother Middle Name</th>           
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

    $("#fileUpload").on("submit", function(e) {
        e.preventDefault();
        // upload_table.clear().draw();
        $("#import_data").hide();
        // $("body").LoadingOverlay("show", {
        //     background: "rgba(165, 190, 100, 0.5)"
        // });
        $("body").LoadingOverlay("show", {
            imageAnimation: "2000ms rotate_right",
            image: "",
            text: "Importing..."
        });

        $('#upload-modal').modal('show');

        var col_data = <?php echo json_encode($col_data) ?>;

            // var data = new FormData(this.value);
            $.ajax({
                type: "POST",
                url: "{{ route('import.file.json') }}",
                data: new FormData(this),
                dataType: "JSON",
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    $("body").LoadingOverlay("hide");
                    // dataset = response.data;
                    // response.table_data.forEach(function(data) {
                    //     var table_data = [];
                    //     col_data.forEach(function(value) {
                    //         table_data.push(data[value]);
                    //     })
                    //     upload_table.row.add(table_data).draw(false);
                    // });
                    // $("#import_data").show();
                   
                    sg_commitment.ajax.reload(null, false);
                   alert("Data Inserted Count: " + response.insert_no + " | Duplicate RSBSA : " + response.duplicate);
                    
                },
                error: function(xhr) {
                    $("body").LoadingOverlay("hide");
                    // $.alert({
                    //     title: '<span class="text-danger">Oops... </span>',
                    //     content: 'Something went wrong <br/><span class="font-weight-bold">' +
                    //         xhr.status + "</span> - " + xhr.statusText,
                    //     theme: 'modern',
                    //     type: 'red',
                    // });
                    alert('Something went wrong - ' + xhr.status + " - " + xhr.statusText);
                }
            });
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
                "url": "{{ route('import.table.ebinhi') }}",
                "dataType": "json",
                "type": "POST",
                "data":{
                    _token: "{{ csrf_token() }}",
                }
            },
            "columns": [{
                        data: "fname"
                    },
                    {
                        data: "midname"
                    },
                    {
                        data: "lname"
                    }, {
                        data: "extename"
                    },{
                        data: "rsbsa_control_number"
                    },{
                        data: "region"
                    },{
                        data: "province_name"
                    },{
                        data: "municipality_name"
                    },{
                        data: "prv_code"
                    },{
                        data: "muni_code"
                    },{
                        data: "barangay_code"
                    },{
                        data: "contact_no"
                    },{
                        data: "farm_area_ws2021"
                    },{
                        data: "farm_area_ds2021"
                    },{
                        data: "committed_area"
                    },{
                        data: "ver_sex"
                    },{
                        data: "mother_lname"
                    },{
                        data: "mother_fname"
                    },{
                        data: "mother_mname"
                    }
                ]
        });

</script>
@endpush