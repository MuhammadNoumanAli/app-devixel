<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $carriers = Carrier::with('user', 'truckType')->where('notification_status', true)->get();
        $dispatchers = Dispatch::with('user')->where('notification_status', true)->get();

        $carriers = $carriers->map(function ($carrier) {
            $carrier->source = 'Carrier';
            return $carrier;
        });

        $dispatchers = $dispatchers->map(function ($dispatcher) {
            $dispatcher->source = 'Load';
            return $dispatcher;
        });

        $mergedArray = $carriers->concat($dispatchers);
        $sortedArray = $mergedArray->sortByDesc('created_at');
        $notifications_data = $sortedArray->values()->all();

        if ($request->ajax()) {
            $html = '<li class="dropdown-header text-dark font-weight-bold">
                        <span id="count_notification">' . count($notifications_data) . ' New Notifications</span>
                    </li>';

            foreach ($notifications_data as $data) {
                $partial_html = '';

                if ($data->source == 'Load') {
                    $partial_html .= '<h6 class="mb-0">New Load $' . number_format($data->rate) . '</h6>';
                    $linkRoute = route('dispatchers.show', $data->id);
                    $icon = '<span class="avatar-initial rounded-circle bg-label-primary"><i class="ti ti-box"></i></span>';
                } else {
                    $truckName = $data->truckType ? $data->truckType->name : 'Carrier';
                    $partial_html .= '<h6 class="mb-0">' . $truckName . ' Added</h6>';
                    $linkRoute = route('carriers.show', $data->id);
                    $icon = '<span class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-truck-delivery"></i></span>';
                }

                $userName = $data->user ? $data->user->full_name : 'System';
                $timeDiff = $data->created_at ? $data->created_at->diffForHumans() : 'Just now';

                $html .= '
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <a href="' . $linkRoute . '?status=view" class="d-flex text-decoration-none text-dark">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">' . $icon . '</div>
                        </div>
                        <div class="flex-grow-1">
                            ' . $partial_html . '
                            <small class="text-muted">' . $userName . ' added new ' . $data->source . '</small>
                            <small class="text-muted d-block">' . $timeDiff . '</small>
                        </div>
                    </a>
                </li>';
            }

            if (empty($notifications_data)) {
                $html .= '<li class="list-group-item text-center text-muted py-3">No new notifications</li>';
            }

            return response()->json([
                'html' => $html,
                'count' => count($notifications_data),
            ], 200);
        }

        return redirect()->route('home');
    }
}
