<?php

namespace App\Models;

class Loan extends Model
{
    protected $table = 'loans';
    protected $fillable = [
        'customer_id',
        'status',
        'instalment',
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

    public function instalments()
    {
        // Relationship with loan instalments
    }

    public function transactions()
    {
        // Relationship with transactions
    }
} 