<style>
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
		text-shadow: 5px 5px 1rem rgb(121, 121, 121);
	}

	.group_title::after{
		content: '';
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

	.section_all > ul > li > *{
		padding: 0.8rem 1.2rem;
	}

	.section_all > ul > li{
		transition: all 0.2s ease-in-out;
		border-radius: 1rem;
	}

	.section_all > ul > li:hover{
		background: rgb(30,183,158)
	}

	.section_all a{
		color: black!important;
		font-weight: 600!important;
	}

	.section_all h2{
		color: black;
	}

	.section_all{
		border-radius: 2rem;
		padding: 1rem 1rem;
	}

	.section_all > h2{
		font-weight: 700;
		text-align: center;
	}

	.section_all .active > a{
		background: rgb(205, 204, 204)!important;
		border-radius: 1rem 0rem 0rem 1rem;
	}

	.sub-menu:hover > *{
		color: white!important;
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
				@if (Auth::user()->roles->first()->name == "nrp-admin")
				NRP
					@else
					RCEF {{$currentSeason}}
				@endif	
				
                </div>

				@if (Auth::user()->roles->first()->name == "nrp-admin")
				<div class="col-md-6" style="    margin: 0;    padding: 0;">
					<img src="{{ asset('public/images/DA.png') }}" alt="..." class="img-circle profile_img" style="width: 4vw !important;    border-radius: 50%; margin-left:20%;   text-align: center;margin-right: 2vw;    margin-top: .2vw;">
				</div>
					@else
					<div class="col-md-6" style="    margin: 0;    padding: 0;">
						<img src="{{ asset('public/images/rcef_LOGO_ds2021.png') }}" alt="..." class="img-circle profile_img" style="width: 4vw !important;    border-radius: 7%;    text-align: center;margin-right: 2vw;    margin-top: .2vw;">
					</div>
				@endif	
				
				
              
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
                <div class="menu_section">
					<h3>GENERAL</h3>
					<ul class="nav side-menu">
						@if (Auth::user()->roles->first()->name == "nrp-admin")
						<li><a href=" {{url('seed-postion')}} "><i class="fa fa-paper-plane-o"></i>Seed Positioning</a></li>
						<li><a href=" {{url('delivery-confrimation')}} "><i class="fa fa-plus-square"></i>Delivery Acceptance</a></li>
						<li><a href=" {{url('specView')}} "><i class="fa fa-delicious"></i>Regional Specification</a></li>

						<li class="sub_menu"><a href="{{route('nrp.export.index')}}"><i class="fa fa-file-text-o"></i>NRP Distribution Report</a></li> 
						@else	
						<li><a href=" {{url('delivery')}} "><i class="fa fa-plus-square"></i>Delivery Acceptance</a></li>
						@endif
						
						
				
					</ul>
				</div>
            </div>

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
