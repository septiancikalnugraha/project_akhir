<?php

namespace App\Models;

class Deposit extends Model
{
    protected $table = 'deposits';
    protected $fillable = [
        'customer_id',
        'customer_balance_before',
        'customer_balance_after',
        'status',
        'type',
        'plan',
        'subtotal',
        'fee',
        'total',
        'notes',
        'fiscal_date'
    ];

    public function customer()
    {
        // Relationship with customer
    }

    public function transactions()
    {
        // Relationship with transactions
    }
} 