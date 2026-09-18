<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarrierRequest;
use App\Http\Requests\CarrierUpdateRequest;
use App\Models\Carrier;
use App\Models\PaymentType;
use App\Models\Setting;
use App\Models\State;
use App\Models\TruckSize;
use App\Models\TruckType;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

//use Auth;

class CarrierController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:Admin|Sales Agent|Sales Manager');

    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('carriers-list');
        // if (! Gate::allows('carriers-list')) {
        //     return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }

        $endDate = Carbon::now()->toDateString();
        $startDate = Carbon::now()->toDateString();
        $data_array = [];
        if (Auth::user()->hasRole('Admin')) {
            $carriers = Carrier::with('truckType', 'user', 'assignedTo')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->paginate(15);
        }else{
            $carriers = Carrier::with('truckType', 'user', 'assignedTo')->latest()->where('user_id','=', auth()->user()->id)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->paginate(15);
        }
        $dispatchers = User::role(['Dispatcher', 'Manager', 'Dispatch Supervisor'])->get();

        $data_array['carriers'] = $carriers;
        $data_array['dispatchers'] = $dispatchers;
        return view('carrier.index', $data_array);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('carriers-create');
        // if (! Gate::allows('carriers-create')) {
        //     return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }


        $data_array = [];
        $truckSizes = TruckSize::get();
        $truckTypes = TruckType::get();
        $paymentTypes = PaymentType::get();
        $states = State::get();
        $salesAgnets = User::role('Sales Agent')->where('status', 'active')->get();
        $data_array['truckSizes'] = $truckSizes;
        $data_array['truckTypes'] = $truckTypes;
        $data_array['paymentTypes'] = $paymentTypes;
        $data_array['states'] = $states;
        $data_array['salesAgnets'] = $salesAgnets;

        return view('carrier.add', $data_array);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CarrierRequest $request)
    {
        $this->authorize('carriers-create');
        // if (! Gate::allows('carriers-create')) {
        //     return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }
        $carrier = new Carrier();

        $carrier->mc_number = $request->mc_number;
        if (Auth::user()->hasRole('Admin')) {
            $carrier->user_id = $request->agent_id;
        }else{
            $carrier->user_id = Auth::user()->id;
        }
        $carrier->dot = $request->dot;
        $carrier->name = $request->name;
        $carrier->email = $request->email;
        $carrier->number = $request->number;
        $carrier->company_name = $request->company_name;
        $carrier->truck_type = $request->truck_type;
        $carrier->truck_size = $request->truck_size;
        $carrier->maximum_weight = $request->maximum_weight;
        $carrier->payment_type = $request->payment_type;
        $carrier->percent_flat = $request->percent_flat;
        $carrier->charge_type = $request->charge_type;

        $carrier->mc_letter     =  $this->saveImage($request, 'mc_letter');
        $carrier->w_form        =  $this->saveImage($request, 'w_form');
        $carrier->coi           =  $this->saveImage($request, 'coi');
        $carrier->noa           =  $this->saveImage($request, 'noa');
        $carrier->void_cheque   =  $this->saveImage($request, 'void_cheque');
        $carrier->extra_document=  $this->saveImage($request, 'extra_document');

        $carrier->all_zones = $request->all_zones ? 1 : 0;
        $carrier->z0 = $request->z0 ? 1 : 0;
        $carrier->z1 = $request->z1 ? 1 : 0;
        $carrier->z2 = $request->z2 ? 1 : 0;
        $carrier->z3 = $request->z3 ? 1 : 0;
        $carrier->z4 = $request->z4 ? 1 : 0;
        $carrier->z5 = $request->z5 ? 1 : 0;
        $carrier->z6 = $request->z6 ? 1 : 0;
        $carrier->z7 = $request->z7 ? 1 : 0;
        $carrier->z8 = $request->z8 ? 1 : 0;
        $carrier->z9 = $request->z9 ? 1 : 0;

        $carrier->street_address = $request->street_address;
        $carrier->city_name = $request->city_name;
        $carrier->state_name = $request->state_name;
        $carrier->zip_code = $request->zip_code;
        $carrier->comment = $request->comment;
        $carrier->rpm = $request->rpm;
        if($carrier->save()){
            $status = 'status';
            $msg = 'Carrier Add Successfully';
        }else{
            $status = 'error';
            $msg = 'Something Went Wrong';
        }
        return redirect()->route('carriers.index')->with($status, $msg);

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Carrier $carrier)
    {
//        if (Auth::user()->hasRole('Sales Agent') && $carrier->user_id != Auth::user()->id) {
//            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
//        }

        if (! Gate::allows('carriers-view')) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        if($request->status == 'view'){
            if ($request->has('status') && $request->status === 'view') {
                // Update the notification_status field
                $carrier->notification_status = false; // Assuming 1 is the value for 'read'
                $carrier->save();
            }
        }
        if (! Gate::allows('carriers-view', $carrier)) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        if($request->ajax()){

            $html = '
                <section class="section profile">
                    <div class="row">
                                <div class="col-xl-7">

                                        <div class="card">
                                            <div class="card-body pt-3">
                                                <!-- Bordered Tabs -->

                                                <div class="tab-content pt-2">
                                                    <div class="tab-pane fade profile-overview active show" id="profile-overview" role="tabpanel">
                                                        <h5 class="card-title">Upload Date</h5>
                                                        <p class="small fst-italic"> ' . date('F d, Y', strtotime($carrier->created_at)) . '</p>

                                                        <h5 class="card-title">Comment</h5>
                                                        <p class="small fst-italic"> ' . (!empty($carrier->comment) ? $carrier->comment : 'N/A') . '</p>

                                                        <h5 class="card-title">Carrier Details</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">MC Number</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->mc_number.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">DOT</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->dot.'</div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Carrier Name</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->name.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Carrier Email</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->email.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Carrier Number</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->number.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Carrier Company</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->company_name.'</div>
                                                        </div>

                                                        <h5 class="card-title">RPM</h5>
                                                        <p class="small fst-italic"> $'.$carrier->rpm.'</p>

                                                        <h5 class="card-title">Truck Details</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Truck Type</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->truckType->name.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Truck Size</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->truckSize->name.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Maximum Weight</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->maximum_weight.' lbs</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Payment Type</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->paymentType->name.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Percentage/Flat Rate</div>
                                                            <div class="col-lg-8 col-md-8">'.ucfirst($carrier->percent_flat).'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Charge Type</div>
                                                            <div class="col-lg-8 col-md-8">'.($carrier->percent_flat == 'flat_rate' ? "$" : "") .$carrier->charge_type.'</div>
                                                        </div>

                                                        <h5 class="card-title">Address Details</h5>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">Address</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->street_address.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label ">City</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->city_name.'</div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">State</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->state->name.'</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 label">Zip Code</div>
                                                            <div class="col-lg-8 col-md-8">'.$carrier->zip_code.'</div>
                                                        </div>



                                                    </div>

                                                </div><!-- End Bordered Tabs -->

                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-xl-5">

                                        <div class="card">
                                            <div class="card-body pt-3">
                                                <!-- Bordered Tabs -->

                                                <div class="tab-content pt-2">
                                                    <div class="tab-pane fade profile-overview active show" id="profile-overview" role="tabpanel">
                                                        <h5 class="card-title">Agent Name</h5>
                                                        <p class="small fst-italic">' .$carrier->user->first_name .' '. $carrier->user->last_name . '</p>

                                                        <h5 class="card-title">Document Received</h5>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-8 label">MC Authority Letter</div>
                                                            <div class="col-lg-3 col-md-4">';
                                                                $extension = \File::extension($carrier->mc_letter);
                                                                $link = url('admin/carrier_img/' . $carrier->mc_letter);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-8 label">W-9 Form</div>
                                                            <div class="col-lg-3 col-md-4">';
                                                                $extension = \File::extension($carrier->w_form);
                                                                $link = url('admin/carrier_img/' . $carrier->w_form);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-8 label">Certificate of Insurance</div>
                                                            <div class="col-lg-3 col-md-4">';
                                                                $extension = \File::extension($carrier->coi);
                                                                $link = url('admin/carrier_img/' . $carrier->coi);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-8 label">Notice of Assignment</div>
                                                            <div class="col-lg-3 col-md-4">';
                                                                $extension = \File::extension($carrier->noa);
                                                                $link = url('admin/carrier_img/' . $carrier->noa);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-8 label">VOID Cheque</div>
                                                            <div class="col-lg-3 col-md-4">';
                                                                $extension = \File::extension($carrier->void_cheque);
                                                                $link = url('admin/carrier_img/' . $carrier->void_cheque);
                                                                $html = $this->getHtml($extension, $link, $html);
                                                                $html .= '
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-8 label">Extra Document</div>
                                                            <div class="col-lg-3 col-md-4">';
                                                                $extension = \File::extension($carrier->extra_document);
                                                                $link = url('admin/carrier_img/' . $carrier->extra_document);
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

                                                        <h5 class="card-title">Zones</h5>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label ">All Zones</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->all_zones == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label ">Z0</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z0 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label ">Z1</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z1 == 1 ? "Yes" : "-") . '</div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z2</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z2 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z3</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z3 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z4</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z4 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z5</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z5 == 1 ? "Yes" : "-") . '</div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z6</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z6 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z7</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z7 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z8</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z8 == 1 ? "Yes" : "-") . '</div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-9 col-md-4 label">Z9</div>
                                                            <div class="col-lg-3 col-md-8">' . ($carrier->z9 == 1 ? "Yes" : "-") . '</div>
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
            return view('carrier.show', compact('carrier'));
        }else{
            if($carrier->user_id == Auth::user()->id){
                return view('carrier.show', compact('carrier'));
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Carrier $carrier)
    {
        if (! Gate::allows('carriers-edit', $carrier)) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

//        if (Auth::user()->hasRole('Sales Agent') && $carrier->user_id != Auth::user()->id) {
//            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
//        }

//        if (Auth::user()->hasPermissionTo('carriers-edit')) {
//            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
//        }

        $data_array = [];
        $truckSizes = TruckSize::get();
        $truckTypes = TruckType::get();
        $paymentTypes = PaymentType::get();
        $states = State::get();


        $data_array['truckSizes'] = $truckSizes;
        $data_array['truckTypes'] = $truckTypes;
        $data_array['paymentTypes'] = $paymentTypes;
        $data_array['carrier'] = $carrier;
        $data_array['states'] = $states;
        $salesAgnets = User::role('Sales Agent')->get();

        if ($request->ajax()){
            $html ='<form id="updateCarrierForm" method="POST" data-carrier-id="'.$carrier->id.'" action="'.route('carriers.update', $carrier->id).'" class="row g-3" enctype="multipart/form-data">
                            <div class="row show-errors" style="display:none;">
                                <div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                                    <ul class="alert-danger-li">

                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-4">
                                    <label for="mc_number" class="form-label">Sales Agent</label>
                                    <select id="agent_id" class="form-select" name="agent_id" required="">
                                        <option selected="">Choose Sales Agent...</option>';
                                            foreach ($salesAgnets as $saleAgent){
                                                $html .='<option value="'.$saleAgent->id.'" '. ($carrier->user_id == $saleAgent->id ? 'selected': '') .'> ' .$saleAgent->first_name. ' ' . $saleAgent->last_name .' </option>';
                                            }
                                            $html .='
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="mc_number" class="form-label">MC Number</label>
                                    <input id="mc_number" type="text" class="form-control " name="mc_number" value="'.$carrier->mc_number.'" required="" autocomplete="mc_number" >
                                </div>
                                <div class="col-md-4">
                                    <label for="dot" class="form-label">DOT</label>
                                    <input id="dot" type="text" class="form-control " name="dot" value="'.$carrier->dot.'">
                                </div>
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Carrier Name</label>
                                    <input id="name" type="text" class="form-control " name="name" value="'.$carrier->name.'" required="" autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="email" class="form-label">Carrier Email</label>
                                    <input id="email" type="text" class="form-control " name="email" value="'.$carrier->email.'" required="">
                                </div>
                                <div class="col-md-4">
                                    <label for="number" class="form-label">Carrier Contact Number</label>
                                    <input id="number" type="text" class="form-control " name="number" value="'.$carrier->number.'" required="">
                                </div>
                                <div class="col-md-4">
                                    <label for="company_name" class="form-label">Carrier Company Name</label>
                                    <input id="company_name" type="text" class="form-control " name="company_name" value="'.$carrier->company_name.'" required="">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="truck_type" class="form-label">Truck Type</label>
                                    <select id="truck_type" class="form-select" name="truck_type" required="">
                                        <option selected="">Choose Truck Type...</option>';
                                            foreach($truckTypes as $truckType){
                                                $html .= '<option value="'.$truckType->id.'" '.($carrier->truck_type == $truckType->id ? 'selected': '').'> '.$truckType->name.'</option>';
                                            }
                                            $html .= '
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="truck_size" class="form-label">Truck Size</label>
                                    <select id="truck_size" class="form-select" name="truck_size" required="">
                                        <option selected="">Choose Truck Size...</option>';
                                        foreach($truckSizes as $truckSize){
                                            $html .= '<option value="'.$truckSize->id.'" '.($carrier->truck_size == $truckSize->id ? 'selected': '').'> '.$truckSize->name.'</option>';
                                        }
                                        $html .= '
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="maximum_weight" class="form-label">Maximum Weight</label>
                                    <input id="maximum_weight" type="text" class="form-control " name="maximum_weight" value="'.$carrier->maximum_weight.'" required="">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select id="payment_type" class="form-select" name="payment_type" required="">
                                        <option selected="">Choose Payment Type...</option>';
                                        foreach($paymentTypes as $paymentType){
                                            $html .= '<option value="'.$paymentType->id.'" '.($carrier->payment_type == $paymentType->id ? 'selected': '').'> '.$paymentType->name.'</option>';
                                        }
                                        $html .= '
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="percent_flat" class="form-label">Percentage/Flat Rate</label>
                                    <select id="percent_flat" class="form-select" name="percent_flat" required>
                                        <option selected="">Choose Percent or Flat...</option>
                                        <option value="percentage" '.($carrier->percent_flat === 'percentage' ? 'selected': '').'>Percentage</option>
                                        <option value="flat_rate" '.($carrier->percent_flat === 'flat_rate' ? 'selected': '').'>Flat Rate</option>
                                </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="charge_type" class="form-label">Charge Type</label>
                                    <input id="charge_type" type="text" class="form-control " name="charge_type" value="'.$carrier->charge_type.'" required="">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <strong> Select Documents</strong>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="mc_letter" class="form-label">MC Authority Letter</label>';
                                    $extension = \File::extension($carrier->mc_letter);
                                    $link = url('admin/carrier_img/' . $carrier->mc_letter);
                                    $html = $this->getHtml($extension, $link, $html);
                                    $html .= '
                                    <input id="mc_letter" type="file" class="form-control " name="mc_letter">
                                </div>
                                <div class="col-md-4">
                                    <label for="w_form" class="form-label">W-9 Form (Tax Paying Proof)</label>';
                                    $extension = \File::extension($carrier->w_form);
                                    $link = url('admin/carrier_img/' . $carrier->w_form);
                                    $html = $this->getHtml($extension, $link, $html);
                                    $html .= '
                                    <input id="w_form" type="file" class="form-control " name="w_form">
                                </div>
                                <div class="col-md-4">
                                    <label for="coi" class="form-label">COI (Certificate of Insurance)</label>';
                                    $extension = \File::extension($carrier->coi);
                                    $link = url('admin/carrier_img/' . $carrier->coi);
                                    $html = $this->getHtml($extension, $link, $html);
                                    $html .= '
                                    <input id="coi" type="file" class="form-control " name="coi">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="noa" class="form-label">NOA (Notice of Assignment)</label>';
                                    $extension = \File::extension($carrier->noa);
                                    $link = url('admin/carrier_img/' . $carrier->noa);
                                    $html = $this->getHtml($extension, $link, $html);
                                    $html .= '
                                    <input id="noa" type="file" class="form-control " name="noa">
                                </div>
                                <div class="col-md-4">
                                    <label for="void_cheque" class="form-label">VOID Cheque</label>';
                                    $extension = \File::extension($carrier->void_cheque);
                                    $link = url('admin/carrier_img/' . $carrier->void_cheque);
                                    $html = $this->getHtml($extension, $link, $html);
                                    $html .= '
                                    <input id="void_cheque" type="file" class="form-control " name="void_cheque">
                                </div>
                                <div class="col-md-4">
                                    <label for="extra_document" class="form-label">Extra Document</label>';
                                    $extension = \File::extension($carrier->extra_document);
                                    $link = url('admin/carrier_img/' . $carrier->extra_document);
                                    $html = $this->getHtml($extension, $link, $html);
                                    $html .= '
                                    <input id="extra_document" type="file" class="form-control " name="extra_document">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="row mb-2">
                                    <strong> Preferred Area</strong>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="all_zones" name="all_zones" '.($carrier->all_zones == 1 ? 'checked': '').'>
                                            <label class="form-check-label" for="all_zones">All Zones</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z0" name="z0" '.($carrier->z0 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z0">Z0</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z1" name="z1" '.($carrier->z1 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z1">Z1</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z2" name="z2" '.($carrier->z2 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z2">Z2</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z3" name="z3" '.($carrier->z3 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z3">Z3</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z4" name="z4" '.($carrier->z4 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z4">Z4</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z5" name="z5" '.($carrier->z5 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z5">Z5</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z6" name="z6" '.($carrier->z6 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z6">Z6</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z7" name="z7" '.($carrier->z7 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z7">Z7</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z8" name="z8" '.($carrier->z8 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z8">Z8</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="z9" name="z9" '.($carrier->z9 == 1 ? 'checked': '').'>
                                        <label class="form-check-label" for="z9">Z9</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label for="zip_code" class="form-label">Zip</label>
                                    <input id="zip_code" type="text" class="form-control " name="zip_code" value="'.$carrier->zip_code.'" required="">
                                                                    </div>
                                <div class="col-md-6">
                                    <label for="city_name" class="form-label">City</label>
                                    <input id="city_name" type="text" class="form-control " name="city_name" value="'.$carrier->city_name.'" required="">
                                                                    </div>
                                <div class="col-md-4">
                                    <label for="state_name" class="form-label">State</label>
                                    <select class="form-control" name="state_name" id="state_name">
                                        <option selected="">Choose State...</option>';
            foreach($states as $state){
                $html .= '<option value="'.$state->id.'" '.($carrier->state_name == $state->id ? 'selected': '').'> '.$state->name.'</option>';
            }
            $html .= '
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="rpm" class="form-label">RPM</label>
                                    <input id="rpm" type="text" class="form-control " name="rpm" value="'.$carrier->rpm.'" required="">
                                </div>
                                <div class="col-md-8">
                                    <label for="street_address" class="form-label">Street Address</label>
                                    <input id="street_address" type="text" class="form-control " name="street_address" value="'.$carrier->street_address.'" required="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea id="comment" class="form-control " name="comment">'.$carrier->comment.'</textarea>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" id="form-submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>';
            $json = [];
            $json['html'] = $html;


            return response($json, 200);
        }

        if (Auth::user()->hasRole('Admin')) {
            $data_array['salesAgnets'] = $salesAgnets;
            return view('carrier.edit', $data_array);
        }else{
            if($carrier->user_id == Auth::user()->id){
                return view('carrier.edit', $data_array);
            }
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");

        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CarrierUpdateRequest $request, Carrier $carrier)
    {
        if (! Gate::allows('carriers-edit', $carrier)) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

//        if (Auth::user()->hasRole('Sales Agent') && $carrier->user_id != Auth::user()->id) {
//            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
//        }

        if (Auth::user()->hasRole('Admin')) {
            $carrier->user_id = $request->agent_id;
        }

        $carrier->mc_number = $request->mc_number;
        $carrier->dot = $request->dot;
        $carrier->name = $request->name;
        $carrier->email = $request->email;
        $carrier->number = $request->number;
        $carrier->company_name = $request->company_name;
        $carrier->truck_type = $request->truck_type;
        $carrier->truck_size = $request->truck_size;
        $carrier->maximum_weight = $request->maximum_weight;
        $carrier->payment_type = $request->payment_type;
        $carrier->percent_flat = $request->percent_flat;
        $carrier->charge_type = $request->charge_type;



        if ($request->hasFile('mc_letter')){
            $carrier->mc_letter =  $this->saveImage($request, 'mc_letter');
        }
        if ($request->hasFile('w_form')){
            $carrier->w_form =  $this->saveImage($request, 'w_form');
        }
        if ($request->hasFile('coi')){
            $carrier->coi =  $this->saveImage($request, 'coi');
        }
        if ($request->hasFile('noa')){
            $carrier->noa =  $this->saveImage($request, 'noa');
        }
        if ($request->hasFile('void_cheque')){
            $carrier->void_cheque =  $this->saveImage($request, 'void_cheque');
        }
        if ($request->hasFile('extra_document')){
            $carrier->extra_document =  $this->saveImage($request, 'extra_document');
        }

        $carrier->all_zones = $request->all_zones ? 1 : 0;
        $carrier->z0 = $request->z0 ? 1 : 0;
        $carrier->z1 = $request->z1 ? 1 : 0;
        $carrier->z2 = $request->z2 ? 1 : 0;
        $carrier->z3 = $request->z3 ? 1 : 0;
        $carrier->z4 = $request->z4 ? 1 : 0;
        $carrier->z5 = $request->z5 ? 1 : 0;
        $carrier->z6 = $request->z6 ? 1 : 0;
        $carrier->z7 = $request->z7 ? 1 : 0;
        $carrier->z8 = $request->z8 ? 1 : 0;
        $carrier->z9 = $request->z9 ? 1 : 0;

        $carrier->street_address = $request->street_address;
        $carrier->city_name = $request->city_name;
        $carrier->state_name = $request->state_name;
        $carrier->zip_code = $request->zip_code;
        $carrier->comment = $request->comment;
        $carrier->rpm = $request->rpm;

        if ($request->ajax()){
            $array_msg = [];
            $array_msg['message'] = 'Something Went Wrong';
            $array_msg['status'] = 'error';
            $success = $carrier->save();
            if ($success){
                $array_msg['status'] = 'success';
                $array_msg['message'] = 'Carrier Update Successfully';

                return response($array_msg, 200);
            }
            return response($array_msg, 200);

        }

        if($carrier->save()){

            $msg = 'Carrier Update Successfully';
        }else{
            $msg = 'Something Went Wrong';
        }
        return redirect()->route('carriers.index')->with("status", $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carrier $carrier)
    {
        $this->authorize('carriers-delete');
        // if (! Gate::allows('carriers-delete', $carrier)) {
        //     return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        // }

        if($carrier->delete()){
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
            $imageName = "im2k".date('YmdHis').'_'.uniqid().'.'.$image->extension();
            $image->move(public_path('admin/carrier_img'),$imageName);
        }
        return $imageName;
    }

    public function sendEmails(Carrier $carrier){

        if (! Gate::allows('send-email', $carrier)) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

//        $data["email"] = "bzumultan0@gmail.com";
//        $data["email"] = "h@in2digitals.com";

        $setting = Setting::find(1);
        $data["email"] = $setting['email'];

        $this->sendCarrierInfoToDispatcher($carrier, $data);
        return redirect()->back();
    }

    public function assignTo(Request $request, Carrier $carrier){

        if (! Gate::allows('assign-carrier', $carrier)) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        $assignedDispatchId = $carrier->assign_to ?? '';
        if (empty($request->dispatchId)){
            $request->dispatchId = null;
        }
        $carrier->assign_to = $request->dispatchId;

        if ($carrier->save()){

            if (!empty($request->dispatchId)){
                $users = User::findOrFail($request->dispatchId);

                $data["email"] = $users['email'];
                $this->sendCarrierInfoToDispatcher($carrier, $data);
                $message = 'Dispatch assign to';

                if (!empty($assignedDispatchId)){
                    $users = User::findOrFail($assignedDispatchId);

                    $data["email"] = $users['email'];

                    $this->sendCarrierTakenEmail($carrier, $data);
                }
            }else{

                $users = User::findOrFail($assignedDispatchId);

                $data["email"] = $users['email'];

                $this->sendCarrierTakenEmail($carrier, $data);
                $message = 'Carrier Taken';
            }
            return response(['message' => $message.' Successfully', 'status' => true ], 200);
        }
        return response(['message' => 'Dispatch Not assign ', 'status' => false ], 200);
    }

    private function sendCarrierInfoToDispatcher($carrier, $data){
        $data["title"] = $carrier->name ." Details With Attachments";
        $data["carrier_data"] = $carrier;
        $data["carrier_data"]['user_name'] = $carrier->user->full_name;
        $data["carrier_data"]['truck_type'] = $carrier->truckType->name;
        $data["carrier_data"]['truck_size'] = $carrier->truckSize->name;
        $data["carrier_data"]['payment_type'] = $carrier->paymentType->name;
        $data["carrier_data"]['state_name'] = $carrier->state->name;

        $mc_letter = $w_form = $coi = $noa = $void_cheque = $extra_document = '';
        if($carrier->mc_letter){
            $mc_letter = public_path('admin/carrier_img/'.$carrier->mc_letter);
        }
        if($carrier->w_form){
            $w_form = public_path('admin/carrier_img/'.$carrier->w_form);
        }
        if($carrier->coi){
            $coi = public_path('admin/carrier_img/'.$carrier->coi);
        }
        if($carrier->noa){
            $noa = public_path('admin/carrier_img/'.$carrier->noa);
        }
        if($carrier->void_cheque){
            $void_cheque = public_path('admin/carrier_img/'.$carrier->void_cheque);
        }
        if($carrier->extra_document){
            $extra_document = public_path('admin/carrier_img/'.$carrier->extra_document);
        }
        $files = [
            $mc_letter,
            $w_form ,
            $coi,
            $noa,
            $void_cheque,
            $extra_document,
        ];

        $files = array_filter($files);

        $response = Mail::send('emails.carrierDetailsEmail', $data, function($message)use($data, $files) {
            $message->to($data["email"])
                ->subject($data["title"]);

            foreach ($files as $file){
                $message->attach($file);
            }
        });

        return true;
    }

    private function sendCarrierTakenEmail($carrier, $data) {

        $data["title"] = $carrier->name ." | This Carrier Is Taken From You";
        $data["carrier_data"] = $carrier;
        $response = Mail::send('emails.carrierTaken', $data, function($message)use($data) {
            $message->to($data["email"])
                ->subject($data["title"]);
        });

        return true;
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
}
