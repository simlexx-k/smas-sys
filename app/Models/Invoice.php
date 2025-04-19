<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Invoice extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'subscription_id',
        'tenant_id',
        'number',
        'amount',
        'status', // pending, paid, overdue, canceled
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
        'payment_method',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'notes',
        'file_path',
        'generated_at',
        'error',
        'last_sent_at',
        'sent_to',
        'send_attempts',
        'send_error'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_period_start' => 'datetime',
        'billing_period_end' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime'
    ];

    protected $attributes = [
        'file_path' => '',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class)->with(['plan', 'tenant']);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    public static function generateNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        $count = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $count);
    }

    public function sendInvoice(Tenant $tenant)
    {
        $this->update([
            'last_sent_at' => now(),
            'sent_to' => $tenant->admin->email,
            'send_attempts' => $this->send_attempts + 1
        ]);
    }
} 