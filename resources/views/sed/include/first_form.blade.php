<div class="container" style="{{($farmer->enableEdit == 0)? '' : 'display: none !important;' }}">
    <div class="row">
        <div class="col-md-12" style="text-align: right">
            <button class="btn btn-danger" type="button" style="background-color:transparent; border:none; color: red;font-size: 30px;" id="cancelTransaction"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="row" style="margin-top:20px;margin-bottom: 20px">
        <div class="col-md-12" style="text-align: center">
            <button class="btn btn-danger" type="button" id="callFailed">CALL FAILED</button>
        </div>
    </div>
    <div class="row" style="padding: 30px">
        <div class="col-md-12" style="text-align: center">
            <span style="font-size: 17px">Is the Farmer willing to participate in e-Binhi Phase III?</span>
        </div>
    </div>
    <div class="row" style="margin-bottom: 30px">
        <div class="col-md-6" style="text-align: right">
            <button class="btn btn-warning" type="button" id="noForm">NO</button>
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary" type="button" id="yesForm">YES</button>
        </div>
    </div>
</div>

<script>
    
    $("#cancelTransaction").on("click", function (e) {
        e.preventDefault();
        var answer = window.confirm("Cancel transaction?");
        if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sed/verification/form/cancel')}}",
                data: {
                    verified_id: "{{$farmer->sed_id}}",
                    _token: "{{csrf_token()}}"
                },
                success: function (response) {
                    // alert(response.message);
                    $('#checkParti').modal('hide');
                }
            });
        }
        else {
            //some code
        }  
    });

    $("#callFailed").on("click", function (e) {
        e.preventDefault();
        var answer = window.confirm("Call Failed?");
        if (answer) {
            $.ajax({
                type: "POST",
                url: "{{url('sed/verification/form/failed')}}",
                data: {
                    verified_id: "{{$farmer->sed_id}}",
                    _token: "{{csrf_token()}}"
                },
                success: function (response) {
                    alert(response.message);
                    $('#checkParti').modal('hide');
                }
            });
        }
        else {
            //some code
        }  
    });

    $("#yesForm").on("click", function () {
        var farmerid = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{url('sed/verification/form')}}",
            data: {
                farmerid: "{{$farmer->sed_id}}",
                _token: "{{csrf_token()}}"
            },
            success: function (response) {
                $('#checkParti').modal('hide');
                if(typeof response.error === 'undefined'){
                    $("#verifyModalContent").html(response);
                    $('#verifyModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }else{
                    alert(response.message);
                }
                farmersTbl.ajax.reload( null, false );
            }
        });	
    });

    $("#noForm").on("click", function () {
        var farmerid = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{url('sed/verification/form/no')}}",
            data: {
                farmerid: "{{$farmer->sed_id}}",
                _token: "{{csrf_token()}}"
            },
            success: function (response) {
                $('#checkParti').modal('hide');
                if(typeof response.error === 'undefined'){
                    $("#verifyModalContent").html(response);
                    $('#verifyModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }else{
                    alert(response.message);
                }
                farmersTbl.ajax.reload( null, false );
            }
        });	
    });

    var forEdit = "{{$farmer->enableEdit}}";
    var status = "{{$farmer->status}}";
    if(forEdit == 1){
        if(status == 1){
            $("#yesForm").trigger("click");
        }else if(status == 2){
            $("#noForm").trigger("click");
        }
    }
</script>