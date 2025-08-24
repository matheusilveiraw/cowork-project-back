<?php

namespace App\Models;

use CodeIgniter\Model;

class DeskRentalsModel extends Model
{
    protected $table = 'deskRentals';
    protected $primaryKey = 'idDeskRental';
    protected $allowedFields = ['idDesk', 'idCustomer', 'period_start', 'period_end'];

    // idDeskRental INT AUTO_INCREMENT PRIMARY KEY,
    // idDesk INT NOT NULL,
    // idCustomer INT NOT NULL,
    // period_start DATETIME NOT NULL,
    // period_end DATETIME NOT NULL,
}
