<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use CodeIgniter\RESTful\ResourceController;
use ResponseTrait;

class DeskRentalsController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\DeskRentalsModel();
    }

    public function getAllDeskRentals()
    {
        return $this->response->setJSON($this->model->findAll());
    }
}
