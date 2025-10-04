<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalCategoriesModel extends Model
{
    protected $table = 'rental_categories';
    protected $primaryKey = 'idCategory';
    protected $allowedFields = ['categoryName', 'base_duration'];
}
?>