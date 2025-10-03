<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use CodeIgniter\RESTful\ResourceController;
use ResponseTrait;


class CustomersController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\CustomersModel();
    }

    public function getAllCustomers()
    {
        return $this->response->setJSON($this->model->findAll());
        //padrão code igniter para buscar tudo no banco
    }

    public function getCustomerById($id)
    {
        $customer = $this->model->find($id);

        if ($customer) {
            return $this->response->setJSON($customer);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cliente não encontrado'
            ])->setStatusCode(404);
        }
    }

    public function insertCustomer()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'JSON inválido']);
        }

        if ($this->model->insert($data)) {
            $id = $this->model->getInsertID();
            return $this->response
                ->setStatusCode(201)
                ->setJSON([
                    'id' => $id,
                    'message' => 'Cliente cadastrado com sucesso!'
                ]);
        }

        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Falha ao inserir cliente']);
    }

    // PUT /customers/{id}
    public function updateCustomer($id = null)
    {
        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) { //padrão code igniter para o update 
            return $this->response->setJSON($data);
        }

        return $this->response->setStatusCode(400)->setJSON(['error' => 'Falha ao atualizar o cliente']);
    }

    // DELETE /customers/{id}
    public function deleteCustomer($id = null)
    {
        try {
            if ($this->model->delete($id)) {
                return $this->response->setJSON([
                    "id" => $id,
                    "message" => "Cliente deletado com sucesso!"
                ])->setStatusCode(200);
            }

            return $this->response->setJSON([
                "error" => "Cliente não encontrado"
            ])->setStatusCode(404);

        } catch (\Exception $e) {
            // Verifica se é erro de chave estrangeira
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                return $this->response->setJSON([
                    "error" => "Não é possível excluir este cliente pois ele está vinculado a um ou mais aluguéis de mesas. Para excluir, primeiro remova todos os aluguéis associados a este cliente."
                ])->setStatusCode(409);
            }

            // Outros erros
            return $this->response->setJSON([
                "error" => "Erro interno ao tentar excluir cliente: " . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
