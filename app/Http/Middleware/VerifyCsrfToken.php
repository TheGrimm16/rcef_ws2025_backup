<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Closure;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
		'api/moet/receive/datasets',
		'connect/get_dropoffpoints',
		'connect/get_coops',
		'sync/sub_server/area_history',
		'sync/sub_server/farmer_profile',
		'sync/sub_server/other_info',
		'sync/sub_server/pending_release',
		'sync/sub_server/performance',
		'sync/sub_server/released',
		'api/get-seedtag/{token}',
		'transfer/dropoff',
		'farmer_profile/get_photo',
		'api/get-seedtag_api/{season}/{year}/{token}',
		'api/fetch/other_info/{code}',
		'api/fetch/farmer_profile/{code}/{start_index}',

		//PRE REG API FOR DISTRI APP
		'api/pre_reg/fetch/farmer_profile/{code}',


		'api/transfer/insert',
		'api/far',
		'api/get/commitment/regional',
		'api/insert/dop',
		'api/insert/delivery/schedule',
		'api.bdd.sg_list/{api_key}',
		'api/buffer/login',
		'api/fetch/buffer/coop/{api_key}/{username}',
		'api/fetch/buffer/dop/{api_key}/{coop_id}',
		'api/fetch/buffer/batches/{api_key}/{prv_dropoff_id}',
		'api/fetch/buffer/batchInfo/{api_key}/{batchTicketNumber}',
		'api/fetch/buffer/rla/{api_key}/{batchTicketNumber}/{seedvariety}',
		'api/insert/breakdown',
		'api/insert/download',
		'api/municipality/yield',
		'api/dop/lib_prv',
		'pi/dop/coopList',
		'app/api/rla_count',
		'app/api/regional/commitments/{api_key}/{coop_id}',
		'app/api/sg_account/{api_key}/{username}',
		'app/api/login',
		'analytics/execute_report/variety',
		'farmer/profile/contact/nationwide',
		
		// E-BINHI APP
		'ebinhi_app/coop_monitoring/{coop_number}/{date_from}/{date_to}',
		'ebinhi_app/coop_inventory/{coop_number}/{current_date}',

		// for SED callers
		'sed/get/farmer/data',
		'sed/get/farmer/data/callback',
		'api/paymaya/fetch/data',
		'api/paymaya/fetch/editable',

		//MOET
		'moet_app/registration',
		'moet_app/login',
		'moet_app/data_request',
		'moet_app/check_db/{db}/{api}',
		
		'qr_extension',
		'report/gad/farmer/process',

		/* joe */
		'finance/api/rla_details',		
		
		//PRE REG
		'api/pre_reg/check_name/{rsbsa}/{fname}/{lname}/{api_key}',
		'api/all_in_one_login',
		'api/pre_reg/insert_data',
		'api/pre_reg/get_qrCode',
		'api/pre_reg/new_farmer/',
		'api/pre_reg/trail/{api_key}/{prv_db}/{rsbsa}',
		'api/pre_reg/prvs',
		'api/pre_reg/v2/farmer',
		'api/pre_reg/v2/insert',
		//Survey
		'ebinhi/survey',
		'ebinhi/survey/questions',
		'ebinhi/survey/answer',
		'ebinhi/survey/check_id',

		//MNE
		'get_mne_excel',
		'generate_mne_excel',

		'ebinhi_app/coop_inventory/',
		'api/calendar/events/{api}/{type}',
		'api/bdd/coops/{api_key}',
		'gad/download/excel/{type}',
		'rcef/unmatched/{prv}',
		'pull_unique_list/{prv}/{api_key}',

		//RCEF ID
		'generate/pdf/rcef_id/{api_key}/{prv}',

		//CSS
		'api/css/ebinhi/{api_key}/{search_value}',
		'api/css/conv/{api_key}/{province}/{municipality}/{search_value}',
		'api/css/conv/{province}/{type}',
		'api/insert/css',
		'api/insert/css/nrpInsert',
		'api/sg_list/{api}/{accreditation}',

		//RCEF-IMS
		'rcef_ims_api/getSelectedData',
		'rcef_ims_api/getProvinces',
		'rcef_ims_api/getMuni',
		'rcef_ims_api/import_file_metrics',
		'rcef_ims_api/get/gad_data/{season}/{prv}',
		'rcef_ims_api/login',
		'rcef_ims_api/checkCredibility',
		'mech/api/page/subCategory',
		'rcef_ims_api/delete_metrics',
		'rcef_ims_api/getCreditDisplayData',
		'rcef_ims_api/receiveCredsData',
		'rcef_ims_api/receiveExtData',
		
		//TRAVIS
		'/api/travis/sync-data',
		'/api/travis/sync-attachments',
		'/api/travis/delete-attachments',
		'/api/travis/sync-transpoOR',
		'/api/travis/sync-accommOR',
		'/api/travis/delete-OR',

		//FarmerFinder
		'api/ff/syncKPdata',
		'api/ff/iarLogging',
		'api/ff/submitReceiver',
		'api/ff/submitPrcCsh',
		'ebinhi_app/coop_inventory/debug',

		//Coop App
		'/api/bm/updateCommitment',
		'/api/bm/addCommitment',
    ];
}