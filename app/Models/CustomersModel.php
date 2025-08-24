<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomersModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'idCustomer';
    protected $allowedFields = ['name', 'email', 'phone', 'address'];
}
