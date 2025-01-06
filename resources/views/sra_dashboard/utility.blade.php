@extends('layouts.index')

@section('content')
<div>
    <div class="page-title">
        <div class="title_left">
            <h3>E-binhi Utility</h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title container">
                    <div class="row">
                        <div class="col-md-8">
                            <h2>E-binhi participants tagging</h2>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="container">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="province" class="control-label">Province <strong
                                        style="color:red">*</strong></label>
                                <select id="province" name="province"
                                    class="js-example-basic-single js-states select form-control"
                                    style="width: 100% !important" required>
                                    <option value="" disabled selected>Select Province</option>

                                    @foreach($provinces as $k => $p)

                                    <option value="{{$p->prv_code}}">{{$p->province_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button name="submit" type="submit" class="btn btn-primary pull-right"
                                    style="margin-top: 25px" id="viewUntagged">View Untagged</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="x_panel">
                <div class="x_title container">
                    <div class="row">
                        <div class="col-md-8">
                            <h2>Results</h2>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="container">
                        <div class="form-group row">
                            <div class="col-sm-1" >Province Name: <input type="hidden" id="prvcode"></div>
                            <div id="provinceName" style="font-weight:bolder" class="col-sm-1"></div>
                            <div class="col-sm-1">Untagged Count:</div>
                            <div id="untaggedCount" style="font-weight:bolder" class="col-sm-1"></div>
                            <div class="col-sm-1">
                                <button name="submit" type="submit" class="btn btn-primary pull-right"
                                    style="margin-top: 0px" id="tagBtn">Tag</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>


@endsection

@push('scripts')
<script>
$("#province").select2({
    width: 'resolve'
});

$("#viewUntagged").click(function(e) {
    e.preventDefault();
     $.ajax({
         type: "POST",
         url: "{{url('sra/utility/untagged/count')}}",
         data: {
             municode: $("#province").val(),
             _token: "{{csrf_token()}}"
         },
         success: function (response) {
             $("#prvcode").val(response.prvcode);
             $("#provinceName").html(response.province_name);
             $("#untaggedCount").html(response.farmer_count);
         }
     });
});

$("#tagBtn").click(function(e) {
    e.preventDefault();
     $.ajax({
         type: "POST",
         url: "{{url('sra/utility/tag')}}",
         data: {
             municode: $("#prvcode").val(),
             _token: "{{csrf_token()}}"
         },
         success: function (response) {
            alert(response.message);
         }
     });
});
</script>
@endpush