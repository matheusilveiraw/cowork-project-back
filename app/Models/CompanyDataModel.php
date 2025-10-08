<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyDataModel extends Model
{
    protected $table            = 'company_data';
    protected $primaryKey       = 'idCompany';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'company_name',
        'trading_name', 
        'cnpj',
        'state_registration',
        'municipal_registration',
        'phone',
        'email',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_zipcode'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'company_name' => 'required|max_length[255]',
        'cnpj' => 'required|max_length[18]',
        'email' => 'required|valid_email',
        'address_street' => 'required',
        'address_city' => 'required',
        'address_state' => 'required|max_length[2]'
    ];

    protected $validationMessages = [
        'cnpj' => [
            'required' => 'CNPJ é obrigatório'
        ],
        'email' => [
            'valid_email' => 'Email deve ser válido'
        ]
    ];

    protected $skipValidation = false;
}