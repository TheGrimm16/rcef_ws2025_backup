<!-- top navigation -->
<div class="top_nav">
  <div class="nav_menu" style="margin-bottom: 0;">
    <nav>
      <div class="nav toggle">
        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
      </div>
	  
	  @if(Auth::user()->roles->first()->name == "da-icts")
        <ul class="nav navbar-nav navbar-left" style="padding: 10px;font-size: 24px;font-weight: 700;position: absolute;margin-left: 50px;">
          <li class="">
            <div id="rcef_ws2020_title">
              <a target="_blank" href="https://rcef-seed.philrice.gov.ph/rcef_ws2020/w.mogado/redirect">
                <u>CLICK TO PROCEED TO RCEF-SMS WS2020</u>
              </a>              
            </div>
          </li>
        </ul>
      @endif

      <ul class="nav navbar-nav navbar-right">
        <li class="">
          <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            @if(Auth::user()->sex == "M")
              <img src="{{ asset('public/images/male_farmer.png') }}" alt=""> {{Auth::user()->firstName}} {{Auth::user()->lastName}}
            @else
              <img src="{{ asset('public/images/female_farmer.png') }}" alt=""> {{Auth::user()->firstName}} {{Auth::user()->lastName}}
            @endif
            
            <span class=" fa fa-angle-down"></span>
          </a>
          <ul class="dropdown-menu dropdown-usermenu pull-right">
            <li><a href="javascript:;"> Profile</a></li>
            <li><a href="#" data-toggle="modal" data-target="#change_pass_modal">Change Password</a></li>
            <li><a href="{{url('/logout')}}"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
</div>
<!-- /top navigation -->
