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

        $rawConv = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
            ->select(DB::raw('LEFT(rcef_id, 4) as prvs'))
            ->orderBy('rcef_id', 'ASC')
            ->get();

        $prvArr = array();
        $prvArr2 = array();
        foreach($rawbep as $row){
            array_push($prvArr, $row->prvs);
        }
        foreach($rawConv as $row){
            array_push($prvArr2, $row->prvs);
        }
        $totalbep = count($prvArr);
        $totalconv = count($prvArr2);

        // // (un)comment this block for speed
        // $rawGender = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
        //     ->join($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'ebinhi_response.rcef_id', '=', 'sed_verified.rcef_id')
        //     ->select(DB::raw('count(sed_verified.sed_id) as count, sed_verified.ver_sex'))
        //     ->groupBy(DB::raw('LEFT(sed_verified.ver_sex, 1)'))
        //     ->orderBy(DB::raw('LEFT(sed_verified.ver_sex, 1)'))
        //     ->get();

        // $maleCount = $rawGender[1]->count;
        // $femaleCount = $rawGender[0]->count;

        $malePerc = 0;
        $femalePerc = 0;
        // $malePerc = number_format((($maleCount / ($maleCount + $femaleCount)) * 100), 2);
        // $femalePerc = number_format((($femaleCount / ($maleCount + $femaleCount)) * 100), 2);


        $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->get();
        
        
        $raw_results2 = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
            ->get();



        $total_respoondents = count($raw_results);
        $total_respoondents_conv = count($raw_results2);
        //Province
        // $includedProvince = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
        //     ->select(DB::raw('LEFT(claim_code, 4) as prvCode'))
        //     ->groupBy(DB::raw('LEFT(claim_code, 4)'))
        //     ->get();
        // $prvC = array();

        // foreach($includedProvince as $row){
        //     array_push($prvC, $row->prvCode);
        // }
        // $includedProvinceName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        //     ->select('prv_code', 'province')
        //     ->whereIn('prv_code', $prvC)
        //     ->groupBy('province')
        //     ->get();
            
        // $provinces = array();
        // foreach($includedProvinceName as $row){
        //     // array_push($provinces, $row->province);
        //     $provinces[] = [
        //         // 'prv' => $row->prv,
        //         'prvCode' => $row->prv_code,
        //         'province' => $row->province,
        //     ];
        // }
            // dd($provinces);
        //BEP
        $survey_questions = [];
        $survey_questions = DB::table('rcef_ionic_db.survey_questions')
            ->select('survey_questions.body as question',
                     'survey_questions.options_en', 
                     'survey_questions.q_id', 
                     'survey_questions.type')
            ->where('mode', '=', 'bep')
            ->where('type', '!=', 'input')
            ->get();
            
        $s_questions = [];
        $s_questions_count = 0;
        foreach ($survey_questions as $question) {
            preg_match('/(\d+)/', $question->q_id, $matches);
            $id_number = isset($matches[1]) ? $matches[1] : null;
            if (isset($question->options_en)) {
                $options = json_decode($question->options_en, true); 

            } else {
                $options = [];
            }

            $total = 0;
            foreach ($options as $index => $item) {
                $code = $item['code'];
                $column = $question->q_id;
                $counts = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                        ->where($column, $code)
                        ->count();
                $options[$index]["count"] = $counts;
                $total += $counts;
            }
            $s_questions[] = [
                'id' => $id_number,
                'q_id' => $question->q_id,
                'question' => $question->question,
                'options' => $options,
                'type' => $question->type,
                'total_response' => $total,
            ];
                    
        }

        //CONV
        $survey_questions_con = [];
        $survey_questions_con = DB::table('rcef_ionic_db.survey_questions')
            ->select('survey_questions.body as question',
                     'survey_questions.options_en', 
                     'survey_questions.q_id', 
                     'survey_questions.type')
            ->where('mode', '=', 'con')
            ->where('type', '!=', 'input')
            ->get();
        $questions_con = [];
        $questions_con_count = 0;
        foreach ($survey_questions_con as $question_con) {
            preg_match('/(\d+)/', $question_con->q_id, $matches);
            $id_number = isset($matches[1]) ? $matches[1] : null;
            if (isset($question_con->options_en)) {
                $options_con = json_decode($question_con->options_en, true); 

            } else {
                $options_con = []; 
            }

            $total = 0;
            foreach ($options_con as $index => $item) {
                $code_con = $item['code'];
                $column_con = $question_con->q_id;
                $counts_con = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
                        ->where($column_con, $code_con)
                        ->count();
                $options_con[$index]["count"] = $counts_con;
                $total += $counts_con;
            }
            $questions_con[] = [
                'id' => $id_number,
                'q_id' => $question_con->q_id,
                'question' => $question_con->question,
                'options' => $options_con,
                'type' => $question_con->type,
                'total_response' => $total,
            ];
        }
            
        $q1_conv = array(
            "id" => "q_1c",
            "qs" => "1. Maayos ba ang proseso ng pagtala?",
            "type" => "spec",
            "yes_2" => 0,
            "yes_1" => 0,
            "neutral" => 0,
            "no_1" => 0,
            "no_2" => 0,
            "none" => 0,
        );
        $q2_conv = array(
            "id" => "q_2c",
            "qs" => "2. Maayos ba at madaling intindihin ang technical briefing?",
            "type" => "spec",
            "yes_2" => 0,
            "yes_1" => 0,
            "neutral" => 0,
            "no_1" => 0,
            "no_2" => 0,
            "none" => 0,
        );
        $q3_conv = array(
            "id" => "q_3c",
            "qs" => "3. Maayos at mabilis ba ang pagkuha ng inyong binhi?",
            "type" => "spec",
            "yes_2" => 0,
            "yes_1" => 0,
            "neutral" => 0,
            "no_1" => 0,
            "no_2" => 0,
            "none" => 0,
        );

        $q01 = array(
            "id" => "q_01",
            "qs" => "1. Ito ba ang unang beses mong nakatanggap ng binhi mula sa Binhi e-Padala?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        $q02 = array(
            "id" => "q_02",
            "qs" => "2. Ito ba ang unang beses mong makasali sa Pre-Registration ng Binhi e-Padala?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        $q1 = array(
            "id" => "q_1",
            "qs" => "3. Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        // $q2 = array(
        //     "id" => "q_2",
        //     "qs" => "2. Nalaman ko ng mas maaga ang skedyul dahil sa text.",
        //     "type" => "spec",
        //     "agree" => 0,
        //     "disagree" => 0,
        //     "neutral" => 0,
        //     "none" => 0,
        // );
        $q3 = array(
            "id" => "q_3",
            "qs" => "4. Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon dahil sa Pre-registration.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q4 = array(
            "id" => "q_4",
            "qs" => "5. Mas malapit ang pinagkuhanan ko ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q5 = array(
            "id" => "q_5",
            "qs" => "6. Mas maikli na ang pila sa pagkuha ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q6 = array(
            "id" => "q_6",
            "qs" => "7. Mas mabilis ang pagkuha ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q7 = array(
            "id" => "q_7",
            "qs" => "8. Mas patas ang pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q8 = array(
            "id" => "q_8",
            "qs" => "9. Mas lumaki ang tsansang makuha ang gusto kong variety ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q9 = array(
            "id" => "q_9",
            "qs" => "10. Mas panatag ang loob ko sa namimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q10 = array(
            "id" => "q_10",
            "qs" => "11. Mas maayos ang serbisyo ng PhilRice Staff ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q11 = array(
            "id" => "q_11",
            "qs" => "12. Mas maayos ang sistema ng pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q12 = array(
            "id" => "q_12",
            "qs" => "13. Mas mabuti ang kabuuang karanasan ko sa pamimigay ng binhi ngayon.",
            "type" => "spec",
            "agree" => 0,
            "disagree" => 0,
            "neutral" => 0,
            "none" => 0,
        );
        $q15 = array(
            "id" => "q_15",
            "qs" => "15. Nakatanggap ka rin ba ng kalendaryo at leaflet ngayon galing sa RCEF?",
            "type" => "bin",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );

        //loop conv
        foreach($raw_results2 as $row){
            if($row->q_1 == "lubos_sang-ayon"){
                $q1_conv["yes_2"]++;
            }else if($row->q_1 == "sang-ayon"){
                $q1_conv["yes_1"]++;
            }else if($row->q_1 == "walang_kinikilingan"){
                $q1_conv["neutral"]++;
            }else if($row->q_1 == "hindi_sang-ayon"){
                $q1_conv["no_1"]++;
            }else if($row->q_1 == "lubos_hindi_sang-ayon"){
                $q1_conv["no_2"]++;
            }else{
                $q1_conv["none"]++;
            }

            if($row->q_2 == "lubos_sang-ayon"){
                $q2_conv["yes_2"]++;
            }else if($row->q_2 == "sang-ayon"){
                $q2_conv["yes_1"]++;
            }else if($row->q_2 == "walang_kinikilingan"){
                $q2_conv["neutral"]++;
            }else if($row->q_2 == "hindi_sang-ayon"){
                $q2_conv["no_1"]++;
            }else if($row->q_2 == "lubos_hindi_sang-ayon"){
                $q2_conv["no_2"]++;
            }else{
                $q2_conv["none"]++;
            }

            if($row->q_3 == "lubos_sang-ayon"){
                $q3_conv["yes_2"]++;
            }else if($row->q_3 == "sang-ayon"){
                $q3_conv["yes_1"]++;
            }else if($row->q_3 == "walang_kinikilingan"){
                $q3_conv["neutral"]++;
            }else if($row->q_3 == "hindi_sang-ayon"){
                $q3_conv["no_1"]++;
            }else if($row->q_3 == "lubos_hindi_sang-ayon"){
                $q3_conv["no_2"]++;
            }else{
                $q3_conv["none"]++;
            }
        }

        //loop bep
        foreach($raw_results as $row){
            if($row->q_01 == "oo"){
                $q01["yes"]++;
            }else if($row->q_01 == "hindi"){
                $q01["no"]++;
            }else{
                $q01["maybe"]++;
            }
            if($row->q_02 == "oo"){
                $q02["yes"]++;
            }else if($row->q_02 == "hindi"){
                $q02["no"]++;
            }else{
                $q02["maybe"]++;
            }

            if($row->q_1 == "oo"){
                $q1["yes"]++;
            }else if($row->q_1 == "hindi"){
                $q1["no"]++;
            }else{
                $q1["maybe"]++;
            }
            
            // if($row->q_2 == "sang-ayon"){
            //     $q2["agree"]++;
            // }else if($row->q_2 == "hindi_sang-ayon"){
            //     $q2["disagree"]++;
            // }else if($row->q_2 == "walang_kinikilingan"){
            //     $q2["neutral"]++;
            // }else{
            //     $q2["none"]++;
            // }

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
        
        //conv

        if($total_respoondents_conv > 0){
            $q1_conv["yes_2"] = number_format((($q1_conv["yes_2"] / $total_respoondents_conv) * 100), 2);
            $q1_conv["yes_1"] = number_format((($q1_conv["yes_1"] / $total_respoondents_conv) * 100), 2);
            $q1_conv["neutral"] = number_format((($q1_conv["neutral"] / $total_respoondents_conv) * 100), 2);
            $q1_conv["no_1"] = number_format((($q1_conv["no_1"] / $total_respoondents_conv) * 100), 2);
            $q1_conv["no_2"] = number_format((($q1_conv["no_2"] / $total_respoondents_conv) * 100), 2);
            $q1_conv["none"] = number_format((($q1_conv["none"] / $total_respoondents_conv) * 100), 2);
            
            $q2_conv["yes_2"] = number_format((($q2_conv["yes_2"] / $total_respoondents_conv) * 100), 2);
            $q2_conv["yes_1"] = number_format((($q2_conv["yes_1"] / $total_respoondents_conv) * 100), 2);
            $q2_conv["neutral"] = number_format((($q2_conv["neutral"] / $total_respoondents_conv) * 100), 2);
            $q2_conv["no_1"] = number_format((($q2_conv["no_1"] / $total_respoondents_conv) * 100), 2);
            $q2_conv["no_2"] = number_format((($q2_conv["no_2"] / $total_respoondents_conv) * 100), 2);
            $q2_conv["none"] = number_format((($q2_conv["none"] / $total_respoondents_conv) * 100), 2);
            
            $q3_conv["yes_2"] = number_format((($q3_conv["yes_2"] / $total_respoondents_conv) * 100), 2);
            $q3_conv["yes_1"] = number_format((($q3_conv["yes_1"] / $total_respoondents_conv) * 100), 2);
            $q3_conv["neutral"] = number_format((($q3_conv["neutral"] / $total_respoondents_conv) * 100), 2);
            $q3_conv["no_1"] = number_format((($q3_conv["no_1"] / $total_respoondents_conv) * 100), 2);
            $q3_conv["no_2"] = number_format((($q3_conv["no_2"] / $total_respoondents_conv) * 100), 2);
            $q3_conv["none"] = number_format((($q3_conv["none"] / $total_respoondents_conv) * 100), 2);
        }

        //bep

        if($total_respoondents > 0){
            $q01["yes"] = number_format((($q01["yes"] / $total_respoondents) * 100), 2);
            $q01["no"] = number_format((($q01["no"] / $total_respoondents) * 100), 2);
            $q01["maybe"] = number_format((($q01["maybe"] / $total_respoondents) * 100), 2);
            
            $q02["yes"] = number_format((($q02["yes"] / $total_respoondents) * 100), 2);
            $q02["no"] = number_format((($q02["no"] / $total_respoondents) * 100), 2);
            $q02["maybe"] = number_format((($q02["maybe"] / $total_respoondents) * 100), 2);
            
            $q1["yes"] = number_format((($q1["yes"] / $total_respoondents) * 100), 2);
            $q1["no"] = number_format((($q1["no"] / $total_respoondents) * 100), 2);
            $q1["maybe"] = number_format((($q1["maybe"] / $total_respoondents) * 100), 2);
            
            // $q2["agree"] = number_format((($q2["agree"] / $total_respoondents) * 100), 2);
            // $q2["disagree"] = number_format((($q2["disagree"] / $total_respoondents) * 100), 2);
            // $q2["neutral"] = number_format((($q2["neutral"] / $total_respoondents) * 100), 2);
            // $q2["none"] = number_format((($q2["none"] / $total_respoondents) * 100), 2);
            
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
            "q01" => $q01,
            "q02" => $q02,
            "q1" => $q1,
            // "q2" => $q2,
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

        $perc_questions_conv = array(
            "q1" => $q1_conv,
            "q2" => $q2_conv,
            "q3" => $q3_conv
        );

        // dd($perc_questions);

        return view('cssDashboard.index2', compact(
            'totalbep',
            'totalconv',
            'femalePerc',
            'malePerc',
            'perc_questions',
            'total_respoondents_conv',
            'perc_questions_conv',
            'survey_questions',
            's_questions',
            'options',
            'survey_questions_con',
            'questions_con',
            'options_con'
        ));
    }

    public function getBdays(){
        $rawbep = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->select(DB::raw('LEFT(rcef_id, 4) as prvs, rcef_id'))
            ->orderBy('rcef_id', 'ASC')
            ->get();

        // $prvArr = array();
        // foreach($rawbep as $row){
        //     array_push($prvArr, $row->prvs);
        // }
        // dd($rawbep);
        $bdays = array();
        foreach($rawbep as $row){
            $rawBdays = DB::table($GLOBALS['season_prefix'].'prv_'.$row->prvs.'.farmer_information_final')
                ->select('birthdate')
                ->where('rcef_id', $row->rcef_id)
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
                $leastAgeBr++;
            }else if($row <= 59){
                $middleAgeBr++;
            }else if($row >= 60){
                $lastAgeBr++;
            }
        }

        if($leastAgeBr > 0 || $middleAgeBr > 0 || $lastAgeBr > 0){
            $leastperc = number_format(($leastAgeBr / count($ages) * 100), 2);
            $middleperc = number_format(($middleAgeBr / count($ages) * 100), 2);
            $lastperc = number_format(($lastAgeBr / count($ages) * 100), 2);
        }else{
            $leastperc = 0;
            $middleperc = 0;
            $lastperc = 0;
        }
        return array(
            "least" => $leastperc,
            "middle" => $middleperc,
            "last" => $lastperc
        );
    }

    public function getGender($default){
        $mode = "ebinhi_response";
        if($default == "bep")
            $mode = "ebinhi_response";
        if($default == "con")
            $mode = "conv_response";

        $rawGender = DB::table($GLOBALS['season_prefix'].'rcep_css.'.$mode)
            ->join($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'ebinhi_response.rcef_id', '=', 'sed_verified.rcef_id')
            ->select(DB::raw('count(sed_verified.sed_id) as count, sed_verified.ver_sex'))
            ->groupBy(DB::raw('LEFT(sed_verified.ver_sex, 1)'))
            ->orderBy(DB::raw('LEFT(sed_verified.ver_sex, 1)'))
            ->get();

        if(count($rawGender) > 0){
            $maleCount = $rawGender[1]->count;
            $femaleCount = $rawGender[0]->count;

            // $maleCount = 63;
            // $femaleCount = 32;
            $malePerc = number_format((($maleCount / ($maleCount + $femaleCount)) * 100), 2);
            $femalePerc = number_format((($femaleCount / ($maleCount + $femaleCount)) * 100), 2);
        }else{
            $malePerc = 0;
            $femalePerc = 0;
        }

        return array(
            "male" => $malePerc,
            "female" => $femalePerc
        );
    }

    public function getBepQuestionResults(){
        $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
            ->get();

        $total_respoondents = count($raw_results);
        $q01 = array(
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
        $q02 = array(
            "yes" => 0,
            "no" => 0,
            "maybe" => 0,
        );
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
            if($row->q_01 == "oo"){
                $q01["yes"]++;
            }else if($row->q_01 == "hindi"){
                $q01["no"]++;
            }else{
                $q01["maybe"]++;
            }
            if($row->q_02 == "oo"){
                $q02["yes"]++;
            }else if($row->q_02 == "hindi"){
                $q02["no"]++;
            }else{
                $q02["maybe"]++;
            }

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

        $q01["yes"] = number_format((($q01["yes"] / $total_respoondents) * 100), 2);
        $q01["no"] = number_format((($q01["no"] / $total_respoondents) * 100), 2);
        $q01["maybe"] = number_format((($q01["maybe"] / $total_respoondents) * 100), 2);

        $q02["yes"] = number_format((($q02["yes"] / $total_respoondents) * 100), 2);
        $q02["no"] = number_format((($q02["no"] / $total_respoondents) * 100), 2);
        $q02["maybe"] = number_format((($q02["maybe"] / $total_respoondents) * 100), 2);

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
            "q01" => $q01,
            "q02" => $q02,
            "q1" => $q1,
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
        if($question == "q_1" || $question == "q_15" || $question == "q_01" || $question == "q_02"){
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

    public function getIndivQuestionConv(Request $request){
        $question = substr($request->question, 0, 3);
        $province = $request->prv;
        $municipality = $request->mun;

        if($province == "All"){
            $province = "%";
        }

        if($municipality == "All"){
            $municipality = "%";
        }

        $rawQs = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
            ->select(DB::raw($question.' as ans'), DB::raw('count('.$question.') as raw'))
            ->where('province', 'like', $province)
            ->where('municipality', 'like', $municipality)
            ->groupBy($question)
            ->orderBy($question)
            ->get();
        
        
        $qsType = "";
        $qs_yn = array(
            "type" => "yn",
            "yes" => 0,
            "no" => 0,
            "maybe" => 0
        );
        $qs_sp = array(
            "type" => "sp",
            "str_agree" => 0,
            "agree" => 0, 
            "neutral" => 0,
            "disagree" => 0,
            "str_agree" => 0,
            "none" => 0,
        );

        //yn
        $y = 0;
        $n = 0;
        $m = 0;
        //end yn
        
        //sp
        $str_agree = 0;
        $agree = 0;
        $neutral = 0;
        $disagree = 0;
        $str_disagree = 0;
        $none = 0;
        //end sp

        $qs_struct = array();
        // if($question != "q_1" || $question != "q_2" || $question != "q_13"){
        //     $qsType = "yn";
        //     foreach($rawQs as $row){
        //         if($row->ans == "hindi"){
        //             $n = $row->raw;
        //         }else if($row->ans == "oo"){
        //             $y = $row->raw;
        //         }else{
        //             $m = $row->raw;
        //         }
        //     }
        // }else{
        $qsType = "sp";
        foreach($rawQs as $row){
            if($row->ans == "lubos_sang-ayon"){
                $str_agree = $row->raw;
            }else if($row->ans == "sang-ayon"){
                $agree = $row->raw;
            }else if($row->ans == "walang_kinikilingan"){
                $neutral = $row->raw;
            }else if($row->ans == "hindi_sang-ayon"){
                $disagree = $row->raw;
            }else if($row->ans == "lubos_hindi_sang-ayon"){
                $str_disagree = $row->raw;
            }else{
                $none = $row->raw;
            }
        }
        // }

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
                "str_agree" => (int)$str_agree,
                "agree" => (int)$agree, 
                "neutral" => (int)$neutral,
                "disagree" => (int)$disagree,
                "str_disagree" => (int)$str_disagree,
                "none" => (int)$none
            );
        }

        return $qs_struct;
    }

    // public function exportStats(Request $request){
    //     $province = $request->prv;
    //     $municipality = $request->mun;
    //     $province_name = $request->prv;
    //     $municipality_name = $request->mun;

    //     if($province == "All"){
    //         $province = "%";
    //         $province_name = "ALL";
    //     }
    //     if($municipality == "All"){
    //         $municipality = "%";
    //         $municipality_name = "ALL";
    //     }

    //     $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
    //         ->where('province', 'like', $province)
    //         ->where('municipality', 'like', $municipality)
    //         ->get();

    //     $total_respoondents = count($raw_results);

    //     $q01 = array(
    //         "yes" => 0,
    //         "no" => 0,
    //         "maybe" => 0,
    //         "na" => "Not Applicable",
    //     );
    //     $q02 = array(
    //         "yes" => 0,
    //         "no" => 0,
    //         "maybe" => 0,
    //         "na" => "Not Applicable",
    //     );
    //     $q1 = array(
    //         "yes" => 0,
    //         "no" => 0,
    //         "maybe" => 0,
    //         "na" => "Not Applicable",
    //     );
    //     $q2 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q3 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q4 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q5 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q6 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q6 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q7 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q8 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q9 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q10 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q11 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q12 = array(
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q15 = array(
    //         "yes" => 0,
    //         "no" => 0,
    //         "maybe" => 0,
    //         "na" => "Not Applicable",
    //     );

    //     foreach($raw_results as $row){
    //         if($row->q_01 == "oo"){
    //             $q01["yes"]++;
    //         }else if($row->q_01 == "hindi"){
    //             $q01["no"]++;
    //         }else{
    //             $q01["maybe"]++;
    //         }
    //         if($row->q_02 == "oo"){
    //             $q02["yes"]++;
    //         }else if($row->q_02 == "hindi"){
    //             $q02["no"]++;
    //         }else{
    //             $q02["maybe"]++;
    //         }
    //         if($row->q_1 == "oo"){
    //             $q1["yes"]++;
    //         }else if($row->q_1 == "hindi"){
    //             $q1["no"]++;
    //         }else{
    //             $q1["maybe"]++;
    //         }

    //         if($row->q_2 == "sang-ayon"){
    //             $q2["agree"]++;
    //         }else if($row->q_2 == "hindi_sang-ayon"){
    //             $q2["disagree"]++;
    //         }else if($row->q_2 == "walang_kinikilingan"){
    //             $q2["neutral"]++;
    //         }else{
    //             $q2["none"]++;
    //         }

    //         if($row->q_3 == "sang-ayon"){
    //             $q3["agree"]++;
    //         }else if($row->q_3 == "hindi_sang-ayon"){
    //             $q3["disagree"]++;
    //         }else if($row->q_3 == "walang_kinikilingan"){
    //             $q3["neutral"]++;
    //         }else{
    //             $q3["none"]++;
    //         }

    //         if($row->q_4 == "sang-ayon"){
    //             $q4["agree"]++;
    //         }else if($row->q_4 == "hindi_sang-ayon"){
    //             $q4["disagree"]++;
    //         }else if($row->q_4 == "walang_kinikilingan"){
    //             $q4["neutral"]++;
    //         }else{
    //             $q4["none"]++;
    //         }

    //         if($row->q_5 == "sang-ayon"){
    //             $q5["agree"]++;
    //         }else if($row->q_5 == "hindi_sang-ayon"){
    //             $q5["disagree"]++;
    //         }else if($row->q_5 == "walang_kinikilingan"){
    //             $q5["neutral"]++;
    //         }else{
    //             $q5["none"]++;
    //         }

    //         if($row->q_6 == "sang-ayon"){
    //             $q6["agree"]++;
    //         }else if($row->q_6 == "hindi_sang-ayon"){
    //             $q6["disagree"]++;
    //         }else if($row->q_6 == "walang_kinikilingan"){
    //             $q6["neutral"]++;
    //         }else{
    //             $q6["none"]++;
    //         }

    //         if($row->q_7 == "sang-ayon"){
    //             $q7["agree"]++;
    //         }else if($row->q_7 == "hindi_sang-ayon"){
    //             $q7["disagree"]++;
    //         }else if($row->q_7 == "walang_kinikilingan"){
    //             $q7["neutral"]++;
    //         }else{
    //             $q7["none"]++;
    //         }

    //         if($row->q_8 == "sang-ayon"){
    //             $q8["agree"]++;
    //         }else if($row->q_8 == "hindi_sang-ayon"){
    //             $q8["disagree"]++;
    //         }else if($row->q_8 == "walang_kinikilingan"){
    //             $q8["neutral"]++;
    //         }else{
    //             $q8["none"]++;
    //         }

    //         if($row->q_9 == "sang-ayon"){
    //             $q9["agree"]++;
    //         }else if($row->q_9 == "hindi_sang-ayon"){
    //             $q9["disagree"]++;
    //         }else if($row->q_9 == "walang_kinikilingan"){
    //             $q9["neutral"]++;
    //         }else{
    //             $q9["none"]++;
    //         }

    //         if($row->q_10 == "sang-ayon"){
    //             $q10["agree"]++;
    //         }else if($row->q_10 == "hindi_sang-ayon"){
    //             $q10["disagree"]++;
    //         }else if($row->q_10 == "walang_kinikilingan"){
    //             $q10["neutral"]++;
    //         }else{
    //             $q10["none"]++;
    //         }

    //         if($row->q_11 == "sang-ayon"){
    //             $q11["agree"]++;
    //         }else if($row->q_11 == "hindi_sang-ayon"){
    //             $q11["disagree"]++;
    //         }else if($row->q_11 == "walang_kinikilingan"){
    //             $q11["neutral"]++;
    //         }else{
    //             $q11["none"]++;
    //         }

    //         if($row->q_12 == "sang-ayon"){
    //             $q12["agree"]++;
    //         }else if($row->q_12 == "hindi_sang-ayon"){
    //             $q12["disagree"]++;
    //         }else if($row->q_12 == "walang_kinikilingan"){
    //             $q12["neutral"]++;
    //         }else{
    //             $q12["none"]++;
    //         }

    //         if($row->q_15 == "oo"){
    //             $q15["yes"]++;
    //         }else if($row->q_15 == "hindi"){
    //             $q15["no"]++;
    //         }else{
    //             $q15["maybe"]++;
    //         }
    //     }

    //     $q01["yes"] = $q01["yes"];
    //     $q01["no"] = $q01["no"];
    //     $q01["maybe"] = $q01["maybe"];
    //     $q01["na"] = "Not Applicable";
        
    //     $q02["yes"] = $q02["yes"];
    //     $q02["no"] = $q02["no"];
    //     $q02["maybe"] = $q02["maybe"];
    //     $q02["na"] = "Not Applicable";
        
    //     $q1["yes"] = $q1["yes"];
    //     $q1["no"] = $q1["no"];
    //     $q1["maybe"] = $q1["maybe"];
    //     $q1["na"] = "Not Applicable";
        
    //     $q2["agree"] = $q2["agree"];
    //     $q2["disagree"] = $q2["disagree"];
    //     $q2["neutral"] = $q2["neutral"];
    //     $q2["none"] = $q2["none"];
        
    //     $q3["agree"] = $q3["agree"];
    //     $q3["disagree"] = $q3["disagree"];
    //     $q3["neutral"] = $q3["neutral"];
    //     $q3["none"] = $q3["none"];
        
    //     $q4["agree"] = $q4["agree"];
    //     $q4["disagree"] = $q4["disagree"];
    //     $q4["neutral"] = $q4["neutral"];
    //     $q4["none"] = $q4["none"];

    //     $q5["agree"] = $q5["agree"];
    //     $q5["disagree"] = $q5["disagree"];
    //     $q5["neutral"] = $q5["neutral"];
    //     $q5["none"] = $q5["none"];

    //     $q6["agree"] = $q6["agree"];
    //     $q6["disagree"] = $q6["disagree"];
    //     $q6["neutral"] = $q6["neutral"];
    //     $q6["none"] = $q6["none"];

    //     $q7["agree"] = $q7["agree"];
    //     $q7["disagree"] = $q7["disagree"];
    //     $q7["neutral"] = $q7["neutral"];
    //     $q7["none"] = $q7["none"];

    //     $q8["agree"] = $q8["agree"];
    //     $q8["disagree"] = $q8["disagree"];
    //     $q8["neutral"] = $q8["neutral"];
    //     $q8["none"] = $q8["none"];
        
    //     $q9["agree"] = $q9["agree"];
    //     $q9["disagree"] = $q9["disagree"];
    //     $q9["neutral"] = $q9["neutral"];
    //     $q9["none"] = $q9["none"];
        
    //     $q10["agree"] = $q10["agree"];
    //     $q10["disagree"] = $q10["disagree"];
    //     $q10["neutral"] = $q10["neutral"];
    //     $q10["none"] = $q10["none"];
        
    //     $q11["agree"] = $q11["agree"];
    //     $q11["disagree"] = $q11["disagree"];
    //     $q11["neutral"] = $q11["neutral"];
    //     $q11["none"] = $q11["none"];
        
    //     $q12["agree"] = $q12["agree"];
    //     $q12["disagree"] = $q12["disagree"];
    //     $q12["neutral"] = $q12["neutral"];
    //     $q12["none"] = $q12["none"];
        
    //     $q15["yes"] = $q15["yes"];
    //     $q15["no"] = $q15["no"];
    //     $q15["maybe"] = $q15["maybe"];
    //     $q15["na"] = "Not Applicable";
        
    //     $master_list = array();
    //     $almost_final = array();
    //     array_push($almost_final, array(
    //             "Question" => "1. Ito ba ang unang beses mong nakatanggap ng binhi mula sa Binhi e-Padala?",
    //             "Agree/Yes" => number_format($q01["yes"], 0),
    //             "Disagree/No" => number_format($q01["no"], 0),
    //             "Neutral" => $q01["na"],
    //             "None" => number_format($q01["maybe"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "2. Ito ba ang unang beses mong makasali sa Pre-Registration ng Binhi e-Padala?",
    //             "Agree/Yes" => number_format($q02["yes"], 0),
    //             "Disagree/No" => number_format($q02["no"], 0),
    //             "Neutral" => $q02["na"],
    //             "None" => number_format($q02["maybe"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "3. Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?",
    //             "Agree/Yes" => number_format($q1["yes"], 0),
    //             "Disagree/No" => number_format($q1["no"], 0),
    //             "Neutral" => $q1["na"],
    //             "None" => number_format($q1["maybe"], 0),
    //         )
    //     );
    //     // array_push($almost_final, array(
    //     //         "Question" => "4. Nalaman ko ng mas maaga ang skedyul dahil sa text.",
    //     //         "Agree/Yes" => number_format($q2["agree"], 0),
    //     //         "Disagree/No" => number_format($q2["disagree"], 0),
    //     //         "Neutral" => number_format($q2["neutral"], 0),
    //     //         "None" => number_format($q2["none"], 0),
    //     //     )
    //     // );
    //     array_push($almost_final, array(
    //             "Question" => "4. Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q3["agree"], 0),
    //             "Disagree/No" => number_format($q3["disagree"], 0),
    //             "Neutral" => number_format($q3["neutral"], 0),
    //             "None" => number_format($q3["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "5. Mas malapit ang pinagkuhanan ko ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q4["agree"], 0),
    //             "Disagree/No" => number_format($q4["disagree"], 0),
    //             "Neutral" => number_format($q4["neutral"], 0),
    //             "None" => number_format($q4["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "6. Mas maikli na ang pila sa pagkuha ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q5["agree"], 0),
    //             "Disagree/No" => number_format($q5["disagree"], 0),
    //             "Neutral" => number_format($q5["neutral"], 0),
    //             "None" => number_format($q5["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "7. Mas mabilis ang pagkuha ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q6["agree"], 0),
    //             "Disagree/No" => number_format($q6["disagree"], 0),
    //             "Neutral" => number_format($q6["neutral"], 0),
    //             "None" => number_format($q6["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "8. Mas patas ang pamimigay ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q7["agree"], 0),
    //             "Disagree/No" => number_format($q7["disagree"], 0),
    //             "Neutral" => number_format($q7["neutral"], 0),
    //             "None" => number_format($q7["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "9. Mas lumaki ang tsansang makuha ang gusto kong variety ngayon.",
    //             "Agree/Yes" => number_format($q8["agree"], 0),
    //             "Disagree/No" => number_format($q8["disagree"], 0),
    //             "Neutral" => number_format($q8["neutral"], 0),
    //             "None" => number_format($q8["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "10. Mas panatag ang loob ko sa namimigay ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q9["agree"], 0),
    //             "Disagree/No" => number_format($q9["disagree"], 0),
    //             "Neutral" => number_format($q9["neutral"], 0),
    //             "None" => number_format($q9["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "11. Mas maayos ang serbisyo ng PhilRice Staff ngayon.",
    //             "Agree/Yes" => number_format($q10["agree"], 0),
    //             "Disagree/No" => number_format($q10["disagree"], 0),
    //             "Neutral" => number_format($q10["neutral"], 0),
    //             "None" => number_format($q10["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "12. Mas maayos ang sistema ng pamimigay ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q11["agree"], 0),
    //             "Disagree/No" => number_format($q11["disagree"], 0),
    //             "Neutral" => number_format($q11["neutral"], 0),
    //             "None" => number_format($q11["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "13. Mas mabuti ang kabuuang karanasan ko sa pamimigay ng binhi ngayon.",
    //             "Agree/Yes" => number_format($q12["agree"], 0),
    //             "Disagree/No" => number_format($q12["disagree"], 0),
    //             "Neutral" => number_format($q12["neutral"], 0),
    //             "None" => number_format($q12["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "15. Nakatanggap ka rin ba ng kalendaryo at leaflet ngayon galing sa RCEF?",
    //             "Agree/Yes" => number_format($q15["yes"], 0),
    //             "Disagree/No" => number_format($q15["no"], 0),
    //             "Neutral" => $q15["na"],
    //             "None" => number_format($q15["maybe"], 0),
    //         )
    //     );

    //     // dd($master_list);
    //     array_push($master_list, $almost_final);
    //     // dd($master_list);
    //         $new_collection = collect(); 
    //         foreach($master_list as $list_collection_row){
    //             $new_collection = $new_collection->merge($list_collection_row);
    //         }

    //         $excel_data = json_decode(json_encode($new_collection), true);

    //         return Excel::create("BEP_".$province_name."_".$municipality_name."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $province_name, $municipality_name) {
    //             $excel->sheet($municipality_name, function($sheet) use ($excel_data) {          
    //                 $sheet->fromArray($excel_data);
    //                 $sheet->freezeFirstRow();
                    
    //                 $sheet->setHeight(1, 30);
    //                 $sheet->cells('A1:E1', function ($cells) {
    //                     $cells->setBackground('#92D050');
    //                     $cells->setAlignment('center');
    //                     $cells->setValignment('center');
    //                 });
    //                 $sheet->setBorder('A1:E1', 'thin');
    //             });
    //         })->download('xlsx');
    // }

    public function exportStats(Request $request){
        $province = $request->prv;
        $municipality = $request->mun;
        $province_name = $request->prv;
        $municipality_name = $request->mun;
        // dd($province);
        if ($province == "All") {
            $province = "%";
            $province_name = "ALL";
        }
        if ($municipality == "All") {
            $municipality = "%";
            $municipality_name = "ALL";
        }

        $str_temp = $province.$municipality."%";

        $survey_questions = DB::table('rcef_ionic_db.survey_questions')
            ->select('survey_questions.body as question', 'survey_questions.options_en', 'survey_questions.q_id', 'survey_questions.type')
            ->where('mode', '=', 'bep')
            ->where('type', '!=', 'input')
            ->get();

        $s_questions = [];
        $headers = ['Question'];
        $data = [];

        foreach ($survey_questions as $question) {
            preg_match('/(\d+)/', $question->q_id, $matches);
            $id_number = isset($matches[1]) ? $matches[1] : null;
            $options = isset($question->options_en) ? json_decode($question->options_en, true) : [];
            $total = 0;

            $option_counts = [];
            foreach ($options as $index => $item) {
                $code = $item['code'];
                $display = $item['display'];
                $column = $question->q_id;
                // $counts = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                //     ->where($column, $code)
                //     ->where('claim_code', 'LIKE', $province.'%')
                //     ->count();
                    
                if($municipality == "%"){
                    $counts = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses as ubr')
                        ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim as tc', 'ubr.claim_code', '=', 'tc.paymaya_code')
                        ->where('ubr.claim_code', 'LIKE', $province.'%')
                        ->where('ubr.'.$column, $code)
                        ->distinct('ubr.claim_code')
                        ->count('ubr.claim_code');
                } else {
                    $counts = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses as ubr')
                        ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim as tc', 'ubr.claim_code', '=', 'tc.paymaya_code')
                        ->where('tc.municipality', $municipality)
                        ->where('ubr.claim_code', 'LIKE', $province.'%')
                        ->where('ubr.'.$column, $code)
                        ->distinct('ubr.claim_code')
                        ->count('ubr.claim_code');
                }
                $options[$index]["count"] = $counts;
                $total += $counts;
                $option_counts[$display] = $counts;
            }

            $s_questions[] = [
                'id' => $id_number,
                'question' => $question->question,
                'options' => $options,
                'type' => $question->type,
                'total_response' => $total,
            ];

            foreach ($options as $option) {
                if (!in_array($option['display'], $headers)) {
                    $headers[] = $option['display'];
                }
            }
            $row = ['question' => $id_number.'. '.$question->question];
            foreach ($headers as $header) {
                if($header != "Question"){
                    $row[$header] = isset($option_counts[$header]) ? $option_counts[$header] : 0;
                }
            }
            $data[] = $row;
        }
        // Prepare the final data array for Excel
        $excel_data = array_merge([$headers], $data);
        // dd($excel_data);
        return Excel::create("BEP_{$province_name}_{$municipality_name}_" . date("Y-m-d g:i A"), function($excel) use ($excel_data, $municipality_name) {
            $excel->sheet($municipality_name, function($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                $sheet->setHeight(1, 30);
                
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
        
                // Set header cell styles
                $sheet->cells('A1:' . $highestColumn . '1', function ($cells) {
                    $cells->setBackground('#92D050');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
        
                $sheet->setBorder('A1:' . $highestColumn . '1', 'thin');
        
                // Center align all data cells after the "Question" column
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->cells('B' . $row . ':' . $highestColumn . $row, function($cells) {
                        $cells->setAlignment('center');
                    });
                }
            });
        })->download('xlsx');
    }

    // public function exportStatsCon(Request $request){
    //     $province = $request->prv;
    //     $municipality = $request->mun;
    //     $province_name = $request->prv;
    //     $municipality_name = $request->mun;

    //     if($province == "All"){
    //         $province = "%";
    //         $province_name = "ALL";
    //     }
    //     if($municipality == "All"){
    //         $municipality = "%";
    //         $municipality_name = "ALL";
    //     }
    //     // dd($province);
    //     // $str_temp = $province.$municipality."%";
    //     $survey_questions_con = [];
    //     $survey_questions_con = DB::table('rcef_ionic_db.survey_questions')
    //         ->select('survey_questions.body as question',
    //                  'survey_questions.options_en', 
    //                  'survey_questions.q_id', 
    //                  'survey_questions.type')
    //         ->where('mode', '=', 'con')
    //         ->where('type', '!=', 'input')
    //         ->get();
    //     $questions_con = [];
    //     $headers = ['Question'];
    //     $data = [];

    //     foreach ($survey_questions_con as $question_con) {
    //         // Extract the number from the q_id field using regex
    //         preg_match('/(\d+)/', $question_con->q_id, $matches);
    //         $id_number = isset($matches[1]) ? $matches[1] : null;
    //         $options_con = isset($question_con->options_en) ? json_decode($question_con->options_en, true) : [];
    //         $total = 0;

    //         $option_counts = [];
    //         foreach ($options_con as $index => $item) {
    //             $code_con = $item['code'];
    //             $display = $item['display'];
    //             $column_con = $question_con->q_id;
    //             $counts_con = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
    //                     ->where($column_con, $code_con)
    //                     ->where('province', 'LIKE', $province)
    //                     ->where('municipality', 'LIKE', $municipality)
    //                     ->count();
    //             $options_con[$index]["count"] = $counts_con;
    //             $total += $counts_con;
    //             $option_counts[$display] = $counts_con;
    //         }
    //         $questions_con[] = [
    //             'id' => $id_number,
    //             'question' => $question_con->question,
    //             'options' => $options_con,
    //             'type' => $question_con->type,
    //             'total_response' => $total,
    //         ];
    //         // dd($questions_con);
    //         foreach ($options_con as $option) {
    //             if (!in_array($option['display'], $headers)) {
    //                 $headers[] = $option['display'];
    //             }
    //         }
    //         $row = ['question' => $id_number.'. '.$question_con->question];
    //         foreach ($headers as $header) {
    //             if($header != "Question"){
    //                 $row[$header] = isset($option_counts[$header]) ? $option_counts[$header] : 0;
    //             }
    //         }
    //         $data[] = $row;
    //     }
    //     $total_row = ['question' => 'Total Respondents'];
    //     foreach ($headers as $header) {
    //         if ($header != "Question") {
    //             // Calculate the total count for each option across all questions
    //                 $total_row[$header] = $total; // Set total count for each option
            
    //         }
    //     }
    //     $data[] = $total_row;
        
    //     $excel_data = array_merge([$headers], $data);
    //         return Excel::create("CON_".$province_name."_".$municipality_name."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $province_name, $municipality_name) {
    //             $excel->sheet($municipality_name, function($sheet) use ($excel_data) {
    //                 $sheet->fromArray($excel_data,null, 'A1', false, false);
    //                 $sheet->freezeFirstRow();
    //                 $sheet->setHeight(1, 30);
    //                 $sheet->cells('A1:' . $sheet->getHighestColumn() . '1', function ($cells) {
    //                     $cells->setBackground('#92D050');
    //                     $cells->setAlignment('center');
    //                     $cells->setValignment('center');
    //                     $cells->setFont(['bold' => true]);
    //                 });
    //                 $sheet->setBorder('A1:' . $sheet->getHighestColumn() . '1', 'thin');
    //                 $sheet->cells('A1:' . $sheet->getHighestColumn() . '9', function ($cells) {
    //                     $cells->setBorder('thin', 'thick', 'thick', 'thick');
    //                 });
    //                 $sheet->cells('A10:' . $sheet->getHighestColumn() . '10', function ($cells) {
    //                     $cells->setBorder('thin', 'thick', 'thick', 'thick');
    //                     $cells->setFont(['bold' => true]);
    //                 });
    //             });
    //         })->download('xlsx');
    // }
    public function exportStatsCon(Request $request) {
        $province = $request->prv;
        $municipality = $request->mun;
        $province_name = $request->prv;
        $municipality_name = $request->mun;
    
        if ($province == "All") {
            $province = "%";
            $province_name = "ALL";
        }
        if ($municipality == "All") {
            $municipality = "%";
            $municipality_name = "ALL";
        }
        $survey_questions_con = [];
        $survey_questions_con = DB::table('rcef_ionic_db.survey_questions')
            ->select('survey_questions.body as question',
                     'survey_questions.options_en', 
                     'survey_questions.q_id', 
                     'survey_questions.type')
            ->where('mode', '=', 'con')
            ->where('type', '!=', 'input')
            ->get();
        $questions_con = [];
        $headers = ['Question'];
        $data = [];
    
        $total_respondents = 0;  // Initialize total respondents count
    
        foreach ($survey_questions_con as $question_con) {
            preg_match('/(\d+)/', $question_con->q_id, $matches);
            $id_number = isset($matches[1]) ? $matches[1] : null;
            $options_con = isset($question_con->options_en) ? json_decode($question_con->options_en, true) : [];
            $total = 0;
    
            $option_counts = [];
            foreach ($options_con as $index => $item) {
                $code_con = $item['code'];
                $display = $item['display'];
                $column_con = $question_con->q_id;
                $counts_con = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
                        ->where($column_con, $code_con)
                        ->where('province', 'LIKE', $province)
                        ->where('municipality', 'LIKE', $municipality)
                        ->count();
                $options_con[$index]["count"] = $counts_con;
                $total += $counts_con;
                $option_counts[$display] = $counts_con;
            }
    
            // Update total respondents count
            $total_respondents += $total;
    
            $questions_con[] = [
                'id' => $id_number,
                'question' => $question_con->question,
                'options' => $options_con,
                'type' => $question_con->type,
                'total_response' => $total,
            ];
    
            foreach ($options_con as $option) {
                if (!in_array($option['display'], $headers)) {
                    $headers[] = $option['display'];
                }
            }
    
            $row = ['question' => $id_number.'. '.$question_con->question];
            foreach ($headers as $header) {
                if($header != "Question"){
                    $row[$header] = isset($option_counts[$header]) ? $option_counts[$header] : 0;
                }
            }
            $data[] = $row;
        }
    
        // Add the "Total Respondents" row
        $total_row = ['question' => 'Total Respondents'];
        foreach ($headers as $header) {
            if ($header != "Question") {
                $total_row[$header] = 0;  // Initialize all cells to 0
            }
        }
        $total_row[end($headers)] = $total;  // Set total respondents in the last column
        $data[] = $total_row;
    
        // Prepare the final data array for Excel
        $excel_data = array_merge([$headers], $data);
    
        return Excel::create("CON_{$province_name}_{$municipality_name}_" . date("Y-m-d g:i A"), function($excel) use ($excel_data, $municipality_name) {
            $excel->sheet($municipality_name, function($sheet) use ($excel_data) {
            $sheet->fromArray($excel_data, null, 'A1', false, false);
            $sheet->freezeFirstRow();
            $sheet->setHeight(1, 30);

            $highestColumn = $sheet->getHighestColumn();
            $highestRow = $sheet->getHighestRow();

            // Set header cell styles
            $sheet->cells('A1:' . $highestColumn . '1', function ($cells) {
                $cells->setBackground('#92D050');
                $cells->setAlignment('center');
                $cells->setValignment('center');
                $cells->setFont(['bold' => true]);
            });

            $sheet->setBorder('A1:' . $highestColumn . '1', 'thin');

            // Center align all data cells after the "Question" column
            for ($row = 2; $row <= $highestRow; $row++) {
                $sheet->cells('B' . $row . ':' . $highestColumn . $row, function($cells) {
                    $cells->setAlignment('center');
                });
            }

            // Set borders for specific rows
            $sheet->cells('A1:' . $highestColumn . '9', function ($cells) {
                $cells->setBorder('thin', 'thick', 'thick', 'thick');
            });
            $sheet->cells('A10:' . $highestColumn . '10', function ($cells) {
                $cells->setBorder('thin', 'thick', 'thick', 'thick');
                $cells->setFont(['bold' => true]);
            });
        });
    })->download('xlsx');

    }
    
    // public function exportStatsCon(Request $request){
    //     $province = $request->prv;
    //     $municipality = $request->mun;
    //     $province_name = $request->prv;
    //     $municipality_name = $request->mun;

    //     if($province == "All"){
    //         $province = "%";
    //         $province_name = "ALL";
    //     }
    //     if($municipality == "All"){
    //         $municipality = "%";
    //         $municipality_name = "ALL";
    //     }

    //     $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
    //         ->where('province', 'like', $province)
    //         ->where('municipality', 'like', $municipality)
    //         ->get();

    //     $total_respoondents = count($raw_results);
    //     $q1_conv = array(
    //         "id" => "q_1c",
    //         "qs" => "1. Maayos ba ang proseso ng pagtala?",
    //         "type" => "spec",
    //         "yes_2" => 0,
    //         "yes_1" => 0,
    //         "neutral" => 0,
    //         "no_1" => 0,
    //         "no_2" => 0,
    //         "none" => 0,
    //         "total" => 0
    //     );
    //     $q2_conv = array(
    //         "id" => "q_2c",
    //         "qs" => "2. Maayos ba at madaling intindihin ang technical briefing?",
    //         "type" => "spec",
    //         "yes_2" => 0,
    //         "yes_1" => 0,
    //         "neutral" => 0,
    //         "no_1" => 0,
    //         "no_2" => 0,
    //         "none" => 0,
    //         "total" => 0
    //     );
    //     $q3_conv = array(
    //         "id" => "q_3c",
    //         "qs" => "3. Maayos at mabilis ba ang pagkuha ng inyong binhi?",
    //         "type" => "spec",
    //         "yes_2" => 0,
    //         "yes_1" => 0,
    //         "neutral" => 0,
    //         "no_1" => 0,
    //         "no_2" => 0,
    //         "none" => 0,
    //         "total" => 0
    //     );

    //     foreach($raw_results as $row){
    //         if($row->q_1 == "lubos_sang-ayon"){
    //             $q1_conv["yes_2"]++;
    //         }else if($row->q_1 == "sang-ayon"){
    //             $q1_conv["yes_1"]++;
    //         }else if($row->q_1 == "walang_kinikilingan"){
    //             $q1_conv["neutral"]++;
    //         }else if($row->q_1 == "hindi_sang-ayon"){
    //             $q1_conv["no_1"]++;
    //         }else if($row->q_1 == "lubos_hindi_sang-ayon"){
    //             $q1_conv["no_2"]++;
    //         }else{
    //             $q1_conv["none"]++;
    //         }

    //         if($row->q_2 == "lubos_sang-ayon"){
    //             $q2_conv["yes_2"]++;
    //         }else if($row->q_2 == "sang-ayon"){
    //             $q2_conv["yes_1"]++;
    //         }else if($row->q_2 == "walang_kinikilingan"){
    //             $q2_conv["neutral"]++;
    //         }else if($row->q_2 == "hindi_sang-ayon"){
    //             $q2_conv["no_1"]++;
    //         }else if($row->q_2 == "lubos_hindi_sang-ayon"){
    //             $q2_conv["no_2"]++;
    //         }else{
    //             $q2_conv["none"]++;
    //         }

    //         if($row->q_3 == "lubos_sang-ayon"){
    //             $q3_conv["yes_2"]++;
    //         }else if($row->q_3 == "sang-ayon"){
    //             $q3_conv["yes_1"]++;
    //         }else if($row->q_3 == "walang_kinikilingan"){
    //             $q3_conv["neutral"]++;
    //         }else if($row->q_3 == "hindi_sang-ayon"){
    //             $q3_conv["no_1"]++;
    //         }else if($row->q_3 == "lubos_hindi_sang-ayon"){
    //             $q3_conv["no_2"]++;
    //         }else{
    //             $q3_conv["none"]++;
    //         }
    //     }
        
    //     $q1["yes_2"] = $q1_conv["yes_2"];
    //     $q1["yes_1"] = $q1_conv["yes_1"];
    //     $q1["neutral"] = $q1_conv["neutral"];
    //     $q1["no_1"] = $q1_conv["no_1"];
    //     $q1["no_2"] = $q1_conv["no_2"];
    //     $q1["none"] = $q1_conv["none"];
    //     $q1["total"] = $q1_conv["none"] + $q1_conv["no_2"] + $q1_conv["no_1"] + $q1_conv["neutral"] + $q1_conv["yes_1"] + $q1_conv["yes_2"];
        
    //     $q2["yes_2"] = $q2_conv["yes_2"];
    //     $q2["yes_1"] = $q2_conv["yes_1"];
    //     $q2["neutral"] = $q2_conv["neutral"];
    //     $q2["no_1"] = $q2_conv["no_1"];
    //     $q2["no_2"] = $q2_conv["no_2"];
    //     $q2["none"] = $q2_conv["none"];
    //     $q2["total"] = $q2_conv["none"] + $q2_conv["no_2"] + $q2_conv["no_1"] + $q2_conv["neutral"] + $q2_conv["yes_1"] + $q2_conv["yes_2"];
        
    //     $q3["yes_2"] = $q3_conv["yes_2"];
    //     $q3["yes_1"] = $q3_conv["yes_1"];
    //     $q3["neutral"] = $q3_conv["neutral"];
    //     $q3["no_1"] = $q3_conv["no_1"];
    //     $q3["no_2"] = $q3_conv["no_2"];
    //     $q3["none"] = $q3_conv["none"];
    //     $q3["total"] = $q3_conv["none"] + $q3_conv["no_2"] + $q3_conv["no_1"] + $q3_conv["neutral"] + $q3_conv["yes_1"] + $q3_conv["yes_2"];
        
        
        
    //     $master_list = array();
    //     $almost_final = array();
    //     array_push($almost_final, array(
    //             "Question" => $q1_conv["qs"],
    //             "Lubos na Sang-ayon" => number_format($q1["yes_2"], 0),
    //             "Sang-ayon" => number_format($q1["yes_1"], 0),
    //             "Walang Kinikilingan" => number_format($q1["neutral"], 0),
    //             "Hindi Sang-ayon" => number_format($q1["no_1"], 0),
    //             "Lubos na Hindi Sang-ayon" => number_format($q1["no_2"], 0),
    //             "Walang Napili" => number_format($q1["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => $q2_conv["qs"],
    //             "Lubos na Sang-ayon" => number_format($q2["yes_2"], 0),
    //             "Sang-ayon" => number_format($q2["yes_1"], 0),
    //             "Walang Kinikilingan" => number_format($q2["neutral"], 0),
    //             "Hindi Sang-ayon" => number_format($q2["no_1"], 0),
    //             "Lubos na Hindi Sang-ayon" => number_format($q2["no_2"], 0),
    //             "Walang Napili" => number_format($q2["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => $q3_conv["qs"],
    //             "Lubos na Sang-ayon" => number_format($q3["yes_2"], 0),
    //             "Sang-ayon" => number_format($q3["yes_1"], 0),
    //             "Walang Kinikilingan" => number_format($q3["neutral"], 0),
    //             "Hindi Sang-ayon" => number_format($q3["no_1"], 0),
    //             "Lubos na Hindi Sang-ayon" => number_format($q3["no_2"], 0),
    //             "Walang Napili" => number_format($q3["none"], 0),
    //         )
    //     );
    //     array_push($almost_final, array(
    //             "Question" => "Total Respondents",
    //             "Lubos na Sang-ayon" => "",
    //             "Sang-ayon" => "",
    //             "Walang Kinikilingan" => "",
    //             "Hindi Sang-ayon" => "",
    //             "Lubos na Hindi Sang-ayon" => "",
    //             "Walang Napili" => number_format($q3["total"], 0),
    //         )
    //     );

    //     // dd($master_list);
    //     array_push($master_list, $almost_final);
    //     // dd($master_list);
    //         $new_collection = collect(); 
    //         foreach($master_list as $list_collection_row){
    //             $new_collection = $new_collection->merge($list_collection_row);
    //         }

    //         $excel_data = json_decode(json_encode($new_collection), true);

    //         // return Excel::create("SRVY_CONV_".$province_name."_".$municipality_name."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $province_name, $municipality_name) {
    //         //     $excel->sheet($province_name.", ".$municipality_name, function($sheet) use ($excel_data) {
    //         //         $sheet->fromArray($excel_data);
    //         //         $sheet->freezeFirstRow();
                    
    //         //         $sheet->setHeight(1, 30);
    //         //         $sheet->cells('A1:G1', function ($cells) {
    //         //             $cells->setBackground('#92D050');
    //         //             $cells->setAlignment('center');
    //         //             $cells->setValignment('center');
    //         //         });
    //         //         $sheet->setBorder('A1:G1', 'thick');
    //         //         $sheet->setBorder('A2:G4', 'thin');
    //         //         $sheet->setBorder('A5:G5', 'thick');
    //         //     });
    //         // })->download('xlsx');
    //         return Excel::create("CON_".$province_name."_".$municipality_name."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $province_name, $municipality_name) {
    //             $excel->sheet($municipality_name, function($sheet) use ($excel_data) {
    //                 $sheet->fromArray($excel_data);
    //                 $sheet->freezeFirstRow();
            
    //                 $sheet->setHeight(1, 30);
    //                 $sheet->cells('A1:G1', function ($cells) {
    //                     $cells->setBackground('#92D050');
    //                     $cells->setAlignment('center');
    //                     $cells->setValignment('center');
    //                     $cells->setFont(['bold' => true]);
    //                 });
    //                 $sheet->setBorder('A1:G1', 'thin');
    //                 $sheet->cells('A1:G5', function ($cells) {
    //                     $cells->setBorder('thin', 'thick', 'thick', 'thick');
    //                 });
    //                 $sheet->cells('A5:G5', function ($cells) {
    //                     $cells->setBorder('thin', 'thick', 'thick', 'thick');
    //                     $cells->setFont(['bold' => true]);
    //                 });
    //             });
    //         })->download('xlsx');
    // }

    // public function getIncludedProvinces(){
    //     $includedRegions = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
    //         ->select(DB::raw('LEFT(rcef_id, 4) as prvCode'))
    //         ->groupBy(DB::raw('LEFT(rcef_id, 4)'))
    //         ->get();
    //     // dd($includedRegions);

    //     $prvC = array();

    //     foreach($includedRegions as $row){
    //         array_push($prvC, $row->prvCode);
    //     }

    //     $includedRegionsName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
    //         ->select('province')
    //         ->whereIn('prv_code', $prvC)
    //         ->groupBy('province')
    //         ->get();

    //     $provinces = array();
    //     foreach($includedRegionsName as $row){
    //         array_push($provinces, $row->province);
    //     }

    //     return $provinces;
    // }
    
    public function getIncludedProvinces(){
        $includedRegions = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
            ->select(DB::raw('LEFT(claim_code, 4) as prvCode'))
            ->groupBy(DB::raw('LEFT(claim_code, 4)'))
            ->get();

        $prvC = array();

        foreach($includedRegions as $row){
            array_push($prvC, $row->prvCode);
        }
        $includedRegionsName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('prv_code', 'province')
            ->whereIn('prv_code', $prvC)
            ->groupBy('province')
            ->get();
        $provinces = array();
        foreach($includedRegionsName as $row){
            $provinces[] = [
                // 'prv' => $row->prv,
                'prvCode' => $row->prv_code,
                'province' => $row->province,
            ];
        }
        // dd($provinces);
        return $provinces;
        // return response()->json($provinces);
    }

    public function getIncludedProvincesConv(){
        $includedRegions = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
            ->select('province')
            ->groupBy('province')
            ->get();

        // $prvC = array();

        // foreach($includedRegions as $row){
        //     array_push($prvC, $row->prvCode);
        // }

        // $includedRegionsName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        //     ->select('province')
        //     ->whereIn('prv_code', $prvC)
        //     ->groupBy('province')
        //     ->get();

        $provinces = array();
        foreach($includedRegions as $row){
            array_push($provinces, $row->province);
        }

        return $provinces;
    }
    // public function getIncludedProvincesConv(){
    //     $includedRegions = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
    //         ->select(DB::raw('LEFT(rcef_id, 4) as prvCode'))
    //         ->groupBy(DB::raw('LEFT(rcef_id, 4)'))
    //         ->get();

    //     $prvC = array();

    //     foreach($includedRegions as $row){
    //         array_push($prvC, $row->prvCode);
    //     }

    //     $includedRegionsName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
    //         ->select('province')
    //         ->whereIn('prv_code', $prvC)
    //         ->groupBy('province')
    //         ->get();

    //     $provinces = array();
    //     foreach($includedRegionsName as $row){
    //         array_push($provinces, $row->province);
    //     }

    //     return $provinces;
    // }

    // public function getIncludedMunicipality(Request $request){
    //     $province = $request->prv;
        
    //     $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
    //         ->select('municipality')
    //         ->where('province', $province)
    //         ->groupBy('municipality')
    //         ->get();
        
    //     $muniArray = array();
    //     foreach($includedMunicipalities as $row){
    //         array_push($muniArray, $row->municipality);
    //     }

    //     return $muniArray;
    // }
    public function getIncludedMunicipality(Request $request){
        $province = $request->prv;
        // dd($province);
        // $provinceCodes = explode(',', $request->prv); 
        // dd($province);
        // $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
        //     ->select('municipality')
        //     ->where('province', $province)
        //     ->groupBy('municipality')
        //     ->get();
        // if (!is_array($provinceCodes)) {
        //     $provinceCodes = [$provinceCodes]; // Convert to array if it's not already
        // }
        
        // $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        //         ->select('prv','municipality','prv_code')
        //         ->where('prv_code', $province)
        //         // ->groupBy('province')
        //         ->get();
    
            $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                            ->join($GLOBALS['season_prefix']."rcep_css.updated_bep_responses", $GLOBALS['season_prefix']."rcep_css.updated_bep_responses.claim_code", "=", $GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.paymaya_code')
                            ->select($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.municipality')
                            ->whereRaw("LEFT(paymaya_code, 4) = ?", [$province])
                            ->distinct($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.municipality')
                            ->get();

        // $distinctRecords = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
        //     ->join($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses', $GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code', '=', $GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.paymaya_code')
        //     ->select(DB::raw('DISTINCT '.$GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.*, '.$GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.*'))
        //     ->get();
        // $prvValues = $includedMunicipalities->pluck('prv_code');
        // dd($includedMunicipalities);

        $muniArray = array();
        foreach($includedMunicipalities as $row){
            // $temp = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
            //     ->select('*')
            //     ->where("claim_code", "=", $row->paymaya_code)
            //     ->get();

            $muniCount = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                    ->whereRaw("LEFT(claim_code, 4) = ?", [$province])
                    ->count();
                    // dd($muniCount);
            // $muniCount = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            // ->whereRaw("LEFT(paymaya_code, 4) = ?", [$row->prv_code])
            // ->count();
            // dd($row);
            $mun_code  = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("municipality", $row->municipality)->first();
            
            $muniArray[] = [
                // 'prv' => $row->prv,
                'municipality' => $row->municipality,
                'count' => $muniCount,
                'mun_code' => $mun_code->munCode,
            ];
        }

        return $muniArray;
    }

    public function getIncludedMunicipalityConv(Request $request){
        $province = $request->prv;
        
        $muniCount = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
                    ->where('province', $province)
                    ->count();
        $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
            ->where('province', $province)
            ->groupBy('municipality')
            ->get();
        $muniArray = array();
        foreach($includedMunicipalities as $row){
            // array_push($muniArray, $row->municipality);
            $muniArray[] = [
                'municipality' => $row->municipality,
                'count' => $muniCount,
            ];
        }

        return $muniArray;
    }
    // public function getIncludedMunicipalityConv(Request $request){
    //     $province = $request->prv;
        
    //     $includedMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
    //         ->select('municipality')
    //         ->where('province', $province)
    //         ->groupBy('municipality')
    //         ->get();
        
    //     $muniArray = array();
    //     foreach($includedMunicipalities as $row){
    //         array_push($muniArray, $row->municipality);
    //     }

    //     return $muniArray;
    // }

    public function filterLocation(Request $request){
        $province = $request->prv;
        $municipality = $request->mun;

        // dd([
        //     $province, $municipality
        // ]);

        if($province == "All"){
            $province = "%";
        }

        if($municipality == "All"){
            $municipality = "%";
        }
        
        $survey_questions = [];
        $survey_questions = DB::table('rcef_ionic_db.survey_questions')
            ->select('survey_questions.body as question',
                     'survey_questions.options_en', 
                     'survey_questions.q_id', 
                     'survey_questions.type')
            ->where('mode', '=', 'bep')
            ->where('type', '!=', 'input')
            ->get();
            
        $s_questions = [];
        // $s_questions_count = 0;
        foreach ($survey_questions as $question) {
            preg_match('/(\d+)/', $question->q_id, $matches);
            $id_number = isset($matches[1]) ? $matches[1] : null;
            if (isset($question->options_en)) {
                $options = json_decode($question->options_en, true); 

            } else {
                $options = [];
            }

            $total = 0;
            foreach ($options as $index => $item) {
                $code = $item['code'];
                $column = $question->q_id;
                // $counts = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                //         ->where('claim_code', 'LIKE', $province.'%')
                //         ->where('claim_code', 'LIKE', $municipality.'%')
                //         ->where($column, $code)
                //         ->count();
                // $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                //     ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", $GLOBALS['season_prefix']."rcep_paymaya.tbl_claim.paymaya_code", "=", $GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code')
                //     ->select($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.*')
                //     ->where($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.municipality', 'LIKE', $municipality)
                //     // ->distinct($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.municipality')
                //     ->count();

                if($municipality == "%"){
                    // $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                    //         ->where('claim_code', 'LIKE', $province.'%')
                    //         ->where('claim_code', 'LIKE', $municipality.'%')
                    //         ->where($column, $code)
                    //         ->count();

                    $prov_name = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                        ->where('prv', 'like', $province.'%')
                        ->first()->province;
                    // $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                    //     ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim', $GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code', '=', $GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.paymaya_code')
                    //     ->where($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.province', $prov_name)
                    //     ->where($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.'.$column, $code)
                    //     ->count();
                    $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses as ubr')
                    ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim as tc', 'ubr.claim_code', '=', 'tc.paymaya_code')
                    // ->where('tc.municipality', $municipality)
                    ->where('ubr.claim_code', 'LIKE', $province.'%')
                    ->where('ubr.'.$column, $code)
                    ->distinct('ubr.claim_code')
                    ->count('ubr.claim_code');
                } else {
                    $prov_name = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                        ->where('prv', 'like', $province.'%')
                        ->where('municipality', $municipality)
                        ->first()->province;
                    // $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                    //     ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim', $GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code', '=', $GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.paymaya_code')
                    //     ->where($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.municipality', $municipality)
                    //     ->where($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.province', $prov_name)
                    //     ->where($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.'.$column, $code)
                    //     ->count();
                    
                    // $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
                    //     ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim', $GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code', '=', $GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.paymaya_code')
                    //     ->where($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim.municipality', $municipality)
                    //     ->where($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code', $prov_name)
                    //     ->where($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.'.$column, $code)
                    //     ->distinct($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses.claim_code')
                    //     ->count();
                    $counts2 = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses as ubr')
                    ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim as tc', 'ubr.claim_code', '=', 'tc.paymaya_code')
                    ->where('tc.municipality', $municipality)
                    ->where('ubr.claim_code', 'LIKE', $province.'%')
                    ->where('ubr.'.$column, $code)
                    ->distinct('ubr.claim_code')
                    ->count('ubr.claim_code');
                }
                // dd($counts2);
                $options[$index]["count"] = $counts2;
                $total += $counts2;
            }
            $s_questions[] = [
                'id' => $id_number,
                'q_id' => $question->q_id,
                'question' => $question->question,
                'options' => $options,
                'type' => $question->type,
                'total_response' => $total,
            ];
                    
        }
        // dd($survey_questions);
        // $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_bep_responses')
        //     ->where('claim_code', 'LIKE', $province.'%')
        //     // ->where('claim_code', 'LIKE', $municipality.'%')
        //     ->get();
        // $total_respoondents = count($raw_results);
        // $perc_questions = array(
        //     "total" => $total_respoondents,
        // );

        return $s_questions;
        // return view('cssDashboard.index2', compact(
        // // return response()->json([
        //     'survey_questions',
        //     's_questions',
        //     'options'
        // ));
    }
    // public function filterLocation(Request $request){
    //     $province = $request->prv;
    //     $municipality = $request->mun;

    //     if($province == "All"){
    //         $province = "%";
    //     }

    //     if($municipality == "All"){
    //         $municipality = "%";
    //     }

    //     $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.ebinhi_response')
    //         ->where('province', 'LIKE', $province)
    //         ->where('municipality', 'LIKE', $municipality)
    //         ->get();

    //     $total_respoondents = count($raw_results);
    //     $q1 = array(
    //         "id" => "q_1",
    //         "qs" => "1. Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?",
    //         "type" => "bin",
    //         "yes" => 0,
    //         "no" => 0,
    //         "maybe" => 0,
    //     );
    //     $q2 = array(
    //         "id" => "q_2",
    //         "qs" => "2. Nalaman ko ng mas maaga ang skedyul dahil sa text.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q3 = array(
    //         "id" => "q_3",
    //         "qs" => "3. Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q4 = array(
    //         "id" => "q_4",
    //         "qs" => "4. Mas malapit ang pinagkuhanan ko ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q5 = array(
    //         "id" => "q_5",
    //         "qs" => "5. Mas maikli na ang pila sa pagkuha ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q6 = array(
    //         "id" => "q_6",
    //         "qs" => "6. Mas mabilis ang pagkuha ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q7 = array(
    //         "id" => "q_7",
    //         "qs" => "7. Mas patas ang pamimigay ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q8 = array(
    //         "id" => "q_8",
    //         "qs" => "8. Mas nasunod ang tamang alokasyon ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q9 = array(
    //         "id" => "q_9",
    //         "qs" => "9. Mas lumaki ang tsansang makuha ang gusto kong variety ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q10 = array(
    //         "id" => "q_10",
    //         "qs" => "10. Mas panatag ang loob ko sa namimigay ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q11 = array(
    //         "id" => "q_11",
    //         "qs" => "11. Mas maayos ang sistema ng pamimigay ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q12 = array(
    //         "id" => "q_12",
    //         "qs" => "12. Mas mabuti ang kabuuang karanasan ko sa pamimigay ng binhi ngayon.",
    //         "type" => "spec",
    //         "agree" => 0,
    //         "disagree" => 0,
    //         "neutral" => 0,
    //         "none" => 0,
    //     );
    //     $q15 = array(
    //         "id" => "q_15",
    //         "qs" => "14. Nakatanggap ka rin ba ng kalendaryo at leaflet ngayon galing sa RCEF?",
    //         "type" => "bin",
    //         "yes" => 0,
    //         "no" => 0,
    //         "maybe" => 0,
    //     );

    //     foreach($raw_results as $row){
    //         if($row->q_1 == "oo"){
    //             $q1["yes"]++;
    //         }else if($row->q_1 == "hindi"){
    //             $q1["no"]++;
    //         }else{
    //             $q1["maybe"]++;
    //         }

    //         if($row->q_2 == "sang-ayon"){
    //             $q2["agree"]++;
    //         }else if($row->q_2 == "hindi_sang-ayon"){
    //             $q2["disagree"]++;
    //         }else if($row->q_2 == "walang_kinikilingan"){
    //             $q2["neutral"]++;
    //         }else{
    //             $q2["none"]++;
    //         }

    //         if($row->q_3 == "sang-ayon"){
    //             $q3["agree"]++;
    //         }else if($row->q_3 == "hindi_sang-ayon"){
    //             $q3["disagree"]++;
    //         }else if($row->q_3 == "walang_kinikilingan"){
    //             $q3["neutral"]++;
    //         }else{
    //             $q3["none"]++;
    //         }

    //         if($row->q_4 == "sang-ayon"){
    //             $q4["agree"]++;
    //         }else if($row->q_4 == "hindi_sang-ayon"){
    //             $q4["disagree"]++;
    //         }else if($row->q_4 == "walang_kinikilingan"){
    //             $q4["neutral"]++;
    //         }else{
    //             $q4["none"]++;
    //         }

    //         if($row->q_5 == "sang-ayon"){
    //             $q5["agree"]++;
    //         }else if($row->q_5 == "hindi_sang-ayon"){
    //             $q5["disagree"]++;
    //         }else if($row->q_5 == "walang_kinikilingan"){
    //             $q5["neutral"]++;
    //         }else{
    //             $q5["none"]++;
    //         }

    //         if($row->q_6 == "sang-ayon"){
    //             $q6["agree"]++;
    //         }else if($row->q_6 == "hindi_sang-ayon"){
    //             $q6["disagree"]++;
    //         }else if($row->q_6 == "walang_kinikilingan"){
    //             $q6["neutral"]++;
    //         }else{
    //             $q6["none"]++;
    //         }

    //         if($row->q_7 == "sang-ayon"){
    //             $q7["agree"]++;
    //         }else if($row->q_7 == "hindi_sang-ayon"){
    //             $q7["disagree"]++;
    //         }else if($row->q_7 == "walang_kinikilingan"){
    //             $q7["neutral"]++;
    //         }else{
    //             $q7["none"]++;
    //         }

    //         if($row->q_8 == "sang-ayon"){
    //             $q8["agree"]++;
    //         }else if($row->q_8 == "hindi_sang-ayon"){
    //             $q8["disagree"]++;
    //         }else if($row->q_8 == "walang_kinikilingan"){
    //             $q8["neutral"]++;
    //         }else{
    //             $q8["none"]++;
    //         }

    //         if($row->q_9 == "sang-ayon"){
    //             $q9["agree"]++;
    //         }else if($row->q_9 == "hindi_sang-ayon"){
    //             $q9["disagree"]++;
    //         }else if($row->q_9 == "walang_kinikilingan"){
    //             $q9["neutral"]++;
    //         }else{
    //             $q9["none"]++;
    //         }

    //         if($row->q_10 == "sang-ayon"){
    //             $q10["agree"]++;
    //         }else if($row->q_10 == "hindi_sang-ayon"){
    //             $q10["disagree"]++;
    //         }else if($row->q_10 == "walang_kinikilingan"){
    //             $q10["neutral"]++;
    //         }else{
    //             $q10["none"]++;
    //         }

    //         if($row->q_11 == "sang-ayon"){
    //             $q11["agree"]++;
    //         }else if($row->q_11 == "hindi_sang-ayon"){
    //             $q11["disagree"]++;
    //         }else if($row->q_11 == "walang_kinikilingan"){
    //             $q11["neutral"]++;
    //         }else{
    //             $q11["none"]++;
    //         }

    //         if($row->q_12 == "sang-ayon"){
    //             $q12["agree"]++;
    //         }else if($row->q_12 == "hindi_sang-ayon"){
    //             $q12["disagree"]++;
    //         }else if($row->q_12 == "walang_kinikilingan"){
    //             $q12["neutral"]++;
    //         }else{
    //             $q12["none"]++;
    //         }

    //         if($row->q_15 == "oo"){
    //             $q15["yes"]++;
    //         }else if($row->q_15 == "hindi"){
    //             $q15["no"]++;
    //         }else{
    //             $q15["maybe"]++;
    //         }
    //     }

    //     $q1["yes"] = number_format((($q1["yes"] / $total_respoondents) * 100), 2);
    //     $q1["no"] = number_format((($q1["no"] / $total_respoondents) * 100), 2);
    //     $q1["maybe"] = number_format((($q1["maybe"] / $total_respoondents) * 100), 2);
        
    //     $q2["agree"] = number_format((($q2["agree"] / $total_respoondents) * 100), 2);
    //     $q2["disagree"] = number_format((($q2["disagree"] / $total_respoondents) * 100), 2);
    //     $q2["neutral"] = number_format((($q2["neutral"] / $total_respoondents) * 100), 2);
    //     $q2["none"] = number_format((($q2["none"] / $total_respoondents) * 100), 2);
        
    //     $q3["agree"] = number_format((($q3["agree"] / $total_respoondents) * 100), 2);
    //     $q3["disagree"] = number_format((($q3["disagree"] / $total_respoondents) * 100), 2);
    //     $q3["neutral"] = number_format((($q3["neutral"] / $total_respoondents) * 100), 2);
    //     $q3["none"] = number_format((($q3["none"] / $total_respoondents) * 100), 2);
        
    //     $q4["agree"] = number_format((($q4["agree"] / $total_respoondents) * 100), 2);
    //     $q4["disagree"] = number_format((($q4["disagree"] / $total_respoondents) * 100), 2);
    //     $q4["neutral"] = number_format((($q4["neutral"] / $total_respoondents) * 100), 2);
    //     $q4["none"] = number_format((($q4["none"] / $total_respoondents) * 100), 2);

    //     $q5["agree"] = number_format((($q5["agree"] / $total_respoondents) * 100), 2);
    //     $q5["disagree"] = number_format((($q5["disagree"] / $total_respoondents) * 100), 2);
    //     $q5["neutral"] = number_format((($q5["neutral"] / $total_respoondents) * 100), 2);
    //     $q5["none"] = number_format((($q5["none"] / $total_respoondents) * 100), 2);

    //     $q6["agree"] = number_format((($q6["agree"] / $total_respoondents) * 100), 2);
    //     $q6["disagree"] = number_format((($q6["disagree"] / $total_respoondents) * 100), 2);
    //     $q6["neutral"] = number_format((($q6["neutral"] / $total_respoondents) * 100), 2);
    //     $q6["none"] = number_format((($q6["none"] / $total_respoondents) * 100), 2);

    //     $q7["agree"] = number_format((($q7["agree"] / $total_respoondents) * 100), 2);
    //     $q7["disagree"] = number_format((($q7["disagree"] / $total_respoondents) * 100), 2);
    //     $q7["neutral"] = number_format((($q7["neutral"] / $total_respoondents) * 100), 2);
    //     $q7["none"] = number_format((($q7["none"] / $total_respoondents) * 100), 2);

    //     $q8["agree"] = number_format((($q8["agree"] / $total_respoondents) * 100), 2);
    //     $q8["disagree"] = number_format((($q8["disagree"] / $total_respoondents) * 100), 2);
    //     $q8["neutral"] = number_format((($q8["neutral"] / $total_respoondents) * 100), 2);
    //     $q8["none"] = number_format((($q8["none"] / $total_respoondents) * 100), 2);
        
    //     $q9["agree"] = number_format((($q9["agree"] / $total_respoondents) * 100), 2);
    //     $q9["disagree"] = number_format((($q9["disagree"] / $total_respoondents) * 100), 2);
    //     $q9["neutral"] = number_format((($q9["neutral"] / $total_respoondents) * 100), 2);
    //     $q9["none"] = number_format((($q9["none"] / $total_respoondents) * 100), 2);
        
    //     $q10["agree"] = number_format((($q10["agree"] / $total_respoondents) * 100), 2);
    //     $q10["disagree"] = number_format((($q10["disagree"] / $total_respoondents) * 100), 2);
    //     $q10["neutral"] = number_format((($q10["neutral"] / $total_respoondents) * 100), 2);
    //     $q10["none"] = number_format((($q10["none"] / $total_respoondents) * 100), 2);
        
    //     $q11["agree"] = number_format((($q11["agree"] / $total_respoondents) * 100), 2);
    //     $q11["disagree"] = number_format((($q11["disagree"] / $total_respoondents) * 100), 2);
    //     $q11["neutral"] = number_format((($q11["neutral"] / $total_respoondents) * 100), 2);
    //     $q11["none"] = number_format((($q11["none"] / $total_respoondents) * 100), 2);
        
    //     $q12["agree"] = number_format((($q12["agree"] / $total_respoondents) * 100), 2);
    //     $q12["disagree"] = number_format((($q12["disagree"] / $total_respoondents) * 100), 2);
    //     $q12["neutral"] = number_format((($q12["neutral"] / $total_respoondents) * 100), 2);
    //     $q12["none"] = number_format((($q12["none"] / $total_respoondents) * 100), 2);
        
    //     $q15["yes"] = number_format((($q15["yes"] / $total_respoondents) * 100), 2);
    //     $q15["no"] = number_format((($q15["no"] / $total_respoondents) * 100), 2);
    //     $q15["maybe"] = number_format((($q15["maybe"] / $total_respoondents) * 100), 2);
        

    //     $perc_questions = array(
    //         "q1" => $q1,
    //         "q2" => $q2,
    //         "q3" => $q3,
    //         "q4" => $q4,
    //         "q5" => $q5,
    //         "q6" => $q6,
    //         "q7" => $q7,
    //         "q8" => $q8,
    //         "q9" => $q9,
    //         "q10" => $q10,
    //         "q11" => $q11,
    //         "q12" => $q12,
    //         "q15" => $q15,
    //         "total" => $total_respoondents,
    //     );

    //     return $perc_questions;
    // }

    // public function filterLocationConv(Request $request){
    //     $province = $request->prv;
    //     $municipality = $request->mun;

    //     if($province == "All"){
    //         $province = "%";
    //     }

    //     if($municipality == "All"){
    //         $municipality = "%";
    //     }

    //     $raw_results = DB::table($GLOBALS['season_prefix'].'rcep_css.conv_response')
    //         ->where('province', 'LIKE', $province)
    //         ->where('municipality', 'LIKE', $municipality)
    //         ->get();

    //     $total_respoondents_conv = count($raw_results);
    //     $q1_conv = array(
    //         "id" => "q_1c",
    //         "qs" => "1. Maayos ba ang proseso ng pagtala?",
    //         "type" => "spec",
    //         "yes_2" => 0,
    //         "yes_1" => 0,
    //         "neutral" => 0,
    //         "no_1" => 0,
    //         "no_2" => 0,
    //         "none" => 0,
    //     );
    //     $q2_conv = array(
    //         "id" => "q_2c",
    //         "qs" => "2. Maayos ba at madaling intindihin ang technical briefing?",
    //         "type" => "spec",
    //         "yes_2" => 0,
    //         "yes_1" => 0,
    //         "neutral" => 0,
    //         "no_1" => 0,
    //         "no_2" => 0,
    //         "none" => 0,
    //     );
    //     $q3_conv = array(
    //         "id" => "q_3c",
    //         "qs" => "3. Maayos at mabilis ba ang pagkuha ng inyong binhi?",
    //         "type" => "spec",
    //         "yes_2" => 0,
    //         "yes_1" => 0,
    //         "neutral" => 0,
    //         "no_1" => 0,
    //         "no_2" => 0,
    //         "none" => 0,
    //     );

    //     foreach($raw_results as $row){
    //         if($row->q_1 == "lubos_sang-ayon"){
    //             $q1_conv["yes_2"]++;
    //         }else if($row->q_1 == "sang-ayon"){
    //             $q1_conv["yes_1"]++;
    //         }else if($row->q_1 == "walang_kinikilingan"){
    //             $q1_conv["neutral"]++;
    //         }else if($row->q_1 == "hindi_sang-ayon"){
    //             $q1_conv["no_1"]++;
    //         }else if($row->q_1 == "lubos_hindi_sang-ayon"){
    //             $q1_conv["no_2"]++;
    //         }else{
    //             $q1_conv["none"]++;
    //         }

    //         if($row->q_2 == "lubos_sang-ayon"){
    //             $q2_conv["yes_2"]++;
    //         }else if($row->q_2 == "sang-ayon"){
    //             $q2_conv["yes_1"]++;
    //         }else if($row->q_2 == "walang_kinikilingan"){
    //             $q2_conv["neutral"]++;
    //         }else if($row->q_2 == "hindi_sang-ayon"){
    //             $q2_conv["no_1"]++;
    //         }else if($row->q_2 == "lubos_hindi_sang-ayon"){
    //             $q2_conv["no_2"]++;
    //         }else{
    //             $q2_conv["none"]++;
    //         }

    //         if($row->q_3 == "lubos_sang-ayon"){
    //             $q3_conv["yes_2"]++;
    //         }else if($row->q_3 == "sang-ayon"){
    //             $q3_conv["yes_1"]++;
    //         }else if($row->q_3 == "walang_kinikilingan"){
    //             $q3_conv["neutral"]++;
    //         }else if($row->q_3 == "hindi_sang-ayon"){
    //             $q3_conv["no_1"]++;
    //         }else if($row->q_3 == "lubos_hindi_sang-ayon"){
    //             $q3_conv["no_2"]++;
    //         }else{
    //             $q3_conv["none"]++;
    //         }
    //     }

    //     $q1_conv["yes_2"] = number_format((($q1_conv["yes_2"] / $total_respoondents_conv) * 100), 2);
    //     $q1_conv["yes_1"] = number_format((($q1_conv["yes_1"] / $total_respoondents_conv) * 100), 2);
    //     $q1_conv["neutral"] = number_format((($q1_conv["neutral"] / $total_respoondents_conv) * 100), 2);
    //     $q1_conv["no_1"] = number_format((($q1_conv["no_1"] / $total_respoondents_conv) * 100), 2);
    //     $q1_conv["no_2"] = number_format((($q1_conv["no_2"] / $total_respoondents_conv) * 100), 2);
    //     $q1_conv["none"] = number_format((($q1_conv["none"] / $total_respoondents_conv) * 100), 2);
        
    //     $q2_conv["yes_2"] = number_format((($q2_conv["yes_2"] / $total_respoondents_conv) * 100), 2);
    //     $q2_conv["yes_1"] = number_format((($q2_conv["yes_1"] / $total_respoondents_conv) * 100), 2);
    //     $q2_conv["neutral"] = number_format((($q2_conv["neutral"] / $total_respoondents_conv) * 100), 2);
    //     $q2_conv["no_1"] = number_format((($q2_conv["no_1"] / $total_respoondents_conv) * 100), 2);
    //     $q2_conv["no_2"] = number_format((($q2_conv["no_2"] / $total_respoondents_conv) * 100), 2);
    //     $q2_conv["none"] = number_format((($q2_conv["none"] / $total_respoondents_conv) * 100), 2);
        
    //     $q3_conv["yes_2"] = number_format((($q3_conv["yes_2"] / $total_respoondents_conv) * 100), 2);
    //     $q3_conv["yes_1"] = number_format((($q3_conv["yes_1"] / $total_respoondents_conv) * 100), 2);
    //     $q3_conv["neutral"] = number_format((($q3_conv["neutral"] / $total_respoondents_conv) * 100), 2);
    //     $q3_conv["no_1"] = number_format((($q3_conv["no_1"] / $total_respoondents_conv) * 100), 2);
    //     $q3_conv["no_2"] = number_format((($q3_conv["no_2"] / $total_respoondents_conv) * 100), 2);
    //     $q3_conv["none"] = number_format((($q3_conv["none"] / $total_respoondents_conv) * 100), 2);

    //     $perc_questions = array(
    //         "q1" => $q1_conv,
    //         "q2" => $q2_conv,
    //         "q3" => $q3_conv,
    //         "total" => $total_respoondents_conv
    //     );

    //     return $perc_questions;
    // }
    
    public function filterLocationConv(Request $request){
        $province = $request->prv;
        $municipality = $request->mun;

        if($province == "All"){
            $province = "%";
        }

        if($municipality == "All"){
            $municipality = "%";
        }
        $survey_questions_con = [];
        $survey_questions_con = DB::table('rcef_ionic_db.survey_questions')
            ->select('survey_questions.body as question',
                     'survey_questions.options_en', 
                     'survey_questions.q_id', 
                     'survey_questions.type')
            ->where('mode', '=', 'con')
            ->where('type', '!=', 'input')
            ->get();
        $questions_con = [];
        // $questions_con_count = 0;
        foreach ($survey_questions_con as $question_con) {
            preg_match('/(\d+)/', $question_con->q_id, $matches);
            $id_number = isset($matches[1]) ? $matches[1] : null;
            if (isset($question_con->options_en)) {
                $options_con = json_decode($question_con->options_en, true); 

            } else {
                $options_con = []; 
            }

            $total = 0;
            foreach ($options_con as $index => $item) {
                $code_con = $item['code'];
                $column_con = $question_con->q_id;
                $counts_con = DB::table($GLOBALS['season_prefix'].'rcep_css.updated_conv_responses')
                        ->where('municipality', 'LIKE', $municipality.'%')
                        ->where('province', 'LIKE', $province.'%')
                        ->where($column_con, $code_con)
                        ->count();
                $options_con[$index]["count"] = $counts_con;
                $total += $counts_con;
            }
            $questions_con[] = [
                'id' => $id_number,
                'q_id' => $question_con->q_id,
                'question' => $question_con->question,
                'options' => $options_con,
                'type' => $question_con->type,
                'total_response' => $total,
            ];
        }
        return $questions_con;
    }
}
