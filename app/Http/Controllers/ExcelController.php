<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelController extends Controller
{
    public function writeToExcel()
    {
        $carriers = Carrier::with('user', 'truckType', 'assignedTo')->latest()->get();

//        dd($carriers);
//        // Fetch your data here as needed
//        $data = [
//            // Your data array here
//        ];

        // Create a new Spreadsheet object
//        $spreadsheet = new Spreadsheet();
//
//        // Add data to the spreadsheet
//        $sheet = $spreadsheet->getActiveSheet();
//
//        // Fill in data from the loop
//        $row = 1; // Start from the first row
//        foreach ($data as $item) {
//            $column = 1;
//            foreach ($item as $value) {
//                echo $item;
//
////                $sheet->setCellValueByColumnAndRow($column, $row, $value);
////                $column++;
//            }
//            $row++;
//        }

        $data = [];
        foreach ($carriers as $key => $carrier){
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
        $spreadsheet->createSheet();
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'My Data');
        $spreadsheet->addSheet($myWorkSheet, 0);

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

        // Create a new Excel writer
        $writer = new Xlsx($spreadsheet);

        // Generate the timestamp
        $timestamp = date('YmdHis');

        // Generate a filename for the Excel file
        $filename = 'records.xlsx';

        // Save the Excel file to the public directory
        $writer->save(public_path('excel/' . $filename));

        // Get the public URL of the Excel file
        $url = asset('excel/' . $filename);
        // Return a redirect response to the URL
        return view('write_excel')->with('url', $url);
    }
}
