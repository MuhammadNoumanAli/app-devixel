<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'mc_number',
        'carrier_id',
        'carrier_name',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'invoice_date',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
        'invoice_date' => 'date',
        'due_date'     => 'date',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceDispatch::class);
    }

    public function invoiceDispatches()
    {
        return $this->hasMany(InvoiceDispatch::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class)->latest('payment_date');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'mc_number', 'mc_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Record a partial or full payment and recalculate status & balances.
     */
    public function recordPayment(array $data, ?int $userId = null): InvoicePayment
    {
        $payment = $this->payments()->create([
            'amount'         => $data['amount'],
            'payment_date'   => $data['payment_date'] ?? now()->toDateString(),
            'payment_method' => $data['payment_method'] ?? null,
            'reference_no'   => $data['reference_no'] ?? null,
            'note'           => $data['note'] ?? null,
            'received_by'    => $userId,
        ]);

        $this->recalculateStatusAndBalance();

        return $payment;
    }

    /**
     * Recalculate paid_amount, due_amount, and status based on total and payments.
     */
    public function recalculateStatusAndBalance(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->due_amount = max(0, (float) $this->total_amount - $totalPaid);

        if ($this->due_amount <= 0 && $this->total_amount > 0) {
            $this->status = 'paid';

            // Also mark child dispatches as Paid (invoice_status = 2)
            $dispatchIds = $this->invoiceDispatches()->whereNotNull('dispatch_id')->pluck('dispatch_id');
            if ($dispatchIds->isNotEmpty()) {
                Dispatch::whereIn('id', $dispatchIds)->update([
                    'invoice_status' => '2',
                ]);
            }
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'due';
        }

        $this->save();
    }
}
