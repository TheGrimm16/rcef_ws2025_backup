<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;
use Session;
use Config;
use Auth;
use Yajra\Datatables\Facades\Datatables;

class SettingsController extends Controller {

    public function index() {
        return view('settings.archive.home');
    }

    public function createTable($table_name, $fields = [], $primary_key) {
        // echo $primary_key;die();
        $Pkey = $primary_key;
        // check if table is not already exists
        if (!Schema::connection('reports_db')->hasTable($table_name)) {
            Schema::connection('reports_db')->create($table_name, function (Blueprint $table) use ($fields, $table_name, $primary_key) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';
                $table->increments($primary_key);
                //$table->integer($primary_key)->primary();
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        if ($field['type'] == 'integer') {
                            $table->{$field['type']}($field['name'])->unsigned();
                        } elseif ($field['type'] == 'timestamp') {
                            $table->{$field['type']}($field['name'])->useCurrent();
                        } elseif (isset($field['limit'])) {
                            $table->{$field['type']}($field['name'], $field['limit']);
                        } else {
                            $table->{$field['type']}($field['name']);
                        }
                    }
                }
            });

            return response()->json(['message' => 'Given table has been successfully created!'], 200);
        }

        return response()->json(['message' => 'Given table is already existis.'], 400);
    }

    function createDatabasePRV($database_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $database_name";
        DB::statement($query);

        \Config::set('database.connections.reports_db.database', $database_name);
        DB::purge('reports_db');

        /*         * AREA HISTORY TABLE */
        $table_name = 'area_history';
        $primary_key = 'areaHistoryId';

        $fields = [
            ['name' => 'farmerId', 'type' => 'string', 'limit' => '255'],
            ['name' => 'region', 'type' => 'string', 'limit' => '75'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
            ['name' => 'barangay', 'type' => 'string', 'limit' => '100'],
            ['name' => 'area', 'type' => 'string', 'limit' => '20'],
            ['name' => 'rsbsa_control_no', 'type' => 'string', 'limit' => '100'],
            ['name' => 'dateCreated', 'type' => 'datetime'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * AREA HISTORY TABLE */

        /*         * FARMER PROFILE TABLE */
        $table_name = 'farmer_profile';
        $primary_key = 'id';

        $fields = [
            ['name' => 'farmerID', 'type' => 'string', 'limit' => '255'],
            ['name' => 'distributionID', 'type' => 'string', 'limit' => '255'],
            ['name' => 'lastName', 'type' => 'string', 'limit' => '75'],
            ['name' => 'firstName', 'type' => 'string', 'limit' => '75'],
            ['name' => 'midName', 'type' => 'string', 'limit' => '75'],
            ['name' => 'extName', 'type' => 'string', 'limit' => '75'],
            ['name' => 'fullName', 'type' => 'string', 'limit' => '255'],
            ['name' => 'sex', 'type' => 'string', 'limit' => '5'],
            ['name' => 'birthdate', 'type' => 'string', 'limit' => '20'],
            ['name' => 'region', 'type' => 'string', 'limit' => '75'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
            ['name' => 'barangay', 'type' => 'string', 'limit' => '100'],
            ['name' => 'allotment_area', 'type' => 'string', 'limit' => '100'],
            ['name' => 'total_area', 'type' => 'string', 'limit' => '100'],
            ['name' => 'affiliationType', 'type' => 'string', 'limit' => '75'],
            ['name' => 'affiliationName', 'type' => 'string', 'limit' => '255'],
            ['name' => 'affiliationAccreditation', 'type' => 'string', 'limit' => '20'],
            ['name' => 'isDaAccredited', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'isLGU', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'rsbsa_control_no', 'type' => 'string', 'limit' => '100'],
            ['name' => 'isNew', 'type' => 'integer', 'limit' => '1'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '1'],
            ['name' => 'update', 'type' => 'integer', 'limit' => '1'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * FARMER PROFILE TABLE */

        /*         * PENDING RELEASE TABLE */
        $table_name = 'pending_release';
        $primary_key = 'pending_id';

        $fields = [
            ['name' => 'farmer_id', 'type' => 'string', 'limit' => '255'],
            ['name' => 'ticket_no', 'type' => 'string', 'limit' => '75'],
            ['name' => 'batch_ticket_no', 'type' => 'string', 'limit' => '255'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
            ['name' => 'dropOffPoint', 'type' => 'string', 'limit' => '150'],
            ['name' => 'seed_variety', 'type' => 'string', 'limit' => '100'],
            ['name' => 'bags', 'type' => 'integer', 'limit' => '1'],
            ['name' => 'rsbsa_control_no', 'type' => 'string', 'limit' => '100'],
            ['name' => 'date_created', 'type' => 'timestamp'],
            ['name' => 'is_released', 'type' => 'integer', 'limit' => '1'],
            ['name' => 'created_by', 'type' => 'string', 'limit' => '255'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '1'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * PENDING RELEASE TABLE */

        /*         * PERFORMANCE TABLE */
        $table_name = 'performance';
        $primary_key = 'id';

        $fields = [
            ['name' => 'farmerID', 'type' => 'string', 'limit' => '255'],
            ['name' => 'variety_used', 'type' => 'string', 'limit' => '255'],
            ['name' => 'seed_usage', 'type' => 'text'],
            ['name' => 'yield', 'type' => 'string', 'limit' => '255'],
            ['name' => 'preferred_variety', 'type' => 'text'],
            ['name' => 'area_planted', 'type' => 'string', 'limit' => '20'],
            ['name' => 'rsbsa_control_no', 'type' => 'string', 'limit' => '100'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * PERFORMANCE TABLE */

        /*         * RELEASED TABLE */
        $table_name = 'released';
        $primary_key = 'release_id';

        $fields = [
            ['name' => 'farmer_id', 'type' => 'string', 'limit' => '255'],
            ['name' => 'ticket_no', 'type' => 'string', 'limit' => '75'],
            ['name' => 'batch_ticket_no', 'type' => 'string', 'limit' => '255'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '150'],
            ['name' => 'dropOffPoint', 'type' => 'string', 'limit' => '150'],
            ['name' => 'seed_variety', 'type' => 'string', 'limit' => '100'],
            ['name' => 'bags', 'type' => 'integer', 'limit' => '1'],
            ['name' => 'rsbsa_control_no', 'type' => 'string', 'limit' => '100'],
            ['name' => 'date_released', 'type' => 'timestamp'],
            ['name' => 'released_by', 'type' => 'string', 'limit' => '255'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '1'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * RELEASED TABLE */
    }

    function createDatabaseDeliveryInspection($database_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $database_name";
        DB::statement($query);

        \Config::set('database.connections.reports_db.database', $database_name);
        DB::purge('reports_db');

        /*         * IAR PRINT LOGS TABLE */
        $table_name = 'iar_print_logs';
        $primary_key = 'logsId';

        $fields = [
            ['name' => 'iarCode', 'type' => 'string', 'limit' => '100'],
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '100'],
            ['name' => 'dateCreated', 'type' => 'string', 'limit' => '100'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * IAR PRINT LOGS TABLE */

        /*         * DROPOFF POINT TABLE */
        $table_name = 'lib_dropoff_point';
        $primary_key = 'dropoffPointId';

        $fields = [
            ['name' => 'region', 'type' => 'string', 'limit' => '100'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '150'],
            ['name' => 'dropOffPoint', 'type' => 'string', 'limit' => '150'],
            ['name' => 'prv', 'type' => 'string', 'limit' => '11'],
            ['name' => 'dateAdded', 'type' => 'timestamp'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * DROPOFF POINT TABLE */

        /*         * ACTUAL DELIVERY POINT TABLE */
        $table_name = 'tbl_actual_delivery';
        $primary_key = 'actualDeliveryId';

        $fields = [
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'region', 'type' => 'string', 'limit' => '100'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '150'],
            ['name' => 'dropOffPoint', 'type' => 'string', 'limit' => '150'],
            ['name' => 'seedVariety', 'type' => 'string', 'limit' => '50'],
            ['name' => 'totalBagCount', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'dateCreated', 'type' => 'string', 'limit' => '75'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'seedTag', 'type' => 'string', 'limit' => '150'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * ACTUAL DELIVERY POINT TABLE */

        /*         * DELIVERY POINT TABLE */
        $table_name = 'tbl_delivery';
        $primary_key = 'deliveryId';

        $fields = [
            ['name' => 'ticketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'coopAccreditation', 'type' => 'string', 'limit' => '150'],
            ['name' => 'sgAccreditation', 'type' => 'string', 'limit' => '150'],
            ['name' => 'seedTag', 'type' => 'string', 'limit' => '150'],
            ['name' => 'seedVariety', 'type' => 'string', 'limit' => '150'],
            ['name' => 'seedClass', 'type' => 'string', 'limit' => '75'],
            ['name' => 'totalWeight', '10', '2', 'type' => 'decimal'],
            ['name' => 'weightPerBag', '10', '2', 'type' => 'decimal'],
            ['name' => 'totalBagCount', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'deliveryDate', 'type' => 'string', 'limit' => '150'],
            ['name' => 'deliverTo', 'type' => 'string', 'limit' => '250'],
            ['name' => 'coordinates', 'type' => 'string', 'limit' => '250'],
            ['name' => 'status', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'inspectorAllocated', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'userId', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'dateCreated', 'type' => 'string', 'limit' => '150'],
            ['name' => 'oldTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'region', 'type' => 'string', 'limit' => '100'],
            ['name' => 'province', 'type' => 'string', 'limit' => '100'],
            ['name' => 'municipality', 'type' => 'string', 'limit' => '150'],
            ['name' => 'dropOffPoint', 'type' => 'string', 'limit' => '150'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * DELIVERY POINT TABLE */

        /*         * DELIVERY STATUS TABLE */
        $table_name = 'tbl_delivery_status';
        $primary_key = 'deliveryStatusId';

        $fields = [
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'status', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'dateCreated', 'type' => 'string', 'limit' => '75'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '11'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * DELIVERY STATUS TABLE */

        /*         * INSPECTION TABLE */
        $table_name = 'tbl_inspection';
        $primary_key = 'inspectionId';

        $fields = [
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'screeningPassed', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'screeningRemarks', 'type' => 'string', 'limit' => '150'],
            ['name' => 'visualPassed', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'visualFindings', 'type' => 'string', 'limit' => '150'],
            ['name' => 'visualRemarks', 'type' => 'string', 'limit' => '150'],
            ['name' => 'visualInspectionImage', 'type' => 'string', 'limit' => '250'],
            ['name' => 'samplingPassed', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'samplingImage', 'type' => 'string', 'limit' => '250'],
            ['name' => 'batchDeliveryImage', 'type' => 'string', 'limit' => '250'],
            ['name' => 'dateInspected', 'type' => 'string', 'limit' => '75'],
            ['name' => 'dateCreated', 'type' => 'string', 'limit' => '75'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '11'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * INSPECTION TABLE */

        /*         * INSPECTION IMAGES TABLE */
        $table_name = 'tbl_inspection';
        $primary_key = 'inspectionId';

        $fields = [
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'visual', 'type' => 'string', 'limit' => '255'],
            ['name' => 'sampling', 'type' => 'string', 'limit' => '255'],
            ['name' => 'deliveryBatch', 'type' => 'string', 'limit' => '255'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '11'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * INSPECTION IMAGES TABLE */

        /*         * SAMPLING TABLE */
        $table_name = 'tbl_sampling';
        $primary_key = 'samplingId';

        $fields = [
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'seedTag', 'type' => 'string', 'limit' => '75'],
            ['name' => 'bagWeight', 'type' => 'string', 'limit' => '15'],
            ['name' => 'dateSampled', 'type' => 'string', 'limit' => '50'],
            ['name' => 'send', 'type' => 'integer', 'limit' => '11'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * SAMPLING TABLE */

        /*         * SCHEDULE TABLE */
        $table_name = 'tbl_schedule';
        $primary_key = 'scheduleId';

        $fields = [
            ['name' => 'userId', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'ticketNumber', 'type' => 'string', 'limit' => '100'],
            ['name' => 'batchTicketNumber', 'type' => 'string', 'limit' => '75'],
            ['name' => 'inspectionDate', 'type' => 'date'],
            ['name' => 'pmo_remarks', 'type' => 'text'],
            ['name' => 'assignedBy', 'type' => 'string', 'limit' => '100'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * SCHEDULE TABLE */

        /*         * TRESHHOLD TABLE */
        $table_name = 'tbl_threshold';
        $primary_key = 'thresholdId';

        $fields = [
            ['name' => 'thresholdName', 'type' => 'string', 'limit' => '50'],
            ['name' => 'thresholdVal', 'type' => 'string', 'limit' => '50'],
            ['name' => 'samplingPercentage', 'type' => 'string', 'limit' => '50'],
            ['name' => 'isActive', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'dateCreated', 'type' => 'string', 'limit' => '50'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * TRESHHOLD TABLE */
    }

    public function createDatabaseCOOP($database_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $database_name";
        DB::statement($query);

        \Config::set('database.connections.reports_db.database', $database_name);
        DB::purge('reports_db');

        /*         * TBL_COMMITMENT TABLE */
        $table_name = 'tbl_commitment';
        $primary_key = 'id';

        $fields = [
            ['name' => 'coopID', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'commitment_value', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'commitment_variety', 'type' => 'string', 'limit' => '75'],
            ['name' => 'addedBy', 'type' => 'string', 'limit' => '55'],
            ['name' => 'date_added', 'type' => 'timestamp'],
            ['name' => 'date_updated', 'type' => 'datetime'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * TBL_COMMITMENT TABLE */

        /*         * TBL_COOPERATIVES TABLE */
        $table_name = 'tbl_cooperatives';
        $primary_key = 'coopId';

        $fields = [
            ['name' => 'coopName', 'type' => 'string', 'limit' => '175'],
            ['name' => 'acronym', 'type' => 'string', 'limit' => '50'],
            ['name' => 'regionId', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'provinceId', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'municipalityId', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'isActive', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'accreditation_no', 'type' => 'string', 'limit' => '75'],
            ['name' => 'current_moa', 'type' => 'string', 'limit' => '50'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * TBL_COOPERATIVES TABLE */

        /*         * TBL_COOPERATIVES TABLE */
        $table_name = 'tbl_total_commitment';
        $primary_key = 'id';

        $fields = [
            ['name' => 'coopID', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'total_value', 'type' => 'integer', 'limit' => '11'],
            ['name' => 'date_created', 'type' => 'timestamp'],
            ['name' => 'moa_number', 'type' => 'string', 'limit' => '30'],
        ];
        $this->createTable($table_name, $fields, $primary_key);
        /*         * TBL_COOPERATIVES TABLE */
    }

    public function archive_deliveryInspection($database_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $database_name";
        DB::statement($query);

        $query = "RENAME TABLE rcep_delivery_inspection.iar_print_logs TO $database_name.iar_print_logs,
                               rcep_delivery_inspection.lib_dropoff_point TO $database_name.lib_dropoff_point,
                               rcep_delivery_inspection.lib_dropoff_point2 TO $database_name.lib_dropoff_point2,
                               rcep_delivery_inspection.tbl_actual_delivery TO $database_name.tbl_actual_delivery,
                               rcep_delivery_inspection.tbl_delivery TO $database_name.tbl_delivery,
                               rcep_delivery_inspection.tbl_delivery_status TO $database_name.tbl_delivery_status,
                               rcep_delivery_inspection.tbl_inspection TO $database_name.tbl_inspection,
                               rcep_delivery_inspection.tbl_inspection2 TO $database_name.tbl_inspection2,
                               rcep_delivery_inspection.tbl_inspection_images TO $database_name.tbl_inspection_images,
                               rcep_delivery_inspection.tbl_sampling TO $database_name.tbl_sampling,
                               rcep_delivery_inspection.tbl_sampling2 TO $database_name.tbl_sampling2,
                               rcep_delivery_inspection.tbl_schedule TO $database_name.tbl_schedule,
                               rcep_delivery_inspection.tbl_schedule_old TO $database_name.tbl_schedule_old,
                               rcep_delivery_inspection.tbl_threshold TO $database_name.tbl_threshold";
        DB::statement($query);

        $query = "DROP DATABASE rcep_delivery_inspection";
        DB::statement($query);
        $this->createDatabaseDeliveryInspection("rcep_delivery_inspection");
    }

    public function archive_PRVTables($database_name, $previous_db_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $database_name";
        DB::statement($query);

        $query = "RENAME TABLE $previous_db_name.area_history TO $database_name.area_history,
                               $previous_db_name.farmer_profile TO $database_name.farmer_profile,
                               $previous_db_name.pending_release TO $database_name.pending_release,
                               $previous_db_name.performance TO $database_name.performance,
                               $previous_db_name.released TO $database_name.released";
        DB::statement($query);

        $query = "DROP DATABASE $previous_db_name";
        DB::statement($query);
        $this->createDatabasePRV($previous_db_name);
    }

    public function archive_SeedCooperatives($database_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $database_name";
        DB::statement($query);

        $query = "RENAME TABLE rcep_seed_cooperatives.tbl_commitment TO $database_name.tbl_commitment,
                               rcep_seed_cooperatives.tbl_cooperatives TO $database_name.tbl_cooperatives,
                               rcep_seed_cooperatives.tbl_total_commitment TO $database_name.tbl_total_commitment";
        DB::statement($query);

        $query = "DROP DATABASE rcep_seed_cooperatives";
        DB::statement($query);
        $this->createDatabaseCOOP("rcep_seed_cooperatives");
    }

    public function updateActiveSeason(Request $request) {
        //get name of selected active season
        $season_name = DB::connection('mysql')->table('lib_seasons')
                ->where('is_active', 1)
                ->first();
        $database_prefix = $season_name->season_prefix;

        //generate databases for new season (seed coop data)
        $database_name = $database_prefix . "_rcep_seed_cooperatives";
        $this->archive_SeedCooperatives($database_name);

        //generate databases for new season (prv data)

        $season_data = DB::connection('mysql')->table('lib_seasons_data')->limit(5)->get();
        foreach ($season_data as $s_data) {
            $database_name = $database_prefix . "_prv_" . $s_data->prv_code;
            $previous_db_name = $GLOBALS['season_prefix']."prv_" . $s_data->prv_code;
            //$this->createDatabasePRV($database_name);
            $this->archive_PRVTables($database_name, $previous_db_name);
        }

        //generate databases for new season (delivery inspection)
        $database_name = $database_prefix . "_rcep_delivery_inspecetion";
        $this->archive_deliveryInspection($database_name);

        //Session::flash("success", "You have successfully selected (" .$season_name->season_name. ") as the active season for the upcoming seed distribution.");
        //return redirect()->route('system.settings.seasonal');
    }

    /*     * QR CODE SETTINGS */

    public function qr_home() {
        $qr_code_max = DB::connection('mysql')->table('lib_settings')->where('setting_code', 'QRMAX')->value('setting_value');
        $qr_logs = DB::connection('mysql')->table('lib_logs')->where('category', 'QRMAX')->get();
        return view('settings.qrcode.home')
                        ->with('qr_code_max', $qr_code_max)
                        ->with('qr_logs', $qr_logs);
    }

    public function qr_update(Request $request) {
        DB::connection('mysql')->table('lib_settings')
                ->where('setting_code', '=', 'QRMAX')
                ->update(['setting_value' => $request->qr_value]);

        DB::connection('mysql')->table('lib_logs')
                ->insert([
                    'category' => 'QRMAX',
                    'description' => 'Update of Max QR Codes per batch to ' . $request->qr_value . ' codes.',
                    'author' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        Session::flash("success", "you have successfully udpated the setting.");
        return redirect()->route('system.settings.qrcode');
    }

    /*     * QR CODE SETTINGS */

    /*     * DISTRIBUTION SETTINGS */

    public function distribution_home() {
        return view('settings.distribution.home');
    }

    public function distribution_tbl(Request $request) {
        return Datatables::of(DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                                ->select('prv_dropoff_id', 'region', 'province', 'municipality', 'dropOffPoint', 'date_created')
                                ->orderBy('dateUpdated', 'desc')
                                ->groupBy('prv_dropoff_id')
                        )
                        ->addColumn('action', function($row) {
                            $drop_off_variables = DB::connection('mysql')->table('lib_dropoff_settings')
                                    ->where('dropoffID', '=', $row->prv_dropoff_id)
                                    ->first();
                            if (count($drop_off_variables) > 0) {
                                return '<a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#update_variables_modal" data-id="' . $row->prv_dropoff_id . '" data-lgu="' . $drop_off_variables->lgu_limit . '"><i class="fa fa-eye"></i> View Variables</a>';
                            } else {
                                return '<a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#add_variables_modal" data-id="' . $row->prv_dropoff_id . '"><i class="fa fa-pencil"></i> Set Variables</a>';
                            }
                        })
                        ->make(true);
    }

    public function distribution_add_variables(Request $request) {
        DB::connection('mysql')->table('lib_dropoff_settings')
                ->insert([
                    'dropoffID' => $request->dropoffID,
                    'lgu_limit' => $request->lgu_limit,
                    'date_recorded' => date("Y-m-d H:i:s"),
                    'date_updated' => date("Y-m-d H:i:s"),
        ]);

        DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', '=', $request->dropoffID)
                ->update(['dateUpdated' => date("Y-m-d H:i:s")]);

        DB::connection('mysql')->table('lib_logs')
                ->insert([
                    'category' => 'DISTRIBUTION',
                    'description' => 'Creation of LGU Limit for the dropoff (' . $request->dropoffID . ')',
                    'author' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        Session::flash("success", "You have successfully updated a dropoff point");
        return redirect()->route('system.settings.distribution');
    }

    public function distribution_update_variables(Request $request) {
        DB::connection('mysql')->table('lib_dropoff_settings')
                ->where('dropoffID', '=', $request->dropoffID_update)
                ->update(
                        [
                            'date_updated' => date("Y-m-d H:i:s"),
                            'lgu_limit' => $request->lgu_limit_update
                        ]
        );

        DB::connection('mysql')->table('lib_logs')
                ->insert([
                    'category' => 'DISTRIBUTION',
                    'description' => 'Updated LGU Limit for the dropoff (' . $request->dropoffID_update . ')',
                    'author' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', '=', $request->dropoffID_update)
                ->update(['dateUpdated' => date("Y-m-d H:i:s")]);

        Session::flash("success", "You have successfully updated a dropoff point");
        return redirect()->route('system.settings.distribution');
    }

    /*     * DISTRIBUTION SETTINGS */
}
