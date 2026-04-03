<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@tripaz.az'],
            [
                'name'              => 'Admin User',
                'email'             => 'admin@tripaz.az',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // 2. Eight host/owner accounts
        $owners = [
            ['name' => 'Əli Həsənov',   'email' => 'owner1@tripaz.az'],
            ['name' => 'Nigar Məmmədli', 'email' => 'owner2@tripaz.az'],
            ['name' => 'Rauf Quliyev',  'email' => 'owner3@tripaz.az'],
            ['name' => 'Aynur İsmayıl', 'email' => 'owner4@tripaz.az'],
            ['name' => 'Tural Əliyev',  'email' => 'owner5@tripaz.az'],
            ['name' => 'Leyla Babayeva','email' => 'owner6@tripaz.az'],
            ['name' => 'Farid Nəsirov', 'email' => 'owner7@tripaz.az'],
            ['name' => 'Aysel Hüseynli','email' => 'owner8@tripaz.az'],
        ];

        foreach ($owners as $ownerData) {
            $owner = User::firstOrCreate(
                ['email' => $ownerData['email']],
                [
                    'name'              => $ownerData['name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $owner->syncRoles(['host']);
        }

        // 3. 20 regular users
        $regularUsers = [
            ['name' => 'Kənan Rəsulov',    'email' => 'user1@tripaz.az'],
            ['name' => 'Günay Əhmədova',   'email' => 'user2@tripaz.az'],
            ['name' => 'Orxan Babayev',    'email' => 'user3@tripaz.az'],
            ['name' => 'Sevinc Vəliyeva',  'email' => 'user4@tripaz.az'],
            ['name' => 'Murad Cəfərov',    'email' => 'user5@tripaz.az'],
            ['name' => 'Xədicə Hüseynova', 'email' => 'user6@tripaz.az'],
            ['name' => 'Bəhruz Nəcəfov',   'email' => 'user7@tripaz.az'],
            ['name' => 'Könül Şirinova',   'email' => 'user8@tripaz.az'],
            ['name' => 'Zaur Əliyev',      'email' => 'user9@tripaz.az'],
            ['name' => 'Lalə Qasımova',    'email' => 'user10@tripaz.az'],
            ['name' => 'Elnur Həsənli',    'email' => 'user11@tripaz.az'],
            ['name' => 'Nərmin Sultanova', 'email' => 'user12@tripaz.az'],
            ['name' => 'Rəşad Kazımov',    'email' => 'user13@tripaz.az'],
            ['name' => 'Günel Muradova',   'email' => 'user14@tripaz.az'],
            ['name' => 'Pərviz Məlikov',   'email' => 'user15@tripaz.az'],
            ['name' => 'Şəbnəm Quliyeva',  'email' => 'user16@tripaz.az'],
            ['name' => 'Cavid Abbasov',    'email' => 'user17@tripaz.az'],
            ['name' => 'Zəhra İsmayılova', 'email' => 'user18@tripaz.az'],
            ['name' => 'Namiq Hüseynov',   'email' => 'user19@tripaz.az'],
            ['name' => 'Aytən Əliyeva',    'email' => 'user20@tripaz.az'],
        ];

        foreach ($regularUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['user']);
        }
    }
}
