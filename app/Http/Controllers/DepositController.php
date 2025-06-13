<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Customer;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = new Deposit();
        $data = $deposits->all();
        return $this->view('deposits.index', ['deposits' => $data]);
    }

    public function create()
    {
        $customers = new Customer();
        $data = $customers->all();
        return $this->view('deposits.create', ['customers' => $data]);
    }

    public function store()
    {
        $deposit = new Deposit();
        $data = [
            'customer_id' => $_POST['customer_id'],
            'type' => $_POST['type'],
            'plan' => $_POST['plan'],
            'subtotal' => $_POST['subtotal'],
            'fee' => $_POST['fee'],
            'total' => $_POST['total'],
            'notes' => $_POST['notes'],
            'fiscal_date' => $_POST['fiscal_date']
        ];
        
        $deposit->create($data);
        return $this->redirect('/deposits');
    }

    public function edit($id)
    {
        $deposit = new Deposit();
        $data = $deposit->find($id);
        return $this->view('deposits.edit', ['deposit' => $data]);
    }

    public function update($id)
    {
        $deposit = new Deposit();
        $data = [
            'type' => $_POST['type'],
            'plan' => $_POST['plan'],
            'subtotal' => $_POST['subtotal'],
            'fee' => $_POST['fee'],
            'total' => $_POST['total'],
            'notes' => $_POST['notes'],
            'fiscal_date' => $_POST['fiscal_date']
        ];
        
        $deposit->update($id, $data);
        return $this->redirect('/deposits');
    }

    public function delete($id)
    {
        $deposit = new Deposit();
        $deposit->delete($id);
        return $this->redirect('/deposits');
    }
} 