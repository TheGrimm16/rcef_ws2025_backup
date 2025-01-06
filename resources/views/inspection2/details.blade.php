<?php $inspection_side = "active"; $inspection_profile="active"?>

@extends('layouts.index')

@section('styles')
<link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
    <style>
        .progress {
            overflow: hidden;
            height: 20px;
            margin-bottom: 20px;
            background-color: #d5ccf5;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
            box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        }

        #inspectors tbody tr td{
           vertical-align: inherit; 
        }
    </style>
@endsection

@section('content')

    <div>
        <div class="clearfix"></div>

        @include('layouts.message')

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ strtoupper($inspector->firstName) }} {{ strtoupper($inspector->lastName) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="inspectors">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Coop Accreditation</th>
                                    <th>Delivery Date</th>
                                    <th>Address</th>
                                    <th>Drop Off</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch_tickets as $batch)
                                    <tr>
                                        <td><a href="{{ route('inspector.details.batch', ['idNumber' => $batch->userId, 'batchID' => $batch->batchTicketNumber]) }}"><u>{{ $batch->batchTicketNumber }}</u></a></td>
                                        <td>{{ $batch->coopAccreditation }}</td>
                                        <td>{{ date("F j, Y g:i A", strtotime($batch->deliveryDate)) }}</td>
                                        <td>
                                            {{ $batch->region }},<br>
                                            {{ $batch->province }},<br>
                                            {{ $batch->municipality }}
                                        </td>
                                        <td>{{ $batch->dropOffPoint }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
@endpush