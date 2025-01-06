@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')

<div class="clearfix"></div>

@include('layouts.message')

<div class="row">
    <div class="col-md-5">
        <div class="x_panel">
            <div class="x_title">
                <h2>SEARCH BATCH TICKET #</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-group">
                    <div class="row">
                        <!--<input type="text" class="form-control" name="batch_number" id="batch_number" placeholder="Ex: 511-BCH-1583868172">-->
                        <select name="batch_number" id="batch_number" class="form-control">
                            <option value="0">Select a batch ticket number</option>
                            @foreach ($pending_deliveries as $row)
                                <option value="{{$row["batchTicketNumber"]}}">{{$row["batchTicketNumber"]}} ({{$row["total_bags"]}} bags)</option>
                            @endforeach
                        </select>
                    </div>
                    
                </div>
                <div class="form-group">
                    <button id="search_batch_btn" class="btn btn-success"><i class="fa fa-search-plus"></i> SEARCH DELIVERY</button>
                </div>
            </div>
        </div><br>

        <div class="x_panel">
            <div class="x_title">
                <h2 id="batch_number_title">{BATCH_TICKET_NUMBER}</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Seed coop:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <textarea name="seed_coop" id="seed_coop" rows="3" class="form-control" disabled></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Region:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="region" id="region" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Province:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="province" id="province" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Municipality:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="municipality" id="municipality" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Dropoff point:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="dop" id="dop" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">DOP ID:</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="dop_id" id="dop_id" disabled>
                        </div>
                    </div>
                </div>
                <hr>
                <button id="edit_dop_btn" data-toggle="modal" data-target="#edit_dop_modal" class="btn btn-warning btn-block"><i class="fa fa-edit"></i> EDIT DROPOFF POINT</button>
            </div>
        </div>
    </div>

    <div class="col-md-7" id="seed_tag_col" style="display: none">
        <div class="x_panel">
            <div class="x_title">
                <h2 id="batch_number_title">SEED TAG LIST: </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content" id="seed_tag_div">
                
            </div>
        </div>
    </div>

    <!-- EDIT SEED TAG MODAL -->
    <div id="show_seedtag_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="seedtag_modal_title">
                        {BATCH_TICKET_NUMBER}
                    </h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="tbl_delivery_id" name="tbl_delivery_id">
                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Tag:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" name="seedTag" id="seedTag" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Seed Variety:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <select name="seedVariety" id="seedVariety" class="form-control" style="width: 100%">
                                    @foreach ($varities as $row)
                                        <option value="{{$row->variety}}">{{$row->variety}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-2">Bag Count:</label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="number" class="form-control" name="totalBagCount" id="totalBagCount" min="0" max="200">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle"></i> CLOSE</button>
                    <button id="edit_seedtag_btn" type="button" class="btn btn-warning"><i class="fa fa-edit"></i> EDIT SEEDTAG INFO</button>
                </div>
            </div>
        </div>
    </div>
    <!-- EDIT SEED TAG MODAL -->


    <!-- EDIT SEED TAG MODAL -->
    <div id="edit_dop_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        EDIT DROPOFF POINT
                    </h4>
                </div>
                <div class="modal-body">
                    <select name="dop_edit" id="dop_edit" class="form-control" style="width:100%">
                        @foreach ($dop_list as $dop_row)
                            <option value="{{$dop_row->prv_dropoff_id}}">{{$dop_row->region}} < {{$dop_row->province}} < {{$dop_row->municipality}} < {{$dop_row->dropOffPoint}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button id="edit_dop_modal_btn" type="button" class="btn btn-warning"><i class="fa fa-edit"></i> EDIT DROPOFF POINT</button>
                </div>
            </div>
        </div>
    </div>
    <!-- EDIT SEED TAG MODAL -->

</div>


@endsection
@push('scripts')
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script src=" {{ asset('public/js/select2.min.js') }} "></script>
<script src=" {{ asset('public/js/parsely.js') }} "></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>

        $("#seedVariety").select2();
        $("#dop_edit").select2();
        $("#batch_number").select2({
            tags: true
        });

        function load_batch_details(batch_number){

            seed_tag_div = '';
            seed_tag_div += '<img src="{{asset('public/images/load_tags.gif')}}" alt="" style="display: block;margin: auto;height: 370px;">';
            $("#seed_tag_div").empty().append(seed_tag_div);

            $.ajax({
                type: 'POST',
                url: "{{ route('edit_delivery.check_batch') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    batch_number: batch_number
                },
                success: function(data){
                   if(data == "already_inspected"){
                        alert("This delivery is already inspected, you can no longer edit it.");
                        $("#seed_coop").val("");
                        $("#region").val("");
                        $("#province").val("");
                        $("#municipality").val("");
                        $("#dop").val("");
                        $("#dop_id").val("");
                        $("#batch_number_title").empty().html("{BATCH_TICKET_NUMBER}");
                   }else if(data == "cancelled_delivery"){
                        alert("This delivery is already cancelled, you can no longer edit it.");
                        $("#seed_coop").val("");
                        $("#region").val("");
                        $("#province").val("");
                        $("#municipality").val("");
                        $("#dop").val("");
                        $("#dop_id").val("");
                        $("#batch_number_title").empty().html("{BATCH_TICKET_NUMBER}");

                   }else{
                        $("#seed_coop").val(data["seed_coop"]);
                        $("#region").val(data["region"]);
                        $("#province").val(data["province"]);
                        $("#municipality").val(data["municipality"]);
                        $("#dop").val(data["dop"]);
                        $("#batch_number_title").empty().html(data["batch_number"]);
                        $("#dop_id").val(data["prv_dropoff_id"]);

                        $("#seed_tag_div").empty();
                        seed_tag_div = '';

                        jQuery.each(data["seeds_list"], function(index, array_value){
                            var card_label = array_value;
                            var split_label = card_label.split(" | ");

                            seed_tag_div = seed_tag_div + '<div class="card">';
                            seed_tag_div = seed_tag_div + '<div class="card-header" id="headingOne">';
                            seed_tag_div = seed_tag_div + '<h5 class="mb-0" style="margin:0">';
                            seed_tag_div = seed_tag_div + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">';
                            seed_tag_div = seed_tag_div + array_value;
                            seed_tag_div = seed_tag_div + '</button>';
                            seed_tag_div = seed_tag_div + '<a href="#" data-toggle="modal" data-target="#show_seedtag_modal" data-tag="'+split_label[0]+'" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-search-plus"></i> View Seed Tag</a>';
                            seed_tag_div = seed_tag_div + '</h5>';
                            seed_tag_div = seed_tag_div + '</div>';
                            seed_tag_div = seed_tag_div + '</div>';
                        });
                        $("#seed_tag_div").append(seed_tag_div)
                   }
                }
            });
        }

        function get_seedtag_data(batch_number, seed_tag){
            $("#seedTag").val("loading...");
            $("#totalBagCount").val("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('edit_delivery.seedtag_info') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    batch_number: batch_number,
                    seed_tag: seed_tag
                },
                success: function (data) {
                    $("#seedTag").val(data["seedTag"]);
                    $("#totalBagCount").val(data["totalBagCount"]);   
                    $("#seedVariety").val(data["seedVariety"]).change();
                    $("#tbl_delivery_id").val(data["deliveryId"])
                }
            });
        }

        $('#show_seedtag_modal').on('show.bs.modal', function (e) {
            var batch_number = $("#batch_number").val();
            $("#seedtag_modal_title").empty().html("EDIT: "+batch_number);
            var seed_tag = $(e.relatedTarget).data('tag');
            get_seedtag_data(batch_number, seed_tag);
        });

        $("#edit_dop_modal").on('show.bs.modal', function (e) {
            var batch_number = $("#batch_number").val();
            var dop_id = $("#dop_id").val();

            $("#dop_edit").val(dop_id).change();
        });

        $("#search_batch_btn").on("click", function(e){
            var batch_number = $("#batch_number").val();

            $("#seed_coop").val("loading...");
            $("#region").val("loading...");
            $("#province").val("loading...");
            $("#municipality").val("loading...");
            $("#dop").val("loading...");
            $("#dop_id").val("loading...");
            $("#batch_number_title").empty().html("loading...");

            $("#seed_tag_div").empty();
            $("#seed_tag_col").css("display", "block");
            load_batch_details(batch_number)
        });

        $("#edit_dop_modal_btn").on("click", function(e){
            var batch_number = $("#batch_number").val();
            var dop_id =  $("#dop_edit").val();

            $("#seed_coop").val("loading...");
            $("#region").val("loading...");
            $("#province").val("loading...");
            $("#municipality").val("loading...");
            $("#dop").val("loading...");
            $("#dop_id").val("loading...");
            $("#batch_number_title").empty().html("loading...");

            $.ajax({
                type: 'POST',
                url: "{{ route('edit_delivery.update_dop') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    batch_number: batch_number,
                    dop_id: dop_id
                },
                success: function (data) {
                    $("#seed_tag_div").empty();
                    $("#seed_tag_col").css("display", "block");
                    load_batch_details(batch_number)
                }
            }).done(function(e){
                $("#edit_dop_modal").modal("hide");
            });
        });

        $("#edit_seedtag_btn").on("click", function(e){
            var seed_tag = $("#seedTag").val();
            var total_bag = $("#totalBagCount").val();   
            var seed_variety = $("#seedVariety").val();
            var batch_number = $("#batch_number").val();
            var tbl_delivery_id = $("#tbl_delivery_id").val();

            $("#edit_seedtag_btn").empty().html("saving data...");

            if(total_bag > 200){
                alert("bag count must not exceed 200 bags per lot");
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ route('edit_delivery.update_seedtag') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        batch_number: batch_number,
                        seed_tag: seed_tag,
                        seed_variety: seed_variety,
                        total_bag: total_bag,
                        tbl_delivery_id: tbl_delivery_id
                    },
                    success: function (data) {
                        if(data == "exceeded_max_volume_per_lot"){
                            alert("Action Invalid: the seed tag has exceeded its maximum allocation of 200 bags, please double-check the inputted bag count.");
                            $("#edit_seedtag_btn").empty().html('<i class="fa fa-edit"></i> EDIT SEEDTAG INFO');

                        }else if(data == "edit_success"){
                            $("#edit_seedtag_btn").empty().html('<i class="fa fa-edit"></i> EDIT SEEDTAG INFO');
                            load_batch_details(batch_number);
                        }
                    }
                }).done(function(e){
                    $("#show_seedtag_modal").modal("hide");
                });
            }
        });
    </script>
@endpush
