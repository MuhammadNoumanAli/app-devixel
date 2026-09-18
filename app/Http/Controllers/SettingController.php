<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{

    public function index(){
        if (! Gate::allows('send-email')) {
            return redirect()->route('home')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }
        $setting = Setting::find(1);
        $data_array['setting'] = $setting;
        return view('settings.index', $data_array);
    }

    public function storeOrUpdate(Request $request)
    {
        $data = $request->all();

        $settingId = $data['id'] ?? null;

        // Check if the record with the given ID exists in the database
        $setting = $settingId ? Setting::find($settingId) : new Setting();

        // Assign data to the model attributes
        $setting->email = $data['email'];

        // Save the record
        if($setting->save()){
            $status = 'status';
            $msg = 'Email Update Successfully';
        }else{
            $status = 'error';
            $msg = 'Something Went Wrong';
        }
        return redirect()->route('settings.index')->with($status, $msg);
    }
}
