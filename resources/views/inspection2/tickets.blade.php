<?php $inspection_side = "active"; $inspection_profile="active"?>

@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
@endsection

@section('content')

    <div>
        <div class="clearfix"></div>

        @include('layouts.message')

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ strtoupper($inspector->firstName) }} {{ strtoupper($inspector->lastName) }} / {{ $batchTicketNumber }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-hover table-striped table-bordered" id="inspectors">
                            <thead>
                                <tr>
                                    <th>Ticket Number</th>
                                    <th>Seed Tag</th>
                                    <th>Seed Variety</th>
                                    <th>Weight</th>
                                    <th>Weight /Bag</th>
                                    <th>Total Bags</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->ticketNumber }}</td>
                                        <td>{{ $ticket->seedTag }}</td>
                                        <td>{{ $ticket->seedVariety }}</td>
                                        <td>{{ $ticket->totalWeight }}</td>
                                        <td>{{ $ticket->weightPerBag }}</td>
                                        <td>{{ $ticket->totalBagCount }}</td>
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