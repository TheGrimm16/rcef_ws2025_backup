<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Yajra\Datatables\Datatables;

class nprController extends Controller
{
    public function index(){
        return view('nrp.index');
    }
    public function specView(){
        return view('nrp.specview');
    }

    public function spectDataList(){      
        $lib_prv =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("regCode", Auth::user()->region)
        ->groupBy("regCode")
        ->first();      
         $spectDataList = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
         ->where('region',$lib_prv->regionName)
         ->where('activeStatus', 0)
         ->groupBy('seed_package','seed_sub_package');
         
         return Datatables::of($spectDataList)
         ->addColumn('actions', function ($query) {                            
            if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('package',$query->seed_package)->where('sub_package',$query->seed_sub_package)->where('region',$query->region)->count() == 0){
             $href = '<a href="#" data-id="'.$query->seed_package.'X'.$query->seed_sub_package.'" class="btn btn-sm btn-warning edit-btn"><i class="fa fa-pencil-square-o"></i> Edit Specification</a>';
             if(Auth::user()->roles->first()->name == "nrp-admin"){
                 $href .= '<a href="#" data-id="'.$query->seed_package.'X'.$query->seed_sub_package.'" class="btn btn-sm btn-danger delete-btn"><i class="fa fa-trash"></i> Delete Specification</a>';
             }
             
            }else{
             $href = '<span class = "label label-success">have delivery on this specification</span>';
            }                                     
           return $href;
      })
         ->make(true);
    }

    public function deleteSpecification(Request $request){     
        
        $lib_prv =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("regCode", Auth::user()->region)
        ->groupBy("regCode")
        ->first();  


         $data = explode("X",$request->id);        
        DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
        ->where('region',$lib_prv->regionName)
        ->where('seed_package',$data[0])
        ->where('seed_sub_package',$data[1])
        ->delete();
    }

    public function getSpec(Request $request){
        
        if($request->spec == 15){
            $specInfo2 = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
            ->where('regCode',substr(Auth::user()->province,0,2))
            ->where('seed_package',$request->spec)
            ->where('seed_sub_package',5)
            ->get();
            $specInfo1 = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
            ->where('regCode',substr(Auth::user()->province,0,2))
            ->where('seed_package',$request->spec)
            ->where('seed_sub_package',3)
            ->get();
        }else if ($request->spec == 18){
            $specInfo2 = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
            ->where('regCode',substr(Auth::user()->province,0,2))
            ->where('seed_package',$request->spec)
            ->where('seed_sub_package',6)
            ->get();
            $specInfo1 = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
            ->where('regCode',substr(Auth::user()->province,0,2))
            ->where('seed_package',$request->spec)
            ->where('seed_sub_package',3)
            ->get();
        }else{
            $specInfo1 = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
            ->where('regCode',substr(Auth::user()->province,0,2))
            ->where('seed_package',$request->spec)            
            ->get();
            $specInfo2=[];
        }

        return compact('specInfo1','specInfo2');
    }
    public function reviewStatus(){
      return   $specInfo = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
        ->where('regCode',substr(Auth::user()->province,0,2))
        ->where('reviewStatus',0)
        ->get();
    }
    public function reviewStatusSave(){
           $specInfo = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
          ->where('regCode',substr(Auth::user()->province,0,2))
          ->where('reviewStatus',0)
          ->update([
            'reviewStatus'=>1
          ]);
      }
  
    
    public function seedRatio(){    
         $lib_prv =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("regCode_int", Auth::user()->region)
        ->groupBy("regCode_int")
        ->first();    
         $seedRatio =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
       ->where('regCode',$lib_prv->regCode)
        ->count();
        return $seedRatio;
    }
    public function forceCompleteDelivery(Request $request){
        
        try {
            if($request->status == 1){                
                return DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('id',$request->id)->update([
                    'status' => 1,
                 ]);   
            }else{
                return DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('id',$request->id)->update([
                    'status' => 0,
                 ]); 
            }
         
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function addDelivery($po,$date){
        $POlist =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')
        ->join($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp', 'tbl_delivery_nrp.commitmentId', '=','tbl_commitment_nrp.id')
        ->select('tbl_commitment_nrp.province','tbl_commitment_nrp.municipal','tbl_delivery_nrp.po','tbl_delivery_nrp.delivery_date')
        ->where('po', $po)
        ->where('tbl_delivery_nrp.delivery_date', $date)
        ->first();

        $specInfo = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
        ->where('regCode',substr(Auth::user()->province,0,2))
        ->groupBy('seed_package','seed_sub_package')
        ->first();
         $specData = $specInfo->seed_package."X".$specInfo->seed_sub_package;

        return view('nrp.add-delivery',compact('POlist','date','specData','specInfo'));
    }
    public function actualDeliveryList(Request $request){
         $query = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')
        ->where('province', $request->province)
        ->where('municipality', $request->municipal)
        ->where('po', $request->po)
        ->orderBy('date_created','DESC');
        return Datatables::of($query)
          ->addColumn('actions', function ($query) {

                $href = '<a href="#" data-id="'.$query->id.'" class="btn btn-sm btn-danger delete-btn"><i class="glyphicon glyphicon 	glyphicon glyphicon-trash"></i> delete</a>';
            
            
               return $href;
          })
          ->make(true);
    }
    public function saveDelivery(Request $request){
        try {
           for ($i=0; $i < count($request->id); $i++) { 
   
            $deliveryData =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('id',$request->id[$i])->first();
            $deliveryDataActual =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->where('batchNumber',$deliveryData->batchNumber)->where('seed_variety',$deliveryData->seed_variety)->sum('package_bags');
            $package =  $deliveryData->package;
            $subPackage =  $deliveryData->sub_package;;
            $deliver_volume= $request->volume[$i]; // bags
            $bags = intval($deliveryData->volume)-intval($deliveryDataActual);
            if(intval($bags)-intval($deliver_volume)<0){
              return "Exhausted";
             }           
            $commitmentData =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')->where('id',$deliveryData->commitmentId)->first();
             DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->insert([
                
            'batchNumber' => $deliveryData->batchNumber,
            'commitmentId' => $deliveryData->commitmentId,
            'po' => $deliveryData->po,
            'province' => $commitmentData->province,
            'municipality' => $commitmentData->municipal,
            'supplierName' =>  $deliveryData->supplierName,
            'seed_variety' => $deliveryData->seed_variety,
            'volume' => $deliver_volume*$package,//kg
            'package' => $deliveryData->package,
            'sub_package' => $deliveryData->sub_package,
            'package_bags' => $deliver_volume,
            'sub_package_bags' => $deliver_volume*($package/$subPackage),
             
              
            ]);
        }
            return "added";
        } catch (\Throwable $th) {
            //throw $th;
        }
          

    }
    public function deliveryView(Request $request){
        $provinces =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("prv_code", Auth::user()->province)
        ->groupBy("province")
        ->get();
        return view('nrp.delivery',compact('provinces'));
    }
    public function getvariety(Request $request){
          $POlist =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')        
        ->where('province', $request->province)
        ->where('municipality', $request->municipal)
        ->where('po', $request->po)
        ->where('tbl_delivery_nrp.delivery_date', $request->date)
        ->get();

        $Data = array();
        foreach ($POlist as $value) {

    
             $actualDelivery =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')                         
             ->where('batchNumber', $value->batchNumber)
             ->where('seed_variety', $value->seed_variety)
            ->sum('package_bags');
            if(!isset($actualDelivery)){
                $actualvolume= 0;
            }else{
                $actualvolume = $actualDelivery;
            }
               array_push($Data,array(
                'batchNumber' => $value->batchNumber,
                'variety' => $value->seed_variety,
                'volume' => $value->volume - $actualvolume, //bags
                'package' => $value->package,
                'sub_package' => $value->sub_package,
                'id' => $value->id,
                'status' => $value->status,
             ));
        }
        return  $Data;
    }




    public function getvarietyNoDate(Request $request){
        $POlist =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')        
      ->where('province', $request->province)
      ->where('municipality', $request->municipal)
      ->where('po', $request->po)      
      ->get();

      $Data = array();
      foreach ($POlist as $value) {

  
           $actualDelivery =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')                         
           ->where('batchNumber', $value->batchNumber)
           ->where('seed_variety', $value->seed_variety)
          ->sum('package_bags');
          if(!isset($actualDelivery)){
              $actualvolume= 0;
          }else{
              $actualvolume = $actualDelivery;
          }
             array_push($Data,array(
              'batchNumber' => $value->batchNumber,
              'variety' => $value->seed_variety,
              'volume' => $value->volume - $actualvolume, //bags
              'package' => $value->package,
              'sub_package' => $value->sub_package,
              'id' => $value->id,
           ));
      }
      return  $Data;
  }

    public function getvarietyDetails(Request $request){
        $POlist =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')
        ->join($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp', 'tbl_delivery_nrp.commitmentId', '=','tbl_commitment_nrp.id')
        ->select('tbl_delivery_nrp.*')
        ->where('province', $request->province)
        ->where('municipal', $request->municipal)
        ->where('po', $request->po)
        ->where('seed_variety', $request->variety)
        ->first();

         $actualDelivery =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')                         
        ->where('commitmentId', $POlist->commitmentId)
        ->where('po', $request->po)
        ->where('seed_variety', $request->variety)
        ->sum('package_bags');
        if(!isset($actualDelivery)){
            $actualvolume= 0;
        }else{
            $actualvolume = $actualDelivery;
        }
      return    $Data = ([
            'volume' => $POlist->volume/$POlist->package - $actualvolume, //bags
            'package' => $POlist->package,
            'package' => $POlist->package,
            'sub_package' => $POlist->sub_package,
            'id' => $POlist->id,
         ]);
        
    }
    
    public function getPO(Request $request){
       return  $POlist =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')        
        ->select('po')
        ->where('province', $request->province)
        ->where('municipality', $request->municipal)
        ->groupBy('po')
        ->get();

    }
    public function confirmationDeliveryView(){
        $provinces =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("regCode_int", Auth::user()->region)
        ->groupBy("province")
        ->get();
        return view('nrp.confimation-delivery-list',compact('provinces'));
    }

    public function seedPostion(){

         $provinces =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("regCode_int", Auth::user()->region)
        ->groupBy("province")
        ->get();

        return view('nrp.seed-postion',compact('provinces'));
    }

    public function saveRatio(Request $request){
        try {          
            $lib_prv =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("regCode_int", Auth::user()->region)
        ->groupBy("regCode_int")
        ->first();                         
        // 1 Bag (15kg) / 3 Packs(5kg)
        // 1 Bag (15kg) / 5 Packs(3kg)
        // 1 Bag (20kg) / 4 Packs(5kg)
        // 1 Bag (18kg) / 3 Packs(6kg)
        // 1 Bag (18kg) / 6 Packs(3kg)
        
         $spec = explode("X",$request->specData);
         if($request->specData == 15 || $request->specData == "15"){
            DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->where('seed_package',15)->delete();
            $dataChecker = 0 ;
            for ($x=0; $x < count($request->fiveKg); $x++) {      
                if($request->fiveKg[$x] < 0){
                    $dataChecker++;
                }
            }
            if($dataChecker<1  ){
                for ($i=0; $i < count($request->fiveKg); $i++) {            
                    DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
                        'regCode' => $lib_prv->regCode,
                        'userId' => Auth::user()->userId,
                        'provinceCode' =>  /* $lib_prv->provCode */ "",
                        'munCode' =>  /* $lib_prv->munCode */ "",
                        'region' =>  $lib_prv->regionName,
                        'province' => /*  $lib_prv->province */ "",
                        'municipality' =>  /* $lib_prv->municipality */ "",
                        'specLabel' => "1 Bag (15kg) / 3 Packs(5kg)",
                        'seed_package' => 15,
                        'seed_sub_package' => 5,
                        'range_start' => $request->initialInput[$i],
                        'range_end' => $request->specInput[$i],
                        'range_volume' => $request->fiveKg[$i],
                    ]);
               }
            }
          


            $dataChecker2 = 0 ;
            for ($z=0; $z < count($request->threeKg); $z++) {      
                if($request->threeKg[$z] < 0){
                    $dataChecker2++;
                }
            }
            if($dataChecker2<1){
                for ($i=0; $i < count($request->threeKg); $i++) {             
                    DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
                        'regCode' => $lib_prv->regCode,
                        'userId' => Auth::user()->userId,
                        'provinceCode' =>  /* $lib_prv->provCode */ "",
                        'munCode' =>  /* $lib_prv->munCode */ "",
                        'region' =>  $lib_prv->regionName,
                        'province' => /*  $lib_prv->province */ "",
                        'municipality' =>  /* $lib_prv->municipality */ "",
                        'specLabel' => "1 Bag (15kg) / 5 Packs(3kg)",
                        'seed_package' => 15,
                        'seed_sub_package' => 3,
                        'range_start' => $request->initialInput[$i],
                        'range_end' => $request->specInput[$i],
                        'range_volume' => $request->threeKg[$i],
                    ]);
                    }
            }

          
         }else  if($request->specData == 18 || $request->specData == "18"){
            DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->where('seed_package',18)->delete();
            $dataChecker3 = 0 ;
            for ($z=0; $z < count($request->fiveKg); $z++) {      
                if($request->fiveKg[$z] < 0){
                    $dataChecker3++;
                }
            }
        
            if($dataChecker3<1 ){
                for ($i=0; $i < count($request->fiveKg); $i++) {             
                    DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
                        'regCode' => $lib_prv->regCode,
                        'userId' => Auth::user()->userId,
                        'provinceCode' =>  /* $lib_prv->provCode */ "",
                        'munCode' =>  /* $lib_prv->munCode */ "",
                        'region' =>  $lib_prv->regionName,
                        'province' => /*  $lib_prv->province */ "",
                        'municipality' =>  /* $lib_prv->municipality */ "",
                        'specLabel' => "1 Bag (18kg) / 3 Packs(6kg)",
                        'seed_package' => 18,
                        'seed_sub_package' => 6,
                        'range_start' => $request->initialInput[$i],
                        'range_end' => $request->specInput[$i],
                        'range_volume' => $request->fiveKg[$i],
                    ]);
               }
            }

            $dataChecker4 = 0 ;
            for ($x=0; $x < count($request->threeKg); $x++) {      
                if($request->threeKg[$x] < 0){
                    $dataChecker4++;
                }
            }
            if($dataChecker4<1){
                for ($i=0; $i < count($request->threeKg); $i++) {             
                    DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
                        'regCode' => $lib_prv->regCode,
                        'userId' => Auth::user()->userId,
                        'provinceCode' =>  /* $lib_prv->provCode */ "",
                        'munCode' =>  /* $lib_prv->munCode */ "",
                        'region' =>  $lib_prv->regionName,
                        'province' => /*  $lib_prv->province */ "",
                        'municipality' =>  /* $lib_prv->municipality */ "",
                        'specLabel' => "1 Bag (18kg) / 6 Packs(3kg)",
                        'seed_package' => 18,
                        'seed_sub_package' => 3,
                        'range_start' => $request->initialInput[$i],
                        'range_end' => $request->specInput[$i],
                        'range_volume' => $request->threeKg[$i],
                    ]);
                    }
            }


         
         }else  if($request->specData == 20 || $request->specData == "20"){
            $dataChecker4 = 0 ;
            
            for ($x=0; $x < count($request->threeKg); $x++) {      
                if($request->threeKg[$x] == 0){
                    $dataChecker4++;
                }
            }
            DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->where('seed_package',20)->delete();
            if($dataChecker4<1 ){              
                for ($i=0; $i < count($request->threeKg); $i++) {             
                    DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
                        'regCode' => $lib_prv->regCode,
                        'userId' => Auth::user()->userId,
                        'provinceCode' =>  /* $lib_prv->provCode */ "",
                        'munCode' =>  /* $lib_prv->munCode */ "",
                        'region' =>  $lib_prv->regionName,
                        'province' => /*  $lib_prv->province */ "",
                        'municipality' =>  /* $lib_prv->municipality */ "",
                        'specLabel' => "1 Bag (20kg) / 4 Packs(5kg)",
                        'seed_package' => 20,
                        'seed_sub_package' => 5,
                        'range_start' => $request->initialInput[$i],
                        'range_end' => $request->specInput[$i],
                        'range_volume' => $request->threeKg[$i],
                    ]);
                    }
            }

            
         }
            

       return "success";
        } catch (\Throwable $th) {
            //throw $th;
        }
        
   
    }

    //public function saveRatio(Request $request){
    //    try {          
    //        $lib_prv =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
    //    ->where("regCode_int", Auth::user()->region)
    //    ->groupBy("regCode_int")
    //    ->first();                         
    //    // 1 Bag (15kg) / 3 Packs(5kg)
    //    // 1 Bag (15kg) / 5 Packs(3kg)
    //    // 1 Bag (20kg) / 4 Packs(5kg)
    //    // 1 Bag (18kg) / 3 Packs(6kg)
    //    // 1 Bag (18kg) / 6 Packs(3kg)
    //    
    //     $spec = explode("X",$request->specData);
    //     if($request->specData == 15 || $request->specData == "15"){
    //        DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->where('seed_package',15)->delete();
    //        $dataChecker = 0 ;
    //        for ($x=0; $x < count($request->fiveKg); $x++) {      
    //            if($request->fiveKg[$x] == 0){
    //                $dataChecker++;
    //            }
    //        }
    //        if($dataChecker<1  && count($request->fiveKg) > 1){
    //            for ($i=0; $i < count($request->fiveKg); $i++) {            
    //                DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
    //                    'regCode' => $lib_prv->regCode,
    //                    'userId' => Auth::user()->userId,
    //                    'provinceCode' =>  /* $lib_prv->provCode */ "",
    //                    'munCode' =>  /* $lib_prv->munCode */ "",
    //                    'region' =>  $lib_prv->regionName,
    //                    'province' => /*  $lib_prv->province */ "",
    //                    'municipality' =>  /* $lib_prv->municipality */ "",
    //                    'specLabel' => "1 Bag (15kg) / 3 Packs(5kg)",
    //                    'seed_package' => 15,
    //                    'seed_sub_package' => 5,
    //                    'range_start' => $request->initialInput[$i],
    //                    'range_end' => $request->specInput[$i],
    //                    'range_volume' => $request->fiveKg[$i],
    //                ]);
    //           }
    //        }
    //      
//
//
    //        $dataChecker2 = 0 ;
    //        for ($z=0; $z < count($request->threeKg); $z++) {      
    //            if($request->threeKg[$z] == 0){
    //                $dataChecker2++;
    //            }
    //        }
    //        if($dataChecker2<1 && count($request->threeKg) > 1){
    //            for ($i=0; $i < count($request->threeKg); $i++) {             
    //                DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
    //                    'regCode' => $lib_prv->regCode,
    //                    'userId' => Auth::user()->userId,
    //                    'provinceCode' =>  /* $lib_prv->provCode */ "",
    //                    'munCode' =>  /* $lib_prv->munCode */ "",
    //                    'region' =>  $lib_prv->regionName,
    //                    'province' => /*  $lib_prv->province */ "",
    //                    'municipality' =>  /* $lib_prv->municipality */ "",
    //                    'specLabel' => "1 Bag (15kg) / 5 Packs(3kg)",
    //                    'seed_package' => 15,
    //                    'seed_sub_package' => 3,
    //                    'range_start' => $request->initialInput[$i],
    //                    'range_end' => $request->specInput[$i],
    //                    'range_volume' => $request->threeKg[$i],
    //                ]);
    //                }
    //        }
//
    //      
    //     }else  if($request->specData == 18 || $request->specData == "18"){
    //        DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->where('seed_package',18)->delete();
    //        $dataChecker3 = 0 ;
    //        for ($z=0; $z < count($request->fiveKg); $z++) {      
    //            if($request->fiveKg[$z] == 0){
    //                $dataChecker3++;
    //            }
    //        }
    //        if($dataChecker3<1 && count($request->fiveKg) > 1){
    //            for ($i=0; $i < count($request->fiveKg); $i++) {             
    //                DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
    //                    'regCode' => $lib_prv->regCode,
    //                    'userId' => Auth::user()->userId,
    //                    'provinceCode' =>  /* $lib_prv->provCode */ "",
    //                    'munCode' =>  /* $lib_prv->munCode */ "",
    //                    'region' =>  $lib_prv->regionName,
    //                    'province' => /*  $lib_prv->province */ "",
    //                    'municipality' =>  /* $lib_prv->municipality */ "",
    //                    'specLabel' => "1 Bag (18kg) / 3 Packs(6kg)",
    //                    'seed_package' => 18,
    //                    'seed_sub_package' => 6,
    //                    'range_start' => $request->initialInput[$i],
    //                    'range_end' => $request->specInput[$i],
    //                    'range_volume' => $request->fiveKg[$i],
    //                ]);
    //           }
    //        }
//
    //        $dataChecker4 = 0 ;
    //        for ($x=0; $x < count($request->threeKg); $x++) {      
    //            if($request->threeKg[$x] == 0){
    //                $dataChecker4++;
    //            }
    //        }
    //        if($dataChecker4<1 && count($request->threeKg) > 1){
    //            for ($i=0; $i < count($request->threeKg); $i++) {             
    //                DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
    //                    'regCode' => $lib_prv->regCode,
    //                    'userId' => Auth::user()->userId,
    //                    'provinceCode' =>  /* $lib_prv->provCode */ "",
    //                    'munCode' =>  /* $lib_prv->munCode */ "",
    //                    'region' =>  $lib_prv->regionName,
    //                    'province' => /*  $lib_prv->province */ "",
    //                    'municipality' =>  /* $lib_prv->municipality */ "",
    //                    'specLabel' => "1 Bag (18kg) / 6 Packs(3kg)",
    //                    'seed_package' => 18,
    //                    'seed_sub_package' => 3,
    //                    'range_start' => $request->initialInput[$i],
    //                    'range_end' => $request->specInput[$i],
    //                    'range_volume' => $request->threeKg[$i],
    //                ]);
    //                }
    //        }
//
//
    //     
    //     }else  if($request->specData == 20 || $request->specData == "20"){
    //        $dataChecker4 = 0 ;
    //        
    //        for ($x=0; $x < count($request->threeKg); $x++) {      
    //            if($request->threeKg[$x] == 0){
    //                $dataChecker4++;
    //            }
    //        }
    //        DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->where('seed_package',20)->delete();
    //        if($dataChecker4<1 && count($request->threeKg) > 1){              
    //            for ($i=0; $i < count($request->threeKg); $i++) {             
    //                DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')->insert([
    //                    'regCode' => $lib_prv->regCode,
    //                    'userId' => Auth::user()->userId,
    //                    'provinceCode' =>  /* $lib_prv->provCode */ "",
    //                    'munCode' =>  /* $lib_prv->munCode */ "",
    //                    'region' =>  $lib_prv->regionName,
    //                    'province' => /*  $lib_prv->province */ "",
    //                    'municipality' =>  /* $lib_prv->municipality */ "",
    //                    'specLabel' => "1 Bag (20kg) / 4 Packs(5kg)",
    //                    'seed_package' => 20,
    //                    'seed_sub_package' => 5,
    //                    'range_start' => $request->initialInput[$i],
    //                    'range_end' => $request->specInput[$i],
    //                    'range_volume' => $request->threeKg[$i],
    //                ]);
    //                }
    //        }
//
    //        
    //     }
    //        
//
    //   return "success";
    //    } catch (\Throwable $th) {
    //        //throw $th;
    //    }
    //    
   //
    //}

    public function getMuni(Request $request){
        $municipality =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where("province", $request->province)
        //->where("prv", Auth::user()->municipality)
        ->groupBy("municipality")
        ->get();

        return json_encode($municipality);
    }
    
    public function saveCommitment(Request $request){
        try {
            $dateDelivery = date_create($request->deliveryDate);
            $request->deliveryDate =  date_format($dateDelivery,"Y-m-d");
            $commitment = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')
            ->where('province', $request->provinceNrp)
            ->where('municipal', $request->municipalityNrp)
            ->where('delivery_date', $request->deliveryDate)
            ->first();
            DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp_logs')->insert([
                'province' => $request->provinceNrp,
                'municipal' => $request->municipalityNrp,
                'volume' => $request->nrpVolume,
                'delivery_date' => $request->deliveryDate,
            ]);
            if(isset($commitment)){
                 DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')
                ->where('province', $request->provinceNrp)
                ->where('municipal', $request->municipalityNrp)
                ->where('delivery_date', $request->deliveryDate)
                ->update([
                    'volume' => $commitment->volume +$request->nrpVolume,
                ]);
                $runningData = $commitment->volume +$request->nrpVolume;
                $this->logs("addtional",$request->nrpVolume,$runningData,Auth::user()->userId);
                return "success%%". $commitment->id;
            }else{
                $id = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')->insertGetId([
                    'province' => $request->provinceNrp,
                    'municipal' => $request->municipalityNrp,
                    'volume' => $request->nrpVolume,
                    'delivery_date' => $request->deliveryDate,
                ]);  
                $this->logs("initial add",$request->nrpVolume,$request->nrpVolume,Auth::user()->userId);
                return "success%%".$id;

            }
            
            
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function confirmationDeliveryDataList(Request $request){
         $query = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')                
        ->select('tbl_delivery_nrp.*',DB::raw('sum(tbl_delivery_nrp.volume) as confirmDelivery'))
        ->groupby('tbl_delivery_nrp.po','tbl_delivery_nrp.delivery_date','tbl_delivery_nrp.batchNumber')
        /* ->get() */;
        
        return Datatables::of($query)
          ->addColumn('actions', function ($query) {

            $href="";
         
            $statusCount = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->select('status')->where('po',$query->po)->get();
            
            $statusState = 0 ;
            foreach ( $statusCount as $key => $value) {
                if($value->status == 1){
                    $statusState++;
                }
            }
            if($statusState == count($statusCount)){
                $delivered=  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->where('batchNumber',$query->batchNumber)->sum('package_bags');  
                
                if($delivered != $query->volume){
                    $href .= '<a href="#" class="btn btn-sm btn-primary continueDelvery" data-id ='.$query->po.'>Continue delivery </a>';
                }else{
                    $href = "completed";    
                }
                
            }else{
                if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->where('batchNumber',$query->batchNumber)->sum('package_bags') != $query->confirmDelivery){
                    $href .= '<a href="'. url("add-delivery/$query->po/$query->delivery_date") .'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon glyphicon-plus-sign"></i> Add Delivery</a>';
                }else{
                    $href = "completed";
                }
            }
            
            if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->where('batchNumber',$query->batchNumber)->count() == 0){
                $href .= '<a href="#" class="btn btn-sm btn-danger delete-btn" data-id ='.$query->po.'><i class="glyphicon glyphicon 	glyphicon glyphicon-trash"></i> Delete</a>';
            } 
           
               return $href;
          })
          ->addColumn('seed_variety', function ($query) {
            $href = '';
            $cDelivery = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('po',$query->po)->where('delivery_date',$query->delivery_date)->get();
            foreach ($cDelivery as $key => $value) {
                $href .= $value->seed_variety."<br>";
            }
               return $href;
          })
          
          ->addColumn('remainingVolume', function ($query) {   
            $delivered=  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->where('batchNumber',$query->batchNumber)->sum('package_bags')   ;      
            if($delivered == 0){
                return $delivered = 0;
            }else{
                return $delivered;
            }
                
          })
          ->make(true);
    }
    public function deleteDop(Request $request){
        try {
            if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')->where('id',$request->id)->delete()){
                return "deleted";
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function deleteconfimDelivered(Request $request){
        try {
            if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('po',$request->id)->delete()){
                return "deleted";
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function deleteDelivered(Request $request){
        try {
            if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_actual_delivery_nrp')->where('id',$request->id)->delete()){
                return "deleted";
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function dopList(Request $request){
         $query = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')
        ->leftjoin($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp','tbl_delivery_nrp.commitmentId','=','tbl_commitment_nrp.id')
        ->select('tbl_commitment_nrp.*',DB::raw(' if(tbl_commitment_nrp.volume - sum(tbl_delivery_nrp.volume) is null,tbl_commitment_nrp.volume,tbl_commitment_nrp.volume - sum(tbl_delivery_nrp.volume)) as remainingVolume'))
        ->groupBy('tbl_commitment_nrp.id')
        ->having('remainingVolume','>',0);
    /*     ->get(); */
        return Datatables::of($query)
        ->addColumn('confirmDelivery', function ($query) {
          $query = DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('commitmentId',$query->id)->sum('volume');
          if(isset($query)){
            return $query;
          }else{
            return 0;
          }
        })
          ->addColumn('actions', function ($query) {

            $href = '<a href="'. url("delivery-confrimation/$query->id") .'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon glyphicon-plus-sign"></i> Add PO</a>';
            /* $href .= '<a href="'. url("delivery-confrimation/$query->id") .'" class="btn btn-sm btn-warning"><i class="glyphicon glyphicon glyphicon-edit"></i> Edit Details</a>'; */
            if($query->volume == $query->remainingVolume){
                $href .= '<a href="#" data-id="'.$query->id.'" class="btn btn-sm btn-danger delete-funtion"><i class="glyphicon glyphicon glyphicon glyphicon-trash"></i> delete</a>';
            }
            
               return $href;
          })

          ->addColumn('remainingVolume', function ($query) {

                if($query->remainingVolume == null){
                    return $query->volume ;
                }else{
                    return $query->remainingVolume;
                }
             
          })
          ->make(true);
    }
    public function confirmationDelivery($id){
        
        $commitmentData =  DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_commitment_nrp')
        ->leftjoin($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp','tbl_delivery_nrp.commitmentId','=','tbl_commitment_nrp.id')
        ->select('tbl_commitment_nrp.*',DB::raw(' if(tbl_commitment_nrp.volume - sum(tbl_delivery_nrp.volume) is null,tbl_commitment_nrp.volume,tbl_commitment_nrp.volume - sum(tbl_delivery_nrp.volume)) as remainingVolume'))
        ->where("tbl_commitment_nrp.id", $id)
        ->first();
          $specInfo = DB::table($GLOBALS['season_prefix'].'nrp_seeds.lib_computation_profile')
        ->where('regCode',substr(Auth::user()->province,0,2))
        ->groupBy('seed_package','seed_sub_package')
        ->get();
         
        return view('nrp.confimation-delivery',compact('commitmentData','specInfo'));
    }
    public function confirmationDeliverySave(Request $request){   
        //dd(json_decode(json_encode($request->commimentinfo),true));

        $newarray = array();
        $stopper =0;
        $new_batch_number = "";
        while($stopper !=1){
            $new_batch_number = Auth::user()->userId."-BCH-".time();
            if(DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->where('batchNumber',$new_batch_number)->count()==0){
                $stopper=1;
            }

        }

        foreach(json_decode(json_encode($request->commimentinfo),true) as $k => $v) 
        {
            $t = $v;
            $t['batchNumber'] = $new_batch_number;
            $newarray[$k] = $t;
        }
        //return  $newarray;
        try {
            DB::table($GLOBALS['season_prefix'].'nrp_seeds.tbl_delivery_nrp')->insert($newarray);  
            return "success";
        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }
    private function logs($transaction,$bags,$runningBal,$user){
         DB::table($GLOBALS['season_prefix'].'nrp_seeds.log')->insertGetId([
            'transanction' => $transaction,
            'volume' => $bags,
            'runningInventory' => $runningBal,
            'user_id' => $user,
        ]); 
    }
    
}
