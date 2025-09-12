<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IARUtil extends Model
{
    protected $table = 'lib_signatories_person';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'honorific_prefix',
        'complete_name',
        'post_nominal',
        'cell_number',
        'email',
        'sex',
        'civil_status',
        'role_id',
        'position_id',
        'is_right'
    ];

    public static function getPeopleDetails()
    {
        $prefix = $GLOBALS['season_prefix'] . 'sdms_db_dev.';

        $rows = DB::table($prefix . 'lib_signatories_person as per')
            ->leftJoin($prefix . 'lib_signatories_person_provinces as pp', 'pp.person_id', '=', 'per.id')
            ->leftJoin($prefix . 'lib_signatories_position as pos', 'per.position_id', '=', 'pos.id')
            ->leftJoin($prefix . 'lib_provinces as prov', function ($join) {
                $join->on(DB::raw('prov.provCode COLLATE utf8_unicode_ci'), '=', DB::raw('pp.provCode COLLATE utf8_unicode_ci'))
                    ->whereNotIn('prov.id', [69]);
            })
            ->leftJoin($prefix . 'lib_regions as r', DB::raw('r.regCode COLLATE utf8_unicode_ci'), '=', DB::raw('prov.regCode COLLATE utf8_unicode_ci'))
            ->select(
                'per.id as person_id',
                'per.honorific_prefix',
                'per.complete_name',
                'per.post_nominal',
                'per.sex',
                'per.cell_number',
                'per.email',
                'per.is_right',
                'prov.provCode',
                'prov.provDesc',
                'r.regCode',
                'r.regDesc',
                'pos.name as position',
                'pos.id as position_id'
            )
            ->orderBy('per.id')
            ->orderBy('per.is_right', 'asc')
            ->orderBy('prov.provDesc', 'asc')
            ->get();
        $people = [];
        
        foreach ($rows as $row) {
            if (!isset($people[$row->person_id])) {
                $people[$row->person_id] = [
                    'id'               => $row->person_id,
                    'honorific_prefix' => $row->honorific_prefix,
                    'complete_name'    => $row->complete_name,
                    'post_nominal'     => $row->post_nominal,
                    'sex'              => $row->sex,
                    'position_id'      => $row->position_id,
                    'position'         => $row->position,
                    'cell_number'      => $row->cell_number,
                    'email'            => $row->email,
                    'is_right'         => $row->is_right,
                    'provinces'        => []
                ];
            }

            // Only add province if it exists
            if (!empty($row->provCode)) {
                $people[$row->person_id]['provinces'][] = [
                    'provCode' => $row->provCode,
                    'provDesc' => $row->provDesc,
                ];
            }
        }

        return array_values($people);
    }

    public static function getRoles()
    {
        return DB::select("
            SELECT id as role_id, 
                name as role_name
            FROM lib_signatories_role
        ");
    }

    public static function getPositions()
    {
        return DB::table('lib_signatories_position')->select('id as position_id', 'name as position_name')
        ->orderby('position_name','asc')
        ->get();
    }

    /**
     * Save or update a person and log the action.
     */
    public static function savePersonDetails($data, $id = null)
    {
        // Log::info('savePersonDetails called', ['data' => $data, 'id' => $id]);
        return DB::transaction(function () use ($data, $id) {
            try {
                $provinceListLog = "Province changes:"; // for logging
                // --- Position Handling ---
                if (!empty($data['position_name']) && empty($data['position_id'])) {
                    $positionName = strtoupper(trim($data['position_name']));
                    $existingPosition = DB::table('lib_signatories_position')
                        ->whereRaw('UPPER(name) = ?', [$positionName])
                        ->first();

                    if ($existingPosition) {
                        $data['position_id'] = $existingPosition->id;
                        // Log::info("Existing position found: {$positionName} (ID: {$existingPosition->id})");
                    } else {
                        $data['position_id'] = DB::table('lib_signatories_position')->insertGetId([
                            'name' => $positionName,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                        // Log::info("New position created: {$positionName} (ID: {$data['position_id']})");
                    }
                }
                unset($data['position_name']);
                // --- Create or Update Person ---
                if ($id) {
                    $person = self::findOrFail($id);
                    $original = $person->getOriginal();
                    $person->update($data);
                    $changes = array_diff_assoc($person->getAttributes(), $original);
                    $action = 'updated';
                } else {
                    $person = self::create($data);
                    $changes = $data;
                    $action = 'created';
                }

                // --- Provinces Handling (Available-only) ---
                if (array_key_exists('regions_provinces', $data) && $person) {
                    $regionsProvinces = json_decode($data['regions_provinces'], true);

                    if (is_array($regionsProvinces)) {
                        // Normalize incoming codes
                        $provinceCodes = [];
                        foreach ($regionsProvinces as $item) {
                            if (!is_string($item) && !is_numeric($item)) continue;
                            $item = trim((string)$item);
                            if ($item === '') continue;
                            $code = strpos($item, 'province:') === 0 ? substr($item, strlen('province:')) : $item;
                            if ($code !== '') $provinceCodes[] = trim($code);
                        }
                        $provinceCodes = array_values(array_unique($provinceCodes));

                        // --- NEW: filter out provinces already owned by active users on same side ---
                        $activeOwned = DB::table('lib_signatories_person_provinces as pp')
                            ->join('lib_signatories_person as p', 'pp.person_id', '=', 'p.id')
                            ->where('p.is_right', $data['is_right'])
                            ->where('p.id', '!=', $person->id)
                            ->pluck('pp.provCode');

                        $provinceCodes = array_diff($provinceCodes, $activeOwned);
                        // --- END filter ---

                        // Current provinces in DB
                        $currentProvinces = collect(
                            DB::table('lib_signatories_person_provinces')
                                ->where('person_id', $person->id)
                                ->pluck('provCode')
                        )->all();
                        
                        if (empty($provinceCodes)) {
                            // Remove all if none remaining
                            if (!empty($currentProvinces)) {
                                DB::table('lib_signatories_person_provinces')
                                    ->where('person_id', $person->id)
                                    ->delete();
                                // Log::info("Removed ALL provinces for person {$person->id}: " . implode(', ', $currentProvinces));
                                $provinceListLog .= " Removed ALL - " . implode(', ', $currentProvinces);
                            }
                        } else {
                            // Delete removed provinces
                            $toDelete = array_diff($currentProvinces, $provinceCodes);
                            if (!empty($toDelete)) {
                                DB::table('lib_signatories_person_provinces')
                                    ->where('person_id', $person->id)
                                    ->whereIn('provCode', $toDelete)
                                    ->delete();
                                // Log::info("Removed provinces for person {$person->id}: " . implode(', ', $toDelete));
                                $provinceListLog .= " Removed - " . implode(', ', $toDelete);
                            }

                            // Insert new provinces
                            $toAdd = array_diff($provinceCodes, $currentProvinces);
                            if (!empty($toAdd)) {
                                $insertProvinces = [];
                                foreach ($toAdd as $provCode) {
                                    $insertProvinces[] = [
                                        'person_id' => $person->id,
                                        'provCode' => $provCode,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ];
                                }
                                DB::table('lib_signatories_person_provinces')->insert($insertProvinces);
                                $provinceListLog .= " Added - "  . implode(', ', $toAdd);
                                // Log::info("Added provinces for person {$person->id}: " . implode(', ', $toAdd));
                            }
                        }
                    }
                }

                // --- Logging ---
                try {
                    DB::table('lib_logs')->insert([
                        'category'    => 'SIGNATORY',
                        'description' => "(ID: {$person->id}) Person: '{$person->complete_name}' {$action}. {$provinceListLog}",
                        'author'      => Auth::check()
                            ? (Auth::user()->username 
                                ? Auth::user()->username 
                                : (Auth::user()->name ? Auth::user()->name : 'unknown'))
                            : 'system',
                        'ip_address'  => request()->ip() ? request()->ip() : '0.0.0.0',
                    ]);

                } catch (\Exception $e) {
                    Log::error("Failed to insert log for person ID {$person->id}: " . $e->getMessage());
                }

                return $person;
            } catch (\Exception $e) {
                Log::error("Error saving person details: " . $e->getMessage());
                throw $e;
            }
        });
    }

    public static function getRegionsWithProvinces()
    {
        $regions = DB::table('lib_regions')
            ->select('regCode', 'regDesc', 'order')
            ->orderBy('order')
            ->where('regCode', '<>', 13)
            ->orderBy('order')
            ->get();

        $provinces = DB::table('lib_provinces as a')
            ->select('a.provCode', 'a.provDesc', 'a.regCode')
            ->where('a.id', '<>', 69) // exclude special case
            ->orderBy('a.provDesc')
            ->get();

        $nested = [];

        //ws2025_rcep_delivery_inspection.lib_prv

        // Initialize each region with empty province list
        foreach ($regions as $region) {
            $nested[$region->regCode] = [
                'region_code' => $region->regCode,
                'region_name' => $region->regDesc,
                'provinces' => []
            ];
        }

        // Assign provinces to their regions
        foreach ($provinces as $province) {
            if (isset($nested[$province->regCode])) {
                $nested[$province->regCode]['provinces'][] = [
                    'provCode' => $province->provCode,
                    'provDesc' => $province->provDesc
                ];
            }
        }

        

        // Return as array of regions with provinces
        // dd($nested);
        return array_values($nested);
    }

    public static function deletePersonWithProvinces($id)
    {   
        
        return DB::transaction(function () use ($id) {
            // Get person's name before deletion
            $person = DB::table('lib_signatories_person')
                ->select('complete_name')
                ->where('id', $id)
                ->first();

            $provinceList = DB::table('lib_signatories_person_provinces')
                ->where('person_id', $id)
                ->pluck('provCode'); // returns a collection of just the provCode values

            $provinceListLog = implode(', ', $provinceList);
                
            $complete_name = $person ? $person->complete_name : '(Unknown)';

            $action = 'delete';
            $changes = [
                'person_id'  => $id,
                'complete_name'  => $complete_name,
                'deleted_at' => Carbon::now()->toDateTimeString()
            ];

            try {
                DB::table('lib_logs')->insert([
                    'category'    => 'SIGNATORY',
                    'description' => "(ID: {$id}) Person: '{$complete_name}' DELETED. Province changes: Removed {$provinceListLog}",
                    'author'      => Auth::check()
                        ? (Auth::user()->username 
                            ? Auth::user()->username 
                            : (Auth::user()->name ? Auth::user()->name : 'unknown'))
                        : 'system',
                    'ip_address'  => request()->ip() ? request()->ip() : '0.0.0.0',
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to insert log for delete person ID {$id}: " . $e->getMessage());
            }

            // Delete related provinces
            DB::table('lib_signatories_person_provinces')
                ->where('person_id', $id)
                ->delete();

            // Delete the person record
            DB::table('lib_signatories_person')
                ->where('id', $id)
                ->delete();

            // Laravel log confirmation
            // Log::info("Person '{$complete_name}' (ID: {$id}) deleted along with related provinces and log entry.");
        });
    }

}
