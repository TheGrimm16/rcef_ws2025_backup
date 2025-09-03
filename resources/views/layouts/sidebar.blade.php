<style>
	@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');

	body{
		--color-mode-bg: #f7f7f7;
		--color-mode-font: #000;
		--color-mode-accent: rgb(80, 60, 5);
	}

	.profile_pic_xmas::after{
		content: '';
		position: absolute;
		width: 100px;
		height: 60px;
		top: 5rem;
		left: -1rem;
		background: url('https://i.ibb.co/K5zxW7g/pngwing-com.png');
		background-size: contain;
		background-repeat: no-repeat;
		transform: rotate(5deg);
		opacity: 1;
	}

	.group_title{
		position: relative;
		z-index: 1;
		/* text-shadow: 5px 5px 1rem rgb(121, 121, 121); */
	}

	.group_title::after{
		/* content: ''; */
		position: absolute;
		z-index: 0;
		width: 100%;
		height: 30px;
		left: 0;
		top: -250%;
		background: url('https://i.ibb.co/gjXh8Fk/canva-MAEu0-HBrm-Fo.png');
		background-size: 100% 100%;
		background-repeat: repeat-x;
		-webkit-filter: drop-shadow(0px 3px 1px #222);
		filter: drop-shadow(0px 3px 1px #222);
		pointer-events: none;
		opacity: 1;
	}

	.snow_maybe{
		display: none;
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		top: -5vw;
		background-color: rgba(147, 171, 255, 0.042);
		z-index: 9999999;
		pointer-events: none;
		backdrop-filter: contrast(90%) saturate(110%);
		--snow-iteration: 5;
		opacity: 1;
		animation: fadeout 15s 20s forwards linear;
	}

	.snowfall{
		aspect-ratio: 1;
		background: white;
		position: absolute;
		border-radius: 100vw;
		transform: translateY(-100%);
	}

	.snowfall1{
		width: 10px;
		top: 0;
		left: 10%;
		animation: fallDown 5s linear var(--snow-iteration);
	}
	.snowfall2{
		width: 10px;
		top: 0;
		left: 34%;
		animation: fallDownLeft 5s 1s linear var(--snow-iteration);
	}
	.snowfall3{
		width: 10px;
		top: 0;
		left: 64%;
		animation: fallDownLeft 4s 2s linear var(--snow-iteration);
	}
	.snowfall4{
		width: 10px;
		top: 0;
		left: 91%;
		animation: fallDown 5s 0s linear var(--snow-iteration);
	}
	.snowfall5{
		width: 5px;
		top: 0;
		left: 98%;
		animation: fallDownLeft 3s 2s linear var(--snow-iteration);
	}
	.snowfall6{
		width: 5px;
		top: 0;
		left: 78%;
		animation: fallDownLeft 4s 1s linear var(--snow-iteration);
	}
	.snowfall7{
		width: 5px;
		top: 0;
		left: 48%;
		animation: fallDownLeft 6s 1s linear var(--snow-iteration);
	}
	.snowfall8{
		width: 5px;
		top: 0;
		left: 18%;
		animation: fallDown 7s 0s linear var(--snow-iteration);
	}
	.snowfall9{
		width: 30px;
		top: 0;
		left: 4%;
		animation: fallDownLeft 3s 2s linear var(--snow-iteration);
	}
	.snowfall10{
		width: 30px;
		top: 0;
		left: 44%;
		animation: fallDownLeft 7s 2s linear var(--snow-iteration);
	}
	.snowfall11{
		width: 30px;
		top: 0;
		left: 88%;
		animation: fallDown 3s 1s linear var(--snow-iteration);
	}
	.snowfall11{
		width: 30px;
		top: 0;
		left: 101%;
		animation: fallDown 8s 1s linear var(--snow-iteration);
	}

	.near{
		filter: blur(5px);
	}
	.middle{
		filter: blur(3px);
	}
	.far{
		filter: blur(1px);
	}

	.global_snow_container{
		animation: leftRight 8s ease-in-out var(--snow-iteration);
	}

	@keyframes fadeOut{
		0%{
			opacity: 1;
		}
		100%{
			opacity: 0;
		}
	}

	@keyframes fallDown{
		0%{
			transform: translateY(-5vw);
		}
		100%{
			transform: translateY(100vw);
		}
	}

	@keyframes fallDownLeft{
		0%{
			transform: translateY(-5vw) translateX(0vw);
		}
		100%{
			transform: translateY(100vw) translateX(-10vw);
		}
	}

	@keyframes leftRight{
		0%, 100%{
			transform: translateX(0);
		}
		30%{
			transform: translateX(-3vw);
		}
		60%{
			transform: translateX(4vw);
		}
	}

	.global_navbar{
		font-size: 12px;
		font-family: Poppins;
		height: 100%;
		/* width: !important; */
		overflow-y: auto;
	}

	.main_side_nav{
		max-height: 65vh;
		min-height: max-content;
		overflow-y: auto;
	}

	.menu_section > ul > li{
		-webkit-box-sizing: border-box!important;
		-moz-box-sizing: border-box!important;
		box-sizing: border-box!important;
	}

	.menu_section > ul > li > *{
		padding: 0.8rem 1.2rem;
	}

	.menu_section > ul > li{
		transition: all 0.2s ease-in-out;
		border-radius: 1rem;
	}

	.menu_section > ul > li:hover{
		background: rgba(30, 183, 158, 0.642);
	}

	.menu_section a{
		color: var(--color-mode-font)!important;
		font-weight: 500;
	}

	.menu_section h2{
		color: var(--color-mode-font);
	}

	.menu_section{
		background: var(--color-mode-bg);
		margin: 0.2rem 1rem;
		border-radius: 2rem;
		padding: 1rem 1rem;
	}

	.menu_section > h3{
		font-weight: 700;
		letter-spacing: 0.2rem;
		color: var(--color-mode-accent);
		text-align: center;
		text-shadow: none;
		margin: 1rem 0rem;
		padding: 0;
	}

	.menu_section .active > a{
		background: rgba(0, 0, 0, 0.038)!important;
		border-radius: 1rem 0rem 0rem 1rem;
	}

	.sub-menu:hover > *{
		color: var(--color-mode-bg)!important;
	}

	.profile{
		margin: 1rem 1rem 0rem 1rem;
		background: var(--color-mode-bg);
		border-radius: 2rem;
		padding: 0.3rem 0.2rem;
	}

	.profile_info{
		transform: translateY(-15%);
	}

	.profile_info > h2{
		font-weight: 700;
		color: var(--color-mode-font);
	}

	/* 
		Toggle Design
	*/

	.switch {
	position: relative;
	display: inline-block;
	width: 2.4rem;
	height: 1.6rem;
	}

	/* Hide default HTML checkbox */
	.switch input {
	opacity: 0;
	width: 0;
	height: 0;
	}

	/* The slider */
	.slider {
		display: none;
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	-webkit-transition: .4s;
	transition: .4s;
	}

	.slider:before {
		display: none;
	position: absolute;
	content: "";
	height: 1.4rem;
	width: 1.4rem;
	left: 1px;
	bottom: 1px;
	background-color: white;
	-webkit-transition: .4s;
	transition: .4s;
	}

	input:checked + .slider {
	background-color: #2196F3;
	}

	input:focus + .slider {
	box-shadow: 0 0 1px #2196F3;
	}

	input:checked + .slider:before {
	-webkit-transform: translateX(0.8rem);
	-ms-transform: translateX(0.8rem);
	transform: translateX(0.8rem);
	}

	/* Rounded sliders */
	.slider.round {
	border-radius: 34px;
	}

	.slider.round:before {
	border-radius: 50%;
	}

	.forDarkModeLabel *{
		color: var(--color-mode-font)!important;
	}

	/* 
		End Toggle Design
	*/

	.current-page > a{
		font-weight: 600!important;
	}

	.dark_mode{
		--color-mode-bg: rgb(30, 30, 30);
		--color-mode-font: rgb(228, 228, 228);
		--color-mode-accent: rgb(157, 115, 0);
	}

	@media only screen and (max-width: 500px) {
		.active .sub_menu .child_menu{
			display: block!important;
		}
	}

</style>
<div class="snow_maybe fade_out_exit">
	<div class="global_snow_container">
	<div class="snowfall snowfall1 middle">

	</div>
	<div class="snowfall snowfall2 middle">

	</div>
	<div class="snowfall snowfall3 middle">

	</div>
	<div class="snowfall snowfall4 middle">

	</div>
	<div class="snowfall snowfall5 far">

	</div>
	<div class="snowfall snowfall6 far">

	</div>
	<div class="snowfall snowfall7 far">

	</div>
	<div class="snowfall snowfall8 far">

	</div>
	<div class="snowfall snowfall9 near">

	</div>
	<div class="snowfall snowfall10 near">

	</div>
	<div class="snowfall snowfall11 near">

	</div>
	<div class="snowfall snowfall12 near">

	</div>
	</div>
</div>
<div class="col-md-3 left_col global_navbar">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="{{route('dashboard.index')}}" class="site_title" style="height: auto;    text-align: center;">
                <div class="col-md-6" style="font-size: 16px;padding: 0;font-weight: bold;">
                    
<?php 

				$curr =  basename(getcwd());
			       $curr = str_replace("rcef_", "", $curr);
                      if(strlen($curr) == 6){
                        $curYr =  intval(substr($curr, 2, 4));
                        $curSeason = strtoupper(substr($curr, 0, 2));
                        $currentSeason = $curSeason.$curYr;
                      }else{
                        $currentSeason = "";
                      }

					 
?>
				RCEF {{$currentSeason}}
                </div>
                <div class="col-md-6" style="    margin: 0;    padding: 0;">
                    <img src="{{ asset('public/images/rcef_LOGO_ws2025.PNG') }}" alt="..." class="img-circle profile_img" style="width: 4vw !important;    border-radius: 7%;    text-align: center;margin-right: 2vw;    margin-top: .2vw;">
                </div>
            </a>
        </div>

        <div class="clearfix"></div>

        <!-- menu profile quick info -->
        <div class="profile clearfix">
            @if(date('m') >= 9)
				<div class="profile_pic profile_pic_xmas">
			@else
				<div class="profile_pic">	
			@endif
                @if(Auth::user()->sex == "M")
				  <img src="{{ asset('public/images/male_farmer.png') }}" alt="male_avatar" class="img-circle profile_img">
				@else
				  <img src="{{ asset('public/images/female_farmer.png') }}" alt="female_avatar" class="img-circle profile_img">
				@endif
            </div>
            <div class="profile_info">
                <span>Welcome,</span>
                <h2>{{Auth::user()->firstName}} {{Auth::user()->lastName}}</h2>
            </div>
        </div>
        <!-- /menu profile quick info -->

        <br />

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
		
		
		@if(Auth::user()->roles->first()->name == "preReg-officer" )
        	<!-- if technodemo -->
        	<div class="menu_section">
                <h3>GENERAL</h3>
                <ul class="nav side-menu">
                    <li><a href="{{route('pre_reg.view_farmer')}}"><i class="fa fa-user"></i> Update Farmer Information </a></li>
					
				{{-- joe --}}
                </ul>
            </div>

					<?php
if($_SERVER['REQUEST_URI']!="/rcef_ds2024/pre_reg/view_farmer"){
	header('Location: ' . route('pre_reg.view_farmer'), true, 303);
	die();
 }

					?>

			
        	 @elseif(Auth::user()->roles->first()->name == "techno_demo_encoder" || Auth::user()->roles->first()->name == "techno_demo_officer")
        	<!-- if technodemo -->
        	<div class="menu_section">
				<h3 class="group_title">GENERAL</h3>
                <ul class="nav side-menu">
                    <li><a href="{{route('palaysikatan.dashboard.index')}}"><i class="fa fa-user"></i> Dashboard </a></li>
					<li><a href="{{route('palaysikatan.farmers')}}"><i class="fa fa-list-alt"></i> Palaysikatan </a></li>
					
					@if( Auth::user()->username == "justine.ragos" || Auth::user()->roles->first()->name == "techno_demo_officer")
					<li><a href="{{route('palaysikatan.tdo-data')}}"><i class="fa fa-list"></i> TDO List </a></li>		
					<li><a href="{{route('palaysikatan.tdo-data-encoded')}}"><i class="fa fa-list"></i> TDO Monitoring</a></li>		
					<li><a href="{{route('palaysikatan.calendar')}}"><i class="fa fa-calendar"></i> Calendar </a></li>				
					@endif
					
					
<!--                    @if (Auth::user()->can('role-list'))
                    <li><a href="{{route('roles.index')}}"><i class="fa fa-user"></i> Roles </a></li>
                    @endif
                    @if (Auth::user()->can('permission-list'))
                    <li><a href="{{route('permissions.index')}}"><i class="fa fa-user"></i> Permissions </a></li>
                    @endif-->
                </ul>
            </div>


			{{-- start MOET DEV  --}}
			@elseif(Auth::user()->roles->first()->name == "moet_dev")
			<div class="menu_section">
				<h3>GENERAL</h3>
                <ul class="nav side-menu">
					<li><a href=" {{url('/moet_dev')}} "><i class="fa fa-database"></i> DB CHECKER</a></li>
			
				</ul>
			</div>
			{{-- end MOET DEV  ---}}

			{{-- start sidebar DEV  --}}
			@elseif(Auth::user()->username ==  "dev.xample")
			<div class="menu_section main_side_nav">
                <h3 class="group_title">General</h3>
                <ul class="nav side-menu">
           
					@if(Auth::user()->roles->first()->name == "system-encoder")
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						@if (Auth::user()->province)
							<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution </a></li>
							<li><a href=" {{route('farmer.id.home')}} "><i class="fa fa-qrcode"></i> Farmer ID </a></li>
							{{-- <li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li> --}}
						@endif
						
					@elseif(Auth::user()->roles->first()->name == "da-icts")
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						<li><a href="{{route('dashboard.delivery_summary')}}"><i class="fa fa-dashboard"></i> Delivery Summary </a></li>
						<li><a><i class="fa fa-database"></i> Seed Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display:block">
							  {{-- <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li> --}}
							  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
							</ul>
						</li>
                    @elseif(Auth::user()->roles->first()->name == "coop-operator")
					  <!-- <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Coop Dashboard </a></li> -->
					  <li><a href="{{route('coop_operator.deliveries')}}"><i class="fa fa-truck"></i> Coop Deliveries </a></li>
					  <li><a href="{{route('coop_operator.sg_enrollment')}}"><i class="fa fa-legal"></i> SG Enrollment</a></li>
					  <li><a href="{{route('coop_operator.sg_matrix')}}"><i class="fa fa-cubes"></i> SG Matrix</a></li>
					  <li><a href="{{route('coop_operator.report')}}"><i class="fa fa-book"></i> Coop Report</a></li>
					 
					@elseif(Auth::user()->roles->first()->name == "sed-caller")
					  <li><a href="{{url('/caller/farmers')}}"><i class="fa fa-users"></i> Farmers </a></li>  
					@elseif(Auth::user()->roles->first()->name == "sed-caller-manager")
					  <li><a href="{{url('/sed/dashboard')}}"><i class="fa fa-calendar"></i> Dashboard </a></li>  
					  <li><a href="{{url('/sed/farmers')}}"><i class="fa fa-check"></i> Verified Farmers </a></li>  
					  <li><a href="{{url('/sed/manage/farmer')}}"><i class="fa fa-list"></i> Farmer List </a></li>  
					  <li><a href="{{url('/sed/manage')}}"><i class="fa fa-users"></i> SED Users </a></li>  
					@elseif(Auth::user()->roles->first()->name == "it-sra")
					<li><a href="{{url('sra/paymaya')}}"><i class="fa fa-calendar"></i> Scheduler </a></li>  
					<li><a href="{{url('sra/scheduled/farmers')}}"><i class="fa fa-users" aria-hidden="true"></i> Scheduled Farmers </a></li> 
					<li><a href="{{url('sra/dop')}}"><i class="fa fa-map"></i> Drop Off Points </a></li>
					<li><a href="{{url('sra/utility')}}"><i class="fa fa-cogs" aria-hidden="true"></i> E-Binhi Utility </a></li>    
					@elseif(Auth::user()->roles->first()->name == "bpi-nsqcs" || Auth::user()->username == "rbombase.1" || Auth::user()->roles->first()->name == "dro")
					  <li><a href="{{route('coop.rla.pmo')}}"><i class="fa fa-eye"></i> Monitor RLA </a></li>
					  <li><a href="{{route('coop.rla.bpi')}}"><i class="fa fa-plus-circle"></i> ADD RLA </a></li>
					@else
						
						<!-- OTHER USERS -->
						<li><a href=" {{url('/')}} "><i class="fa fa-home"></i> Home Page</a></li>
						<li><a><i class="fa fa-bar-chart"></i> Analytics<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<li><a href="{{route('analytics.home')}}"> Analytics Summary</a></li>
								<li><a href="{{route('yieldCount.home')}}"> Yield Report</a></li>
								<li><a href="{{route('palaysikatan.dashboard.index')}}"> Palaysikatan Dashboard </a></li>
								<li><a href="{{route('payment_dashboard.home')}}">Payment Dashboard</a></li>
								<!-- <li><a href="{{route('coop.dashboard')}}">Coop Dashboard</a></li> -->
								<li><a href="{{route('station_report.home')}}">Station Dashboard </a></li>
								<li><a href="{{route('planting_calendar_index')}}">Seed Variety Performance Dashboard</a></li>
								@endif
								<li><a href="{{route('dashboard.gad.view')}}">GAD Dashboard</a></li>
								<li><a href="{{route('cssDashboard')}}">CSS Dashboard</a></li>
								<li><a href="#" data-toggle="modal" data-target="#noticePage404">KP Kits/IEC Distribution</a></li>
								<li><a href="#" data-toggle="modal" data-target="#noticePage404">RCEF KYC (MAP)</a></li>
								<li><a href="{{url('/DeliveryDashboard')}}">Delivery Dashboard </a></li>
								<li><a href="{{route('KPDistribution_index')}}">KP-IEC Distribution Dashboard</a></li>
								{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer") --}}
								{{-- @endif --}}
								{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer") --}}
								{{-- @endif --}}
							</ul>
						</li>

						<li><a><i class="fa fa-flag"></i> Projects<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a style="display: flex; align-items: center; justify-content: space-between;"><span style="display: flex; align-items: center;">Binhi e-Padala</span><span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										<li class="sub_menu"><a href="{{route('paymaya.beneficiary_report')}}">Beneficiary Reports</a></li>
										<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Payment Forms/Monitoring</a></li>
									</ul>
								</li>
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<li><a style="display: flex; align-items: center; justify-content: space-between;"><span style="display: flex; align-items: center;">PalaySikatan</span><span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Data Collection Form</a></li>
										<li><a href="{{route('palaysikatan.farmers')}}">Data Monitoring Modules</a></li>
									</ul>
								</li>
								<li><a>MOET<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  <li><a href="{{route('moet.web.view.farmer')}}">Farmer Profiles</a></li>
									  <li><a href="{{route('moet.web.map_view.farmer')}}">Soil Fertility Maps</a></li>
									  <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Fertilizer Recommendations</a></li>
									</ul>
					  			</li>
								@endif
								<li><a>Preregistration Subsystem<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										<li><a href="{{route('preregDashboard')}}">National/Station Dashboard</a></li>
									</ul>
					  			</li>
								<li><a>e-Paalala<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Advisory Control & Monitoring</a></li>
										<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Geo-maps</a></li>
									</ul>
					  			</li>
							</ul>
						</li>
						<li><a><span><i class="fa fa-bullseye"></i>Impact <br> Assesment</span><span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Monintoring & Evaluation</a></li>
							  <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Farmer Satisfaction Survey</a></li>
							</ul>
						</li>

						<li><a><i class="fa fa-industry"></i>Seed Production<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('rsis.rs_distri.dashboard')}}">RS Availability</a></li>
								{{-- <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">RS Availability</a></li> --}}
								<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">RS Distribution</a></li>
							</ul>
						</li>
						
						<li><a><i class="fa fa-briefcase"></i>Seed Supply<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('rsis.rla.dashboard')}}">Result of Lab Analysis (RLA)</a></li>
								<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Participating SGC/A</a></li>
							</ul>
						</li>
						
						<li><a><i class="fa fa-truck"></i>Seed Delivery<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">Delivery Schedules & Confirmation</a></li>
								<li class="sub_menu"><a href="#" data-toggle="modal" data-target="#noticePage404">SGC/A Summary of Deliveries</a></li>
							</ul>
						</li>
						
						<li><a href="{{url('/insp_monitoring')}}"><i class="fa fa-search"></i>Seed Inspection Results</a></li>
						{{-- <li><a>Seed Inspection<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							</ul>
						</li> --}}
						
						<li><a><i class="fa fa-share-alt"></i>Distribution<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="#" data-toggle="modal" data-target="#noticePage404">Seed Distribution</a></li>
								<li><a href="#" data-toggle="modal" data-target="#noticePage404">KP Distribution</a></li>
								<li><a>Buffer / Inventory & Replacement Seeds<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										<li><a href="{{route('rcef.inspection.buffer.designation')}}">Assign Inspector (Buffer App)</a></li>
										<li><a href="{{route('rcef.buffer.inspector.schedule')}}">Change Inspector (Buffer App)</a></li>
										<li><a href="{{route('web.dop.maker.replacement.index')}}">Create Drop Off Point (Replacement)</a></li>
										@if(Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->roles->first()->name == "data-officer" ||  Auth::user()->roles->first()->name == "system-admin" ||  Auth::user()->roles->first()->name == "rcef-pmo")
											<li class="sub_menu"><a href="{{route('view.report.break_down.index')}}">Second Inspection Result</a></li>
										@endif
									</ul>
								</li>
								
								<li><a>Document Generation (FAR, IAR, SAR etc.)<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									<li><a href="{{route('FarGenerationPs.index')}}">Generate Municipal FAR</a></li> 
									{{-- <li><a href="{{route('FarGenerationVd.index')}}">Validated Profiles re-deployment</a></li> --}}
									@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "pnm.marcelo.pampanga.com" || Auth::user()->username == "NUEVAECIJA_Jhoemar" || Auth::user()->username == "e.lopez"  )
									{{-- <li><a href="{{route('FarGeneration.index')}}">Current Season FAR</a></li> --}}
									{{-- <li><a href="{{route('FarGenerationPreReg.index')}}">Pre-Registered Farmer</a></li>  
									@endif
									@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "bs.pungtilan" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc" || Auth::user()->username == "je.almine" || Auth::user()->username == "R.Bombase" )
										{{-- <li><a href="{{route('FarGeneration.index')}}">Current Season FAR</a></li> --}}
										<li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
									@endif
									@if(Auth::user()->username == "e.lopez")								
									<li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
									@endif
									<li><a onclick="genBlankFAR('a3');">Generate Blank FAR</a>
									
									</li>
									<li class="{{ @$report_dis_summary != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.iar_table')}}">Generate IAR</a></li>
									</ul>
								</li>
								@if(Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->username == "jpalileo")
									<li><a href="{{route('encoding_vs')}}">Online Encoding (from Verifier)</a></li>
									<li><a href="{{route('encoding_vs_fca')}}">Online Encoding (FCA Member)</a></li>
									<li><a href="{{route('encoding_vs_lowland')}}">Online Encoding (Small Landholding)</a></li>
									<li><a href="{{route('encoding_vs_homeAddressClaim')}}">Online Encoding (Home Address Claims)</a></li>
									
									
								@endif
							</ul>
						</li>

						<li class="{{ @$report_side != '' ? 'active' : '' }}"><a><i class="fa fa-database"></i> Reports <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$report_side != '' ? 'display:block' : 'display:none' }} ">
								<li><a>Seed Beneficiary Report<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  {{-- <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li> --}}
									  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
									  {{-- <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#export_nrp">NRP</a></li> --}}
									</ul>
								</li>
								<li class="sub_menu"><a href="{{route('report.variety.overall')}}">Seed Variety Report</a></li>
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<!--<li class=""><a href=" {{route('rcef.report')}}" target="_blank">Manual Override (REFRESH)</a></li>-->
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<li class="sub_menu"><a href="{{route('yield_ui.home')}}">Yield Tables</a></li>
								@endif

								<!--<li class=""><a href=" {{route('rcef.report.beneficiaries')}}  ">Distribution Server</a></li>-->
								  
								  
								 @if(Auth::user()->roles->first()->name == "rcef-programmer") 
									<li><a>Allocation vs Delivery<span class="fa fa-chevron-down"></span></a>
										<ul class="nav child_menu">
										  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'regional')}}">Regional</a></li>
										  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'provincial')}}">Provincial</a></li>
										  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'municipal')}}">Municipal</a></li>
										</ul>
									</li>
							    @endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso" || Auth::user()->username == "eb.cabanisas" || Auth::user()->username == "aa.alonzo" || Auth::user()->username == "jc.tizon" )
									<li><a>DRO Report<span class="fa fa-chevron-down"></span></a>
										<ul class="nav child_menu">
											<li class="sub_menu"><a href="{{route('ui.export.municipal')}}" >Municipal Statistics</a></li>
											<li class="sub_menu"><a href="{{route('ui.export.provincial')}}" >Provincial Statistics</a></li>
											<li class="sub_menu"><a href="{{route('ui.export.regional')}}" >Regional Statistics</a></li>
										  <li class="sub_menu"><a href="{{route('report.export.replacement.excel')}}">Replacement Seeds</a></li>
								   		    <li class="sub_menu"><a href="{{route('report.download_commitment_delivery.coop')}}">Local Seed Supply Analysis</a></li>
											   <li class="sub_menu"><a href="{{route('delivery_dashboard.all.coop')}}">Cooperative Delivery Report</a></li>
											
										</ul>
									</li>
								  <!--<li class="sub_menu"><a href="{{route('data.yield.home')}}">Yield Report</a></li>-->
								  @endif
								
								
								{{-- <li class="{{ @$report_dis_summary != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.iar_table')}}">Generate IAR</a></li> --}}
								
 								<li class="{{ @$bufferInventoryformview != '' ? 'active' : '' }}"><a href=" {{route('bufferInventoryformview')}}  ">IAR (Replacement)</a></li>
								
								@if(Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->roles->first()->name == "system-admin") 
								  <li class="sub_menu"><a href="{{route('farmer_profile.contact.statinfo')}}">Farmer With Contact Information (Statistics)</a></li>
							    @endif
								
								@if (Auth::user()->can('acc-list'))
								<li class="{{ @$report_form != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.acc_iar_table')}}  ">Accountant IAR</a></li>
								@endif
							</ul>
						</li>

						@if(Auth::user()->roles->first()->name != "sed-caller" &&  Auth::user()->roles->first()->name != "sed-caller-manager" && Auth::user()->roles->first()->name != "it-sra" || Auth::user()->roles->first()->name == "rcef-programmer")
						{{-- <li><a href="{{route('distribution.replacement')}}"><i class="fa fa-stack-exchange"></i> Open for Replacement</a></li> --}}
						<li><a><i class="fa fa-cogs"></i> Utility <span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										@if(Auth::user()->roles->first()->name == "rcef-programmer")

										<li><a>User Management.<span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												<li><a href="{{route('users.index')}}">Add / Edit / Deactivate Users</a></li>
												<li><a href="{{route('users.approval')}}">User Requests</a></li>
											</ul>
										</li>
										@endif
										<li><a>Seed <br> Delivery<span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												<li><a><i class=""></i> Troubleshooting <span class="fa fa-chevron-down"></span></a>
													<ul class="nav child_menu">
													
														@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer")
															<li><a href="#" data-toggle="modal" data-target="#utilDel_modal">Cancel Delivery</a></li>
														@endif									
														@if(Auth::user()->roles->first()->name == "rcef-programmer")
															<li><a href="{{route('delivery_web.cancel.home')}}"> Cancel Confirmed Deliveries</a></li>
															<li><a href="{{route('utility.select_area.view')}}"> Area Troubleshooting UI</a></li>
																
														@endif
														@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "data-officer")
															<li><a href="#" data-toggle="modal" data-target="#iar_print_log">Reset printed IAR</a></li>
														@endif

														@if(Auth::user()->roles->first()->name == "administrator" || Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->roles->first()->name == "rcef-programmer")
															<li><a href="{{route('web.dop.maker.regular')}}">DOP Maker</a></li>	
														@endif
														
													</ul>
												</li>

												<li><a>Monitoring <span class="fa fa-chevron-down"></span></a>
													<ul class="nav child_menu">
														<li class="sub_menu"><a href="{{route('pendingBatch.index')}}">Pending Deliveries</a></li> 
														<li class="sub_menu"><a href="{{route('cancelledBatch.index')}}">Cancelled Deliveries</a></li> 
														<li><a href="{{route('HistoryMonitoring.index')}}">Seed Transfer History</a></li>
													</ul>
												</li>
											</ul>
										</li>
										<li class="{{ @$inspection_side != '' ? 'active' : '' }}"><a>Seed Inspection <span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu" style="{{ @$inspection_side != '' ? 'display:block' : 'display:none' }} ">
												<!--<li class="{{ @$inspection_verification != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.registration')}}  ">Registration</a></li>-->
												<li class=""><a href="{{route('rcef.inspector.schedule')}}">Change Inspector</a></li>
												<li class="{{ @$inspection_form != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.designation2')}}  ">Assign Inspector</a></li>
												{{-- <li><a href="{{url('/insp_monitoring')}}">Inspection monitoring </a></li> --}}
											</ul>
										</li>

										<li><a>Result of Lab Analysis<span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												<li><a href="{{route('rla.monitoring.home')}}"> RLA Monitoring</a></li>
												@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->userId == 504)
													<li><a href="{{route('rla.monitoring.homeMissing')}}"> RLA Monitoring (Missing RLA)</a></li>
												@endif
												@if(Auth::user()->roles->first()->name == "rcef-programmer")
													<!--<li><a href="{{route('edit_delivery.home')}}">Edit Delivery</a></li>-->
												@endif
												@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer")
													<li><a href="{{route('coop.rla')}}">Batch RLA Upload</a></li> 
												@endif
												
												@if(Auth::user()->roles->first()->name == "coop-operator" || Auth::user()->roles->first()->name == "rcef-programmer")
													<li><a href="{{route('coop.rla.manual')}}">Upload RLA (MANUAL)</a></li> 
												@endif
												
												@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "NUEVAECIJA_Jhoemar"  || Auth::user()->username == "e.lopez" || Auth::user()->username == "rbombase.1" || Auth::user()->roles->first()->name == "dro")
													 <li><a href="{{route('coop.rla.bpi')}}">ADD RLA </a></li> 
												@endif
												
												@if(Auth::user()->roles->first()->name == "rcef-programmer")
													<li><a href="{{route('coop.rla.pmo')}}">Monitor RLA</a></li>
													<li><a href="{{route('coop.rla.approve_home')}}">Approve RLA</a></li>
												@endif
												
												@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "system-admin")
													<!--<li><a href="{{route('coop.rla.edit')}}">EDIT RLA</a></li>--> 
												@endif
												
												@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-pmo")
													<li><a href="#" data-toggle="modal" disabled="" data-target="#rla_finder">RLA Finder</a></li>
													{{-- <li><a href="#" data-toggle="modal" disabled="" data-target="#noticePage404">Sample Modal</a></li> --}}
												@endif
											</ul>
										</li>

										<li><a>Seed Distribution <span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												<li><a href="{{route('distribution.app.stocks_home')}}">Release Stocks</a></li>
												<li><a href="{{route('released.data.index')}}"> Edit\Clean Distribution Data</a></li>
												<li><a href="{{route('paymaya.beneficiary.codes')}}">Beneficiary List with Codes (BeP)</a></li>
											</ul>
										</li>


										<li><a>Farmer List <span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer") --}}
												<li><a href=" {{route('rcef.id.generation')}} ">RCEF Seeds ID</a></li>
												{{-- @endif --}}
		
												
												<li><a href=" {{route('farmer.finder')}} ">Farmer Finder</a></li>	
												
												@if(Auth::user()->roles->first()->name == "rcef-programmer")
												<li><a href=" {{route('historical.farmer.finder')}} ">Farmer Beneficiary History</a></li>	
												
												@endif
												
												@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "aa.alonzo" )
													<li><a href=" {{route('export.farmer.list')}} ">Export Farmer List </a></li>
												@endif
		
											</ul>
										</li>

										@if(Auth::user()->roles->first()->name == "rcef-programmer")

										<li><a>Seed Cooperative<span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												 <li><a href="{{route('coop.commitment')}}">Adjust Commitment</a></li>
												 @if(Auth::user()->roles->first()->name == "rcef-programmer")
												 	<li><a href="{{route('sg.list')}}">Blacklist SG</a></li>		
												 @endif
											</ul>
										</li>
								

										<li> <a>Direct Import <span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
												<li class="sub_menu"><a href="{{route('import.seed_growers')}}">Seed Growers</a></li> 
												<li class="sub_menu"><a href="{{route('import.rla')}}">RLA</a></li> 							
												<li class="sub_menu"><a href="{{route('import.ebinhi')}}">E-Binhi</a></li> 							
												<li class="sub_menu"><a href="{{route('import.ebinhi.update.status')}}">E-Binhi update status</a></li> 							
											</ul>
										</li>


										{{-- <li class="sub_menu"><a href="{{route('farmer_profile.with.contact.nationwide')}}">Farmer w/ Contact Counter Nationwide Process</a></li>  --}}
										
										@endif

										@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "dra.jardinez")
											<li class="sub_menu"><a href="{{route('customExportUI')}}">Custom Export UI</a></li> 
											<li><a href=" {{route('farmer.id.home')}} ">QR Code Generation </a></li>
										@endif


										@if(Auth::user()->roles->first()->name == "rcef-programmer")
											<li><a href="{{route('encoder.yield.home')}}">Yield Updating</a></li>
										@endif
										@if(Auth::user()->roles->first()->name == "rcef-programmer")
											<li><a href="{{route('dopMaker.replacement')}}">Open for Replacement</a></li>
										@endif
							</ul>
						</li>
						@endif
						

						


						 
						<!--<li><a><i class="fa fa-cogs"></i> Settings <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('system.settings.archive')}}">Archive</a></li>
								<li><a href="{{route('system.settings.qrcode')}}">QR Code</a></li>
								<li><a href="{{route('system.settings.distribution')}}">Distribution</a></li>
							</ul>
						</li>-->
					   <?php
					 //  echo "<script>alert(". Auth::user()->roles->first()->name.") </script>";
					   $prov = substr(Auth::user()->province,0,2);
						if(Auth::user()->province!="" and (Auth::user()->roles->first()->name == "rcef-programmer" or Auth::user()->roles->first()->name == "administrator"  or Auth::user()->roles->first()->name == "rcef-pmo")){
	

									    $curr =  basename(getcwd());
									    $curr = str_replace("rcef_", "", $curr);
									    if(strlen($curr) == 6){
									      $curYr =  intval(substr($curr, 2, 4));
									      $curSeason = strtoupper(substr($curr, 0, 2));
									        if($curSeason=="DS"){
									          $prvSeason = "WS";
									          $prvYr = $curYr - 1;
									        }elseif($curSeason=="WS"){
									          $prvSeason = "DS";
									          $prvYr = $curYr;
									        }

									        $currentSeason = $curSeason.$curYr;
									        $previousSeason = $prvSeason.$prvYr;


									    }else{
									    	$currentSeason = "Current";
									    	$previousSeason = "Previous";
									    }

    
    
 

					   ?>
							<!--<li><a href="{{route('rcef.transfers')}}"><i class="fa fa-refresh"></i> Transfers </a></li>-->
							 
							<li><a><i class="fa fa-refresh"></i> Transfers <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li><a href="{{route('rcef.transfers')}}">{{$previousSeason}}-{{$currentSeason}} </a></li>
									<li><a href="{{route('rcef.transfers.ws2020')}}">{{$currentSeason}}-{{$currentSeason}} </a></li>
								</ul>	
							</li>

						
							
							{{-- <li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li> --}}
						<?php
						}
						else if(Auth::user()->province!="") {
							?>
							{{-- <li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li> --}}
							<?php
						}
						?>
					@endif


					
                </ul>
            </div>

			<div class="menu_section">
				<h3>Sidebar Settings</h3>
				<ul style="padding-left: 0rem;">
					<li style="list-style: none; margin: 0; padding: 0; display: flex; align-items: center;">
						<label class="switch" style="cursor: pointer;">
							<input type="checkbox" id="darkModeToggle">
							<span class="slider round"></span>
						</label>
						<label class="forDarkModeLabel" style="margin-left: -2rem; display: flex; align-items: center; cursor: pointer;" for="darkModeToggle"><i class="fa fa-adjust" aria-hidden="true"></i> <span id="darkModeLabel"></span></label>
					</li>
				</ul>
			</div>
			<?php
	$userManagement = array();
	// $userManagement["agusan.admin"] = "agusan.admin";
	// $userManagement["jc.felix"] = "jc.felix";
	// $userManagement["Kavin04"] = "Kavin04";
	// $userManagement["r.javines"] = "r.javines";
	// $userManagement["h.bansilan"] = "h.bansilan";
	// $userManagement["lb.admin"] = "lb.admin";
	// $userManagement["J.abas"] = "J.abas";
	// $userManagement["kavin04"] = "kavin04";
 ?>
            {{-- USER MANAGEMENT --}}
           
			{{-- end sidebar DEV  ---}}

			{{-- start paymnet processor  --}}
			@elseif(Auth::user()->roles->first()->name == "ces_payment_processor")
			<div class="menu_section">
				<h3 class="group_title">GENERAL</h3>
                <ul class="nav side-menu">
					<li><a href=" {{url('/')}} "><i class="fa fa-dashboard"></i> Home Page</a></li>
					<li><a href="{{route('dv_formatter.home')}}"><i class="fa fa-file-text-o"></i>DV Formatter</a></li>
					<li><a href="{{route('processor.home')}}"><i class="fa fa-edit"></i>Payment Attachement</a></li>
				</ul>
			</div>
			{{-- end paymnet processor  --}}
			
			{{-- start paymnet dro  --}}
			@elseif(Auth::user()->roles->first()->name == "dro")
			<div class="menu_section">
				<h3>GENERAL</h3>
                <ul class="nav side-menu">
					<li><a href=" {{url('/')}} "><i class="fa fa-dashboard"></i> Home Page</a></li>
					<li><a href="{{route('dro.home')}}"><i class="fa fa-edit"></i>DRO</a></li>
					<li><a><i class="fa fa-street-view" aria-hidden="true"></i> Farmer List <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							<li><a href=" {{route('farmer.finder')}} "><i class="fa fa-search" aria-hidden="true"></i>Farmer Finder</a></li>	

						</ul>
					</li>
				</ul>
			</div>
			{{-- end paymnet dro  ---}}

			{{-- start paymnet accountant  --}}
			@elseif(Auth::user()->roles->first()->name == "payment_accountant")
			<div class="menu_section">
				<h3 class="group_title">GENERAL</h3>
                <ul class="nav side-menu">
					<li><a href=" {{url('/')}} "><i class="fa fa-dashboard"></i> Home Page</a></li>
					<li><a href="{{route('accountant.home')}}"><i class="fa fa-edit"></i>Accountant</a></li>
				</ul>
			</div>
			{{-- end paymnet accountant  --}}

            @elseif(Auth::user()->roles->first()->name == "extension_encoder")
            <?php
            	// if($_SERVER['REQUEST_URI']!="/rcef_ds2024/extension/home"){
            	// 	if($_SERVER['REQUEST_URI']!="/rcef_ds2024/fargeneration/ps"){
				// 	   header('Location: ' . route('rcef.extension.home'), true, 303);
				// 	   die();
            	// 	}
            	// }
            ?>


            <div class="menu_section">
                <h3>GENERAL</h3>
                <ul class="nav side-menu">
                    <li><a><i class="fa fa-puzzle-piece"></i> RCEF Extension<span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							 <li><a href="{{route('FarGenerationPs.index')}}">Generate Municipal FAR</a></li>
							 <li><a href="{{route('rcef.extension.home')}}">Data-Entry / Updating</a></li>
							 <li><a href="{{route('KPEncoderMonitoring_index')}}">Encoder Monitoring</a></li>
							 <li><a href="{{route('KPDistribution_index')}}">KP-IEC Distribution Dashboard</a></li>
							 <li><a href="{{route('dashboard.gad.view')}}">GAD Dashboard</a></li>
							 
						 <!-- <li><a href="{{route('rcef.inspection.buffer.designation')}}">Reports</a></li> -->
						</ul>
					</li>

					<?php
					$ext_far = array();
					$ext_far["aldrin.castro"] =1;
					$ext_far["reuel.maramara"] =1;
					$ext_far["derwin.villena"] =1;
					$ext_far["deejay.jimenez"] =1;
					$ext_far["asc.fontanilla"] =1;
					$ext_far["v.tingson"] =1;
					$ext_far["marelie.tangog"] =1;
					$ext_far["kimbie.pedtamanan"] =1;
					?>
					@if(isset($ext_far[Auth::user()->username]))
					<li><a><i class="fa fa-folder-open"></i> Generate FAR<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('FarGenerationPs.index')}}">Generate Municipal FAR</a></li> 
							  
							</ul>
						</li>
					@endif
                </ul>
            </div>
            @elseif(Auth::user()->roles->first()->name == "ebinhi-implementor")

            <?php
		//	dd($_SERVER['REQUEST_URI']);
            	if($_SERVER['REQUEST_URI']!="/rcef_ds2024/paymaya/distribution"){
            		//dd($_SERVER['REQUEST_URI']);
					   	if($_SERVER['REQUEST_URI']!="/rcef_ds2024/paymaya/report/beneficiary/codes"){
						   if($_SERVER['REQUEST_URI']!="/rcef_ds2024/fargeneration/ebinhi"){
							if($_SERVER['REQUEST_URI']!="/rcef_ds2024/cssDashboard"){
								header('Location: ' . route('paymaya.seed_distribution'), true, 303);
								die();
            				}	
            				}	
            			}	
            	}
            ?>
            <div class="menu_section">
                <h3 class="group_title">GENERAL</h3>
                <ul class="nav side-menu">
						<li><a><i class="fa fa-binoculars"></i> e-Binhi Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
							  <li><a href="{{route('paymaya.seed_distribution')}}">Beneficiary List</a></li>
							 <!-- <li class="sub_menu"><a href="{{route('paymaya.beneficiary_report')}}">Beneficiary Reports</a></li> -->
							  <li><a href="{{route('paymaya.beneficiary.codes')}}">Beneficiary List with Codes</a></li>
								@if(Auth::user()->username == "bep_css")
								<li><a href="{{route('cssDashboard')}}">CSS Dashboard</a></li>
								@endif
							</ul>
						</li>
                </ul>
            </div>
        	

            <!--START FOR GENERAL ACCESS-->
        	@else
            <div class="menu_section">
                <h3 class="group_title">General</h3>
                <ul class="nav side-menu">
           
					@if(Auth::user()->roles->first()->name == "system-encoder")
					@php

					// ENCODER
						if($_SERVER['REQUEST_URI'] == "/rcef_ds2024/"){
							header('Location: ' . route('farmer.finder'), true, 303);
						}	
					@endphp
						{{-- <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li> --}}
						<li><a><i class="fa fa-folder-open"></i> Generate FAR<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								  <li><a href="{{route('pre_list_index')}}">Generate Farmer Pre List</a></li> 

							</ul>
						</li>

						<li><a><i class="fa fa-street-view" aria-hidden="true"></i> Farmer List <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href=" {{route('farmer.finder')}} "><i class="fa fa-search" aria-hidden="true"></i>Farmer Finder</a></li>	

							</ul>
						</li>

						<li><a><i class="fa fa-tasks"></i> Distribution Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('encoding_vs')}}">Online Encoding (from Verifier)</a></li>
								<li><a href="{{route('encoding_vs_fca')}}">Online Encoding (FCA Member)</a></li>
								<li><a href="{{route('encoding_vs_lowland')}}">Online Encoding (Small Landholding)</a></li>
								@if(Auth::user()->roles->first()->name == "system-encoder" || Auth::user()->stationId != 11010)
								<li><a href="{{route('encoding_vs_homeAddressClaim')}}">Online Encoding (Home Address Claims)</a></li>
								<li><a href="{{route('onlineEncodingNew')}}">Online Encoding for New Farmers</a></li>
								@endif
								
								<!-- <li><a href="{{route('farmerVerification')}}">Farmer KYC</a></li> -->
								<!-- <li><a href="{{route('FarmerKYCMonitoring_index')}}">Farmer KYC Monitoring</a></li> -->

								


							</ul>
						</li>

						
					@elseif(Auth::user()->roles->first()->name == "da-icts")
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						<li><a href="{{route('dashboard.delivery_summary')}}"><i class="fa fa-dashboard"></i> Delivery Summary </a></li>
						<li><a><i class="fa fa-database"></i> Seed Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display:block">
							  {{-- <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li> --}}
							  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
							</ul>
						</li>
                    @elseif(Auth::user()->roles->first()->name == "coop-operator")
					  <!-- <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Coop Dashboard </a></li> -->
					  <li><a href="{{route('coop_operator.deliveries')}}"><i class="fa fa-truck"></i> Coop Deliveries </a></li>
					  <li><a href="{{route('coop_operator.sg_enrollment')}}"><i class="fa fa-legal"></i> SG Enrollment</a></li>
					  <li><a href="{{route('coop_operator.sg_matrix')}}"><i class="fa fa-cubes"></i> SG Matrix</a></li>
					  <li><a href="{{route('coop_operator.report')}}"><i class="fa fa-book"></i> Coop Report</a></li>
					 
					@elseif(Auth::user()->roles->first()->name == "sed-caller")
					  <li><a href="{{url('/caller/farmers')}}"><i class="fa fa-users"></i> Farmers </a></li>  
					@elseif(Auth::user()->roles->first()->name == "sed-caller-manager")
					  <li><a href="{{url('/sed/dashboard')}}"><i class="fa fa-calendar"></i> Dashboard </a></li>  
					  <li><a href="{{url('/sed/farmers')}}"><i class="fa fa-check"></i> Verified Farmers </a></li>  
					  <li><a href="{{url('/sed/manage/farmer')}}"><i class="fa fa-list"></i> Farmer List </a></li>  
					  <li><a href="{{url('/sed/manage')}}"><i class="fa fa-users"></i> SED Users </a></li>  
					@elseif(Auth::user()->roles->first()->name == "it-sra")
					<li><a href="{{url('sra/paymaya')}}"><i class="fa fa-calendar"></i> Scheduler </a></li>  
					<li><a href="{{url('sra/scheduled/farmers')}}"><i class="fa fa-users" aria-hidden="true"></i> Scheduled Farmers </a></li> 
					<li><a href="{{url('sra/dop')}}"><i class="fa fa-map"></i> Drop Off Points </a></li>
					<li><a href="{{url('sra/utility')}}"><i class="fa fa-cogs" aria-hidden="true"></i> E-Binhi Utility </a></li>    
					@elseif(Auth::user()->roles->first()->name == "bpi-nsqcs" || Auth::user()->username == "rbombase.1" || Auth::user()->roles->first()->name == "dro")
					  <li><a href="{{route('coop.rla.pmo')}}"><i class="fa fa-eye"></i> Monitor RLA </a></li>
					  <li><a href="{{route('coop.rla.bpi')}}"><i class="fa fa-plus-circle"></i> ADD RLA </a></li>
					@else
						
						<!-- OTHER USERS -->
						<li><a href=" {{url('/')}} "><i class="fa fa-home"></i> Home Page</a></li>
						<li><a><i class="fa fa-bar-chart"></i> Analytics<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<li><a href="{{route('analytics.home')}}"> Analytics Summary</a></li>
								<li><a href="{{route('yieldCount.home')}}"> Yield Report</a></li>
								<li><a href="{{route('palaysikatan.dashboard.index')}}"> Palaysikatan Dashboard </a></li>
								<li><a href="{{route('payment_dashboard.home')}}">Payment Dashboard</a></li>
								<li><a href="{{route('station_report.home')}}">Station Dashboard </a></li>
								<!-- <li><a href="{{route('coop.dashboard')}}">Coop Dashboard</a></li> -->
								<li><a href="{{route('planting_calendar_index')}}">Seed Variety Performance Dashboard</a></li>
								@endif
								<li><a href="{{route('dashboard.gad.view')}}">GAD Dashboard</a></li>
								<li><a href="{{route('preregDashboard')}}">Pre-registration Dashboard</a></li>
								<li><a href="{{route('cssDashboard')}}">CSS Dashboard</a></li>
								{{-- <li><a href="#" data-toggle="modal" data-target="#noticePage404">KP Kits/IEC Distribution</a></li> --}}
								{{-- <li><a href="#" data-toggle="modal" data-target="#noticePage404">RCEF KYC (MAP)</a></li> --}}
								<li><a href="{{url('/DeliveryDashboard')}}">Delivery Dashboard </a></li>
								<li><a href="{{route('KPDistribution_index')}}">KP-IEC Distribution Dashboard</a></li>
								{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer") --}}
								{{-- @endif --}}
								{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer") --}}
								{{-- @endif --}}
							</ul>
						</li>


						
						<li><a><i class="fa fa-flask"></i>RLA <br> Management<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('rla.monitoring.home')}}"> RLA Monitoring</a></li>
								@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->userId == 504)
									<li><a href="{{route('rla.monitoring.homeMissing')}}"> RLA Monitoring (Missing RLA)</a></li>
								@endif
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<!--<li><a href="{{route('edit_delivery.home')}}">Edit Delivery</a></li>-->
								@endif
								@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer")
									<li><a href="{{route('coop.rla')}}">Batch RLA Upload</a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "coop-operator" || Auth::user()->roles->first()->name == "rcef-programmer")
									<li><a href="{{route('coop.rla.manual')}}">Upload RLA (MANUAL)</a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "NUEVAECIJA_Jhoemar"  || Auth::user()->username == "e.lopez" || Auth::user()->username == "rbombase.1" || Auth::user()->roles->first()->name == "dro")
									 <li><a href="{{route('coop.rla.bpi')}}">ADD RLA </a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<li><a href="{{route('coop.rla.pmo')}}">Monitor RLA</a></li>
									<li><a href="{{route('coop.rla.approve_home')}}">Approve RLA</a></li>
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "system-admin")
									<!--<li><a href="{{route('coop.rla.edit')}}">EDIT RLA</a></li>--> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-pmo")
									<li><a href="#" data-toggle="modal" disabled="" data-target="#rla_finder">RLA Finder</a></li>
									{{-- <li><a href="#" data-toggle="modal" disabled="" data-target="#noticePage404">Sample Modal</a></li> --}}
								@endif
							</ul>
						</li>
						

						
						<li><a><i class="fa fa-folder-open"></i> Generate FAR<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							<li><a href="{{route('FarGenerationPs.index')}}">Generate Municipal FAR</a></li> 
							{{-- <li><a href="{{route('FarGenerationVd.index')}}">Validated Profiles re-deployment</a></li>  --}}
							


							  @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "pnm.marcelo.pampanga.com" || Auth::user()->username == "NUEVAECIJA_Jhoemar" || Auth::user()->username == "e.lopez"  )
							  {{-- <li><a href="{{route('FarGeneration.index')}}">Current Season FAR</a></li>
							  <li><a href="{{route('FarGenerationPreReg.index')}}">Pre-Registered Farmer</a></li>  --}}
						 	 @endif

								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "bs.pungtilan" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc" || Auth::user()->username == "je.almine" || Auth::user()->username == "R.Bombase" )
									{{-- <li><a href="{{route('FarGeneration.index')}}">Current Season FAR</a></li> --}}
									<li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
								@endif

								@if(Auth::user()->username == "e.lopez")								
								<li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
								@endif
								

							
								  <li class="sub_menu"><a onclick="genBlankFAR('a3');"> Generate Blank FAR</a>
								  
								  </li>
							

								  <li><a href="{{route('pre_list_index')}}">Generate Farmer Pre List</a></li> 

							</ul>
						</li>
					
						
						<li><a><i class="fa fa-tasks"></i> Distribution Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							@if(Auth::user()->roles->first()->name == "seed-inspector" || Auth::user()->username == "J.Baldonado" || Auth::user()->username == "L.Padua" || Auth::user()->username == "R.Millena" || Auth::user()->roles->first()->name == "administrator" || Auth::user()->username == "A.Rivera" )
								<li><a href="{{route('encoding_vs')}}">Online Encoding (from Verifier)</a></li>
								<li><a href="{{route('encoding_vs_fca')}}">Online Encoding (FCA Member)</a></li>
								<li><a href="{{route('encoding_vs_lowland')}}">Online Encoding (Small Landholding)</a></li>
								<li><a href="{{route('encoding_vs_homeAddressClaim')}}">Online Encoding (Home Address Claims)</a></li>
								<li><a href="{{route('onlineEncodingNew')}}">Online Encoding for New Farmers</a></li>
								
							@endif
							  @if(Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->username == "mt.garcia" ||  Auth::user()->username == "jpalileo" || Auth::user()->username == "aquino.rr" || Auth::user()->username == "jragos_pc" || Auth::user()->username == "p.landasan" || Auth::user()->roles->first()->name == "administrator")
							  <li><a>Encoding <span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										<li><a href="{{route('encoding_vs')}}">From Verifier</a></li>
										<li><a href="{{route('encoding_vs_fca')}}">FCA Member</a></li>
										<li><a href="{{route('encoding_vs_lowland')}}">Small Landholding</a></li>
										<li><a href="{{route('encoding_vs_homeAddressClaim')}}">Home Address Claims</a></li>
										<li><a href="{{route('onlineEncodingNew')}}">New Farmers</a></li>
										
									</ul>
								</li>

								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "administrator" || Auth::user()->roles->first()->name == "seed-inspector" || Auth::user()->roles->first()->name == "system-encoder")
								<!-- <li><a href="{{route('farmerVerification')}}">Farmer KYC</a></li> -->
								<!-- <li><a href="{{route('FarmerKYCMonitoring_index')}}">Farmer KYC Monitoring</a></li> -->
								@endif

								<li><a href="{{route('distribution.app.stocks_home')}}">Release Stocks</a></li>
								<li><a href="{{route('released.data.index')}}"> Edit\Delete Distribution Data</a></li>
								<li><a href="{{route('web.dop.maker.regular')}}">DOP Maker</a></li>	
								<li><a href="{{route('stocks.monitoring.index')}}">Stocks Monitoring</a></li>
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<li><a href="{{route('distribution.app.stocks_seedType')}}">Change Seed Type</a></li>
								<li><a href="{{route('pre_reg.view_farmer')}}"> Update Farmer Information</a></li>
								@endif
							  @else
								<li><a href="{{route('distribution.app.stocks_home_public')}}">Stocks Downloaded</a></li>
								<li><a href="{{route('stocks.monitoring.index')}}">Stocks Monitoring</a></li>
									@if(Auth::user()->roles->first()->name == "administrator" || Auth::user()->roles->first()->name == "rcef-pmo")
										<li><a href="{{route('released.data.index')}}"> Edit\Delete Distribution Data</a></li>
										<li><a href="{{route('web.dop.maker.regular')}}">DOP Maker</a></li>	
									
									@endif
							  @endif
							  


							</ul>
						</li>
						
						<li class="{{ @$inspection_side != '' ? 'active' : '' }}"><a><i class="fa fa-eye"></i> Seed Inspection <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$inspection_side != '' ? 'display:block' : 'display:none' }} ">
								<!--<li class="{{ @$inspection_verification != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.registration')}}  ">Registration</a></li>-->
								<li class=""><a href="{{route('rcef.inspector.schedule')}}">Change Inspector</a></li>
								<li class="{{ @$inspection_form != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.designation2')}}  ">Assign Inspector</a></li>
								<li><a href="{{url('/insp_monitoring')}}">Inspection monitoring </a></li>
							</ul>
					    </li>
						
						@if(Auth::user()->roles->first()->name == "data-officer" || Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->roles->first()->name == "rcef-programmer" )
						<!--<li><a><i class="fa fa-google"></i> RCEF Google Sheet<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li class="sub_menu"><a href="{{route('rcep.google_sheet.summary')}}">Summary</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.google_sheet.schedule_home')}}">Data-Entry</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.google_sheet.dashboard')}}">Dashboard</a></li>
							</ul>
						  </li>-->
						@endif
						
						
	
						<li><a><i class="fa fa-binoculars"></i> BeP Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								@if(Auth::user()->username == "	m.padilla" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc")
									  <li><a href="{{route('accountant.home')}}">e binhi Accountant</a></li>
								@endif
								<li><a href="{{route('BePDashboard_index')}}">BeP Dashboard</a></li>
								<li><a href="{{route('paymaya.seed_distribution')}}">Beneficiary List</a></li>
								<!--<li><a href="{{route('paymaya.inspector_ui')}}">Inspector Interface</a></li>-->
						
							  <!--<li><a href="{{route('paymaya.variety_report')}}">Variety Report</a></li>-->
							  <li class="sub_menu"><a href="{{route('paymaya.beneficiary_report')}}">Beneficiary Reports</a></li>
							   

							   @if(Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc" || Auth::user()->username == "NUEVAECIJA_Jhoemar" || Auth::user()->username == "e.lopez" || Auth::user()->roles->first()->name == "rcef-programmer" )
							   		@if(Auth::user()->roles->first()->name == "rcef-programmer")
									{{-- <li class="sub_menu"><a href="{{route('upload.paymaya.process.index')}}" onclick="return confirm('Proceed Processing Paymaya Codes?')">Process Paymaya Codes</a></li>  --}}
									<li><a href="{{route('paymaya.beneficiary.codes')}}">Beneficiary List with Codes</a></li>
									<li><a href="#" data-toggle="modal" data-target="#paymaya_tags_modal">Excess QR Codes</a></li>
									<li class="sub_menu"><a href="{{route('paymaya.municipalities.list')}}">Municipalities</a></li>
									@endif
								 @endif
								  @if(Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->username == "dc.gaspar" ||  Auth::user()->username == "jg.villanueva"||  Auth::user()->username == "e.lopez"||  Auth::user()->username == "jt.rivera" || Auth::user()->username == "renaida_pascual" || Auth::user()->username == "processor_jbl" || Auth::user()->username == "tine" || Auth::user()->username == "ddc.espiritu" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc")
							
								  <li><a>Payments<span class="fa fa-chevron-down"></span></a>
								 
								  <ul class="nav child_menu">
											<li><a href="{{route('manual_payment')}}">Manual Payment</a></li>
										  <li class="sub_menu"><a href="{{route('paymaya.reports.payments')}}">e-Binhi Payments</a></li>
										  <li class="sub_menu"><a href="{{route('paymaya.payment.reports.excel')}}">Export Batches</a></li>
										  <li><a href="{{route('paymaya.signatories')}}">e-Binhi Payments Signatories</a></li>
										  <li><a href="{{route('ebinhi.coops')}}">e-Binhi Cooperatives</a></li>	
								  </ul>
								</li>
							   @endif
							   @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc")
							   <li><a href="{{route('ebinhi.utility')}}">e-Binhi Tools</a></li>
								@endif

								@if(Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc")
								<li class="sub_menu"><a href="{{route('customExportUI')}}">Custom Export UI</a></li> 

								@endif

							</ul>
						</li>
						
						
						<li><a><i class="fa fa-users"></i> Seed Cooperative<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('coop.commitment')}}">Commitment</a></li>
								 @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "lei.malubag" || Auth::user()->username == "mcrmercado")
	 								<li><a href="{{route('rsis.rla.dashboard')}}">(RSIS) Result of Lab Analysis </a></li>
	 								<li><a href="{{route('rsis.rs_distri.dashboard')}}">RS - CS Seed Production </a></li>
	 								<li><a href="{{route('api.coop.logs')}}">Commitment Adjustment Logs</a></li>

									 
								 @endif
							

							</ul>
						</li>

						
						
						<li><a><i class="fa fa-cubes" aria-hidden="true"></i>Buffer / Replacement Seeds<span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							<li><a href="{{route('rcef.inspection.buffer.designation')}}">Assign Inspector (Buffer App)</a></li>
							<li><a href="{{route('rcef.buffer.inspector.schedule')}}">Change Inspector (Buffer App)</a></li>
							<li><a href="{{route('web.dop.maker.replacement.index')}}">Create Drop Off Point (Replacement)</a></li>
							@if(Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->roles->first()->name == "data-officer" ||  Auth::user()->roles->first()->name == "system-admin" ||  Auth::user()->roles->first()->name == "rcef-pmo")
							<li class="sub_menu"><a href="{{route('view.report.break_down.index')}}">Second Inspection Result</a></li>
							@endif
							
						</ul>
					</li>
					
					@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance"|| Auth::user()->roles->first()->name == "rcef-finance-dro" || Auth::user()->roles->first()->name == "rcef-finance-receiver" || Auth::user()->roles->first()->name == "rcef-finance-preparer" || Auth::user()->roles->first()->name == "rcef-finance-cashier" || Auth::user()->roles->first()->name == "rcef-finance-processor" || Auth::user()->username == "kruz" || Auth::user()->username == "jc.tizon")
					<li><a><i class="fa fa-money"></i> Payments<span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance"|| Auth::user()->roles->first()->name == "rcef-finance-dro" || Auth::user()->roles->first()->name == "rcef-finance-receiver" || Auth::user()->roles->first()->name == "rcef-finance-preparer" || Auth::user()->roles->first()->name == "rcef-finance-cashier" || Auth::user()->roles->first()->name == "rcef-finance-processor" || Auth::user()->username == "kruz" || Auth::user()->username == "jc.tizon")
								 <li><a href="{{route('paymentsDashboard')}}">Payments Dashboard</a></li>
								@endif

								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance"|| Auth::user()->roles->first()->name == "rcef-finance-preparer" || Auth::user()->username == "kruz" || Auth::user()->username == "jc.tizon")
								 <li><a href="{{route('DVPreparation')}}">DV Preparation</a></li>
								 @endif

								 @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance")
								 <li><a href="#" data-toggle="modal" data-target="#notification_modal">Notification Settings</a></li>
								 @endif
								 <!-- <li><a href="">Document Tracking </a></li>  -->
								</ul>
							</li>
							@endif



						 @if(Auth::user()->roles->first()->name == "rcef-programmer")
						<li><a><i class="fa fa-puzzle-piece"></i> RCEF Extension<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('rcef.extension.home')}}">Data-Entry / Updating</a></li>
								 <li><a href="{{route('KPEncoderMonitoring_index')}}">Encoder Monitoring</a></li>
								 <!-- <li><a href="{{route('rcef.inspection.buffer.designation')}}">Reports</a></li> -->
							</ul>
						</li>
						<li><a href=" {{route('farmer.id.home')}} "><i class="fa fa-qrcode"></i> QR Code Generation </a></li>	
						@endif

						
						
					
						
								
								<li><a><i class="fa fa-street-view" aria-hidden="true"></i> Farmer List <span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
										{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer") --}}
										<li><a href=" {{route('rcef.id.generation')}} "><i class="fa fa-print"></i> RCEF ID Generation </a></li>
										{{-- @endif --}}

										@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "aa.alonzo" )
										<li><a href=" {{route('export.farmer.list')}} "><i class="fa fa-file-excel-o" aria-hidden="true"></i>Export Farmer List </a></li>	
									
										@endif

										@if(Auth::user()->roles->first()->name == "rcef-programmer")
										<li><a href=" {{route('historical.farmer.finder')}} "><i class="fa fa-search" aria-hidden="true"></i>Historical Farmer Finder</a></li>	

										@endif

									


										<li><a href=" {{route('farmer.finder')}} "><i class="fa fa-search" aria-hidden="true"></i>Farmer Finder</a></li>	

									</ul>
								</li>
				



						<!--<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution </a></li>-->						
						<!--li><a><i class="fa fa-users"></i> Farmer Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('farmer_profile.home')}}">WS 2020</a></li>
							  <li><a href="{{route('farmer_profile.home.list')}}">WS 2020 x DS 2021 List</a></li>
							  <li><a href="{{route('farmer_profile.cross.check')}}">WS 2020 x DS 2021 x WS 2021 List</a></li>
							
							 

							</ul>
						  </li>-->

						 
						 @if(Auth::user()->roles->first()->name == "rcef-programmer")
						  <li><a href="{{route('palaysikatan.farmers')}}"><i class="fa fa-list-alt"></i>Palaysikatan</a></li>

						  <li><a><i class="fa fa-list-alt"></i>MOET APP<span class="fa fa-chevron-down"></span></a>
										<ul class="nav child_menu">
										  <li><a href="{{route('moet.web.view.farmer')}}">MOET's Farmer Profile</a></li>
										  <li><a href="{{route('moet.web.map_view.farmer')}}">MOET's Map View</a></li>
										  
										</ul>
						  </li>


						  
						  @endif

						  @if(Auth::user()->roles->first()->name == "rcef-programmer")
							  	 <li><a href="{{route('encoder.yield.home')}}"><i class="fa fa-pencil-square-o"></i>Yield Updating</a></li>
							  @endif
						@if(Auth::user()->roles->first()->name == "rcef-programmer")
							  <li><a href="{{route('dopMaker.replacement')}}"><i class="fa fa-openid"></i>Open for Replacement</a></li>
						 @endif


						 <li class="{{ @$report_side != '' ? 'active' : '' }}"><a><i class="fa fa-database"></i> Reports <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$report_side != '' ? 'display:block' : 'display:none' }} ">
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<!--<li class=""><a href=" {{route('rcef.report')}}" target="_blank">Manual Override (REFRESH)</a></li>-->
								@endif
								
								@if(Auth::user()->username == "aa.alonzo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "dc.gaspar" )
								<li class="sub_menu"><a href="{{route('DistributionData_index')}}">Data Warehouse</a></li>
								@endif
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<li class="sub_menu"><a href="{{route('yield_ui.home')}}">Yield Tables</a></li>
								@endif
								
								<!--<li class=""><a href=" {{route('rcef.report.beneficiaries')}}  ">Distribution Server</a></li>-->
								<li class="sub_menu"><a>Seed Beneficiary<span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									{{-- <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li> --}}
									<li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
									<li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
									<li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
									{{-- <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#export_nrp">NRP</a></li> --}}
								</ul>
							</li>
							
							@if(Auth::user()->roles->first()->name == "rcef-programmer") 
							<li class="sub_menu"><a>Allocation vs Delivery<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li class="sub_menu"><a href="{{route('delivery.allocation.view', 'regional')}}">Regional</a></li>
								<li class="sub_menu"><a href="{{route('delivery.allocation.view', 'provincial')}}">Provincial</a></li>
								<li class="sub_menu"><a href="{{route('delivery.allocation.view', 'municipal')}}">Municipal</a></li>
							</ul>
						</li>
						@endif
						
						@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso" || Auth::user()->username == "eb.cabanisas" || Auth::user()->username == "aa.alonzo" || Auth::user()->username == "jc.tizon" )
						<li class="sub_menu"><a>DRO Report<span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							<li class="sub_menu"><a href="{{route('ui.export.municipal')}}" >Municipal Statistics</a></li>
							<li class="sub_menu"><a href="{{route('ui.export.provincial')}}" >Provincial Statistics</a></li>
							<li class="sub_menu"><a href="{{route('ui.export.regional')}}" >Regional Statistics</a></li>
							<li class="sub_menu"><a href="{{route('report.export.replacement.excel')}}">Replacement Seeds</a></li>
							<li class="sub_menu"><a href="{{route('report.download_commitment_delivery.coop')}}">Local Seed Supply Analysis</a></li>
							<li class="sub_menu"><a href="{{route('delivery_dashboard.all.coop')}}">Cooperative Delivery Report</a></li>
							
						</ul>
					</li>
					<!--<li class="sub_menu"><a href="{{route('data.yield.home')}}">Yield Report</a></li>-->
					@endif
					
					<li class="sub_menu"><a href="{{route('report.variety.overall')}}">Seed Variety Report</a></li>
					<li class="{{ @$report_dis_summary != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.iar_table')}}">Generate IAR</a></li>
					
					<li class="{{ @$bufferInventoryformview != '' ? 'active' : '' }}"><a href=" {{route('bufferInventoryformview')}}  ">IAR (Replacement)</a></li>
					<li class="sub_menu"><a id="extractDeletedIARs">Extract Deleted IARs</a></li>
					
					@if(Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->roles->first()->name == "system-admin") 
					<li class="sub_menu"><a href="{{route('farmer_profile.contact.statinfo')}}">Farmer With Contact Information (Statistics)</a></li>
					@endif
					
					@if (Auth::user()->can('acc-list'))
					<li class="{{ @$report_form != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.acc_iar_table')}}  ">Accountant IAR</a></li>
					@endif
				</ul>
			</li>
						<!--<li><a><i class="fa fa-cogs"></i> Settings <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('system.settings.archive')}}">Archive</a></li>
								<li><a href="{{route('system.settings.qrcode')}}">QR Code</a></li>
								<li><a href="{{route('system.settings.distribution')}}">Distribution</a></li>
							</ul>
						</li>-->
					   <?php
					 //  echo "<script>alert(". Auth::user()->roles->first()->name.") </script>";
					   $prov = substr(Auth::user()->province,0,2);
						if(Auth::user()->province!="" and (Auth::user()->roles->first()->name == "rcef-programmer" or Auth::user()->roles->first()->name == "administrator"  or Auth::user()->roles->first()->name == "rcef-pmo")){
	

									    $curr =  basename(getcwd());
									    $curr = str_replace("rcef_", "", $curr);
									    if(strlen($curr) == 6){
									      $curYr =  intval(substr($curr, 2, 4));
									      $curSeason = strtoupper(substr($curr, 0, 2));
									        if($curSeason=="DS"){
									          $prvSeason = "WS";
									          $prvYr = $curYr - 1;
									        }elseif($curSeason=="WS"){
									          $prvSeason = "DS";
									          $prvYr = $curYr;
									        }

									        $currentSeason = $curSeason.$curYr;
									        $previousSeason = $prvSeason.$prvYr;


									    }else{
									    	$currentSeason = "Current";
									    	$previousSeason = "Previous";
									    }

    
    
 

					   ?>
							<!--<li><a href="{{route('rcef.transfers')}}"><i class="fa fa-refresh"></i> Transfers </a></li>-->
							 
							<li><a><i class="fa fa-refresh"></i> Transfers <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li><a href="{{route('rcef.transfers')}}">{{$previousSeason}}-{{$currentSeason}} </a></li>
									<li><a href="{{route('rcef.transfers.ws2020')}}">{{$currentSeason}}-{{$currentSeason}} </a></li>

						
								</ul>	
							</li>

						
							
							{{-- <li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li> --}}
						<?php
						}
						else if(Auth::user()->province!="") {
							?>
							{{-- <li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li> --}}
							<?php
						}
						?>
					@endif



          

						

			@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "system-admin"||Auth::user()->roles->first()->name == "data-officer"||Auth::user()->roles->first()->name == "administrator")
            {{-- <li><a href="{{route('distribution.replacement')}}"><i class="fa fa-stack-exchange"></i> Open for Replacement</a></li> --}}
			<li><a><i class="fa fa-cogs"></i> Utility <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							<li><a><i class=""></i> Troubleshooting <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
								
									@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer")
										<li><a href="#" data-toggle="modal" data-target="#utilDel_modal">Cancel Delivery</a></li>
									@endif									
									@if(Auth::user()->roles->first()->name == "rcef-programmer")
										<li><a href="{{route('delivery_web.cancel.home')}}"> Cancel Confirmed Deliveries</a></li>
										<li><a href="{{route('utility.select_area.view')}}"> Area Troubleshooting UI</a></li>
											
									@endif
									@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "data-officer")
										<li><a href="#" data-toggle="modal" data-target="#iar_print_log">Reset printed IAR</a></li>
									@endif
									
								</ul>
							</li>

							@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "data-officer")
							<li><a><i class=""></i> Monitoring <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li class="sub_menu"><a href="{{route('pendingBatch.index')}}">Pending Deliveries</a></li> 
									<li class="sub_menu"><a href="{{route('cancelledBatch.index')}}">Cancelled Deliveries</a></li> 
									<li><a href="{{route('HistoryMonitoring.index')}}"> Transfer Data List </a></li>
								</ul>
							</li>
							@endif
							

							{{-- @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "data-officer")
								<li><a href="#" data-toggle="modal" data-target="#iar_print_log">Reset printed IAR</a></li>
							@endif --}}


							 @if(Auth::user()->roles->first()->name == "rcef-programmer")
                             
                           	

                        	<!-- <li class="sub_menu"><a href="{{route('process.report.index')}}">Report Reconcilation</a></li> 	-->
							
							<li><a href="{{route('sg.list')}}"><i class="fa fa-tags"></i> Blacklist SG</a></li> 		
					

                            <li class="sub_menu"><a href="{{route('farmer.profile.puller.index')}}">Farmer Profile (Area Update) </a></li>

							





							<li> <a><i class=""></i> Import <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li class="sub_menu"><a href="{{route('import.seed_growers')}}">Seed Growers</a></li> 
									<li class="sub_menu"><a href="{{route('import.rla')}}">RLA</a></li> 							
									<li class="sub_menu"><a href="{{route('import.ebinhi')}}">E-Binhi</a></li> 							
									<li class="sub_menu"><a href="{{route('import.ebinhi.update.status')}}">E-Binhi update status</a></li> 							
							
							
									@if(Auth::user()->roles->first()->name == "rcef-programmer" )
									<li class="sub_menu"><a href="{{route('import.release_uploader')}}">Distribution Data </a></li> 							
									@endif
								</ul>
							</li>


							<li class="sub_menu"><a href="{{route('farmer_profile.with.contact.nationwide')}}">Farmer W/ Contact Counter Nationwide process</a></li> 
                            
							@endif

							@if(Auth::user()->roles->first()->name == "administrator")
							<li class="sub_menu"><a href="{{route('replacements')}}">Replacement Seeds Tagging</a></li> 
							@endif
							@if(Auth::user()->roles->first()->name == "rcef-programmer")
								<li class="sub_menu"><a href="{{route('customExportUI')}}">Custom Export UI</a></li> 
								<li class="sub_menu"><a href="{{route('nrp.export.index')}}">NRP Distribution Report</a></li> 
								<li class="sub_menu"><a href="{{route('station.monitoring')}}">Server Monitoring</a></li> 
								<li class="sub_menu"><a href="{{route('icts-farmer-finder-rsbsa')}}">Farmer Verifier</a></li> 
								<li class="sub_menu"><a href="{{route('replacements')}}">Replacement Seeds Tagging</a></li> 
								<li class="sub_menu"><a href="{{route('fcaTagging')}}">Farmer Profile Tagging</a></li> 
								<li class="sub_menu"><a href="{{route('addSeedGrower')}}">Add Seed Grower</a></li> 
								<li class="sub_menu"><a href="{{route('deletePreReg')}}">Delete Pre-Registration Data</a></li> 
								<li class="sub_menu"><a href="{{route('bepToReg')}}">BeP to Conventional Profile Tagging</a></li> 
								<li class="sub_menu"><a href="{{route('deliveryStatus.home')}}">Cooperative Delivery Status</a></li> 
								
								
			
								@elseif(Auth::user()->username == "NUEVAECIJA_Jhoemar")
								
								@endif

						



				</ul>
			</li>
			@endif





			@if(Auth::user()->username == "rs.jandoc")
			<li><a><i class="fa fa-cogs"></i> Utility <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
						<li class="sub_menu"><a href="{{route('customExportUI')}}">Custom Export UI</a></li>
						<li class="sub_menu"><a href="{{route('deletePreReg')}}">Delete Pre-Registration Data</a></li> 
						<li class="sub_menu"><a href="{{route('bepToReg')}}">BeP to Conventional Profile Tagging</a></li> 


			@endif
			@if(Auth::user()->username == "NUEVAECIJA_Jhoemar")
			<li><a><i class="fa fa-cogs"></i> Utility <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
						<li class="sub_menu"><a href="{{route('addSeedGrower')}}">Add Seed Grower</a></li>

			@endif





					
                </ul>
            </div>

			<div class="menu_section">
				<h3>Sidebar Settings</h3>
				<ul style="padding-left: 0rem;">
					<li style="list-style: none; margin: 0; padding: 0; display: flex; align-items: center;">
						<label class="switch" style="cursor: pointer;">
							<input type="checkbox" id="darkModeToggle">
							<span class="slider round"></span>
						</label>
						<label class="forDarkModeLabel" style="margin-left: -2rem; display: flex; align-items: center; cursor: pointer;" for="darkModeToggle"><i class="fa fa-adjust" aria-hidden="true"></i> <span id="darkModeLabel"></span></label>
					</li>
				</ul>
			</div>

<?php
	$userManagement = array();
	// $userManagement["agusan.admin"] = "agusan.admin";
	// $userManagement["jc.felix"] = "jc.felix";
	// $userManagement["Kavin04"] = "Kavin04";
	// $userManagement["r.javines"] = "r.javines";
	// $userManagement["h.bansilan"] = "h.bansilan";
	// $userManagement["lb.admin"] = "lb.admin";
	// $userManagement["J.abas"] = "J.abas";
	// $userManagement["kavin04"] = "kavin04";
 ?>
            {{-- USER MANAGEMENT --}}
            @if(isset($userManagement[Auth::user()->username]) || Auth::user()->roles->first()->name == "rcef-programmer")

            <div class="menu_section">
                <h3>User Management</h3>
                <ul class="nav side-menu">
                    <li><a href="{{route('users.index')}}"><i class="fa fa-user"></i> Users </a></li>
					<li><a href="{{route('users.approval')}}"><i class="fa fa-check-square-o" aria-hidden="true"></i>User Request List </a></li>
					
					
<!--                    @if (Auth::user()->can('role-list'))
                    <li><a href="{{route('roles.index')}}"><i class="fa fa-user"></i> Roles </a></li>
                    @endif
                    @if (Auth::user()->can('permission-list'))
                    <li><a href="{{route('permissions.index')}}"><i class="fa fa-user"></i> Permissions </a></li>
                    @endif-->
                </ul>
         

            </div>
            @endif

           <!-- if not technodemo -->
       

        @endif 
        </div>
        <!-- /sidebar menu -->


    </div>
</div>


<!-- notice of delivery modal -->
<div class="modal fade bs-example-modal-md" id="noticeOfDeliveryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><b>Farmer Details (Performance, Affiliations, etc.)</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-6">
                        <div class="btn-toolbar" data-role="editor-toolbar"
                             data-target="#editor">
                            ...
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- notice modal -->
<div class="modal fade bs-example-modal-md" id="noticePage404" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><b>Notice</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
					<div class="col-12">
						<center>Sorry! This module isn't ready yet! Come back later!</center>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Notification Modal -->
<div id="notification_modal" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 25%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title">
          <span>Notification Settings</span><br/>
        </h4>
      </div>
      <div class="modal-body">
        <input type="checkbox" id="email">
        <label for="email"> Send e-mail alert</label><br>
        <input type="checkbox" id="sms">
        <label for="sms"> Send SMS alert</label><br>
        <button id="saveNotifSettings" type="button" class="btn btn-success btn-sm" data-dismiss="modal">
            SAVE
          </button>
        <button type="button" class="btn btn-sm" data-dismiss="modal">
            CANCEL
          </button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('public/js/jquery.min.js') }}"></script>
<script>
	checkCurrentTheme();
	function checkCurrentTheme(){
		if(localStorage.getItem("currentTheme") === "light" || !localStorage.getItem("currentTheme")){
			$(".menu_section").removeClass("dark_mode");
			$(".profile").removeClass("dark_mode");
			$("#darkModeLabel").text("Toggle Dark Mode");
		}
		else{
			$(".menu_section").addClass("dark_mode");
			$(".profile").addClass("dark_mode");;
			$("#darkModeLabel").text("Toggle Light Mode");
		}
	}

	$("#darkModeToggle").change(function() {
		if(localStorage.getItem("currentTheme") === "light" || !localStorage.getItem("currentTheme")){
			localStorage.setItem("currentTheme", "dark");
			checkCurrentTheme();
		}else{
			localStorage.setItem("currentTheme", "light");
			checkCurrentTheme();
		}
	});


	//Payments

	$("#notification_modal").on("shown.bs.modal", function (e) {
        $.ajax({
                    type: 'GET',
                    url: "{{ route('checkNotifSetting') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(data){ 
                        if(data[0].status==1||data[0].status=='1') {
                                $("#email").prop("checked", true);
                            }
                            else{
                                $("#email").prop("checked", false);
                            }

                        if(data[1].status==1||data[1].status=='1') {
                                $("#sms").prop("checked", true);
                            }
                            else{
                                $("#sms").prop("checked", false);
                            }
                    }
                });
  });


  $("#saveNotifSettings").on("click", function () {
	  var email = $("#email").prop("checked");
	  var sms = $("#sms").prop("checked");
	  
	  $.ajax({
		  type: 'GET',
		  url: "{{ route('updateNotifSetting') }}",
		  dataType: "json",
		  data: {
			  _token: "{{ csrf_token() }}",
			  email : email,
			  sms : sms
			},
			success: function(data){ 
				console.log(data);
			}
		});
	});

	$("#extractDeletedIARs").on("click", function () {
		window.open(`https://rcef-seed.philrice.gov.ph/rcef_ws2025/extractDeletedIARs`);
	});
</script>
