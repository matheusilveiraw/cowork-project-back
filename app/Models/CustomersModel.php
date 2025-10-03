<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomersModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'idCustomer';
    protected $allowedFields    = ['nameCustomer', 'emailCustomer', 'phoneCustomer', 'addressCustomer'];
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Validações básicas
    protected $validationRules = [
        'nameCustomer' => 'required|min_length[2]|max_length[100]',
        'emailCustomer' => 'required|valid_email|max_length[100]|is_unique[customers.emailCustomer]'
    ];

    protected $validationMessages = [
        'nameCustomer' => [
            'required' => 'O campo nome é obrigatório',
            'min_length' => 'O nome deve ter pelo menos 2 caracteres'
        ],
        'emailCustomer' => [
            'required' => 'O campo email é obrigatório',
            'valid_email' => 'Digite um email válido',
            'is_unique' => 'Já existe um cliente com este email'
        ]
    ];

    protected $skipValidation = false;
}
