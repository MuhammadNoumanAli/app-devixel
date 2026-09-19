<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\TruckType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestPaginationDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Truck Types (so total is >= 15)
        $truckTypesData = [
            'Reefer 53ft',
            'Flatbed 48ft',
            'Step Deck 53ft',
            'Power Only',
            'Box Truck 26ft',
            'Hotshot 40ft',
            'Conestoga 53ft',
            'Lowboy 48ft',
        ];
        foreach ($truckTypesData as $name) {
            TruckType::firstOrCreate(['name' => $name]);
        }
        $allTruckTypes = TruckType::all();

        // 2. Seed Users (so total is >= 25)
        $usersData = [
            ['first_name' => 'Alexander', 'last_name' => 'Wright', 'email' => 'alexander.wright@demo.com', 'role' => 'Dispatcher'],
            ['first_name' => 'Sophia', 'last_name' => 'Bennett', 'email' => 'sophia.bennett@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Lucas', 'last_name' => 'Foster', 'email' => 'lucas.foster@demo.com', 'role' => 'Dispatcher'],
            ['first_name' => 'Mia', 'last_name' => 'Harrison', 'email' => 'mia.harrison@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Ethan', 'last_name' => 'Reynolds', 'email' => 'ethan.reynolds@demo.com', 'role' => 'Dispatch Supervisor'],
            ['first_name' => 'Charlotte', 'last_name' => 'King', 'email' => 'charlotte.king@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Benjamin', 'last_name' => 'Scott', 'email' => 'benjamin.scott@demo.com', 'role' => 'Dispatcher'],
            ['first_name' => 'Amelia', 'last_name' => 'Green', 'email' => 'amelia.green@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Henry', 'last_name' => 'Adams', 'email' => 'henry.adams@demo.com', 'role' => 'Dispatcher'],
            ['first_name' => 'Harper', 'last_name' => 'Baker', 'email' => 'harper.baker@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Daniel', 'last_name' => 'Nelson', 'email' => 'daniel.nelson@demo.com', 'role' => 'Dispatcher'],
            ['first_name' => 'Evelyn', 'last_name' => 'Carter', 'email' => 'evelyn.carter@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Jackson', 'last_name' => 'Mitchell', 'email' => 'jackson.mitchell@demo.com', 'role' => 'Dispatcher'],
            ['first_name' => 'Aria', 'last_name' => 'Perez', 'email' => 'aria.perez@demo.com', 'role' => 'Sales Agent'],
            ['first_name' => 'Sebastian', 'last_name' => 'Roberts', 'email' => 'sebastian.roberts@demo.com', 'role' => 'Dispatcher'],
        ];

        foreach ($usersData as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'first_name'      => $u['first_name'],
                    'last_name'       => $u['last_name'],
                    'password'        => Hash::make('password123'),
                    'status'          => 'active',
                    'load_commission' => rand(5, 15),
                ]
            );

            if (!empty($u['role'])) {
                $role = Role::where('name', $u['role'])->first();
                if ($role && !$user->hasRole($u['role'])) {
                    $user->assignRole($role);
                }
            }
        }

        $dispatchers = User::role(['Dispatcher', 'Dispatch Supervisor'])->get();
        $agents = User::role('Sales Agent')->get();
        $admin = User::first();

        // 3. Seed Carriers (25 realistic carriers)
        $carrierNames = [
            ['Apex Freight Solutions', 'John Apex', '(800) 555-0101', 'Chicago', 'IL', '60601'],
            ['Blue Horizon Express', 'Sarah Horizon', '(800) 555-0102', 'Dallas', 'TX', '75201'],
            ['Eagle Eye Hauling', 'Robert Eagle', '(800) 555-0103', 'Atlanta', 'GA', '30301'],
            ['Silver Star Transport', 'Michael Silver', '(800) 555-0104', 'Los Angeles', 'CA', '90001'],
            ['Cascade Mountain Logistics', 'David Cascade', '(800) 555-0105', 'Seattle', 'WA', '98101'],
            ['Pinnacle Cargo Systems', 'Emily Pinnacle', '(800) 555-0106', 'Denver', 'CO', '80201'],
            ['Golden State Carriers', 'James Golden', '(800) 555-0107', 'Phoenix', 'AZ', '85001'],
            ['Midwest Swift Transport', 'Olivia Swift', '(800) 555-0108', 'Columbus', 'OH', '43201'],
            ['Lone Star Heavy Haul', 'Lucas Lone', '(800) 555-0109', 'Houston', 'TX', '77001'],
            ['Liberty Logistics LLC', 'Aria Liberty', '(800) 555-0110', 'Philadelphia', 'PA', '19101'],
            ['Vanguard Freightways', 'Henry Vanguard', '(800) 555-0111', 'Indianapolis', 'IN', '46201'],
            ['Titanium Transit Co', 'Charlotte Titanium', '(800) 555-0112', 'Charlotte', 'NC', '28201'],
            ['Summit Ridge Trucking', 'Alexander Summit', '(800) 555-0113', 'Salt Lake City', 'UT', '84101'],
            ['Keystone Line Logistics', 'Mia Keystone', '(800) 555-0114', 'Pittsburgh', 'PA', '15201'],
            ['Redwood Express Lines', 'Benjamin Redwood', '(800) 555-0115', 'Sacramento', 'CA', '95801'],
            ['Atlantic Coast Dispatch', 'Evelyn Atlantic', '(800) 555-0116', 'Jacksonville', 'FL', '32201'],
            ['Ironclad Freight Network', 'Jackson Ironclad', '(800) 555-0117', 'Detroit', 'MI', '48201'],
            ['Prairie Wind Transport', 'Amelia Prairie', '(800) 555-0118', 'Kansas City', 'MO', '64101'],
            ['Polaris Global Freight', 'Sebastian Polaris', '(800) 555-0119', 'Minneapolis', 'MN', '55401'],
            ['Frontier Highway Lines', 'Harper Frontier', '(800) 555-0120', 'Omaha', 'NE', '68101'],
            ['Thunderbird Cargo', 'Daniel Thunderbird', '(800) 555-0121', 'Oklahoma City', 'OK', '73101'],
            ['Oasis Logistics Group', 'Sophia Oasis', '(800) 555-0122', 'Las Vegas', 'NV', '89101'],
            ['Great Lakes Transport', 'Ethan Great', '(800) 555-0123', 'Milwaukee', 'WI', '53201'],
            ['Pacific Crest Express', 'Michael Pacific', '(800) 555-0124', 'Portland', 'OR', '97201'],
            ['Metro Crossroad Haulers', 'Jessica Metro', '(800) 555-0125', 'Nashville', 'TN', '37201'],
        ];

        $statuses = ['pending', 'in_progress', 'not_responding', 'documents_required', 'done'];

        $paymentType = \App\Models\PaymentType::firstOrCreate(['name' => 'QuickPay']);

        foreach ($carrierNames as $idx => $c) {
            $mcNum = 800100 + $idx;
            $dotNum = 3500100 + $idx;
            $assignedDispatcher = $dispatchers->isNotEmpty() ? $dispatchers->random() : $admin;
            $agent = $agents->isNotEmpty() ? $agents->random() : $admin;
            $status = $statuses[$idx % count($statuses)];
            $truckType = $allTruckTypes->isNotEmpty() ? $allTruckTypes->random()->id : 1;

            $carrier = Carrier::firstOrCreate(
                ['mc_number' => $mcNum],
                [
                    'dot'               => $dotNum,
                    'name'              => $c[1],
                    'company_name'      => $c[0],
                    'number'            => $c[2],
                    'email'             => strtolower(str_replace(' ', '', $c[1])) . '@freight.demo',
                    'user_id'           => $agent->id,
                    'assign_to'         => $assignedDispatcher->id,
                    'assignment_status' => $status,
                    'assigned_at'       => Carbon::now()->subDays(rand(0, 20)),
                    'truck_type'        => $truckType,
                    'payment_type'      => $paymentType->id,
                    'charge_type'       => 'Percentage',
                    'percent_flat'      => rand(7, 12),
                    'city_name'         => $c[3],
                    'state_name'        => $c[4],
                    'zip_code'          => $c[5],
                    'active_status'     => 1,
                    'created_at'        => Carbon::now()->subDays(rand(0, 20)),
                    'updated_at'        => Carbon::now()->subHours(rand(1, 48)),
                ]
            );
        }

        // 4. Seed Dispatches / Loads (20 loads)
        $allCarriers = Carrier::take(25)->get();
        for ($i = 1; $i <= 20; $i++) {
            $carrier = $allCarriers->random();
            $dispatcher = $dispatchers->isNotEmpty() ? $dispatchers->random() : $admin;
            $rate = rand(12, 38) * 100;
            $percentage = rand(7, 12);
            $receivable = round(($rate * $percentage) / 100, 2);

            Dispatch::firstOrCreate(
                ['load_number' => 'LD-' . (5000 + $i)],
                [
                    'user_id'           => $dispatcher->id,
                    'mc_number'         => $carrier->mc_number,
                    'owner_name'        => $carrier->name,
                    'driver_name'       => 'Driver ' . $i,
                    'driver_number'     => '(800) 555-99' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'truck_number'      => 'TRK-' . rand(100, 999),
                    'trailer_number'    => 'TRL-' . rand(1000, 9999),
                    'pick_location'     => 'Dallas, TX',
                    'delivery_location' => 'Atlanta, GA',
                    'load_date'         => Carbon::now()->subDays(rand(1, 15))->toDateString(),
                    'pick_date'         => Carbon::now()->subDays(rand(1, 15))->toDateString(),
                    'delivery_date'     => Carbon::now()->addDays(rand(1, 5))->toDateString(),
                    'total_miles'       => rand(400, 1800),
                    'rate'              => $rate,
                    'percentage'        => $percentage,
                    'receivable'        => $receivable,
                    'broker_company_name' => 'C.H. Robinson Demo',
                    'broker_mc'         => '123456',
                    'broker_number'     => '(800) 555-7788',
                    'broker_email'      => 'broker@chrobinson.demo',
                    'broker_rep_name'   => 'Broker Agent ' . $i,
                    'invoice_status'    => rand(0, 1),
                    'created_at'        => Carbon::now()->subDays(rand(0, 15)),
                    'updated_at'        => Carbon::now()->subDays(rand(0, 15)),
                ]
            );
        }

        // 5. Seed Invoices (15 invoices)
        $invStatuses = ['due', 'partial', 'paid'];
        for ($j = 1; $j <= 15; $j++) {
            $carrier = $allCarriers->random();
            $total = rand(8, 30) * 100;
            $invStatus = $invStatuses[$j % count($invStatuses)];

            if ($invStatus === 'paid') {
                $paid = $total;
                $due = 0;
            } elseif ($invStatus === 'partial') {
                $paid = round($total / 2, 2);
                $due = $total - $paid;
            } else {
                $paid = 0;
                $due = $total;
            }

            Invoice::firstOrCreate(
                ['invoice_no' => 'INV-2026-' . str_pad($j, 4, '0', STR_PAD_LEFT)],
                [
                    'mc_number'    => $carrier->mc_number,
                    'carrier_id'   => $carrier->id,
                    'carrier_name' => $carrier->company_name,
                    'total_amount' => $total,
                    'paid_amount'  => $paid,
                    'due_amount'   => $due,
                    'status'       => $invStatus,
                    'invoice_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                    'due_date'     => Carbon::now()->addDays(rand(5, 20))->toDateString(),
                    'notes'        => 'Automated test invoice record #' . $j,
                    'created_by'   => $admin->id,
                    'created_at'   => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at'   => Carbon::now()->subDays(rand(0, 10)),
                ]
            );
        }
    }
}
