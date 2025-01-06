<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="#" class="site_title" style="height: auto;    text-align: center;">
                <div class="col-md-6" style="font-size: 16px;    padding: 0;    font-weight: bold;">
                    RCEF WS2020
                </div>
                <div class="col-md-6" style="    margin: 0;    padding: 0;">
                    <img src="{{ asset('public/images/rcef_LOGO_ws2020.png') }}" alt="..." class="img-circle profile_img" style="width: 4vw !important;    border-radius: 7%;    text-align: center;margin-right: 2vw;    margin-top: .2vw;">
                </div>
            </a>
        </div>

        <div class="clearfix"></div>

        <!-- menu profile quick info -->
        <div class="profile clearfix">
            <div class="profile_pic">
                @if(Auth::user()->sex == "M")
				  <img src="{{ asset('public/images/male_farmer.png') }}" alt="..." class="img-circle profile_img">
				@else
				  <img src="{{ asset('public/images/female_farmer.png') }}" alt="..." class="img-circle profile_img">
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
            <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
					@if(Auth::user()->roles->first()->name == "system-encoder")
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						@if (Auth::user()->province)
							<li><a href="{{url('/releasing')}}"><i class="fa fa-share"></i> Distribution - Transferred DS 2019 seeds</a></li>
							<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution - WS 2020 Seeds</a></li>
							<li><a href=" {{route('farmer.id.home')}} "><i class="fa fa-qrcode"></i> Farmer ID </a></li>
							<li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li>
						@endif
						
					@elseif(Auth::user()->roles->first()->name == "da-icts")
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						<li><a href="{{route('dashboard.delivery_summary')}}"><i class="fa fa-dashboard"></i> Delivery Summary </a></li>
						<li><a><i class="fa fa-database"></i> Seed Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display:block">
							  <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
							</ul>
						</li>
                    @else
						<!-- OTHER USERS -->
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						<li><a href="{{url('/DeliveryDashboard')}}"><i class="fa fa-dashboard"></i> Delivery Dashboard </a></li>
						<li><a href="{{route('station_report.home')}}"><i class="fa fa-dashboard"></i> Station Dashboard </a></li>
						<li><a href="{{url('/insp_monitoring')}}"><i class="fa fa-search-plus"></i> Inspection monitoring </a></li>
						<li><a href=" {{route('farmer.id.home')}} "><i class="fa fa-qrcode"></i> Farmer ID </a></li>
						@if (Auth::user()->province)
							<li><a href="{{url('/releasing')}}"><i class="fa fa-share"></i> Distribution - Transferred DS 2019 seeds</a></li>
							<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution - WS 2020 Seeds</a></li>
						@endif
						@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2)
							<li><a href="{{route('delivery_web.cancel.home')}}"><i class="fa fa-power-off"></i> Cancel Delivery</a></li>
						@endif
						
						<li><a><i class="fa fa-users"></i> Farmer Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('farmer_profile.home')}}">DS 2019</a></li>
							</ul>
						  </li>
						
						<li><a><i class="fa fa-users"></i> Seed Cooperative<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('coop.commitment')}}">Commitment</a></li>
								@if(Auth::user()->roles->first()->name == "system-admin")
									<li><a href="{{route('coop.dashboard')}}">Dashboard</a></li>
									<li><a href="{{route('coop.rla')}}">RLA Details</a></li>
								@endif
							</ul>
						</li>

						<li class="{{ @$inspection_side != '' ? 'active' : '' }}"><a><i class="fa fa-eye"></i> Inspection <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$inspection_side != '' ? 'display:block' : 'display:none' }} ">
								<!--<li class="{{ @$inspection_verification != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.registration')}}  ">Registration</a></li>-->
								<li class=""><a href="{{route('rcef.inspector.schedule')}}">Change Inspector</a></li>
								<li class="{{ @$inspection_form != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.designation2')}}  ">Assign Inspector</a></li>
							</ul>
					    </li>

						<li class="{{ @$report_side != '' ? 'active' : '' }}"><a><i class="fa fa-database"></i> Reports <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$report_side != '' ? 'display:block' : 'display:none' }} ">
								@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2)
									<li class=""><a href=" {{route('rcef.report')}}" target="_blank">Manual Override (REFRESH)</a></li>
								@endif

								<li class=""><a href=" {{route('rcef.report.beneficiaries')}}  ">Distribution Server</a></li>
								  <li><a>Seed Beneficiary<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
									</ul>
								  </li>
								  <li><a>Seed Variety<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  <li class="sub_menu"><a href="{{route('report.variety.overall')}}">Overall Summary</a></li>
									  <li class="sub_menu"><a href="{{route('report.variety.dop')}}">Per dropoff point</a></li>
									</ul>
								  </li>
								
								<li class="{{ @$report_dis_summary != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.iar_table')}}  ">IAR</a></li>
								@if (Auth::user()->can('acc-list'))
								<li class="{{ @$report_form != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.acc_iar_table')}}  ">Accountant IAR</a></li>
								@endif
							
							</ul>
						</li>
						<li><a><i class="fa fa-cogs"></i> Settings <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<!--<li><a href="{{route('system.settings.archive')}}">Archive</a></li>-->
								<li><a href="{{route('system.settings.qrcode')}}">QR Code</a></li>
								<li><a href="{{route('system.settings.distribution')}}">Distribution</a></li>
							</ul>
						</li>
					   <?php
					   $prov = substr(Auth::user()->province,0,2);
						if(Auth::user()->province!="" and ($prov != '03' or Auth::user()->userId == 84 or Auth::user()->userId == 161 or Auth::user()->userId == 28 or Auth::user()->userId == 370)){
	
					   ?>
							<!--<li><a href="{{route('rcef.transfers')}}"><i class="fa fa-refresh"></i> Transfers </a></li>-->
							<li><a><i class="fa fa-refresh"></i> Transfers <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li><a href="{{route('rcef.transfers')}}">DS2019-WS2020 </a></li>
									<li><a href="{{route('rcef.transfers.ws2020')}}">WS2020-WS2020 </a></li>
								</ul>
							</li>
							
							<li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li>
						<?php
						}
						else if(Auth::user()->province!="") {
							?>
							<li><a href="{{route('rcef.checking')}}"><i class="fa fa-check"></i> Checking </a></li>
							<?php
						}
						?>
					@endif

                </ul>
            </div>

            {{-- USER MANAGEMENT --}}
            @if(Auth::user()->roles->first()->name == "system-admin")
            <div class="menu_section">
                <h3>User Management</h3>
                <ul class="nav side-menu">
                    <li><a href="{{route('users.index')}}"><i class="fa fa-user"></i> Users </a></li>
<!--                    @if (Auth::user()->can('role-list'))
                    <li><a href="{{route('roles.index')}}"><i class="fa fa-user"></i> Roles </a></li>
                    @endif
                    @if (Auth::user()->can('permission-list'))
                    <li><a href="{{route('permissions.index')}}"><i class="fa fa-user"></i> Permissions </a></li>
                    @endif-->
                </ul>
            </div>
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
