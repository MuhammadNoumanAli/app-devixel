<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\InvoiceDispatch;
use App\Models\InvoicePayment;
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
    /**
     * Invoices Dashboard with Status Tabs (All, Due, Partial, Paid)
     */
    public function index(Request $request)
    {
        $statusTab = $request->get('status', 'all');
        $mcFilter  = $request->get('mc_number');
        $search    = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $baseQuery = Invoice::with(['invoiceDispatches.dispatcher', 'invoiceDispatches.dispatch', 'carrier', 'payments.receiver', 'creator'])
            ->latest('invoice_date')
            ->latest('id');

        if (!empty($mcFilter)) {
            $baseQuery->where('mc_number', $mcFilter);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $baseQuery->whereBetween('invoice_date', [$startDate, $endDate]);
        }

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('mc_number', 'like', "%{$search}%")
                  ->orWhere('carrier_name', 'like', "%{$search}%")
                  ->orWhereHas('invoiceDispatches', function ($itemQ) use ($search) {
                      $itemQ->where('load_number', 'like', "%{$search}%");
                  });
            });
        }

        // Metrics for summary cards and tab badges (calculated across filtered query before tab restriction)
        $metricsQuery = clone $baseQuery;
        $allInvoices = $metricsQuery->get();

        $metrics = [
            'all_count'     => $allInvoices->count(),
            'all_total'     => $allInvoices->sum('total_amount'),
            'due_count'     => $allInvoices->where('status', 'due')->count(),
            'due_total'     => $allInvoices->where('status', 'due')->sum('due_amount'),
            'partial_count' => $allInvoices->where('status', 'partial')->count(),
            'partial_total' => $allInvoices->where('status', 'partial')->sum('due_amount'),
            'paid_count'    => $allInvoices->where('status', 'paid')->count(),
            'paid_total'    => $allInvoices->where('status', 'paid')->sum('paid_amount'),
            'total_paid'    => $allInvoices->sum('paid_amount'),
            'total_due'     => $allInvoices->sum('due_amount'),
        ];

        // Apply tab filter if not 'all'
        if ($statusTab && $statusTab !== 'all') {
            $baseQuery->where('status', $statusTab);
        }

        $invoices = $baseQuery->paginate(15)->withQueryString();

        // Distinct MC numbers for dropdown filter
        $mc_numbers = Carrier::select('mc_number', 'company_name')->orderBy('company_name')->get();

        return view('invoices.index', compact(
            'invoices',
            'metrics',
            'statusTab',
            'mc_numbers',
            'mcFilter',
            'search',
            'startDate',
            'endDate'
        ));
    }

    public function viewDispatcherPDFView(Request $request)
    {
        if ($request->ajax()) {
            $startDate = date('Y-m-d', strtotime($request->start_date));
            $endDate = date('Y-m-d', strtotime($request->end_date));
        } else {
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDay(7)->toDateString();
        }

        $data_array = [];
        $dispatchers = $mc_numbers = [];

        $mc_numbers = Dispatch::select('dispatches.mc_number', 'carriers.company_name')
            ->join('carriers', 'dispatches.mc_number', '=', 'carriers.mc_number')
            ->whereBetween(DB::raw('DATE(dispatches.created_at)'), [$startDate, $endDate])
            ->groupBy('dispatches.mc_number', 'carriers.company_name')
            ->get();

        $dispatchers = Dispatch::with('user')->where('is_cancel', 0)->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);

        if (!empty($request->mc_number)) {
            $dispatchers->where('mc_number', $request->mc_number);
        }

        if ($request->has('invoice_status') && $request->invoice_status !== '') {
            if ($request->invoice_status === 'uninvoiced') {
                $dispatchers->where(function ($q) {
                    $q->whereNull('invoice_generate')
                      ->orWhere('invoice_generate', 0)
                      ->orWhere('invoice_generate', false);
                });
            } elseif ($request->invoice_status === 'invoiced') {
                $dispatchers->where('invoice_generate', 1);
            }
        }

        $dispatchers = $dispatchers->get();

        if ($request->ajax()) {
            $mc_html = '<option value="">-- Select MC Number --</option>';
            foreach ($mc_numbers as $row) {
                $selected = ($row->mc_number == $request->mc_number) ? 'selected' : '';
                $mc_html .= '<option value="' . $row->mc_number . '" ' . $selected . ' >' . $row->mc_number . ' - ' . $row->company_name . ' </option>';
            }

            $table_html = '';
            foreach ($dispatchers as $dispatcher) {
                $table_html .= '<tr>';
                if ($dispatcher->invoice_generate == 1) {
                    $table_html .= '<td>
                        <div class="form-check" title="Invoice already generated (cannot be re-selected)">
                          <input class="form-check-input" type="checkbox" disabled style="cursor: not-allowed; opacity: 0.45;">
                        </div>
                    </td>';
                } else {
                    $table_html .= '<td>
                        <div class="form-check">
                          <input class="form-check-input chcktbl" type="checkbox" name="invoice" value="' . $dispatcher->id . '">
                        </div>
                    </td>';
                }
                $table_html .= '<td>' . $dispatcher->mc_number . '</td>';
                $table_html .= '<td>' . $dispatcher->load_number . '</td>';

                $table_html .= '<td>' . ($dispatcher->user ? $dispatcher->user->first_name . ' ' . $dispatcher->user->last_name : 'System') . '</td>';
                $table_html .= '<td>' . $dispatcher->pick_location . '</td>';
                $table_html .= '<td>' . $dispatcher->delivery_location . '</td>';
                $table_html .= '<td>' . \Carbon\Carbon::parse($dispatcher->delivery_date)->format("F d, Y") . '</td>';
                $table_html .= '<td class="fw-semibold">' . $dispatcher->owner_name . '</td>';
                $table_html .= '<td class="small fw-semibold text-secondary">$' . number_format($dispatcher->rate) . '</td>';
                $payableAmt = ($dispatcher->receivable !== null && (float)$dispatcher->receivable > 0)
                    ? (float)$dispatcher->receivable
                    : (((float)$dispatcher->rate > 0 && (float)$dispatcher->percentage > 0) ? (float)(($dispatcher->rate * $dispatcher->percentage) / 100) : 0);
                $table_html .= '<td class="fw-bold text-success">$' . number_format($payableAmt, 2) . '</td>';

                $table_html .= '<td>
                    <select onchange="changeInvoiceStatus(this, ' . $dispatcher->id . ')" style="font-size:11px; padding: 0.275rem 2.25rem 0.275rem 0.75rem;width:80px;" class="form-select" aria-label="Default select example">
                        <option selected value="">--Select--</option>
                        <option value="1" ' . ($dispatcher->invoice_status == 1 ? 'selected' : '') . ' >Invoice Sent</option>
                        <option value="2" ' . ($dispatcher->invoice_status == 2 ? 'selected' : '') . ' >Paid</option>
                    </select>
                </td>';

                $downloadUrl = route('invoices.downloadDispatcherPDF', [
                    'selected_invoices' => $dispatcher->id,
                    'mc_number' => $dispatcher->mc_number,
                ]);

                $table_html .= '<td class="text-center">
                    <div class="d-inline-flex gap-1 align-items-center">
                        <a href="' . $downloadUrl . '" class="btn btn-sm btn-icon btn-text-primary rounded-pill" title="Download Invoice PDF">
                            <i class="ti ti-download fs-5"></i>
                        </a>
                        <a href="' . route('dispatchers.show', $dispatcher->id) . '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="View Dispatch Details">
                            <i class="ti ti-eye fs-5"></i>
                        </a>
                    </div>
                </td>';

                $table_html .= '</tr>';
            }
            $data_array['mc_html'] = $mc_html;
            $data_array['table_html'] = $table_html;
            return response($data_array, 200);
        } else {
            $data_array['mc_numbers'] = $mc_numbers;
            $data_array['dispatchers'] = $dispatchers;
            return view('reports.dispatcher-pdf', $data_array);
        }
    }

    /**
     * Generate / Download Dispatcher PDF Invoice
     * Creates Master Invoice record handled against MC #, and Child records with dispatcher_id
     */
    public function downloadDispatcherPDF(Request $request)
    {
        if (empty($request->selected_invoices)) {
            return redirect()->back()->with("error", 'Please select at least one load to download.');
        }

        $selectedRecords = explode(',', $request->selected_invoices);
        $selectedRecords = array_filter(array_map('trim', $selectedRecords));

        if (empty($selectedRecords)) {
            return redirect()->back()->with("error", 'Please select at least one load to download.');
        }

        // Fetch selected dispatches directly by ID
        $dispatchers = Dispatch::with('user', 'carrier')->whereIn('id', $selectedRecords)->get();

        if ($dispatchers->isEmpty()) {
            return redirect()->back()->with("error", 'No records found for the selected IDs.');
        }

        $firstDispatch = $dispatchers->first();
        $mc_number = $request->mc_number ?: $firstDispatch->mc_number;
        $carrier = $firstDispatch->carrier ?: Carrier::where('mc_number', $mc_number)->first();
        $carrier_name = $carrier ? $carrier->company_name : ($firstDispatch->owner_name ?? 'N/A');

        // Check if an existing invoice already links these dispatches
        $existingInvoiceDispatch = InvoiceDispatch::whereIn('dispatch_id', $selectedRecords)->first();
        if ($existingInvoiceDispatch && $existingInvoiceDispatch->invoice) {
            $invoice = $existingInvoiceDispatch->invoice;
            $invoice_no = $invoice->invoice_no;
            $invoice->load(['invoiceDispatches.dispatch', 'invoiceDispatches.dispatcher', 'carrier', 'payments']);

            // Recalculate invoice total_amount based on payable amounts if it was using old rate logic
            $actualPayableTotal = $invoice->invoiceDispatches->sum('receivable');
            if ($actualPayableTotal > 0 && abs((float)$invoice->total_amount - (float)$actualPayableTotal) > 0.01) {
                $invoice->total_amount = $actualPayableTotal;
                $invoice->due_amount = max(0, $actualPayableTotal - (float)$invoice->paid_amount);
                if ($invoice->due_amount <= 0 && $invoice->paid_amount > 0) {
                    $invoice->status = 'paid';
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'due';
                }
                $invoice->save();
            }
        } else {
            // Generate unique Invoice Number
            $invoice_no = $this->generateInvoiceNumber();

            // Calculate total payable amount (dispatch fee receivable from carrier) - NOT the gross load rate!
            $totalAmount = $dispatchers->sum(function ($d) {
                if ($d->receivable !== null && (float)$d->receivable > 0) {
                    return (float) $d->receivable;
                }
                if ((float)$d->rate > 0 && (float)$d->percentage > 0) {
                    $calc = (float)(($d->rate * $d->percentage) / 100);
                    $d->update(['receivable' => $calc]);
                    return $calc;
                }
                return (float) ($d->receivable ?? 0);
            });

            // 1. Create Main Invoice record handled against MC Number
            $invoice = Invoice::create([
                'invoice_no'   => $invoice_no,
                'mc_number'    => $mc_number,
                'carrier_id'   => $carrier ? $carrier->id : null,
                'carrier_name' => $carrier_name,
                'total_amount' => $totalAmount,
                'paid_amount'  => 0.00,
                'due_amount'   => $totalAmount,
                'status'       => 'due',
                'invoice_date' => now()->toDateString(),
                'created_by'   => Auth::id(),
            ]);

            // 2. Create One-to-Many Child Table Relation (InvoiceDispatch) with dispatcher_id
            foreach ($dispatchers as $dispatch) {
                $receivableAmount = ($dispatch->receivable !== null && (float)$dispatch->receivable > 0)
                    ? (float)$dispatch->receivable
                    : (((float)$dispatch->rate > 0 && (float)$dispatch->percentage > 0) ? (float)(($dispatch->rate * $dispatch->percentage) / 100) : 0);

                if (empty($dispatch->receivable) && $receivableAmount > 0) {
                    $dispatch->update(['receivable' => $receivableAmount]);
                }

                InvoiceDispatch::create([
                    'invoice_id'    => $invoice->id,
                    'dispatch_id'   => $dispatch->id,
                    'dispatcher_id' => $dispatch->user_id, // Dispatcher ID who booked the load!
                    'load_number'   => $dispatch->load_number,
                    'rate'          => $dispatch->rate ?? 0,
                    'receivable'    => $receivableAmount,
                ]);

                $dispatch->update([
                    'invoice_generate' => '1',
                    'invoice_status'   => '1',
                ]);
            }
        }

        $data = [
            'title'       => $carrier ? $carrier->company_name : 'Invoice',
            'date'        => date('d/m/Y'),
            'dispatchers' => $dispatchers,
            'carrier'     => $carrier,
            'invoice_no'  => $invoice_no,
            'invoice'     => $invoice,
        ];

        $pdf = PDF::loadView('generate_pdf', $data);
        return $pdf->download(($carrier ? $carrier->company_name : 'Invoice') . ' ' . $invoice_no . '.pdf');
    }

    /**
     * Download PDF for a specific existing invoice
     */
    public function downloadInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['invoiceDispatches.dispatch', 'invoiceDispatches.dispatcher', 'carrier', 'payments']);

        $dispatchers = $invoice->invoiceDispatches->map(function ($item) {
            if ($item->dispatch) {
                return $item->dispatch;
            }
            return (object) [
                'load_number'       => $item->load_number,
                'pick_location'     => 'N/A',
                'delivery_location' => 'N/A',
                'pick_date'         => null,
                'delivery_date'     => null,
                'rate'              => $item->rate,
                'receivable'        => $item->receivable,
                'percentage'        => null,
            ];
        });

        $carrier = $invoice->carrier ?: (object) [
            'company_name'   => $invoice->carrier_name,
            'mc_number'      => $invoice->mc_number,
            'number'         => 'N/A',
            'street_address' => '',
            'city_name'      => '',
            'zip_code'       => '',
        ];

        $data = [
            'title'       => $invoice->carrier_name ?: 'Invoice',
            'date'        => $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : date('d/m/Y'),
            'dispatchers' => $dispatchers,
            'carrier'     => $carrier,
            'invoice_no'  => $invoice->invoice_no,
            'invoice'     => $invoice,
        ];

        $pdf = PDF::loadView('generate_pdf', $data);
        return $pdf->download(($invoice->carrier_name ?: 'Invoice') . ' ' . $invoice->invoice_no . '.pdf');
    }

    /**
     * Download all invoices statement / report for a specific MC number
     */
    public function downloadMcAllInvoicesPdf(Request $request, $mcNumber)
    {
        $carrier = Carrier::where('mc_number', $mcNumber)->first();
        $invoices = Invoice::with(['invoiceDispatches.dispatcher', 'invoiceDispatches.dispatch', 'payments.receiver'])
            ->where('mc_number', $mcNumber)
            ->latest('invoice_date')
            ->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', "No invoices found for MC #{$mcNumber}.");
        }

        $carrierName = $carrier ? $carrier->company_name : ($invoices->first()->carrier_name ?? "MC-{$mcNumber}");

        $totalInvoiced = $invoices->sum('total_amount');
        $totalPaid     = $invoices->sum('paid_amount');
        $totalDue      = $invoices->sum('due_amount');

        $data = [
            'title'         => "Statement - {$carrierName} (MC #{$mcNumber})",
            'date'          => date('d/m/Y'),
            'mc_number'     => $mcNumber,
            'carrier'       => $carrier,
            'carrier_name'  => $carrierName,
            'invoices'      => $invoices,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid'     => $totalPaid,
            'totalDue'      => $totalDue,
        ];

        $pdf = PDF::loadView('reports.mc_statement_pdf', $data);
        return $pdf->download("Statement_MC_{$mcNumber}_" . date('Ymd') . '.pdf');
    }

    /**
     * Store partial or full payment for an invoice
     */
    public function storePayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_date'   => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'note'           => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $invoice->recordPayment($validated, Auth::id());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment of $' . number_format($validated['amount'], 2) . ' recorded successfully!',
                'invoice' => [
                    'id'          => $invoice->id,
                    'paid_amount' => number_format($invoice->paid_amount, 2),
                    'due_amount'  => number_format($invoice->due_amount, 2),
                    'status'      => $invoice->status,
                ],
                'payment' => [
                    'id'             => $payment->id,
                    'amount'         => number_format($payment->amount, 2),
                    'payment_date'   => $payment->payment_date->format('M d, Y'),
                    'payment_method' => $payment->payment_method ?? 'N/A',
                    'reference_no'   => $payment->reference_no ?? 'N/A',
                    'note'           => $payment->note ?? '',
                ],
            ], 200);
        }

        return redirect()->back()->with('status', 'Payment of $' . number_format($validated['amount'], 2) . ' recorded successfully!');
    }

    /**
     * Get payment history and child loads details as JSON for modals
     */
    public function getPayments(Invoice $invoice)
    {
        $invoice->load(['payments.receiver', 'invoiceDispatches.dispatcher']);

        $payments = $invoice->payments->map(function ($payment) {
            return [
                'id'             => $payment->id,
                'amount'         => number_format($payment->amount, 2),
                'payment_date'   => $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A',
                'payment_method' => $payment->payment_method ?: 'N/A',
                'reference_no'   => $payment->reference_no ?: 'N/A',
                'note'           => $payment->note ?: '',
                'receiver'       => $payment->receiver ? ($payment->receiver->first_name . ' ' . $payment->receiver->last_name) : 'System',
                'delete_url'     => route('invoices.payments.destroy', $payment->id),
            ];
        });

        $loads = $invoice->invoiceDispatches->map(function ($item) {
            return [
                'load_number' => $item->load_number,
                'dispatcher'  => $item->dispatcher ? ($item->dispatcher->first_name . ' ' . $item->dispatcher->last_name) : 'System',
                'rate'        => number_format($item->rate, 2),
                'receivable'  => number_format($item->receivable, 2),
            ];
        });

        return response()->json([
            'id'           => $invoice->id,
            'invoice_no'   => $invoice->invoice_no,
            'mc_number'    => $invoice->mc_number,
            'carrier_name' => $invoice->carrier_name,
            'total_amount' => number_format($invoice->total_amount, 2),
            'paid_amount'  => number_format($invoice->paid_amount, 2),
            'due_amount'   => number_format($invoice->due_amount, 2),
            'status'       => $invoice->status,
            'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : 'N/A',
            'payments'     => $payments,
            'loads'        => $loads,
        ], 200);
    }

    /**
     * Delete a recorded payment and recalculate balances
     */
    public function destroyPayment(InvoicePayment $payment)
    {
        $invoice = $payment->invoice;
        $amount = $payment->amount;
        $payment->delete();

        $invoice->recalculateStatusAndBalance();

        return response()->json([
            'success' => true,
            'message' => 'Payment of $' . number_format($amount, 2) . ' deleted successfully.',
            'invoice' => [
                'id'          => $invoice->id,
                'paid_amount' => number_format($invoice->paid_amount, 2),
                'due_amount'  => number_format($invoice->due_amount, 2),
                'status'      => $invoice->status,
            ],
        ], 200);
    }

    public function downloadDispatcherXLX(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $dispatchers = [];
        $dispatchers = Dispatch::with('user', 'carrier')->latest()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        $dispatchers = $dispatchers->get();

        $data = [];
        foreach ($dispatchers as $key => $dispatcher) {
            $invoice_status = 'N/A';
            if ($dispatcher->invoice_status == 1) {
                $invoice_status = 'Invoice Sent';
            } else if ($dispatcher->invoice_status == 2) {
                $invoice_status = 'Paid';
            }
            $formattedData = [
                'Sr. #'          => ++$key,
                'Agent Name'     => ($dispatcher->user ? $dispatcher->user->first_name . " " . $dispatcher->user->last_name : 'System'),
                'Carrier Name'   => $dispatcher->owner_name,
                'Company Name'   => (isset($dispatcher->carrier->company_name) ? $dispatcher->carrier->company_name : 'N/A'),
                'Load Number'    => $dispatcher->load_number,
                'Origin'         => $dispatcher->pick_location,
                'Destination'    => $dispatcher->delivery_location,
                'PU Date'        => $dispatcher->pick_date,
                'DL Date'        => $dispatcher->delivery_date,
                'Rate'           => $dispatcher->rate,
                'Percentage'     => $dispatcher->percentage,
                'Receivable'     => $dispatcher->receivable,
                'Invoice Status' => $invoice_status,
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
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $lastColumn = $sheet->getHighestColumn();
        $headingRange = 'A1:' . $lastColumn . '1';
        $borderStyle = [
            'borderStyle' => Border::BORDER_THICK,
            'color'       => ['rgb' => '87CEEB'],
            'weight'      => 'thick',
            'borderSize'  => 20,
        ];
        $sheet->getStyle($headingRange)->getBorders()->getBottom()->applyFromArray($borderStyle);
        $sheet->getStyle($headingRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        foreach ($data as $item) {
            $column = 1;
            foreach ($item as $value) {
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }

            $spreadsheet->getActiveSheet()
                ->getStyle('J2:J' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getActiveSheet()
                ->getStyle('L2:L' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $row++;
        }

        foreach ($sheet->getRowIterator(1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $columnWidth = $sheet->getColumnDimension($cell->getColumn())->getWidth();
                $newWidth = max($columnWidth, strlen($cell->getValue()) + 2);
                if ($cell->getColumn() === 'J') {
                    $sheet->getColumnDimension($cell->getColumn())->setWidth(15);
                } else {
                    $sheet->getColumnDimension($cell->getColumn())->setWidth($newWidth);
                }
            }
        }

        $timestamp = date('YmdHis');
        $filename = $startDate . '_' . $endDate . '_' . $timestamp . ' Invoice.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function changeInvoiceStatus(Request $request, Dispatch $dispatcher)
    {
        if ($request->invoice_status == '') {
            $success = $dispatcher->update([
                'invoice_status'   => '0',
                'invoice_generate' => '0',
            ]);
        } else if ($request->invoice_status == 1) {
            $success = $dispatcher->update([
                'invoice_status'   => '1',
                'invoice_generate' => '1',
            ]);
        } else {
            $success = $dispatcher->update([
                'invoice_status'   => '2',
                'invoice_generate' => '1',
            ]);
        }

        $json = [];
        $json['status'] = 'error';
        $json['message'] = 'Status Not Change';
        if ($success) {
            $json['status'] = 'success';
            $json['message'] = 'Status Change Successfully!';
        }
        return response($json, 200);
    }

    private function generateInvoiceNumber(): string
    {
        $latest = Invoice::whereNotNull('invoice_no')->orderBy('id', 'desc')->first();
        if ($latest && preg_match('/(\d+)$/', $latest->invoice_no, $matches)) {
            $nextNum = (int) $matches[1] + 1;
            return 'INV-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }
        $count = Invoice::count();
        return 'INV-' . (1001 + $count);
    }

    public function getAllCarrierNotPaid(Request $request)
    {
        if ($request->ajax()) {
            $startDate = date('Y-m-d', strtotime($request->start_date));
            $endDate = date('Y-m-d', strtotime($request->end_date));
        } else {
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDay(7)->toDateString();
        }

        $data_array = [];
        $dispatchers = $mc_numbers = [];

        $mc_numbers = Dispatch::select('dispatches.mc_number', 'carriers.company_name')
            ->join('carriers', 'dispatches.mc_number', '=', 'carriers.mc_number')
            ->where('dispatches.invoice_status', 1)
            ->groupBy('dispatches.mc_number', 'carriers.company_name')
            ->get();

        $dispatchers = Dispatch::with('user')->latest();

        if (!empty($request->mc_number)) {
            $dispatchers->where('mc_number', $request->mc_number);
        }
        $dispatchers->where('invoice_status', '=', 1);
        $dispatchers = $dispatchers->get();

        if ($request->ajax()) {
            $mc_html = '<option value="">-- Select MC Number --</option>';
            foreach ($mc_numbers as $row) {
                $selected = ($row->mc_number == $request->mc_number) ? 'selected' : '';
                $mc_html .= '<option value="' . $row->mc_number . '" ' . $selected . ' >' . $row->mc_number . ' - ' . $row->company_name . ' </option>';
            }

            $table_html = '';
            foreach ($dispatchers as $dispatcher) {
                $table_html .= '<tr>';
                $table_html .= '<td>' . $dispatcher->mc_number . '</td>';
                $table_html .= '<td>' . $dispatcher->load_number . '</td>';

                $table_html .= '<td>' . ($dispatcher->user ? $dispatcher->user->first_name . ' ' . $dispatcher->user->last_name : 'System') . '</td>';
                $table_html .= '<td>' . $dispatcher->pick_location . '</td>';
                $table_html .= '<td>' . $dispatcher->delivery_location . '</td>';
                $table_html .= '<td>' . \Carbon\Carbon::parse($dispatcher->delivery_date)->format("F d, Y") . '</td>';
                $table_html .= '<td>' . $dispatcher->owner_name . '</td>';
                $table_html .= '<td>$' . $dispatcher->rate . '</td>';

                $table_html .= '<td><button style="text-decoration: none !important;margin-top: -4px!important;font-size: 13px!important;padding-left: 0 !important;" class="btn btn-link viewdetails" data-id="' . $dispatcher->id . '"><i class="ri-eye-fill" aria-hidden="true"></i></button></td>';

                $table_html .= '</tr>';
            }
            $data_array['mc_html'] = $mc_html;
            $data_array['table_html'] = $table_html;
            return response($data_array, 200);
        } else {
            $data_array['mc_numbers'] = $mc_numbers;
            $data_array['dispatchers'] = $dispatchers;
            return view('invoices.carrier_not_paid', $data_array);
        }
    }
}
