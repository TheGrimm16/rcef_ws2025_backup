@extends('layouts.index')

@section('content')
<style>
    /* .heads{
        display: flex; 
        flex-wrap: wrap; 
        gap: 1em;
        margin-bottom: 1em;
        position: relative;
    } */

    .no-close .ui-dialog-titlebar-close {
        display: none;
    }

    .heads-small{
        width: 100%;
        display: grid;
        grid-template-columns: 
            repeat(auto-fit, minmax(175px, 1fr));
        gap: 1em;
        margin-bottom: 2em;
    }
    
    .heads, #varieties-container{
        width: 100%;
        display: grid;
        grid-template-columns: 
            repeat(auto-fit, minmax(250px, 1fr));
        gap: 1em;
        margin-bottom: 2em;
    }

    main{
        font-family: system-ui, sans-serif;
        position: relative;
        width: 100%;
    }

    .wrapper{
        max-width: 960px;
        margin: auto;
    }
    
    .flex-wrap{
        display: flex;
        flex-wrap: wrap;
        gap: 1em;
    }

    .fw-700{
        font-weight: 700;
    }

    .fw-900{
        font-weight: 900;
    }

    ._card{
        /* box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); */
        padding: 2em 1em;
        border-radius: 1em;
        position: relative;
    }
    
    .individual-selector{
        position: relative;
        padding: 2em 1em;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        border-radius: 1em;
    }

    .indiv{
        display: flex;
        opacity: 0;
        gap: 1em;
        background-color: rgba(5, 186, 5, 0.52);
        padding: 0.8rem;
        border-radius: 2em;
        color: black;
        /* animation: slideIn 0.2s ease-in-out forwards; */
    }

    .indiv .nai{
        background-color: rgba(1, 162, 1, 0.469);
        font-size: 2em;
        font-weight: 900;
        padding: 0.8rem;
        border-radius: 0.8em;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .if-verified{
        display: none;
    }

    .na{
        background-color: rgba(153, 173, 153, 0.52);
    }

    .na .nai{
        background-color: rgba(153, 173, 153, 0.344);
    }

    .indiv .fin{
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 0.4rem;
    }

    .indiv .fin .title{
        font-weight: 700;
        font-size: 1.2em;
    }

    @keyframes slideIn{
        0%{
            opacity: 0;
            transform: translateX(-5rem);
        }
        100%{
            opacity: 1;
            transform: translateX(0);
        }
    }

    .is-second-form-passed{
        display: none;
    }
</style>
<body>
    <main>
        <div class="wrapper">
            <header>
                <div class="title" style="line-height: 80%; padding: 2em 1em;">
                    <span style="font-size: 3em;" class="fw-700">Online Encoding</span>
                    <br>
                    <span style="font-size: 1em; padding-left: 1em;" class="fw-700">for <span style="color: green">new</span> farmers...</span>
                </div>
            </header>
            <div class="heads">
                <div class="part1 _card">
                    <form>
                        <h3 class="fw-700">0. Verification</h3>
                        <div class="heads">
                            <div class="form-group">
                                <label for="rsbsa-ctrl-no">*FFRS RSBSA No.</label>
                                <input type="text" class="form-control" id="rsbsa-ctrl-no" aria-describedby="rsbsa-ctrl-no" placeholder="XX-XX-XX-XXX-XXXXXX">
                            </div>
                            <div class="form-group">
                                <label for="rcef-id">RCEF ID</label>
                                <input readonly type="text" class="form-control" id="rcef-id" aria-describedby="rcef-id" value="" placeholder="(Automatically generated)">
                                <small class="form-text text-muted">Auto-generated. No need to touch.</small>
                            </div>
                            <div class="form-group">
                                <label for="db-ref">Database Reference Number</label>
                                <input readonly type="text" class="form-control" id="db-ref" aria-describedby="db-ref" value="" placeholder="(Automatically generated)">
                                <small class="form-text text-muted">Auto-generated. No need to touch.</small>
                            </div>
                        </div>
                        <h3 class="fw-700">I. Personal Information</h3>
                        <small class="form-text text-warning"><i>Make sure you populate all parts of the name, sex and birthdate for more thorough verification.</i></small>
                        <div class="heads">
                            <div class="form-group">
                                <label for="firstName">*First Name</label>
                                <input type="text" class="form-control" id="firstName" aria-describedby="firstName" placeholder="Juan">
                                <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                            </div>
                            <div class="form-group">
                                <label for="midName">Middle Name</label>
                                <input type="text" class="form-control" id="midName" aria-describedby="midName" placeholder="Dulce">
                            </div>
                            <div class="form-group">
                                <label for="lastName">*Last Name</label>
                                <input type="text" class="form-control" id="lastName" aria-describedby="lastName" placeholder="Dela Cruz">
                            </div>
                            <div class="form-group">
                                <label for="extName">Name Extension</label>
                                <input type="text" class="form-control" id="extName" aria-describedby="extName" placeholder="Jr/Sr/I/II">
                            </div>
                            <div class="form-group">
                                <label for="sex">*Sex</label>
                                <select style="min-width: 150px; max-width: 100%;" name="sex" id="sex" class="form-control form-select">
                                    <option value="FEMALE">Female</option>
                                    <option value="MALE">Male</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="birthdate">*Birthday</label>
                                <input type="date" class="form-control" id="birthdate" aria-describedby="birthdate" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="heads">
                            <button id="btn-verify" class="btn btn-primary">Verify <i class="fa fa-check-circle-o" aria-hidden="true"></i></button>
                        </div>
                        <div class="if-verified">
                            <!-- <h3 class="fw-700">II. Hometown</h3>
                            <div class="heads">
                                <div class="form-group">
                                    <label for="address_prv">*Province</label>
                                    <select style="min-width: 150px; max-width: 100%;" name="address_prv" id="address_prv" class="form-control form-select"></select>
                                </div>
                                <div class="form-group">
                                    <label for="address_mun">*Municipality</label>
                                    <select style="min-width: 150px; max-width: 100%;" name="address_mun" id="address_mun" class="form-control form-select"></select>
                                </div>
                                <div class="form-group">
                                    <label for="address_bgy">*Barangay</label>
                                    <select style="min-width: 150px; max-width: 100%;" name="address_bgy" id="address_bgy" class="form-control form-select"></select>
                                </div>
                            </div> -->
                            <h3 class="fw-700">II-III. Home & Farm Information</h3>
                            <div class="heads">
                                <div class="form-group">
                                    <label for="address_prv">*Province</label>
                                    <select style="min-width: 150px; max-width: 100%;" name="address_prv" id="address_prv" class="form-control form-select"></select>
                                </div>
                                <div class="form-group">
                                    <label for="farm_mun">*Municipality</label>
                                    <select style="min-width: 150px; max-width: 100%;" name="farm_mun" id="farm_mun" class="form-control form-select"></select>
                                </div>
                                <div class="form-group">
                                    <label for="farm_bgy">*Barangay</label>
                                    <select style="min-width: 150px; max-width: 100%;" name="farm_bgy" id="farm_bgy" class="form-control form-select"></select>
                                </div>
                                <div class="form-group">
                                    <label for="farm_area">*Farm Area (in ha)</label>
                                    <input type="number" class="form-control" id="farm_area" aria-describedby="farm_area" placeholder="X.XX ha">
                                </div>
                            </div>
                            <h3 class="fw-700">IV. Personal Background</h3>
                            <div class="heads">
                                <div class="form-group">
                                    <label for="mother_name">Mother's Full Name</label>
                                    <input type="text" class="form-control" id="mother_name" aria-describedby="mother_name" placeholder="Leave blank if unknown">
                                </div>
                            </div>
                            <h2 class="fw-700" style="margin-left: 1rem;">IV-B. Check if any of the following applies to the farmer at hand.</h2>
                            <div class="flex-wrap" style="margin-left: 1.4rem;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isIp">
                                    <label class="form-check-label" for="isIp">Indigenous People</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isPwd">
                                    <label class="form-check-label" for="isPwd">Person with Disability</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isArb">
                                    <label class="form-check-label" for="isArb">Agrarian Reform Beneficiary</label>
                                </div>
                            </div>
                            <br>
                            <div class="heads">
                                <button id="btn-finalize-profile" class="btn btn-success">Finalize Profile <i class="fa fa-check-circle-o" aria-hidden="true"></i></button>
                            </div>
                            <div class="is-second-form-passed">
                                <h3 class="fw-700">V. Release Info</h3>
                                <div class="heads">
                                    <!-- <div class="province-select individual-selector">
                                        <label for="province_select">Province</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="province_select" id="province_select" class="form-control form-select">
                                        </select>
                                    </div>
                                    <div class="municipality-select individual-selector">
                                        <label for="municipality_select">Municipality</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="municipality_select" id="municipality_select" class="form-control form-select">
                                        </select>
                                    </div> -->
                                    <div class="form-check">
                                        <label for="dropoff_select">Dropoff Point</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="dropoff_select" id="dropoff_select" class="form-control form-select"></select>
                                        <small class="form-text text-muted">Based on farm address</small>
                                    </div>
                                </div>
                                <h4 class="fw-700">Available Varieties</h4>
                                <div class="heads">
                                    <div id="varieties-container">
                                    </div>
                                </div>
                                <div class="heads">
                                    <div class="form-check">
                                        <label for="seed_variety">Seed Variety</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="seed_variety" id="seed_variety" class="form-control form-select"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="claimed_area">Area Claimed</label>
                                        <input type="number" class="form-control" id="claimed_area" aria-describedby="claimed_area" placeholder="0.46" readonly>
                                        <small class="form-text text-muted">Based on farm area</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="claimed_bags">Bags Claimed</label>
                                        <input type="number" class="form-control" id="claimed_bags" aria-describedby="claimed_bags" placeholder="1" readonly>
                                        <small class="form-text text-muted">Automatically computed</small>
                                    </div>
                                </div>
                                <h4 class="fw-700">Yield Details</h4>
                                <div class="heads-small">
                                    <div class="form-group">
                                        <label for="area_harvested">*Area Harvested</label>
                                        <input type="number" class="form-control" id="area_harvested" aria-describedby="area_harvested" placeholder="X.X ha">
                                    </div>
                                    <div class="form-group">
                                        <label for="no_of_bags">*No. Of Bags</label>
                                        <input type="number" class="form-control" id="no_of_bags" aria-describedby="no_of_bags" placeholder="XX bags">
                                    </div>
                                    <div class="form-group">
                                        <label for="wt_per_bag">*Weight per bag</label>
                                        <input type="number" class="form-control" id="wt_per_bag" aria-describedby="wt_per_bag" placeholder="XX kgs">
                                    </div>
                                    <div class="form-group">
                                        <label for="yield">Yield</label>
                                        <input type="number" class="form-control" id="yield" aria-describedby="yield" placeholder="T/ha" readonly>
                                        <small class="form-text text-muted">Automatically computed</small>
                                    </div>
                                </div>
                                <div class="heads">
                                    <div class="form-group">
                                        <label for="variety_planted">*Variety Planted</label>
                                        <!-- <input type="text" class="form-control" id="variety_planted" aria-describedby="variety_planted" placeholder="NSIC Rc 222"> -->
                                        <select style="min-width: 150px; max-width: 100%;" name="variety_planted" id="variety_planted" class="form-control form-select">
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="seed_type">*Seed Type</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="seed_type" id="seed_type" class="form-control form-select">
                                            <option value="Inbred">Inbred</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="seed_class">*Seed Class</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="seed_class" id="seed_class" class="form-control form-select">
                                            <option value="Certified">Certified</option>
                                            <option value="Good">Good</option>
                                        </select>
                                    </div>
                                </div>
                                <h4 class="fw-700">Current Season</h4>
                                <div class="heads">
                                    <div class="form-group">
                                        <label for="crop_estab">*Crop Establishment</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="crop_estab" id="crop_estab" class="form-control form-select">
                                            <option value="Direct">Direct</option>
                                            <option value="Transplanted">Transplanted</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ecosystem_cs">*Ecosystem</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="ecosystem_cs" id="ecosystem_cs" class="form-control form-select">
                                            <option value="rain-upl">Rainfed (Upland)</option>
                                            <option value="rain-low">Rainfed (Lowland)</option>
                                            <option value="irri-nia">Irrigated (NIS/NIA)</option>
                                            <option value="irri-cis">Irrigated (CIS/Communal)</option>
                                            <option value="irri-stw">Irrigated (Shallow Tube Well)</option>
                                            <option value="irri-swp">Irrigated (SWIP)</option>
                                            <option value="irri-rsp">Irrigated (River/Steam pump)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="planting_month">*Planting Month</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="planting_month" id="planting_month" class="form-control form-select">
                                            <option value="01">January</option>
                                            <option value="02">February</option>
                                            <option value="03">March</option>
                                            <option value="04">April</option>
                                            <option value="05">May</option>
                                            <option value="06">June</option>
                                            <option value="07">July</option>
                                            <option value="08">August</option>
                                            <option value="09">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="planting_week">*Planting Week</label>
                                        <select style="min-width: 150px; max-width: 100%;" name="planting_week" id="planting_week" class="form-control form-select">
                                            <option value="01">1st week</option>
                                            <option value="02">2nd week</option>
                                            <option value="03">3rd week</option>
                                            <option value="04">4th week</option>
                                        </select>
                                    </div>
                                </div>
                                <h4 class="fw-700">Other Distribution Details</h4>
                                <div class="flex-wrap">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rcvdKp">
                                        <label class="form-check-label" for="rcvdKp">Received KP Kit?</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rcvdFert">
                                        <label class="form-check-label" for="rcvdFert">Fertilizer?</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rcvdCash">
                                        <label class="form-check-label" for="rcvdCash">Cash Incentives?</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rcvdCredit">
                                        <label class="form-check-label" for="rcvdCredit">Credit/Loan?</label>
                                    </div>
                                </div>
                                <div class="heads">
                                    <button id="btn-save-dist" class="btn btn-success">Save Distribution</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>

@endsection

@push('scripts')
<script>
    $("#province_select").select2({
        placeholder: "Select a province",
        allowClear: true
    });
    $("#municipality_select").select2({
        placeholder: "Select a municipality",
        allowClear: true
    });
    $("#dropoff_select").select2({
        placeholder: "Select a dropoff",
        allowClear: true
    });

    globalInfo = {
        "is_new": 4,
        "claiming_prv": "", 
        "claiming_brgy": "", 
        "no_of_parcels": 1,
        "rsbsa_control_no": "",
        "rcef_id": "",
        "db_ref": "",
        "assigned_rsbsa": "",
        "firstName": "",
        "midName": "",
        "lastName": "",
        "extName": "",
        "fullName": "",
        "sex": "",
        "province": "",
        "municipality": "",
        "brgy_name": "",
        "birthdate": "",
        "mother_name": "",
        "tel_no": "",
        "geo_code": "",
        "fca_name": "None",
        "is_pwd": 0,
        "is_arb": "NO",
        "is_ip": 0,
        "tribe_name": "None",
        "ben_4ps": "NO",
        "data_source": "RSMS",
        "sync_date": null,
        "final_area": 0,
        "final_claimable": 0,
        "is_claimed": 1,
        "total_claimed": 0,
        "total_claimed_area": 0,
        "is_replacement": 0,
        "replacement_area": 0,
        "replacement_bags": 0,
        "replacement_bags_claimed": 0,
        "replacement_area_claimed": 0,
        "is_ebinhi": 0,
        "print_count": 0,
        "to_prv_code": ""
    };

    globalRealeaseInfo = {
        "new_released_id": null,
        "id": 0,
        "rcef_id": "",
        "db_ref": "",
        "prv_dropoff_id": "",
        "province": "",
        "municipality": "",
        "dropOffPoint": "",
        "transaction_code": "",
        "dataSharing": 1,
        "is_representative": 0,
        "rep_name": "",
        "rep_id": "",
        "rep_relation": "",
        "claimed_area": 0,
        "bags_claimed": 0,
        "seed_variety": "",
        "remarks": null,
        "recipient_ls": "No",
        "planted_rcvd_seeds_ls": "",
        "reason_not_planted_rcvd_seeds_ls": "",
        "yield_area_harvested_ls": 0,
        "yield_no_of_bags_ls": 0,
        "yield_wt_per_bag": 0,
        "crop_establishment_cs": "",
        "seedling_age": 0,
        "ecosystem_cs": "",
        "ecosystem_source_cs": "",
        "planting_week": "",
        "has_kp_kit": "",
        "kp_kit_count": 0,
        "other_benefits_received": "",
        "date_released": "",
        "released_by": "",
        "time_start": "-",
        "time_end": "-",
        "app_version": "new_farmer_encoding1",
        "distribution_type": "Regular",
        "mode": "online_encoding_new",
        "with_fertilizer_voucher": null,
        "farmer_id_address": 0,
        "content_rsbsa": "",
        "new_rcef_id": "",
        "server_date_received": null,
        "category": "INBRED",
        "birthdate": "",
        "final_area": 0,
        "line_designation": "",
        "yield_last_season_details": "",
        "sex": "",
        "lot_series_claims": "",
        "list_version": "1",
        "status": "1",
        "process_report_status": "not process",
        "membership": "",
        "membership_status": "",
        "is_replacement": "0",
        "low_land_members": null
    };

    existing = {
        "icts": false,
        "curr": false
    };

    passedVerifications = false;
    availableVarieties = [];

    getInitialProvinces();

    $("#rsbsa-ctrl-no").on('input', (event) => {
        let currVal = $("#rsbsa-ctrl-no").val();
        
        // Define the positions where you want to insert hyphens
        const positions = [2, 5, 8, 12];

        // Loop through positions and add hyphens
        for (const pos of positions) {
            if (currVal.length > pos && currVal.charAt(pos) !== '-') {
                currVal = currVal.slice(0, pos) + '-' + currVal.slice(pos);
                $("#rsbsa-ctrl-no").val(currVal);
            }
        }
    });


    function getInitialProvinces(){
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getProvinces')}}",
            success: function(result){
                if(result){
                    result.forEach((element, idx) => {
                        $("#province_select").append(`<option value="${element.prv_code}">${element.province}</option>`);
                    });
                    $("#province_select").val(null);
                    $("#municipality_select").val(null);
                    $("#dropoff_select").val(null);
                }
            }
        });
    }

    $("#province_select").on('change', () => {
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getMunicipalities')}}",
            data: {prv: `${$("#province_select").val()}`},
            success: function(result){
                $("#municipality_select").empty();
                $("#dropoff_select").empty();
                if(result){
                    result.forEach((element, idx) => {
                        $("#municipality_select").append(`<option value="${element.temp_prv}">${element.municipality}</option>`);
                    });
                    $("#municipality_select").val(null);
                    $("#dropoff_select").val(null);
                    $("#varieties-container").empty();
                }
            }
        });
    });

    $("#municipality_select").on('change', () => {
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getDropoff')}}",
            data: {mun: `${$("#municipality_select").val()}`},
            success: function(result){
                $("#dropoff_select").empty();
                $("#varieties-container").empty();
                if(result){
                    result.forEach((element, idx) => {
                        $("#dropoff_select").append(`<option value="${element.prv_dropoff_id}">${element.dropOffPoint}</option>`);
                    });
                    $("#dropoff_select").val(null);
                }
            }
        });
    });

    $("#dropoff_select").on('change', () => {
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getBalance')}}",
            data: {dop: `${$("#dropoff_select").val()}`},
            success: function(result){
                $("#varieties-container").empty();
                if(result){
                    availableVarieties = result;
                    setSeedVars(result);
                    result.forEach((element, idx) => {
                        $("#varieties-container").append(
                            `
                            <div class="indiv ${element.totalBag > 0? '' : 'na'}" data-index="${idx + 1}" style="animation: slideIn 0.4s calc(${idx + 1} * .15s) ease-in-out forwards;">
                                <div class="nai">
                                    ${element.totalBag > 0? (element.totalBag < 10? "0"+element.totalBag: element.totalBag): "N/A"}
                                </div>
                                <div class="fin">
                                    <span class="title">
                                        ${element.seedVariety}
                                    </span>
                                    <span class="desc">
                                        bags remaining.
                                    </span>
                                </div>
                            </div>  
                            `
                        );
                    });
                }
            }
        });
    });

    $("#btn-verify").on('click', (event) => {
        event.preventDefault();

        if(passedVerifications){
            releaseForm();
            disableSecondForm();
            passedVerifications = false;
            return;
        }

        globalInfo.rsbsa_control_no = $("#rsbsa-ctrl-no").val();
        globalInfo.assigned_rsbsa = $("#rsbsa-ctrl-no").val();
        globalInfo.firstName = ($("#firstName").val()).toUpperCase();
        globalInfo.midName = ($("#midName").val()).toUpperCase();
        globalInfo.lastName = ($("#lastName").val()).toUpperCase();
        globalInfo.extName = ($("#extName").val()).toUpperCase();
        globalInfo.fullName = `${globalInfo.lastName}, ${globalInfo.firstName}${globalInfo.midName? " "+globalInfo.midName : ""}${globalInfo.extName? ", "+globalInfo.extName : ""}`;
        globalInfo.sex = $("#sex").val();
        globalInfo.birthdate = $("#birthdate").val();
        
        resetErrors(globalInfo);
        handleErrors(globalInfo);

        if(!globalInfo.rsbsa_control_no || !globalInfo.firstName || !globalInfo.lastName){
            return;
        }


        //disable form
        $("#rsbsa-ctrl-no").prop('readonly', true);
        $("#firstName").prop('readonly', true);
        $("#midName").prop('readonly', true);
        $("#lastName").prop('readonly', true);
        $("#extName").prop('readonly', true);
        $("#sex").prop('disabled', true);
        $("#birthdate").prop('readonly', true);
        $("#btn-verify").attr('disabled', true);

        //replace status display
        $("#btn-verify").text("Verifying RSBSA from ICTS...");
        verifyRsbsaFromIcts(globalInfo.rsbsa_control_no)
    });

    function verifyRsbsaFromIcts(rsbsaData) {
        $.ajax({
            type: 'GET',
            url: "https://da-nrp.philrice.gov.ph/da-philrice-verifier/icts-farmer-verifier",
            data: {
                rsbsaData: rsbsaData
            },
            success: function (result) {
                if (result === "no data") {
                    existing.icts = false;
                    $("#btn-verify").text("Verifying full profile from ICTS...");
                    verifyNameFromIcts();
                } else {
                    existing.icts = true;
                    let commos = [];
                    has_no_rice = false;
                    if (result[0].parcels.length < 1) {
                        has_no_rice = true;
                    }
                    else {
                        result[0].parcels.forEach(element => {
                            element.commodities.forEach(el => {
                                commos.push(el);
                                if (el.cropname == "Rice/Palay") {
                                    has_no_rice = false;
                                    $("<div>").html(`
                                            <span style="font-style: italic">We've found this profile associated with the RSBSA you entered:</span>
                                            <br>
                                            <br>
                                            <span>RSBSA No: </span><span class="fw-700">${result[0].rsbsa_no}</span>
                                            <br>
                                            <span>Fullname: </span><span class="fw-700">${result[0].lname}, ${result[0].fname} ${result[0].mname} ${result[0].ext_name}</span>
                                            <br>
                                            <span>Birthdate: </span><span class="fw-700">${result[0].birthday}</span>
                                            <br>
                                            <br>
                                            <span style="font-weight: 500; font-style: italic;">If this is the farmer you're trying to encode, please use the <span class="fw-700">Verifier App</span>. Encoding cannot continue.</span>
                                        `).dialog({
                                        title: "Profile Found",
                                        modal: true,
                                        closeOnEscape: false,
                                        buttons: {
                                            Ok: function () {
                                                $(this).dialog("close");
                                                releaseForm();
                                            }
                                        },
                                        open: function (event, ui) {
                                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                                        }
                                    });
                                } else {
                                    has_no_rice = true;
                                }
                            });
                        });
                    }
                    if (has_no_rice) {
                        $("<div>").html(`
                                <span style="font-style: italic">Farmer with <strong>NO Rice/Palay area</strong> detected. </span>
                                <br>
                                <br>
                                <span>RSBSA No: </span><span class="fw-700">${result[0].rsbsa_no}</span>
                                <br>
                                <span>Fullname: </span><span class="fw-700">${result[0].lname}, ${result[0].fname} ${result[0].mname} ${result[0].ext_name}</span>
                                <br>
                                <span>Birthdate: </span><span class="fw-700">${result[0].birthday}</span>
                                <br>
                                <br>
                                <span style="font-weight: 500; font-style: italic;">Click "Okay" to proceed encoding this farmer without Rice/Palay commodities.</span>
                            `).dialog({
                            title: "Profile Found",
                            modal: true,
                            closeOnEscape: false,
                            buttons: {
                                Ok: function () {
                                    populateValues(result, true);
                                    $(this)?.dialog("close");
                                    $(this)?.dialog("close");
                                    $(this)?.dialog("close");
                                    finalizeForm();
                                }
                            },
                            open: function (event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                            }
                        });
                    }
                }
            }
        });
    }

    function populateValues(result, no_comm = false) {
        $("#firstName").val(result[0]?.fname);
        $("#midName").val(result[0]?.mname);
        $("#lastName").val(result[0]?.lname);
        $("#extName").val(result[0]?.ext_name);
        $("#sex").val(`${result[0].sex}`);
        $("#birthdate").val(`${result[0].birthday}`);
        globalInfo.firstName = result[0].fname;
        globalInfo.midName = result[0].mname;
        globalInfo.lastName = result[0].lname;
        globalInfo.extName = result[0].ext_name;
        globalInfo.fullName = `${result[0].lname}, ${result[0].fname} ${result[0].mname} ${result[0].ext_name}`;
        globalInfo.sex = result[0].sex;
        globalInfo.birthdate = result[0].birthday;
        globalRealeaseInfo.remarks = no_comm ? "No Commodity" : "";
    }

    function verifyNameFromIcts() {
        $.ajax({
            type: 'GET',
            url: "https://da-nrp.philrice.gov.ph/da-philrice-verifier/icts-farmer-verifier",
            data: {
                rsbsaData: null,
                firstname: globalInfo.firstName,
                middlename: globalInfo.midName,
                lastname: globalInfo.lastName,
                extname: globalInfo.extName,
                sex: globalInfo.sex,
                birthday: globalInfo.birthdate
            },
            success: function (result) {
                if (result === "no data") {
                    existing.icts = false;
                    $("#btn-verify").text("Verifying full profile from RSMS list...");
                    checkCurrentDatabase();
                } else {
                    existing.icts = true;
                    let commos = [];
                    has_no_rice = false;
                    if (result[0].parcels.length < 1) {
                        has_no_rice = true;
                    }
                    else {
                        result[0].parcels.forEach(element => {
                            element.commodities.forEach(el => {
                                commos.push(el);
                                if (el.cropname == "Rice/Palay") {
                                    has_no_rice = false;
                                    $("<div>").html(`
                                            <span style="font-style: italic">We've found this profile associated with the RSBSA you entered:</span>
                                            <br>
                                            <br>
                                            <span>RSBSA No: </span><span class="fw-700">${result[0].rsbsa_no}</span>
                                            <br>
                                            <span>Fullname: </span><span class="fw-700">${result[0].lname}, ${result[0].fname} ${result[0].mname} ${result[0].ext_name}</span>
                                            <br>
                                            <span>Birthdate: </span><span class="fw-700">${result[0].birthday}</span>
                                            <br>
                                            <br>
                                            <span style="font-weight: 500; font-style: italic;">If this is the farmer you're trying to encode, please use the <span class="fw-700">Verifier App</span>. Encoding cannot continue.</span>
                                        `).dialog({
                                        title: "Profile Found",
                                        modal: true,
                                        closeOnEscape: false,
                                        buttons: {
                                            Ok: function () {
                                                $(this).dialog("close");
                                                releaseForm();
                                            }
                                        },
                                        open: function (event, ui) {
                                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                                        }
                                    });
                                } else {
                                    has_no_rice = true;
                                }
                            });
                        });
                    }
                    if (has_no_rice) {
                        $("<div>").html(`
                            <span style="font-style: italic">Farmer with <strong>NO Rice/Palay area</strong> detected. </span>
                            <br>
                            <br>
                            <span>RSBSA No: </span><span class="fw-700">${result[0].rsbsa_no}</span>
                            <br>
                            <span>Fullname: </span><span class="fw-700">${result[0].lname}, ${result[0].fname} ${result[0].mname} ${result[0].ext_name}</span>
                            <br>
                            <span>Birthdate: </span><span class="fw-700">${result[0].birthday}</span>
                            <br>
                            <br>
                            <span style="font-weight: 500; font-style: italic;">Click "Okay" to proceed encoding this farmer without Rice/Palay commodities.</span>
                        `).dialog({
                            title: "Profile Found",
                            modal: true,
                            closeOnEscape: false,
                            buttons: {
                                Ok: function () {
                                    populateValues(result, true);
                                    $(this)?.dialog("close");
                                    $(this)?.dialog("close");
                                    $(this)?.dialog("close");
                                    finalizeForm();
                                }
                            },
                            open: function (event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                            }
                        });
                    }
                }
            }
        });
    }

    function checkCurrentDatabase(){
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.verifyFarmerFromList')}}",
            data: {
                rsbsa_control_no: globalInfo.rsbsa_control_no,
                firstName: globalInfo.firstName,
                midName: globalInfo.midName,
                lastName: globalInfo.lastName,
                extName: globalInfo.extName,
                sex: globalInfo.sex,
                birthdate: globalInfo.birthdate
            },
            success: function(result){
                if(result.status === "PASS"){
                    existing.curr = false;
                    $("<div>").html(`
                            <span style="font-style: italic">${result.message} Encoding will continue.</span>
                        `).dialog({
                        title: "Profile Verification Passed",
                        modal: true,
                        closeOnEscape: false,
                        buttons: {
                            Proceed: function() {
                                $(this).dialog("close");
                                finalizeForm();
                            },
                            Cancel: function() {
                                $(this).dialog("close");
                                releaseForm();
                            }
                        },
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                        }
                    });
                }else{
                    existing.curr = true;
                    $("<div>").html(`
                            <span style="font-style: italic">${result.message}</span>
                            <br>
                            <br>
                            <span>RSBSA No: </span><span class="fw-700">${result.data.rsbsa_control_no}</span>
                            <br>
                            <span>Fullname: </span><span class="fw-700">${result.data.fullName}</span>
                            <br>
                            <span>Birthdate: </span><span class="fw-700">${result.data.sex}</span>
                            <br>
                            <br>
                            <span style="font-weight: 500; font-style: italic;">If this is the farmer you're trying to encode, please use the <span class="fw-700">Distribution App</span>. Encoding cannot continue.</span>
                        `).dialog({
                        title: "Profile Found",
                        modal: true,
                        closeOnEscape: false,
                        buttons: {
                            Ok: function() {
                                $(this).dialog("close");
                                releaseForm();
                            }
                        },
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                        }
                    });
                }
            }
        });
    }

    function releaseForm(){
        $("#rsbsa-ctrl-no").prop('readonly', false);
        $("#firstName").prop('readonly', false);
        $("#midName").prop('readonly', false);
        $("#lastName").prop('readonly', false);
        $("#extName").prop('readonly', false);
        $("#sex").prop('disabled', false);
        $("#birthdate").prop('readonly', false);
        $("#btn-verify").attr('disabled', false);

        $("#btn-verify").text("Verify");
    }

    function finalizeForm(){
        $("#btn-verify").html(`Edit Information Above <i class="fa fa-pencil-square-o" aria-hidden="true"></i>`);
        $("#btn-verify").attr('disabled', false);
        passedVerifications = true;
        enableSecondForm();
    }

    function enableSecondForm(){
        $('.if-verified').css('display', 'block');

        //also initialize select2s
        $("#address_prv").select2({
            placeholder: "Select a province",
            allowClear: true
        });
        $("#address_mun").select2({
            placeholder: "Select a municipality",
            allowClear: true
        });
        $("#address_bgy").select2({
            placeholder: "Select a barangay",
            allowClear: true
        });
        $("#farm_mun").select2({
            placeholder: "Select a municipality",
            allowClear: true
        });
        $("#farm_bgy").select2({
            placeholder: "Select a barangay",
            allowClear: true
        });

        getAddrProvinces();
    }

    function disableSecondForm(){
        $('.if-verified').css('display', 'none');
    }

    function resetErrors(infoObj){
        if(infoObj?.rsbsa_control_no){
            $("#rsbsa-ctrl-no").css('box-shadow', '');
        }if(infoObj?.firstName){
            $("#firstName").css('box-shadow', '');
        }if(infoObj?.midName){
            $("#midName").css('box-shadow', '');
        }if(infoObj?.lastName){
            $("#lastName").css('box-shadow', '');
        }if(infoObj?.extName){
            $("#extName").css('box-shadow', '');
        }
    }

    function handleErrors(infoObj){
        if(!infoObj?.rsbsa_control_no){
            $("#rsbsa-ctrl-no").css('box-shadow', '0px 3px 0px red');
        }if(!infoObj?.firstName){
            $("#firstName").css('box-shadow', '0px 3px 0px red');
        }if(!infoObj?.midName){
            $("#midName").css('box-shadow', '0px 3px 0px orange');
        }if(!infoObj?.lastName){
            $("#lastName").css('box-shadow', '0px 3px 0px red');
        }if(!infoObj?.extName){
            $("#extName").css('box-shadow', '0px 3px 0px orange');
        }if(infoObj?.sex){
            $("#sex").css('box-shadow', '0px 3px 0px orange');
        }if(infoObj?.birthdate){
            $("#birthdate").css('box-shadow', '0px 3px 0px orange');
        }
    }

    function getAddrProvinces(){
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getAddrProvinces')}}",
            success: function(result){
                result.forEach((element, idx) => {
                    $("#address_bgy").empty();
                    $("#address_prv").append(`
                    <option value="${element.prv_code}">${element.province}</option>
                    `);
                });
                $("#address_prv").val(null);

                $("#address_mun").val(null);
                $("#address_bgy").val(null);
            }
        });
    }
    
    $("#area_harvested").on("input", () => {
        areaH = $("#area_harvested").val() || 0;
        noBags = $("#no_of_bags").val() || 0;
        wtBags = $("#wt_per_bag").val() || 0;

        $("#yield").val(autoComputeYield(noBags, wtBags, areaH));
    });

    $("#no_of_bags").on("input", () => {
        areaH = $("#area_harvested").val() || 0;
        noBags = $("#no_of_bags").val() || 0;
        wtBags = $("#wt_per_bag").val() || 0;

        $("#yield").val(autoComputeYield(noBags, wtBags, areaH));
    });

    $("#wt_per_bag").on("input", () => {
        areaH = $("#area_harvested").val() || 0;
        noBags = $("#no_of_bags").val() || 0;
        wtBags = $("#wt_per_bag").val() || 0;

        $("#yield").val(autoComputeYield(noBags, wtBags, areaH));
    });

    $("#address_prv").on('change', () => {
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getAddrMunicipalities')}}",
            data: {prv: $("#address_prv").val()},
            success: function(result){
                $("#address_mun").empty();
                $("#farm_mun").empty();
                $("#address_bgy").empty();
                result.forEach((element, idx) => {
                    $("#address_mun").append(
                        `<option value="${element.prv}">${element.municipality}</option>`
                    );
                    $("#farm_mun").append(
                        `<option value="${element.prv}">${element.municipality}</option>`
                    );
                });
                $("#address_mun").val(null);
                $("#farm_mun").val(null).trigger('change');
                $("#address_bgy").val(null);
            }
        });
    });
    
    $("#address_mun").on('change', () => {
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getAddrBarangays')}}",
            data: {geo: $("#address_mun").val()},
            success: function(result){
                $("#address_bgy").empty();
                result.forEach((element, idx) => {
                    $("#address_bgy").append(
                        `<option value="${element.geocode_brgy}">${element.name}</option>`
                    );
                });
                $("#address_bgy").val(null);
            }
        });
    });

    $("#farm_mun").on('change', () => {
        $.ajax({
            type: 'GET',
            url: "{{route('onlineEncodingNew.getAddrBarangays')}}",
            data: {geo: $("#farm_mun").val()},
            success: function(result){
                $("#farm_bgy").empty();
                result.forEach((element, idx) => {
                    $("#farm_bgy").append(
                        `<option value="${element.geocode_brgy}">${element.name}</option>`
                    );
                });
                $("#farm_bgy").val(null);
            }
        });
    });

    $("#btn-finalize-profile").on("click", (event) => {
        event.preventDefault();

        globalInfo.final_area = parseFloat($("#farm_area").val());
        globalInfo.final_claimable = Math.ceil(parseFloat($("#farm_area").val()) * 2) > 10? 10 : Math.ceil(parseFloat($("#farm_area").val()) * 2);
        globalInfo.mother_name = $("#mother_name").val()? ($("#mother_name").val()).toUpperCase() : "-";
        globalInfo.geo_code = $("#farm_bgy").val();
        globalInfo.claiming_brgy = $("#farm_bgy").val();
        globalInfo.is_ip = $("#isIp").prop("checked")? 1 : 0;
        globalInfo.is_pwd = $("#isPwd").prop("checked")? 1 : 0;
        globalInfo.is_arb = $("#isArb").prop("checked")? "YES" : "NO";

        if(!globalInfo.final_area || !globalInfo.geo_code || !globalInfo.claiming_brgy){
            stopFinalizeAlert({addr_bgy: globalInfo.geo_code, farm_bgy: globalInfo.claiming_brgy, area: globalInfo.final_area});
            return;
        }
        
        confirmationFinalizeAlert();
    }); 

    function stopFinalizeAlert(eparams){
        $("<div>").html(`
                <span class="fw-700">Field(s) cannot be empty!</span>
                <br>
                <ul>
                    ${!eparams.addr_bgy? "<li>Hometown</li>" : ""}
                    ${!eparams.farm_bgy? "<li>Farm Address</li>" : ""}
                    ${!eparams.area? "<li>Farm Area</li>" : ""}
                </ul>
            `).dialog({
            title: "Notice",
            modal: true,
            closeOnEscape: false,
            buttons: {
                Ok: function() {
                    $(this).dialog("close");
                }
            },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
            }
        });
    }

    function confirmationFinalizeAlert(){
        $("<div>").html(`
                <span class="fw-700">Please make sure all the fields above are correct! They are final and once you click "Continue", you won't be able to edit them.</span>
                <br>
                <br>
                <span><i>Click "Edit" to go back and re-edit all the fields.</i></span>
            `).dialog({
            title: "Notice",
            modal: true,
            closeOnEscape: false,
            buttons: {
                Continue: function() {
                    $(this).dialog("close");
                    finalizeSecondForm();
                },
                Edit: function() {
                    $(this).dialog("close");
                }
            },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
            }
        });
    }

    function finalizeSecondForm(){
        finalizeProfile();
    }

    function finalizeProfile(){
        $("#farm_area").prop('readonly', true);
        $("#mother_name").prop('readonly', true);
        $("#address_prv").prop('disabled', true);
        $("#address_mun").prop('disabled', true);
        $("#address_bgy").prop('disabled', true);
        $("#farm_mun").prop('disabled', true);
        $("#farm_bgy").prop('disabled', true);
        $("#isIp").prop("disabled", true);
        $("#isPwd").prop("disabled", true);
        $("#isArb").prop("disabled", true);

        $("#btn-verify").css('display', 'none');
        $("#btn-finalize-profile").css('display', 'none');
        $(".is-second-form-passed").css('display', 'block');

        $("#dropoff_select").select2({
            placeholder: "Select a dropoff",
            allowClear: true,
            ajax: {
                url: "{{route('onlineEncodingNew.getDropoff')}}",
                dataType: 'json',
                data: {mun: $("#farm_mun").val()},
                processResults: function (data) {
                    // Transform the data into the format expected by Select2
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.prv_dropoff_id,
                                text: item.dropOffPoint
                                // You can customize based on your API response structure
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $("#variety_planted").select2({
            placeholder: "Select Variety",
            allowClear: true,
            ajax: {
                url: "{{route('onlineEncodingNew.getSeedVars')}}",
                processResults: function (data) {
                    // Transform the data into the format expected by Select2
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.seedItem,
                                text: item.seedName
                                // You can customize based on your API response structure
                            };
                        })
                    };
                },
                cache: true
            }
        })

        $("#claimed_area").val(globalInfo.final_area);
        $("#claimed_bags").val(globalInfo.final_claimable);
    }

    function setSeedVars(seeds){
        $("#seed_variety").select2({
            placeholder: "Select a seed variety",
            allowClear: true
        });
        seeds.forEach((element, idx) => {
            if(element.totalBag > 0){
                $("#seed_variety").append(
                    `<option value="${element.seedVariety}">${element.seedVariety}</option>`
                );
            }
        });
    }

    $("#btn-save-dist").on('click', (event) => {
        event.preventDefault();

        globalRealeaseInfo.province = $("#address_prv :selected").text();
        globalRealeaseInfo.municipality = $("#farm_mun :selected").text();
        globalRealeaseInfo.dropOffPoint = $("#dropoff_select :selected").text();
        globalRealeaseInfo.prv_dropoff_id = $("#dropoff_select").val();
        globalRealeaseInfo.seed_variety = $("#seed_variety").val();
        globalRealeaseInfo.claimed_area = parseFloat($("#claimed_area").val());
        globalRealeaseInfo.bags_claimed = parseInt($("#claimed_bags").val());
        globalRealeaseInfo.crop_establishment_cs = $("#crop_estab").val();
        
        switch ($("#ecosystem_cs").val()) {
            case "rain-upl":
                globalRealeaseInfo.ecosystem_cs = "rainfed";
                globalRealeaseInfo.ecosystem_source_cs = "Upland";
                break;
            case "rain-low":
                globalRealeaseInfo.ecosystem_cs = "rainfed";
                globalRealeaseInfo.ecosystem_source_cs = "Lowland";
                break;
            case "irri-nia":
                globalRealeaseInfo.ecosystem_cs = "irrigated";
                globalRealeaseInfo.ecosystem_source_cs = "NIS/NIA";
                break;
            case "irri-cis":
                globalRealeaseInfo.ecosystem_cs = "irrigated";
                globalRealeaseInfo.ecosystem_source_cs = "CIS(Communal)";
                break;
            case "irri-stw":
                globalRealeaseInfo.ecosystem_cs = "irrigated";
                globalRealeaseInfo.ecosystem_source_cs = "STW(Shallow Tube Well)";
                break;
            case "irri-swp":
                globalRealeaseInfo.ecosystem_cs = "irrigated";
                globalRealeaseInfo.ecosystem_source_cs = "SWIP(Small water impounding pond)";
                break;
            case "irri-rsp":
                globalRealeaseInfo.ecosystem_cs = "irrigated";
                globalRealeaseInfo.ecosystem_source_cs = "River/Stream Pumping";
                break;
            default:
                globalRealeaseInfo.ecosystem_cs = "rainfed";
                globalRealeaseInfo.ecosystem_source_cs = "Upland";
        }

        globalRealeaseInfo.planting_week = $("#planting_month").val()+"/"+$("#planting_week").val();
        globalRealeaseInfo.has_kp_kit = $("#rcvdKp").prop("checked")? "yes" : "no";
        
        tempOtherBenf = [];
        if($("#rcvdFert").prop("checked")) tempOtherBenf.push("fertilizer");
        if($("#rcvdCash").prop("checked")) tempOtherBenf.push("cash incentive");
        if($("#rcvdCredit").prop("checked")) tempOtherBenf.push("credit/loan");
        globalRealeaseInfo.other_benefits_received = tempOtherBenf.join(", ");
        
        globalRealeaseInfo.date_released = new Date().toISOString().split('T')[0];
        globalRealeaseInfo.content_rsbsa = globalInfo.rsbsa_control_no;
        globalRealeaseInfo.birthdate = globalInfo.birthdate;
        globalRealeaseInfo.final_area = globalInfo.final_area;

        let tempYield = [];
        tempYield.push({
            variety: $("#variety_planted").val(),
            area: $("#area_harvested").val(),
            bags: $("#no_of_bags").val(),
            weight: $("#wt_per_bag").val(),
            type: $("#seed_type").val(),
            class: $("#seed_class").val()
        });

        globalRealeaseInfo.yield_last_season_details = JSON.stringify(tempYield);
        globalRealeaseInfo.sex = globalInfo.sex;

        globalInfo.province = $("#address_prv :selected").text();
        globalInfo.municipality = $("#farm_mun :selected").text();
        globalInfo.brgy_name = $("#farm_bgy :selected").text();

        globalInfo.total_claimed_area = parseFloat($("#claimed_area").val());
        globalInfo.total_claimed = parseInt($("#claimed_bags").val());
        globalInfo.replacement_area = parseFloat($("#claimed_area").val());
        globalInfo.replacement_bags = parseInt($("#claimed_bags").val());

        globalInfo.claiming_prv = $("#farm_mun").val();
        globalInfo.claiming_prv = `${globalInfo.claiming_prv.substring(0, 2)}-${globalInfo.claiming_prv.substring(2, 4)}-${globalInfo.claiming_prv.substring(4)}`;

        if(!globalRealeaseInfo.seed_variety || !$("#variety_planted").val() || !$("#area_harvested").val() || !$("#no_of_bags").val() || !$("#wt_per_bag").val() || ($("#area_harvested").val() < 0.1 || $("#area_harvested").val() > 14) || ($("#no_of_bags").val() < 20 || $("#no_of_bags").val() > 3000) || ($("#wt_per_bag").val() < 35 || $("#wt_per_bag").val() > 70)){
            stopDistribution();
            return;
        }

        startDistribution();
    });

    function stopDistribution(){
        $("<div>").html(`
                <span class="fw-700">We've detected anomalies on required field(s):</span>
                <br>
                <br>
                <ul>
                    ${!globalRealeaseInfo.seed_variety? "<li>Seed Variety Claimed</li>" : ""}
                    ${!$("#variety_planted").val()? "<li>Yield: Seed Variety</li>" : ""}
                    ${!$("#area_harvested").val()? "<li>Yield: Area Harvested</li>" : ""}
                    ${!$("#no_of_bags").val()? "<li>Yield: Number of Bags</li>" : ""}
                    ${!$("#wt_per_bag").val()? "<li>Yield: Weight per Bag</li>" : ""}
                    ${$("#area_harvested").val() < 0.1? "<li>Yield: Area harvested below the minimum 0.1</li>" : ""}
                    ${$("#area_harvested").val() > 14? "<li>Yield: Area harvested above the maximum 14</li>" : ""}
                    ${$("#no_of_bags").val() < 20? "<li>Yield: Total production is below the minimum 20 bags</li>" : ""}
                    ${$("#no_of_bags").val() > 3000? "<li>Yield: Total production is above the maximum 3000 bags</li>" : ""}
                    ${$("#wt_per_bag").val() < 35? "<li>Yield: Weight per bag is below the minimum 35 kilograms</li>" : ""}
                    ${$("#wt_per_bag").val() > 70? "<li>Yield: Weight per bag is above the maximum 70 kilograms</li>" : ""}
                </ul>
                <br>
                <br>
                <i class="fw-700">Please fix the anomal(ies) before continuing!<i>
            `).dialog({
            title: "Notice",
            modal: true,
            closeOnEscape: false,
            buttons: {
                Okay: function() {
                    $(this).dialog("close");
                }
            },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
            }
        });
    }

    function startDistribution(){
        $("<div>").html(`
                <span class="fw-700">Farmer Information</span>
                <br>
                <br>
                <span>Name: <span class="fw-700">${globalInfo.fullName}</span></span>
                <br>
                <span>Address: <span class="fw-700">${[globalInfo.province, globalInfo.municipality, globalInfo.brgy_name].join(', ')}</span></span>
                <br>
                <span>Farm Address: <span class="fw-700">${[globalRealeaseInfo.province, globalRealeaseInfo.municipality].join(", ")}</span></span>
                <br>
                <br>
                <span>Trying to claim <span class="fw-700">${globalRealeaseInfo.bags_claimed}</span> bag(s) of <span class="fw-700">${globalRealeaseInfo.seed_variety}</span>. Confirm if correct!</span>
            `).dialog({
            title: "Summary",
            modal: true,
            closeOnEscape: false,
            buttons: {
                'Go Back': function() {
                    $(this).dialog("close");
                },
                Confirm: function() {
                    saveDistribution();
                    $(this).dialog("close");
                }
            },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
            }
        });
    }

    function saveDistribution(){
        disableLastForm();
        
        $("#btn-save-dist").prop("disabled", true);
        $("#btn-save-dist").text("Commiting information to database...");

        $.ajax({
            type: 'POST',
            url: "{{route('onlineEncodingNew.saveDistribution')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                profile: globalInfo,
                release: globalRealeaseInfo
            },
            success: function(result){
                if(result.status == 200){
                    openSuccessMessage(result.data);
                }else{
                    openFailedMessage(result);
                }
            }
        });
    }

    function disableLastForm(){
        $("#dropoff_select").prop('disabled', true);
        $("#seed_variety").prop('disabled', true);
        $("#area_harvested").prop('readonly', true);
        $("#no_of_bags").prop('readonly', true);
        $("#wt_per_bag").prop('readonly', true);
        $("#variety_planted").prop('disabled', true);
        $("#seed_type").prop('disabled', true);
        $("#seed_class").prop('disabled', true);
        $("#crop_estab").prop('disabled', true);
        $("#ecosystem_cs").prop('disabled', true);
        $("#planting_month").prop('disabled', true);
        $("#planting_week").prop('disabled', true);
        $("#rcvdKp").prop('disabled', true);
        $("#rcvdFert").prop('disabled', true);
        $("#rcvdCash").prop('disabled', true);
        $("#rcvdCredit").prop('disabled', true);
    }

    function enableLastForm(){
        $("#dropoff_select").prop('disabled', false);
        $("#seed_variety").prop('disabled', false);
        $("#area_harvested").prop('readonly', false);
        $("#no_of_bags").prop('readonly', false);
        $("#wt_per_bag").prop('readonly', false);
        $("#variety_planted").prop('disabled', false);
        $("#seed_type").prop('disabled', false);
        $("#seed_class").prop('disabled', false);
        $("#crop_estab").prop('disabled', false);
        $("#ecosystem_cs").prop('disabled', false);
        $("#planting_month").prop('disabled', false);
        $("#planting_week").prop('disabled', false);
        $("#rcvdKp").prop('disabled', false);
        $("#rcvdFert").prop('disabled', false);
        $("#rcvdCash").prop('disabled', false);
        $("#rcvdCredit").prop('disabled', false);
    }

    function openSuccessMessage(eparams){
        $("#btn-save-dist").text("Success!");
        $("#rcef-id").val(eparams.rcef_id);
        $("#db-ref").val(eparams.db_ref);

        $("<div>").html(`
                <span>Successfully encoded <span class="fw-700">${eparams.rcef_id}</span>!</span>
                <br>
                <br>
                <span>You can scroll up to the very top of the page to see the generated <span class="fw-700">Database Reference Number</span> and <span class="fw-700">RCEF ID</span></span>
            `).dialog({
            title: "Notice",
            modal: true,
            closeOnEscape: false,
            buttons: {
                Close: function() {
                    $(this).dialog("close");
                    window.scrollTo(0, 0);
                }
            },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
            }
        });
    }

    function openFailedMessage(eparams){
        $("#btn-save-dist").text("Save Distribution");
        $("#btn-save-dist").prop("disabled", false);

        $("<div>").html(`
                <span>There was an error saving the profile/release. Please try again later...</span>
                <br>
                <br>
                <code>${eparams}</code>
            `).dialog({
            title: "Notice",
            modal: true,
            closeOnEscape: false,
            buttons: {
                Okay: function() {
                    $(this).dialog("close");
                }
            },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
            }
        });
        enableLastForm();
    }
    
    function autoComputeYield(noBag = 0, wtBag = 0, areaH = 0){
        return Math.round((noBag * wtBag / areaH) / 1000 * 100) / 100;
    }
</script>
@endpush