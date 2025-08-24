<?php

namespace App\Controllers;

use App\Models\DesksModel;
use CodeIgniter\RESTful\ResourceController;
use ResponseTrait;


class DesksController extends BaseController
{
    protected $modelName = DesksModel::class;
    protected $format = 'json';


    public function __construct()
    {
        $this->model = new \App\Models\DesksModel();
    }

    public function getAllDesks()
    {
        return $this->response->setJSON($this->model->findAll());
    }

    public function getDesksById($id){
        $desk = $this->model->find($id);

        if ($desk) {
            return $this->response->setJSON($desk);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mesa não encontrada'
            ])->setStatusCode(404);
        }
    }

    public function insertDesk(){
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
            ->setJSON(['error' => 'Falha ao inserir a mesa']);
    }

}
