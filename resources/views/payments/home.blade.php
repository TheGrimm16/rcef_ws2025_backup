@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}"/>
    <link
    rel="stylesheet"
    href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}"/>
    <link href="public/css/HoldOn.min.css" rel="stylesheet" />
    <link
    rel="stylesheet"
    href="https://code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css"/>
    <style>
        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        .stationMonitoring .boxes{
            margin-bottom: 1em;
        }
/* 
        .notification{
            padding: 2px;
            border-radius: 10px;
        } */

        .boxes {
            width: 100%;
            height: 100%; 
            padding: 1em 2em;
            /* border: 1px #6b6b6b;
            box-sizing: border-box;
            border-radius: 1em;
            padding: 1em 1em !important;
            backdrop-filter: blur(5px); */
            border-radius: 30px;
            background: #e0e0e0;
            box-shadow: 20px 20px 60px #bebebe,
               -20px -20px 60px #ffffff;
               border: 2px solid #c3c6ce;
                -webkit-transition: 0.5s ease-out;
                transition: 0.5s ease-out;
                overflow: visible;
        }

        .boxes:hover {
        border-color: #3ed655;
        -webkit-box-shadow: 10px 5px 18px 0 rgba(255, 255, 255, 0.877);
        box-shadow: 10px 5px 18px 0 rgba(255, 255, 255, 0.877);
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

        .mini-flex{
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .mini-item{
            display: flex;
            justify-content: space-between;
            border-bottom: dotted 2px black;
            padding: 0;
        }

        .mini-item *{
            margin: 0.4em 0;
            padding: 0 0.4em;
            background: #e0e0e0;
            transform: translateY(0.7em);
        }


        .grid-container {
            display: grid;
            gap: 2em;
            /* padding: 10px; */
            grid-template-areas:
            "one one one"
            "two fou fou"
            "thr fou fou";
            grid-template-columns: 30fr 22fr 22fr;
        }

        .stationMonitoring {
            /* display: grid; */
            gap: 10px;
            padding: 10px;
        }

        .italicized {
            font-style: italic;
            color: black !important;
        }

        @media screen and (max-width: 760px) {
            .grid-container {
                grid-template-areas:
                "one one"
                "two thr"
                "fou fou";
            }
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h1>Seed Delivery Payments Dashboard</h1>
                <h5>Monitor payments of seed deliveries nationwide</h5>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div>
                    <div class="grid-container">
                        <div id="totalDisbursed">
                            <div class="boxes shadow-md">
                                <h4>Total Amount Disbursed</h4>
                                <hr />
                                <h1 style="font-weight: 700;">
                                ₱ {{$totalDisbursed}}
                                </h1>
                            </div>
                        </div>
                        <div id="seedsDelivered">
                            <div class="boxes shadow-md">
                                <h4>
                                Total seeds delivered (relative to target)
                                </h4>
                                <hr />
                                <h1 style="font-weight: 700;"><i class="fa fa-truck" aria-hidden="true"></i> {{$totalDelivered}} ({{$totalDeliveredPercentage}}%)</h1>
                            </div>
                        </div>
                        <div id="paidDeliveries">
                            <div class="boxes shadow-md">
                                <h4>Paid deliveries (relative to actual delivered)</h4>
                                <hr />
                                <h1 style="font-weight: 700;"><i class="fa fa-check-square-o" aria-hidden="true"></i> {{$totalPaidDeliveries}} ({{$totalPaidPercentage}}%)</h1>
                            </div>
                        </div>
                        <div id="statusMonitoring">
                            <div class="boxes shadow-md mini-flex">
                                <div class="mini-content">
                                    <div class="mini-item">
                                        <h3>For Transmittal:</h3>
                                        <h3>{{$forTransmit}}</h3>
                                    </div>
                                    <div class="mini-item">
                                        <h3>Received (CES):</h3>
                                        <h3>{{$receivedCES}}</h3>
                                    </div>
                                    <div class="mini-item">
                                        <h3>For Preparation:</h3>
                                        <h3>{{$forPreparation}}</h3>
                                    </div>
                                    <div class="mini-item">
                                        <h3>For Processing:</h3>
                                        <h3>{{$forProcessing}}</h3>
                                    </div>
                                    <div class="mini-item">
                                        <h3>Paid Deliveries:</h3>
                                        <h3>{{$paidDeliveries}}</h3>
                                    </div>
                                </div>
                                <div class="mini-footer">
                                    <hr />
                                    <h6>
                                    <span class="italicized">The statistics above represents the seed payment status of all RCEF Branch units</span>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stationMonitoring">
                        <hr>
                        <div>
                            <h1>Station Monitoring</h1>
                            <div class="col-md-9" >
                                <h5>Accomplishments & status of each RCEF Branch Unit</h5>
                            </div>
                            <div class="col-md-3 datepicker" style="display: flex; justify-content: end">
                                <input type="text" id="date1" /><button
                                    type="button"
                                    id="submitDate"
                                    class="btn btn-success submit">
                                    Submit
                                </button>
                            </div>
                        </div>

                        
                        <hr>
                        <div id="agusan">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Agusan
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesAgusan">{{$deliveriesAgusan}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidAgusan">{{$paidAgusan}} ({{$paidPercentageAgusan}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedAgusan">₱ {{$totalDisbursedAgusan}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitAgusan">{{$forTransmitAgusan}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="batac">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Batac
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesBatac">{{$deliveriesBatac}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidBatac">{{$paidBatac}} ({{$paidPercentageBatac}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedBatac">₱ {{$totalDisbursedBatac}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitBatac">{{$forTransmitBatac}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="bicol">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Bicol
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesBicol">{{$deliveriesBicol}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidBicol">{{$paidBicol}} ({{$paidPercentageBicol}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedBicol">₱ {{$totalDisbursedBicol}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitBicol">{{$forTransmitBicol}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="CES">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice CES
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesCES">{{$deliveriesCES}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidCES">{{$paidCES}} ({{$paidPercentageCES}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedCES">₱ {{$totalDisbursedCES}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitCES">{{$forTransmitCES}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="isabela">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Isabela
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesIsabela">{{$deliveriesIsabela}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidIsabela">{{$paidIsabela}} ({{$paidPercentageIsabela}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedIsabela">₱ {{$totalDisbursedIsabela}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitIsabela">{{$forTransmitIsabela}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="losBanos">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Los Baños
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesLosBanos">{{$deliveriesLosBanos}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidLosBanos">{{$paidLosBanos}} ({{$paidPercentageLosBanos}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedLosBanos">₱ {{$totalDisbursedLosBanos}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitLosBanos">{{$forTransmitLosBanos}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="midsayap">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Midsayap
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesMidsayap">{{$deliveriesMidsayap}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidMidsayap">{{$paidMidsayap}} ({{$paidPercentageMidsayap}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedMidsayap">₱ {{$totalDisbursedMidsayap}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitMidsayap">{{$forTransmitMidsayap}}</h3>
                                </div>
                            </div>
                        </div>

                        <div id="negros">
                            <div class="boxes shadow-md" style="display:flex">
                                <div class="col-md-3">
                                    <h1 style="font-weight: 700;">
                                    PhilRice Negros
                                    </h1>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total seed deliveries</h4>
                                    <h3 id="deliveriesNegros">{{$deliveriesNegros}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total deliveries paid</h4>
                                    <h3 id="paidNegros">{{$paidNegros}} ({{$paidPercentageNegros}}%)</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>Total amount disbursed</h4>
                                    <h3 id="totalDisbursedNegros">₱ {{$totalDisbursedNegros}}</h3>
                                </div>
                                <div class="col-md-2">
                                    <h4>For transmittal</h4>
                                    <h3 id="forTransmitNegros">{{$forTransmitNegros}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="idleTransactions">
                        <div>
                            <hr>
                            <h1>Idle Transactions (Needs Attention) </h1>
                            <h5>Documents that are idle to any current processes longer than 3 days</h5>
                            <a
                            href="#"
                            target="_blank"
                            data-toggle="modal"
                            data-target="#confirmation_modal"
                            id="notifSettings" class="btn btn-info btn-sm notification"
                            ><h6>Send Notification</h6></a
                            >
                            <table
                            class="table table-hover table-striped table-bordered"
                            id="idle_tbl"
                            style="background-color: white">            
                                <thead style="background-color: white">
                                    <th>IAR Number</th>
                                    <th>Drop-off Point (Location)</th>
                                    <th>Number of Bags</th>
                                    <th>Payments Status</th>
                                </thead>
                                <tbody
                                    class="idleTransactionsData"
                                    style="background-color: white">
                                    @foreach ($idleTransactions as $row)
                                    <tr>
                                    <td>{{ $row["iar_no"] }}</td>
                                    <td>{{ $row["dropOffPoint"] }}</td>
                                    <td>{{ $row["volume"] }}</td>
                                    <td>{{ $row["status"] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <!-- <h5 id="notifSettings"><u>Notification Settings</u></h5> -->
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="confirmation_modal" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 25%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title">
          <span>Send notification(s)?</span><br/>
        </h4>
      </div>
      <div class="modal-body">
        <button id="sendNotification" type="button" class="btn btn-success btn-sm" data-dismiss="modal">
            SEND
          </button>
        <button type="button" class="btn btn-sm" data-dismiss="modal">
            CANCEL
          </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')

<script>
    $("#date1").daterangepicker({
    startDate: moment().startOf('month'),
    endDate: moment().endOf('month')
    });

    $("#sendNotification").on("click", function () {
            var email = $("#email").prop("checked");
            var sms = $("#sms").prop("checked");

            $.ajax({
                    type: 'GET',
                    url: "{{ route('sendAlert') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data){ 
                        console.log(data);
                    }
                });
        });


    $("#submitDate").on("click", function(){
        $date = $("#date1").val();
        $startDate = $date.substring(0,10);
        $endDate = $date.substring(13,23);
        console.log($startDate,$endDate);

        $.ajax({
                    type: 'POST',
                    url: "{{ route('getDatedData') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        startDate: $startDate,
                        endDate: $endDate
                    },
                    success: function(data){ 
                        console.log(data);
                        $('#deliveriesAgusan').text(data.deliveriesAgusan);
                        $('#deliveriesBatac').text(data.deliveriesBatac);
                        $('#deliveriesBicol').text(data.deliveriesBicol);
                        $('#deliveriesCES').text(data.deliveriesCES);
                        $('#deliveriesIsabela').text(data.deliveriesIsabela);
                        $('#deliveriesMidsayap').text(data.deliveriesMidsayap);
                        $('#deliveriesLosBanos').text(data.deliveriesLosBanos);
                        $('#deliveriesNegros').text(data.deliveriesNegros);
                        $('#paidAgusan').text(data.paidAgusan);
                        $('#paidBatac').text(data.paidBatac);
                        $('#paidBicol').text(data.paidBicol);
                        $('#paidCES').text(data.paidCES);
                        $('#paidIsabela').text(data.paidIsabela);
                        $('#paidMidsayap').text(data.paidMidsayap);
                        $('#paidLosBanos').text(data.paidLosBanos);
                        $('#paidNegros').text(data.paidNegros);
                        $('#forTransmitAgusan').text(data.forTransmitAgusan);
                        $('#forTransmitBatac').text(data.forTransmitBatac);
                        $('#forTransmitBicol').text(data.forTransmitBicol);
                        $('#forTransmitCES').text(data.forTransmitCES);
                        $('#forTransmitIsabela').text(data.forTransmitIsabela);
                        $('#forTransmitMidsayap').text(data.forTransmitMidsayap);
                        $('#forTransmitLosBanos').text(data.forTransmitLosBanos);
                        $('#forTransmitNegros').text(data.forTransmitNegros);
                        $('#totalDisbursedAgusan').text(data.totalDisbursedAgusan);
                        $('#totalDisbursedBatac').text(data.totalDisbursedBatac);
                        $('#totalDisbursedBicol').text(data.totalDisbursedBicol);
                        $('#totalDisbursedCES').text(data.totalDisbursedCES);
                        $('#totalDisbursedIsabela').text(data.totalDisbursedIsabela);
                        $('#totalDisbursedMidsayap').text(data.totalDisbursedMidsayap);
                        $('#totalDisbursedLosBanos').text(data.totalDisbursedLosBanos);
                        $('#totalDisbursedNegros').text(data.totalDisbursedNegros);
                    }
                });
    });


</script>
@endpush()
