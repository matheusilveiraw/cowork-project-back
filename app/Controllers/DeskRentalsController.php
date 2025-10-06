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

    if (!$data) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'JSON inválido']);
    }

    $required = ['idDesk', 'idCustomer', 'idPlan', 'startPeriod'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => "Campo obrigatório faltando: {$field}"]);
        }
    }

    $deskModel = new \App\Models\DesksModel();
    $mesa = $deskModel->find($data['idDesk']);
    if (!$mesa) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Mesa não encontrada']);
    }

    $customerModel = new \App\Models\CustomersModel();
    $cliente = $customerModel->find($data['idCustomer']);
    if (!$cliente) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Cliente não encontrado']);
    }

    $planModel = new \App\Models\RentalPlansModel();
    $plan = $planModel->find($data['idPlan']);
    
    if (!$plan) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Plano não encontrado']);
    }

    $categoryModel = new \App\Models\RentalCategoriesModel();
    $category = $categoryModel->find($plan['idCategory']);
    
    if (!$category) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Categoria do plano não encontrada']);
    }

    $startPeriod = \DateTime::createFromFormat('Y-m-d H:i:s', $data['startPeriod']);
    if (!$startPeriod) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Formato de data inválido. Use: YYYY-MM-DD HH:MM:SS']);
    }

    $now = new \DateTime();
    if ($startPeriod < $now) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Data de início não pode ser no passado']);
    }

    $endPeriod = clone $startPeriod;
    
    switch ($category['categoryName']) {
        case 'Diária':
            $endPeriod->modify('+0 day');
            break;
        case 'Semanal':
            $endPeriod->modify('+6 days');
            break;
        case 'Mensal':
            $endPeriod->modify('+29 days');
            break;
        default:
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Categoria de plano inválida']);
    }

    $shiftModel = new \App\Models\RentalShiftsModel();
    $shift = $shiftModel->find($plan['idShift']);
    
    if ($shift) {
        switch ($shift['shiftName']) {
            case 'Manhã':
                $endPeriod->setTime(12, 0, 0); 
                break;
            case 'Tarde':
                $endPeriod->setTime(18, 0, 0);
                break;
            case 'Integral':
                $endPeriod->setTime(18, 0, 0); 
                break;
        }
    }

    if (!$this->isDeskAvailable($data['idDesk'], $startPeriod->format('Y-m-d H:i:s'), $endPeriod->format('Y-m-d H:i:s'))) {
        return $this->response
            ->setStatusCode(409)
            ->setJSON(['error' => 'Mesa já está alugada neste período']);
    }

    $dadosInserir = [
        'idDesk' => $data['idDesk'],
        'idCustomer' => $data['idCustomer'],
        'idPlan' => $data['idPlan'],
        'startPeriod' => $startPeriod->format('Y-m-d H:i:s'),
        'endPeriod' => $endPeriod->format('Y-m-d H:i:s'),
        'total_price' => $plan['price']
    ];

    if ($this->model->insert($dadosInserir)) {
        $insertedId = $this->model->getInsertID();
        $dadosInserir['idDeskRental'] = $insertedId;
        
        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => 'success',
                'message' => 'Aluguel criado com sucesso',
                'data' => $dadosInserir
            ]);
    }

    return $this->response
        ->setStatusCode(400)
        ->setJSON(['error' => 'Falha ao inserir aluguel da mesa']);
}

    private function isDeskAvailable($deskId, $startPeriod, $endPeriod)
    {
        $conflictingRentals = $this->model
            ->where('idDesk', $deskId)
            ->where("(
                (startPeriod <= '{$startPeriod}' AND endPeriod >= '{$startPeriod}') OR
                (startPeriod <= '{$endPeriod}' AND endPeriod >= '{$endPeriod}') OR
                (startPeriod >= '{$startPeriod}' AND endPeriod <= '{$endPeriod}') OR
                (startPeriod <= '{$startPeriod}' AND endPeriod >= '{$endPeriod}')
            )")
            ->findAll();

        return empty($conflictingRentals);
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