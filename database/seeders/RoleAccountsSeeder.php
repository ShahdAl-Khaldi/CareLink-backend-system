<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\ReceptionistProfile;
use App\Models\LabProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // الأدمن وحده active من البداية عشان يقدر يفوت ويفعّل الباقي
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'national_id' => '100000001',
                'role' => 'admin',
                'status' => true,
            ]
        );

        $doctor = User::firstOrCreate(
            ['email' => 'doctor@gmail.com'],
            [
                'name' => 'Dr. Demo',
                'password' => Hash::make('12345678'),
                'national_id' => '100000002',
                'role' => 'doctor',
                'status' => false,
            ]
        );

        DoctorProfile::firstOrCreate(
            ['user_id' => $doctor->id],
            [
                'specialty' => 'باطنية',
                'years_of_experience' => 5,
                'status' => 'inactive',
                'gender' => 'male',
            ]
        );

        User::firstOrCreate(
            ['email' => 'patient@gmail.com'],
            [
                'name' => 'Patient Demo',
                'password' => Hash::make('12345678'),
                'national_id' => '100000003',
                'role' => 'patient',
                'status' => false,
            ]
        );

        $reception = User::firstOrCreate(
            ['email' => 'reception@gmail.com'],
            [
                'name' => 'Reception Demo',
                'password' => Hash::make('12345678'),
                'national_id' => '100000004',
                'role' => 'reception',
                'status' => false,
            ]
        );
        ReceptionistProfile::firstOrCreate(['user_id' => $reception->id]);

        $lab = User::firstOrCreate(
            ['email' => 'lab@gmail.com'],
            [
                'name' => 'Lab Demo',
                'password' => Hash::make('12345678'),
                'national_id' => '100000005',
                'role' => 'lab',
                'status' => false,
            ]
        );
        LabProfile::firstOrCreate(['user_id' => $lab->id]);
    }
}