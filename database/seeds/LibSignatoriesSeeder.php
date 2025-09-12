<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LibSignatoriesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('lib_signatories_person_provinces')->truncate();
        DB::table('lib_signatories_person')->truncate();
        DB::table('lib_signatories_position')->truncate();
        DB::table('lib_signatories_province')->truncate();
        DB::table('lib_signatories_role')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Roles
        $roles = [
            'rcef',
            'gass',
            // add more roles here if needed
        ];

        foreach ($roles as $role) {
            DB::table('lib_signatories_role')->insert([
                'name' => $role,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 2. Positions
        $positions = [
            'CES PDO',
            'CES Regional Coordinator',
            'Administrative Officer II',
            'Accountant II',
            'Administrative Officer III',
            // add more positions here if needed
        ];

        foreach ($positions as $pos) {
            DB::table('lib_signatories_position')->insert([
                'name' => $pos,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 3. Persons (signatories) with role and position names
        $signatories = [
            [
                'name' => 'Engr. Lorenzo Lopez Jr.',
                'role' => 'rcef',
                'position' => 'CES PDO',
                'provinces' => ["NUEVA ECIJA", "PAMPANGA", "TARLAC"],
            ],
            [
                'name' => 'Jhoemar Dela Cruz',
                'role' => 'rcef',
                'position' => 'CES Regional Coordinator',
                'provinces' => ["AURORA", "BATAAN", "BULACAN", "ZAMBALES"],
            ],
            [
                'name' => 'Maria Aster Joy A. Garcia',
                'role' => 'gass',
                'position' => 'Administrative Officer II',
                'provinces' => ["NUEVA ECIJA", "QUIRINO", "NUEVA VIZCAYA"],
            ],
            [
                'name' => 'Kristine M. Paggao',
                'role' => 'gass',
                'position' => 'Accountant II',
                'provinces' => ["BENGUET", "IFUGAO"],
            ],
            // add more signatories here as needed...
        ];

        foreach ($signatories as $signatory) {
            // Find role id
            $roleId = DB::table('lib_signatories_role')->where('name', $signatory['role'])->value('id');
            // Find position id
            $positionId = DB::table('lib_signatories_position')->where('name', $signatory['position'])->value('id');

            // Parse full name into parts (very basic splitting, adjust if you want)
            $nameParts = explode(' ', $signatory['name']);
            $firstName = array_shift($nameParts);
            $lastName = array_pop($nameParts);
            $middleName = implode(' ', $nameParts);

            $personId = DB::table('lib_signatories_person')->insertGetId([
                'honorific_prefix' => null,
                'first_name' => $firstName,
                'middle_name' => $middleName ?: null,
                'last_name' => $lastName,
                'extension_name' => null,
                'post_nominal' => null,
                'sex' => null,
                'civil_status' => null,
                'role_id' => $roleId,
                'position_id' => $positionId,
                'coverage_group' => null,
                'aor' => null,
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Link provinces by province name to the person
            foreach ($signatory['provinces'] as $provName) {
                // Get provCode by provDesc (assuming lib_signatories_province.provDesc)
                $provCode = DB::table('lib_signatories_province')
                    ->whereRaw('UPPER(TRIM(provDesc)) = ?', [strtoupper(trim($provName))])
                    ->value('provCode');

                if ($provCode) {
                    DB::table('lib_signatories_person_provinces')->insert([
                        'person_id' => $personId,
                        'provCode' => $provCode,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
