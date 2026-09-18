<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Michael',
                'last_name' => 'Chang',
                'email' => 'michael@gmail.com',
                'role' => 'Dispatcher',
            ],
            [
                'first_name' => 'Jessica',
                'last_name' => 'Williams',
                'email' => 'jessica@gmail.com',
                'role' => 'Sales Agent',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Miller',
                'email' => 'david@gmail.com',
                'role' => 'Dispatcher',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily@gmail.com',
                'role' => 'Sales Agent',
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Taylor',
                'email' => 'robert@gmail.com',
                'role' => 'Dispatch Supervisor',
            ],
            [
                'first_name' => 'Olivia',
                'last_name' => 'Martinez',
                'email' => 'olivia@gmail.com',
                'role' => 'Sales Agent',
            ],
            [
                'first_name' => 'James',
                'last_name' => 'Anderson',
                'email' => 'james@gmail.com',
                'role' => 'Manager',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );

            if (!empty($data['role'])) {
                $role = Role::where('name', $data['role'])->first();
                if ($role) {
                    $user->syncRoles([$role]);
                }
            }
        }
    }
}
