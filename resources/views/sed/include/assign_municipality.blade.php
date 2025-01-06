<style>
     .help-block.text-danger{
        color: #D9534F;
        font-style:italic;
    }
</style>
<form class="form-horizontal" id="assignForm" method="POST" autocomplete="off">
    <input type="hidden" name="_token" value="{{csrf_token()}}" />
    <input type="hidden" name="userID" value="{{$users->userId}}" />
 
    <div class="container" style="padding: 30px">
        <div class="row">
            <div class="col-sm-12">
                <h4 style=" margin-bottom:20px">Assign Municipality</h4>
            </div>
            <div class="col-sm-12">
                <h6 style=" margin-bottom:20px">{{$users->firstName}} {{$users->middleName}} {{$users->lastName}} {{$users->extName}}</h6>
                <h6 style=" margin-bottom:20px">User Code: {{$users->userId}}</h6>
            </div>
        </div>
       
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="secondaryEmail" class="control-label">Province <strong style="color:red">*</strong></label>
                <select id="province" name="province" class="js-example-basic-single js-states select form-control" style="width: 100% !important" required>
                    <option value="" disabled selected>Select Province</option>
                   
                    @foreach($provinces as $k => $p)
                    <?php 
                        $selected_p = "";
                        
                        if(isset($users->province)){
                            if($users->province == $k){
                                $selected_p = "selected";
                            }
                        }
                    ?>
                    <option value="{{$k}}" {{$selected_p}}>{{$p}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="municipality" class="control-label">Municipality <strong style="color:red">*</strong></label>
                <select id="municipality" name="municipality" class="js-example-basic-single js-states select form-control" style="width: 100% !important" required>
                        <option value="" disabled selected>Select Municipality</option>
                        
                        @foreach($municipalities as $k => $m)
                        <?php 
                            $selected_m = "";
                            
                            if(isset($users->municipality)){
                                if($users->municipality == $k){
                                    $selected_m = "selected";
                                }
                            }
                        ?>
                        <option value="{{$k}}" {{$selected_m}}>{{$m}}</option>
                        @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-6">
                <label for="secondaryEmail" class="control-label">Total no. of farmers: </label>
                <span  class="" id="total_farmers">
                    
                </span>
            </div>
            <div class="col-sm-6">
                <label for="secondaryEmail" class="control-label">Unallocated no. of farmers: </label>
                <span  class="" id="unallocated">
                    
                </span>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-12">
                <label for="contactno" class="control-label">Allocate farmers</label>
                <input type="hidden" name="max_id" id="max_id" >
                <input type="hidden" name="min_id" id="min_id" >
            </div>
            <div class="col-sm-6">
                <label for="min" class="control-label">min id</label>
                <input id="min" name="min" type="number" id="min" class="form-control" value="" disabled>
            </div>
            <div class="col-sm-6">
                <label for="max" class="control-label">max id</label>
                <input id="max" name="max" id="max" type="number" class="form-control" value="" >
            </div>
        </div>
        
       
        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right">Save</button>
            <button class="btn btn-light pull-right" id="dismissModal">Cancel</button>
        </div>
    </div>
</form>
<script>
    // $("#municipality").prop("disabled", true);
    $("#province").select2({
        width: 'resolve'
    });
    $("#municipality").select2({
        width: 'resolve'
    });
    $("#province").on('keyup change', function() {
        $("#min").val(0);
        $("#min_id").val(0);
        $("#max").val(0);
        $("#max_id").val(0);
        $("#total_farmers").html(0);
        $("#unallocated").html(0);
        $('.help-block.text-danger').empty();
        if ($(this).val() == "") {
            $("#municipality").prop("disabled", true);
            $("#municipality").val("");
        } else {

            $.ajax({
                type: "POST",
                url: "{{url('sed/municipality')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    provCode: $(this).val()
                },
                success: function(response) {
                    $("#municipality").prop("disabled", false);
                    obj = JSON.parse(response);

                    $('#municipality').empty();
                    $('#municipality').append($('<option>').val("").text(""));
                    obj.forEach(data => {
                        $('#municipality').append($('<option>').val(data.citymunCode).text(
                            data.citymunDesc));
                    });
                }
            });
        }

    });
    $("#dismissModal").on("click", function (e) {
        e.preventDefault();
        $('#assignMunicipality').modal('hide');
    });

    $("#assignForm").on("submit", function (e) {
        e.preventDefault();
        var answer = window.confirm("Save data?");
        if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sed/users/assign/municipality/save')}}",
                data: $(this).serialize(),
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
                        alert(response.message);
                        $('#assignMunicipality').modal('hide');
                        $('#assignForm').trigger("reset");
                    }
                    
                }
            });
        }
        else {
            //some code
        }  
    });
    load_allocation_data();
    function load_allocation_data(){
        $.ajax({
            type: "POST",
            url: "{{url('sed/load/allocation/details')}}",
            data: {
                _token: "{{ csrf_token() }}",
                muni_code: $("#municipality").val()
            },
            success: function (response) {
                $('.help-block.text-danger').empty();
                $("#min").val(response['min']);
                $("#min_id").val(response['min']);
                $("#max").val(response['max']);
                $("#max_id").val(response['max']);
                $("#total_farmers").html(response['max']);
                $("#unallocated").html(response['unallocated']);
            }
        });
    }

    $("#municipality").on("keyup change", function(){
        load_allocation_data();
    });
    

</script>