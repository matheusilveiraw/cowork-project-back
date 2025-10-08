<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

Events::on('deskrental.created', function($deskRentalId) {
    $invoiceModel = new \App\Models\InvoicesModel();
    $deskRentalModel = new \App\Models\DeskRentalsModel();
    
    if ($invoiceModel->invoiceExistsForRental($deskRentalId)) {
        return;
    }

    $deskRental = $deskRentalModel->find($deskRentalId);
    if (!$deskRental) return;

    $customerModel = new \App\Models\CustomersModel();
    $planModel = new \App\Models\RentalPlansModel();
    $companyModel = new \App\Models\CompanyDataModel();
    
    $customer = $customerModel->find($deskRental['idCustomer']);
    $plan = $planModel->find($deskRental['idPlan']);
    $company = $companyModel->first();

    if (!$company) return;

    $year = date('Y');
    $lastInvoice = $invoiceModel->orderBy('idInvoice', 'DESC')->first();
    $sequence = $lastInvoice ? ((int) substr($lastInvoice['invoice_number'], -6)) + 1 : 1;
    $invoiceNumber = $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);

    $invoiceData = [
        'invoice_number' => $invoiceNumber,
        'idDeskRental' => $deskRentalId,
        'issue_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+7 days')),
        'total_amount' => $deskRental['total_price'],
        'status' => 'pending',
        'xml_content' => '<?xml version="1.0"?><nfe>NF para aluguel ' . $deskRentalId . '</nfe>',
        'access_key' => substr(md5(uniqid(rand(), true)), 0, 44)
    ];

    $invoiceModel->insert($invoiceData);
});