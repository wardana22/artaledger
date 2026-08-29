<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserAndUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Run prerequisite seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            UnitSeeder::class,
        ]);

        $roles = [
            'admin' => Role::where('name', 'Super Admin')->first(),
            'manager' => Role::where('name', 'Akuntan / Finance Manager')->first(),
            'staff' => Role::where('name', 'Staf Keuangan')->first(),
            'auditor' => Role::where('name', 'Auditor / Viewer')->first(),
        ];

        $unitsByCode = Unit::all()->keyBy('code');

        // 2. Create Super Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@artaledger.com'],
            [
                'name' => 'Dev Admin (Super Admin)',
                'password' => Hash::make('password'),
            ]
        );
        if ($roles['admin']) {
            $admin->syncRoles([$roles['admin']]);
        }

        // 3. Create Manager User (Manajer Keuangan)
        $manager = User::updateOrCreate(
            ['email' => 'manager@artaledger.com'],
            [
                'name' => 'Bambang Irawan (Manajer Keuangan)',
                'password' => Hash::make('password'),
            ]
        );
        if ($roles['manager']) {
            $manager->syncRoles([$roles['manager']]);
        }
        // Manager manages all units
        $manager->units()->sync($unitsByCode->pluck('id')->toArray());

        // 4. Create Auditor User (Auditor Internal)
        $auditor = User::updateOrCreate(
            ['email' => 'auditor@artaledger.com'],
            [
                'name' => 'Dewi Sartika (Auditor Internal)',
                'password' => Hash::make('password'),
            ]
        );
        if ($roles['auditor']) {
            $auditor->syncRoles([$roles['auditor']]);
        }
        // Auditor has global read access to all units
        $auditor->units()->sync($unitsByCode->pluck('id')->toArray());

        // 5. Create 11 Finance Staff Users (Staf Keuangan per Unit)
        $staffSeedData = [
            ['email' => 'staf.kp@artaledger.com', 'name' => 'Andi Wijaya (Staf KP)', 'unit' => 'KP'],
            ['email' => 'staf.rst@artaledger.com', 'name' => 'Siti Nurhaliza (Staf RST)', 'unit' => 'RST'],
            ['email' => 'staf.ku@artaledger.com', 'name' => 'Ahmad Fauzi (Staf KU)', 'unit' => 'KU'],
            ['email' => 'staf.kpn@artaledger.com', 'name' => 'Lestari Putri (Staf KPN)', 'unit' => 'KPN'],
            ['email' => 'staf.lda@artaledger.com', 'name' => 'Eko Prasetyo (Staf LDA)', 'unit' => 'LDA'],
            ['email' => 'staf.sgh@artaledger.com', 'name' => 'Rina Amalia (Staf SGH)', 'unit' => 'SGH'],
            ['email' => 'staf.sgo@artaledger.com', 'name' => 'Hendra Gunawan (Staf SGO)', 'unit' => 'SGO'],
            ['email' => 'staf.trt@artaledger.com', 'name' => 'Maya Indah (Staf TRT)', 'unit' => 'TRT'],
            ['email' => 'staf.ksk@artaledger.com', 'name' => 'Bambang Susilo (Staf KSK)', 'unit' => 'KSK'],
            ['email' => 'staf.sli@artaledger.com', 'name' => 'Nita Kurnia (Staf SLI)', 'unit' => 'SLI'],
            ['email' => 'staf.sro@artaledger.com', 'name' => 'Rizky Hidayat (Staf SRO)', 'unit' => 'SRO'],
        ];

        foreach ($staffSeedData as $data) {
            $staffUser = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            if ($roles['staff']) {
                $staffUser->syncRoles([$roles['staff']]);
            }

            if (isset($unitsByCode[$data['unit']])) {
                $staffUser->units()->sync([$unitsByCode[$data['unit']]->id]);
            }
        }
    }
}
