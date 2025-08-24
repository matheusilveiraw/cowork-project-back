<?php

namespace App\Models;

use CodeIgniter\Model;

class DeskRentalsModel extends Model
{
    protected $table = 'deskRentals';
    protected $primaryKey = 'idDeskRental';
    protected $allowedFields = ['idDesk', 'idCustomer', 'startPeriod', 'endPeriod'];

    // idDeskRental INT AUTO_INCREMENT PRIMARY KEY,
    // idDesk INT NOT NULL,
    // idCustomer INT NOT NULL,
    // startPeriod DATETIME NOT NULL,
    // endPeriod DATETIME NOT NULL,
}
