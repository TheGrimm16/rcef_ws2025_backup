@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
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
    .x_content {
        padding: 0 5px 6px;
        float: left;
        clear: both;
        margin-top: 0; 
    }
  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Select a seed cooperative
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row">
                    <div class="col-md-10">
                        <select name="coop" id="coop" class="form-control">
                            <option value="0">Please select a seed cooperative</option>
                            @foreach ($cooperatives as $row)
                                <option value="{{$row->accreditation_no}}">{{$row->coopName}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success" id="load_btn"><i class="fa fa-database"></i> GENERATE LIST</button>
                    </div>
                </div>
                
            </div>
        </div><br>

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Participating Seed Growers List
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <table class="table table-hover table-striped table-bordered" id="sg_tbl">
                    <thead>
                        <th style="width:170px !important;">Full Name</th>
                        <th>Seed Tags</th>
                        <th style="width:100px !important;">Bags Passed</th>
                        <th style="width:110px !important;">Status</th>
                        <th style="width:40px !important;"><center>Action</center></th>
                    </thead>
                </table>
            </div>
        </div><br>        
    </div>

     <!-- SG TAGS MODAL -->
     <div id="sg_tags_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="sg_tags_name">
                        {SG_NAME}
                    </h4>
                    <span id="sg_tags_coop">{COOP_NAME}</span>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="sg_tags_tbl">
                                <thead>
                                    <th>SeedTag</th>
                                    <th>Variety</th>
                                    <th>Bags Passed</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SG TAGS MODAL -->

    <!-- BLACKLIST MODAL -->
    <div id="blacklist_sg_modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        CHANGE SEED GROWER STATUS: {BLACKLISTED}
                    </h4>
                </div>
                <div class="modal-body">
                    <p>You are about to change the classification of the selected seed grower as `blacklisted`, by doing so, you will disable the lots (passed) tagged to selected seed grower 
                        from the selection in the delivery process of the associated seed cooperative. Do you wish to proceed?</p>

                    <textarea name="blacklist_reason" id="blacklist_reason" class="form-control" rows="5" placeholder="Pleasee state a valid reason..."></textarea>
                        
                    <input type="hidden" value="" id="sg_id" name="sg_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="add_to_blacklist_btn"><i class="fa fa-tags"></i> Add to Blacklist</button>
                </div>
            </div>
        </div>
    </div>
    <!-- BLACKLIST MODAL -->

@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#sg_tbl").DataTable();
        $("#load_btn").on("click", function(e){
            var coop = $("#coop").val();

            $('#sg_tbl').DataTable().clear();
            $("#sg_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "pageLength": 100,
                "ajax": {
                    "url": "{{ route('sg.table') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        coop: coop
                    }
                },
                "columns":[
                    {"data": "full_name"},
                    {"data": "seed_tags", searchable: false},
                    {"data": "bags_passed", searchable: false},
                    {"data": "blacklist_status", searchable: false},
                    {"data": "action", searchable: false},
                ]
            });
        });

        $('#sg_tags_modal').on('show.bs.modal', function (e) {
            var sg_id = $(e.relatedTarget).data('id');
            var coop_accre = $(e.relatedTarget).data('coop');

            $.ajax({
                type: 'POST',
                url: "{{ route('load.sg.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    coop_accre: coop_accre,
                    sg_id: sg_id
                },
                success: function(data){
                    $("#sg_tags_name").empty().html(data["sg_name"]);
                    $("#sg_tags_coop").empty().html(data["coop_name"]);
                }
            }).done(function(e){
                $("#sg_tags_tbl").DataTable().clear();
                $("#sg_tags_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('load.sg.tags') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            "sg_id": sg_id,
                            "coop_accre": coop_accre
                        }
                    },
                    "columns":[
                        {"data": "seed_tag"},
                        {"data": "seedVariety"},
                        {"data": "bags_passed"}
                    ]
                });
            });
        });

        $('#blacklist_sg_modal').on('show.bs.modal', function (e) {
            var sg_id = $(e.relatedTarget).data('id');
            $("#sg_id").val(sg_id);
            $("#blacklist_reason").val('');
        });

        $("#add_to_blacklist_btn").on("click", function(e){
            var sg_id = $("#sg_id").val();
            var blacklist_reason = $("#blacklist_reason").val();
            var coop_accre = $("#coop").val();
            
            if($("#blacklist_reason").val() == ''){
                alert("Please state a valid reason for this action.");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('coop.member.blacklist') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        sg_id: sg_id,
                        blacklist_reason: blacklist_reason
                    },
                    success: function(data){
                        $('#blacklist_sg_modal').modal('hide');
                    }
                }).done(function(){
                    $('#sg_tbl').DataTable().clear();
                    $("#sg_tbl").DataTable({
                        "bDestroy": true,
                        "autoWidth": false,
                        "searchHighlight": true,
                        "processing": true,
                        "serverSide": true,
                        "orderMulti": true,
                        "order": [],
                        "pageLength": 100,
                        "ajax": {
                            "url": "{{ route('sg.table') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}"
                            }
                        },
                        "columns":[
                            {"data": "seed_coop", searchable: false},
                            {"data": "full_name"},
                            {"data": "bags_passed", searchable: false},
                            {"data": "blacklist_status", searchable: false},
                            {"data": "action", searchable: false},
                        ]
                    });
                });
            }
        });
    </script>
@endpush
