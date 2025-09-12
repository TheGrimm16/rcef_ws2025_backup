/* HOLDON JS CONFIG*/
const holdon_options = {
    theme: "sk-bounce",
    message: 'Loading...',
    textColor: "white"
}
/* END OF HOLDON JS CONFIG */

const _token = $('input[name="_token"]').val();

$(document).ready(function () {
    $('#upcoming_harvest_weekly').DataTable({
        columns: [
            {data: 'cooperative', name: 'cooperative'},
            {data: 'variety', name: 'variety'},
            {data: 'week1', name: 'week1'},
            {data: 'week2', name: 'week2'},
            {data: 'week3', name: 'week3'},
            {data: 'week4', name: 'week4'}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'upcoming_harvest_weekly',
            method: 'GET'
        }
    });

    $('#upcoming_harvest_10days').DataTable({
        columns: [
            {data: 'cooperative', name: 'cooperative'},
            {data: 'province', name: 'province'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'upcoming_harvest_10days',
            method: 'GET',
        }
    }).on('click', '.view_seed_growers', function () {
        HoldOn.open(holdon_options)
        let coopId = this.id

        $('#harvest_seed_grower_table').DataTable({
            columns: [
                {data: 'name', name: 'name'},
                {data: 'variety_planted', name: 'variety_planted'},
                {data: 'harvest_estimate', name: 'harvest_estimate'},
                {data: 'harvesting_date', name: 'harvesting_date'},
                {data: 'availability_date', name: 'availability_date'}
            ],
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'harvest_seed_growers',
                method: 'POST',
                data: {
                    _token: _token,
                    coopId: coopId,
                    days: 10,
                }
            },
            initComplete: function () {
                $('#harvest_seed_grower_modal').modal('toggle')
                HoldOn.close()
            }
        })
    })

    $('#upcoming_harvest_30days').DataTable({
        columns: [
            {data: 'cooperative', name: 'cooperative'},
            {data: 'province', name: 'province'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'upcoming_harvest_30days',
            method: 'GET',
        }
    }).on('click', '.view_seed_growers', function () {
        HoldOn.open(holdon_options)
        let coopId = this.id

        $('#harvest_seed_grower_table').DataTable({
            columns: [
                {data: 'name', name: 'name'},
                {data: 'variety_planted', name: 'variety_planted'},
                {data: 'harvest_estimate', name: 'harvest_estimate'},
                {data: 'harvesting_date', name: 'harvesting_date'},
                {data: 'availability_date', name: 'availability_date'}
            ],
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'harvest_seed_growers',
                method: 'POST',
                data: {
                    _token: _token,
                    coopId: coopId,
                    days: 30
                }
            },
            initComplete: function () {
                $('#harvest_seed_grower_modal').modal('toggle')
                HoldOn.close()
            }
        })
    })

    $('#seed_coop_table').DataTable({
        columns: [
            {data: 'name', name: 'name'},
            {data: 'province', name: 'province'},
            {data: 'area_planted', name: 'area_planted'},
            {data: 'transplanting_date', name: 'transplanting_date'},
            {data: 'harvesting_date', name: 'harvesting_date'},
            {data: 'availability_date', name: 'availability_date'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'seed_cooperatives',
            method: 'GET',
        }
    }).on('click', '.view_municipalities', function () {
        HoldOn.open(holdon_options)
        let coopId = this.id

        $('#municipalities_table').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'cooperative_municipalities',
                method: 'POST',
                data: {
                    _token: _token,
                    coopId: coopId
                }
            },
            columns: [
                {data: 'name', name: 'name'},
                {data: 'variety_planted', name: 'variety_planted'},
                {data: 'area_planted', name: 'area_planted'},
                {data: 'harvest_estimate', name: 'harvest_estimate'},
                {data: 'harvesting_date', name: 'harvesting_date'},
                {data: 'availability_date', name: 'availability_date'}
            ],
            initComplete: function () {
                $('#municipalities_modal').modal('toggle')
                HoldOn.close()
            }
        })
    })

    $('#seed_growers_table').DataTable({
        columns: [
            {data: 'name', name: 'name'},
            {data: 'cooperative', name: 'cooperative'},
            {data: 'province', name: 'province'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'seed_growers',
            method: 'GET',
        }
    }).on('click', '.view_seed_grower', function () {
        HoldOn.open(holdon_options)
        let accreditation_number = this.id

        $.ajax({
            url: 'seed_grower_profile',
            method: 'POST',
            data: {
                _token: _token,
                accreditation_number: accreditation_number
            },
            dataType: 'json',
            success: function (source) {
                if (source) {
                    $('#seed_grower_profile_table #name').html(source['Name'])
                    $('#seed_grower_profile_table #accreditation_no').html(source['Code_Number'])
                } else {
                    $('#seed_grower_profile_table #name').html('')
                    $('#seed_grower_profile_table #accreditation_no').html('')
                }
            }
        })

        $('#seed_growers_details_table').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'seed_grower_details',
                method: 'POST',
                data: {
                    _token: _token,
                    accreditation_number: accreditation_number
                }
            },
            columns: [
                {data: 'variety_planted', name: 'variety_planted'},
                {data: 'area_planted', name: 'area_planted'},
                {data: 'date_planted', name: 'date_planted'},
                {data: 'harvest_estimate', name: 'harvest_estimate'},
                {data: 'harvesting_date', name: 'harvesting_date'},
                {data: 'availability_date', name: 'availability_date'}
            ],
            initComplete: function () {
                $('#seed_growers_details_modal').modal('toggle')
                HoldOn.close()
            }
        })


    })

    //  $('#coop_table').DataTable({
    //     columns: [
    //         {data: 'name', name: 'name'},
    //         {data: 'commit', name: 'commit', orderable: false, searchable: false},
    //         {data: 'confirm', name: 'confirm', orderable: false, searchable: false},
    //         {data: 'inspect', name: 'inspect', orderable: false, searchable: false},
    //         {data: 'actions', name: 'actions', orderable: false, searchable: false}
    //     ],
    //     processing: true,
    //     serverSide: true,
    //     ajax: {
    //         url: 'cooperatives',
    //         method: 'GET',
    //     }
    // }).on('click', '.view_seed_growers', function() {
    //     HoldOn.open(holdon_options)
    //     let coopId = this.id
    //
    //     $('#seed_grower_table').DataTable({
    //         columns: [
    //             {data: 'Name', name: 'Name'},
    //             {data: 'Seed_variety', name: 'Seed_variety'},
    //             {data: 'commitedBags', name: 'commitedBags'},
    //             {data: 'deliveredBags', name: 'deliveredBags'},
    //             {data: 'deliveredDate', name: 'deliveredDate'},
    //             {data: 'inspectedBags', name: 'inspectedBags'},
    //             {data: 'inspectedDate', name: 'inspectedDate'},
    //             {data: 'status', name: 'status'},
    //
    //         ],
    //         destroy: true,
    //         processing: true,
    //         serverSide: true,
    //         ajax: {
    //             url: 'sg_delivery_status',
    //             method: 'POST',
    //             datatype: "json",
    //             data: {
    //                 _token: _token,
    //                 coopId: coopId,
    //                 days: 30,
    //             }
    //         },
    //
    //         initComplete: function(settings, json) {
    //
    //             $('#seed_grower_modal').modal('toggle')
    //             HoldOn.close()
    //         }
    //     })
    // })
    $('#coop_table').DataTable({
        columns: [
            {data: 'name', name: 'name'},
            {data: 'commit', name: 'commit', orderable: false, searchable: false},
            {data: 'confirm', name: 'confirm', orderable: false, searchable: false},
            {data: 'inspect', name: 'inspect', orderable: false, searchable: false},
            {data: 'distribute', name: 'distribute', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'cooperatives',
            method: 'GET',
        }
    }).on('click', '.view_seed_growers', function () {
        HoldOn.open(holdon_options)
        let coopId = this.id
        $.ajax({
            url: 'coop_data',
            method: 'POST',
            data: {
                _token: _token,
                coopId: coopId
            },
            dataType: 'json',
            success: function (source) {
                if (source) {
                    $("#batch_modal #coop_name").text(source['coopname'])
                    $("#batch_modal #commited").text(source['bags'] + " bags")
                    $("#batch_modal #confirmed").text(source['confirmedBags'] + " bags")
                    $("#batch_modal #unconfirmed").text(source['unconfirmed'] + " bags")
                } else {
                    alert("something went wrong");
                }
            }
        })
        $('#batch_table').DataTable({
            columns: [
                {data: 'code', name: 'code'},
                {data: 'Seed_variety', name: 'Seed_variety'},
                {data: 'deliveredBags', name: 'deliveredBags'},
                {data: 'deliveredDate', name: 'deliveredDate'},
                {data: 'dropOffPoint', name: 'dropOffPoint'},
                {data: 'inspectedBags', name: 'inspectedBags'},
                {data: 'inspectedDate', name: 'inspectedDate'},
                {data: 'status', name: 'status'},
            ],
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'batch_status',
                method: 'POST',
                datatype: "json",
                data: {
                    _token: _token,
                    coopId: coopId,
                    days: 30,
                }
            },

            initComplete: function (settings, json) {
                $('#batch_modal').modal('toggle')
                HoldOn.close()
            }
        })
    })
    // input by timothy

    // jayvee dela cruz: error removed GET http://dbmp2.philrice.gov.ph/
    // $.ajax({
    //     url: location.protocol+"//dbmp2.philrice.gov.ph/rcef_ws2022/coop_summary_total",
    //     method: 'GET',
    //     dataType: 'json',
    //     success: function (source) {
    //         if (source) {
    //             $("#coop_total").text(source['coopTotal']);
    //             $("#sg_total").text(source['sgTotal']);
    //             $("#ha_total").text(source['haTotal']);
    //         } else {
    //             alert("something went wrong");
    //         }
    //     }
    // });

    $('#submit_iar_location').on('click', function () {
        let province = $('#distribution_province').val()
        let municipality = $('#distribution_municipality').val()
        let dropoff_point = $('#dropoff_point').val()
        HoldOn.open(holdon_options)

        $('#iar_table').DataTable({
            columns: [
                {data: 'batchno', name: 'batchno'},
                {data: 'date', name: 'date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'iar_list',
                method: 'POST',
                datatype: "json",
                data: {
                    _token: _token,
                    province: province,
                    municipality: municipality,
                    dropoff_point: dropoff_point,
                }
            },

            initComplete: function (settings, json) {
                $('#iar_modal').modal('toggle')
                HoldOn.close()
            }
        })

    })

    let usersTbl = $('#usersTbl').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        ajax: 'users/datatable',
        columns: [
            {data: 'name', name: 'name'},
            {data: 'username', name: 'username'},
            {data: 'email', name: 'email'},
            {data: 'roles', name: 'roles', orderable: false, searchable: false},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ]
    })

    let rolesTbl = $('#rolesTbl').DataTable({
        processing: true,
        serverSide: true,
        // stateSave: true,
        ajax: Laravel.tableRoute,
        columns: [
            {data: 'role', name: 'role'},
            {data: 'description', name: 'description'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        oLanguage: {
            sProcessing: 'Loading...'
        },
        order: [[0, 'asc']]
    })

    let permissionsTbl = $('#permissionsTbl').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        ajax: Laravel.tableRoute,
        columns: [
            {data: 'permission', name: 'permission'},
            {data: 'description', name: 'description'},
            {data: 'status', name: 'status'},
            {data: 'actions', name: 'actions'}
        ],
        oLanguage: {
            sProcessing: 'Loading...'
        },
        order: [[0, 'asc']]
    })
})

$('select[name="province"]').on('change', function () {
    HoldOn.open(holdon_options);
    var provCode = $(this).val();
    $('select[name="municipality"]').empty();
    $('input[name="region"]').empty();
    $.ajax({
        method: 'POST',
        url: 'province',
        data: {
            _token: _token,
            provCode: provCode
        },
        dataType: 'json',
        success: function (source) {
            $('select[name="municipality"]').append('<option>--SELECT ASSIGNED MUNICIPALITY--</option>');
            $.each(source, function (i, d) {
                $('select[name="municipality"]').append('<option value="' + d.citymunCode + '">' + d.citymunDesc + '</option>');
            });
        }
    });

    $.ajax({
        method: 'POST',
        url: 'region',
        data: {
            _token: _token,
            provCode: provCode
        },
        dataType: 'json',
        success: function (source) {
            $('input[name="region"]').val(source.regCode);
        }
    });

    HoldOn.close();
});

$('#search_farmer').autocomplete({
    source: "releasing/search_farmer",
    response: function (event, ui) {
        if (ui.content.length === 0) {
//            $('#add_farmer').empty()
//            $("#empty-message").text("No results found")
//            let add_farmer_btn = "<button class='btn btn-primary' onclick='add_farmer()'><i class='fa fa-plus'></i> Add RSBSA Stub / Control #</button>"
//            $('#add_farmer').append(add_farmer_btn)
        } else {
            $("#empty-message").empty()
            $('#add_farmer').empty()
        }
    },
    select: function (event, ui) {
        $('#search_farmer').val(ui.item.value)
        let farmer_id = ui.item.id
        $('#farmer_profile_table #sex').html(ui.item.sex)
        $('#farmer_profile_table #birthdate').html(ui.item.birthdate)
        $('#farmer_profile_table #address').html(ui.item.address)
        $('#farmer_profile_table #affiliation_name').html(ui.item.affiliation_name)
        $('#farmer_profile_table #affiliation_accreditation').html(ui.item.affiliation_accreditation)
        $('#farmer_id').val(ui.item.id)
        $('#farm_area').val(ui.item.area)

        // Check if rsbsa checking is required
        $.ajax({
            type: 'GET',
            url: 'check_rsbsa_requirement',
            dataType: 'json',
            success: function (source) {
                console.log(source)
                if (source == "required") {
                    // Check if farmer is in the rsbsa list
                    $.ajax({
                        type: 'GET',
                        url: 'check_farmer_rsbsa/' + farmer_id,
                        dataType: 'json',
                        success: function (source) {
                            if (source == "lgu") {
                                $('#add_rsbsa_control_no_modal').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                })
                            }
                        }
                    })
                }
            }
        })

    }
})

// Submit RSBSA Control number
$('#save_rsbsa_control_no').on('click', function () {
    let rsbsa_control_no = $('#add_rsbsa_control_no_modal #rsbsa_control_no').val()
    let farmer_id = $('#farmer_id').val()

    $.ajax({
        type: 'POST',
        url: 'update_farmer_rsbsa',
        data: {
            _token: _token,
            farmer_id: farmer_id,
            rsbsa_control_no: rsbsa_control_no
        },
        dataType: 'json',
        success: function (source) {
            if (source == "success") {
                alert('RSBSA Stub / Control # saved')
                $('#add_rsbsa_control_no_modal').modal('toggle')
            } else if (source == "failed") {
                alert('Error')
            }
        }
    })
})

// Cancel transaction
$('#cancel_transaction').on('click', function () {
    window.location.href = 'releasing'
})

$('#add_farmer_modal #cancel_transaction').on('click', function () {
    window.location.href = 'releasing'
})

$('#submit_release').on('click', function () {
    let farmer_id = $('#farmer_id').val()
    let farm_area = $('#farm_area').val()
    let variety = $('input[name=preferred_variety]:checked').val()
    let new_farm_area = $('#farm_area2').val()

    if (new_farm_area > farm_area) {
        alert('NEW FARM AREA SHOULD NOT BE BIGGER THAN THE OLD FARM AREA.')
    } else {
        if (new_farm_area != '' && new_farm_area > 0) {
            if (farmer_id && new_farm_area && variety) {
                $.ajax({
                    type: 'POST',
                    url: 'releasing/store',
                    data: {
                        _token: _token,
                        farmer_id: farmer_id,
                        farm_area: new_farm_area,
                        variety: variety,
                        new_area: 1
                    },
                    dataType: 'json',
                    success: function (source) {
                        if (source.status == "cannot suffice") {
                            confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety)
                        } else if (source.status == "limit reached") {
                            alert('RSBSA already used or you have exhausted your allocation. Thank you.')
                            location.reload();
                        } else if (source.status == "stocks depleted") {
                            alert('Stocks has been depleted. Try another variety. Thank you.')
                            location.reload();
                        } else {
                            alert('Success. You will receive ' + source.stock_pending_release + ' bags. Thank you!')
                            farm_performance_prompt()
                        }
                    }
                })
            } else {
                alert('Please complete the fields')
            }
        } else {
            if (farmer_id && farm_area && variety) {
                $.ajax({
                    type: 'POST',
                    url: 'releasing/store',
                    data: {
                        _token: _token,
                        farmer_id: farmer_id,
                        farm_area: farm_area,
                        variety: variety,
                        new_area: 0,
                    },
                    dataType: 'json',
                    success: function (source) {
                        if (source.status == "cannot suffice") {
                            confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety)
                        } else if (source.status == "limit reached") {
                            alert('You have exhausted your allocation. Thank you.')
                            location.reload();
                        } else if (source.status == "stocks depleted") {
                            alert('Stocks has been depleted. Try another variety. Thank you.')
                            location.reload();
                        } else {
                            alert('Success. You will receive ' + source.stock_pending_release + ' bags. Thank you!')
                            farm_performance_prompt()
                        }
                    }
                })
            } else {
                alert('Please complete the fields')
            }
        }
    }
})

// Ask farmer if continue getting insufficient bags
function confirm_variety(_token, farmer_id, bags, batch_ticket_no, variety, qr_code, rsbsa_control_no) {
    let confirmation = confirm('Available seeds cannot suffice your allocation. Remaining: (' + bags + ' bags), Continue?')
    if (confirmation == true) {
        $.ajax({
            type: 'POST',
            url: 'releasing/confirm-store',
            data: {
                _token: _token,
                farmer_id: farmer_id,
                bags: bags,
                batch_ticket_no: batch_ticket_no,
                variety: variety
            },
            dataType: 'json',
            success: function (source) {
                if (source.status == "cannot suffice") {
                    confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety, qr_code)
                } else if (source.status == "stocks depleted") {
                    $.ajax({
                        type: 'POST',
                        url: 'checking/deleteFarmer',
                        data: {
                            _token: _token,
                            farmer_id: farmer_id,
                            qr_code: qr_code,
                            rsbsa_control_no: rsbsa_control_no
                        }
                    });
                    alert('Stocks has been depleted. Try another variety. Thank you.')
                } else {
                    alert('Success. You will receive ' + source.stock_pending_release + ' bags. Thank you!')
                    // confirm_address(source.farmer_id)
                    farm_performance_prompt()
                }
            }
        })
    }
}

function farm_performance_prompt() {
    $('#farm_performance_modal').modal({
        backdrop: 'static',
        keyboard: false
    })
}

$('#farm_performance_modal #save_farm_performance').on('click', function () {
    let farmer_id = $('#farmer_id').val()
    let area_planted = $('#area_planted').val()
    let variety_used_prefix = $('#variety_used_prefix').val()
    let variety_used = $('#variety_used').val()
    let seed_usage = $('#seed_usage').val()
    let yield = $('#yield').val()
    let qr_code = $('#qr_code').val()

    // Submit farm performance
    $.ajax({
        type: 'POST',
        url: 'releasing/farm_performance',
        data: {
            _token: _token,
            farmer_id: farmer_id,
            area_planted: area_planted,
            variety_used_prefix: variety_used_prefix,
            variety_used: variety_used,
            seed_usage: seed_usage,
            yield: yield,
            qr_code: qr_code
        },
        dataType: 'json',
        success: function (source) {
            if (source == "success") {
                alert("Added Farm Performance")
                $('#farm_performance_modal').modal('toggle')
                location.reload();
            }
        }
    })
})

// QR Code scanner
let scanner = new Instascan.Scanner({video: document.getElementById('preview')});
let beep = document.getElementById("qr_audio");
scanner.addListener('scan', function (content) {
    console.log(content);
    beep.play()
    $('#farm_performance_modal #qr_code').val(content) // farm performance modal
    $('#farmer_registration_form #distribution_id').val(content) // farmer registration form
    $('#add_farmer_modal #qr_code2').val(content) // add farmer modal
});
Instascan.Camera.getCameras().then(function (cameras) {
    if (cameras.length > 0) {
        scanner.start(cameras[0]);
    } else {
        console.error('No cameras found.');
    }
}).catch(function (e) {
    console.error(e);
});

$('#submit_distribution_location_ws').on('click', function () {
    let province = $('#distribution_province_ws').val()
    let municipality = $('#distribution_municipality_ws').val()
    let dropoff_point = $('#dropoff_point_ws').val()
    let rsbsa_checking = false;

    // Check if RSBSA Checking is checked
    if (document.getElementById('rsbsa_checking').checked) {
        rsbsa_checking = true;
    }
    var prv_ext = dropoff_point.substring(0, 4);
    var prv = dropoff_point.substring(0, 6);
    var prv_id = $("#prv_id").val();
    if (prv_ext == prv_id && (province != '' && municipality != '' && dropoff_point != '')) {
        // Submit
        $.ajax({
            type: 'POST',
            url: 'releasing_ws/select_distribution_location',
            data: {
                _token: _token,
                province: province,
                municipality: municipality,
                prv: prv,
                dropoff_point: dropoff_point,
                rsbsa_checking: rsbsa_checking
            },
            dataType: 'json',
            success: function (source) {
                if (source == "success") {
                    window.location.href = 'releasing_ws'
                }
            }
        })
    } else {
        if (province == '' || municipality == '' || dropoff_point == '') {
            alert("Please select drop off point.");
        } else {
            alert("Your account is not valid for the selected province.");
        }
    }
})
// Select distribution location
$('#submit_distribution_location').on('click', function () {
    let province = $('#distribution_province').val()
    let municipality = $('#distribution_municipality').val()
    let dropoff_point = $('#dropoff_point').val()
    let rsbsa_checking = false;

    // Check if RSBSA Checking is checked
    if (document.getElementById('rsbsa_checking').checked) {
        rsbsa_checking = true;
    }
    var prv_ext = dropoff_point.substring(0, 4);
    var prv = dropoff_point.substring(0, 6);
    var prv_id = $("#prv_id").val();
    if (prv_ext == prv_id && (province != '' && municipality != '' && dropoff_point != '')) {
        // Submit
        $.ajax({
            type: 'POST',
            url: 'releasing/select_distribution_location',
            data: {
                _token: _token,
                province: province,
                municipality: municipality,
                prv: prv,
                dropoff_point: dropoff_point,
                rsbsa_checking: rsbsa_checking
            },
            dataType: 'json',
            success: function (source) {
                if (source == "success") {
                    window.location.href = 'releasing'
                }
            }
        })
    } else {
        if (province == '' || municipality == '' || dropoff_point == '') {
            alert("Please select drop off point.");
        } else {
            alert("Your account is not valid for the selected province.");
        }
    }
})
$('#distribution_province_ws').on('change', function () {
    let province = $('#distribution_province_ws').val()
    $('#distribution_municipality_ws').empty()
    // Get municipalities
    $.ajax({
        type: 'GET',
        url: 'releasing_ws/get_municipalities/' + province,
        dataType: 'json',
        success: function (source) {
            let options = "<option></option>"
            source.forEach(function (item) {
                options += "<option value='" + item['municipality'] + "'>" + item['municipality'] + "</option>"
            })
            $('#distribution_municipality_ws').append(options)
        }
    })
})

// On change municipality dropdown in select distribution location
$('#distribution_municipality_ws').on('change', function () {
    let province = $('#distribution_province_ws').val()
    let municipality = $('#distribution_municipality_ws').val()
    $('#dropoff_point_ws').empty()
    // Get dropoff points
    $.ajax({
        type: 'GET',
        url: 'releasing_ws/get_dropoff_points/' + province + '/' + municipality,
        dataType: 'json',
        success: function (source) {
            let options = "<option></option>"
            source.forEach(function (item) {
                options += "<option value='" + item['prv_dropoff_id'] + "'>" + item['dropOffPoint'] + "</option>"
            })
            $('#dropoff_point_ws').append(options)
        }
    })
})
// On change province dropdown in select distribution location
$('#distribution_province').on('change', function () {
    let province = $('#distribution_province').val()
    $('#distribution_municipality').empty()
    // Get municipalities
    $.ajax({
        type: 'GET',
        url: 'releasing/get_municipalities/' + province,
        dataType: 'json',
        success: function (source) {
            let options = "<option></option>"
            source.forEach(function (item) {
                options += "<option value='" + item['municipality'] + "'>" + item['municipality'] + "</option>"
            })
            $('#distribution_municipality').append(options)
        }
    })
})

// On change municipality dropdown in select distribution location
$('#distribution_municipality').on('change', function () {
    let province = $('#distribution_province').val()
    let municipality = $('#distribution_municipality').val()
    $('#dropoff_point').empty()
    // Get dropoff points
    $.ajax({
        type: 'GET',
        url: 'releasing/get_dropoff_points/' + province + '/' + municipality,
        dataType: 'json',
        success: function (source) {
            let options = "<option></option>"
            source.forEach(function (item) {
                options += "<option value='" + item['prv_dropoff_id'] + "'>" + item['dropOffPoint'] + "</option>"
            })
            $('#dropoff_point').append(options)
        }
    })
})

// On click add farmer button in releasing page
function add_farmer() {
    $('#add_farmer_modal').modal({
        backdrop: 'static',
        keyboard: false
    })
}
$('#save_farmer_ws').on('click', function () {
    let rsbsa_control_no = $('#add_farmer_modal #rsbsa_control_no').val()
    let phone = $('#add_farmer_modal #phone').val()
    let actual_area = $('#add_farmer_modal #actual_area').val()
    let variety = $('#add_farmer_modal input[name=preferred_variety2]:checked').val()
    let qr_code = $('#add_farmer_modal #qr_code2').val()
    let sex = $('#add_farmer_modal #farmer_gender').val()
    let firstname = $('#add_farmer_modal #firstname').val()
    let middlename = $('#add_farmer_modal #middlename').val()
    let lastname = $('#add_farmer_modal #lastname').val()
    let extname = $('#add_farmer_modal #extname').val()
    let birthdate = $('#add_farmer_modal #birthdate').val()
    let mfname = $('#add_farmer_modal #mfname').val()
    let mmname = $('#add_farmer_modal #mmname').val()
    let mlname = $('#add_farmer_modal #mlname').val()
    let mextname = $('#add_farmer_modal #mextname').val()
    let valid_id = $('#add_farmer_modal #valid_id').val()
    let relationship = $('#add_farmer_modal #relationship').val()
    let variety_used = $('#add_farmer_modal #variety_used').val()
    let seed_usage = $('#add_farmer_modal #seed_usage').val()
    let preferred_variety = $('#add_farmer_modal #preferred_variety').val()
    let area_planted = $('#add_farmer_modal #area_planted').val()
    let yields = $('#add_farmer_modal #yields').val()
    let bags_distributed = $('#add_farmer_modal #bags_distributed').val()
    let save = false;
    var is_representative = 0;
	var is_changed = 0;
    // Validation of form fields
    // On list
//    if (document.getElementById('on_list').checked == true) {
    if ($('#add_farmer_modal #with_rep').is(":checked") == true) {
        var is_representative = 1;
    } else {
        valid_id = '';
        relationship = '';
    }

	if ($('#add_farmer_modal #change_bag').is(":checked") == true) {
        var is_changed = 1;
    } else {
        var is_changed = 0;
    }
	
    if (rsbsa_control_no == "" || actual_area == "" || qr_code == "" ||
            variety == undefined || sex == 0 || firstname == "" || lastname == "" ||
            birthdate == "" || mfname == "" || mlname == "") {
        alert('Please input required fields')
        $('#save_farmer_ws').prop('disabled', false);
        save = false
        HoldOn.close()
    } else {
        // QR code length validation
        if (qr_code.length != 9) {
            alert('Invalid length of QR Code. Must be 9 characters')
            $('#save_farmer_ws').prop('disabled', false);
            save = false
            HoldOn.close()
        } else {
            save = true
        }
    }
    // Not on list
//    } else {
//        if (rsbsa_control_no == "" || actual_area == "") {
//            alert('Please input required fields')
//            $('#save_farmer').prop('disabled', false);
//            save = false;
//            HoldOn.close()
//        } else {
//            save = true;
//        }
//    }

    // If validated fields is correct then save to database
    if (save == true) {
        $('#save_farmer_ws').prop('disabled', true);
        HoldOn.open(holdon_options)
        // Save farmer details
        $.ajax({
            type: 'POST',
            url: 'add_farmer_rsbsa',
            data: {
                _token: _token,
                rsbsa_control_no: rsbsa_control_no,
                qr_code: qr_code,
                sex: sex,
                phone: phone,
                firstName: firstname,
                midName: middlename,
                lastName: lastname,
                actual_area: actual_area,
                extName: extname,
                birthdate: birthdate,
                mfname: mfname,
                mmname: mmname,
                bags_distributed: bags_distributed,
                is_changed: is_changed,
                mlname: mlname,
                mextname: mextname,
                valid_id: valid_id,
                relationship: relationship,
                is_representative: is_representative,
                variety_used: variety_used,
                seed_usage: seed_usage,
                preferred_variety: preferred_variety,
                area_planted: area_planted,
                yields: yields
            },
            dataType: 'json',
            success: function (source) {
                if (source == "rsbsa already inputted") {
                    alert("RSBSA Stub/Control # was already inputted in the database");
                    $('#save_farmer_ws').prop('disabled', false);
                    save = false;
                    HoldOn.close()
                } else if (source == "limit reach") {
                    alert("RSBSA already used or allocated bags already claimed!");
                    $('#save_farmer_ws').prop('disabled', false);
                    save = false;
                    HoldOn.close()
                }else if (source == "qr code already inputted") {
                    alert("QR code was already inputted in the database");
                    $('#save_farmer_ws').prop('disabled', false);
                    save = false;
                    HoldOn.close()
                }
                if (source['result'] == "success") {
//                    if (document.getElementById('on_list').checked == true) {
                    // Releasing
                    let farmer_id = source['farmerID']
                    $('#farmer_id').val(farmer_id)
                    if (farmer_id && variety) {
                        $.ajax({
                            type: 'POST',
                            url: 'releasing_ws/store',
                            data: {
                                _token: _token,
                                farmer_id: farmer_id,
                                qr_code: qr_code,
                                farm_area: actual_area,
                                variety: variety,
                                bags_distributed: bags_distributed,
								is_changed: is_changed,
                                rsbsa_control_no: rsbsa_control_no
                            },
                            dataType: 'json',
                            success: function (source) {
                                // if (source.status == "cannot suffice") {
                                //     confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety)
                                // }
                                if (source.status == "cannot suffice") {
                                    confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety, qr_code, rsbsa_control_no)

                                    $.ajax({
                                        type: 'POST',
                                        url: 'checking/deleteFarmer',
                                        data: {
                                            _token: _token,
                                            farmer_id: farmer_id,
                                            qr_code: qr_code,
                                            rsbsa_control_no: rsbsa_control_no
                                        }
                                    });
                                    HoldOn.close()
                                    $('#save_farmer_ws').prop('disabled', false);
                                } else if (source.status == "limit reached") {
                                    alert('You have exhausted your allocation. Thank you.')
                                    $.ajax({
                                        type: 'POST',
                                        url: 'checking/deleteFarmer',
                                        data: {
                                            _token: _token,
                                            farmer_id: farmer_id,
                                            qr_code: qr_code,
                                            rsbsa_control_no: rsbsa_control_no
                                        }
                                    });
                                    HoldOn.close()
                                    $('#save_farmer_ws').prop('disabled', false);
                                } else if (source.status == "stocks depleted") {
                                    alert('Stocks has been depleted. Try another variety. Thank you.')
                                    $.ajax({
                                        type: 'POST',
                                        url: 'checking/deleteFarmer',
                                        data: {
                                            _token: _token,
                                            farmer_id: farmer_id,
                                            qr_code: qr_code,
                                            rsbsa_control_no: rsbsa_control_no
                                        }
                                    });
                                    HoldOn.close()
                                    $('#save_farmer_ws').prop('disabled', false);
                                } else {
                                    alert('Success. You will receive ' + source.stock_pending_release + ' bags. Thank you!')
                                    $('#add_farmer_modal').modal('toggle')
                                    location.reload();
                                }

                                $('#save_farmer_ws').prop('disabled', false);
                            }
                        })
                    } else {
                        alert('Please complete the fields')
                        HoldOn.close()
                    }
//                    } else {
//                        alert('You are now on the waiting list. Thank you!')
//                        location.reload();
//                    }
                } else if (source['result'] == "failed") {
                    alert('Error')
                    $('#save_farmer_ws').prop('disabled', false);
                    HoldOn.close()
                }
            }
        })
    }
})
// Submit add farmer form in add farmer modal
$('#save_farmer').on('click', function () {
    let rsbsa_control_no = $('#add_farmer_modal #rsbsa_control_no').val()
    let phone = $('#add_farmer_modal #phone').val()
    let actual_area = $('#add_farmer_modal #actual_area').val()
    let variety = $('#add_farmer_modal input[name=preferred_variety2]:checked').val()
    let qr_code = $('#add_farmer_modal #qr_code2').val()
    let sex = $('#add_farmer_modal #farmer_gender').val()
    let firstname = $('#add_farmer_modal #firstname').val()
    let middlename = $('#add_farmer_modal #middlename').val()
    let lastname = $('#add_farmer_modal #lastname').val()
    let extname = $('#add_farmer_modal #extname').val()
    let birthdate = $('#add_farmer_modal #birthdate').val()
    let mfname = $('#add_farmer_modal #mfname').val()
    let mmname = $('#add_farmer_modal #mmname').val()
    let mlname = $('#add_farmer_modal #mlname').val()
    let mextname = $('#add_farmer_modal #mextname').val()
    let valid_id = $('#add_farmer_modal #valid_id').val()
    let relationship = $('#add_farmer_modal #relationship').val()
    let variety_used = $('#add_farmer_modal #variety_used').val()
    let seed_usage = $('#add_farmer_modal #seed_usage').val()
    let preferred_variety = $('#add_farmer_modal #preferred_variety').val()
    let bags_distributed = $('#add_farmer_modal #bags_distributed').val()
    let area_planted = $('#add_farmer_modal #area_planted').val()
    let yields = $('#add_farmer_modal #yields').val()
    let save = false;
    var is_representative = 0;
    var is_changed = 0;
    // Validation of form fields
    // On list
//    if (document.getElementById('on_list').checked == true) {
    if ($('#add_farmer_modal #with_rep').is(":checked") == true) {
        var is_representative = 1;
    } else {
        valid_id = '';
        relationship = '';
    }

	if ($('#add_farmer_modal #change_bag').is(":checked") == true) {
        var is_changed = 1;
    } else {
        var is_changed = 0;
    }

    if (rsbsa_control_no == "" || actual_area == "" || qr_code == "" ||
            variety == undefined || sex == 0 || firstname == "" || lastname == "" ||
            birthdate == "" || mfname == "" || mlname == "") {
        alert('Please input required fields')
        $('#save_farmer').prop('disabled', false);
        save = false
        HoldOn.close()
    } else {
        // QR code length validation
        if (qr_code.length != 9) {
            alert('Invalid length of QR Code. Must be 9 characters')
            $('#save_farmer').prop('disabled', false);
            save = false
            HoldOn.close()
        } else {
            save = true
        }
    }
    // Not on list
//    } else {
//        if (rsbsa_control_no == "" || actual_area == "") {
//            alert('Please input required fields')
//            $('#save_farmer').prop('disabled', false);
//            save = false;
//            HoldOn.close()
//        } else {
//            save = true;
//        }
//    }

    // If validated fields is correct then save to database
    if (save == true) {
        $('#save_farmer').prop('disabled', true);
        HoldOn.open(holdon_options)
        // Save farmer details
        $.ajax({
            type: 'POST',
            url: 'add_farmer_rsbsa',
            data: {
                _token: _token,
                rsbsa_control_no: rsbsa_control_no,
                qr_code: qr_code,
                sex: sex,
                phone: phone,
                firstName: firstname,
                midName: middlename,
                lastName: lastname,
                actual_area: actual_area,
                extName: extname,
                birthdate: birthdate,
                mfname: mfname,
                mmname: mmname,
                mlname: mlname,
                mextname: mextname,
                valid_id: valid_id,
                bags_distributed: bags_distributed,
                is_changed: is_changed,
                relationship: relationship,
                is_representative: is_representative,
                variety_used: variety_used,
                seed_usage: seed_usage,
                preferred_variety: preferred_variety,
                area_planted: area_planted,
                yields: yields
            },
            dataType: 'json',
            success: function (source) {
                if (source == "rsbsa already inputted") {
                    alert("RSBSA Stub/Control # was already inputted in the database");
                    $('#save_farmer').prop('disabled', false);
                    save = false;
                    HoldOn.close()
                } else if (source == "limit reach") {
                    alert("RSBSA already used or allocated bags already claimed!");
                    $('#save_farmer').prop('disabled', false);
                    save = false;
                    HoldOn.close()
                }else if (source == "qr code already inputted") {
                    alert("QR code was already inputted in the database");
                    $('#save_farmer').prop('disabled', false);
                    save = false;
                    HoldOn.close()
                }
                if (source['result'] == "success") {
//                    if (document.getElementById('on_list').checked == true) {
                    // Releasing
                    let farmer_id = source['farmerID']
                    $('#farmer_id').val(farmer_id)
                    if (farmer_id && variety) {
                        $.ajax({
                            type: 'POST',
                            url: 'releasing/store',
                            data: {
                                _token: _token,
                                farmer_id: farmer_id,
                                qr_code: qr_code,
                                farm_area: actual_area,
                                variety: variety,
								bags_distributed: bags_distributed,
								is_changed: is_changed,
                                rsbsa_control_no: rsbsa_control_no
                            },
                            dataType: 'json',
                            success: function (source) {
                                // if (source.status == "cannot suffice") {
                                //     confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety)
                                // }
                                if (source.status == "cannot suffice") {
                                    confirm_variety(_token, farmer_id, source.confirm_bags, source.batch_ticket_no, source.variety, qr_code, rsbsa_control_no)

                                    $.ajax({
                                        type: 'POST',
                                        url: 'checking/deleteFarmer',
                                        data: {
                                            _token: _token,
                                            farmer_id: farmer_id,
                                            qr_code: qr_code,
                                            rsbsa_control_no: rsbsa_control_no
                                        }
                                    });
                                    HoldOn.close()
                                    $('#save_farmer').prop('disabled', false);
                                } else if (source.status == "limit reached") {
                                    alert('You have exhausted your allocation. Thank you.')
                                    $.ajax({
                                        type: 'POST',
                                        url: 'checking/deleteFarmer',
                                        data: {
                                            _token: _token,
                                            farmer_id: farmer_id,
                                            qr_code: qr_code,
                                            rsbsa_control_no: rsbsa_control_no
                                        }
                                    });
                                    HoldOn.close()
                                    $('#save_farmer').prop('disabled', false);
                                } else if (source.status == "stocks depleted") {
                                    alert('Stocks has been depleted. Try another variety. Thank you.')
                                    $.ajax({
                                        type: 'POST',
                                        url: 'checking/deleteFarmer',
                                        data: {
                                            _token: _token,
                                            farmer_id: farmer_id,
                                            qr_code: qr_code,
                                            rsbsa_control_no: rsbsa_control_no
                                        }
                                    });
                                    HoldOn.close()
                                    $('#save_farmer').prop('disabled', false);
                                } else {
                                    alert('Success. You will receive ' + source.stock_pending_release + ' bags. Thank you!')
                                    $('#add_farmer_modal').modal('toggle')
                                    location.reload();
                                }

                                $('#save_farmer').prop('disabled', false);
                            }
                        })
                    } else {
                        alert('Please complete the fields')
                        HoldOn.close()
                    }
//                    } else {
//                        alert('You are now on the waiting list. Thank you!')
//                        location.reload();
//                    }
                } else if (source['result'] == "failed") {
                    alert('Error')
                    $('#save_farmer').prop('disabled', false);
                    HoldOn.close()
                }
            }
        })
    }
})

// Remove disabled attribute of submit button when QR code is not empty in farm performance form
$('#farm_performance_modal #qr_code').on('change', function () {
    // P630369000000001
    if ($('#farm_performance_modal #qr_code').val().length >= 16) {
        $('#farm_performance_modal #save_farm_performance').css('display', 'block').css('float', 'right')
    } else {
        $('#farm_performance_modal #save_farm_performance').css('display', 'none')
    }
})

// Show new farm area input
$('#update_area').on('click', function () {
    $('#farm_area2_input').css('display', 'block');
})

// Show select variety input
$('#on_list').on('click', function () {
    if (document.getElementById('on_list').checked == true) {
        $('#select_variety_input').css('display', 'block')
    } else {
        $('#select_variety_input').css('display', 'none')
    }
})

// Ask farmer if farm address is still the same
// REPLACED BY FARM PERFORMANCE FORM
/*function confirm_address(farmer_id) {
 $.ajax({
 url: 'releasing/search_address/'+farmer_id,
 dataType: 'json',
 success: function(source) {
 console.log(source);
 let provinces_options = ""
 let municipalities_options = ""
 $.each(source.provinces, function(i, val) {
 if (val.provDesc == source.area.province) {
 provinces_options += "<option val='"+val.provDesc+"' selected>"+val.provDesc+"</option>"
 } else {
 provinces_options += "<option val='"+val.provDesc+"'>"+val.provDesc+"</option>"
 }
 })
 
 $.each(source.municipalities, function(i, val) {
 if (val.citymunDesc == source.area.municipality) {
 municipalities_options += "<option val='"+val.citymunDesc+"' selected>"+val.citymunDesc+"</option>"
 } else {
 municipalities_options += "<option val='"+val.citymunDesc+"'>"+val.citymunDesc+"</option>"
 }
 })
 
 $('#farm_address_confirmation_modal #confirm_region').val(source.area.region)
 $('#farm_address_confirmation_modal #confirm_province').append(provinces_options)
 $('#farm_address_confirmation_modal #confirm_municipality').append(municipalities_options)
 $('#farm_address_confirmation_modal #confirm_barangay').val(source.area.barangay)
 $('#farm_address_confirmation_modal #confirm_area').val(source.area.area)
 $('#farm_address_confirmation_modal').modal('toggle')
 }
 })
 }
 
 $('#farm_address_confirmation_modal #confirm_province').on('change', function() {
 let province = $(this).val();
 
 $.ajax({
 url: 'releasing/search_municipalities/'+province,
 dataType: 'json',
 success: function(source) {
 $('#farm_address_confirmation_modal #confirm_municipality').empty()
 let municipalities_options = "<option></option>"
 $.each(source.municipalities, function(i, val) {
 municipalities_options += "<option val='"+val.citymunDesc+"'>"+val.citymunDesc+"</option>"
 })
 $('#farm_address_confirmation_modal #confirm_municipality').append(municipalities_options)
 $('#farm_address_confirmation_modal #confirm_region').val(source.region)
 }
 })
 })
 
 $('#farm_address_confirmation_modal #save_new_address').on('click', function() {
 let farmer_id = $('#farmer_id').val()
 let region = $('#farm_address_confirmation_modal #confirm_region').val()
 let province = $('#farm_address_confirmation_modal #confirm_province').val()
 let municipality = $('#farm_address_confirmation_modal #confirm_municipality').val()
 let barangay = $('#farm_address_confirmation_modal #confirm_barangay').val()
 let area = $('#farm_address_confirmation_modal #confirm_area').val()
 
 // Submit new farm address
 
 $.ajax({
 type: 'POST',
 url: 'releasing/insert-address',
 data: {
 _token: _token,
 farmer_id: farmer_id,
 region: region,
 province: province,
 municipality: municipality,
 barangay: barangay,
 area: area
 },
 dataType: 'json',
 success: function(source) {
 if (source == "success") {
 alert("Updated farm address")
 $('#farm_address_confirmation_modal').modal('toggle')
 }
 }
 })
 }) */
