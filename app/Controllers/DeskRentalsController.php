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

    public function insertDeskRental(){
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'JSON inválido']);
        }

        if ($this->model->insert($data)) { 
            return $this->response
                ->setStatusCode(201)
                ->setJSON($data);
        }

        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Falha ao inserir aluguel da mesa']);
    }
}
