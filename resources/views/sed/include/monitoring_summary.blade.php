<style>
    .summarytitle{
        font-weight: bolder;
        font-size: 20px;
    }
</style>
<div class="container" style="padding:10px">
    <div class="row">
        <div class="row">
            <div class="col-xs-3 summarytitle">
               <div class="summarytitle">Total Farmers:</div>  
               <div class="summaryNum">
                   {{$yes}}
               </div>
            </div>
            <div class="col-xs-3 summarytitle">
               <div class="summarytitle">Answered Yes:</div>  
               <div class="summaryNum">
                   {{$yes_calls}}
               </div>
            </div>
            <div class="col-xs-2 summarytitle">
                <div class="summarytitle">Answered No:</div>
                <div class="summaryNum">
                    {{$no}}
                </div>
            </div>
            <div class="col-xs-2 summarytitle">
                <div class="summarytitle">Failed Calls:</div>
                <div class="summaryNum">
                    {{$failed}}
                </div>
            </div>
            <div class="col-xs-2 summarytitle">
                <div class="summarytitle">Pending:</div>
                <div class="summaryNum">
                    {{$pending}}
                </div>
            </div> 
        </div>
    </div>
</div>