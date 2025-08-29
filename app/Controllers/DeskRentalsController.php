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

    public function insertDeskRental()
    {
        $data = $this->request->getJSON(true);

        //precisa validar se a data de inicio é maior que a data de fim
        //preciso decidir se o sistema identifica o plano ou se deixo pro usuario escolher o plano
        //provavelmente mais fácil deixar na mão do sistema

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

    public function getDeskRentalById($id)
    {
        $deskRental = $this->model->find($id);

        if ($deskRental) {
            return $this->response->setJSON($deskRental);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Aluguel da mesa não encontrado'
            ])->setStatusCode(404);
        }
    }

    public function updateDeskRental($id = null)
    {
        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            return $this->response->setJSON($data);
        }

        return $this->response->setStatusCode(400)->setJSON(['error' => 'Falha ao atualizar aluguel da mesa']);
    }

    public function deleteDeskRental($id = null)    {
        if ($this->model->delete($id)) {
            return $this->response->setJSON([
                "id" => $id,
                "message" => "Alguel da mesa deletado!"
            ])->setStatusCode(200);
        }

        return $this->response->setJSON([
            "error" => "Aluguel da mesa não encontrado"
        ])->setStatusCode(404);
    }
}