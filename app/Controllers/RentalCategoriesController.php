<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use CodeIgniter\RESTful\ResourceController;
use ResponseTrait;

class RentalCategoriesController extends ResourceController
{
    protected $model;
    
    public function __construct()
    {
        $this->model = new RentalCategoriesModel();
    }
    
    public function index() { /* dps implemento o crudv*/ }
}