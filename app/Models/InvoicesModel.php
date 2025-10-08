<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoicesModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'idInvoice';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'invoice_number',
        'idDeskRental', 
        'issue_date',
        'due_date',
        'total_amount',
        'status',
        'payment_date',
        'payment_method',
        'xml_content',
        'access_key'
    ];

    protected bool $allowEmptyInserts = false;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $validationRules = [
        'invoice_number' => 'required|is_unique[invoices.invoice_number]',
        'idDeskRental'   => 'required|integer|is_not_unique[deskRentals.idDeskRental]',
        'issue_date'     => 'required|valid_date',
        'due_date'       => 'required|valid_date',
        'total_amount'   => 'required|decimal',
        'status'         => 'required|in_list[pending,paid,cancelled]'
    ];

    protected $validationMessages = [
        'invoice_number' => [
            'required' => 'O número da nota fiscal é obrigatório',
            'is_unique' => 'Este número de nota fiscal já existe'
        ],
        'idDeskRental' => [
            'required' => 'O aluguel é obrigatório',
            'is_not_unique' => 'O aluguel informado não existe'
        ],
        'total_amount' => [
            'required' => 'O valor total é obrigatório',
            'decimal' => 'O valor total deve ser um número decimal'
        ]
    ];

    protected $skipValidation = false;
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function findByInvoiceNumber($invoiceNumber)
    {
        return $this->where('invoice_number', $invoiceNumber)->first();
    }

    public function findByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }

    public function findPendingInvoices()
    {
        return $this->where('status', 'pending')->findAll();
    }

    public function findPaidInvoices()
    {
        return $this->where('status', 'paid')->findAll();
    }

    public function findWithRentalData($invoiceId)
    {
        return $this->select('invoices.*, 
                             deskRentals.startPeriod, 
                             deskRentals.endPeriod,
                             desks.deskNumber,
                             desks.deskName,
                             customers.nameCustomer,
                             customers.emailCustomer,
                             rental_plans.planName,
                             rental_plans.price')
                    ->join('deskRentals', 'deskRentals.idDeskRental = invoices.idDeskRental')
                    ->join('desks', 'desks.idDesk = deskRentals.idDesk')
                    ->join('customers', 'customers.idCustomer = deskRentals.idCustomer')
                    ->join('rental_plans', 'rental_plans.idPlan = deskRentals.idPlan')
                    ->where('invoices.idInvoice', $invoiceId)
                    ->first();
    }

    public function invoiceExistsForRental($deskRentalId)
    {
        return $this->where('idDeskRental', $deskRentalId)->first();
    }

    public function findAllWithRelatedData()
    {
        return $this->select('invoices.*, 
                             deskRentals.startPeriod, 
                             deskRentals.endPeriod,
                             desks.deskNumber,
                             desks.deskName,
                             customers.nameCustomer,
                             rental_plans.planName')
                    ->join('deskRentals', 'deskRentals.idDeskRental = invoices.idDeskRental')
                    ->join('desks', 'desks.idDesk = deskRentals.idDesk')
                    ->join('customers', 'customers.idCustomer = deskRentals.idCustomer')
                    ->join('rental_plans', 'rental_plans.idPlan = deskRentals.idPlan')
                    ->orderBy('invoices.issue_date', 'DESC')
                    ->findAll();
    }

    public function findByDateRange($startDate, $endDate)
    {
        return $this->where('issue_date >=', $startDate)
                    ->where('issue_date <=', $endDate)
                    ->findAll();
    }


    public function getInvoiceStats()
    {
        $stats = [
            'total' => $this->countAll(),
            'pending' => $this->where('status', 'pending')->countAllResults(),
            'paid' => $this->where('status', 'paid')->countAllResults(),
            'cancelled' => $this->where('status', 'cancelled')->countAllResults(),
            'total_amount' => $this->selectSum('total_amount')->get()->getRow()->total_amount,
            'paid_amount' => $this->selectSum('total_amount')->where('status', 'paid')->get()->getRow()->total_amount
        ];

        return $stats;
    }
}