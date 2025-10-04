<?php

namespace App\Models;

use CodeIgniter\Model;

class DeskRentalsModel extends Model
{
    protected $table = 'deskRentals';
    protected $primaryKey = 'idDeskRental';
    protected $allowedFields = ['idDesk', 'idCustomer', 'idPlan', 'startPeriod', 'endPeriod', 'total_price'];
}