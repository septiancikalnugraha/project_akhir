<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Customer;

class LoanController extends Controller
{
    public function index()
    {
        $loans = new Loan();
        $data = $loans->all();
        return $this->view('loans.index', ['loans' => $data]);
    }

    public function create()
    {
        $customers = new Customer();
        $data = $customers->all();
        return $this->view('loans.create', ['customers' => $data]);
    }

    public function store()
    {
        $loan = new Loan();
        $data = [
            'customer_id' => $_POST['customer_id'],
            'status' => $_POST['status'],
            'instalment' => $_POST['instalment'],
            'subtotal' => $_POST['subtotal'],
            'fee' => $_POST['fee'],
            'total' => $_POST['total'],
            'notes' => $_POST['notes'],
            'fiscal_date' => $_POST['fiscal_date']
        ];
        
        $loan->create($data);
        return $this->redirect('/loans');
    }

    public function edit($id)
    {
        $loan = new Loan();
        $data = $loan->find($id);
        return $this->view('loans.edit', ['loan' => $data]);
    }

    public function update($id)
    {
        $loan = new Loan();
        $data = [
            'status' => $_POST['status'],
            'instalment' => $_POST['instalment'],
            'subtotal' => $_POST['subtotal'],
            'fee' => $_POST['fee'],
            'total' => $_POST['total'],
            'notes' => $_POST['notes'],
            'fiscal_date' => $_POST['fiscal_date']
        ];
        
        $loan->update($id, $data);
        return $this->redirect('/loans');
    }

    public function delete($id)
    {
        $loan = new Loan();
        $loan->delete($id);
        return $this->redirect('/loans');
    }
} 