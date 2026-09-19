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
    public function index(Request $request)
    {
        if (!auth()->user()->can('truck-types-list') && !auth()->user()->can('list-trucks') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
        $perPage = in_array((int)$request->input('per_page'), [10, 15, 20, 25, 50]) ? (int)$request->input('per_page') : 10;
        $truckTypes = TruckType::latest()->paginate($perPage)->withQueryString();
        return view('truckTypes.index', [
            'truckTypes' => $truckTypes,
            'truck_types' => $truckTypes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('truck-types-create') && !auth()->user()->can('add-truck') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
        return view('truckTypes.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TruckTypeRequest $request)
    {
        if (!auth()->user()->can('truck-types-create') && !auth()->user()->can('add-truck') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
        $truck_type = new TruckType();
        $truck_type->name = $request->name;
        $truck_type->commission = $request->commission;
        if ($truck_type->save()){
            return redirect()->route('truck-types.index')->with('status', 'Truck Added Successfully');
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
        if (!auth()->user()->can('truck-types-edit') && !auth()->user()->can('edit-truck') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
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
                        <input id="name" type="text" class="form-control" name="name" value="'.e($truckType->name).'" required autocomplete="name">
                    </div>
                    <div class="col-md-6">
                        <label for="commission" class="form-label">Commission (%)</label>
                        <input id="commission" type="number" step="0.01" class="form-control" name="commission" value="'.e($truckType->commission).'">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>';

            return response(['html' => $html], 200);
        }
        return view('truckTypes.edit', compact('truckType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TruckType $truckType)
    {
        if (!auth()->user()->can('truck-types-edit') && !auth()->user()->can('edit-truck') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'commission' => 'nullable|numeric|between:0,100',
        ]);

        $truckType->name = $request->name;
        $truckType->commission = $request->commission;

        if ($truckType->save()){
            if ($request->ajax()) {
                return response([
                    'status' => 'success',
                    'message' => 'Truck Type Updated Successfully',
                ], 200);
            }
            return redirect()->route('truck-types.index')->with('status', 'Truck Type Updated Successfully');
        }

        if ($request->ajax()) {
            return response([
                'status' => 'error',
                'message' => 'Something Went Wrong',
            ], 200);
        }
        return redirect()->back()->with('error', 'Something Went Wrong');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TruckType $truckType)
    {
        if (!auth()->user()->can('truck-types-delete') && !auth()->user()->can('delete-truck') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
        if($truckType->delete()){
            return redirect()->back()->with('status', 'Record Deleted Successfully!');
        }else{
            return redirect()->back()->with('error', 'Record Not Deleted!');
        }
    }
}
