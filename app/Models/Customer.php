<?php

namespace App\Models;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = [
        'user_id',
        'code',
        'balance',
        'name',
        'email',
        'phone',
        'address',
        'birthdate',
        'birthplace'
    ];

    public function user()
    {
        // Relationship with user
    }

    public function deposits()
    {
        // Relationship with deposits
    }

    public function loans()
    {
        // Relationship with loans
    }

    public function transactions()
    {
        // Relationship with transactions
    }
} 