@extends('layouts.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">

    <style>
        .btn-success[disabled]{
            background: #26B99A;
            border: 1px solid #169F85;
        }
        .buttons{
            display: flex;
            position: relative;
            width: 100%;
            justify-content: end;
        }
        
        .buttons #genFMIS{
            
        }

        .tile_stats_count::before{
            display: none;
        }

        .flex {
            padding: 0.6em;
            display: grid; 
            grid-template-columns: 1fr 1fr 1fr; 
            gap: 1em; 
            grid-template-rows: 1fr;
        }

        .flex .item .x_panel {
            height: 100%;
            border-radius: 0.6em;
        }

        main {
            color: brown;
            display: flex;
            flex-direction: column;
            gap: 1em;
        }

        main .content-wrapper{
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1em;
        }

        main .content-wrapper:nth-of-type(2n){
            display: grid;
            grid-template-columns: 2fr 3fr;
            gap: 1em;
        }

        main .content-wrapper > div {
            position: relative;
            background-color: white;
            border-radius: 1.8em;
            box-shadow: 0 1px 1px rgba(0,0,0,.08), 0 2px 2px rgba(0,0,0,.16);
            padding: 1.6em;
            display: flex;
            flex-direction: column;
            gap: 1.6em;
        }

        main .content-wrapper > div .insight-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        main .content-wrapper > div .insight-header .title {
            font-weight: 900;
            font-size: 1.2em;
        }

        main .content-wrapper > div .insight-header .controls {
            font-size: 1.45em;
            cursor: pointer;
            background: #d8d8d800;
            transition: background 0.1s linear;
        }

        main .content-wrapper > div .insight-content {
            background-color: #d8d8d820;
            overflow-x: scroll;
            border-radius: 1.4em;
            height: 100%;
            padding: 1em;
            position: relative;
            display: flex;
            gap: 1em;
            overflow-x: hidden;
        }

        main .content-wrapper > div .insight-content .insight-card {
            box-sizing: border-box;
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,.04), 0 1px 1px rgba(0,0,0,.08);
            width: 200px;
            height: 100%;
            flex-shrink: 0;
            border-radius: 0.8em;
            position: relative;
            overflow: visible;
        }

        main .content-wrapper > div .insight-content .insight-card .card-content{
            padding: 1em;
            display: flex;
            flex-direction: column;
            gap: 1em;
        }

        main .content-wrapper > div .insight-content .insight-card .card-content > .title{
            font-weight: 900;
        }

        
        main .content-wrapper > div .insight-content .insight-card .card-content > .value{
            font-weight: 900;
            font-size: 1.8em;
        }

        main .content-wrapper > div .insight-content .insight-card .card-content > .breakdown{
            display: flex;
            flex-direction: column;
        }

        main .content-wrapper > div .insight-content .insight-card .card-content > .breakdown > .small-title{
            font-size: 0.8em;
            font-weight: 700;
        }

        main .content-wrapper > div .insight-content .insight-card .card-content > .breakdown > .small-value{
            font-size: 1.2em;
            font-weight: 900;
        }

        main .content-wrapper > div .insight-content .insight-card:nth-last-child(1)::after {
            content: "";
            background-color: #ffffff80;
            position: absolute;
            height: 100%;
            width: 100%;
            border-radius: 0.8em;
            margin-inline-start: 1em;
            transform: translateX(100%);
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div>
    <main style="display: none">
        <div id="content-wrapper" class="content-wrapper">
            <div class="delivery-insights">
                <div class="insight-header">
                    <span class="title">Delivery Insights</span>
                    <div class="controls"><i class="fa fa-external-link" aria-hidden="true"></i></div>
                </div>
                <div class="insight-content">
                    <div class="declared-delivery insight-card">
                        <div class="card-content">
                            <span class="title">Total Delivery Declared by SGC/A (bags)</span>
                            <span class="value">{{number_format($confirmed->total_bag_count)}}</span>
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">RCEF</span>
                                <span class="small-value">{{number_format(isset($confirmed->total_bag_count_RCEF) ? $confirmed->total_bag_count_RCEF : 0)}}</span>
                            </div>
                            <div class="breakdown" style="color: #241571">
                                <span class="small-title">NRP (Inbred)</span>
                                <span class="small-value">{{number_format(isset($confirmed->total_bag_count_NRP) ? $confirmed->total_bag_count_NRP : 0)}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="actual-delivery insight-card">
                        <div class="card-content">
                            <span class="title">Total Actual Delivery (20kg/bag)</span>
                            <span class="value">{{number_format((isset($actual->total_bag_count) ? $actual->total_bag_count : 0) + (isset($transferred_2) ? $transferred_2 : 0) )}}</span>
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">Inspected</span>
                                <span class="small-value">{{number_format((isset($actual->total_bag_count) ? $actual->total_bag_count : 0) - (isset($paymaya_delivery) ? $paymaya_delivery : 0))}}</span>
                            </div>
                            <div class="breakdown" style="color: #241571">
                                <span class="small-title">From Previous Season</span>
                                <span class="small-value">{{number_format(isset($transferred_2) ? $transferred_2 : 0)}}</span>
                            </div>
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">Binhi ePadala</span>
                                <span class="small-value">{{number_format(isset($paymaya_delivery) ? $paymaya_delivery : 0)}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="declared-delivery insight-card">
                        <i class="fa fa-plus-circle" aria-hidden="true" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); color: #00000020; font-size: 2.4em;"></i>
                    </div>
                </div>
            </div>
            <div class="distri-insights">
            <div class="insight-header">
                    <span class="title">Distribution Insights</span>
                    <div class="controls"><i class="fa fa-external-link" aria-hidden="true"></i></div>
                </div>
                <div class="insight-content">
                    <div class="insight-card">
                        <div class="card-content">
                            <span class="title">Distributed Bags</span>
                            <span class="value">{{number_format((isset($distributed->total_bags) ? $distributed->total_bags : 0) + (isset($paymaya_bags) ? $paymaya_bags : 0))}}</span>
                            <div class="breakdown" style="color: #241571">
                                <span class="small-title">Regular Distribution</span>
                                <span class="small-value">{{number_format(isset($distributed->total_bags) ? $distributed->total_bags : 0)}}</span>
                            </div>
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">Binhi ePadala</span>
                                <span class="small-value">{{number_format(isset($paymaya_bags) ? $paymaya_bags : 0)}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="insight-card">
                        <i class="fa fa-plus-circle" aria-hidden="true" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); color: #00000020; font-size: 2.4em;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div id="content-wrapper" class="content-wrapper">
            <div class="delivery-insights">
                <div class="insight-header">
                    <span class="title">Miscellaneous</span>
                    <div class="controls"><i class="fa fa-external-link" aria-hidden="true"></i></div>
                </div>
                <div class="insight-content">
                    <div class="actual-delivery insight-card">
                        <div class="card-content">
                            <span class="title">Estimated Area Planted (ha)</span>
                            <span class="value">{{number_format(isset($distributed->total_claimed_area) ? $distributed->total_claimed_area: 0,'2','.',',')}}</span>
                            <div class="breakdown" style="color: #241571">
                                <span class="small-title">Regular</span>
                                <span class="small-value"></span>
                            </div>
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">Binhi ePadala</span>
                                <span class="small-value"></span>
                            </div>
                        </div>
                    </div>
                    <div class="declared-delivery insight-card">
                        <i class="fa fa-plus-circle" aria-hidden="true" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); color: #00000020; font-size: 2.4em;"></i>
                    </div>
                </div>
            </div>
            <div class="distri-insights">
                <div class="insight-header">
                    <span class="title">Beneficiary Insights</span>
                    <div class="controls"><i class="fa fa-external-link" aria-hidden="true"></i></div>
                </div>
                <div class="insight-content">
                    <div class="insight-card">
                        <div class="card-content">
                            <span class="title">Total Seed Beneficiaries</span>
                            <span class="value">{{number_format((isset($distributed->total_farmers) ? $distributed->total_farmers : 0) + (isset($paymaya_beneficiaries) ? $paymaya_beneficiaries : 0))}}</span>
                            <div class="breakdown" style="color: #241571">
                                <span class="small-title">Regular Distribution</span>
                                <span class="small-value">{{number_format(isset($distributed->total_farmers) ? $distributed->total_farmers : 0)}}</span>
                            </div>
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">Binhi ePadala</span>
                                <span class="small-value">{{number_format(isset($paymaya_beneficiaries) ? $paymaya_beneficiaries : 0)}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="insight-card">
                        <div class="card-content">
                            <span class="title">Seed Beneficiaries' Sex Percentage</span>
                            
                            <div class="breakdown" style="color: #234f1e">
                                <span class="small-title">Female</span>
                                <span class="small-value">{{number_format(isset($femalePercentage) ? $femalePercentage : 0, 2)}}% </span>
                            </div>
                            <div class="breakdown" style="color: #241571">
                                <span class="small-title">Male</span>
                                <span class="small-value">{{number_format(isset($malePercentage) ? $malePercentage : 0, 2)}}% </span>
                            </div>
                        </div>
                    </div>
                    <div class="insight-card">
                        <i class="fa fa-plus-circle" aria-hidden="true" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); color: #00000020; font-size: 2.4em;"></i>
                    </div>
                </div>
            </div>
        </div>
        @if(Auth::user()->roles->first()->name == "rcef-programmer")
            <button onclick="refresh_national();" style="float:right; margin-bottom:0px;" class="btn btn-success btn-sm"> <i class="fa fa-refresh" aria-hidden="true" ></i> REFRESH STATISTICS</button>
        @endif
    </main>
    <div class="row">
         <div class="col-md-12">
                @if($distributed != "N/A")
               @else
               @endif


        </div>
        <div id="id-auth" style="display: none">{{Auth::user()->roles->first()->name}}</div>


            {{-- <div class="col-md-2">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Commitments</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" style="font-size:30px;">{{number_format(isset($confirmed->commitment) ? $confirmed->commitment : 0)}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
     --}}
        

        <!-- Start Flex -->
        <div class="flex" style="">
            <div class="item">
                <div class="x_panel" >
                    <div class="x_title">
                        <h2>Total Delivery Declared by SGC/A-</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count" ><i class="fa fa-check-square-o" style="margin-right:3px;" aria-hidden="true"></i>{{number_format(isset($confirmed->total_bag_count) ? $confirmed->total_bag_count : 0)}}</div>
                            </div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-leaf"> RCEF: {{number_format(isset($confirmed->total_bag_count_RCEF) ? $confirmed->total_bag_count_RCEF : 0)}}</i>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-pagelines">   NRP (Inbred): {{number_format(isset($confirmed->total_bag_count_NRP) ? $confirmed->total_bag_count_NRP : 0)}}</i>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="item">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total actual delivery (20kg/bags)</h2> 
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0;">
                            <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0; ">
                                <div class="count"><i class="fa fa-truck"></i> {{number_format((isset($actual->total_bag_count) ? $actual->total_bag_count : 0) +  (isset($transferred_2) ? $transferred_2 : 0) )}}
                                </div>
                            </div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-eye"> Inspected: {{number_format((isset($actual->total_bag_count) ? $actual->total_bag_count : 0) - (isset($paymaya_delivery) ? $paymaya_delivery : 0))}}</i>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-refresh">   Transferred (PS): {{number_format(isset($transferred_2) ? $transferred_2 : 0)}}</i>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                            <i class="fa fa-cube">   e-Binhi: {{number_format(isset($paymaya_delivery) ? $paymaya_delivery : 0)}} </i> </div>
                                    </div>
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                            <i class="fa fa-mail-forward">   Buffer: {{number_format(isset($buffer) ? $buffer : 0)}} </i> </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
            @if($distributed != "N/A")
                <div class="item">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Distributed (20kg/bags)</h2>
                            @if(Auth::user()->roles->first()->name == "rcef-programmer")
                                <button onclick="refresh_national();" style="float:right; margin-bottom:0px;" class="btn btn-success btn-sm"> <i class="fa fa-refresh" aria-hidden="true" ></i> REFRESH STATISTICS</button>
                            @endif
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content form-horizontal form-label-left">
                            <div class="row tile_count" style="margin: 0">
                                <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                    <div class="count"><i class="fa fa-check-circle"></i> {{number_format((isset($distributed->total_bags) ? $distributed->total_bags : 0) + (isset($paymaya_bags) ? $paymaya_bags : 0))}}</div>
                                </div>

                                <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                    <div class="row ml-3">
                                        <div class="col-md-12 col-sm-4 col-xs-4">
                                            <div class="sub-count" id=""> <i class="fa fa-cubes"> Regular: {{number_format(isset($distributed->total_bags) ? $distributed->total_bags : 0)}}</i>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-sm-4 col-xs-4">
                                            <div class="sub-count" id="">
                                            <i class="fa fa-cube">  e-Binhi: {{number_format(isset($paymaya_bags) ? $paymaya_bags : 0)}} </i> </div>
                                        </div>
                                    
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- End Flex -->

        <div class="flex" style="">
			<div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>Total seed beneficiaries</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-users"></i> {{number_format((isset($distributed->total_farmers) ? $distributed->total_farmers : 0) + (isset($paymaya_beneficiaries) ? $paymaya_beneficiaries : 0))}}</div>
							</div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                         <i class="fa fa-cubes">    Regular: {{number_format(isset($distributed->total_farmers) ? $distributed->total_farmers : 0)}} </i> </div>
                                    </div>

                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                          <i class="fa fa-cube">   e-Binhi: {{number_format(isset($paymaya_beneficiaries) ? $paymaya_beneficiaries : 0)}} </i></div>
                                    </div>

                                </div>
                            </div>
						</div>
					</div>
				</div>
			</div>
			
            <div class="item">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total seed beneficiaries percentage by sex</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-5 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-male"></i> {{number_format(isset($malePercentage) ? $malePercentage : 0, 2)}}% </div>
                            </div>
                            
                            <div class="col-md-5 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-female"></i> {{number_format(isset($femalePercentage) ? $femalePercentage : 0, 2)}}% </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>Estimated area planted (ha)</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-map-marker"></i> {{number_format(isset($distributed->total_claimed_area) ? $distributed->total_claimed_area : 0,'2','.',',')}}</div>
							</div>
						</div>
					</div>
				</div>
			</div> 

        </div>

        @if(Auth::user()->roles->first()->name == "rcef-programmer")
        <div class="flex" style="">
            <div class="item">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Yield</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-pagelines"></i> {{number_format(isset($total_yield) ? $total_yield : 0,2)}}</div>
                            </div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                           <i class="fa fa-leaf">  42 Provinces: - </i> </div>
                                    </div>

                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-leaf"> 15 Provinces: - </i> </div>
                                    </div>
                                    
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-leaf"> 20 Provinces: - </i> </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
			 <div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>Average Landholding</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-group"></i></div>
							</div>
						</div>
					</div>
				</div>
			</div> 
			 <div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>CSS</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-edit"></i></div>
							</div>
						</div>
					</div>
				</div>
			</div> 
        </div>
        @endif

        <div class="col-md-12" >
        <div class="x_panel" style="border-radius: 0.6em;">
            <div class="x_title">
                <!-- <h2> Yield t/ha (42 Provinces)</h2> -->
                <h2>Percent Accomplishment vs Target (All Regions) as of February 2024</h2><br><br>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal form-label-left">
                <div class="row tile_count" style="margin: 0">
                     <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0; text-align: center;">
                        <div class="count"><i class="fa fa-bar-chart"></i>
                            <!-- {{number_format($yield_data->yield,2)}}
                             {{-- @if($distributed->yield_ws2021 <= 0) N/A @else {{$distributed->yield_ws2021}} @endif --}} -->
                             {{ isset($percentage) ? $percentage : 0 }}% out of {{ isset($targetSum) ? $targetSum : 0 }}
                            <button style="float:right; margin-bottom:0px;" id="exportData" class="btn btn-success btn-sm"> <i class="fa fa-download" aria-hidden="true" ></i> REGIONAL ACCOMPLISHMENTS</button>
                            <!-- <form method="POST" enctype="multipart/form-data">
                                       
                                <div class="input-group-append"> 
                                    <button type="submit" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;">Upload Data</button>
                                    <input type="file" class="btn-xs pull-right" id="uploadData" name="file" style="margin-top: 9px;margin-right: 10px;"> 
                                </div>
                            </form> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
		@else
        <div class="item">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Distributed (20kg/bags)</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content form-horizontal form-label-left">
                            <div class="row tile_count" style="margin: 0">
                                <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                    <div class="count"><i class="fa fa-check-circle"></i> {{number_format((isset($distributed->total_bags) ? $distributed->total_bags : 0) + (isset($paymaya_bags) ? $paymaya_bags : 0))}}</div>
                                </div>

                                <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                    <div class="row ml-3">
                                        <div class="col-md-12 col-sm-4 col-xs-4">
                                            <div class="sub-count" id=""> <i class="fa fa-cubes"> Regular: {{number_format(isset($distributed->total_bags) ? $distributed->total_bags : 0)}}</i>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-sm-4 col-xs-4">
                                            <div class="sub-count" id="">
                                            <i class="fa fa-cube">  e-Binhi: {{number_format(isset($paymaya_bags) ? $paymaya_bags : 0)}} </i> </div>
                                        </div>
                                    
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- End Flex -->

        <div class="flex" style="">
			<div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>Total seed beneficiaries</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-users"></i> {{number_format((isset($distributed->total_farmers) ? $distributed->total_farmers : 0) + (isset($paymaya_beneficiaries) ? $paymaya_beneficiaries : 0))}}</div>
							</div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                         <i class="fa fa-cubes">    Regular: {{number_format(isset($distributed->total_farmers) ? $distributed->total_farmers : 0)}} </i> </div>
                                    </div>

                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                          <i class="fa fa-cube">   e-Binhi: {{number_format(isset($paymaya_beneficiaries) ? $paymaya_beneficiaries : 0)}} </i></div>
                                    </div>

                                </div>
                            </div>
						</div>
					</div>
				</div>
			</div>
			
            <div class="item">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total seed beneficiaries percentage by sex</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-5 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-male"></i> {{number_format(isset($malePercentage) ? $malePercentage : 0, 2)}}% </div>
                            </div>
                            
                            <div class="col-md-5 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-female"></i> {{number_format(isset($femalePercentage) ? $femalePercentage : 0, 2)}}% </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>Estimated area planted (ha)</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-map-marker"></i> {{number_format(isset($distributed->total_claimed_area) ? $distributed->total_claimed_area : 0,'2','.',',')}}</div>
							</div>
						</div>
					</div>
				</div>
			</div> 

        </div>

        @if(Auth::user()->roles->first()->name == "rcef-programmer")
        <div class="flex" style="">
            <div class="item">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Total Yield</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content form-horizontal form-label-left">
                        <div class="row tile_count" style="margin: 0">
                            <div class="col-md-7 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
                                <div class="count"><i class="fa fa-pagelines"></i> {{number_format(isset($total_yield) ? $total_yield : 0,2)}}</div>
                            </div>

                            <div class="col-md-5 col-sm-12 col-xs-12" style="padding-bottom: 0;padding-left: 0;">
                                <div class="row ml-3">
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id="">
                                           <i class="fa fa-leaf">  42 Provinces: - </i> </div>
                                    </div>

                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-leaf"> 15 Provinces: - </i> </div>
                                    </div>
                                    
                                    <div class="col-md-12 col-sm-4 col-xs-4">
                                        <div class="sub-count" id=""> <i class="fa fa-leaf"> 20 Provinces: - </i> </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
			 <div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>Average Landholding</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-group"></i></div>
							</div>
						</div>
					</div>
				</div>
			</div> 
			 <div class="item">
				<div class="x_panel">
					<div class="x_title">
						<h2>CSS</h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content form-horizontal form-label-left">
						<div class="row tile_count" style="margin: 0">
							<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0;padding-left: 0;">
								<div class="count"><i class="fa fa-edit"></i></div>
							</div>
						</div>
					</div>
				</div>
			</div> 
        </div>
        @endif

		@endif     

        
		
		
	
		
		
    <div class="row">
        <div class="col-md-12" style="padding: 0 1.6em;">
            <div class="x_panel" style="border-radius: 0.6em;">
                <div class="x_title">
                    <h2 style="margin-top: 10px;">Delivery Schedule</h2>
                    <div class="input-group pull-right" style="width: 500px;">
                        <input type="text" name="date_of_delivery" id="date_of_delivery" class="form-control" value="{{$filter_start}} - {{$filter_end}}" />
                        <div class="input-group-btn">
                            <button class="btn btn-success" id="load_schedule_btn" style="margin:0">LOAD DELIVERIES</button>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" id="delivery_sched_div">
                    @if($delivery_regions != "no_deliveries")
                        @foreach ($delivery_regions as $row)
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h5 class="mb-0" style="margin:0">
                                        <button style="color: #7387a8;text-decoration:none;" class="btn btn-link">
                                            {{$row->region}}
                                        </button>
                                        <a href="#" data-toggle="modal" data-target="#show_region_sched" data-region="{{$row->region}}" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> View Deliveries</a>
                                    </h5>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no_delivery_wrapper" style="width:100%;height:400px;background-color:#d8d8d8;">
                            <img src="{{asset('public/images/no_delivery.png')}}" alt="" style="display: block;margin: auto;height: 300px;padding-top: 25px;">
                            <p style="text-align: center;font-size: 26px;color:black;">No seed deliveries found for the selected dates...</p>
                        </div>
                    @endif

                    <!--<div style="width:100%;height:500px;background-color:#d8d8d8;">
                        <img src="{{asset('public/images/load_del.gif')}}" alt="" style="display: block;margin: auto;width: 100%;height:100%">
                    </div>-->
                </div>
            </div>
        </div>

    <div id="show_region_sched" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width: 1300px; margin: auto; position: relative; top: 10%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="region_sched_title">
                        {REGION}
                    </h4>
                    <!--<span id="coop_accreditation_title">{COOP_ACCREDITATION}</span>-->
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <table class="table table-bordered table-striped" id="delivery_sched_tbl">
                        <thead>
                            @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance")
                            <th> </th>
                            @else
                            <!-- Empty -->
                            @endif
                            <th>Cooperatives</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Batch Ticket Number</th>
                            <th>Dropoff Point</th>
                            <th>Expected</th>
                            <th>Accepted</th>
                            <th>Date of delivery</th>
                            <th>Delivery Status</th>
                            <th>Payment Status</th>
                            <th>Has Delivery Receipt?</th>
                            @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance")
                            <th>Remarks</th>
                            @else
                            <!-- Empty -->
                            @endif
                        </thead>
                    </table>  
                </div>

                <div class="buttons">
                    <button type="button" style="display: none" id="genFMIS" data-toggle='modal' data-target='#show_iar_modal' class="btn btn-success">Generate FMIS Particulars</button>    
                </div>
            </div>
        </div>
    </div>

</div>

<!-- IAR PREVIEW MODAL -->
<div id="show_iar_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>IAR-FMIS Generated Particulars</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success alert-dismissible fade in" role="alert" id="iar_fmis_msg" style="display: none;">
                    <strong><i class="fa fa-check-circle"></i> Success!</strong> IAR-FMIS Particulars copied to clipboard
                </div>
                <textarea name="iar_particulars" id="iar_particulars" cols="30" rows="5" class="form-control" readonly></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="copy_btn" data-clipboard-target="#iar_particulars">Copy to clipboard</button>
            </div>
        </div>
    </div>
</div>
<!-- IAR PREVIEW MODAL -->


<!-- Particulars PREVIEW MODAL -->
<div id="show_particulars_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span>IAR-FMIS Generated Particulars</span><br>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success alert-dismissible fade in" role="alert" id="particulars_fmis_msg" style="display: none;">
                    <strong><i class="fa fa-check-circle"></i> Success!</strong> IAR-FMIS Particulars copied to clipboard
                </div>
                <textarea name="view_particulars" id="view_particulars" cols="30" rows="5" class="form-control" readonly></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="copy_particulars" data-clipboard-target="#view_particulars">Copy to clipboard</button>
            </div>
        </div>
    </div>
</div>
<!-- Particulars PREVIEW MODAL -->

@endsection()

@push('scripts')
@endpush

@push('scripts')
<script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
<script src=" {{ asset('public/js/select2.min.js') }} "></script>
<script src=" {{ asset('public/js/parsely.js') }} "></script>
<script src=" {{ asset('public/js/daterangepicker.js') }} "></script>

<script>
    // checkForChanges();
    function refresh_national(){
       
       var data = "{{$_SERVER['REQUEST_URI']}}";
       data = data.replace("/rcef_", "");
       data = data.replace("/", "");
       var url = "https://rcef-checker.philrice.gov.ph/api/midnight/national/"+data;

        HoldOn.open(holdon_options);
       $.ajax({
            type: 'GET',
            url: url,
            data: {
            },
            success: function(data){
                HoldOn.close();
            }
        });
            
    
    }


    $('#delivery_summary_table').DataTable();
    $("#date_of_delivery").daterangepicker(null,function(a,b,c){
        //console.log(a.toISOString(),b.toISOString(),c)
    });

    $("#load_schedule_btn").on("click", function(e){
        var date_duration = $("#date_of_delivery").val();
        $("#delivery_sched_div").empty();
        var delivery_div = '';

        delivery_div = delivery_div + '<div style="width:100%;height:400px;background-color:#d8d8d8;">';
        delivery_div = delivery_div + '<img src="{{asset('public/images/load_del.gif')}}" alt="" style="display: block;margin: auto;width: 100%;height:100%">';
        delivery_div = delivery_div + '</div>';
        $("#delivery_sched_div").append(delivery_div)

        $.ajax({
            type: 'POST',
            url: "{{ route('dashboard.delivery_schedule.search_regions') }}",
            data: {
                _token: "{{ csrf_token() }}",
                date_duration : date_duration,
            },
            success: function(data){
                delivery_div = '';
                $("#delivery_sched_div").empty();
                if(data == "no_deliveries"){
                    delivery_div = delivery_div + '<div class="no_delivery_wrapper" style="width:100%;height:400px;background-color:#d8d8d8;">';
                    delivery_div = delivery_div + '<img src="{{asset('public/images/no_delivery.png')}}" alt="" style="display: block;margin: auto;height: 300px;padding-top: 25px;">';
                    delivery_div = delivery_div + '<p style="text-align: center;font-size: 26px;color:black;">No seed deliveries found for the selected dates...</p>';
                    delivery_div = delivery_div + '</div>';
                    $("#delivery_sched_div").append(delivery_div)
                }else{
                    jQuery.each(data, function(index, array_value){
                        delivery_div = delivery_div + '<div class="card">';
                        delivery_div = delivery_div + '<div class="card-header" id="headingOne">';
                        delivery_div = delivery_div + '<h5 class="mb-0" style="margin:0">';
                        delivery_div = delivery_div + '<button style="color: #7387a8;text-decoration:none;" class="btn btn-link">';
                        delivery_div = delivery_div + array_value;
                        delivery_div = delivery_div + '</button>';
                        delivery_div = delivery_div + '<a href="#" data-toggle="modal" data-target="#show_region_sched" data-region="'+array_value+'" class="btn btn-warning btn-xs pull-right" style="margin-top: 9px;margin-right: 10px;"><i class="fa fa-eye"></i> View Deliveries</a>';
                        delivery_div = delivery_div + '</h5>';
                        delivery_div = delivery_div + '</div>';
                        delivery_div = delivery_div + '</div>';
                    });
                    $("#delivery_sched_div").append(delivery_div)
                }
            }
        });
    }); 

    $('#show_region_sched').on('show.bs.modal', function (e) {
        var date_duration = $("#date_of_delivery").val();
        var region = $(e.relatedTarget).data('region');
        var id_auth = $("#id-auth").text();


        $("#region_sched_title").empty().html("Seed Deliveries for the region of: "+region);

        if(id_auth === "rcef-programmer" || id_auth === "rcef-finance"){
            $("#delivery_sched_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('dashboard.delivery_schedule.custom') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        date_duration : date_duration,
                        region: region
                    }
                },
                "columns":[
                    {data: 'action'},
                    {data: 'coop_name'},    
                    {data: 'province'},
                    {data: 'municipality'},
                    {data: 'batchTicketNumber'},
                    {data: 'dropOffPoint'},
                    {data: 'expected_delivery_volume'},
                    {data: 'actual_delivery_volume'},
                    {data: 'delivery_date'},
                    {data: 'status'},
                    {data: 'paymentStatus'},
                    {data: 'deliveryReceipt'},
                    {data: 'particulars'},
                ]
            });
        }else{
            $("#delivery_sched_tbl").DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searchHighlight": true,
                "processing": true,
                "serverSide": true,
                "orderMulti": true,
                "order": [],
                "ajax": {
                    "url": "{{ route('dashboard.delivery_schedule.custom') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}",
                        date_duration : date_duration,
                        region: region
                    }
                },
                "columns":[
                    {data: 'coop_name'},    
                    {data: 'province'},
                    {data: 'municipality'},
                    {data: 'batchTicketNumber'},
                    {data: 'dropOffPoint'},
                    {data: 'expected_delivery_volume'},
                    {data: 'actual_delivery_volume'},
                    {data: 'delivery_date'},
                    {data: 'status'},
                    {data: 'paymentStatus'},
                    {data: 'deliveryReceipt'}
                ]
            });
        }
    });

    // $("#region_select").on("change", function(e){
    //     var region = $(this).val();

    //     $("#province_select").empty().append("<option value='0'>Loading provinces please wait...</option>");
    //     $.ajax({
    //         type: 'POST',
    //         url: "{{ route('delivery_summary.provinces') }}",
    //         data: {
    //             _token: "{{ csrf_token() }}",
    //             region: region
    //         },
    //         success: function(data){
    //             $("#province_select").empty().append("<option value='0'>Please select a province</option>");
    //             $("#province_select").append(data);
    //         }
    //     });
    // });

    // $("#province_select").on("change", function(e){
    //     var region = $("#region_select").val();
    //     var province = $(this).val();

    //     $("#municipality_select").empty().append("<option value='0'>Loading municipalities please wait...</option>");
    //     $.ajax({
    //         type: 'POST',
    //         url: "{{ route('delivery_summary.municipalities') }}",
    //         data: {
    //             _token: "{{ csrf_token() }}",
    //             region: region,
    //             province: province
    //         },
    //         success: function(data){
    //             $("#municipality_select").empty().append("<option value='0'>Please select a municipality</option>");
    //             $("#municipality_select").append(data);
    //         }
    //     });
    // });


    
    // $("#refresh_national_data").on("click", function(e){
    //         alert("National Data is now processing, Please Wait... \n Estimated time: 10 minutes");
    //        $.ajax({
    //         type: 'POST',
    //         url: "{{ route('rcef.national_refresh') }}",
    //         data: {
    //             _token: "{{ csrf_token() }}",
    //         },
    //         success: function(data){
               
    //         }
    //     });

           
    //        $("#refresh_national_data").attr("disabled", "true");
    //        window.location.replace("{{route('dashboard.index')}}")

    // });


    // $("#generate_delivery_btn").on("click", function(e){
    //     var region = $("#region_select").val();
    //     var province = $("#province_select").val();
    //     var municipality = $("#municipality_select").val();

    //     if(region != '' && region != '0' &&
    //        province != '' && province != '0' &&
    //        municipality != '' && municipality != '0'){
    //             $('#delivery_summary_table').DataTable().clear();
    //             $("#delivery_summary_table").DataTable({
    //                 "bDestroy": true,
    //                 "autoWidth": false,
    //                 "searchHighlight": true,
    //                 "processing": true,
    //                 "serverSide": true,
    //                 "orderMulti": true,
    //                 "order": [],
    //                 "ajax": {
    //                     "url": "{{ route('delivery_summary.datatable') }}",
    //                     "dataType": "json",
    //                     "type": "POST",
    //                     "data":{
    //                         "_token": "{{ csrf_token() }}",
    //                         region: region,
    //                         province: province,
    //                         municipality: municipality
    //                     }
    //                 },
    //                 "columns":[
    //                     {data: 'dropoff_point', name: 'dropoff_point'},
    //                     {data: 'confirmed_delivery', name: 'confirmed_delivery'},
    //                     {data: 'actual_delivery', name: 'actual_delivery'},
    //                 ]
    //             });
    //     }else{
    //         alert("Please select a region, province, and municipality");
    //     }        
    // });

    $("#region_select").on("change", function(e){
        var region = $(this).val();

        $("#province_select").empty().append("<option value='0'>Loading provinces please wait...</option>");
        $.ajax({
            type: 'POST',
            url: "{{ route('delivery_summary.provinces') }}",
            data: {
                _token: "{{ csrf_token() }}",
                region: region
            },
            success: function(data){
                $("#province_select").empty().append("<option value='0'>Please select a province</option>");
                $("#province_select").append(data);
            }
        });
    });

    $("#province_select").on("change", function(e){
       
       var region = $("#region_select").val();
       var province = $(this).val();
       
       $("#month_select").empty().append("<option value='0'>Loading month please wait...</option>");
       $.ajax({
           type: 'POST', 
           url: "{{ route('delivery_summary.month') }}",
           data: {
               _token: "{{ csrf_token() }}",
               region: region,
               province: province,

           },
           success: function(data){
               $("#month_select").empty().append("<option value='0'>Please select a Month</option>");
               $("#month_select").append(data);
           }
       });
   });

   $("#refresh_national_data").on("click", function(e){
            alert("National Data is now processing, Please Wait... \n Estimated time: 10 minutes");
           $.ajax({
            type: 'POST',
            url: "{{ route('rcef.national_refresh') }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(data){
               
            }
        });

           
           $("#refresh_national_data").attr("disabled", "true");
           window.location.replace("{{route('dashboard.index')}}")

    });


    $("#generate_delivery_btn").on("click", function(e){
        var region = $("#region_select").val();
        var province = $("#province_select").val();
        var month = $("#month_select").val();
        // var municipality = $("#municipality_select").val();

        if(region != '' && region != '0' &&
           province != '' && province != '0' &&
           month != '' && month != '0')
        //    &&
        //    municipality != '' && municipality != '0')
           {
                $('#delivery_summary_table').DataTable().clear();
                $("#delivery_summary_table").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,    
                    "searchHighlight": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "ajax": {
                        "url": "{{ route('delivery_summary.datatable') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}",
                            region: region,
                            province: province,
                            month: month
                            // municipality: municipality


                        }
                    },
                    "columns":[
                        // {data: 'dropoff_point', name: 'dropoff_point'},
                        // {data: 'confirmed_delivery', name: 'confirmed_delivery'},
                        {data: 'region', name: 'region'},
                        {data: 'province', name: 'province'},
                        {data: 'totalBagCount', name: 'totalBagCount'},
                        {data: 'targetVolume', name: 'targetVolume'},
                        // {data: 'pro_code', name: 'pro_code'},
                        // {data: 'municipality', name: 'municipality'},
                        {data: 'percent', name: 'percent'}
                        
                    ]
                });
        }else{
            alert("Please select a region, province and month");
        }        
    });

    $("#exportData").on("click", function(e){
    var reg = $('#region').val();
    var url = '{{ route("exportData", ["reg" => ":reg"]) }}';
    url = url.replace(':reg', reg);
    window.open(url);
    });

    // function checkForChanges(){
    //     setInterval(() => {
    //         var checkedVals = $('.radioCoop:checkbox:checked').map(function() {
    //             return this.dataset.deliv;
    //         }).get();
    //     // console.log(checkedVals.length);
    //     if(checkedVals.length == 0){
    //         $("#genFMIS").css('display', 'none');
    //     }else{
    //         $("#genFMIS").css('display', 'block');
    //         console.log(checkedVals);
    //     }
    //     }, 500);
    // }

    $(document).on('change', '.radioCoop:checkbox', function(e) {
        var checkedVals = $('.radioCoop:checkbox:checked').map(function() {
                return this.dataset.deliv;
            }).get();
        var deltype = $('.radioCoop:checkbox:checked').map(function() {
                return this.dataset.deltype;
            }).get();
        var coop = $('.radioCoop:checkbox:checked').map(function() {
                return this.dataset.coop;
            }).get();
        var province = $('.radioCoop:checkbox:checked').map(function() {
                return this.dataset.province;
            }).get();
        if(checkedVals.length == 0){
            $("#genFMIS").css('display', 'none');
        }else if(checkedVals.length == 1){
            $("#genFMIS").css('display', 'block');
        }else{
            console.log(deltype[0],coop[0],province[0]);
            for(i=1; i<checkedVals.length;i++){
                if(deltype[0]==deltype[i] || coop[0]==coop[i] || province[0]==province[i]){
                    if(deltype[0]!=deltype[i]){
                        alert('Please choose only deliveries of the same delivery type.');
                        $(this).prop('checked', false);
                    }
                    else if(coop[0]!=coop[i]){
                        alert('Please choose only deliveries of the same cooperative.');
                        $(this).prop('checked', false);
                    }
                    else if(province[0]!=province[i]){
                        alert('Please choose only deliveries of the same province.');
                        $(this).prop('checked', false);
                    }
                }
            }
            
        }

    });



    $("#show_iar_modal").on('show.bs.modal', function (e) {
        var checkedVals = $('.radioCoop:checkbox:checked').map(function() {
                return this.dataset.deliv;
            }).get();
        console.log(checkedVals);

        if(checkedVals.length>0){
            $("#iar_particulars").empty().val("generating particulars...");
            $("#iar_fmis_msg").css("display", "none");
    
            $.ajax({
                type: 'POST',
                url: "{{ route('delivery_summary.particulars') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    checkedVals: checkedVals
                },
                success: function(data){
                    $("#iar_particulars").empty().val(data);
                    $('.radioCoop:checkbox:checked').prop('disabled', true);
                }
            });
        }
        else{
            e.preventDefault();
            alert("No deliveries are selected. Please select at least one delivery.");
        }
        
    });

    $("#show_iar_modal").on('hidden.bs.modal', function (e) {
        $("#show_region_sched").modal('hide');
     });
    
    document.getElementById("copy_btn").addEventListener("click", function() {
        var copy_status = copyToClipboard(document.getElementById("iar_particulars"));
        if(copy_status == true){
            $("#iar_fmis_msg").css("display", "block");
        }
    });
    
    document.getElementById("copy_particulars").addEventListener("click", function() {
        var copy_status = copyToClipboard(document.getElementById("view_particulars"));
        if(copy_status == true){
            $("#particulars_fmis_msg").css("display", "block");
        }
    });

    $("#show_particulars_modal").on('show.bs.modal', function (e) {
            var batch = $(e.relatedTarget).data('batch');
            $("#view_particulars").empty().val("generating particulars...");
            $("#particulars_fmis_msg").css("display", "none");

            console.log(batch);
    
            $.ajax({
                type: 'POST',
                url: "{{ route('delivery_summary.viewParticulars') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    batch: batch,
                },
                success: function(data){
                    $("#view_particulars").empty().val(data);
                    $('.radioCoop:checkbox:checked').prop('disabled', true);
                }
            });
        
    });
    
    function copyToClipboard(elem) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
        var origSelectionStart, origSelectionEnd;
        if (isInput) {
            // can just use the original source element for the selection and copy
            target = elem;
            origSelectionStart = elem.selectionStart;
            origSelectionEnd = elem.selectionEnd;
        } else {
            // must use a temporary form element for the selection and copy
            target = document.getElementById(targetId);
            if (!target) {
                var target = document.createElement("textarea");
                target.style.position = "absolute";
                target.style.left = "-9999px";
                target.style.top = "0";
                target.id = targetId;
                document.body.appendChild(target);
            }
            target.textContent = elem.textContent;
        }
        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);
        
        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch(e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }
        
        if (isInput) {
            // restore prior selection
            elem.setSelectionRange(origSelectionStart, origSelectionEnd);
        } else {
            // clear temporary content
            target.textContent = "";
        }
        return succeed;
    }

</script>
@endpush