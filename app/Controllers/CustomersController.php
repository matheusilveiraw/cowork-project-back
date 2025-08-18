<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use CodeIgniter\RESTful\ResourceController;

class CustomersController extends ResourceController
{
    protected $modelName = 'App\Models\CustomersModel';
    protected $format = 'json';

    // GET /customers
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    // GET /customers/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            return $this->respond($data);
        }
        return $this->failNotFound("Customer not found");
    }

    // POST /customers
    public function create()
    {
        $data = $this->request->getJSON(true); // pega JSON do body
        if ($this->model->insert($data)) {
            return $this->respondCreated($data);
        }
        return $this->fail("Failed to create customer");
    }

    // PUT /customers/{id}
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if ($this->model->update($id, $data)) {
            return $this->respond($data);
        }
        return $this->fail("Failed to update customer");
    }

    // DELETE /customers/{id}
    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(["id" => $id, "message" => "Deleted"]);
        }
        return $this->failNotFound("Customer not found");
    }
}
