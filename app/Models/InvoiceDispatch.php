<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'dispatch_id',
        'dispatcher_id',
        'load_number',
        'rate',
        'receivable',
    ];

    protected $casts = [
        'rate'       => 'decimal:2',
        'receivable' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }
}
