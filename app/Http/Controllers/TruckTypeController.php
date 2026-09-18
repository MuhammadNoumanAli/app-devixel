<?php

namespace App\Http\Controllers;

use App\Http\Requests\TruckTypeRequest;
use App\Models\TruckType;
use Illuminate\Http\Request;

class TruckTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('list-trucks');
        $data = [];
        $data['truck_types'] = TruckType::latest()->paginate(10);
        return view('truckTypes.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('add-truck');
        return view('truckTypes.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TruckTypeRequest $request)
    {
        $this->authorize('add-truck');
        $truck_type = new TruckType();
        $truck_type->name = $request->name;
        $truck_type->commission = $request->commission;
        if ($truck_type->save()){
            return redirect()->route('truck-types.index')->with('status', 'Truck Add Successfully');
        }
        return redirect()->route('truck-types.index')->with('error', 'Something Went Wrong');
    }

    /**
     * Display the specified resource.
     */
    public function show(TruckType $truckType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, TruckType $truckType)
    {
        $this->authorize('edit-truck');
        $json = [];
        if ($request->ajax()){
            $html ='<form id="updateTruckTypeForm" method="POST" data-truck-id="'.$truckType->id.'" action="'.route('truck-types.update', $truckType->id).'" class="row g-3" enctype="multipart/form-data">
                <div class="row show-errors" style="display:none;">
                    <div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                        <ul class="alert-danger-li">

                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" class="form-control " name="name" value="'.$truckType->name.'" required="" autocomplete="name" >
                    </div>
                    <div class="col-md-6">
                        <label for="commission" class="form-label">Commission</label>
                        <input id="commission" type="text" class="form-control " name="commission" value="'.$truckType->commission.'">
                    </div>
                </div>


                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>';

            $json['html'] = $html;
        }
        return response($json, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TruckType $truckType)
    {
        $this->authorize('edit-truck');
        $truckType->name = $request->name;
        $truckType->commission = $request->commission;

        $array_msg = [];
        $array_msg['message'] = 'Something Went Wrong';
        $array_msg['status'] = 'error';
        if ($truckType->save()){
            $array_msg['status'] = 'success';
            $array_msg['message'] = 'Truck Update Successfully';

            return response($array_msg, 200);
        }
        return response($array_msg, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TruckType $truckType)
    {
        $this->authorize('delete-truck');
        if($truckType->delete()){
            return redirect()->back()->with('status', 'Record Deleted Successfully!');
        }else{
            return redirect()->back()->with('error', 'Record Not Deleted!');
        }
    }
}
