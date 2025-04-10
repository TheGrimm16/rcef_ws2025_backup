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
                <h3>GENERAL</h3>
                <ul class="nav side-menu">
                    <li><a href="{{route('palaysikatan.dashboard.index')}}"><i class="fa fa-user"></i> Dashboard </a></li>
					<li><a href="{{route('palaysikatan.farmers')}}"><i class="fa fa-list-alt"></i> Palaysikatan </a></li>
					
					
					@if( Auth::user()->username == "justine.ragos" || Auth::user()->roles->first()->name == "techno_demo_officer")
					<li><a href="{{route('palaysikatan.tdo-data')}}"><i class="fa fa-list"></i> TDO List </a></li>		
					<li><a href="{{route('palaysikatan.calendar')}}"><i class="fa fa-calendar"></i> Calendar </a></li>				
					@endif
					

					@if( Auth::user()->username == "juvy_ann.pamp" || Auth::user()->username == "christine.damaso"  )
					<li><a><i class="fa fa-folder-open"></i> Generate FAR<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('FarGenerationPs.index')}}">Generate Municipal FAR</a></li> 
							</ul>
						</li>
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

			{{-- start paymnet processor  --}}
			@elseif(Auth::user()->roles->first()->name == "ces_payment_processor")
			<div class="menu_section">
				<h3>GENERAL</h3>
                <ul class="nav side-menu">
					<li><a href=" {{url('/')}} "><i class="fa fa-dashboard"></i> Home Page</a></li>
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
				</ul>
			</div>
			{{-- end paymnet dro  ---}}

			{{-- start paymnet accountant  --}}
			@elseif(Auth::user()->roles->first()->name == "payment_accountant")
			<div class="menu_section">
				<h3>GENERAL</h3>
                <ul class="nav side-menu">
					<li><a href=" {{url('/')}} "><i class="fa fa-dashboard"></i> Home Page</a></li>
					<li><a href="{{route('accountant.home')}}"><i class="fa fa-edit"></i>Accountant</a></li>
				</ul>
			</div>
			{{-- end paymnet accountant  --}}

            @elseif(Auth::user()->roles->first()->name == "extension_encoder")
            <?php
            	if($_SERVER['REQUEST_URI']!="/rcef_ds2024/extension/home"){
            		if($_SERVER['REQUEST_URI']!="/rcef_ds2024/fargeneration/ps"){
					   header('Location: ' . route('rcef.extension.home'), true, 303);
					   die();
            		}
            	}
            ?>


            <div class="menu_section">
                <h3>GENERAL</h3>
                <ul class="nav side-menu">
                    <li><a><i class="fa fa-puzzle-piece"></i> RCEF Extension<span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							 <li><a href="{{route('rcef.extension.home')}}">Data-Entry / Updating</a></li>
							 
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
						   	header('Location: ' . route('paymaya.seed_distribution'), true, 303);
						   die();
            			}	
            			}	
            	}
            ?>
            <div class="menu_section">
                <h3>GENERAL</h3>
                <ul class="nav side-menu">
						<li><a><i class="fa fa-binoculars"></i> e-Binhi Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
							  <li><a href="{{route('paymaya.seed_distribution')}}">Beneficiary List</a></li>
							 <!-- <li class="sub_menu"><a href="{{route('paymaya.beneficiary_report')}}">Beneficiary Reports</a></li> -->
							  <li><a href="{{route('paymaya.beneficiary.codes')}}">Beneficiary List with Codes</a></li>
							 

							</ul>
						</li>
                </ul>
            </div>
        	

            <!--START FOR GENERAL ACCESS-->
        	@else
            <div class="menu_section">
                <h3>General</h3>
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
					@elseif(Auth::user()->roles->first()->name == "bpi-nsqcs")
					  <li><a href="{{route('coop.rla.pmo')}}"><i class="fa fa-eye"></i> Monitor RLA </a></li>
					  <li><a href="{{route('coop.rla.bpi')}}"><i class="fa fa-plus-circle"></i> ADD RLA </a></li>
					@else
						
						<!-- OTHER USERS -->
						<li><a href=" {{url('/')}} "><i class="fa fa-dashboard"></i> Home Page</a></li>
						<li><a><i class="fa fa-bar-chart"></i> Analytics<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
								   <li><a href="{{route('analytics.home')}}"> Analytics Summary</a></li>
								   <li><a href="{{route('yieldCount.home')}}"> Yield Report</a></li>
								   <li><a href="{{route('palaysikatan.dashboard.index')}}"> Palaysikatan Dashboard </a></li>
								@endif
								<li><a href="{{url('/DeliveryDashboard')}}">Delivery Dashboard </a></li>
								<li><a href="{{route('station_report.home')}}">Station Dashboard </a></li>
								<li><a href="{{route('coop.dashboard')}}">Coop Dashboard</a></li>
								<li><a href="{{route('payment_dashboard.home')}}">Payment Dashboard</a></li>
								<li><a href="{{route('dashboard.gad.view')}}">GAD Dashboard</a></li>
							</ul>
						</li>
						
						<li><a><i class="fa fa-flask"></i> RLA Management<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('rla.monitoring.home')}}"> RLA Monitoring</a></li>
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<!--<li><a href="{{route('edit_delivery.home')}}">Edit Delivery</a></li>-->
								@endif
								@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer")
									<li><a href="{{route('coop.rla')}}">Batch RLA Upload</a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "coop-operator" || Auth::user()->roles->first()->name == "rcef-programmer")
									<li><a href="{{route('coop.rla.manual')}}">Upload RLA (MANUAL)</a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "NUEVAECIJA_Jhoemar"  || Auth::user()->username == "e.lopez" )
									 <li><a href="{{route('coop.rla.bpi')}}">ADD RLA </a></li> 
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-pmo" || Auth::user()->roles->first()->name == "rcef-programmer")
									<li><a href="{{route('coop.rla.pmo')}}">Monitor RLA</a></li>
									<li><a href="{{route('coop.rla.approve_home')}}">Approve RLA</a></li>
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "system-admin")
									<!--<li><a href="{{route('coop.rla.edit')}}">EDIT RLA</a></li>--> 
								@endif
							</ul>
						</li>
						

						
						<li><a><i class="fa fa-folder-open"></i> Generate FAR<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
				
				
							  	
							  <li><a href="{{route('FarGenerationPs.index')}}">Generate Municipal FAR</a></li> 
							

							  @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "pnm.marcelo.pampanga.com" || Auth::user()->username == "NUEVAECIJA_Jhoemar" || Auth::user()->username == "e.lopez"  )
							  {{-- <li><a href="{{route('FarGeneration.index')}}">Current Season FAR</a></li> --}}
							  <li><a href="{{route('FarGenerationPreReg.index')}}">Pre-Registered Farmer</a></li> 
						 	 @endif

								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "bs.pungtilan" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "je.almine" || Auth::user()->username == "R.Bombase" )
									{{-- <li><a href="{{route('FarGeneration.index')}}">Current Season FAR</a></li> --}}
									<li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
								@endif

								@if(Auth::user()->username == "e.lopez")								
								<li><a href="{{route('far.ebinhi.ui')}}">Generate e-Binhi FAR</a></li>
								@endif
								

									
								  {{-- <li><a><i class="fa fa-folder-open"></i> Generate Blank FAR<span class="fa fa-chevron-down"></span></a>
								  	<ul>
								  		<li><a onclick="genBlankFAR('a3');">Municipal FAR A3</a></li>
								  		<li><a onclick="genBlankFAR('ext');">Municipal FAR Extension</a></li>
								  		
								  	</ul>
								  </li> --}}




							</ul>
						</li>
					
						
						<li><a><i class="fa fa-tasks"></i> Distribution Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  @if(Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->username == "jpalileo")
								<li><a href="{{route('distribution.app.stocks_home')}}">Release Stocks</a></li>
								<li><a href="{{route('distribution.app.stocks_seedType')}}">Change Seed Type</a></li>
								<li><a href="{{route('released.data.index')}}"> Edit\Delete Distribution Data</a></li>
								<li><a href="{{route('web.dop.maker.regular')}}">DOP Maker</a></li>	
								
								
								<li><a href="{{route('pre_reg.view_farmer')}}"> Update Farmer Information</a></li>
							
							  @else
								<li><a href="{{route('distribution.app.stocks_home_public')}}">Stocks Monitoring</a></li>
									@if(Auth::user()->roles->first()->name == "administrator")
										<li><a href="{{route('released.data.index')}}"> Edit\Delete Distribution Data</a></li>
										<li><a href="{{route('web.dop.maker.regular')}}">DOP Maker</a></li>	
									@endif
							  @endif
							  	<li><a href="{{route('stocks.monitoring.index')}}">Stocks Monitoring</a></li>


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
						
						
	
						<li><a><i class="fa fa-binoculars"></i> e-Binhi Monitoring<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								@if(Auth::user()->username == "	m.padilla" || Auth::user()->roles->first()->name == "rcef-programmer")
									  <li><a href="{{route('accountant.home')}}">e binhi Accountant</a></li>
								@endif
							  <li><a href="{{route('paymaya.seed_distribution')}}">Beneficiary List</a></li>
							  <!--<li><a href="{{route('paymaya.inspector_ui')}}">Inspector Interface</a></li>-->
						
							  <!--<li><a href="{{route('paymaya.variety_report')}}">Variety Report</a></li>-->
							  <li class="sub_menu"><a href="{{route('paymaya.beneficiary_report')}}">Beneficiary Reports</a></li>
							   

							   @if(Auth::user()->username == "bs.pungtilan" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "NUEVAECIJA_Jhoemar" || Auth::user()->username == "e.lopez" || Auth::user()->roles->first()->name == "rcef-programmer" )
							   		<li><a href="{{route('paymaya.beneficiary.codes')}}">Beneficiary List with Codes</a></li>
							   		@if(Auth::user()->roles->first()->name == "rcef-programmer")
									{{-- <li class="sub_menu"><a href="{{route('upload.paymaya.process.index')}}" onclick="return confirm('Proceed Processing Paymaya Codes?')">Process Paymaya Codes</a></li>  --}}
                            
									<li><a href="#" data-toggle="modal" data-target="#paymaya_tags_modal">Excess QR Codes</a></li>
									<li class="sub_menu"><a href="{{route('paymaya.municipalities.list')}}">Municipalities</a></li>
									@endif
								 @endif
								  @if(Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "eb.cabanisas" ||  Auth::user()->username == "dc.gaspar" ||  Auth::user()->username == "jg.villanueva"||  Auth::user()->username == "e.lopez"||  Auth::user()->username == "jt.rivera")
								 
								  
							
								  	
								  <li><a>Payments<span class="fa fa-chevron-down"></span></a>
								 
								  <ul class="nav child_menu">
								  
										  <li class="sub_menu"><a href="{{route('paymaya.reports.payments')}}">e-Binhi Payments</a></li>
										  <li class="sub_menu"><a href="{{route('paymaya.payment.reports.excel')}}">Export Batches</a></li>
										  <li><a href="{{route('paymaya.signatories')}}">e-Binhi Payments Signatories</a></li>	
								  </ul>
								</li>
							   @endif

							</ul>
						</li>
						
						<li><a><i class="fa fa-users"></i> Seed Cooperative<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('coop.commitment')}}">Commitment</a></li>
								 <li><a href="{{route('coop.rla-report')}}">Live RLA Report</a></li>


							</ul>
						</li>
						

						<li><a><i class="fa fa-users"></i> Buffer / Replacement <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								<li><a href="{{route('rcef.inspection.buffer.designation')}}">Assign Inspector (Buffer App)</a></li>
								<li><a href="{{route('rcef.buffer.inspector.schedule')}}">Change Inspector (Buffer App)</a></li>
								 <li><a href="{{route('web.dop.maker.replacement.index')}}">Create Drop Off Point (Replacement)</a></li>
								 	@if(Auth::user()->roles->first()->name == "rcef-programmer" ||  Auth::user()->roles->first()->name == "data-officer" ||  Auth::user()->roles->first()->name == "system-admin")
								<li class="sub_menu"><a href="{{route('view.report.break_down.index')}}">Second Inspection Result</a></li>
							@endif

							</ul>
						</li>




						 @if(Auth::user()->roles->first()->name == "rcef-programmer")
						<li><a><i class="fa fa-puzzle-piece"></i> RCEF Extension<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
								 <li><a href="{{route('rcef.extension.home')}}">Data-Entry / Updating</a></li>
								 <li><a href="{{route('Statistic')}}">Encoder Monitoring</a></li>
								 <!-- <li><a href="{{route('rcef.inspection.buffer.designation')}}">Reports</a></li> -->
							</ul>
						</li>
						@endif

						<li><a href=" {{route('farmer.id.home')}} "><i class="fa fa-qrcode"></i> QR Code Generation </a></li>	
						<!--<li><a href="{{url('/releasing_ws')}}"><i class="fa fa-share"></i> Distribution </a></li>-->						
						<!--li><a><i class="fa fa-users"></i> Farmer Beneficiaries<span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu">
							  <li><a href="{{route('farmer_profile.home')}}">WS 2020</a></li>
							  <li><a href="{{route('farmer_profile.home.list')}}">WS 2020 x DS 2021 List</a></li>
							  <li><a href="{{route('farmer_profile.cross.check')}}">WS 2020 x DS 2021 x WS 2021 List</a></li>
							
							 

							</ul>
						  </li>-->

						 
						 @if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso")
						  <li><a href="{{route('palaysikatan.farmers')}}"><i class="fa fa-list-alt"></i>Palaysikatan</a></li>


						  <li><a><i class="fa fa-list-alt"></i>MOET APP<span class="fa fa-chevron-down"></span></a>
										<ul class="nav child_menu">
										  <li><a href="{{route('moet.web.view.farmer')}}">MOET's Farmer Profile</a></li>
										  <li><a href="{{route('moet.web.map_view.farmer')}}">MOET's Map View</a></li>
										  
										</ul>
						  </li>


						  
						  @endif

						  @if(Auth::user()->roles->first()->name == "encoder_yield" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso")
							  	 <li><a href="{{route('encoder.yield.home')}}"><i class="fa fa-pencil-square-o"></i>Yield Updating</a></li>
							  @endif


						 <li class="{{ @$report_side != '' ? 'active' : '' }}"><a><i class="fa fa-database"></i> Reports <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="{{ @$report_side != '' ? 'display:block' : 'display:none' }} ">
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<!--<li class=""><a href=" {{route('rcef.report')}}" target="_blank">Manual Override (REFRESH)</a></li>-->
								@endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer")
									<li class="sub_menu"><a href="{{route('yield_ui.home')}}">Yield Tables</a></li>
								@endif

								<!--<li class=""><a href=" {{route('rcef.report.beneficiaries')}}  ">Distribution Server</a></li>-->
								  <li><a>Seed Beneficiary<span class="fa fa-chevron-down"></span></a>
									<ul class="nav child_menu">
									  <li class="sub_menu"><a href="{{route('rcep.report2.national')}}">National</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.home')}}">Regional</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.province')}}">Provincial</a></li>
									  <li class="sub_menu"><a href="{{route('rcep.report2.municipality')}}">Municipal</a></li>
									  {{-- <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#export_nrp">NRP</a></li> --}}
									</ul>
								  </li>
								  
								 @if(Auth::user()->roles->first()->name == "rcef-programmer") 
									<li><a>Allocation vs Delivery<span class="fa fa-chevron-down"></span></a>
										<ul class="nav child_menu">
										  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'regional')}}">Regional</a></li>
										  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'provincial')}}">Provincial</a></li>
										  <li class="sub_menu"><a href="{{route('delivery.allocation.view', 'municipal')}}">Municipal</a></li>
										</ul>
									</li>
							    @endif
								
								@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso" || Auth::user()->username == "eb.cabanisas" || Auth::user()->username == "aa.alonzo")
									<li><a>Excel Export<span class="fa fa-chevron-down"></span></a>
										<ul class="nav child_menu">
										  <li class="sub_menu"><a href="#" data-toggle="modal" data-target="#statistics_municipality_modal">Municipal Statistics</a></li>
										  <li class="sub_menu"><a href="{{route('report.export.replacement.excel')}}">Replacement Seeds</a></li>
								   		    <li class="sub_menu"><a href="{{route('report.download_commitment_delivery.coop')}}">Local Seed Supply Analysis</a></li>
										</ul>
									</li>
								  <!--<li class="sub_menu"><a href="{{route('data.yield.home')}}">Yield Report</a></li>-->
								@endif
								
								<li class="sub_menu"><a href="{{route('report.variety.overall')}}">Seed Variety Report</a></li>
								<li class="{{ @$report_dis_summary != '' ? 'active' : '' }}"><a href=" {{route('deliverydashboard.iar_table')}}">Generate IAR</a></li>
								
 								<li class="{{ @$bufferInventoryformview != '' ? 'active' : '' }}"><a href=" {{route('bufferInventoryformview')}}  ">IAR (Replacement)</a></li>
								
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
						if(Auth::user()->province!="" and ($prov != '03' or Auth::user()->userId == 84 or Auth::user()->userId == 161 or Auth::user()->roles->first()->name == "rcef-programmer" or Auth::user()->roles->first()->name == "administrator" )){
	

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



          

						

			@if(Auth::user()->roles->first()->name != "sed-caller" &&  Auth::user()->roles->first()->name != "sed-caller-manager" && Auth::user()->roles->first()->name != "it-sra")
            <li><a><i class="fa fa-cogs"></i> Utility <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							<li><a><i class=""></i> Troubleshooting <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
								
									@if(Auth::user()->roles->first()->name == "system-admin" || Auth::user()->roles->first()->name == "rcef-programmer")
										<li><a href="#" data-toggle="modal" data-target="#utilDel_modal">Cancel Delivery</a></li>
									@endif									
									@if(Auth::user()->roles->first()->name == "rcef-programmer")
										<li><a href="{{route('delivery_web.cancel.home')}}"> Cancel Confirmed Deliveries</a></li>	
									@endif

									
								</ul>
							</li>

						
							<li><a><i class=""></i> Monitoring <span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">
									<li class="sub_menu"><a href="{{route('pendingBatch.index')}}">Pending Deliveries</a></li> 
									<li class="sub_menu"><a href="{{route('cancelledBatch.index')}}">Cancelled Deliveries</a></li> 
									
								</ul>
							</li>
							<li><a href="{{route('HistoryMonitoring.index')}}"> Transfer Data List </a></li>

							@if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "data-officer")
							<li><a href="#" data-toggle="modal" data-target="#iar_print_log">Reset printed IAR</a></li>
							@endif


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
								</ul>
							</li>


							<li class="sub_menu"><a href="{{route('farmer_profile.with.contact.nationwide')}}">Farmer W/ Contact Counter Nationwide process</a></li> 
                            
							@endif

						



				</ul>
			</li>
			@endif











					
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
