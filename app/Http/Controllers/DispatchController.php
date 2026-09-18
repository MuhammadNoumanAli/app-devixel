<?php

namespace App\Http\Controllers;

use App\Http\Requests\DispatchRequest;
use App\Models\Carrier;
use App\Models\CommissionUser;
use App\Models\Dispatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class DispatchController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:Admin|Dispatcher');
    // }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('dispatchers-list');
        // if (! Gate::allows('dispatchers-list')) {
        //     return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }

        $endDate = Carbon::now()->toDateString();
        $startDate = Carbon::now()->subDay()->toDateString();

        $data_array = [];
        if (Auth::user()->hasRole('Admin')) {
            $dispatchers = Dispatch::with('user')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->paginate(15);
        }else{
            $dispatchers = Dispatch::latest()->where('user_id','=', auth()->user()->id)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->paginate(15);
        }
        $data_array['dispatchers'] = $dispatchers;
        return view('dispatch.index', $data_array);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // if (! Gate::allows('dispatchers-create')) {
        //     return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }
        $this->authorize('dispatchers-create');

        return view('dispatch.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DispatchRequest $request)
    {
        // if (! Gate::allows('dispatchers-create')) {
        //     return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }
        $this->authorize('dispatchers-create');

        $data = $request->except('_token');
        $data['rate_confirmation']      =  $this->saveImage($request, 'rate_confirmation');
        $data['bol_pod']                =  $this->saveImage($request, 'bol_pod');
        $data['additional_doc']           =  $this->saveImage($request, 'additional_doc');
        $data['user_id'] = Auth::user()->id;

        $user = Auth::user();
        $today = date('Y-m-d');
        $requestedDate = date('Y-m-d', strtotime($request->load_date));
        if ($user->can('add-previous-load-date')) {
            $data['load_date'] = date('Y-m-d', strtotime($request->load_date));
        } else if ($requestedDate >= $today) {
            $data['load_date'] = $requestedDate;
        }

        $data['pick_date'] = date('Y-m-d', strtotime($request->pick_date));
        $data['delivery_date'] = date('Y-m-d', strtotime($request->delivery_date));

        $result = Dispatch::create($data);
        if($result){
            $this->calculateAgentCommission($request);

            $this->calculateDispatchCommission($request);
            $status = 'status';
            $msg = 'Dispatch Add Successfully';
        }else{
            $status = 'error';
            $msg = 'Dispatch Not Added';
        }
        return redirect()->route('dispatchers.index')->with($status, $msg);

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Dispatch $dispatcher)
    {

        // if (!Auth::user()->hasRole('Admin') && $dispatcher->user_id != Auth::user()->id) {
        //     return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }

        if (!Auth::user()->can('dispatchers-view')) {
            return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        if($request->status == 'view'){
            if ($request->has('status') && $request->status === 'view') {
                // Update the notification_status field
                $dispatcher->notification_status = false; // Assuming 1 is the value for 'read'
                $dispatcher->save();
            }
        }

        if($request->ajax()){

            $html = '<section class="section profile">
                                <div class="row">
                                    <div class="col-xl-6">

                                        <div class="card">
                                            <div class="card-body pt-3">
                                                <!-- Bordered Tabs -->

                                                <div class="tab-content pt-2">
                                                    <div class="tab-pane fade profile-overview active show" id="profile-overview" role="tabpanel">
                                                        <h5 class="card-title">Comment</h5>';
            $comment = 'N/A';
            if ($dispatcher->comment) {
                $comment = $dispatcher->comment;
            }
            $html .= '<p class="small fst-italic">' . $comment . '</p>


                                                        <h5 class="card-title">Load Details</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">MC Number</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->mc_number . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Carrier Name</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->owner_name . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Load Number</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->load_number . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Pick Location</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->pick_location . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Delivery Location</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->delivery_location . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Load Date</div>
                                                            <div class="col-lg-8 col-md-8">' . date('F d, Y', strtotime($dispatcher->load_date)) . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Pick Date</div>
                                                            <div class="col-lg-8 col-md-8">' . date('F d, Y', strtotime($dispatcher->pick_date)) . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Delivery Date</div>
                                                            <div class="col-lg-8 col-md-8">' . date('F d, Y', strtotime($dispatcher->delivery_date)) . '</div>
                                                        </div>

                                                        <h5 class="card-title">Truck Details</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Truck Number</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->truck_number . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Trailer No</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->trailer_number . '</div>
                                                        </div>

                                                        <h5 class="card-title">Price and Mileage</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Total Miles</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->total_miles . ' mi</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Rate</div>
                                                            <div class="col-lg-8 col-md-8">$' . $dispatcher->rate . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Percentage</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->percentage . '%</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Receivable</div>
                                                            <div class="col-lg-8 col-md-8">$' . $dispatcher->receivable . '</div>
                                                        </div>

                                                    </div>

                                                </div><!-- End Bordered Tabs -->

                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-xl-6">

                                        <div class="card">
                                            <div class="card-body pt-3">
                                                <!-- Bordered Tabs -->
                                                <div class="tab-content pt-2">
                                                    <div class="tab-pane fade profile-overview active show" id="profile-overview" role="tabpanel">
                                                        <h5 class="card-title">Upload Date</h5>
                                                        <p class="small fst-italic"> ' . date('F d, Y', strtotime($dispatcher->created_at)) . '</p>

                                                        <h5 class="card-title">Agent Name</h5>
                                                        <p class="small fst-italic">' . $dispatcher->user->first_name . ' ' . $dispatcher->user->last_name . '</p>

                                                        <h5 class="card-title">Drivers Detail</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Driver Name</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->driver_name . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Driver Contact No</div>
                                                            <div class="col-lg-8 col-md-8">' . $dispatcher->driver_number . '</div>
                                                        </div>

                                                        <h5 class="card-title">Document Received</h5>

                                                        <div class="row">
                                                            <div class="col-lg-7 col-md-7 label">Rate Confirmation</div>
                                                            <div class="col-lg-5 col-md-5">';
                                                                $extension = \File::extension($dispatcher->rate_confirmation);
                                                                $link = url('admin/dispatch_documents/' . $dispatcher->rate_confirmation);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-7 col-md-7 label">BOL/POD</div>
                                                            <div class="col-lg-5 col-md-5">';
                                                                $extension = \File::extension($dispatcher->bol_pod);
                                                                $link = url('admin/dispatch_documents/' . $dispatcher->bol_pod);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-7 col-md-7 label">Additional Doc</div>
                                                            <div class="col-lg-5 col-md-5">';
                                                                $extension = \File::extension($dispatcher->additional_doc);
                                                                $link = url('admin/dispatch_documents/' . $dispatcher->additional_doc);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!-- End Bordered Tabs -->
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body pt-3">
                                                <!-- Bordered Tabs -->

                                                <div class="tab-content pt-2">
                                                    <div class="tab-pane fade profile-overview active show" id="profile-overview" role="tabpanel">

                                                        <h5 class="card-title">Broker Details </h5>

                                                        <div class="row">
                                                            <div class="col-lg-5 col-md-5 label ">Company Name</div>
                                                            <div class="col-lg-7 col-md-7">' . $dispatcher->broker_company_name . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-5 col-md-5 label ">MC Number</div>
                                                            <div class="col-lg-7 col-md-7">' . $dispatcher->broker_mc . '</div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="col-lg-5 col-md-5 label">Contact Number</div>
                                                            <div class="col-lg-7 col-md-7">' . $dispatcher->broker_number . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-5 col-md-5 label">Email</div>
                                                            <div class="col-lg-7 col-md-7">' . $dispatcher->broker_email . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-5 col-md-5 label">Representative Name</div>
                                                            <div class="col-lg-7 col-md-7">' . $dispatcher->broker_rep_name . '</div>
                                                        </div>

                                                    </div>

                                                </div><!-- End Bordered Tabs -->

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>';

            $json = [];
            $json['html'] = $html;


            return response($json, 200);
        }


        if (Auth::user()->hasRole('Admin')) {
            return view('dispatch.show', compact('dispatcher'));
        }else{
            if($dispatcher->user_id == Auth::user()->id){
                return view('dispatch.show', compact('dispatcher'));
            }
            return redirect()->route('dispatch.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");

        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Dispatch $dispatcher)
    {
        // if (!Auth::user()->hasRole('Admin') && $dispatcher->user_id != Auth::user()->id) {
        //     return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }

        if (!Auth::user()->can('dispatchers-edit')) {
            return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        $data_array = [];
        $data_array['dispatcher'] = $dispatcher;

        $carrier_details = Carrier::where('mc_number', $dispatcher->mc_number)->first();
        $d_none = '';
        if($carrier_details && $carrier_details->percent_flat === 'flat_rate'){
            $d_none = 'd-none';
        }
        if ($request->ajax()){
            $html = ' <form id="updateDispatcherForm" method="POST" data-dispatcher-id="'.$dispatcher->id.'" action="'.route('dispatchers.update', $dispatcher->id).'" class="row g-3" enctype="multipart/form-data">
                                <div class="row show-errors" style="display:none;">
                                    <div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                                        <ul class="alert-danger-li">

                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                                <div class="row mb-2 mt-3">
                                    <input type="hidden" name="Update">
                                    <strong>Load Details</strong>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="mc_number" class="form-label">MC Number</label>
                                        <input id="mc_number" type="text" class="form-control " name="mc_number" value="'.$dispatcher->mc_number.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="owner_name" class="form-label">Carrier Name</label>
                                        <input id="owner_name" type="text" class="form-control " name="owner_name" value="'.$dispatcher->owner_name.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="load_number" class="form-label">Load Number</label>
                                        <input id="load_number" type="text" class="form-control " name="load_number" value="'.$dispatcher->load_number.'" required="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="pick_location" class="form-label">Pickup Location</label>
                                        <input id="pick_location" type="text" class="form-control " name="pick_location" value="'.$dispatcher->pick_location.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="delivery_location" class="form-label">Delivery Location</label>
                                        <input id="delivery_location" type="text" class="form-control " name="delivery_location" value="'.$dispatcher->delivery_location.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="load_date" class="form-label">Load Date</label>
                                        <input id="load_date" type="date" class="form-control " name="load_date" value="'.$dispatcher->load_date.'">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="pick_date" class="form-label">Pick Date</label>
                                        <input id="pick_date" type="date" class="form-control " name="pick_date" value="'.$dispatcher->pick_date.'" required="" autocomplete="pick_date">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="delivery_date" class="form-label">Delivery Date</label>
                                        <input id="delivery_date" type="date" class="form-control " name="delivery_date" value="'.$dispatcher->delivery_date.'">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="driver_name" class="form-label">Driver Name</label>
                                        <input id="driver_name" type="text" class="form-control " name="driver_name" value="'.$dispatcher->driver_name.'" required="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="truck_number" class="form-label">Truck Number</label>
                                        <input id="truck_number" type="text" class="form-control " name="truck_number" value="'.$dispatcher->truck_number.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="trailer_number" class="form-label">Trailer Number</label>
                                        <input id="trailer_number" type="text" class="form-control " name="trailer_number" value="'.$dispatcher->trailer_number.'">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="driver_number" class="form-label">Driver Contact Number</label>
                                        <input id="driver_number" type="text" class="form-control " name="driver_number" value="'.$dispatcher->driver_number.'" required="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="total_miles" class="form-label">Total Miles</label>
                                        <input id="total_miles" type="text" class="form-control " name="total_miles" value="'.$dispatcher->total_miles.'" required="">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="rate" class="form-label">Rate</label>
                                        <input id="rate" type="text" class="form-control " name="rate" value="'.$dispatcher->rate.'" required="">
                                    </div>
                                    <div class="col-md-3 '.$d_none.'" id="hide_percentage">
                                        <label for="percentage" class="form-label">Percentage</label>
                                        <input id="percentage" type="text" class="form-control " name="percentage" value="'.$dispatcher->percentage.'">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="receivable" class="form-label">Receivable</label>
                                        <input id="receivable" type="text" class="form-control " name="receivable" value="'.$dispatcher->receivable.'">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <strong>Brokers Details</strong>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="broker_company_name" class="form-label">Company Name</label>
                                        <input id="broker_company_name" type="text" class="form-control " name="broker_company_name" value="'.$dispatcher->broker_company_name.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="broker_mc" class="form-label">MC number</label>
                                        <input id="broker_mc" type="text" class="form-control " name="broker_mc" value="'.$dispatcher->broker_mc.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="broker_number" class="form-label">Contact Number</label>
                                        <input id="broker_number" type="text" class="form-control " name="broker_number" value="'.$dispatcher->broker_number.'" required="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="broker_email" class="form-label">Email</label>
                                        <input id="broker_email" type="text" class="form-control " name="broker_email" value="'.$dispatcher->broker_email.'" required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="broker_rep_name" class="form-label">Representative Name</label>
                                        <input id="broker_rep_name" type="text" class="form-control " name="broker_rep_name" value="'.$dispatcher->broker_rep_name.'" required="">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <strong> Select Documents</strong>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="rate_confirmation" class="form-label">Rate Confirmation</label>';
                                            $extension = \File::extension($dispatcher->rate_confirmation);
                                            $link = url('admin/dispatch_documents/' . $dispatcher->rate_confirmation);
                                            $html = $this->getHtml($extension, $link, $html);
                                            $html .= '
                                        <input id="rate_confirmation" type="file" class="form-control " name="rate_confirmation">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bol_pod" class="form-label">BOL/POD</label>';
                                        $extension = \File::extension($dispatcher->bol_pod);
                                        $link = url('admin/dispatch_documents/' . $dispatcher->bol_pod);
                                        $html = $this->getHtml($extension, $link, $html);
                                        $html .= '
                                        <input id="bol_pod" type="file" class="form-control " name="bol_pod">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="additional_doc" class="form-label">Additional Documents</label>';
                                        $extension = \File::extension($dispatcher->additional_doc);
                                        $link = url('admin/dispatch_documents/' . $dispatcher->additional_doc);
                                        $html = $this->getHtml($extension, $link, $html);
                                        $html .= '
                                        <input id="additional_doc" type="file" class="form-control " name="additional_doc">
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label for="comment" class="form-label">Comment</label>
                                            <textarea id="comment" class="form-control " name="comment">'.$dispatcher->comment.'</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>';
            $json = [];
            $json['html'] = $html;
            return response($json, 200);
        }

        if (Auth::user()->hasRole('Admin')) {
            return view('dispatch.edit', $data_array);
        }else{
            if($dispatcher->user_id == Auth::user()->id){
                return view('dispatch.edit', $data_array);
            }
            return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DispatchRequest $request, Dispatch $dispatcher)
    {
        // if (!Auth::user()->hasRole('Admin') && $dispatcher->user_id != Auth::user()->id) {
        //     return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }

        if (!Auth::user()->can('dispatchers-edit')) {
            return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        $data = $request->except('_token, _method');
        if ($request->hasFile('rate_confirmation')){
            $data['rate_confirmation']      =  $this->saveImage($request, 'rate_confirmation');
        }
        if ($request->hasFile('bol_pod')){
            $data['bol_pod']                =  $this->saveImage($request, 'bol_pod');
        }
        if ($request->hasFile('additional_doc')){
            $data['additional_doc']           =  $this->saveImage($request, 'additional_doc');
        }
//        $data['user_id'] = Auth::user()->id;

        $user = Auth::user();
        if ($user->can('edit-load-date')) {
            $data['load_date'] = date('Y-m-d', strtotime($request->load_date));
        }

        $data['pick_date'] = date('Y-m-d', strtotime($request->pick_date));
        $data['delivery_date'] = date('Y-m-d', strtotime($request->delivery_date));
        $result = $dispatcher->update($data);

        if ($request->ajax()){
            $array_msg = [];
            $array_msg['message'] = 'Something Went Wrong';
            $array_msg['status'] = 'error';
            if ($result){
                $array_msg['status'] = 'success';
                $array_msg['message'] = 'Dispatch Update Successfully';

                return response($array_msg, 200);
            }
            return response($array_msg, 200);
        }

        if($result){
            $status = 'status';
            $msg = 'Dispatch Update Successfully';
        }else{
            $status = 'error';
            $msg = 'Dispatch Not Updated';
        }
        return redirect()->route('dispatchers.index')->with($status, $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dispatch $dispatcher)
    {
        if (!Auth::user()->can('dispatchers-delete')) {
            return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        $deliveryDate = $dispatcher->delivery_date;
        $receivable = $dispatcher->receivable;

        // remove the commission if record delete
        $commission = CommissionUser::where('user_id', $dispatcher->user_id)
            ->whereRaw('`year_month` = ?', [date('Y-m', strtotime($deliveryDate))])
            ->first();

        $carrier = Carrier::with('truckType')->where('mc_number', $dispatcher->mc_number)->first();

        if($dispatcher->delete()){
            if ($commission){
                $commission->commission_price = $commission->commission_price - $receivable;
                $commission->active_truck = $commission->active_truck - 1;
                $commission->save();
            }

            if ($carrier){
                $commissionCarrier = CommissionUser::where('user_id', $carrier->user_id)
                    ->whereRaw('`year_month` = ?', [date('Y-m', strtotime($deliveryDate))])
                    ->first();

                if ($commissionCarrier){
                    $commissionCarrier->commission_price = $commissionCarrier->commission_price - $carrier->truckType->commission;
                    $commissionCarrier->active_truck = $commissionCarrier->active_truck - 1;
                    $commissionCarrier->save();

                    $carrier->active_status = 0;
                    $carrier->save();
                }
            }

            return redirect()->back()->with('status', 'Record Deleted Successfully!');
        }else{
            return redirect()->back()->with('error', 'Record Not Deleted!');
        }
    }

    public function saveImage($request, $fileName): string
    {
        $imageName = '';
        if ($request->hasFile($fileName)){
            $image = $request->file($fileName);
            $imageName = "dispatch_".date('YmdHis').'_'.uniqid().'.'.$image->extension();
            $image->move(public_path('admin/dispatch_documents'),$imageName);
        }
        return $imageName;
    }

    public function editAttachDocument(Dispatch $dispatcher){
        $this->authorize('dispatcher-edit-attachment');
        $data_array['dispatcher'] = $dispatcher;
        return view('dispatch.attach-image', $data_array);
    }

    public function updateAttachDocument(Request $request, Dispatch $dispatcher){
        $this->authorize('dispatcher-edit-attachment');
        $validated = $request->validate([
            'bol_pod' => 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf',
        ]);

        if ($request->hasFile('bol_pod')){
            $dispatcher-> bol_pod                =  $this->saveImage($request, 'bol_pod');
        }

        $result = $dispatcher->save();
        if($result){
            $status = 'status';
            $msg = 'Document Update Successfully';
        }else{
            $status = 'error';
            $msg = 'Document Not Updated';
        }
        return redirect()->route('reports.dispatchers')->with($status, $msg);
    }

    /**
     * @param $extension
     * @param \Illuminate\Foundation\Application|string|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Contracts\Foundation\Application $link
     * @param string $html
     * @return string
     */
    public function getHtml($extension, \Illuminate\Foundation\Application|string|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Contracts\Foundation\Application $link, string $html): string
    {
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
            $html .= '<a href="' . $link . '" target="_blank" download><img src="' . $link . '" height="20" width="20" alt="Image"></a>';
        } elseif ($extension === 'pdf' || $extension === 'doc' || $extension === 'docx') {
            $html .= '<a href="' . $link . '" target="_blank" download>Download</a>';
        } else {
            $html .= '-';
        }
        return $html;
    }

    public function getMCDetails(Request $request)
    {
        $mc_number = $request->input('mc_number');
        $carrier = Carrier::where('mc_number', $mc_number)->first();
        if ($carrier) {
            return response()->json([
                'status' => true,
                'name' => $carrier->name,
                'company_name' => $carrier->company_name,
                'percent_flat' => $carrier->percent_flat,
                'charge_type' => $carrier->charge_type,
                'number' => $carrier->number,
            ]);
        }
        return response()->json(['status' => false, 'message' => 'Carrier not found'], 404);
    }

    public function calculateAgentCommission($request): void
    {
        $carrier = Carrier::with('truckType')->where('active_status', '0')->where('mc_number', $request->mc_number)->first();

        $date = Carbon::parse($request->delivery_date)->format("Y-m");
        if ($carrier){
            $truckCommissionPrice = $carrier->truckType->commission;
            if ($carrier->active_status == 0){
                $carrier->active_status = 1;
                $carrier->save();
            }
            $commission = CommissionUser::where('user_id', $carrier->user_id)->where('year_month', $date)->first();
            if (empty($commission)){
                $commission = new CommissionUser();
                $commission->year_month = $date;
                $commission->user_id = $carrier->user_id;
                $commission->user_type = 0;
            }
            $commission->commission_price = $commission->commission_price + $truckCommissionPrice;
            $commission->active_truck = $commission->active_truck + 1;
            $commission->save();
        }
    }

    public function calculateDispatchCommission($request): void
    {
        //$date = date('Y-m');
        $date = Carbon::parse($request->delivery_date)->format("Y-m");
        $userId = Auth::user()->id;
        $commission = CommissionUser::where('user_id', $userId)->where('year_month', $date)->first();
        if (empty($commission)){
            $commission = new CommissionUser();
            $commission->year_month = $date;
            $commission->user_id = $userId;
            $commission->user_type = 1;
        }
        $commission->commission_price += $request->receivable;
        $commission->active_truck = $commission->active_truck + 1;
        $commission->save();
    }

    public function isCancel(Request $request, Dispatch $dispatcher)
    {
        $this->authorize('can-load-cancel');
        // Retrieve the selected status from the request
        $selectedStatus = $request->input('is_cancel');

        // Update the user's status
        $dispatcher->is_cancel = $selectedStatus;
        $success = $dispatcher->save();
        if($success){
            return response(['message' => 'Status Updated', 'status' => true ], 200);
        }
        return response(['message' => 'Status Updated', 'status' => false ], 200);
    }

}
