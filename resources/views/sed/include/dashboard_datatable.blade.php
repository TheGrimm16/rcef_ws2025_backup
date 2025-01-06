<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Farmer List</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
                            <table class="table table-striped table-bordered wrap" id="farmersTbl">
                                <thead>
                                    <tr>
                                        <th style="width: auto;">ID</th>
                                        <th style="width: auto;">Farmer Name</th>
                                        <th style="width: auto;">Province</th>
                                        <th style="width: auto;">Municipality</th>
                                        <th style="width: auto;">Contact no.</th>
                                        <th style="width: auto;">Sex</th>
                                        <th style="width: auto;">RSBSA Area WS2021</th>
										<th style="width: auto;">RSBSA Area DS2021</th>
                                        <th style="width: auto;">Assigned User</th>
                                        <th style="width: auto;">Previous E-Binhi benificiary?</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$('#farmersTbl').DataTable({
    "serverSide": true,
    "ajax": {
        "url": "{!! route('sed.dashboard.datatable') !!}",
        "type": "POST",
        "data": function(d) {
            d._token = "{{csrf_token()}}";
            d.municode = "{{$municode}}"
        }
    },
    columns: [{
            data: 'farmer_id',
            name: 'farmer_id'
        }, {
            data: 'fullname',
            name: 'fullname'
        },
        {
            data: 'provDesc',
            name: 'provDesc'
        },
        {
            data: 'citymunDesc',
            name: 'citymunDesc'
        },
        {
            data: 'contact_no',
            name: 'contact_no'
        },
        {
            data: 'ver_sex',
            name: 'ver_sex'
        },
        {
            data: 'farm_area_ws2021',
            name: 'farm_area_ws2021'
        },
        {
            data: 'farm_area_ds2021',
            name: 'farm_area_ds2021'
        },
        {
            data: 'createdBy',
            name: 'createdBy'
        },
        {
            data: 'has_claim',
            name: 'has_claim'
        }
    ]
});
</script>