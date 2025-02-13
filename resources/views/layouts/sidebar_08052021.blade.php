<div class="col-md-3 left_col">
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
                    <img src="{{ asset('public/images/rcef_LOGO_ds2021.png') }}" alt="..." class="img-circle profile_img" style="width: 4vw !important;    border-radius: 7%;    text-align: center;margin-right: 2vw;    margin-top: .2vw;">
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
        	  @if(Auth::user()->roles->first()->name != "techno_demo_encoder")
            <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
           
					@if(Auth::user()->roles->first()->name == "system-encoder")
						<li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard </a></li>
						@if (Auth::user()->province)
							<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution </a></li>
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
                    @elseif(Auth::user()->roles->first()->name == "coop-operator")
					  <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Coop Dashboard </a></li>
					  <li><a href="{{route('coop_operator.deliveries')}}"><i class="fa fa-truck"></i> Coop Deliveries </a></li>
					  <li><a href="{{route('coop_operator.sg_enrollment')}}"><i class="fa fa-legal"></i> SG Enrollment</a></li>
					  <li><a href="{{route('coop_operator.sg_matrix')}}"><i class="fa fa-cubes"></i> SG Matrix</a></li>
					  <li><a href="{{route('coop_operator.report')}}"><i class="fa fa-book"></i> Coop Report</a></li>
					  
					@elseif(Auth::user()->roles->first()->name == "sed-caller")
					  <li><a href="{{url('/sed/farmers/all')}}"><i class="fa fa-users"></i> Farmers </a></li>  
					@elseif(Auth::user()->roles->first()->name == "sed-caller-manager")
					  <li><a href="{{url('/sed/dashboard')}}"><i class="fa fa-calendar"></i> Dashboard </a></li>  
					  <li><a href="{{url('/sed/farmers')}}"><i class="fa fa-check"></i> Verified Farmers </a></li>  
					  <li><a href="{{url('/sed/manage/farmer')}}"><i class="fa fa-list"></i> Farmer List </a></li>  
					  <li><a href="{{url('/sed/manage')}}"><i class="fa fa-users"></i> SED Users </a></li>  
					
					@elseif(Auth::user()->roles->first()->name == "bpi-nsqcs")
					  <li><a href="{{route('coop.rla.pmo')}}"><i class="fa fa-eye"></i> Monitor RLA </a></li>
					  <li><a href="{{route('coop.rla.bpi')}}"><i class="fa fa-plus-circle"></i> ADD RLA </a></li>
					@else
						
						<!-- OTHER USERS -->
						<li><a><i class="fa fa-dashboard"></i> Dashboards<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{url('/DeliveryDashboard')}}">Delivery Dashboard </a></li>
								<li><a href="{{route('station_report.home')}}">Station Dashboard </a></li>
								<li><a href="{{route('coop.dashboard')}}">Coop Dashboard</a></li>
								<li><a href="{{route('rcep.distribution.dashboard')}}">Distribution Dashboard</a></li>
								<li><a href="{{route('payment_dashboard.home')}}">Payment Dashboard</a></li>
								<li><a href="{{route('dashboard.gad.view')}}">GAD Dashboard</a></li>

								@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->roles->first()->name == "rcef-programmer" )
								<li><a href="{{route('analytics.home')}}"> Analytics Page</a></li>

								@endif

								@if(Auth::user()->roles->first()->name == "system-admin")
								<li><a href="{{route('rla.monitoring.home')}}"> RLA Dashboard</a></li>

									<!-- <li><a href="{{route('coop.rla')}}">Upload RLA (BATCH)</a></li> -->
								@endif


							</ul>
						</li>
						
						
						
					
						
					
						

							<li><a><i class="fa fa-list"></i> Generate FLSAR<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="#" data-toggle="modal" data-target="#flsar_modal">Locate FLSAR</a></li>
							  <li><a href="{{route('FarGenerationPs.index')}}"><i class="fa fa-list"></i> Generate FLSAR</a></li>
							   @if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga")
									 <li><a href="{{route('FarGeneration.index')}}"><i class="fa fa-list"></i> Generate Current Season FLSAR</a></li>
								 @endif

							</ul>
						  </li>
						



						<li><a><i class="fa fa-globe"></i> Distribution Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  @if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "racariaga" || Auth::user()->username == "19-0922" || Auth::user()->username == "kjgdeleon")
								<li><a href="{{route('distribution.app.stocks_home')}}">Release Stocks</a></li>
								<li><a href="{{route('distribution.app.stocks_seedType')}}">Change Seed Type</a></li>
							
							  @else
								<li><a href="{{route('distribution.app.stocks_home_public')}}">Stocks Monitoring</a></li>
							  @endif
							  	<li><a href="{{route('stocks.monitoring.index')}}">Stocks Monitoring Per Seed Variety</a></li>


							</ul>
						</li>
						
						@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->userId == 2618 || Auth::user()->roles->first()->name == "data-officer" || Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->username == "kjgdeleon" || Auth::user()->roles->first()->name == "rcef-programmer" )
						<li><a><i class="fa fa-google"></i> RCEF Google Sheet<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li class="sub_menu"><a href="{{route('rcep.google_sheet.summary')}}">Summary</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.google_sheet.schedule_home')}}">Data-Entry</a></li>
							  <li class="sub_menu"><a href="{{route('rcep.google_sheet.dashboard')}}">Dashboard</a></li>
							  <!--<li class="sub_menu"><a href="{{route('rcep.google_sheet.weekly_home')}}">Weekly Report</a></li>-->
							</ul>
						  </li>
						@endif
						
	
						<li><a><i class="fa fa-binoculars"></i> e-Binhi Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('paymaya.seed_distribution')}}">Beneficiary List</a></li>
							  <!--<li><a href="{{route('paymaya.inspector_ui')}}">Inspector Interface</a></li>-->
						
							  <!--<li><a href="{{route('paymaya.variety_report')}}">Variety Report</a></li>-->
							  <li class="sub_menu"><a href="{{route('paymaya.beneficiary_report')}}">Beneficiary Reports</a></li>
							   <li><a href="{{route('paymaya.beneficiary.codes')}}"><i class="fa fa-barcode"></i> Beneficiary List with Codes</a></li>

							   @if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" ||  Auth::user()->username == "kjgdeleon" )
									<li><a href="{{route('far.ebinhi.ui')}}"><i class="fa fa-list"></i> Generate e-Binhi FLSAR</a></li>
									<li class="sub_menu"><a href="{{route('upload.paymaya.process.index')}}" onclick="return confirm('Proceed Processing Paymaya Codes?')">Process Paymaya Code</a></li> 
                            
									<li><a href="#" data-toggle="modal" data-target="#paymaya_tags_modal">Excess QR Codes</a></li>
									
								 @endif

							</ul>
						  </li>
					 
						 
						
						<li><a><i class="fa fa-truck"></i> Seed Deliveries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{url('/')}}">Delivery Schedule</a></li>
								@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2)
								<li><a href="{{route('edit_delivery.home')}}">Edit Delivery</a></li>
								@endif
								@if(Auth::user()->roles->first()->name == "system-admin")
									 <li><a href="{{route('coop.rla')}}">Upload RLA (BATCH)</a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "coop-operator")
									 <li><a href="{{route('coop.rla.manual')}}">Upload RLA (MANUAL)</a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->username == "NUEVAECIJA_Jhoemar"  || Auth::user()->username == "e.lopez" )
									 <li><a href="{{route('coop.rla.bpi')}}">ADD RLA </a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-pmo")
									<li><a href="{{route('coop.rla.pmo')}}">Monitor RLA</a></li>
									<li><a href="{{route('coop.rla.approve_home')}}">Approve RLA</a></li>
								@endif
								
								@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->userId == 2618 || Auth::user()->roles->first()->name == "system-admin")
									 <li><a href="{{route('coop.rla.edit')}}">EDIT RLA</a></li> 
								@endif
							</ul>
						</li>
						
						
						<li><a href=" {{route('farmer.id.home')}} "><i class="fa fa-qrcode"></i> QR Code Generation </a></li>
						@if (Auth::user()->province)
							
							<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution </a></li>
										
						@endif
						
						<li><a><i class="fa fa-users"></i> Farmer Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('farmer_profile.home')}}">WS 2020</a></li>
							  <li><a href="{{route('farmer_profile.home.list')}}">WS 2020 x DS 2021 List</a></li>
							  <li><a href="{{route('farmer_profile.cross.check')}}">WS 2020 x DS 2021 x WS 2021 List</a></li>
							
							 

							</ul>
						  </li>

						 
						 @if(Auth::user()->roles->first()->name == "kjgdeleon" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "19-0922")
						  <li><a href="{{route('palaysikatan.farmers')}}"><i class="fa fa-list-alt"></i>Palaysikatan</a></li>
						  @endif

						  @if(Auth::user()->roles->first()->name == "encoder_yield" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" || Auth::user()->username == "rm.capiroso")
							  	 <li><a href="{{route('encoder.yield.home')}}"><i class="fa fa-pencil-square-o"></i>Yield Updating</a></li>
							  @endif


						<li><a><i class="fa fa-users"></i> Seed Cooperative<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('coop.commitment')}}">Commitment</a></li>
							</ul>
						</li>

						<li class="{{ @$inspection_side != '' ? 'active' : '' }}"><a><i class="fa fa-eye"></i> Inspection <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$inspection_side != '' ? 'display:block' : 'display:none' }} ">
								<!--<li class="{{ @$inspection_verification != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.registration')}}  ">Registration</a></li>-->
								<li class=""><a href="{{route('rcef.inspector.schedule')}}">Change Inspector</a></li>
								<li class="{{ @$inspection_form != '' ? 'active' : '' }}"><a href=" {{route('rcef.inspection.designation2')}}  ">Assign Inspector</a></li>
								<li><a href="{{url('/insp_monitoring')}}">Inspection monitoring </a></li>
							</ul>
					    </li>

						<li class="{{ @$report_side != '' ? 'active' : '' }}"><a><i class="fa fa-database"></i> Reports <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$report_side != '' ? 'display:block' : 'display:none' }} ">
								@if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2)
									<!--<li class=""><a href=" {{route('rcef.report')}}" target="_blank">Manual Override (REFRESH)</a></li>-->
								@endif

								<!--<li class=""><a href=" {{route('rcef.report.beneficiaries')}}  ">Distribution Server</a></li>-->
								  <li><a>Seed Beneficiary<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
									  <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#export_nrp">NRP</a></li>
									</ul>
								  </li>
								  
								 @if(Auth::user()->username == "jpalileo"  || Auth::user()->username == "19-0922" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" || Auth::user()->username == "rm.capiroso") 
									 <li><a>Allocation vs Delivery<span class="fa fa-chevron-down"></span></a>
											<ul class="nav child_menu">
											  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'regional')}}">Regional</a></li>
											  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'provincial')}}">Provincial</a></li>
											  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'municipal')}}">Municipal</a></li>
											</ul>
									  </li>
							    @endif


								  @if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2)
								  <li class="sub_menu"><a href="{{route('report.area_range')}}">Area Range</a></li>
							      @endif
								  
								  <li class="sub_menu"><a href="{{route('report.variety.overall')}}">Seed Variety</a></li>
								
								<li class="{{ @$report_dis_summary != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.iar_table')}}  ">IAR</a></li>
								
								@if(Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->roles->first()->name == "system-admin") 
								  <li class="sub_menu"><a href="{{route('farmer_profile.contact.statinfo')}}">Farmer With Contact Information (Statistics)</a></li>
							    @endif
								
								@if (Auth::user()->can('acc-list'))
								<li class="{{ @$report_form != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.acc_iar_table')}}  ">Accountant IAR</a></li>
								@endif
								
								
								@if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" || Auth::user()->username == "rm.capiroso" )
								 	
							 

								 <li><a>Excel Export<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  <li class="sub_menu"><a href="{{route('report.export.statistics.municipal')}}">Municipal Statistics</a></li>
									  <li class="sub_menu"><a href="{{route('report.export.replacement.excel')}}">Replacement Seeds</a></li>
									</ul>
								  </li>
								   <li class="sub_menu"><a href="{{route('data.yield.home')}}">Yield Report</a></li>

								  

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
					 //  echo "<script>alert(". Auth::user()->roles->first()->name.") </script>";
					   $prov = substr(Auth::user()->province,0,2);
						if(Auth::user()->province!="" and ($prov != '03' or Auth::user()->userId == 84 or Auth::user()->userId == 161 or Auth::user()->userId == 28 or Auth::user()->userId == 370 or Auth::user()->userId == 1959 or Auth::user()->userId == 2 or Auth::user()->roles->first()->name == "administrator" )){
	

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

							@if(Auth::user()->roles->first()->name == "system-admin" ||  Auth::user()->roles->first()->name == "data-officer")
								<li class="sub_menu"><a href="{{route('view.report.break_down.index')}}">Second Inspection Result</a></li>
							@endif

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



          




            <li><a><i class="fa fa-wrench"></i> Utility <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							<li> <a><i class=""></i> Delivery Transaction <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li class="sub_menu"><a href="{{route('pendingBatch.index')}}">Pending Delivery List</a></li> 
									<li class="sub_menu"><a href="{{route('cancelledBatch.index')}}">Cancelled Delivery List</a></li> 
								 @if(Auth::user()->roles->first()->name == "system-admin")
								<li><a href="#" data-toggle="modal" data-target="#utilDel_modal">Cancel Delivery</a></li>
								@endif	
								
								@if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" ||  Auth::user()->username == "kjgdeleon")
								<li><a href="{{route('delivery_web.cancel.home')}}"> Cancel Confirmed Deliveries</a></li>	

								@endif



								</ul>
							</li>
							<li><a href="{{route('HistoryMonitoring.index')}}"> Transfer Data List </a></li>

							@if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" ||  Auth::user()->username == "kjgdeleon"  || Auth::user()->roles->first()->name == "data-officer")
							<li><a href="#" data-toggle="modal" data-target="#iar_print_log">Reset printed IAR</a></li>
							@endif


							 @if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" ||  Auth::user()->username == "kjgdeleon")
                             
                           	

                        	<li class="sub_menu"><a href="{{route('process.report.index')}}">Report Reconcilation</a></li> 	
							
							<li><a href="{{route('sg.list')}}"><i class="fa fa-tags"></i> Blacklist SG</a></li> 		
					

                            <li class="sub_menu"><a href="{{route('farmer.profile.puller.index')}}">Farmer Profile (Area Update) </a></li>

							





							<li> <a><i class=""></i> Import <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li class="sub_menu"><a href="{{route('import.seed_growers')}}">Seed Growers</a></li> 
									<li class="sub_menu"><a href="{{route('import.rla')}}">RLA</a></li> 							
								</ul>
							</li>


							<li class="sub_menu"><a href="{{route('farmer_profile.with.contact.nationwide')}}">Farmer W/ Contact Counter Nationwide process</a></li> 
                            
							@endif

							@if(Auth::user()->roles->first()->name == "rcef-programmer")
					
							@endif



				</ul>
			</li>












					
                </ul>
            </div>

<?php
	$userManagement = array();
	$userManagement["19-0922"] = "19-0922";
	$userManagement["jpalileo"] = "jpalileo";
	$userManagement["rd.rimandojr"] = "rd.rimandojr";
	$userManagement["racariaga"] = "racariaga";
	$userManagement["kjgdeleon"] = "kjgdeleon";
	$userManagement["agusan.admin"] = "agusan.admin";
	$userManagement["jc.felix"] = "jc.felix";
	$userManagement["Kavin04"] = "Kavin04";
	$userManagement["r.javines"] = "r.javines";
	$userManagement["h.bansilan"] = "h.bansilan";
	$userManagement["lb.admin"] = "lb.admin";
	$userManagement["J.abas"] = "J.abas";
	$userManagement["kavin04"] = "kavin04";
	
 ?>
            {{-- USER MANAGEMENT --}}
            @if(isset($userManagement[Auth::user()->username]))
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

           <!-- if not technodemo -->
        @else
        	<!-- if technodemo -->
        	<div class="menu_section">
                <h3>GENERAL</h3>
                <ul class="nav side-menu">
                    <li><a href="{{route('dashboard.index')}}"><i class="fa fa-user"></i> Dashboard </a></li>
					<li><a href="{{route('palaysikatan.farmers')}}"><i class="fa fa-list-alt"></i> Palaysikatan </a></li>
					
					
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
