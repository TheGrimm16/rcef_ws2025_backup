<form class="form-horizontal" id="editdopForm" method="POST" autocomplete="off">
    <input type="hidden" name="_token" value="{{csrf_token()}}" />
    <input type="hidden" name="id" value="{{$data->ebinhi_dopID}}" />

    <div class="container" style="padding: 30px">
        <div class="row">
            <div class="col-sm-12">
                <h4 style=" ">Province: {{$data->province}}</h4>
            </div>
            <div class="col-sm-12">
                <h4 style=" ">Municipality: {{$data->municipality}}</h4>
            </div>
            <div class="col-sm-12">
                <h4 style=" ">Drop Off Point: {{$data->pickup_location}}</h4>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="dop" class="control-label">New Drop Off Point <strong style="color:red">*</strong></label>
                <input id="dop" name="dop" type="text" class="form-control" required value="">
            </div>
        </div>


        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right">Save</button>
            <button name="updateFarmers" id="updateFarmers" class="btn btn-info pull-right">Save And Update Scheduled Farmers</button>
            <button class="btn btn-light pull-right" data-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>
<script>



$("#editdopForm").on("submit", function(e) {
    e.preventDefault();
    var answer = window.confirm("Save data?");
    if (answer) {
        $.ajax({
            type: "POST",
            url: "{{url('sed/dop/form/edit')}}",
            data: $(this).serialize(),
            success: function(response) {

                alert(response.message);
                if (response.status == 1) {
                    $('#checkParti2').modal('hide');
                    dopTbl.ajax.reload(null, false);
                }

            }
        });
    } else {
        //some code
    }
});

$("#updateFarmers").on("click", function(e) {
    e.preventDefault();
    var answer = window.confirm("Save data and update scheduled farmers?");
    if (answer) {
        $.ajax({
            type: "POST",
            url: "{{url('sed/dop/form/edit/farmers')}}",
            data: $("#editdopForm").serialize(),
            success: function(response) {

                alert(response.message);
                if (response.status == 1) {
                    $('#checkParti2').modal('hide');
                    dopTbl.ajax.reload(null, false);
                }

            }
        });
    } else {
        //some code
    }
});
</script>