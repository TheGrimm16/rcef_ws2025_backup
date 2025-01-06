<form class="form-horizontal" id="userForm" method="POST" autocomplete="off">
    <input type="hidden" name="_token" value="{{csrf_token()}}" />

    <div class="container" style="padding: 30px">
        <div class="row">
            <div class="col-sm-12">
                <h4 style=" margin-bottom:20px">USER</h4>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="username" class="control-label">Username <strong style="color:red">*</strong></label>
                <input id="username" name="username" type="text" class="form-control" required value="">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="contact_no" class="control-label">Contact Number <strong
                        style="color:red">*</strong></label>
                <input id="contact_no" name="contact_no" type="number" class="form-control" required value="">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-6">
                <label for="firstName" class="control-label">First Name <strong style="color:red">*</strong></label>
                <input id="firstName" name="firstName" type="text" class="form-control" required value="">
            </div>
            <div class="col-sm-6">
                <label for="middleName" class="control-label">Middle Name </label>
                <input id="middleName" name="middleName" type="text" class="form-control" value="">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-9">
                <label for="lastName" class="control-label">Last Name <strong style="color:red">*</strong></label>
                <input id="lastName" name="lastName" type="text" class="form-control" value="" required>
            </div>
            <div class="col-sm-3">
                <label for="extName" class="control-label">Suffix </label>
                <input id="extName" name="extName" type="text" class="form-control" value="">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="sex" class="control-label">Sex</label>
                <select id="sex" name="sex" class="select form-control">

                    <?php 
                    $male = "";
                    $female = "";

                    if(isset($sed_verified->ver_sex)){
                        if(strtolower($sed_verified->ver_sex) == "male"){
                            $male = "selected";
                        }else if(strtolower($sed_verified->ver_sex) == "female"){
                            $female = "selected";
                        }
                    }
                    
                     ?>
                    <option value="" disabled selected>Sex</option>
                    <option value="male" {{$male}}>Male</option>
                    <option value="female" {{$female}}>Female</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-6">
                <label for="password" class="control-label">Password <strong style="color:red">*</strong></label>
                <input id="password" name="password" type="password" class="form-control" value="" required>
            </div>
            <div class="col-sm-6">
                <label for="confirmPassword" class="control-label">Confirm Password <strong
                        style="color:red">*</strong></label>
                <input id="confirmPassword" name="confirmPassword" type="password" class="form-control" value=""
                    required>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-6">
                <label for="email" class="control-label">Email <strong style="color:red">*</strong></label>
                <input id="email" name="email" type="email" class="form-control" value="" required>
            </div>
            <div class="col-sm-6">
                <label for="secondaryEmail" class="control-label">Alternate Email </label>
                <input id="secondaryEmail" name="secondaryEmail" type="email" class="form-control" value="">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="secondaryEmail" class="control-label">Assigned Province <strong
                        style="color:red">*</strong></label>
                <select id="province" name="province" class="js-example-basic-single js-states select form-control"
                    style="width: 100% !important" required>
                    <option value="" disabled selected>Select Province</option>

                    @foreach($provinces as $k => $p)
                    <?php 
                        $selected_p = "";
                        
                        // if(isset($sed_verified->preffered_variety1)){
                        //     if($sed_verified->preffered_variety1 == $v1->variety){
                        //         $selected_v1 = "selected";
                        //     }
                        // }
                    ?>
                    <option value="{{$p->regCode}}{{$p->provCode}}" {{$selected_p}}>{{$p->province}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="municipality" class="control-label">Municipality <strong
                        style="color:red">*</strong></label>
                <select id="municipality" name="municipality"
                    class="js-example-basic-single js-states select form-control" style="width: 100% !important"
                    required>
                    <option value=""></option>
                </select>
            </div>
        </div>


        <div class="form-group row" style="margin-top: 20px">
            <button name="submit" type="submit" class="btn btn-primary pull-right">Save</button>
            <button class="btn btn-light pull-right" id="dismissModal">Cancel</button>
        </div>
    </div>
</form>
<script>
$("#municipality").prop("disabled", true);
$("#province").select2({
    width: 'resolve'
});
$("#municipality").select2({
    width: 'resolve'
});
$("#province").on('change', function() {
    if ($(this).val() == "") {
        $("#municipality").prop("disabled", true);
        $("#municipality").val("");
    } else {
        $("#municipality").prop("disabled", true);
        $("#municipality").val("Loading...");
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
$("#dismissModal").on("click", function(e) {
    e.preventDefault();
    $('#userModal').modal('hide');
});

$("#userForm").on("submit", function(e) {
    e.preventDefault();
    var answer = window.confirm("Save data?");
    if (answer) {
        $.ajax({
            type: "POST",
            url: "{{url('sed/users/form/save')}}",
            data: $(this).serialize(),
            success: function(response) {

                alert(response.message);
                if (response.status == 0) {
                    $('#userModal').modal('hide');
                    $('#userForm').trigger("reset");
                    usersTbl.ajax.reload(null, false);
                }

            }
        });
    } else {
        //some code
    }
});
</script>