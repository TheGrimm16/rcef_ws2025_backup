<style>
    .fin{
        border-radius: 2em;
        background-color: rgba(179, 36, 38, 0.568);
        display: flex;
        padding: 0.4em;
        gap: 1em;
    }

    .fin .nai{
        border-radius: 1.8em;
        background-color: rgba(179, 57, 59, 0.568);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.4em;
        min-width: 5em;
    }

    .fin .lab{
        display: flex;
        flex-direction: column;
    }

    .fin .lab .desc{
        font-size: 0.8em;
    }
    .mega{
        font-weight: 900;
        font-size: 2.4em;
        color: #00000060;
    }
    .bold{
        font-weight: 900;
        color: #00000060;
    }
</style>
<div class="modal fade" id="select_members_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" >        
    <div class="modal-dialog">
      <div class="modal-content" style="width:150%;" >
        <div class="modal-header">
          
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h2>Select low land holding members</h2>
        </div>
        <div class="modal-body"  >                
                <div class="row">
                    <div class="col-md-12">
                        <div class="x_content form-horizontal form-label-left">
                        <table class="table table-hover table-striped table-bordered" id="lowland_tbl">
                            <thead>
                                <th> </th>
                                <th>RSBSA #</th>
                                <th>Farmer Name</th>
                                <th>Municipality</th>
                                <th style="width: 15%;">Area</th> 
                            </thead>
                            <tbody id='lowland_tbl_body'>
                                
                            </tbody>
                        </table>
                     </div>      
                    </div>
                    <div class="col-md-4">
                        <div class="fin">
                            <div class="nai">
                                <span class="mega" id="total_area">0</span>
                            </div>
                            <div class="lab">
                                <span class="bold">Total Area</span>
                                <span class="desc">Overall area selected</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        
                    </div>
                    <div class="col-md-3" style="display: flex; justify-content: flex-end;">
                        <button id="confirmMembers" class="btn btn-success" disabled>Confirm</button>
                    </div>
                </div>
               
               
        </div>
      </div>
    </div>
  </div>
