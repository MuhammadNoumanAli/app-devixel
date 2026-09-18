<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Dispatch;
use App\Models\TruckType;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Auth middleware is enforced at the route group level in routes/web.php
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [];
        // Get the authenticated user
        $user = Auth::user();

        $firstDayOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::today();
        $now = Carbon::now();
        $last30Days = Carbon::now()->subDays(30);
        if($user->hasRole('Admin')) {
            $carrierToday = Carrier::whereDate('created_at', $today)->count();
            $data['carrierLast30Days'] = Carrier::whereBetween('created_at', [$firstDayOfMonth, $now])->count();
            $dispatcherToday = Dispatch::whereDate('load_date', $today)->sum('rate');
            $data['dispatcherLast30DaysSum'] = Dispatch::whereBetween('load_date', [$firstDayOfMonth, $now])->sum('rate');
            $data['dispatcherLast30DaysReceivable'] = Dispatch::whereBetween('load_date', [$firstDayOfMonth, $now])->sum('receivable');

            // amount need to receive
            $endDate = Carbon::now()->toDateString();
            //$startDate = Carbon::now()->subDay(30)->toDateString();
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $dispatches = Dispatch::whereBetween(DB::raw('DATE(dispatches.created_at)'), [$startDate, $endDate])->get();
            $data['receivablePending'] = $dispatches->where('invoice_status', 0)->sum('receivable');
            $data['receivableInvoiceSent'] = $dispatches->where('invoice_status', 1)->sum('receivable');
            $data['receivablePaid'] = $dispatches->where('invoice_status', 2)->sum('receivable');
            $data['totalReceiveable'] = $data['receivablePending'] + $data['receivableInvoiceSent'] + $data['receivablePaid'];

            // Set the start and end dates for the previous month
            $preStartDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $preEndDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            // Fetch the dispatches data within the previous month date range
            $preDispatches = Dispatch::whereBetween(DB::raw('DATE(dispatches.created_at)'), [$preStartDate, $preEndDate])->get();

            $data['preReceivablePending'] = $preDispatches->where('invoice_status', 0)->sum('receivable');
            $data['preReceivableInvoiceSent'] = $preDispatches->where('invoice_status', 1)->sum('receivable');
            $data['preReceivablePaid'] = $preDispatches->where('invoice_status', 2)->sum('receivable');
            $data['preTotalReceiveable'] = $data['preReceivablePending'] + $data['preReceivableInvoiceSent'] + $data['preReceivablePaid'];

            // dd($data);
        }else{
            $carrierToday = Carrier::where('user_id', $user->id)->whereDate('created_at', $today)->count();
            $data['carrierLast30Days'] = Carrier::where('user_id', $user->id)->whereBetween('created_at', [$firstDayOfMonth, $now])->count();
            $dispatcherToday = Dispatch::where('user_id', $user->id)->whereDate('load_date', $today)->sum('rate');
            $data['dispatcherLast30DaysSum'] = Dispatch::where('user_id', $user->id)->whereBetween('load_date', [$firstDayOfMonth, $now])->sum('rate');
            $data['dispatcherLast30DaysReceivable'] = Dispatch::where('user_id', $user->id)->whereBetween('load_date', [$firstDayOfMonth, $now])->sum('receivable');
        }

//        if ($user->hasRole('Sales Agent')) {
//            // Show only the user's records of that day for sales agents
//            $carrierToday = Carrier::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
////            $carrierYesterday = Carrier::where('user_id', $user->id)->whereDate('created_at', Carbon::yesterday())->count();
//        } else {

        // commented this code
        //$carrierToday = Carrier::whereDate('created_at', Carbon::today())->count();

//            $carrierYesterday = Carrier::whereDate('created_at', Carbon::yesterday())->count();
//        }

        // $thirtyDaysAgo = Carbon::today()->subDays(29);
        // $data['carrierLast30Days'] = Carrier::whereDate('created_at', '>=', $thirtyDaysAgo)->count();

        // commented this code
        // $firstDayOfMonth = Carbon::now()->startOfMonth();
        // $data['carrierLast30Days'] = Carrier::whereBetween('created_at', [$firstDayOfMonth, Carbon::now()])->count();



//        if ($user->hasRole('Dispatcher')) {
//            // Show only the user's records of that day for sales agents
//            $dispatcherToday = Dispatch::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->sum('rate');
////            $dispatcherYesterday = Dispatch::where('user_id', $user->id)->whereDate('created_at', Carbon::yesterday())->count();
//        } else {

        // commented this code
        //$dispatcherToday = Dispatch::whereDate('load_date', Carbon::today())->sum('rate');


//            $dispatcherYesterday = Dispatch::whereDate('created_at', Carbon::yesterday())->count();
//        }

        // $thirtyDaysAgo = Carbon::today()->subDays(29);
        // $data['dispatcherLast30DaysSum'] = Dispatch::whereDate('load_date', '>=', $thirtyDaysAgo)->sum('rate');

        // commented this code
        // $firstDayOfMonth = Carbon::now()->startOfMonth();
        // $data['dispatcherLast30DaysSum'] = Dispatch::whereBetween('load_date', [$firstDayOfMonth, Carbon::now()])->sum('rate');



        $data['carrierToday'] = $carrierToday ?? 0;
        $data['carrierLast30Days'] = $data['carrierLast30Days'] ?? 0;
        $data['dispatcherToday'] = $dispatcherToday ?? 0;
        $data['dispatcherLast30DaysSum'] = $data['dispatcherLast30DaysSum'] ?? 0;
        $data['dispatcherLast30DaysReceivable'] = $data['dispatcherLast30DaysReceivable'] ?? 0;

        // Vuexy Dashboard metrics
        $data['dispatchToday'] = Dispatch::whereDate('load_date', $today)->count();
        $data['dispatchLast30Days'] = Dispatch::whereBetween('load_date', [$firstDayOfMonth, $now])->count();
        $data['revenueToday'] = $data['dispatcherToday'];
        $data['revenueLast30Days'] = $data['dispatcherLast30DaysSum'];
        $data['commissionToday'] = Dispatch::whereDate('load_date', $today)->sum('receivable');
        $data['commissionLast30Days'] = $data['dispatcherLast30DaysReceivable'];
        $data['recentSales'] = Carrier::with('user')->latest()->take(5)->get();
        $data['recentDispatch'] = Dispatch::with('user')->latest()->take(5)->get();

        // Check percentage for carrier increase and decrease
//        if($carrierYesterday == 0){
//            $percentage = 100;
//            $data['carrierStatus'] = 'increase';
//            $data['carrierPercentage'] = round($percentage, 2);
//        }else{
//            $change = $carrierToday - $carrierYesterday;
//            $percentage = ($change / $carrierYesterday) * 100;
//            if ($change > 0) {
//                $data['carrierStatus'] = 'increase';
//                $data['carrierPercentage'] = round($percentage, 2);
//            } elseif ($change < 0) {
//                $data['carrierStatus'] = 'decrease';
//                $data['carrierPercentage'] = round($percentage, 2);
//            } else {
//                $data['carrierStatus'] = 'no change';
//                $data['carrierPercentage'] = round($percentage, 2);
//            }
//        }
//
//        // Check percentage for dispatcher increase and decrease
//        if($dispatcherYesterday == 0){
//            $percentage = 100;
//            $data['dispatcherStatus'] = 'increase';
//            $data['dispatcherPercentage'] = round($percentage, 2);
//        }else{
//            $change = $dispatcherToday - $dispatcherYesterday;
//            $percentage = ($change / $dispatcherYesterday) * 100;
//            if ($change > 0) {
//                $data['dispatcherStatus'] = 'increase';
//                $data['dispatcherPercentage'] = round($percentage, 2);
//            } elseif ($change < 0) {
//                $data['dispatcherStatus'] = 'decrease';
//                $data['dispatcherPercentage'] = round($percentage, 2);
//            } else {
//                $data['dispatcherStatus'] = 'no change';
//                $data['dispatcherPercentage'] = round($percentage, 2);
//            }
//        }
        return view('home', $data);
    }

    public function carrierCharts(){
        $role = Role::where('name', 'Sales Agent')->first();

        // Get all users with the "sales agent" role
        $users = $role->users;
        // Get the date range for the last 7 days
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Loop through each user and fetch their carrier records for the last 7 days
        foreach ($users as $user) {
            $userRecord = [];
            for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                $count = Carrier::where('user_id', $user->id)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();
                $userRecord[] = $count;
            }
            $data[] = [
                'name' => $user->first_name,
                'data' => $userRecord,
            ];
        }

        // Prepare the date range array
        $dateRange = [];
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Prepare the final response array
        $response = [
            'data' => $data,
            'categories' => $dateRange,
        ];

        // Return the response as JSON
        return response()->json($response);
    }

    public function carrierChartsType(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

//        if ($user->hasRole('Sales Agent')) {
//            // Load carrier records for the current sales agent
//            $users = [$user];
//        } else {
        // $role = Role::where('name', 'Sales Agent')->first();
        // Get all users with the "sales agent" role


        // $users = $role->users;
        // if ($role) {
        //     $users = User::status('active')
        //         ->whereHas('roles', function ($query) use ($role) {
        //             $query->where('id', $role->id);
        //         })
        //         ->get();
        // }

//        }

//        $role = Role::where('name', 'Sales Agent')->first();
//        // Get all users with the "sales agent" role
//        $users = $role->users;

        $roles = Role::whereIn('name', ['Sales Agent', 'Sales Manager'])->get();

        if ($roles->isNotEmpty()) {
            $userIds = $roles->flatMap->users->pluck('id')->unique()->toArray();

            $users = User::status('active')
                ->whereIn('id', $userIds)
                ->get();
        }


        if($request->chart == '7'){
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }elseif($request->chart == 'thisMonth'){
            // first_day_of_the_current_month
            $startDate = Carbon::today()->startOfMonth();
            // last_day_of_the_current_month
            $endDate = Carbon::now()->endOfDay();
        }elseif ($request->chart == 30){
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }elseif ($request->chart == 'lastMonth'){
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        }

        // Loop through each user and fetch their carrier records for the last 7 days
        foreach ($users as $user) {
            $userRecord = [];
            for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                $count = Carrier::where('user_id', $user->id)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();
                $userRecord[] = $count;
            }
            $data[] = [
                'name' => $user->first_name,
                'data' => $userRecord,
            ];
        }

        // Prepare the date range array
        $dateRange = [];
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Prepare the final response array
        $response = [
            'data' => $data,
            'categories' => $dateRange,
        ];

        // Return the response as JSON
        return response()->json($response);
    }

    public function carrierPieCharts(Request $request)
    {
        $role = Role::where('name', 'Sales Agent')->first();
        // Get all users with the "sales agent" role
        $users = $role->users;
        if ($request->chart == '7') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'thisMonth') {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 30) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'lastMonth') {
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        }

        $data = [];
        // Loop through each user and fetch their carrier records for the specified date range
        foreach ($users as $user) {
            $count = Carrier::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $data[] = [
                'value' => $count,
                'name' => $user->first_name,
            ];
        }

        // Return the response as JSON
        return response()->json($data);
    }


    public function dispatchersChartsType(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

//        if ($user->hasRole('Dispatcher')) {
//            // Load carrier records for the current sales agent
//            $users = [$user];
//        } else {
        // $role = Role::where('name', 'Dispatcher')->first();
        // Get all users with the "sales agent" role
        // $users = $role->users;
        // if ($role) {
        //     $users = User::status('active')
        //         ->whereHas('roles', function ($query) use ($role) {
        //             $query->where('id', $role->id);
        //         })
        //         ->get();
        // }
//        }

//        $role = Role::where('name', 'Dispatcher')->first();
//        // Get all users with the "sales agent" role
//        $users = $role->users;

        $roles = Role::whereIn('name', ['Dispatcher', 'Dispatch Supervisor'])->get();

        if ($roles->isNotEmpty()) {
            $userIds = $roles->flatMap->users->pluck('id')->unique()->toArray();

            $users = User::status('active')
                ->whereIn('id', $userIds)
                ->get();
        }


        if($request->chart == '7'){
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }elseif($request->chart == 'thisMonth'){
            // first_day_of_the_current_month
            $startDate = Carbon::today()->startOfMonth();
            // last_day_of_the_current_month
            $endDate = Carbon::now()->endOfDay();
        }elseif ($request->chart == 30){
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }elseif ($request->chart == 'lastMonth'){
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        }

        // Loop through each user and fetch their carrier records for the last 7 days
        foreach ($users as $user) {
            $userRecord = [];
            for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                $count = Dispatch::where('user_id', $user->id)
                    ->whereDate('load_date', $date->format('Y-m-d'))
                    ->count();
                $userRecord[] = $count;
            }
            $data[] = [
                'name' => $user->first_name,
                'data' => $userRecord,
            ];
        }

        // Prepare the date range array
        $dateRange = [];
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Prepare the final response array
        $response = [
            'data' => $data,
            'categories' => $dateRange,
        ];

        // Return the response as JSON
        return response()->json($response);
    }

    public function dispatchersChartsRevenueTypeOld(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Define date range based on the chart parameter
        if ($request->chart == '7') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'thisMonth') {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 30) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'lastMonth') {
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        }

        // Initialize the data array
        $data = [];

        // Check the role of the user
        if ($user->hasRole('Admin')) {
            // Admin: Fetch users with the roles Dispatcher or Dispatch Supervisor
            $roles = Role::whereIn('name', ['Dispatcher', 'Dispatch Supervisor'])->get();
            $userIds = $roles->flatMap->users->pluck('id')->unique()->toArray();

            $users = User::status('active')->whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $userRecord = [];
                for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                    $count = Dispatch::where('user_id', $user->id)
                        ->whereDate('load_date', $date->format('Y-m-d'))
                        ->sum('receivable');
                    $userRecord[] = $count;
                }
                $data[] = [
                    'name' => $user->full_name,
                    'data' => $userRecord,
                ];
            }
        } elseif ($user->hasRole(['Dispatcher', 'Dispatch Supervisor'])) {
            $mcNumbers = Dispatch::with('carrier')->where('user_id', $user->id)
                ->whereBetween('load_date', [$startDate, $endDate])
                ->pluck('mc_number')
                ->unique()
                ->toArray();

            $dispatchers = Dispatch::with('carrier')->whereIn('mc_number', $mcNumbers)
                ->select('mc_number') // Select only necessary fields
                ->distinct() // Ensure only unique mc_number entries
                ->get();

            foreach ($dispatchers as $dispatch) {
                $mcRecord = [];
                for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                    $count = Dispatch::with('carrier')->where('user_id', $user->id)
                        ->where('mc_number', $dispatch->mc_number)
                        ->whereDate('load_date', $date->format('Y-m-d'))
                        ->sum('receivable');
                    $mcRecord[] = $count; // Add revenue for the date (or 0 if no revenue)
                }
                $data[] = [
                    'name' => $dispatch->carrier->company_name, // Use carrier company name instead of mc_number for display
                    'data' => $mcRecord,
                ];
            }
        }

        // Prepare the date range array
        $dateRange = [];
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Prepare the final response array
        $response = [
            'data' => $data,
            'categories' => $dateRange,
        ];

        // Return the response as JSON
        return response()->json($response);
    }

    public function dispatchersChartsRevenueType(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Define date range based on the chart parameter
        if ($request->chart == '7') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'thisMonth') {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 30) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'lastMonth') {
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        } elseif ($request->chart == 'monthWise') {
            $startDate = Carbon::now()->subMonths(12)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Initialize the data array
        $data = [];

        // Check the role of the user
        if ($user->hasRole('Admin')) {
            // Admin: Fetch users with the roles Dispatcher or Dispatch Supervisor
            $roles = Role::whereIn('name', ['Dispatcher', 'Dispatch Supervisor'])->get();
            $userIds = $roles->flatMap->users->pluck('id')->unique()->toArray();

            $users = User::status('active')->whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $userRecord = [];
                if ($request->chart == 'monthWise') {
                    // For monthWise, group data by month
                    for ($date = clone $startDate; $date <= $endDate; $date->addMonth()) {
                        $count = Dispatch::where('user_id', $user->id)
                            ->whereYear('load_date', $date->year)
                            ->whereMonth('load_date', $date->month)
                            ->sum('receivable');
                        $userRecord[] = $count;
                    }
                } else {
                    // For other cases, group data by day
                    for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                        $count = Dispatch::where('user_id', $user->id)
                            ->whereDate('load_date', $date->format('Y-m-d'))
                            ->sum('receivable');
                        $userRecord[] = $count;
                    }
                }
                $data[] = [
                    'name' => $user->full_name,
                    'data' => $userRecord,
                ];
            }
        } elseif ($user->hasRole(['Dispatcher', 'Dispatch Supervisor'])) {
            // Dispatcher or Dispatch Supervisor: Fetch data grouped by mc_number
            $mcNumbers = Dispatch::with('carrier')->where('user_id', $user->id)
                ->whereBetween('load_date', [$startDate, $endDate])
                ->pluck('mc_number')
                ->unique()
                ->toArray();

            $dispatchers = Dispatch::with('carrier')->whereIn('mc_number', $mcNumbers)
                ->select('mc_number') // Select only necessary fields
                ->distinct() // Ensure only unique mc_number entries
                ->get();

            foreach ($dispatchers as $dispatch) {
                $mcRecord = [];
                for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                    $count = Dispatch::with('carrier')->where('user_id', $user->id)
                        ->where('mc_number', $dispatch->mc_number)
                        ->whereDate('load_date', $date->format('Y-m-d'))
                        ->sum('receivable');
                    $mcRecord[] = $count; // Add revenue for the date (or 0 if no revenue)
                }
                $data[] = [
                    'name' => $dispatch->carrier->company_name, // Use carrier company name instead of mc_number for display
                    'data' => $mcRecord,
                ];
            }
        }

        // Prepare the date range array
        $dateRange = [];
        if ($request->chart == 'monthWise') {
            // For monthWise, show month names
            for ($date = clone $startDate; $date <= $endDate; $date->addMonth()) {
                $dateRange[] = $date->format('F Y');
            }
        } else {
            // For other cases, show daily dates
            for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                $dateRange[] = $date->format('Y-m-d');
            }
        }

        // Prepare the final response array
        $response = [
            'data' => $data,
            'categories' => $dateRange,
        ];

        // Return the response as JSON
        return response()->json($response);
    }


    function carrierPieChart(Request $request){
        $role = Role::where('name', 'Sales Agent')->first();

        // Get all users with the "sales agent" role
        $users = $role->users;
        if ($request->chart == '7') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'thisMonth') {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 30) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'lastMonth') {
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        }

        $data = [];
        // Loop through each user and fetch their carrier records for the specified date range
        foreach ($users as $user) {
            $count = Carrier::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $data[] = [
                'value' => $count,
                'name' => $user->first_name,
            ];
        }

        // Return the response as JSON
        return response()->json($data);
    }


    function truckTypePieChart(Request $request) {
        // Define the date range based on the selected chart type
        if ($request->chart == 'last_seven_days') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($request->chart == 'last_thirty_days') {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->chart == 'last_month') {
            $startDate = Carbon::now()->subMonth(1)->startOfMonth();
            $endDate = Carbon::now()->subMonth(1)->endOfMonth();
        } else {
            // Handle other chart types or throw an error if necessary
        }

        $truckTypes = TruckType::get();
        // Build the query to fetch dispatches with related carrier and user data
        foreach ($truckTypes as $truckType) {
            $count = Dispatch::whereHas('carrier', function ($query) use ($truckType, $startDate, $endDate) {
                $query->where('truck_type', $truckType->id)
                    ->whereBetween('load_date', [$startDate, $endDate]);
            })->count();

            $data[] = [
                'value' => $count,
                'name' => $truckType->name,
            ];
        }

        // Return the data as JSON response
        return response()->json($data);
    }
}
