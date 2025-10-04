<?php

namespace App\Controllers;

use App\Models\RentalPlansModel;
use CodeIgniter\RESTful\ResourceController;

class RentalPlansController extends ResourceController
{
    protected $model;
    
    public function __construct()
    {
        $this->model = new RentalPlansModel();
    }
    
    public function index()
    {
        $planos = $this->model->findAll();
        return $this->response->setJSON($planos);
    }
}