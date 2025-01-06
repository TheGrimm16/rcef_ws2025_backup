<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Redirect,Response;
use Auth;
class calendarController extends Controller
{

    public function __construct()
    {
     $this->geotag_con = 'geotag_db';
    }
    
    public function calendarList(){
        $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        ->where('prv', 'like',Auth::user()->province."%")
        ->first();
        $province = $province->province;
        $barangay = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
        ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'crop_production.farmer_id_fk', '=','farmer_info.fid')      
        ->select('farmer_info.barangay as barangay')
        ->where('add_province', "like",$province)
        ->groupBy('farmer_info.barangay')
        ->get();
        
        return view('palaysikatan.calendar')
        ->with("barangay", $barangay);
       }

      

       /* public function calendarData(){
           $cropStab=[
            'manual_transplanting',
            'mechanized_transplanting',
            'drum_seeding',
            'drum_seeding_sp',
           ];

           foreach ($cropStab as  $cropStabData) {
              
             $activity = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_activity')->where('crop_stablishment',$cropStabData)->get();
             if(count( $activity)>0){
                foreach ($activity as  $data){

                     $farmer = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'crop_production.farmer_id_fk','=','farmer_info.fid')   
                    ->select('*',DB::raw('count(date_sown) as date_sown_data'))
                    ->where('crop_production.crop_establishment',$cropStabData)
                    ->groupBy('crop_production.date_sown')                 
                    ->get();
                   foreach ($farmer as $value) {
                    $result_data[]=array('id'=> 1,
                    'title'   =>  substr($data->activity,0,39) .'('.$value->date_sown_data.')',
                    'start'   =>  date('Y-m-d', strtotime($value->date_sown . ' +'.$data->day.' day')),
                    'end'   =>  date('Y-m-d', strtotime($value->date_sown . ' +'.$data->day.' day')),
                    'crop'   =>  $cropStabData,
                    //'Address'   =>    $value->add_region.", ".$value->add_province.", ".$value->add_municipality,
                    'activity'   =>  $data->activity.'('.$value->date_sown_data.')',
                    );
                   }
                    

                   
                    
                }
             }
           

           }
        return Response::json($result_data);
       } */

       public function calendarData(){

        $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        ->where('prv', 'like',Auth::user()->province."%")
        ->first();

   $province = $province->province;

   if(Auth::user()->province == ""){
        $province = "No Province";
   }

   if(Auth::user()->username == "r.benedicto"){
        $province = "%";
   }



       $farmer = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
      ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'crop_production.farmer_id_fk', '=','farmer_info.fid')      
      ->select('*','farmer_info.fid as farmer_Id')
      ->where('add_province', "like",$province)
      ->get();
     
        $result_data;
        foreach ($farmer as  $value) {
            if($value->date_sown=="0000-00-00"){
                continue;
            }
             $variety = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.seed_variety')->where('variety',$value->variety_planted)->limit(1)->first();
            if(count($variety)>=1){
               
             $activity = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_activity')
             ->orWhere('crop_stablishment', 'like', '%'.$value->crop_establishment.'%')
             ->get();
                foreach ($activity as  $data){
                
                    
                    
                    $result_data[]=array('id'=> $value->farmer_Id,
                    'title'   =>   $value->r_lastName." ".$value->r_firstName." ".$value->r_middleName." : ".substr($data->activity,0,40),
                    'start'   =>  date('Y-m-d', strtotime($value->date_sown . ' +'.$data->day.' day')),
                    'end'   =>  date('Y-m-d', strtotime($value->date_sown . ' +'.$data->day.' day')),
                    //'farmer'   =>  $value->r_lastName." ".$value->r_firstName." ".$value->r_middleName,
                    'Address'   =>    $value->add_region.", ".$value->add_province.", ".$value->add_municipality.", ".$value->barangay,
                    'crop'   =>  $value->crop_establishment,
                    'activity'   =>  $data->activity,
                    'barangay'   =>  $value->barangay,
                    );
                }

                $result_data[]=array('id'=> $value->farmer_Id,
                'title'   =>   $value->r_lastName." ".$value->r_firstName." ".$value->r_middleName." : Harvesting . 85 % Physiological Maturity",
                'start'   =>  date('Y-m-d', strtotime($value->date_sown . ' +'.$variety->maturity.' day')),
                'end'   =>  date('Y-m-d', strtotime($value->date_sown . ' +'.$variety->maturity.' day')),
                //'farmer'   =>  $value->r_lastName." ".$value->r_firstName." ".$value->r_middleName,
                'Address'   =>    $value->add_region.", ".$value->add_province.", ".$value->add_municipality.", ".$value->barangay,
                'crop'   =>  $value->crop_establishment,
                'activity'   =>  "Harvesting . 85 % Physiological Maturity",
                'barangay'   =>  $value->barangay,
                );
                
            }

        }
       

        return Response::json($result_data);
       }

       
}
