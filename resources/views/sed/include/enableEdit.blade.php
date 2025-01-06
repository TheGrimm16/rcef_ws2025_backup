<style>
    .help-block.text-danger{
        color: #D9534F;
        font-style:italic;
    }
</style>
<form class="form-horizontal" id="enableEditForm">
    <input type="hidden" name="_token" value="{{csrf_token()}}" />
    <input type="hidden" name="id" value="{{$data['id']}}" />
    <input type="hidden" name="value" value="{{$data['value']}}" />
    <div class="container" style="padding: 30px">
        @if($data['value'] == 1)
        <div class="row">
            <div class="col-12">
                <h4 style=" margin-bottom:20px">ENABLE EDIT FARMER DETAILS</h4>
            </div>
        </div>
       
       
        <div class="form-group row">
            <div class="col-12">
                <label for="remarks" class="control-label">REMARKS </label>
            </div>
            <div class="col-sm-12">
                <input id="remarks" name="remarks" type="text" class="form-control" value="" required>
            </div>
        </div>
        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right" id="enableEditSave">Save</button>
        </div>
        @else
        <div class="row">
            <div class="col-12">
                <h4 style=" margin-bottom:20px">DISABLE EDIT?</h4>
            </div>
        </div>
       
        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right" id="enableEditSave">YES</button>
            <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
        </div>
        @endif
    </div>
</form>
<script>

    $("#enableEditSave").on("click", function (e) {
        e.preventDefault();
        // $('#verifyModal').modal('hide');
        // var answer = window.confirm("Save data?");
        // if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sed/enable/edit')}}",
                data: $("#enableEditForm").serialize(),
                success: function (response) {
                    
                    if(response.status == 4){
                        var arr = response.message;
                        let id = "";
                        $('.help-block.text-danger').empty();
                        for (let i = 0; i < arr.length; i++) {
                            id = "#" + arr[i].key;
                            // $("'"+id+"'").siblings('.help-block').empty();
                            $(id).after( '<span class="help-block text-danger"><small>'+ arr[i].value +'</small></span>' );
                        }
                    }else{
                        // alert(response.message);
                        $('#checkParti2').modal('hide');
                    }
                    
                }
            });
        // }
        // else {
        //     //some code
        // }  
    });




</script>