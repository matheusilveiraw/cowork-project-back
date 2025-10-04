<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalShiftsModel extends Model
{
    protected $table = 'rental_shifts';
    protected $primaryKey = 'idShift';
    protected $allowedFields = ['shiftName', 'description', 'start_time', 'end_time'];
}
?>