<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\File;
use Auth;
use Illuminate\Support\Facades\Storage;
use \stdClass;
class DroController extends Controller
{
  

    
    public function seedtagSearch(Request $request){

        $final=array();
          $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                //   ->where('has_rla', '=', '1')
                  ->where('batchTicketNumber', '=', $request->batchTicketNumber)
                  ->where('seedTag',$request->seed_tag)
                  ->get();


                  foreach ($data as $value) {
                    $path="";
                    $remarksDRO="";
                    $remarksCES="";
                    $status="";
                    $stat_color="";
                    $is_batch="";
                    $is_batch_type="";

                    $seedTags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                        ->where('tbl_payments_attachements.seed_tag', $value->seedTag)
                        ->where('tbl_payments_attachements.is_seed_tag','=',1)
                        ->orderBy('id', 'DECS')
                        ->first();
                     
                    if(count($seedTags )>0){
                        $path=$seedTags->file_path;
                        $remarksDRO=$seedTags->remarks_dro;
                        $remarksCES=$seedTags->remarks_ces;

                            if($seedTags->status == 0){
                                $status = 'For Assesment';
                                $stat_color ='btn-warning btn-xs btn-block';
                                
                            }if($seedTags->status == 1){
                                $status = 'Passed';
                                $stat_color ='btn-success btn-xs btn-block';
                            }if($seedTags->status == 2){
                                $status = 'Failed';
                                $stat_color ='btn-danger btn-xs btn-block';
                            }if($seedTags->status ==''){
                                $status = 'For Assesment';
                                $stat_color ='btn-warning btn-xs btn-block';

                            }
                   
                        
                    }
                    $tmp =[
                        'seed_tag'=>$value->seedTag,
                        'variety'=>$value->seedVariety,
                        'volume'=>$value->totalBagCount,
                        'path'=>$path,
                        'remarks_dro'=>$remarksDRO,
                        'remarks_CES'=>$remarksCES,
                        'batch_number'=>$request->batch_ticket,
                        'status'=>$status,
                        'stat_color'=> $stat_color,
                        'is_batch'=> $is_batch,
                        'is_batch_type'=> $is_batch_type
                 
                      ];
                array_push($final,$tmp);
                }

                return $final;


    }



    public function getRla(Request $request){

        $search = $request->search;
      
        if($search == ''){
             $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
             ->where('batchTicketNumber',$request->batch)
             ->get();
        }else{
            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('batchTicketNumber',$request->batch)
           ->where(function($query) use ($search){
           $query->where('seedTag','LIKE',"%".$search."%");
           })
           ->limit(20)->get();
        }
        $response = array();
        foreach($data as $value){
           
           $response[] = array(
                "id"=>$value->seedTag,
                "text"=>$value->seedTag,
           );
        }

        echo json_encode($response);
        exit;
    }
    public function index(){

        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
                ->limit(10)
                ->get();

        $select2_data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
            ->get();

       

        $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->where('region', '!=', '')
            ->groupBy('region')
            ->orderBy('region')
            ->get();

        $data = $this->get_top_10_deliveries();
            return view("dro.home")
            ->with("data", $data)
            ->with("regions", $regions)
            ->with("select2_data", $select2_data)
            ->with("coops", $coops);
        }

        public function get_top_10_deliveries() {
            $tmp_arr = array();
             $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                    ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                    ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as d', 'd.batchTicketNumber', '=','a.batchTicketNumber')
                    ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                    ->select('a.batchTicketNumber','a.region','a.province','a.municipality', 'b.coopName', 'c.status AS batch_status ')
                    ->addSelect(DB::raw('DATE_FORMAT(d.deliveryDate, "%b %d, %Y") as dateCreated_new'))
                    // ->where('a.has_rla', '=', '1')
                    ->groupBy('a.batchTicketNumber')
                    ->orderBy('a.dateCreated','decs')
                    ->limit(10)
                    ->get();
    
                    foreach($data as $row){
                        $volume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('batchTicketNumber',$row->batchTicketNumber)->sum('totalBagCount');
                       
                        $object = new stdClass();
                        $object->batchTicketNumber = $row->batchTicketNumber;
                        $object->region= $row->region;
                        $object->province= $row->province;
                        $object->municipality=$row->municipality;
                        $object->coopName= $row->coopName;
                        $object->sum_total_bags = $volume;
                        $object->dateCreated_new = $row->dateCreated_new;
                        $object->batch_status = $row->batch_status;
    
                        array_push($tmp_arr, $object);
                    }
    
                    return $tmp_arr;
               
        }

    public function update_status(Request $request){

        DB::beginTransaction();
        try{

            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                ->where('seed_tag', $request->seed_tag)
                ->where('batch_ticket', $request->batch_number)
                ->update([
                    'status' => $request->status,
                    'remarks_ces'=> $request->remarks3
                ]);

        DB::commit();
            } catch (\Exception $e) {
            DB::rollback();
        }
            

    }


    public function update_status_overall(Request $request){

       $check_batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status')
             ->where('batchTicketNumber', $request->batch_number)
             ->first();

        if(!isset($check_batch)){
               // insert
               DB::beginTransaction();
               try{
       
                $payment_status = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status')
                       ->insert([
                           'batchTicketNumber' => $request->batch_number,
                           // 'coop_moa'=> $request->coop_moa,
                           'status'=> $request->status
                       ]);
       
               DB::commit();
               return 1;
                   } catch (\Exception $e) {
                   DB::rollback();
               }
   
        }else{  
               // update

               DB::beginTransaction();
               try{

                if($check_batch->status == '1' || $check_batch->status == '2' || $check_batch->status == '3' || $check_batch->status == '4'  ){
                    return $check_batch->status;
                }else{

                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status')
                    ->where('batchTicketNumber', $request->batch_number)
                    ->update([
                        'status' => $request->status,
                    ]);
                }
                DB::commit();
                return 1;
                    } catch (\Exception $e) {
                    DB::rollback();
                    }
        }           
        
    }


    public function get_for_assesment(Request $request){
        $attach1 = '';
        $attach2 = '';
        $attach3 = '';

        $attach1_status = '';
        $attach2_status = '';
        $attach3_status = '';

        $final=array();
          $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as a')
                //   ->where('has_rla', '=', '1')
                  ->where('a.batchTicketNumber', '=', $request->batch_ticket)
                  ->get();

                   // 
    
        $inspection_att1 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
        ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
        ->where('tbl_payments_attachements.is_batch','=', 1)
        ->where('tbl_payments_attachements.is_batch_type','=', 1)
        ->get();

        if(count($inspection_att1)>0){
            $attach1 = 1;
            foreach ($inspection_att1 as $row) {
                $attach1_status = $row->status;
            }
            
        }else{
            $attach1 = 0;
        }

    $inspection_att2 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
        ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
        ->where('tbl_payments_attachements.is_batch','=', 1)
        ->where('tbl_payments_attachements.is_batch_type','=', 2)
        ->get();

        if(count($inspection_att2)>0){
            $attach2 = 1;

            foreach ($inspection_att2 as $row) {
                $attach2_status = $row->status;
            }
        }else{
            $attach2 = 0;
        }

    $inspection_att3 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
        ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
        ->where('tbl_payments_attachements.is_batch','=', 1)
        ->where('tbl_payments_attachements.is_batch_type','=', 3)
        ->get();
    
        if(count($inspection_att3)>0){
            $attach3 = 1;
            foreach ($inspection_att3 as $row) {
                $attach3_status = $row->status;
            }
        }else{
            $attach3 = 0;
        }


        $batch_stat =[
            'batch_ticket'=>$request->batch_ticket,
            'attach1'=>$attach1,
            'attach2'=>$attach2,
            'attach3'=>$attach3,
            'attach1_status'=>$attach1_status,
            'attach2_status'=>$attach2_status,
            'attach3_status'=>$attach3_status
        ];

                 
                  foreach ($data as $value) {
                      $path="";
                      $remarksDRO="";
                      $remarksCES="";
                      $status="";
                      $stat_color="";
                      $is_batch="";
                      $is_batch_type="";

                      $seedTags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                          ->where('tbl_payments_attachements.seed_tag', $value->seedTag)
                          ->where('tbl_payments_attachements.is_seed_tag','=',1)
                          ->orderBy('id', 'DECS')
                          ->first();
                       
                      if(count($seedTags )>0){
                          $path=$seedTags->file_path;
                          $remarksDRO=$seedTags->remarks_dro;
                          $remarksCES=$seedTags->remarks_ces;
  
                              if($seedTags->status == 0){
                                  $status = 'For Assesment';
                                  $stat_color ='btn-warning btn-xs btn-block';
                                  
                              }if($seedTags->status == 1){
                                  $status = 'Passed';
                                  $stat_color ='btn-success btn-xs btn-block';
                              }if($seedTags->status == 2){
                                  $status = 'Failed';
                                  $stat_color ='btn-danger btn-xs btn-block';
                              }if($seedTags->status ==''){
                                  $status = 'For Assesment';
                                  $stat_color ='btn-warning btn-xs btn-block';
  
                              }
                     
                          
                      }
                      $tmp =[
                          'seed_tag'=>$value->seedTag,
                          'variety'=>$value->seedVariety,
                          'volume'=>$value->totalBagCount,
                          'path'=>$path,
                          'remarks_dro'=>$remarksDRO,
                          'remarks_CES'=>$remarksCES,
                          'batch_number'=>$request->batch_ticket,
                          'status'=>$status,
                          'stat_color'=> $stat_color,
                          'is_batch'=> $is_batch,
                          'is_batch_type'=> $is_batch_type
                   
                        ];
                  array_push($final,$tmp);
                  }
        //   return $final;
        return compact('final','batch_stat');
    }

    public function get_attachements(Request $request){


      //return  Storage::disk('ftp')->get($request->batch_ticket.'/_MGL1413.jpg');
    //   return "https://drive.philrice.gov.ph/RCEF/Billing/".$request->batch_ticket."/_MGL1413.jpg";
    //   return $contents = Storage::get('Billing/'.$request->batch_ticket.'/_MGL1413.jpg');

        return $attachements = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
           ->where('tbl_payments_attachements.seed_tag', '=', $request->seed_tag)
           ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
           ->where('tbl_payments_attachements.is_seed_tag','=', 1)
           ->get();
      }


      public function get_attachements_batch(Request $request){

        return $attachements = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
           ->where('tbl_payments_attachements.is_batch_type', '=',$request->batch_type)
           ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
           ->get();
      }

         

    
 public function storeMedia(Request $request){
    $now = date_create()->format('Y-m-d H:i:s');
    $userName = Auth::user()->username;
    DB::beginTransaction();
    
    try {
        foreach ($request->file('file') as $file) {
            //set a unique file name
            // $filename = 'psi-'. $inserted_inspection_id . '-' . uniqid().'.'.$file->getClientOriginalExtension();
            $filename = $file->getClientOriginalName();

            $filename=str_replace("#","_",$filename);
            $filename=str_replace(" ","_",$filename);
            $filename=str_replace("-","_",$filename);
            $filename = date("Y_m_d_g_i")."_".$filename;

                                    // dd($filename);
            //directory
            $dir="public/dro_upload/".$request->batch_number."/";
            
                //get filename with extension
                $filenamewithextension = $file->getClientOriginalName();
          
                //get filename without extension
                $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
          
                //get file extension
                $extension = $file->getClientOriginalExtension();
          
                //filename to store
                $filenametostore = $request->batch_number."/".$filename.'.'.$extension;
          
                //Upload File to external server
               Storage::disk('ftp')->put($filenametostore, fopen($file, 'r+'));
          
                //Store $filenametostore in the database
  
     
          
        

            //move the files to the correct folder
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true, true);
            }

            //save details to db
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
            ->insert([
                'file_name'=> $filename.'.'.$extension,
                'file_path'=> $dir.$filename.'.'.$extension,
                'file_size'=> $file->getSize(),
                'batch_ticket'=> $request->batch_number,
                'seed_tag'=> $request->seed_tag_number_field,
                'is_batch'=> $request->is_batch,
                'is_batch_type'=>$request->is_batch_type,
                'is_seed_tag'=>$request->is_seed_tag,
                'status'=>0,
                'remarks_dro'=>$request->remarks_dro,
                'uploaded_by_usernarme' =>$userName
            ]);

            $file->move($dir,$filename.'.'.$extension);
        }
        DB::commit();
        return response()->json([
            'message' => 'OK',
        ],200);
        } catch (Exception $e) {
            DB::rollback();
             return response()->json([
                'message'=>$e->getMessage()
            ],500);
        }

    }

        public function filter(Request $request) {
            $region = $request->region;
            $province = $request->province;
            $municipality = $request->municipality;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $attachement_status = $request-> attachement_status;
            $current_moa = $request->current_moa;
            
            // coop only
            if($current_moa != "" && $start_date == "" && $end_date == "" && $region == "" && $province == "" && $municipality == "" && $attachement_status == "0"){
                
                $data = array();
                $tmp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                       ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                       ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as d', 'd.batchTicketNumber', '=','a.batchTicketNumber')
                       ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                       ->select('a.batchTicketNumber','a.region','a.province','a.municipality', 'b.coopName', 'c.status AS batch_status')
                       ->addSelect(DB::raw('DATE_FORMAT(d.deliveryDate, "%b %d, %Y") as dateCreated_new'))
                    //    ->where('a.has_rla', '=', '1')
                       ->where('a.moa_number','=',$current_moa)
                       ->groupBy('a.batchTicketNumber')
                       ->orderBy('a.dateCreated','decs') 
                       ->get();
       
                       foreach($tmp as $row){
                           $volume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('batchTicketNumber',$row->batchTicketNumber)->sum('totalBagCount');
                          
                           $object = new stdClass();
                           $object->batchTicketNumber = $row->batchTicketNumber;
                           $object->region= $row->region;
                           $object->province= $row->province;
                           $object->municipality=$row->municipality;
                           $object->coopName= $row->coopName;
                           $object->sum_total_bags = $volume;
                           $object->dateCreated_new = $row->dateCreated_new;
                           $object->batch_status = $row->batch_status;
       
                           array_push($data, $object);
                       }

                         

            // coop + status
            }if($current_moa != "" && $start_date == "" && $end_date == "" && $region == "" && $province == "" && $municipality == "" && $attachement_status != "0"){                  
                
                if($attachement_status == '6'){
                    $data = array();
                    $tmp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as d', 'd.batchTicketNumber', '=','a.batchTicketNumber')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.batchTicketNumber','a.region','a.province','a.municipality', 'b.coopName', 'c.status AS batch_status')
                        ->addSelect(DB::raw('DATE_FORMAT(d.deliveryDate, "%b %d, %Y") as dateCreated_new'))
                        // ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs') 
                        ->get();
        
                        foreach($tmp as $row){
                            $volume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('batchTicketNumber',$row->batchTicketNumber)->sum('totalBagCount');
                            
                            $object = new stdClass();
                            $object->batchTicketNumber = $row->batchTicketNumber;
                            $object->region= $row->region;
                            $object->province= $row->province;
                            $object->municipality=$row->municipality;
                            $object->coopName= $row->coopName;
                            $object->sum_total_bags = $volume;
                            $object->dateCreated_new = $row->dateCreated_new;
                            $object->batch_status = $row->batch_status;
        
                            array_push($data, $object);
                        }                           
                }else{
                    $data = array();
                    $tmp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as d', 'd.batchTicketNumber', '=','a.batchTicketNumber')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.batchTicketNumber','a.region','a.province','a.municipality', 'b.coopName', 'c.status AS batch_status')
                        ->addSelect(DB::raw('DATE_FORMAT(d.deliveryDate, "%b %d, %Y") as dateCreated_new'))
                        // ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs') 
                        ->get();
        
                        foreach($tmp as $row){
                            $volume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('batchTicketNumber',$row->batchTicketNumber)->sum('totalBagCount');
                            
                            $object = new stdClass();
                            $object->batchTicketNumber = $row->batchTicketNumber;
                            $object->region= $row->region;
                            $object->province= $row->province;
                            $object->municipality=$row->municipality;
                            $object->coopName= $row->coopName;
                            $object->sum_total_bags = $volume;
                            $object->dateCreated_new = $row->dateCreated_new;
                            $object->batch_status = $row->batch_status;
        
                            array_push($data, $object);
                        }     

                }

            // coop date
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region == "" && $province == "" && $municipality == "" && $attachement_status == "0"){
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('a.moa_number','=',$current_moa)
                            // ->where('a.has_rla', '=', '1')
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

             // coop stat date
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region == "" && $province == "" && $municipality == "" && $attachement_status != "0"){
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('a.moa_number','=',$current_moa)
                                ->where('c.status','=',null)
                                // ->where('a.has_rla', '=', '1')
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('a.moa_number','=',$current_moa)
                                ->where('c.status', '=', $attachement_status)
                                // ->where('a.has_rla', '=', '1')
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();

                }

            // coop date reg 
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province == "" && $municipality == "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            // ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
            
            // coop date reg stat              
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province == "" && $municipality == "" && $attachement_status != "0"){   
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                // ->where('a.has_rla', '=', '1')
                                ->where('a.moa_number','=',$current_moa)
                                ->where('a.region','LIKE','%'.$region.'%')
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('c.status','=',null)
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                // ->where('a.has_rla', '=', '1')
                                ->where('a.moa_number','=',$current_moa)
                                ->where('a.region','LIKE','%'.$region.'%')
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('c.status', '=', $attachement_status)
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();
                }
            //  coop date reg prov 
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality == "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            // ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            //  coop date reg prov stat
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality == "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            // ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            // ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }

            // coop date reg prov muni
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality != "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            // ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            // coop date reg prov muni stat
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality != "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            // ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }
                
            // coop reg prov muni
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality != "" && $attachement_status == "0"){  
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            // coop reg prov muni stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality != "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('a.municipality','LIKE','%'.$municipality.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('a.municipality','LIKE','%'.$municipality.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }
                
       
             // coop reg prov 
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality == "" && $attachement_status == "0"){  
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

            // coop reg prov stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality == "" && $attachement_status != "0"){
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();
                }

            // coop reg
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province == "" && $municipality == "" && $attachement_status == "0"){  
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

            // coop reg stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province == "" && $municipality == "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }
                
            }

            $sg = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                ->where('isActive', 1)
                ->where('current_moa',$current_moa)
                ->orderBy('coopName')
                ->get();

            $data_count = count($data);

        return compact('data','sg','data_count');
        }
}
