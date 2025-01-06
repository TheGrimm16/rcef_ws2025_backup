<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Controller;

use PDFTIM;
use Yajra\Datatables\Facades\Datatables;

class bufferInventoryController extends Controller
{






  public function BufferIRDatatable(Request $request){

      return Datatables::of(DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
      //->select("batchTicketNumber", "dropOffPoint", DB::raw("SUM('totalBagCount') as bags"), "deliveryDate")
      ->where('region','like', $request->region)
      ->where('province', $request->provincebuffer)
      ->where('municipality',$request->Municipalitybuffer)
      ->where('isBuffer', 9)
      ->where('is_cancelled',0)
      ->groupBy('batchTicketNumber')
      )
      ->addColumn('origin', function($row){   
        //dd($row->batchTicketNumber);               
       $origin =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer") 
            ->where("replacement_ticket", "LIKE", "%".$row->batchTicketNumber."%")
            ->first();
            if(count($origin)>0){
            return $origin->batchTicketNumber." (".$origin->seedTag.")"; 
            }else{
              return "Not indicated";
            }
      })
	->addColumn('batchTicket', function($row){   
			return $row->batchTicketNumber;
      })	  
      ->addColumn('DropOfPoint', function($row){    
        return $row->dropOffPoint;
        
      })
      ->addColumn('TotalBag', function($row) use ($request){      
        return DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->where('region','like', $request->region)
        ->where('province', $request->provincebuffer)
        ->where('municipality',$request->Municipalitybuffer)
        ->where("batchTicketNumber", $row->batchTicketNumber)
        ->where('isBuffer', 9)
        ->sum("totalBagCount");

      })
      ->addColumn('DeliveryDate', function($row){  
        return date('F d, Y',strtotime($row->deliveryDate));
      })
      ->addColumn('action', function($row){       
        $origin =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer") 
        ->where("replacement_ticket", "LIKE", "%".$row->batchTicketNumber."%")
        ->first();
        if(count($origin)>0){
            $dis = "";
        }else{
            $dis = "disabled";
        }
        return "<button class='btn btn-danger btn-xs filter_btn_pdf_btn' data-id='$row->batchTicketNumber' data-target='#release_stocks_modal' ".$dis.">Generate PDF</button>";   
      })
      ->make(true);
    
  }
    public function bufferInventoryInspectionResult(Request $request){

        $datatmp =[];
        $data=[];
        

        /* replacement ticket */
         return  $tbl_actual_deliverys = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
          ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_breakdown_buffer', 'tbl_breakdown_buffer.replacement_ticket' ,'=','tbl_actual_delivery.batchTicketNumber')
          ->where('tbl_actual_delivery.region',$request->region)
          ->where('tbl_actual_delivery.province',$request->provincebuffer)
          ->where('tbl_actual_delivery.municipality',$request->Municipalitybuffer)
          ->where('tbl_actual_delivery.isBuffer',9)   
          ->where('is_cancelled',0)    
          ->get();
        foreach ($tbl_actual_deliverys as $tbl_actual_delivery) {
       
        $seedTagList=[];
       
        /* origin batch ticket */
         $asOriginTicket = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_breakdown_buffer')
        ->where('replacement_ticket',$tbl_actual_delivery->batchTicketNumber)->first();


          $tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->where('batchTicketNumber',$tbl_actual_delivery->batchTicketNumber)->first();

        $tbl_cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('accreditation_no',$tbl_delivery->coopAccreditation)->first();

        /* seedTagList data */
        
        $seedTag = $tbl_actual_delivery->seedTag;
        $seedTagArray = explode("/",$seedTag);

         $tbl_rla_detailsInfo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
        ->where('coopAccreditation',$tbl_delivery->coopAccreditation)
        ->where('labNo',$seedTagArray[0])
        ->where('lotNo',$seedTagArray[1])
        ->get();
        $pagestate=0; 
        $pageNo=0;
        $pageData="";
        $seedTagtmp=[]; 
        $tmp =[];
        $final =[];
        for ($counter=0; $counter < count($tbl_rla_detailsInfo) ; $counter++) {           
          $tmp =[
            'sg_name'=>$tbl_rla_detailsInfo[$counter]->sg_name,
            'seedTag'=>$tbl_rla_detailsInfo[$counter]->labNo."/".$tbl_rla_detailsInfo[$counter]->lotNo,
            'totalV'=>$tbl_delivery->totalBagCount,
            'totalD'=>$tbl_actual_delivery->totalBagCount

          ];
          array_push($final,$tmp);
          $pagestate++;
          if($pagestate==6){          
            $seedTagtmp=['page'.$pageNo.''=>$final];
            $pageNo++;            
            array_push($seedTagList,$seedTagtmp);
            $pagestate=0;
            $seedTagtmp=[];
            $pageData="";
            $tmp =[];
            $final =[];
          }
        }
       
        
         if($pagestate < 6){         
          for ($x=$pagestate; $x < 6 ; $x++) { 
            $tmp =[
              'sg_name'=>" ",
              'seedTag'=>" ",
              'totalV'=>" ",
              'totalD'=>" "
  
            ];
            array_push($final,$tmp);
          }
          $seedTagtmp=[
            'page'.$pageNo.''=>$final
          ];
          array_push($seedTagList,$seedTagtmp);
         
        }        
       

         $pageCount = (count($seedTagList)/6);
        
         $datatmp=[
        'replacementTicket'=>$tbl_actual_delivery->batchTicketNumber,
        'originTicket'=>$asOriginTicket->batchTicketNumber,
        'region'=>$request->region,
        'province'=>$request->provincebuffer,
        'municipality'=>$request->Municipalitybuffer,
        'DOP'=>$tbl_delivery->dropOffPoint,
        'CoopName'=>$tbl_cooperatives->coopName,
        'accreditation_no'=>$tbl_cooperatives->accreditation_no,
        'moa'=>$tbl_cooperatives->current_moa,
        'SeedTagList'=>$seedTagList,
        'countPage'=> ceil($pageCount),
       
        ];
        array_push($data,$datatmp);
      }
        return json_encode($data);
    }
    public function provincelist(Request $request){
    
       return  $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->where('region',$request->provCode)
        ->where('isBuffer', 9)
        ->where('is_cancelled',0)
        ->groupBy('province')->get();
    }
    public function MunicipalitybufferData(Request $request){
        return  $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
         ->where('province',$request->provCode)
         ->where('isBuffer', 9)
         ->where('is_cancelled',0)
         ->groupBy('municipality')->get();
     }
    public function index(){
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->where('is_cancelled',0)->where('isBuffer',9)->groupBy('region')->get();
        return view('bufferInventoryInspection.index',compact('regions'));
     }

     
    public function bufferInverntory($region,$provincebuffer,$Municipalitybuffer,$id){    
      $datatmp =[];
      $datas=[];        
        $tbl_actual_deliverys = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->where('region',$region)
        ->where('province',$provincebuffer)
        ->where('municipality',$Municipalitybuffer)
        ->where('batchTicketNumber',$id)
        ->groupBy('batchTicketNumber')
        ->where('isBuffer',9)
        ->where('is_cancelled',0)
        ->get();
      //  dd($tbl_actual_deliverys);
        foreach ($tbl_actual_deliverys as $tbl_actual_delivery) {
            
        $seedTagList=[];
       
        /* origin batch ticket */
         $asOriginTicket = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_breakdown_buffer')
        ->where('replacement_ticket', 'LIKE', "%".$tbl_actual_delivery->batchTicketNumber."%")->first();
         // dd($asOriginTicket);

          $tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->where('batchTicketNumber',$tbl_actual_delivery->batchTicketNumber)->first();

        $tbl_cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('accreditation_no',$tbl_delivery->coopAccreditation)->first();

        $pagestate=0; 
        $pageNo=0;
        $pageData="";
        $seedTagtmp=[]; 
        $tmp =[];
        $final =[];
        /* seedTagList data */
         $ForSeedTags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->where('region',$region)
        ->where('province',$provincebuffer)
        ->where('municipality',$Municipalitybuffer)
        ->where('batchTicketNumber',$tbl_actual_delivery->batchTicketNumber)
        ->groupBy('seedTag')
        ->where('isBuffer',9)
        ->where('is_cancelled',0)
        ->get();
        foreach ($ForSeedTags as $key => $ForSeedTag) {
          $seedTag = $ForSeedTag->seedTag;
          $seedTagArray = explode("/",$seedTag);

         $tbl_rla_detailsInfo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
        ->where('coopAccreditation',$tbl_delivery->coopAccreditation)
        ->where('labNo','like',"%".$seedTagArray[0]."%")
        ->where('lotNo','like',"%".$seedTagArray[1]."%")
        ->get();
       
          // dd($seedTagArray);

          $actual_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->where("batchTicketNumber", $tbl_actual_delivery->batchTicketNumber)
            ->where("seedTag", $seedTag)
            ->first();

          if(count($actual_data)>0){
              $actual_delivery = $actual_data->totalBagCount;
          }else{
            $actual_delivery = 0;
          }


        for ($counter=0; $counter < count($tbl_rla_detailsInfo) ; $counter++) {           
          $tmp =[
            'sg_name'=>$tbl_rla_detailsInfo[$counter]->sg_name,
            'seedTag'=>$seedTag,
            'totalV'=>$ForSeedTag->totalBagCount,
            'totalD'=>$actual_delivery

          ];
          array_push($final,$tmp);
          $pagestate++;
          if($pagestate==15){          
            $seedTagtmp=['page'.$pageNo.''=>$final];
            $pageNo++;            
            array_push($seedTagList,$seedTagtmp);
            $pagestate=0;
            $seedTagtmp=[];
            $pageData="";
            $tmp =[];
            $final =[];
          }
        }
        }
        
       
        
         if($pagestate < 15 && $pagestate > 0){         
          for ($x=$pagestate; $x < 15 ; $x++) { 
            $tmp =[
              'sg_name'=>"##",
              'seedTag'=>"##",
              'totalV'=>"##",
              'totalD'=>"##"
  
            ];
            array_push($final,$tmp);
          }
          $seedTagtmp=[
            'page'.$pageNo.''=>$final
          ];
          array_push($seedTagList,$seedTagtmp);
         
        }
			$origin =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer") 
            ->where("replacement_ticket", $tbl_actual_delivery->batchTicketNumber)
            ->first();        
       
        $pageCount = (count($seedTagList));
	     $fileName=$tbl_actual_delivery->batchTicketNumber;
         $datatmp=[
        'replacementTicket'=>$tbl_actual_delivery->batchTicketNumber,
        'originTicket'=>$asOriginTicket->batchTicketNumber." (".$asOriginTicket->seedTag.")" ,
        'region'=>$region,
        'province'=>$provincebuffer,
        'municipality'=>$Municipalitybuffer,
        'DOP'=>$tbl_delivery->dropOffPoint,
        'CoopName'=>$tbl_cooperatives->coopName,
        'accreditation_no'=>$tbl_cooperatives->accreditation_no,
        'moa'=>$tbl_cooperatives->current_moa,
        'SeedTagList'=>$seedTagList,
        'countPage'=> ceil($pageCount),
        'is_palleted'=> $asOriginTicket->is_palleted,
        'is_good_stocking'=> $asOriginTicket->is_good_stocking,
        'is_good_wh'=> $asOriginTicket->is_good_wh,
        'wh_pest'=> $asOriginTicket->wh_pest,
        'wh_temperature'=> $asOriginTicket->wh_temperature,
        'wh_roofing'=> $asOriginTicket->wh_roofing,
        'remarks'=> $asOriginTicket->remarks
       
        ];

        array_push($datas,$datatmp);
      }

      // dd($datas);
         $pdf = PDFTIM::loadView('bufferInventoryInspection.bufferInventoryPDFview.bufferInventoryPDF',compact('datas'))->setPaper('legal');
         $pdf_name = "IAR_REPLACEMENT_".$fileName.".pdf";
        return $pdf->stream($pdf_name);

     
    }
}
