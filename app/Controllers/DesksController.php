<?php

namespace App\Controllers;

use App\Models\DesksModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;



class DesksController extends BaseController
{
    protected $modelName = DesksModel::class;
    protected $format = 'json';
    use ResponseTrait;

    public function __construct()
    {
        $this->model = new \App\Models\DesksModel();
    }

    public function getAllDesks()
    {
        $mesas = $this->model->orderBy('deskNumber', 'ASC')->findAll();
        return $this->response->setJSON($mesas);
    }

    public function getDesksById($id)
    {
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

    public function insertDesk()
    {
        try {
            $json = $this->request->getJSON();
            $deskModel = new \App\Models\DesksModel();
            $dto = [
                'deskNumber' => $json->deskNumber,
                'deskName' => $json->deskName
            ];

            // Validações-----------------

            if (!$json) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => 'JSON inválido']);
            }

            if (!isset($json->deskNumber) || empty($json->deskNumber)) {
                return $this->fail('Número da mesa é obrigatório', 400);
            }

            $existingDesk = $deskModel->where('deskNumber', value: $json->deskNumber)->first();

            if ($existingDesk) {
                return $this->fail('Já existe uma mesa com este número', 409);
            }

            //validações off -------------

            if ($deskModel->insert($dto)) {
                $newId = $deskModel->getInsertID();

                return $this->response
                    ->setStatusCode(201)
                    ->setJSON([
                        'status' => 'success',
                        'message' => 'Mesa criada com sucesso!',
                        'data' => [
                            'id' => $newId,
                            'deskNumber' => $json->deskNumber,
                            'deskName' => $json->deskName
                        ]
                    ]);
            }

            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Falha ao inserir a mesa']);

        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    public function updateDesk($id = null)
    {
        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            return $this->response->setJSON($data);
        }

        return $this->response->setStatusCode(400)->setJSON(['error' => 'Falha ao atualizar a mesa']);
    }

    public function deleteDesk($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->response->setJSON([
                "id" => $id,
                "message" => "Mesa deletada!"
            ])->setStatusCode(200);
        }

        return $this->response->setJSON([
            "error" => "Mesa não encontrado"
        ])->setStatusCode(404);
    }
}
