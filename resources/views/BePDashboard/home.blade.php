<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index') @section('styles')
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}" />
<link
  rel="stylesheet"
  href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}"
/>
<link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}" />
<link href="public/css/HoldOn.min.css" rel="stylesheet" />
<link
  rel="stylesheet"
  href="https://code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css"
/>
<style>
  .shadow-sm {
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  }
  .shadow {
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  }
  .shadow-md {
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  }
  .shadow-lg {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
      0 4px 6px -4px rgb(0 0 0 / 0.1);
  }
  .shadow-xl {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1),
      0 8px 10px -6px rgb(0 0 0 / 0.1);
  }
  .shadow-2xl {
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  }
  .shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);
  }
  .shadow-none {
    box-shadow: 0 0 #0000;
  }

  .right_col {
    position: relative !important;
  }

  .x_panel {
    color: black;
    position: absolute;
    border-radius: 2em;
  }

  .x_title {
    margin: 10px;
    border: 1px #6b6b6b;
    box-sizing: border-box;
    background-color: transparent;
    border-radius: 10px;
    padding: 10px;
  }

  .x_title h1 {
    font-weight: 700;
  }

  #containers {
    margin: 10px;
    border: 1px #6b6b6b;
    box-sizing: border-box;
    border-radius: 10px;
    padding: 10px;
    background-color: #faf5f580;
    backdrop-filter: blur(5px);
  }

  h4 {
    font-weight: 700;
    margin: 0 0 0.4em 0;
    padding: 0;
  }

  hr {
    border: none;
    height: 1px;
    background-color: #6b6b6b;
    margin-top: 0.5px;
    margin-bottom: 10px;
  }

  select {
    border: 1px solid #888;
    border-radius: 0.6em;
    padding: 0.2em;
    font-size: 1.4em;
  }

  .selectors {
    display: inline-block;
  }

  .selector_cards {
    display: inline-block;
    margin-right: 10px;
    margin-left: 10px;
  }

  .submit {
    width: max-content;
    margin-left: 15px;
    height: 2.6em;
  }

  .boxes {
    width: 100%;
    height: 100%;
    border: 1px #6b6b6b;
    box-sizing: border-box;
    border-radius: 1em;
    padding: 2em 1em !important;
    background-color: #cfffde;
    backdrop-filter: blur(5px);
  }

  .col-md-12 {
    background-color: #fcf6d7;
  }
  #tgtben,
  #tgtbag,
  #tgtarea,
  #actben,
  #actbag,
  #actarea {
    width: 100%;
    float: left;
    margin-top: 10px;
    margin-bottom: 10px;
  }
  .row {
    clear: both;
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
  }

  .shadow {
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
  }

  .btn {
    display: inline-block;
    margin-top: 5px;
    margin-right: 5px;
  }

  #row {
    display: none;
  }

  #date1 {
    height: 2.6em;
    width: 30em;
  }
  #submit {
    margin: 0;
    height: 2.6em;
  }

  .grid-container > div:nth-of-type(1) {
    grid-area: one;
  }

  .grid-container > div:nth-of-type(2) {
    grid-area: two;
  }

  .grid-container > div:nth-of-type(3) {
    grid-area: thr;
  }

  .grid-container > div:nth-of-type(4) {
    grid-area: fou;
  }

  .grid-container > div:nth-of-type(5) {
    grid-area: fiv;
  }

  .grid-container > div:nth-of-type(6) {
    grid-area: six;
  }

  .grid-container > div:nth-of-type(7) {
    grid-area: sev;
  }

  .grid-container > div:nth-of-type(8) {
    grid-area: eig;
  }

  .grid-container > div:nth-of-type(9) {
    grid-area: nin;
  }

  .grid-container {
    display: grid;
    gap: 10px;
    padding: 10px;
    grid-template-areas:
      "one one one two two two thr thr thr"
      "fou fou fou fiv fiv fiv six six six"
      "eig eig eig nin nin nin sev sev sev";
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr;
  }

  .onLoadProvincialData,
  .datedProvincialData {
    text-align: right;
  }

  .onLoadProvincialData tr td:nth-of-type(1),
  .datedProvincialData tr td:nth-of-type(1) {
    text-align: left;
  }

  #page {
    transform: translateY(-50px);
    opacity: 0;
    animation: slideDown 1000ms ease-in-out forwards;
  }

  @keyframes slideDown {
    0% {
      transform: translateY(-120px);
      opacity: 0;
    }
    100% {
      transform: translateY(-50px);
      opacity: 1;
    }
  }

  .italicized {
    font-style: italic;
    color: black !important;
  }
</style>
@endsection @section('content')

<div class="clearfix" id="page">
  @include('layouts.message')

  <div class="row" id="page2">
    <div class="col-md-12" id="page1">
      <div class="x_panel">
        <div class="x_title">
          <div class="grid-container">
            <div class="col-md-6" id="tgtben">
              <div class="boxes shadow-md">
                <h4 style="color: black">Target number of beneficiaries:</h4>
                <hr />
                <h1 style="color: black">
                  {{ $beneficiariesCount }} Beneficiaries
                </h1>
              </div>
            </div>
            <div class="col-md-6" id="tgtbag">
              <div class="boxes shadow-md">
                <h4 style="color: black">
                  Target number of bags to be distributed:
                </h4>
                <hr />
                <h1 style="color: black">{{ $bagsCount }} Bags</h1>
              </div>
            </div>
            <div class="col-md-6" id="tgtarea">
              <div class="boxes shadow-md">
                <h4 style="color: black">Target area coverage:</h4>
                <hr />
                <h1 style="color: black">{{ $areaCount }} ha</h1>
              </div>
            </div>
            <div class="col-md-6" id="actben">
              <div class="boxes shadow-md">
                <h4 style="color: black">Actual number of beneficiaries:</h4>
                <hr />
                <h1 style="color: black">
                  {{ $actualBeneficiaries }} Beneficiaries
                </h1>
                <h6 style="color: black">
                  <span class="italicized"
                    >({{ $beneficiariesPercentage }}% accomplished, relative to
                    target beneficiaries)</span
                  >
                </h6>
              </div>
            </div>
            <div class="col-md-6" id="actbag">
              <div class="boxes shadow-md">
                <h4 style="color: black">Actual number of bags distributed:</h4>
                <hr />
                <h1 style="color: black">{{ $actualBags }} Bags</h1>
                <h6 style="color: black">
                  <span class="italicized"
                    >({{ $bagsPercentage }}% accomplished, relative to target
                    bags)</span
                  >
                </h6>
              </div>
            </div>
            <div class="col-md-6" id="actarea">
              <div class="boxes shadow-md">
                <h4 style="color: black">Estimated area planted:</h4>
                <hr />
                <h1 style="color: black">{{ $actualArea }} ha</h1>
                <h6 style="color: black">
                  <span class="italicized"
                    >({{ $areaPercentage }}% accomplished, relative to target
                    area)</span
                  >
                </h6>
              </div>
            </div>
            
          </div>
          <div class="controls" style="display: flex; justify-content: space-between; padding: 0.4em 2em;">
              <div>
                <select name="selectedView" id="selectedView">
                  <option value="provincial">Provincial View</option>
                  <option value="coop">Cooperative View</option>
                </select>
              </div>
              <div style="display: flex; justify-content: end; gap: 1em;">
                <input type="text" id="date1" /><button
                  type="button"
                  id="submit"
                  class="btn btn-success submit"
                >
                  Submit
                </button>
              </div>
            </div>
          <div
            class="x_content form-horizontal form-label-left"
            style="height: 45vh !important; overflow-y: scroll !important"
          >
            <table
              class="table table-hover table-striped table-bordered"
              id="province_tbl"
              style="background-color: white"
            >
              <thead>
                <th>Province/Cooperative</th>
                <th style="display: none">Municipality</th>
                <th>Target Beneficiaries</th>
                <th>Target Bags</th>
                <th>Target Area</th>
                <th>Beneficiaries (Claimants)</th>
                <th>Bags Distributed</th>
                <th>Amount</th>
                <th>Equivalent Area</th>

                <th>Export options</th>
              </thead>
              <tbody class="onLoadProvincialData">
                @foreach ($provTgt as $row)
                <tr>
                  <td>{{ $row["province"] }}</td>
                  <td style="display: none">{{ $row["municipality"] }}</td>
                  <td>{{ $row["beneficiaries"] }}</td>
                  <td>{{ $row["bags"] }}</td>
                  <td>{{ $row["area"] }}</td>
                  <td>{{ $row["actualBeneficiaries"] }}</td>
                  <td>{{ $row["actualBags"] }}</td>
                  <td>{{ $row["amount"] }}</td>
                  <td>{{ $row["actualArea"] }}</td>
                  <td>
                  <a href="{{ route('downloadPrvData', ['province' => $row['province']]) }}" class="btn btn-success btn-sm">
                  <i class="fa fa-download"></i> Download provincial data</a>
                    <a
                      href="#"
                      target="_blank"
                      data-province="{{ $row['province'] }}"
                      data-toggle="modal"
                      data-target="#download_modal"
                      id="view_mun"
                      class="btn btn-success btn-sm"
                      ><i class="fa fa-eye"></i> View municipal data</a
                    >
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>

            <table
              class="table table-hover table-striped table-bordered"
              id="province_tbl2"
              style="display: none"
              style="background-color: white"
            >
              <thead style="background-color: white">
                <th>Province/Cooperative</th>
                <th style="display: none">Municipality</th>
                <th>Target Beneficiaries</th>
                <th>Target Bags</th>
                <th>Target Area</th>
                <th>Beneficiaries (Claimants)</th>
                <th>Bags Distributed</th>
                <th>Amount</th>
                <th>Equivalent Area</th>

                <th>Export options</th>
              </thead>
              <tbody
                class="datedProvincialData"
                style="background-color: white"
              ></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="download_modal" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 70%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title">
          <span
            >Binhi e-Padala Municipal Distribution for Province/Cooperative "<span
              id="modal_province"
            ></span
            >"</span
          ><br />
        </h4>
      </div>
      <div class="modal-body">
        <table
          class="table table-hover table-striped table-bordered"
          id="municipality_tbl"
        >
          <thead>
            <th>Municipality</th>
            <th>Target Beneficiaries</th>
            <th>Target Bags</th>
            <th>Target Area</th>
            <th>Beneficiaries (Claimants)</th>
            <th>Bags Distributed</th>
            <th>Amount</th>
            <th>Equivalent Area</th>
            <th>Export options</th>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="download_modal2" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 70%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title">
          <span
            >Binhi e-Padala Municipal Distribution for Province/Cooperative "<span
              id="modal_province2"
            ></span
            >"</span
          ><br />
        </h4>
      </div>
      <div class="modal-body">
        <table
          class="table table-hover table-striped table-bordered"
          id="municipality_tbl2"
        >
          <thead>
            <th>Municipality</th>
            <th>Target Beneficiaries</th>
            <th>Target Bags</th>
            <th>Target Area</th>
            <th>Beneficiaries (Claimants)</th>
            <th>Bags Distributed</th>
            <th>Amount</th>
            <th>Equivalent Area</th>
            <th>Export options</th>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection() @push('scripts')
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script src=" {{ asset('public/js/select2.min.js') }} "></script>
<script src=" {{ asset('public/js/parsely.js') }} "></script>
<script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
<!-- <script src=" {{ asset('public/js/highcharts.js') }} "></script> -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="public/js/HoldOn.min.js"></script>

<script>
  var currentDate = new Date();

  var yearStart = new Date(currentDate.getFullYear(), 0, 1);

  var startDateString =
    yearStart.getMonth() +
    1 +
    "/" +
    yearStart.getDate() +
    "/" +
    yearStart.getFullYear();
  var endDateString =
    currentDate.getMonth() +
    1 +
    "/" +
    currentDate.getDate() +
    "/" +
    currentDate.getFullYear();

  $("#date1").daterangepicker({
    startDate: startDateString,
    endDate: endDateString,
    minDate: new Date(2023, 0, 1),
  });


  $("#submit").on("click", function (e) {
    var daterange = $("#date1").val();
    var selectedView = $("#selectedView").val();
    $date1 = daterange.substring(0, 10);
    $date2 = daterange.substring(13, 23);

    if (selectedView === 'provincial') {
      columns = [
        { data: "province" },
        { data: "beneficiaries" },
        { data: "bags" },
        { data: "area" },
        { data: "actualBeneficiaries" },
        { data: "actualBags" },
        { data: "amount" },
        { data: "actualArea" },
        { data: "action" },
      ];
    } 
    else if (selectedView === 'coop') {
      columns = [
        { data: "coop" },
        { data: "beneficiaries" },
        { data: "bags" },
        { data: "area" },
        { data: "actualBeneficiaries" },
        { data: "actualBags" },
        { data: "amount" },
        { data: "actualArea" },
        { data: "action" },
      ];
    }
    $("#province_tbl").hide();
    $("#province_tbl2").show();
    $("#province_tbl2").DataTable().clear();
    $("#province_tbl2").DataTable({
      bDestroy: true,
      autoWidth: false,
      searchHighlight: true,
      processing: true,
      serverSide: true,
      orderMulti: true,
      order: [],
      ajax: {
        url: "{{ route('get_DatedData') }}",
        dataType: "json",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          selectedView: selectedView,
          date1: $date1,
          date2: $date2,
        },
      },
      columns: columns,
    });
  });

  $("#download_modal").on("shown.bs.modal", function (e) {
    var province = $(e.relatedTarget).data("province");

    $("#municipality_tbl").DataTable().clear();
    $("#municipality_tbl").DataTable({
      bDestroy: true,
      autoWidth: false,
      searchHighlight: true,
      processing: true,
      serverSide: true,
      orderMulti: true,
      order: [],
      ajax: {
        url: "{{ route('get_MunicipalData') }}",
        dataType: "json",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          province: province,
        },
      },
      columns: [
        { data: "municipality" },
        { data: "beneficiaries" },
        { data: "bags" },
        { data: "area" },
        { data: "actualBeneficiaries" },
        { data: "actualBags" },
        { data: "amount" },
        { data: "actualArea" },
        { data: "action" },
      ],
    });
    $("#modal_province").text(province);
  });

  $("#download_modal2").on("shown.bs.modal", function (e) {
    var province = $(e.relatedTarget).data("province");
    var daterange = $("#date1").val();
    var selectedView = $("#selectedView").val();
    $date1 = daterange.substring(0, 10);
    $date2 = daterange.substring(13, 23);

    $("#municipality_tbl2").DataTable().clear();
    $("#municipality_tbl2").DataTable({
      bDestroy: true,
      autoWidth: false,
      searchHighlight: true,
      processing: true,
      serverSide: true,
      orderMulti: true,
      order: [],
      ajax: {
        url: "{{ route('get_DatedMunData') }}",
        dataType: "json",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          province: province,
          selectedView: selectedView,
          date1: $date1,
          date2: $date2,
        },
      },
      columns: [
        { data: "municipality" },
        { data: "beneficiaries" },
        { data: "bags" },
        { data: "area" },
        { data: "actualBeneficiaries" },
        { data: "actualBags" },
        { data: "amount" },
        { data: "actualArea" },
        { data: "action" },
      ],
    });
    $("#modal_province2").text(province);
  });

  $("#btn_download").on("click", function (e) {
    var province = $("#modal_province").text();
    var date1 = $("#date1").val();
    var date2 = $("#date2").val();

    window.open("provincial/" + province + "/" + date1 + "/" + date2, "_blank");
  });
</script>
@endpush
