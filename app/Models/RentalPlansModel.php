<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalPlansModel extends Model
{
    protected $table = 'rental_plans';
    protected $primaryKey = 'idPlan';
    protected $allowedFields = ['idCategory', 'idShift', 'planName', 'price', 'is_active'];
}
?>