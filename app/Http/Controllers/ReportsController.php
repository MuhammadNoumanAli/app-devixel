<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\TruckType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use DB;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportsController extends Controller
{

    public function carriersReport(Request $request)
    {
        if (! Gate::allows('carriers-report')) {
            return redirect()->route('carriers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        if($request->ajax()){
            $startDate = date('Y-m-d', strtotime($request->start_date));
            $endDate = date('Y-m-d', strtotime($request->end_date));
        }else{
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDay(30)->toDateString();
        }

        $dispatchers = User::role(['Dispatcher', 'Manager', 'Dispatch Supervisor'])->get();


        $data_array = [];
//        $carriers = Carrier::latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();

        // if (Auth::user()->hasRole('Admin|Sales Manager')) {
        if (Auth::user()->can('report-all-carriers')) {
            $carriers = Carrier::with('user', 'truckType', 'assignedTo')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if(isset($request->agentId) && $request->agentId != 'all'){
                $carriers = Carrier::with('user', 'truckType', 'assignedTo')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('user_id', '=', $request->agentId)->get();
            }
        }else{
            $carriers = Carrier::with('user', 'truckType', 'assignedTo')->latest()->where('user_id','=', auth()->user()->id)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
//            $carriers = Carrier::with('truckType', 'user', 'truckSize', 'paymentType')->latest()->where('user_id','=', auth()->user()->id)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
        }
        if($request->ajax()){
            foreach ($carriers as $key => $carrier){
                $newArray = [];
                $newArray[] = ++$key;
                $newArray[] = $carrier->user->first_name.' '.$carrier->user->last_name;
                $newArray[] = $carrier->mc_number;
                $newArray[] = $carrier->name;
                $newArray[] = $carrier->number;
                $newArray[] = $carrier->truckType->name;

                $option = '';

                if (Gate::allows('assign-carrier')) {
                    $option = '<select onchange="getDispatchId(this, ' . $carrier->id . ')" style="font-size:11px; padding: 0.275rem 2.25rem 0.275rem 0.75rem;" class="form-select" aria-label="Default select example">';
                    $option .= '<option value="" selected>--Select--</option>'; // Set a default empty option with "selected" attribute
                    foreach ($dispatchers as $dispatcher) {
                        $selected = ($dispatcher->id == $carrier->assign_to) ? 'selected' : ''; // Check if it's the assigned dispatcher
                        $option .= '<option value="' . $dispatcher->id . '" ' . $selected . '>' . $dispatcher->full_name . '</option>';
                    }
                    $option .= '</select>';
                } else {
                    if (!empty($carrier->assignedTo->full_name)) {
                        $option .= $carrier->assignedTo->full_name;
                    } else {
                        $option .= 'N/A';
                    }
                }
                $newArray[] = $option;
                $anchor = '';
                if (Gate::allows('carriers-view')) {
                    $anchor = '<button style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link viewcarrierdetails pe-1" data-id="'. $carrier->id .'"><i class="ri-eye-fill" aria-hidden="true"></i></button>';
                }
//                $anchor = '<a class="pe-1" href="' . route('carriers.show', $carrier->id) . '"><i class="ri-eye-fill" aria-hidden="true"></i></a>';
                if (Gate::allows('carriers-edit')) {
                    $anchor .= '<button style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link editcarrierdetails pe-1" data-id="'. $carrier->id .'"><i class="ri-edit-2-fill" aria-hidden="true"></i></button>';

//                    $anchor .= '<a class="pe-1" href="' . route('carriers.edit', $carrier->id) . '"><i class="ri-edit-2-fill" aria-hidden="true"></i></a>';
                }
                if (Gate::allows('carriers-delete')) {
                    // Replace double curly braces with proper concatenation
                    $anchor .= '<a href="javascript: void(0)" onclick="return confirmAndSubmit(' . $carrier->id . ')">
                            <i class="ri-delete-bin-3-fill"></i>
                        </a>
                        <form id="delete-record-' . $carrier->id . '" action="' . route('carriers.destroy', $carrier->id) . '" method="POST" class="d-none">
                            ' . csrf_field() . ' ' . method_field('DELETE') . '
                        </form>';
                }
                if (Gate::allows('send-email')) {
                    $anchor .= '<a class="ps-1" href="' . route('carriers.email', $carrier->id) . '" title="Send Email"><i class="ri-mail-send-line"></i> </a>';
                }
                $newArray[] = $anchor;
                $data_array[] = $newArray;
            }
            return response($data_array, 200);
        }else{
            $salesAgnets = User::role('Sales Agent')->get();
            $data_array['salesAgnets'] = $salesAgnets;
            $data_array['carriers'] = $carriers;

            return view('reports.carriers', $data_array);
        }
    }

    public function dispatchersReport(Request $request)
    {
        if (! Gate::allows('dispatchers-report')) {
            return redirect()->route('dispatchers.index')->with("error", "YOU HAVE NOT THE RIGHT PERMISSIONS.");
        }

        if($request->ajax()){
            $startDate = date('Y-m-d', strtotime($request->start_date));
            $endDate = date('Y-m-d', strtotime($request->end_date));
        }else{
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDay(30)->toDateString();
        }

        if (Auth::user()->can('report-all-dispatchers')) {
//            $dispatchers = Dispatch::with('user')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            $dispatchers = Dispatch::with('user')->latest()->whereBetween('load_date', [$startDate, $endDate])->get();
            if(isset($request->dispatcherId) && $request->dispatcherId != 'all'){
//                $dispatchers = Dispatch::with('user')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('user_id', '=', $request->dispatcherId)->get();
                $dispatchers = Dispatch::with('user')->latest()->whereBetween('load_date', [$startDate, $endDate])->where('user_id', '=', $request->dispatcherId)->get();
            }
        }else{
//            $dispatchers = Dispatch::with('user')->latest()->where('user_id','=', auth()->user()->id)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            $dispatchers = Dispatch::with('user')->latest()->where('user_id','=', auth()->user()->id)->whereBetween('load_date', [$startDate, $endDate])->get();
        }
        $data_array = [];
        if($request->ajax()){
            foreach ($dispatchers as $key => $dispatcher){
                $newArray = [];
                $newArray[] = ++$key;
                $newArray[] = $dispatcher->user->first_name.' '.$dispatcher->user->last_name;
                $newArray[] = $dispatcher->mc_number;
                $newArray[] = $dispatcher->pick_location;
                $newArray[] = $dispatcher->delivery_location;
                $newArray[] = date('F d, Y', strtotime($dispatcher->load_date));
                $newArray[] = $dispatcher->owner_name;
                $newArray[] = '$'.$dispatcher->rate;
                $newArray[] = $dispatcher->broker_company_name;
                $status = '';
                if($dispatcher->is_cancel){
                    $status = 'cancel-dot';
                }
                if (Gate::allows('can-load-cancel')){
                    $cancelStatus ='<div class="is_cancel dot-status '.$status.'" data-dispatch-id="'.$dispatcher->id.'" data-dispatch-status="'.$dispatcher->is_cancel.'" onclick="return cancelLoad(' . $dispatcher->id . ')"></div>';
                }else{
                    $cancelStatus ='<div class="is_cancel dot-status '.$status.'" data-dispatch-id="'.$dispatcher->id.'" data-dispatch-status="'.$dispatcher->is_cancel.'"></div>';
                }
                $newArray[] = $cancelStatus;
                $anchor = '';
                if (Gate::allows('dispatchers-view')) {
                    $anchor = '<button style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link viewdetails pe-1" data-id="'. $dispatcher->id .'"><i class="ri-eye-fill" aria-hidden="true"></i></button>';

//                    $anchor = '<a class="pe-1" href="' . route('dispatchers.show', $dispatcher->id) . '"><i class="ri-eye-fill" aria-hidden="true"></i></a>';
                }
                if (Gate::allows('dispatchers-edit')) {
                    $anchor .= '<button title="Edit Record" style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link editdispatcherdetails pe-1" data-id="'. $dispatcher->id .'"><i class="ri-edit-2-fill" aria-hidden="true"></i></button>';

//                    $anchor .= '<a class="pe-1" href="' . route('dispatchers.edit', $dispatcher->id) . '"><i class="ri-edit-2-fill" aria-hidden="true"></i></a>';
                }

                if (Gate::allows('dispatcher-edit-attachment')){
                    $anchor .= '<a class="pe-1" title="Edit Document" href="' . route('dispatchers.editAttachDocument', $dispatcher->id) . '"><i class="ri-file-edit-line" aria-hidden="true"></i></a>';
                }


                if (Gate::allows('dispatchers-delete')) {
                    // Replace double curly braces with proper concatenation
                    $anchor .= '<a href="javascript: void(0)" onclick="return confirmAndSubmit(' . $dispatcher->id . ')">
                            <i class="ri-delete-bin-3-fill"></i>
                        </a>
                        <form id="delete-record-' . $dispatcher->id . '" action="' . route('dispatchers.destroy', $dispatcher->id) . '" method="POST" class="d-none">
                            ' . csrf_field() . ' ' . method_field('DELETE') . '
                        </form>';
                }


                $newArray[] = $anchor;
                $data_array[] = $newArray;
            }
            return response($data_array, 200);
        }else {
            $dispatch_users = User::role('Dispatcher')->get();
            $data_array['dispatch_users'] = $dispatch_users;
            $data_array['dispatchers'] = $dispatchers;
            return view('reports.dispatchers', $data_array);
        }
    }

    public function truckTypesReport(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $truckType = $request->truckType;
//        $query = Dispatch::join('carriers', 'dispatches.mc_number', '=', 'carriers.mc_number')
//            ->join('truck_types', 'carriers.truck_type', '=', 'truck_types.id')
//            ->join('users', 'carriers.user_id', '=', 'users.id')
//            ->select('dispatches.id as dispatch_id', 'dispatches.*', 'truck_types.name as truck_name','truck_types.*', 'users.*')
//            ->where('dispatches.load_date', '>=', $startDate)
//            ->where('dispatches.load_date', '<=', $endDate);
//        if(isset($request->truckType) && $request->truckType != 'all'){
//            $query->where('carriers.truck_type', $truckType);
//        }

        $query = Dispatch::with(['carrier.truckType', 'user'])
            ->where('load_date', '>=', $startDate)
            ->where('load_date', '<=', $endDate);

        if(isset($request->truckType) && $request->truckType != 'all'){
            $query->whereHas('carrier', function ($query) use ($request) {
                $query->where('truck_type', $request->truckType);
            });
        }

        $dispatchersWithTruck = $query->get();
        $data_array = [];
        if($request->ajax()){
            foreach ($dispatchersWithTruck as $key => $dispatcher){
                $newArray = [];
                $newArray[] = ++$key;
                $newArray[] = $dispatcher->user->first_name.' '.$dispatcher->user->last_name;
                $newArray[] = $dispatcher->mc_number;
                $newArray[] = $dispatcher->carrier->truckType->name;
                $newArray[] = '$'.$dispatcher->rate;
                $newArray[] = $dispatcher->percentage;
                $newArray[] = '$'.$dispatcher->receivable;
                $newArray[] = date('F d, Y', strtotime($dispatcher->load_date));
                $newArray[] = date('F d, Y', strtotime($dispatcher->created_at));
                $data_array[] = $newArray;
            }
            return response($data_array, 200);
        }else {
            $data_array['truckTypes'] = TruckType::get();
            return view('reports.truck_types', $data_array);
        }
    }

    public function downloadCarrierXLS(Request $request){
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));

        $data_array = [];
        if (Auth::user()->can('xls-download-all-carriers')) {
            $carriers = Carrier::with('truckType', 'user', 'truckSize', 'paymentType', 'assignedTo')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if($request->agentId != 'all' && $request->agentId !== 'undefined'){
                $carriers = Carrier::with('truckType', 'user', 'truckSize', 'paymentType', 'assignedTo')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('user_id', '=', $request->agentId)->get();
            }
        }else{
            $carriers = Carrier::with('truckType', 'user', 'truckSize', 'paymentType', 'assignedTo')->latest()->where('user_id','=', auth()->user()->id)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
        }

        $data = [];
        foreach ($carriers as $key => $carrier){
//            if (Auth::user()->hasRole('Admin')) {
            $formattedData = [
                'Sr. #' => ++$key ,
                'Agent Name' => $carrier->user->first_name ." ".$carrier->user->last_name ,
                'Assign To' => !empty($carrier->assignedTo) ? $carrier->assignedTo->full_name : 'N/A' ,
                'MC Number' => $carrier->mc_number ,
                'DOT' => $carrier->dot ,
                'Carrier Name' => $carrier->name ,
                'Carrier Email' => $carrier->email ,
                'Carrier Number' => $carrier->number ,
                'Carrier Company' => $carrier->company_name ,
                'RPM' => '$'.$carrier->rpm ,
                'Truck Type' => $carrier->truckType->name ,
                'Truck Size' => $carrier->truckSize->name ,
                'Maximum Weight' => $carrier->maximum_weight. "LBS" ,
                'Payment Type' => $carrier->paymentType->name ,
                'Charge Type' => $carrier->charge_type ,
                'Address' => $carrier->street_address ,
                'City' => $carrier->city_name ,
                'State' => $carrier->state_name ,
                'Zip Code' => $carrier->zip_code ,
                'All Zones' => $carrier->all_zones == 1 ? 'Yes' : '-' ,
                'Z0' => $carrier->z0 == 1 ? 'Yes' : '-' ,
                'Z1' => $carrier->z1 == 1 ? 'Yes' : '-' ,
                'Z2' => $carrier->z2 == 1 ? 'Yes' : '-' ,
                'Z3' => $carrier->z3 == 1 ? 'Yes' : '-' ,
                'Z4' => $carrier->z4 == 1 ? 'Yes' : '-' ,
                'Z5' => $carrier->z5 == 1 ? 'Yes' : '-' ,
                'Z6' => $carrier->z6 == 1 ? 'Yes' : '-' ,
                'Z7' => $carrier->z7 == 1 ? 'Yes' : '-' ,
                'Z8' => $carrier->z8 == 1 ? 'Yes' : '-' ,
                'Z9' => $carrier->z9 == 1 ? 'Yes' : '-' ,
                'MC Authority Letter' => $carrier->mc_letter != '' ? url('admin/carrier_img/'.$carrier->mc_letter) : '-',
                'W-9 Form' => $carrier->w_form != '' ? url('admin/carrier_img/'.$carrier->w_form) : '-',
                'Certificate of Insurance' => $carrier->coi != '' ? url('admin/carrier_img/'.$carrier->coi) : '-',
                'Notice of Assignment' => $carrier->noa != '' ? url('admin/carrier_img/'.$carrier->noa) : '-',
                'VOID Cheque' => $carrier->void_cheque != '' ? url('admin/carrier_img/'.$carrier->void_cheque) : '-',
                'Additional Document' => $carrier->extra_document != '' ? url('admin/carrier_img/'.$carrier->extra_document) : '-',
                'Comment' => $carrier->comment != '' ? $carrier->comment : '-',
            ];
            $data[] = $formattedData;
        }

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Add data to the spreadsheet
        $sheet = $spreadsheet->getActiveSheet();
        // Set headers
//        if (Auth::user()->hasRole('Admin')) {
        $headers = ['Sr #', 'Agent Name', 'Assign To', 'MC Number', 'DOT', 'Carrier Name', 'Carrier Email', 'Carrier Number', 'Carrier Company', 'RPM', 'Truck Type', 'Truck Size', 'Maximum Weight', 'Payment Type', 'Charge Type', 'Address', 'City', 'State', 'Zip Code', 'All Zones', 'Z0', 'Z1', 'Z2', 'Z3', 'Z4', 'Z5', 'Z6', 'Z7', 'Z8', 'Z9', 'MC Authority Letter', 'W-9 Form', 'Certificate of Insurance', 'Notice of Assignment', 'VOID Cheque', 'Additional Document', 'Comment'];
//        }else{
//            $headers = ['Sr #', 'MC Number', 'DOT', 'Carrier Name', 'Carrier Email', 'Carrier Number', 'Carrier Company', 'RPM', 'Truck Type', 'Truck Size', 'Payment Type', 'Charge Type', 'Address', 'City', 'State', 'Zip Code', 'Z0', 'Z1', 'Z2', 'Z3', 'Z4', 'Z5', 'Z6', 'Z7', 'Z8', 'Z9', 'MC Authority Letter', 'W-9 Form', 'Certificate of Insurance', 'Notice of Assignment', 'VOID Cheque', 'Additional Document', 'Comment'];
//        }
        $column = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($column, 1, $header);
            $column++;
        }


        $sheet->getStyle('A1:AE1' )->getFont()->setBold(true);
        // set height
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(36);

        // Set heading row height
        $sheet->getRowDimension(1)->setRowHeight(26); // Adjust the height as needed

        // Apply border and alignment to the heading cells
        $lastColumn = $sheet->getHighestColumn();
        $headingRange = 'A1:' . $lastColumn . '1';
        $borderStyle = [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['rgb' => '87CEEB'],
            'weight' => 'thick',
            'borderSize' => 20,
        ];
        $sheet->getStyle($headingRange)->getBorders()->getBottom()->applyFromArray($borderStyle);
        $sheet->getStyle($headingRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);


        // Get the style for cells A1 to A5 and set the background color and text color
        $cellRange = 'A1:H1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8FE3B1'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells J1 to M1 and set the background color and text color
        $cellRange = 'J1:N1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDD9C4'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code



        // Get the style for cells J1 to M1 and set the background color and text color
        $cellRange = 'O1:R1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2DCDB'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code




        // Get the style for cells R1 to AA1 and set the background color and text color
        $cellRange = 'S1:AC1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D8E4BC'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells R1 to AA1 and set the background color and text color
        $cellRange = 'AD1:AJ1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DAEEF3'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code

        // Fill in data from the loop
        $row = 2; // Start from the second row
        foreach ($data as $item) {
            $column = 1;
            foreach ($item as $value) {
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }

            // Apply currency formatting to the 'Rate' and 'Receivable' columns
            $spreadsheet->getActiveSheet()
                ->getStyle('J2:J' . $row) // Apply to the 'Rate' column
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $row++;
        }

        // Set the width of the columns based on the heading text length
        foreach ($sheet->getRowIterator(1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $columnWidth = $sheet->getColumnDimension($cell->getColumn())->getWidth();
                $newWidth = max($columnWidth, strlen($cell->getValue()) + 2);
                $sheet->getColumnDimension($cell->getColumn())->setWidth($newWidth);

//                $cellStyle = $cell->getStyle();
//                $alignment = $cellStyle->getAlignment();
//                $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // Generate the timestamp
        $timestamp = date('YmdHis');

        // Generate a filename for the Excel file
        $filename = $startDate . '_' . $endDate . '_' . $timestamp . 'AgentList.xlsx';

        // Create a new Excel writer
        $writer = new Xlsx($spreadsheet);

        // Save the Excel file
        $writer->save($filename);

        // Return the file as a download response
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function downloadDispatcherXLS(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));

        $data_array = [];

        if (Auth::user()->can('xls-download-all-dispatchers')) {
            $dispatchers = Dispatch::where('is_cancel', 0)->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if(isset($request->dispatchId) && $request->dispatchId != 'all'){
                $dispatchers = Dispatch::latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('user_id', '=', $request->dispatchId)->get();
            }
        }else{
            $dispatchers = Dispatch::latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('user_id','=', auth()->user()->id)->get();
        }
        $data = [];
        foreach ($dispatchers as $key => $dispatcher){
//            if (Auth::user()->hasRole('Admin')) {

            $formattedData = [
                'Sr. #' => ++$key ,
                'Dispatcher Name' => $dispatcher->user->first_name ." ".$dispatcher->user->last_name ,
                'MC Number' => $dispatcher->mc_number ,
                'Carrier Name' => $dispatcher->owner_name ,
                'Load Number' => $dispatcher->load_number ,
                'Pick Location' => $dispatcher->pick_location ,
                'Delivery Location' => $dispatcher->delivery_location ,
                'Load Date' => $dispatcher->load_date ,
                'Pick Date' => $dispatcher->pick_date ,
                'Delivery Date' => $dispatcher->delivery_date ,
                'Truck Number' => $dispatcher->truck_number ,
                'Trailer No' => $dispatcher->trailer_number ,
                'Total Miles' => $dispatcher->total_miles.' mi' ,
                'Rate' => $dispatcher->rate ,
                'Percentage' => $dispatcher->percentage."%" ,
                'Receivable' => $dispatcher->receivable ,
                'Driver Name' => $dispatcher->driver_name ,
                'Driver Contact No.' => $dispatcher->driver_number ,
                'Rate Confirmation' => $dispatcher->rate_confirmation != '' ? url('admin/dispatch_documents/'.$dispatcher->rate_confirmation) : '-',
                'BOL/POD' => $dispatcher->bol_pod != '' ? url('admin/dispatch_documents/'.$dispatcher->bol_pod) : '-',
                'Additional Doc' => $dispatcher->additional_doc != '' ? url('admin/dispatch_documents/'.$dispatcher->additional_doc) : '-',
                'Broker Company Name' => $dispatcher->broker_company_name ,
                'Broker MC Number' => $dispatcher->broker_mc ,
                'Broker Contact Number' => $dispatcher->broker_number ,
                'Broker Email' => $dispatcher->broker_email ,
                'Broker Representative Name' => $dispatcher->broker_rep_name ,
            ];
//            }else{
//                $formattedData = [
//                    'Sr. #' => ++$key ,
//                    'MC Number' => $dispatcher->mc_number ,
//                    'Owner Name' => $dispatcher->owner_name ,
//                    'Load Number' => $dispatcher->load_number ,
//                    'Pick Location' => $dispatcher->pick_location ,
//                    'Delivery Location' => $dispatcher->delivery_location ,
//                    'Load Date' => $dispatcher->load_date ,
//                    'Pick Date' => $dispatcher->pick_date ,
//                    'Delivery Date' => $dispatcher->delivery_date ,
//                    'Truck Number' => $dispatcher->truck_number ,
//                    'Trailer No' => $dispatcher->trailer_number ,
//                    'Total Miles' => $dispatcher->total_miles ,
//                    'Rate' => '$'.$dispatcher->rate ,
//                    'Percentage' => $dispatcher->percentage."%" ,
//                    'Receivable' => '$'.$dispatcher->receivable ,
//                    'Driver Name' => $dispatcher->driver_name ,
//                    'Driver Contact No.' => $dispatcher->driver_number ,
//                    'Rate Confirmation' => $dispatcher->rate_confirmation != '' ? url('admin/dispatch_documents/'.$dispatcher->rate_confirmation) : '-',
//                    'BOL/POD' => $dispatcher->bol_pod != '' ? url('admin/dispatch_documents/'.$dispatcher->bol_pod) : '-',
//                    'Additional Doc' => $dispatcher->additional_doc != '' ? url('admin/dispatch_documents/'.$dispatcher->additional_doc) : '-',
//                    'Broker Company Name' => $dispatcher->broker_company_name ,
//                    'Broker MC Number' => $dispatcher->broker_mc ,
//                    'Broker Contact Number' => $dispatcher->broker_number ,
//                    'Broker Email' => $dispatcher->broker_email ,
//                    'Broker Representative Name' => $dispatcher->broker_rep_name ,
//                ];
//            }
            $data[] = $formattedData;
        }

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Add data to the spreadsheet
        $sheet = $spreadsheet->getActiveSheet();
        // Set headers
//        if (Auth::user()->hasRole('Admin')) {
        $headers = ['Sr #', 'Dispatcher Name', 'MC Number', 'Carrier Name', 'Load Number', 'Pick Location', 'Delivery Location', 'Load Date', 'Pick Date', 'Delivery Date', 'Truck Number', 'Trailer Number', 'Total Miles', 'Rate', 'Percentage', 'Receivable', 'Driver Name', 'Driver Contact No', 'Rate Confirmation', 'BOL/POD', 'Additional Doc.', 'Company Name', 'MC Number', 'Contact Number', 'Email', 'Representative Name'];
//        }else{
//            $headers = ['Sr #', 'MC Number', 'DOT', 'Owner Name', 'Load Number', 'Pick Location', 'Delivery Location', 'Load Date', 'Pick Date', 'Delivery Date', 'Truck Number', 'Trailer Number', 'Total Miles', 'Rate', 'Percentage', 'Receivable', 'Driver Name', 'Driver Contact No', 'Rate Confirmation', 'BOL/POD', 'Additional Doc.', 'Company Name', 'MC Number', 'Contact Number', 'Email', 'Representative Name'];
//        }
        $column = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($column, 1, $header);
            $column++;
        }


        $sheet->getStyle('A1:Z1' )->getFont()->setBold(true);
        // set height
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(36);

        // Set heading row height
        $sheet->getRowDimension(1)->setRowHeight(26); // Adjust the height as needed

        // Apply border and alignment to the heading cells
        $lastColumn = $sheet->getHighestColumn();
        $headingRange = 'A1:' . $lastColumn . '1';
        $borderStyle = [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['rgb' => '87CEEB'],
            'weight' => 'thick',
            'borderSize' => 20,
        ];
        $sheet->getStyle($headingRange)->getBorders()->getBottom()->applyFromArray($borderStyle);
        $sheet->getStyle($headingRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);


        // Get the style for cells A1 to J5 and set the background color and text color
        $cellRange = 'A1:J1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8FE3B1'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells K1 to L1 and set the background color and text color
        $cellRange = 'K1:L1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDD9C4'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells M1 to P1 and set the background color and text color
        $cellRange = 'M1:P1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2DCDB'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells Q1 to R1 and set the background color and text color
        $cellRange = 'Q1:R1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D8E4BC'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells S1 to U1 and set the background color and text color
        $cellRange = 'S1:U1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DAEEF3'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code


        // Get the style for cells S1 to U1 and set the background color and text color
        $cellRange = 'V1:Z1';
        $style = $sheet->getStyle($cellRange);

        // Set the background color
        $fill = $style->getFill();
        $fill->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FDE9D9'); // Replace 'FF0000' with the desired background color code

        // Set the text color
        $font = $style->getFont();
        $font->getColor()->setRGB('000000'); // Replace 'FFFFFF' with the desired text color code

        // Fill in data from the loop
        $row = 2; // Start from the second row
        foreach ($data as $item) {
            $column = 1;
            foreach ($item as $value) {
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }

            // Apply currency formatting to the 'Rate' and 'Receivable' columns
            $spreadsheet->getActiveSheet()
                ->getStyle('N2:N' . $row) // Apply to the 'Rate' column
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            // Apply currency formatting to the 'Rate' and 'Receivable' columns
            $spreadsheet->getActiveSheet()
                ->getStyle('P2:P' . $row) // Apply to the 'Rate' column
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);


            $row++;
        }

        // Set the width of the columns based on the heading text length
        foreach ($sheet->getRowIterator(1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $columnWidth = $sheet->getColumnDimension($cell->getColumn())->getWidth();
                $newWidth = max($columnWidth, strlen($cell->getValue()) + 2);
                if ($cell->getColumn() === 'N') {
                    $sheet->getColumnDimension($cell->getColumn())->setWidth(15); // Set the desired width
                }else{
                    $sheet->getColumnDimension($cell->getColumn())->setWidth($newWidth);
                }
            }
        }

        // Generate the timestamp
        $timestamp = date('YmdHis');

        // Generate a filename for the Excel file
        $filename = $startDate . '_' . $endDate . '_' . $timestamp . 'DispatcherList.xlsx';

        // Create a new Excel writer
        $writer = new Xlsx($spreadsheet);

        // Save the Excel file
        $writer->save($filename);

        // Return the file as a download response
        return response()->download($filename)->deleteFileAfterSend(true);
    }

}
