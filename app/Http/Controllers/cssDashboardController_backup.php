<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Yajra\Datatables\Datatables;
use Auth;
use Excel;

class cssDashboardController extends Controller
{
    public function index2(){
        return view('cssDashboard.index2');
    }
    public function index(){

        $rawbep = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select(DB::raw('LEFT(rcef_id, 4) as prvs'))
            ->orderBy('rcef_id', 'ASC')
            ->get();

        $prvArr = array();
        foreach($rawbep as $row){
            array_push($prvArr, $row->prvs);
        }
        $totalbep = count($prvArr);

        $rawbep = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select(DB::raw('LEFT(rcef_id, 4) as prvs'))
            ->orderBy('rcef_id', 'ASC')
            ->get();

        $prvArr = array();
        foreach($rawbep as $row){
            array_push($prvArr, $row->prvs);
        }
        $totalbep = count($prvArr);

        $rawGender = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->join($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'ebinhi_response.rcef_id', '=', 'sed_verified.rcef_id')
            ->select(DB::raw('count(sed_verified.sed_id) as count, sed_verified.ver_sex'))
            ->groupBy(DB::raw('LEFT(sed_verified.ver_sex, 1)'))
            ->orderBy(DB::raw('LEFT(sed_verified.ver_sex, 1)'))
            ->get();

        // dd($rawGender);
        if(count($rawGender) > 0){
            $maleCount = $rawGender[1]->count;
            $femaleCount = $rawGender[0]->count;
            
            $malePerc = number_format((($maleCount / ($maleCount + $femaleCount)) * 100), 2);
            $femalePerc = number_format((($femaleCount / ($maleCount + $femaleCount)) * 100), 2);
        }else{
            $maleCount = 0;
            $femaleCount = 0;

            $malePerc = 0;
            $femalePerc = 0;
        }


        
        $questions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->get();

        $total_respoondents = count($raw_results);
        $q1 = array(
            "id" => "q_1",
            "qs" => "1. Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        $q2 = array(
            "id" => "q_2",
            "qs" => "2. Nalaman ko ng mas maaga ang skedyul dahil sa text.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q3 = array(
            "id" => "q_3",
            "qs" => "3. Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q4 = array(
            "id" => "q_4",
            "qs" => "4. Mas malapit ang pinagkuhanan ko ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q5 = array(
            "id" => "q_5",
            "qs" => "5. Mas maikli na ang pila sa pagkuha ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "id" => "q_6",
            "qs" => "6. Mas mabilis ang pagkuha ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q7 = array(
            "id" => "q_7",
            "qs" => "7. Mas patas ang pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q8 = array(
            "id" => "q_8",
            "qs" => "8. Mas nasunod ang tamang alokasyon ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q9 = array(
            "id" => "q_9",
            "qs" => "9. Mas lumaki ang tsansang makuha ang gusto kong variety ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q10 = array(
            "id" => "q_10",
            "qs" => "10. Mas panatag ang loob ko sa namimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q11 = array(
            "id" => "q_11",
            "qs" => "11. Mas maayos ang sistema ng pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q12 = array(
            "id" => "q_12",
            "qs" => "12. Mas mabuti ang kabuuang karanasan ko sa pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q15 = array(
            "id" => "q_15",
            "qs" => "14. Nakatanggap ka rin ba ng kalendaryo at leaflet ngayon galing sa RCEF?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );

        foreach($raw_results as $row){
            if($row->q_1 == "oo"){
                $q1["yes"]++;
            }else if($row->q_1 == "hindi"){
                $q1["no"]++;
            }else{
                $q1["maybe"]++;
            }

            if($row->q_2 == "sang-ayon"){
                $q2["agree"]++;
            }else if($row->q_2 == "hindi_sang-ayon"){
                $q2["disagree"]++;
            }else if($row->q_2 == "walang_kinikilingan"){
                $q2["neutral"]++;
            }else{
                $q2["none"]++;
            }

            if($row->q_3 == "sang-ayon"){
                $q3["agree"]++;
            }else if($row->q_3 == "hindi_sang-ayon"){
                $q3["disagree"]++;
            }else if($row->q_3 == "walang_kinikilingan"){
                $q3["neutral"]++;
            }else{
                $q3["none"]++;
            }

            if($row->q_4 == "sang-ayon"){
                $q4["agree"]++;
            }else if($row->q_4 == "hindi_sang-ayon"){
                $q4["disagree"]++;
            }else if($row->q_4 == "walang_kinikilingan"){
                $q4["neutral"]++;
            }else{
                $q4["none"]++;
            }

            if($row->q_5 == "sang-ayon"){
                $q5["agree"]++;
            }else if($row->q_5 == "hindi_sang-ayon"){
                $q5["disagree"]++;
            }else if($row->q_5 == "walang_kinikilingan"){
                $q5["neutral"]++;
            }else{
                $q5["none"]++;
            }

            if($row->q_6 == "sang-ayon"){
                $q6["agree"]++;
            }else if($row->q_6 == "hindi_sang-ayon"){
                $q6["disagree"]++;
            }else if($row->q_6 == "walang_kinikilingan"){
                $q6["neutral"]++;
            }else{
                $q6["none"]++;
            }

            if($row->q_7 == "sang-ayon"){
                $q7["agree"]++;
            }else if($row->q_7 == "hindi_sang-ayon"){
                $q7["disagree"]++;
            }else if($row->q_7 == "walang_kinikilingan"){
                $q7["neutral"]++;
            }else{
                $q7["none"]++;
            }

            if($row->q_8 == "sang-ayon"){
                $q8["agree"]++;
            }else if($row->q_8 == "hindi_sang-ayon"){
                $q8["disagree"]++;
            }else if($row->q_8 == "walang_kinikilingan"){
                $q8["neutral"]++;
            }else{
                $q8["none"]++;
            }

            if($row->q_9 == "sang-ayon"){
                $q9["agree"]++;
            }else if($row->q_9 == "hindi_sang-ayon"){
                $q9["disagree"]++;
            }else if($row->q_9 == "walang_kinikilingan"){
                $q9["neutral"]++;
            }else{
                $q9["none"]++;
            }

            if($row->q_10 == "sang-ayon"){
                $q10["agree"]++;
            }else if($row->q_10 == "hindi_sang-ayon"){
                $q10["disagree"]++;
            }else if($row->q_10 == "walang_kinikilingan"){
                $q10["neutral"]++;
            }else{
                $q10["none"]++;
            }

            if($row->q_11 == "sang-ayon"){
                $q11["agree"]++;
            }else if($row->q_11 == "hindi_sang-ayon"){
                $q11["disagree"]++;
            }else if($row->q_11 == "walang_kinikilingan"){
                $q11["neutral"]++;
            }else{
                $q11["none"]++;
            }

            if($row->q_12 == "sang-ayon"){
                $q12["agree"]++;
            }else if($row->q_12 == "hindi_sang-ayon"){
                $q12["disagree"]++;
            }else if($row->q_12 == "walang_kinikilingan"){
                $q12["neutral"]++;
            }else{
                $q12["none"]++;
            }

            if($row->q_15 == "oo"){
                $q15["yes"]++;
            }else if($row->q_15 == "hindi"){
                $q15["no"]++;
            }else{
                $q15["maybe"]++;
            }
        }

        if($total_respoondents > 0){
            $q1["yes"] = number_format((($q1["yes"] / $total_respoondents) * 100), 2);
        $q1["no"] = number_format((($q1["no"] / $total_respoondents) * 100), 2);
        $q1["maybe"] = number_format((($q1["maybe"] / $total_respoondents) * 100), 2);
        
        $q2["agree"] = number_format((($q2["agree"] / $total_respoondents) * 100), 2);
        $q2["disagree"] = number_format((($q2["disagree"] / $total_respoondents) * 100), 2);
        $q2["neutral"] = number_format((($q2["neutral"] / $total_respoondents) * 100), 2);
        $q2["none"] = number_format((($q2["none"] / $total_respoondents) * 100), 2);
        
        $q3["agree"] = number_format((($q3["agree"] / $total_respoondents) * 100), 2);
        $q3["disagree"] = number_format((($q3["disagree"] / $total_respoondents) * 100), 2);
        $q3["neutral"] = number_format((($q3["neutral"] / $total_respoondents) * 100), 2);
        $q3["none"] = number_format((($q3["none"] / $total_respoondents) * 100), 2);
        
        $q4["agree"] = number_format((($q4["agree"] / $total_respoondents) * 100), 2);
        $q4["disagree"] = number_format((($q4["disagree"] / $total_respoondents) * 100), 2);
        $q4["neutral"] = number_format((($q4["neutral"] / $total_respoondents) * 100), 2);
        $q4["none"] = number_format((($q4["none"] / $total_respoondents) * 100), 2);

        $q5["agree"] = number_format((($q5["agree"] / $total_respoondents) * 100), 2);
        $q5["disagree"] = number_format((($q5["disagree"] / $total_respoondents) * 100), 2);
        $q5["neutral"] = number_format((($q5["neutral"] / $total_respoondents) * 100), 2);
        $q5["none"] = number_format((($q5["none"] / $total_respoondents) * 100), 2);

        $q6["agree"] = number_format((($q6["agree"] / $total_respoondents) * 100), 2);
        $q6["disagree"] = number_format((($q6["disagree"] / $total_respoondents) * 100), 2);
        $q6["neutral"] = number_format((($q6["neutral"] / $total_respoondents) * 100), 2);
        $q6["none"] = number_format((($q6["none"] / $total_respoondents) * 100), 2);

        $q7["agree"] = number_format((($q7["agree"] / $total_respoondents) * 100), 2);
        $q7["disagree"] = number_format((($q7["disagree"] / $total_respoondents) * 100), 2);
        $q7["neutral"] = number_format((($q7["neutral"] / $total_respoondents) * 100), 2);
        $q7["none"] = number_format((($q7["none"] / $total_respoondents) * 100), 2);

        $q8["agree"] = number_format((($q8["agree"] / $total_respoondents) * 100), 2);
        $q8["disagree"] = number_format((($q8["disagree"] / $total_respoondents) * 100), 2);
        $q8["neutral"] = number_format((($q8["neutral"] / $total_respoondents) * 100), 2);
        $q8["none"] = number_format((($q8["none"] / $total_respoondents) * 100), 2);
        
        $q9["agree"] = number_format((($q9["agree"] / $total_respoondents) * 100), 2);
        $q9["disagree"] = number_format((($q9["disagree"] / $total_respoondents) * 100), 2);
        $q9["neutral"] = number_format((($q9["neutral"] / $total_respoondents) * 100), 2);
        $q9["none"] = number_format((($q9["none"] / $total_respoondents) * 100), 2);
        
        $q10["agree"] = number_format((($q10["agree"] / $total_respoondents) * 100), 2);
        $q10["disagree"] = number_format((($q10["disagree"] / $total_respoondents) * 100), 2);
        $q10["neutral"] = number_format((($q10["neutral"] / $total_respoondents) * 100), 2);
        $q10["none"] = number_format((($q10["none"] / $total_respoondents) * 100), 2);
        
        $q11["agree"] = number_format((($q11["agree"] / $total_respoondents) * 100), 2);
        $q11["disagree"] = number_format((($q11["disagree"] / $total_respoondents) * 100), 2);
        $q11["neutral"] = number_format((($q11["neutral"] / $total_respoondents) * 100), 2);
        $q11["none"] = number_format((($q11["none"] / $total_respoondents) * 100), 2);
        
        $q12["agree"] = number_format((($q12["agree"] / $total_respoondents) * 100), 2);
        $q12["disagree"] = number_format((($q12["disagree"] / $total_respoondents) * 100), 2);
        $q12["neutral"] = number_format((($q12["neutral"] / $total_respoondents) * 100), 2);
        $q12["none"] = number_format((($q12["none"] / $total_respoondents) * 100), 2);
        
        $q15["yes"] = number_format((($q15["yes"] / $total_respoondents) * 100), 2);
        $q15["no"] = number_format((($q15["no"] / $total_respoondents) * 100), 2);
        $q15["maybe"] = number_format((($q15["maybe"] / $total_respoondents) * 100), 2);
        }
        

        $perc_questions = array(
            "q1" => $q1,
            "q2" => $q2,
            "q3" => $q3,
            "q4" => $q4,
            "q5" => $q5,
            "q6" => $q6,
            "q7" => $q7,
            "q8" => $q8,
            "q9" => $q9,
            "q10" => $q10,
            "q11" => $q11,
            "q12" => $q12,
            "q15" => $q15,
        );

        // dd($perc_questions);

        return view('cssDashboard.index2', compact(
            'questions',
            'totalbep',
            'femalePerc',
            'malePerc',
            'perc_questions'
        ));
    }

    public function getBdays(){
        $rawbep = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select(DB::raw('LEFT(rcef_id, 4) as prvs, rcef_id'))
            ->orderBy('rcef_id', 'ASC')
            ->get();

        $prvArr = array();
        foreach($rawbep as $row){
            array_push($prvArr, $row->prvs);
        }
        // dd($rawbep);
        $bdays = array();
        foreach($rawbep as $row){
            $rawBdays = DB::table($GLOBALS['season_prefix'].'prv_'.$row->prvs.'.farmer_information_final')
                ->select('birthdate')
                ->get();
            array_push($bdays, $rawBdays[0]->birthdate);
        }
        // dd($bdays);

        $ages = array();
        foreach($bdays as $row){
            $birthday = explode('/', $row);
            array_push($ages, (date("md", date("U", mktime(0, 0, 0, $birthday[0], $birthday[1], $birthday[2]))) > date("md")
            ? ((date("Y") - $birthday[2]) - 1)
            : (date("Y") - $birthday[2])));
        }

        // dd($ages);
        $leastAgeBr = 0;
        $middleAgeBr = 0;
        $lastAgeBr = 0;
        foreach($ages as $row){
            if($row <= 29){
                $leastAgeBr = $leastAgeBr + 1;
            }else if($row <= 59){
                $middleAgeBr = $middleAgeBr + 1;
            }else if($row >= 60){
                $lastAgeBr = $lastAgeBr + 1;
            }
        }

        $leastperc = number_format(($leastAgeBr / count($ages) * 100), 2);
        $middleperc = number_format(($middleAgeBr / count($ages) * 100), 2);
        $lastperc = number_format(($lastAgeBr / count($ages) * 100), 2);
        return array(
            "least" => $leastperc,
            "middle" => $middleperc,
            "last" => $lastperc
        );
    }

    public function getBepQuestionResults(){
        $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->get();

        $total_respoondents = count($raw_results);
        $q1 = array(
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        $q2 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q3 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q4 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q5 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q7 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q8 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q9 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q10 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q11 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q12 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q15 = array(
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );

        foreach($raw_results as $row){
            if($row->q_1 == "oo"){
                $q1["yes"]++;
            }else if($row->q_1 == "hindi"){
                $q1["no"]++;
            }else{
                $q1["maybe"]++;
            }

            if($row->q_2 == "sang-ayon"){
                $q2["agree"]++;
            }else if($row->q_2 == "hindi_sang-ayon"){
                $q2["disagree"]++;
            }else if($row->q_2 == "walang_kinikilingan"){
                $q2["neutral"]++;
            }else{
                $q2["none"]++;
            }

            if($row->q_3 == "sang-ayon"){
                $q3["agree"]++;
            }else if($row->q_3 == "hindi_sang-ayon"){
                $q3["disagree"]++;
            }else if($row->q_3 == "walang_kinikilingan"){
                $q3["neutral"]++;
            }else{
                $q3["none"]++;
            }

            if($row->q_4 == "sang-ayon"){
                $q4["agree"]++;
            }else if($row->q_4 == "hindi_sang-ayon"){
                $q4["disagree"]++;
            }else if($row->q_4 == "walang_kinikilingan"){
                $q4["neutral"]++;
            }else{
                $q4["none"]++;
            }

            if($row->q_5 == "sang-ayon"){
                $q5["agree"]++;
            }else if($row->q_5 == "hindi_sang-ayon"){
                $q5["disagree"]++;
            }else if($row->q_5 == "walang_kinikilingan"){
                $q5["neutral"]++;
            }else{
                $q5["none"]++;
            }

            if($row->q_6 == "sang-ayon"){
                $q6["agree"]++;
            }else if($row->q_6 == "hindi_sang-ayon"){
                $q6["disagree"]++;
            }else if($row->q_6 == "walang_kinikilingan"){
                $q6["neutral"]++;
            }else{
                $q6["none"]++;
            }

            if($row->q_7 == "sang-ayon"){
                $q7["agree"]++;
            }else if($row->q_7 == "hindi_sang-ayon"){
                $q7["disagree"]++;
            }else if($row->q_7 == "walang_kinikilingan"){
                $q7["neutral"]++;
            }else{
                $q7["none"]++;
            }

            if($row->q_8 == "sang-ayon"){
                $q8["agree"]++;
            }else if($row->q_8 == "hindi_sang-ayon"){
                $q8["disagree"]++;
            }else if($row->q_8 == "walang_kinikilingan"){
                $q8["neutral"]++;
            }else{
                $q8["none"]++;
            }

            if($row->q_9 == "sang-ayon"){
                $q9["agree"]++;
            }else if($row->q_9 == "hindi_sang-ayon"){
                $q9["disagree"]++;
            }else if($row->q_9 == "walang_kinikilingan"){
                $q9["neutral"]++;
            }else{
                $q9["none"]++;
            }

            if($row->q_10 == "sang-ayon"){
                $q10["agree"]++;
            }else if($row->q_10 == "hindi_sang-ayon"){
                $q10["disagree"]++;
            }else if($row->q_10 == "walang_kinikilingan"){
                $q10["neutral"]++;
            }else{
                $q10["none"]++;
            }

            if($row->q_11 == "sang-ayon"){
                $q11["agree"]++;
            }else if($row->q_11 == "hindi_sang-ayon"){
                $q11["disagree"]++;
            }else if($row->q_11 == "walang_kinikilingan"){
                $q11["neutral"]++;
            }else{
                $q11["none"]++;
            }

            if($row->q_12 == "sang-ayon"){
                $q12["agree"]++;
            }else if($row->q_12 == "hindi_sang-ayon"){
                $q12["disagree"]++;
            }else if($row->q_12 == "walang_kinikilingan"){
                $q12["neutral"]++;
            }else{
                $q12["none"]++;
            }

            if($row->q_15 == "oo"){
                $q15["yes"]++;
            }else if($row->q_15 == "hindi"){
                $q15["no"]++;
            }else{
                $q15["maybe"]++;
            }
        }

        $q1["yes"] = number_format((($q1["yes"] / $total_respoondents) * 100), 2);
        $q1["no"] = number_format((($q1["no"] / $total_respoondents) * 100), 2);
        $q1["maybe"] = number_format((($q1["maybe"] / $total_respoondents) * 100), 2);
        
        $q2["agree"] = number_format((($q2["agree"] / $total_respoondents) * 100), 2);
        $q2["disagree"] = number_format((($q2["disagree"] / $total_respoondents) * 100), 2);
        $q2["neutral"] = number_format((($q2["neutral"] / $total_respoondents) * 100), 2);
        $q2["none"] = number_format((($q2["none"] / $total_respoondents) * 100), 2);
        
        $q3["agree"] = number_format((($q3["agree"] / $total_respoondents) * 100), 2);
        $q3["disagree"] = number_format((($q3["disagree"] / $total_respoondents) * 100), 2);
        $q3["neutral"] = number_format((($q3["neutral"] / $total_respoondents) * 100), 2);
        $q3["none"] = number_format((($q3["none"] / $total_respoondents) * 100), 2);
        
        $q4["agree"] = number_format((($q4["agree"] / $total_respoondents) * 100), 2);
        $q4["disagree"] = number_format((($q4["disagree"] / $total_respoondents) * 100), 2);
        $q4["neutral"] = number_format((($q4["neutral"] / $total_respoondents) * 100), 2);
        $q4["none"] = number_format((($q4["none"] / $total_respoondents) * 100), 2);

        $q5["agree"] = number_format((($q5["agree"] / $total_respoondents) * 100), 2);
        $q5["disagree"] = number_format((($q5["disagree"] / $total_respoondents) * 100), 2);
        $q5["neutral"] = number_format((($q5["neutral"] / $total_respoondents) * 100), 2);
        $q5["none"] = number_format((($q5["none"] / $total_respoondents) * 100), 2);

        $q6["agree"] = number_format((($q6["agree"] / $total_respoondents) * 100), 2);
        $q6["disagree"] = number_format((($q6["disagree"] / $total_respoondents) * 100), 2);
        $q6["neutral"] = number_format((($q6["neutral"] / $total_respoondents) * 100), 2);
        $q6["none"] = number_format((($q6["none"] / $total_respoondents) * 100), 2);

        $q7["agree"] = number_format((($q7["agree"] / $total_respoondents) * 100), 2);
        $q7["disagree"] = number_format((($q7["disagree"] / $total_respoondents) * 100), 2);
        $q7["neutral"] = number_format((($q7["neutral"] / $total_respoondents) * 100), 2);
        $q7["none"] = number_format((($q7["none"] / $total_respoondents) * 100), 2);

        $q8["agree"] = number_format((($q8["agree"] / $total_respoondents) * 100), 2);
        $q8["disagree"] = number_format((($q8["disagree"] / $total_respoondents) * 100), 2);
        $q8["neutral"] = number_format((($q8["neutral"] / $total_respoondents) * 100), 2);
        $q8["none"] = number_format((($q8["none"] / $total_respoondents) * 100), 2);
        
        $q9["agree"] = number_format((($q9["agree"] / $total_respoondents) * 100), 2);
        $q9["disagree"] = number_format((($q9["disagree"] / $total_respoondents) * 100), 2);
        $q9["neutral"] = number_format((($q9["neutral"] / $total_respoondents) * 100), 2);
        $q9["none"] = number_format((($q9["none"] / $total_respoondents) * 100), 2);
        
        $q10["agree"] = number_format((($q10["agree"] / $total_respoondents) * 100), 2);
        $q10["disagree"] = number_format((($q10["disagree"] / $total_respoondents) * 100), 2);
        $q10["neutral"] = number_format((($q10["neutral"] / $total_respoondents) * 100), 2);
        $q10["none"] = number_format((($q10["none"] / $total_respoondents) * 100), 2);
        
        $q11["agree"] = number_format((($q11["agree"] / $total_respoondents) * 100), 2);
        $q11["disagree"] = number_format((($q11["disagree"] / $total_respoondents) * 100), 2);
        $q11["neutral"] = number_format((($q11["neutral"] / $total_respoondents) * 100), 2);
        $q11["none"] = number_format((($q11["none"] / $total_respoondents) * 100), 2);
        
        $q12["agree"] = number_format((($q12["agree"] / $total_respoondents) * 100), 2);
        $q12["disagree"] = number_format((($q12["disagree"] / $total_respoondents) * 100), 2);
        $q12["neutral"] = number_format((($q12["neutral"] / $total_respoondents) * 100), 2);
        $q12["none"] = number_format((($q12["none"] / $total_respoondents) * 100), 2);
        
        $q15["yes"] = number_format((($q15["yes"] / $total_respoondents) * 100), 2);
        $q15["no"] = number_format((($q15["no"] / $total_respoondents) * 100), 2);
        $q15["maybe"] = number_format((($q15["maybe"] / $total_respoondents) * 100), 2);
        

        return array(
            "q1" => $q1,
            "q2" => $q2,
            "q3" => $q3,
            "q4" => $q4,
            "q5" => $q5,
            "q6" => $q6,
            "q7" => $q7,
            "q8" => $q8,
            "q9" => $q9,
            "q10" => $q10,
            "q11" => $q11,
            "q12" => $q12,
            "q15" => $q15,
        );
            // dd($raw_results);
    }

    public function getIndivQuestion(Request $request){
        $question = $request->question;
        $province = $request->prv;
        $municipality = $request->mun;

        if($province == "All"){
            $province = "%";
        }

        if($municipality == "All"){
            $municipality = "%";
        }

        $rawQs = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select(DB::raw($question.' as ans'), DB::raw('count('.$question.') as raw'))
            ->where('province', 'like', $province)
            ->where('municipality', 'like', $municipality)
            ->groupBy($question)
            ->orderBy($question)
            ->get();
        // dd($rawQs);
        
        
        $qsType = "";
        $qs_yn = array(
            "type" => "yn",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0
        );
        $qs_sp = array(
            "type" => "sp",
            "agree" => 0,
            "disagree" => 0, 
            "neutral" => 0,
            "none" => 0
        );

        //yn
        $y = 0;
        $n = 0;
        $m = 0;
        //end yn
        
        //sp
        $agree = 0;
        $disagree = 0;
        $neutral = 0;
        $none = 0;
        //end sp

        $qs_struct = array();
        if($question == "q_1" || $question == "q_15"){
            $qsType = "yn";
            foreach($rawQs as $row){
                if($row->ans == "hindi"){
                    $n = $row->raw;
                }else if($row->ans == "oo"){
                    $y = $row->raw;
                }else{
                    $m = $row->raw;
                }
            }
        }else{
            $qsType = "sp";
            foreach($rawQs as $row){
                if($row->ans == "hindi_sang-ayon"){
                    $disagree = $row->raw;
                }else if($row->ans == "sang-ayon"){
                    $agree = $row->raw;
                }else if($row->ans == "walang_kinikilingan"){
                    $neutral = $row->raw;
                }else{
                    $none = $row->raw;
                }
            }
        }

        if($qsType == "yn"){
            $qs_struct = array(
                "type" => "yn",
                "yes" => (int)$y,
                "no" => (int)$n,
                "maybe" => (int)$m
            );
        }
        else{
            $qs_struct = array(
                "type" => "sp",
                "agree" => (int)$agree,
                "disagree" => (int)$disagree, 
                "neutral" => (int)$neutral,
                "none" => (int)$none
            );
        }

        // dd($qs_struct);
        return $qs_struct;
    }

    public function exportStats(Request $request){
        $province = $request->prv;
        $municipality = $request->mun;
        $province_name = $request->prv;
        $municipality_name = $request->mun;

        if($province == "All"){
            $province = "%";
            $province_name = "ALL";
        }
        if($municipality == "All"){
            $municipality = "%";
            $municipality_name = "ALL";
        }

        $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->where('province', 'like', $province)
            ->where('municipality', 'like', $municipality)
            ->get();

        $total_respoondents = count($raw_results);
        $q1 = array(
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
            "na" => "Not Applicable",
        );
        $q2 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q3 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q4 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q5 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q7 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q8 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q9 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q10 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q11 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q12 = array(
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q15 = array(
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
            "na" => "Not Applicable",
        );

        foreach($raw_results as $row){
            if($row->q_1 == "oo"){
                $q1["yes"]++;
            }else if($row->q_1 == "hindi"){
                $q1["no"]++;
            }else{
                $q1["maybe"]++;
            }

            if($row->q_2 == "sang-ayon"){
                $q2["agree"]++;
            }else if($row->q_2 == "hindi_sang-ayon"){
                $q2["disagree"]++;
            }else if($row->q_2 == "walang_kinikilingan"){
                $q2["neutral"]++;
            }else{
                $q2["none"]++;
            }

            if($row->q_3 == "sang-ayon"){
                $q3["agree"]++;
            }else if($row->q_3 == "hindi_sang-ayon"){
                $q3["disagree"]++;
            }else if($row->q_3 == "walang_kinikilingan"){
                $q3["neutral"]++;
            }else{
                $q3["none"]++;
            }

            if($row->q_4 == "sang-ayon"){
                $q4["agree"]++;
            }else if($row->q_4 == "hindi_sang-ayon"){
                $q4["disagree"]++;
            }else if($row->q_4 == "walang_kinikilingan"){
                $q4["neutral"]++;
            }else{
                $q4["none"]++;
            }

            if($row->q_5 == "sang-ayon"){
                $q5["agree"]++;
            }else if($row->q_5 == "hindi_sang-ayon"){
                $q5["disagree"]++;
            }else if($row->q_5 == "walang_kinikilingan"){
                $q5["neutral"]++;
            }else{
                $q5["none"]++;
            }

            if($row->q_6 == "sang-ayon"){
                $q6["agree"]++;
            }else if($row->q_6 == "hindi_sang-ayon"){
                $q6["disagree"]++;
            }else if($row->q_6 == "walang_kinikilingan"){
                $q6["neutral"]++;
            }else{
                $q6["none"]++;
            }

            if($row->q_7 == "sang-ayon"){
                $q7["agree"]++;
            }else if($row->q_7 == "hindi_sang-ayon"){
                $q7["disagree"]++;
            }else if($row->q_7 == "walang_kinikilingan"){
                $q7["neutral"]++;
            }else{
                $q7["none"]++;
            }

            if($row->q_8 == "sang-ayon"){
                $q8["agree"]++;
            }else if($row->q_8 == "hindi_sang-ayon"){
                $q8["disagree"]++;
            }else if($row->q_8 == "walang_kinikilingan"){
                $q8["neutral"]++;
            }else{
                $q8["none"]++;
            }

            if($row->q_9 == "sang-ayon"){
                $q9["agree"]++;
            }else if($row->q_9 == "hindi_sang-ayon"){
                $q9["disagree"]++;
            }else if($row->q_9 == "walang_kinikilingan"){
                $q9["neutral"]++;
            }else{
                $q9["none"]++;
            }

            if($row->q_10 == "sang-ayon"){
                $q10["agree"]++;
            }else if($row->q_10 == "hindi_sang-ayon"){
                $q10["disagree"]++;
            }else if($row->q_10 == "walang_kinikilingan"){
                $q10["neutral"]++;
            }else{
                $q10["none"]++;
            }

            if($row->q_11 == "sang-ayon"){
                $q11["agree"]++;
            }else if($row->q_11 == "hindi_sang-ayon"){
                $q11["disagree"]++;
            }else if($row->q_11 == "walang_kinikilingan"){
                $q11["neutral"]++;
            }else{
                $q11["none"]++;
            }

            if($row->q_12 == "sang-ayon"){
                $q12["agree"]++;
            }else if($row->q_12 == "hindi_sang-ayon"){
                $q12["disagree"]++;
            }else if($row->q_12 == "walang_kinikilingan"){
                $q12["neutral"]++;
            }else{
                $q12["none"]++;
            }

            if($row->q_15 == "oo"){
                $q15["yes"]++;
            }else if($row->q_15 == "hindi"){
                $q15["no"]++;
            }else{
                $q15["maybe"]++;
            }
        }

        $q1["yes"] = $q1["yes"];
        $q1["no"] = $q1["no"];
        $q1["maybe"] = $q1["maybe"];
        $q1["na"] = "Not Applicable";
        
        $q2["agree"] = $q2["agree"];
        $q2["disagree"] = $q2["disagree"];
        $q2["neutral"] = $q2["neutral"];
        $q2["none"] = $q2["none"];
        
        $q3["agree"] = $q3["agree"];
        $q3["disagree"] = $q3["disagree"];
        $q3["neutral"] = $q3["neutral"];
        $q3["none"] = $q3["none"];
        
        $q4["agree"] = $q4["agree"];
        $q4["disagree"] = $q4["disagree"];
        $q4["neutral"] = $q4["neutral"];
        $q4["none"] = $q4["none"];

        $q5["agree"] = $q5["agree"];
        $q5["disagree"] = $q5["disagree"];
        $q5["neutral"] = $q5["neutral"];
        $q5["none"] = $q5["none"];

        $q6["agree"] = $q6["agree"];
        $q6["disagree"] = $q6["disagree"];
        $q6["neutral"] = $q6["neutral"];
        $q6["none"] = $q6["none"];

        $q7["agree"] = $q7["agree"];
        $q7["disagree"] = $q7["disagree"];
        $q7["neutral"] = $q7["neutral"];
        $q7["none"] = $q7["none"];

        $q8["agree"] = $q8["agree"];
        $q8["disagree"] = $q8["disagree"];
        $q8["neutral"] = $q8["neutral"];
        $q8["none"] = $q8["none"];
        
        $q9["agree"] = $q9["agree"];
        $q9["disagree"] = $q9["disagree"];
        $q9["neutral"] = $q9["neutral"];
        $q9["none"] = $q9["none"];
        
        $q10["agree"] = $q10["agree"];
        $q10["disagree"] = $q10["disagree"];
        $q10["neutral"] = $q10["neutral"];
        $q10["none"] = $q10["none"];
        
        $q11["agree"] = $q11["agree"];
        $q11["disagree"] = $q11["disagree"];
        $q11["neutral"] = $q11["neutral"];
        $q11["none"] = $q11["none"];
        
        $q12["agree"] = $q12["agree"];
        $q12["disagree"] = $q12["disagree"];
        $q12["neutral"] = $q12["neutral"];
        $q12["none"] = $q12["none"];
        
        $q15["yes"] = $q15["yes"];
        $q15["no"] = $q15["no"];
        $q15["maybe"] = $q15["maybe"];
        $q15["na"] = "Not Applicable";
        
        $master_list = array();
        $almost_final = array();
        array_push($almost_final, array(
                "Question" => "1. Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?",
                "Agree/Yes" => number_format($q1["yes"], 0),
                "Disagree/No" => number_format($q1["no"], 0),
                "Neutral" => $q1["na"],
                "None" => number_format($q1["maybe"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "2. Nalaman ko ng mas maaga ang skedyul dahil sa text.",
                "Agree/Yes" => number_format($q2["agree"], 0),
                "Disagree/No" => number_format($q2["disagree"], 0),
                "Neutral" => number_format($q2["neutral"], 0),
                "None" => number_format($q2["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "3. Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon.",
                "Agree/Yes" => number_format($q3["agree"], 0),
                "Disagree/No" => number_format($q3["disagree"], 0),
                "Neutral" => number_format($q3["neutral"], 0),
                "None" => number_format($q3["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "4. Mas malapit ang pinagkuhanan ko ng binhi ngayon.",
                "Agree/Yes" => number_format($q4["agree"], 0),
                "Disagree/No" => number_format($q4["disagree"], 0),
                "Neutral" => number_format($q4["neutral"], 0),
                "None" => number_format($q4["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "5. Mas maikli na ang pila sa pagkuha ng binhi ngayon.",
                "Agree/Yes" => number_format($q5["agree"], 0),
                "Disagree/No" => number_format($q5["disagree"], 0),
                "Neutral" => number_format($q5["neutral"], 0),
                "None" => number_format($q5["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "6. Mas mabilis ang pagkuha ng binhi ngayon.",
                "Agree/Yes" => number_format($q6["agree"], 0),
                "Disagree/No" => number_format($q6["disagree"], 0),
                "Neutral" => number_format($q6["neutral"], 0),
                "None" => number_format($q6["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "7. Mas patas ang pamimigay ng binhi ngayon.",
                "Agree/Yes" => number_format($q7["agree"], 0),
                "Disagree/No" => number_format($q7["disagree"], 0),
                "Neutral" => number_format($q7["neutral"], 0),
                "None" => number_format($q7["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "8. Mas nasunod ang tamang alokasyon ng binhi ngayon.",
                "Agree/Yes" => number_format($q8["agree"], 0),
                "Disagree/No" => number_format($q8["disagree"], 0),
                "Neutral" => number_format($q8["neutral"], 0),
                "None" => number_format($q8["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "9. Mas lumaki ang tsansang makuha ang gusto kong variety ngayon.",
                "Agree/Yes" => number_format($q9["agree"], 0),
                "Disagree/No" => number_format($q9["disagree"], 0),
                "Neutral" => number_format($q9["neutral"], 0),
                "None" => number_format($q9["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "10. Mas panatag ang loob ko sa namimigay ng binhi ngayon.",
                "Agree/Yes" => number_format($q10["agree"], 0),
                "Disagree/No" => number_format($q10["disagree"], 0),
                "Neutral" => number_format($q10["neutral"], 0),
                "None" => number_format($q10["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "11. Mas maayos ang sistema ng pamimigay ng binhi ngayon.",
                "Agree/Yes" => number_format($q11["agree"], 0),
                "Disagree/No" => number_format($q11["disagree"], 0),
                "Neutral" => number_format($q11["neutral"], 0),
                "None" => number_format($q11["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "12. Mas mabuti ang kabuuang karanasan ko sa pamimigay ng binhi ngayon.",
                "Agree/Yes" => number_format($q12["agree"], 0),
                "Disagree/No" => number_format($q12["disagree"], 0),
                "Neutral" => number_format($q12["neutral"], 0),
                "None" => number_format($q12["none"], 0),
            )
        );
        array_push($almost_final, array(
                "Question" => "14. Nakatanggap ka rin ba ng kalendaryo at leaflet ngayon galing sa RCEF?",
                "Agree/Yes" => number_format($q15["yes"], 0),
                "Disagree/No" => number_format($q15["no"], 0),
                "Neutral" => $q15["na"],
                "None" => number_format($q15["maybe"], 0),
            )
        );

        // dd($master_list);
        array_push($master_list, $almost_final);
        // dd($master_list);
            $new_collection = collect(); 
            foreach($master_list as $list_collection_row){
                $new_collection = $new_collection->merge($list_collection_row);
            }

            $excel_data = json_decode(json_encode($new_collection), true);

            return Excel::create("SRVY_".$province_name."_".$municipality_name."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Unscheduled List", function($sheet) use ($excel_data) {          
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:E1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:E1', 'thin');
                });
            })->download('xlsx');
    }

    public function getIncludedProvinces(){
        $includedRegions = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select(DB::raw('LEFT(rcef_id, 4) as prvCode'))
            ->groupBy(DB::raw('LEFT(rcef_id, 4)'))
            ->get();
        // dd($includedRegions);

        $prvC = array();

        foreach($includedRegions as $row){
            array_push($prvC, $row->prvCode);
        }

        $includedRegionsName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province')
            ->whereIn('prv_code', $prvC)
            ->groupBy('province')
            ->get();

        $provinces = array();
        foreach($includedRegionsName as $row){
            array_push($provinces, $row->province);
        }

        return $provinces;
    }

    public function getIncludedMunicipality(Request $request){
        $province = $request->prv;
        
        $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select('municipality')
            ->where('province', $province)
            ->groupBy('municipality')
            ->get();
        
        $muniArray = array();
        foreach($includedMunicipalities as $row){
            array_push($muniArray, $row->municipality);
        }

        return $muniArray;
    }

    public function filterLocation(Request $request){
        $province = $request->prv;
        $municipality = $request->mun;

        if($province == "All"){
            $province = "%";
        }

        if($municipality == "All"){
            $municipality = "%";
        }

        $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->where('province', 'LIKE', $province)
            ->where('municipality', 'LIKE', $municipality)
            ->get();

        $total_respoondents = count($raw_results);
        $q1 = array(
            "id" => "q_1",
            "qs" => "1. Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        $q2 = array(
            "id" => "q_2",
            "qs" => "2. Nalaman ko ng mas maaga ang skedyul dahil sa text.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q3 = array(
            "id" => "q_3",
            "qs" => "3. Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q4 = array(
            "id" => "q_4",
            "qs" => "4. Mas malapit ang pinagkuhanan ko ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q5 = array(
            "id" => "q_5",
            "qs" => "5. Mas maikli na ang pila sa pagkuha ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "id" => "q_6",
            "qs" => "6. Mas mabilis ang pagkuha ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q7 = array(
            "id" => "q_7",
            "qs" => "7. Mas patas ang pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q8 = array(
            "id" => "q_8",
            "qs" => "8. Mas nasunod ang tamang alokasyon ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q9 = array(
            "id" => "q_9",
            "qs" => "9. Mas lumaki ang tsansang makuha ang gusto kong variety ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q10 = array(
            "id" => "q_10",
            "qs" => "10. Mas panatag ang loob ko sa namimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q11 = array(
            "id" => "q_11",
            "qs" => "11. Mas maayos ang sistema ng pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q12 = array(
            "id" => "q_12",
            "qs" => "12. Mas mabuti ang kabuuang karanasan ko sa pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q15 = array(
            "id" => "q_15",
            "qs" => "14. Nakatanggap ka rin ba ng kalendaryo at leaflet ngayon galing sa RCEF?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );

        foreach($raw_results as $row){
            if($row->q_1 == "oo"){
                $q1["yes"]++;
            }else if($row->q_1 == "hindi"){
                $q1["no"]++;
            }else{
                $q1["maybe"]++;
            }

            if($row->q_2 == "sang-ayon"){
                $q2["agree"]++;
            }else if($row->q_2 == "hindi_sang-ayon"){
                $q2["disagree"]++;
            }else if($row->q_2 == "walang_kinikilingan"){
                $q2["neutral"]++;
            }else{
                $q2["none"]++;
            }

            if($row->q_3 == "sang-ayon"){
                $q3["agree"]++;
            }else if($row->q_3 == "hindi_sang-ayon"){
                $q3["disagree"]++;
            }else if($row->q_3 == "walang_kinikilingan"){
                $q3["neutral"]++;
            }else{
                $q3["none"]++;
            }

            if($row->q_4 == "sang-ayon"){
                $q4["agree"]++;
            }else if($row->q_4 == "hindi_sang-ayon"){
                $q4["disagree"]++;
            }else if($row->q_4 == "walang_kinikilingan"){
                $q4["neutral"]++;
            }else{
                $q4["none"]++;
            }

            if($row->q_5 == "sang-ayon"){
                $q5["agree"]++;
            }else if($row->q_5 == "hindi_sang-ayon"){
                $q5["disagree"]++;
            }else if($row->q_5 == "walang_kinikilingan"){
                $q5["neutral"]++;
            }else{
                $q5["none"]++;
            }

            if($row->q_6 == "sang-ayon"){
                $q6["agree"]++;
            }else if($row->q_6 == "hindi_sang-ayon"){
                $q6["disagree"]++;
            }else if($row->q_6 == "walang_kinikilingan"){
                $q6["neutral"]++;
            }else{
                $q6["none"]++;
            }

            if($row->q_7 == "sang-ayon"){
                $q7["agree"]++;
            }else if($row->q_7 == "hindi_sang-ayon"){
                $q7["disagree"]++;
            }else if($row->q_7 == "walang_kinikilingan"){
                $q7["neutral"]++;
            }else{
                $q7["none"]++;
            }

            if($row->q_8 == "sang-ayon"){
                $q8["agree"]++;
            }else if($row->q_8 == "hindi_sang-ayon"){
                $q8["disagree"]++;
            }else if($row->q_8 == "walang_kinikilingan"){
                $q8["neutral"]++;
            }else{
                $q8["none"]++;
            }

            if($row->q_9 == "sang-ayon"){
                $q9["agree"]++;
            }else if($row->q_9 == "hindi_sang-ayon"){
                $q9["disagree"]++;
            }else if($row->q_9 == "walang_kinikilingan"){
                $q9["neutral"]++;
            }else{
                $q9["none"]++;
            }

            if($row->q_10 == "sang-ayon"){
                $q10["agree"]++;
            }else if($row->q_10 == "hindi_sang-ayon"){
                $q10["disagree"]++;
            }else if($row->q_10 == "walang_kinikilingan"){
                $q10["neutral"]++;
            }else{
                $q10["none"]++;
            }

            if($row->q_11 == "sang-ayon"){
                $q11["agree"]++;
            }else if($row->q_11 == "hindi_sang-ayon"){
                $q11["disagree"]++;
            }else if($row->q_11 == "walang_kinikilingan"){
                $q11["neutral"]++;
            }else{
                $q11["none"]++;
            }

            if($row->q_12 == "sang-ayon"){
                $q12["agree"]++;
            }else if($row->q_12 == "hindi_sang-ayon"){
                $q12["disagree"]++;
            }else if($row->q_12 == "walang_kinikilingan"){
                $q12["neutral"]++;
            }else{
                $q12["none"]++;
            }

            if($row->q_15 == "oo"){
                $q15["yes"]++;
            }else if($row->q_15 == "hindi"){
                $q15["no"]++;
            }else{
                $q15["maybe"]++;
            }
        }

        $q1["yes"] = number_format((($q1["yes"] / $total_respoondents) * 100), 2);
        $q1["no"] = number_format((($q1["no"] / $total_respoondents) * 100), 2);
        $q1["maybe"] = number_format((($q1["maybe"] / $total_respoondents) * 100), 2);
        
        $q2["agree"] = number_format((($q2["agree"] / $total_respoondents) * 100), 2);
        $q2["disagree"] = number_format((($q2["disagree"] / $total_respoondents) * 100), 2);
        $q2["neutral"] = number_format((($q2["neutral"] / $total_respoondents) * 100), 2);
        $q2["none"] = number_format((($q2["none"] / $total_respoondents) * 100), 2);
        
        $q3["agree"] = number_format((($q3["agree"] / $total_respoondents) * 100), 2);
        $q3["disagree"] = number_format((($q3["disagree"] / $total_respoondents) * 100), 2);
        $q3["neutral"] = number_format((($q3["neutral"] / $total_respoondents) * 100), 2);
        $q3["none"] = number_format((($q3["none"] / $total_respoondents) * 100), 2);
        
        $q4["agree"] = number_format((($q4["agree"] / $total_respoondents) * 100), 2);
        $q4["disagree"] = number_format((($q4["disagree"] / $total_respoondents) * 100), 2);
        $q4["neutral"] = number_format((($q4["neutral"] / $total_respoondents) * 100), 2);
        $q4["none"] = number_format((($q4["none"] / $total_respoondents) * 100), 2);

        $q5["agree"] = number_format((($q5["agree"] / $total_respoondents) * 100), 2);
        $q5["disagree"] = number_format((($q5["disagree"] / $total_respoondents) * 100), 2);
        $q5["neutral"] = number_format((($q5["neutral"] / $total_respoondents) * 100), 2);
        $q5["none"] = number_format((($q5["none"] / $total_respoondents) * 100), 2);

        $q6["agree"] = number_format((($q6["agree"] / $total_respoondents) * 100), 2);
        $q6["disagree"] = number_format((($q6["disagree"] / $total_respoondents) * 100), 2);
        $q6["neutral"] = number_format((($q6["neutral"] / $total_respoondents) * 100), 2);
        $q6["none"] = number_format((($q6["none"] / $total_respoondents) * 100), 2);

        $q7["agree"] = number_format((($q7["agree"] / $total_respoondents) * 100), 2);
        $q7["disagree"] = number_format((($q7["disagree"] / $total_respoondents) * 100), 2);
        $q7["neutral"] = number_format((($q7["neutral"] / $total_respoondents) * 100), 2);
        $q7["none"] = number_format((($q7["none"] / $total_respoondents) * 100), 2);

        $q8["agree"] = number_format((($q8["agree"] / $total_respoondents) * 100), 2);
        $q8["disagree"] = number_format((($q8["disagree"] / $total_respoondents) * 100), 2);
        $q8["neutral"] = number_format((($q8["neutral"] / $total_respoondents) * 100), 2);
        $q8["none"] = number_format((($q8["none"] / $total_respoondents) * 100), 2);
        
        $q9["agree"] = number_format((($q9["agree"] / $total_respoondents) * 100), 2);
        $q9["disagree"] = number_format((($q9["disagree"] / $total_respoondents) * 100), 2);
        $q9["neutral"] = number_format((($q9["neutral"] / $total_respoondents) * 100), 2);
        $q9["none"] = number_format((($q9["none"] / $total_respoondents) * 100), 2);
        
        $q10["agree"] = number_format((($q10["agree"] / $total_respoondents) * 100), 2);
        $q10["disagree"] = number_format((($q10["disagree"] / $total_respoondents) * 100), 2);
        $q10["neutral"] = number_format((($q10["neutral"] / $total_respoondents) * 100), 2);
        $q10["none"] = number_format((($q10["none"] / $total_respoondents) * 100), 2);
        
        $q11["agree"] = number_format((($q11["agree"] / $total_respoondents) * 100), 2);
        $q11["disagree"] = number_format((($q11["disagree"] / $total_respoondents) * 100), 2);
        $q11["neutral"] = number_format((($q11["neutral"] / $total_respoondents) * 100), 2);
        $q11["none"] = number_format((($q11["none"] / $total_respoondents) * 100), 2);
        
        $q12["agree"] = number_format((($q12["agree"] / $total_respoondents) * 100), 2);
        $q12["disagree"] = number_format((($q12["disagree"] / $total_respoondents) * 100), 2);
        $q12["neutral"] = number_format((($q12["neutral"] / $total_respoondents) * 100), 2);
        $q12["none"] = number_format((($q12["none"] / $total_respoondents) * 100), 2);
        
        $q15["yes"] = number_format((($q15["yes"] / $total_respoondents) * 100), 2);
        $q15["no"] = number_format((($q15["no"] / $total_respoondents) * 100), 2);
        $q15["maybe"] = number_format((($q15["maybe"] / $total_respoondents) * 100), 2);
        

        $perc_questions = array(
            "q1" => $q1,
            "q2" => $q2,
            "q3" => $q3,
            "q4" => $q4,
            "q5" => $q5,
            "q6" => $q6,
            "q7" => $q7,
            "q8" => $q8,
            "q9" => $q9,
            "q10" => $q10,
            "q11" => $q11,
            "q12" => $q12,
            "q15" => $q15,
            "total" => $total_respoondents,
        );

        return $perc_questions;
    }
}
