@extends('layouts.index')

@section('content')
    {{-- CSRF TOKEN --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div>
	
		<div class="col-md-12">
				<div class="alert alert-success" role="alert">
					  Download Online releasing app here (for dry season 2019 seeds): 
					  <a title="download" style="color:#000;font-weight:bold;" href="https://rcef-seed.philrice.gov.ph/rcef_ws2020/public/apps/releasingApp_v1.5.1_online.apk" download>Releasing app - version 1.5.1</a>
				</div>

				<div class="alert alert-info" role="alert">
					  Camera/Scanner not working? Check here: 
					  <a style="color:#000;font-weight:bold;" href="https://rcef-seed.philrice.gov.ph/rcef_ws2020/public/images/chome_flags.PNG" target="_blank">Set your chrome flags!</a>
				</div>
		</div>
        <div class="page-title">
            <div class="title_left">
              <h3>Distribution</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row tile_count">
            @foreach ($pending as $item)
                <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                    <span class="count_top">{{$item['variety']}}</span>
                    <div class="count">{{$item['pending']}}</div>
                    <span class="count_bottom">Available Stocks (bags)</span>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>1. Farmer Profile</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="form-group">
                            <!--<label class="control-label col-md-3">Search Farmer</label>-->
                            <div class="col-md-6">
<!--                                <input type="text" class="form-control" name="search" id="search_farmer" placeholder="Search Farmer" autocomplete="off" autofocus>
                                <p id="empty-message"></p>-->
                                <div id="add_farmer">
                                    <button class='btn btn-primary' onclick='add_farmer()'><i class='fa fa-plus'></i> Add Entry</button>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <table class="table table-bordered table-striped" id="farmer_profile_table">
                            <tbody>
                                <tr>
                                    <td style="width: 40%; text-align: right;">BIRTHDATE</td>
                                    <td id="birthdate" style="width: 60%;"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">SEX</td>
                                    <td id="sex"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">ADDRESS</td>
                                    <td id="address"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">AFFILIATION</td>
                                    <td id="affiliation_name"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">AFFILIATION ACCREDITATION</td>
                                    <td id="affiliation_accreditation"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>2. Seeds</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <input type="hidden" id="farmer_id">

                        <div class="form-group">
                            <label class="control-label col-md-3">Farm Area (ha)</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="farm_area" id="farm_area" placeholder="Farm Area" readonly>
                                <br>
                                <button type="button" name="update_area" id="update_area" class="btn btn-primary">Update Farm Area</button>
                            </div>
                        </div>

                        <div class="form-group" id="farm_area2_input" style="display: none;">
                            <label class="control-label col-md-3">New Farm Area</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="farm_area2" id="farm_area2" placeholder="New Farm Area">
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="control-label col-md-3">Select Variety</label>
                            <div class="col-md-9">
                                @foreach ($available_seeds as $item)
                                    <div class="radio">
                                        <label style="margin-left: 10px;">
                                            <input type="radio" name="preferred_variety" value="{{$item->seedVariety}}" class="flat"> {{$item->seedVariety}}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="button" name="button" id="submit_release" class="btn btn-lg btn-success" style="float: right; margin-top: 10px;"><i class="fa fa-check"></i> Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('releasing.includes.farm_performance')
    @include('releasing.includes.add_rsbsa_control_no_modal')
    @include('releasing.includes.add_farmer_modal')
@endsection
