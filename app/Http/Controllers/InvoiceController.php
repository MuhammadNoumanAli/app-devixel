<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PDF;
use Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InvoiceController extends Controller
{
    function viewDispatcherPDFView(Request $request){
        if($request->ajax()){
//            $startDate = Carbon::now()->subDay(7)->toDateString();
            $startDate = date('Y-m-d', strtotime($request->start_date));
            $endDate = date('Y-m-d', strtotime($request->end_date));
        }else{
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDay(7)->toDateString();
        }
        $data_array = [];
        $dispatchers = $mc_numbers = [];
        if (Auth::user()->hasRole('Admin')) {
//            $mc_numbers = Dispatch::select('mc_number')
//                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
//                ->groupBy('mc_number')
//                ->get();
            $mc_numbers = Dispatch::select('dispatches.mc_number', 'carriers.company_name')
                ->join('carriers', 'dispatches.mc_number', '=', 'carriers.mc_number')
                ->whereBetween(DB::raw('DATE(dispatches.created_at)'), [$startDate, $endDate])
                ->groupBy('dispatches.mc_number', 'carriers.company_name')
                ->get();

            $dispatchers = Dispatch::with('user')->where('is_cancel', 0)->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);

            if (!empty($request->mc_number)) {
                $dispatchers->where('mc_number', $request->mc_number);
            }

            $dispatchers = $dispatchers->get();
        }

        if($request->ajax()){
            // mc number
            $mc_html = '<option value="">-- Select MC Number --</option>';
            foreach ($mc_numbers as $row){
                $selected = ($row->mc_number == $request->mc_number) ? 'selected' : '';
                $mc_html .= '<option value="'.$row->mc_number.'" '.$selected.' >'.$row->mc_number.' - ' .$row->company_name. ' </option>';
            }

            $table_html = '';
            foreach ($dispatchers as $dispatcher){
                $table_html .= '<tr>';
                if ($dispatcher->invoice_generate != 1){
                    $table_html .= '<td><input class="chcktbl" type="checkbox" name="invoice" value="'.$dispatcher->id.'" ></td>';
                }else{
                    $table_html .= '<td></td>';
                }
                $table_html .= '<td>'.$dispatcher->mc_number.'</td>';
                $table_html .= '<td>'.$dispatcher->load_number.'</td>';

                $table_html .= '<td>'.$dispatcher->user->first_name .' '.$dispatcher->user->last_name.'</td>';
                $table_html .= '<td>'.$dispatcher->pick_location.'</td>';
                $table_html .= '<td>'.$dispatcher->delivery_location.'</td>';
                $table_html .= '<td>'.\Carbon\Carbon::parse($dispatcher->delivery_date)->format("F d, Y").'</td>';
                $table_html .= '<td>'.$dispatcher->owner_name.'</td>';
                $table_html .= '<td>$'.$dispatcher->rate.'</td>';

                $table_html .= '<td>
                    <select onchange="changeInvoiceStatus(this, '.$dispatcher->id.')" style="font-size:11px; padding: 0.275rem 2.25rem 0.275rem 0.75rem;width:80px;" class="form-select" aria-label="Default select example">
                        <option selected value="">--Select--</option>
                        <option value="1" '.($dispatcher->invoice_status == 1 ? 'selected' : '') .' >Invoice Sent</option>
                        <option value="2" '.($dispatcher->invoice_status == 2 ? 'selected' : '') .' >Paid</option>
                    </select>
                </td>';

//                $table_html .= ' <td><button class="btn btn-info viewdetails" data-id="'. $dispatcher->id .'" >View Details</button></td>';
                $table_html .= '<td><button style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link viewdetails" data-id="'. $dispatcher->id .'"><i class="ri-eye-fill" aria-hidden="true"></i></button></td>';

                $table_html .= '</tr>';
            }
            $data_array['mc_html'] = $mc_html;
            $data_array['table_html'] = $table_html;
            return response($data_array, 200);

        }else{
            $data_array['mc_numbers'] = $mc_numbers;
            $data_array['dispatchers'] = $dispatchers;
            return view('reports.dispatcher-pdf', $data_array);
        }
    }


    public function downloadDispatcherPDF(Request $request){

//        if($request->ajax()){
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
//        }

        $dispatchers = [];
        if (Auth::user()->hasRole('Admin')) {
            $dispatchers = Dispatch::with('user', 'carrier')->where('is_cancel', 0)->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);

            if (!empty($request->mc_number)) {
                $dispatchers->where('mc_number', $request->mc_number);
            }

            if (empty($request->selected_invoices)){
                return redirect()->back()->with("error", 'Please select some records');
            }

            if (!empty($request->selected_invoices)) {
                $selectedRecords = explode(',', $request->selected_invoices);
                $dispatchers = $dispatchers->whereIn('id', $selectedRecords);

                // change the status of downloaded records
                foreach ($selectedRecords as $item) {
                    $dispatch = Dispatch::find($item);

                    if ($dispatch) {
                        $success = $dispatch->update([
                            'invoice_generate' => '1',
                            'invoice_status' => '1',
                        ]);
                    }
                }
                $dispatchers = $dispatchers->get();
            }else{
                $dispatchers = $dispatchers->where('invoice_generate', '=', '0')->where('is_cancel', 0);
//                $selectedRecords = $dispatchers->get();
                $dispatchers = $dispatchers->get();

//                dd($selectedRecords);

                // change the status of downloaded records
                foreach ($dispatchers as $item) {
                    $dispatch = Dispatch::find($item->id);

                    if ($dispatch) {
                        $success = $dispatch->update([
                            'invoice_generate' => '1',
                            'invoice_status' => '1',
                        ]);
                    }
                }
            }
        }

        // get Invoice Numebr
        $invoice_no = $this->getInvoiceNumber();

        $carrier = [];
        if (!empty($dispatchers[0]->carrier)){
            $carrier = $dispatchers[0]->carrier;
            $data = [
                'title' => $carrier->company_name ,
                'date' => date('d/m/Y'),
                'dispatchers' => $dispatchers,
                'carrier' => $carrier,
                'invoice_no' => $invoice_no,
            ];
        }else{
            $data = [
                'title' => 'Invoice',
                'date' => date('d/m/Y'),
                'dispatchers' => $dispatchers,
                'carrier' => $carrier,
                'invoice_no' => $invoice_no,
            ];
        }

        $pdf = PDF::loadView('generate_pdf', $data);
        // return $pdf->download($data['title'].'.pdf');
        return $pdf->download($data['title'].' '.$invoice_no.'.pdf');
    }

    public function downloadDispatcherXLX(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $dispatchers = [];
        if (Auth::user()->hasRole('Admin')) {
            $dispatchers = Dispatch::with('user', 'carrier')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            $dispatchers = $dispatchers->get();
        }
//        dd($dispatchers);
        $data = [];
        foreach ($dispatchers as $key => $dispatcher){
            $invoice_status = 'N/A';
            if($dispatcher->invoice_status == 1){
                $invoice_status = 'Invoice Sent';
            }else if($dispatcher->invoice_status == 2){
                $invoice_status = 'Paid';
            }
            $formattedData = [
                'Sr. #' => ++$key ,
                'Agent Name' => $dispatcher->user->first_name ." ".$dispatcher->user->last_name ,
                'Carrier Name' => $dispatcher->owner_name ,
                'Company Name' => (isset($dispatcher->carrier->company_name) ? $dispatcher->carrier->company_name : 'N/A') ,
                'Load Number' => $dispatcher->load_number ,
                'Origin' => $dispatcher->pick_location ,
                'Destination' => $dispatcher->delivery_location ,
                'PU Date' => $dispatcher->pick_date ,
                'DL Date' => $dispatcher->delivery_date ,
                'Rate' => $dispatcher->rate ,
                'Percentage' => $dispatcher->percentage ,
                'Receivable' => $dispatcher->receivable ,
                'Invoice Status' => $invoice_status ,
            ];
            $data[] = $formattedData;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['Sr #', 'Agent Name', 'Carrier Name', 'Company Name', 'Load Number', 'Origin', 'Destination', 'PU Date', 'DL Date', 'Rate', 'Percentage', 'Receivable', 'Invoice Status'];

        $column = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($column, 1, $header);
            $column++;
        }
        $sheet->getStyle('A1:M1' )->getFont()->setBold(true);
        // set height
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(36);

        // Set heading row height
        $sheet->getRowDimension(1)->setRowHeight(26); // Adjust the height as needed

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

            $spreadsheet->getActiveSheet()
                ->getStyle('L2:L' . $row) // Apply to the 'Receivable' column
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
                if ($cell->getColumn() === 'J') {
                     $sheet->getColumnDimension($cell->getColumn())->setWidth(15); // Set the desired width
                }else{
                    $sheet->getColumnDimension($cell->getColumn())->setWidth($newWidth);
                }

//                $cellStyle = $cell->getStyle();
//                $alignment = $cellStyle->getAlignment();
//                $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // Generate the timestamp
        $timestamp = date('YmdHis');

        // Generate a filename for the Excel file
        $filename = $startDate . '_' . $endDate . '_' . $timestamp . ' Invoice.xlsx';

        // Create a new Excel writer
        $writer = new Xlsx($spreadsheet);

        // Save the Excel file
        $writer->save($filename);

        // Return the file as a download response
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function changeInvoiceStatus(Request $request, Dispatch $dispatcher)
    {
        if($request->invoice_status == ''){ // Invoice not sent
            $success = $dispatcher->update([
                'invoice_status' => '0',
                'invoice_generate' => '0',
            ]);
        }else if($request->invoice_status == 1){ // Invoice sent
            $success = $dispatcher->update([
                'invoice_status' => '1',
                'invoice_generate' => '1',
            ]);
        }else{ // Invoice Paid
            $success = $dispatcher->update([
                'invoice_status' => '2',
                'invoice_generate' => '1',
            ]);
        }
        $json = [];
        $json['status'] = 'error';
        $json['message'] = 'Status Not Change';
        if ($success){
            $json['status'] = 'success';
            $json['message'] = 'Status Change Successfully!';
        }
        return response($json, 200);
    }
    private function getInvoicenumber(){
        // Retrieve the current invoice number
        $currentInvoice = Invoice::latest('id')->first();
        $currentInvoiceNumber = $currentInvoice ? $currentInvoice->invoice_no : 1000; // Default starting value

        // Increment the invoice number
        $newInvoiceNumber = $currentInvoiceNumber + 1;

        // Update the invoice number in the model
        if ($currentInvoice) {
            $currentInvoice->update(['invoice_no' => $newInvoiceNumber]);
        }
        return $newInvoiceNumber;
    }

    function getAllCarrierNotPaid(Request $request){
        if($request->ajax()){
            $startDate = date('Y-m-d', strtotime($request->start_date));
            $endDate = date('Y-m-d', strtotime($request->end_date));
        }else{
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDay(7)->toDateString();
        }
        $data_array = [];
        $dispatchers = $mc_numbers = [];
        if (Auth::user()->hasRole('Admin')) {
            $mc_numbers = Dispatch::select('dispatches.mc_number', 'carriers.company_name')
                ->join('carriers', 'dispatches.mc_number', '=', 'carriers.mc_number')
                // ->whereBetween(DB::raw('DATE(dispatches.created_at)'), [$startDate, $endDate])
                ->where('dispatches.invoice_status', 1)
                ->groupBy('dispatches.mc_number', 'carriers.company_name')
                ->get();

            $dispatchers = Dispatch::with('user')->latest();
//            $dispatchers->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);

            if (!empty($request->mc_number)) {
                $dispatchers->where('mc_number', $request->mc_number);
            }
            $dispatchers->where('invoice_status', '=', 1);
            $dispatchers = $dispatchers->get();
        }

        if($request->ajax()){
            // mc number
            $mc_html = '<option value="">-- Select MC Number --</option>';
            foreach ($mc_numbers as $row){
                $selected = ($row->mc_number == $request->mc_number) ? 'selected' : '';
                $mc_html .= '<option value="'.$row->mc_number.'" '.$selected.' >'.$row->mc_number.' - ' .$row->company_name. ' </option>';
            }

            $table_html = '';
            foreach ($dispatchers as $dispatcher){
                $table_html .= '<tr>';
                $table_html .= '<td>'.$dispatcher->mc_number.'</td>';
                $table_html .= '<td>'.$dispatcher->load_number.'</td>';

                $table_html .= '<td>'.$dispatcher->user->first_name .' '.$dispatcher->user->last_name.'</td>';
                $table_html .= '<td>'.$dispatcher->pick_location.'</td>';
                $table_html .= '<td>'.$dispatcher->delivery_location.'</td>';
                $table_html .= '<td>'.\Carbon\Carbon::parse($dispatcher->delivery_date)->format("F d, Y").'</td>';
                $table_html .= '<td>'.$dispatcher->owner_name.'</td>';
                $table_html .= '<td>$'.$dispatcher->rate.'</td>';

//                $table_html .= ' <td><button class="btn btn-info viewdetails" data-id="'. $dispatcher->id .'" >View Details</button></td>';
                $table_html .= '<td><button style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link viewdetails" data-id="'. $dispatcher->id .'"><i class="ri-eye-fill" aria-hidden="true"></i></button></td>';

                $table_html .= '</tr>';
            }
            $data_array['mc_html'] = $mc_html;
            $data_array['table_html'] = $table_html;
            return response($data_array, 200);

        }else{
            $data_array['mc_numbers'] = $mc_numbers;
            $data_array['dispatchers'] = $dispatchers;
            return view('invoices.carrier_not_paid', $data_array);
        }
    }

}
