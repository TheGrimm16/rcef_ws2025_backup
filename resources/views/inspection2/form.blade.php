<?php $registry_side = "active"; $registry_form="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>
    ul.parsley-errors-list {
        list-style: none;
        color: red;
        padding-left: 0;
        display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 5px;
        right: 1px;
        width: 20px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #a7acb5;
        color: black;
    }
  </style>
@endsection

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>

        <form action="{{ route('rcef.inspector.save') }}" method="POST" data-parsley-validate>
        {!! csrf_field() !!}
        <div class="clearfix"></div>

            @include('layouts.message')

            <div class="col-md-6 col-sm-12 col-xs-12">
                <!-- delivery details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>1. Delivery Details</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Ticket Number: </label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="ticket_number" id="ticket_number" placeholder="{{ $delivery_details->ticketNumber }}" value="{{ $delivery_details->ticketNumber }}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Variety: </label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="seed_tag" id="seed_tag" placeholder="{{ $delivery_details->seedVariety }} ({{ $delivery_details->seedClass }})" value="{{ $delivery_details->seedVariety }} ({{ $delivery_details->seedClass }})" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Seed Tag: </label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="seed_tag" id="seed_tag" placeholder="{{ $delivery_details->seedTag }}" value="{{ $delivery_details->seedTag }}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Total Weight: </label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="totalWeight" id="totalWeight" placeholder="{{ $delivery_details->totalWeight }}" value="{{ $delivery_details->totalWeight }}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Weight per bag: </label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="weightPerBag" id="weightPerBag" placeholder="{{ $delivery_details->weightPerBag }} per bag" value="{{ $delivery_details->weightPerBag }} per bag" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Delivery Date: </label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="delivery_date" id="delivery_date" placeholder="{{ date("F j, Y", strtotime($delivery_details->deliveryDate)) }}" value="{{ date("F j, Y", strtotime($delivery_details->deliveryDate)) }}" readonly>
                            </div>
                        </div>
                </div>
                </div><br>
                <!-- /delivery details -->

                <!-- seed grower details -->
                <div class="x_panel">
                <div class="x_title">
                    <h2>2. Seed Grower / Coop Details</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left">
                    <br/>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Name: </label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="seed_grower_name" id="seed_grower_name" placeholder="{{ $seed_grower_details->Name }}" value="{{ $seed_grower_details->Name }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Accreditation #: </label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="seed_grower_accreditation" id="seed_grower_accreditation" placeholder="{{ $seed_grower_details->Code_Number }}" value="{{ $seed_grower_details->Code_Number }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Duration: </label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="seed_grower_acc_duration" id="seed_grower_acc_duration" placeholder="{{ date("F, j Y", strtotime($seed_grower_details->accred_start)) }} - {{ date("F, j Y", strtotime($seed_grower_details->accred_end)) }}" value="{{ date("F, j Y", strtotime($seed_grower_details->accred_start)) }} - {{ date("F, j Y", strtotime($seed_grower_details->accred_end)) }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Address: </label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="seed_permanent_address" id="seed_permanent_address" placeholder="{{ $seed_grower_details->address }}" value="{{ $seed_grower_details->address }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Farm Address: </label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="seed_farm_address" id="seed_farm_address" placeholder="{{ $seed_grower_details->Province }}, {{ $seed_grower_details->Municipality }}, {{ $seed_grower_details->Brgy }}" 
                            value="{{ $seed_grower_details->Province }}, {{ $seed_grower_details->Municipality }}, {{ $seed_grower_details->Brgy }}" disabled readonly>
                        </div>
                    </div>        
                </div>
                </div>
                <!-- /seed grower details -->
            </div>


            <div class="col-md-6 col-sm-6 col-xs-6">
                <!-- farm performance -->
                <div class="x_panel">
                    <div class="x_title">
                    <h2>3. Seed Inspector (permanent Staff)</h2>
                    <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                    <br/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">PhilRice Staff <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <select name="philrice_staff" id="philrice_staff" class="form-control" data-parsley-min="1">
                                    <option value="0">Please select a permanent staff</option>
                                    @foreach($philrice_staff_details as $staff_details)
                                        <option value="{{ $staff_details->emp_idno }}">{{ $staff_details->emp_fullname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Branch:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="hris_branch" id="hris_branch" placeholder="Select a staff profile" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Office:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="hris_office" id="hris_office" placeholder="Select a staff profile" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Division:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="hris_division" id="hris_division" placeholder="Select a staff profile" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Unit:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <input type="text" class="form-control" name="hris_unit" id="hris_unit" placeholder="Select a staff profile" readonly>
                            </div>
                        </div>

                        <div class="ln_solid"></div>

                        <!--<div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Duration <span>*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                            <input type="text" class="form-control" name="inspector_duration" id="inspector_duration" data-inputmask="'mask': '99/99/9999 - 99/99/9999'" placeholder="MM/DD/YYYY - MM/DD/YYYY" data-parsley-required>
                            <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                            </div>
                        </div>-->

                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-3">Duration <span>*</span></label>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                        <input type="text" name="inspector_duration" class="form-control" value="01/01/2018 - 01/15/2018" />
                                </div>
                            </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-3">Remarks:</label>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                               <textarea name="pmo_remarks" class="form-control" id="pmo_remarks" rows="5"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-9 col-md-offset-3">
                                <input type="reset" class="btn btn-round btn-danger" value="Reset Fields">
                                <input type="submit" class="btn btn-round btn-success" value="save & Validate">
                            </div>
                        </div>
                    </div>
                    </div><br>
                <!-- /farm performance -->
        </div>
        </form>



    </div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

    <script>
        $("#philrice_staff").select2();
        $('input[name="inspector_duration"]').daterangepicker({
            opens: 'left',
            startDate: moment().startOf('hour'),
            endDate: moment().startOf('hour').add(30, 'days')
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });

        $("#philrice_staff").on("change", function(e){
            var id_number = $(this).val();

            $("#hris_branch").val("loading please wait...");
            $("#hris_office").val("loading please wait...");
            $("#hris_division").val("loading please wait...");
            $("#hris_unit").val("loading please wait...");

            $.ajax({
                type: 'POST',
                url: "{{ route('api.employee.details') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_number: id_number
                },
                success: function(data){
                    $("#hris_branch").val(data['branch']);
                    $("#hris_office").val(data['office']);
                    $("#hris_division").val(data['division']);
                    $("#hris_unit").val(data['unit']);
                }
            });
        });
    </script>
@endpush
