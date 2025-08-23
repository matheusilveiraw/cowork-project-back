<?php

namespace App\Models;

use CodeIgniter\Model;

class DesksModel extends Model
{
    protected $table = 'desks';
    protected $primaryKey = 'idDesk';
    protected $allowedFields = ['deskNumber', 'deskName'];
}
