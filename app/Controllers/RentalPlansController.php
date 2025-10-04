<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalPlansController extends ResourceController
{
    protected $model;
    
    public function __construct()
    {
        $this->model = new RentalPlansModel();
    }
    
    public function index() { /* dps implementar o crud */ }
}

?>