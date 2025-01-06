<div class="modal fade" id="search_farmer_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" >        
    <div class="modal-dialog">
      <div class="modal-content" style="width:150%;" >
        <div class="modal-header">
          
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h2>Search Farmer</h2>
        </div>
        <div class="modal-body"  >
                <div class="row">
                    <div class="col-md-10">
                        <label for="search_bar">Search Farmer</label>
                        <input type="text" class="form-control" id="search_bar" name="search_bar" onkeyup="search();" placeholder="Search Here"> 
                    </div>
                    <!-- <div class="col-md-2">
                        <label for="new_farmer"></label> <br>
                       <button class="btn btn-warning btn-md" id="new_farmer" name="new_farmer"> <i class="fa fa-plus" aria-hidden="true"></i> New Farmer</button>
                    </div> -->
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="x_content form-horizontal form-label-left">
                        <table class="table table-hover table-striped table-bordered" id="dataTBL">
                            <thead>
                                <th>RSBSA #</th>
                                <th>Farmer Name</th>
                  
                                {{-- <th style="width: 15%;">Area</th> --}}
                                <th>Sex</th>
                                <th>Birthdate</th>
                                <th>Action</th>
                            </thead>
                            <tbody id='databody'>
                                
                            </tbody>
                        </table>
                     </div>      
                    </div>
                </div>
               
               
        </div>
      </div>
    </div>
  </div>
