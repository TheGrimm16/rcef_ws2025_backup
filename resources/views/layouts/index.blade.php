<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @include('layouts.cssLinks')

    <style>
        .dataTables_filter {
            width: auto;
        }
        
        .body, body, .main_container{
          height: 100vh;
          overflow-y: hidden;
          position: relative;
        }

        .mother_content{
          min-height: 90%!important;
          max-height: 90%!important;
          overflow-y: auto;
          scroll-behavior: smooth;
          padding-bottom: 6rem!important;
          /* margin-bottom: 10rem!important; */
        }


            /* width */
        ::-webkit-scrollbar {
          width: 5px;
          height: 3px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
          background: #f1f1f100;
          border-radius: 10px;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
          background: #888;
          border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
          background: #555;
        }

        footer{
          position: absolute;
          bottom: 0;
          left: 0;
          right: 0;
        }

        

        @media only screen and (min-width: 1200px){
          body{
            overflow: auto;
          }
        }


        @media only screen and (max-width: 450px){
          .body, body, .main_container{
            height: 100%;
            overflow: unset;
          }

          .mother_content{
            min-height: 90%!important;
            max-height: 90%!important;
            width: auto;
            scroll-behavior: smooth;
            padding-bottom: 6rem!important;
          }

          .global_navbar{
            overflow-y: visible!important;
            height: max-content!important;
          }

          footer{
            position: unset;
          }
        }

    </style>



    <title>RCEF Seed Production Monitoring System</title>
  </head>
  <body class="nav-md">
    <div class="container body">
        <div class="main_container">

          @if(Auth::user()->roles->first()->name == "branch-it")
          @include('layouts.sidebar_branch_it')
        @elseif(Auth::user()->roles->first()->name == "nrp-admin" || Auth::user()->roles->first()->name == "nrp-lgu")
        @include('layouts.sidebar-nrp')
        @else
          @include('layouts.sidebar')
        @endif

          @include('layouts.navbar')

            <!-- page content -->
            <div class="right_col mother_content" role="main">
                @yield('content')
            </div>
            <!-- /page content -->

            <!-- footer content -->
            <footer>
              <div class="pull-right">
                  RCEF 2019
              </div>
              <div class="clearfix"></div>
            </footer>
            <!-- /footer content -->
        </div>
    </div>
	
	<div id="change_pass_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      CHANGE PASSWORD
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
                  <div class="form-group">
                    <label for="user_old_password">* New Password</label>
                    <input type="password" class="form-control" value="" id="user_new_password" name="user_new_password" autocomplete>
                  </div>
                  
                  <div class="form-group">
                    <label for="user_new_password">* Confirm Password</label>
                    <input type="password" class="form-control" value="" id="user_confirm_password" name="user_confirm_password" autocomplete>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="change_pass_btn"><i class="fa fa-unlock"></i> Confirm Change Password</button>
              </div>
              </form>
          </div>
      </div>
    </div>
	

  
	<div id="statistics_municipality_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content" style="width: 40%;">
              <div class="modal-header">
                  <h4 class="modal-title">
                      Export Municipal Statistics 
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
            
                      
                <label for="" class="col-xs-3">FROM:</label>
                <label id="from">
        <input type="text" style="width: 50%; text-align: center;" value="{{date('m/01/Y')}}" class="form-control" name="stat_date1" id="stat_date1" placeholder="Date From">
                </label> <br>
                
                  <label for="" class="col-xs-3">TO:</label>
                <label id="to">
        <input type="text" style="width: 50%; text-align: center;" value="{{date('m/d/Y')}}" class="form-control" name="stat_date2" id="stat_date2" placeholder="Date To">
                </label> 

                   
            </div>
              <div class="modal-footer">
                <button type="button" style="float:left;" class="btn btn-success" id="generate_municipal_statistics"><i class="fa fa-file-excel-o"></i> Download Excel</button>
              </div>
              </form>
          </div>
      </div>
    </div> 
  
	
	<div id="paymaya_tags_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      SCAN QR Code of Selected Seed tag
                  </h4>
              </div>
              <div class="modal-body">
                <div class="form-horizontal form-label-left">  
                  <div class="form-group">
                      <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span>Seed Tag</label>
                      <div class="col-md-10 col-sm-10 col-xs-10" required>
                          <select style="width:100%" name="seedTag_paymaya" id="seedTag_paymaya" class="form-control" required>
                            <option value="0">Please enter atleast 2 characters to begin searching</option>
                          </select>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="control-label col-md-2 col-sm-2 col-xs-2"><span style="color: red">*</span> QR Code</label>
                      <div class="col-md-10 col-sm-10 col-xs-10">
                          <input type="text" class="form-control" name="qr_code_paymaya" id="qr_code_paymaya" placeholder="Please scan / type the QR Code">
                      </div>
                  </div>

                  <video id="preview_paymaya" style="display:none;"></video>
                  <audio id="qr_audio_paymaya">
                      <source src="{{asset('public/sounds/Beep.mp3')}}" type="audio/mpeg">
                  </audio>
              </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="flag_qr_btn"><i class="fa fa-flag"></i> Flag as unusable</button>
              </div>
          </div>
      </div>
    </div>
	
	<!--GENERATE NRP-->
    <div id="export_nrp" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      GENERATE NRP PROFILES
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
                  <div class="form-group">
                    <select name="nrp_province" id="nrp_province" class="form-control" style="width:100%;">
                      <option value="0">Please select a province</option>
                    </select>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" id="generate_nrp_btn"><i class="fa fa-table"></i> GENERATE LIST</button>
              </div>
              </form>
          </div>
      </div>
    </div>

	
	
    <div id="utilDel_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      CANCEL DELIVERY 
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
                  <div class="form-group">
                    <label for="util_batchNumber">Select Batch Number</label>
                    <select name="util_batchNumber" id="util_batchNumber" class="form-control" style="width:100%;">
                      <option value="0">Please select a Batch Number</option>
                    </select>
                  </div>
              </div>

              <div class="modal-body">
                    <label for="" class="col-xs-2">Coop Name:</label>
                    <label id="util_moa">N/A</label> <br>

                    <label for="" class="col-xs-2">Volume</label>
                    <label id="util_volume">N/A</label> <br>
                    <label for="" class="col-xs-2">Drop off</label>
                    <label id="util_dop">N/A</label> <br>
                    <label for="" class="col-xs-2">Delivery Date</label>
                    <label id="util_delDate">N/A</label>

                    
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="cancel_delivery_btn"><i class="fa fa-exclamation-triangle"></i> Cancel Delivery</button>
              </div>
              </form>
          </div>
      </div>
    </div>




    <div id="iar_print_log" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      RESET IAR
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
                  <div class="form-group">
                    <label for="iar">Select Batch Number</label>
                    <select name="util_iarbatch" id="util_iarbatch" class="form-control" style="width:100%;">
                      <option value="0">Please select a Batch Number</option>
                    </select>
                  </div>
              </div>

              <div class="modal-body">
                    <label for="" class="col-xs-2">Province:</label>
                    <label id="iar_province">N/A</label> <br>

                    <label for="" class="col-xs-2">Municipality:</label>
                    <label id="iar_municipality">N/A</label> <br>
                    <label for="" class="col-xs-2">Volume</label>
                    <label id="iar_volume">N/A</label> <br>
                    <label for="" class="col-xs-2">Delivery Date</label>
                    <label id="iar_delDate">N/A</label>

                    
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="reset_iar"><i class="fa fa-exclamation-triangle"></i> Reset IAR</button>
              </div>
              </form>
          </div>
      </div>
    </div>



<div id="flsar_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      GENERATE FLSAR
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
                  <div class="form-group">
                    <label for="flsar_municipality">Select a Municipality</label>
                    <select name="flsar_municipality" id="flsar_municipality" class="form-control" style="width:100%;">
                      <option value="0">Please select a municipality</option>
                    </select>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" id="generate_flsar_btn"><i class="fa fa-list"></i> Generate FLSAR</button>
        <button type="button" class="btn btn-warning" id="generate_flsar_btn_excel"><i class="fa fa-table"></i> Generate FLSAR (EXCEL)</button>
              </div>
              </form>
          </div>
      </div>
    </div>
  
  <div id="flsar_modal_blank" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">
                      GENERATE FLSAR {BLANK}
                  </h4>
              </div>
              <form action="">
              <div class="modal-body">
                  This function enables you to generate and print blank FLSAR(s) to be used in: DR SEASON 2021, please specify the number of pages you want to print.
                  <br><br>
                  <label for="blank_page">Number of Pages to Print: </label>
                  <input type="number" class="form-control" name="blank_page" id="blank_page" min="1" max="100" required>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" id="generate_flsar_blank_btn"><i class="fa fa-list"></i> Generate FLSAR</button>
              </div>
              </form>
          </div>
      </div>
    </div>
 



<!-- DATA MODAL -->
<div id="rla_finder" class="modal fade" role="dialog" >
  <div class="modal-dialog" style="width: 80%">
      <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              <h4 class="modal-title">
                  <span>RLA INFORMATION</span><br>
              </h4>
          </div>
          <div class="modal-body" id ="bodyModal">

              
                  <div class="x_panel">               
                      
                      <div class="col-md-12">
                          <h2>
                              RLA FINDER
                          </h2>
                      </div>
                  

                      <div class="col-md-2"  style=" padding-right:0;">
                          <input type="text" name="find_lab" id="find_lab" class="form-control" placeholder="Labaratory No" autocomplete="off"> 
                      
                      </div>
                      <div class="col-md-2"  style=" padding-right:0;">
                          <input type="text" name="find_lot" id="find_lot" class="form-control" placeholder="Lot No" autocomplete="off">
                      
                      </div>
                      <div class="col-md-1" style=" padding-left:0;">
                      <a id="search_button_rla"  style="margin:0; width:3vw;" class="form-control btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></a>
                      </div>
                  </div>

              <table class="table table-hover table-striped table-bordered" id="rla_found" width="100%">
                  <thead>
                      <th >Season Checked</th>
                      <th >Seed Coop</th>
                      <th  >Seed Grower</th>
                      <th  >Batch Ticket Number</th>
                      <th  >Laboratory #</th>
                      <th  >Lot #</th>
                      <th  >Seed Variety</th>
                      <th  >Passed Volume</th>

                      <th>Last Season Info</th>
                      <th>Current Season Info</th>
                      <th>Is Buffer</th>
                      <th>Second Inspection Info</th>
                  </thead>
           
                  
                  <tbody id='databody'>
                  </tbody>
              </table>
  
          </div>
          <div class="modal-footer">      
          </div>
      </div>
  </div>
</div>
<!--DATA MODAL -->




	
	
	
	
	
    @include('layouts.jsLinks')
  </body>
</html>
