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

        $shiftModel = new \App\Models\RentalShiftsModel();
        $shift = $shiftModel->find($plan['idShift']);

        if (!$shift) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Turno do plano não encontrado']);
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

        $startTime = $this->getStartTimeForShift($shift['shiftName']);
        $endTime = $this->getEndTimeForShift($shift['shiftName']);

        $startPeriod->setTime($startTime['hour'], $startTime['minute'], 0);

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

        $endPeriod->setTime($endTime['hour'], $endTime['minute'], 0);

        if (!$this->isDeskAvailableForShift($data['idDesk'], $startPeriod->format('Y-m-d H:i:s'), $endPeriod->format('Y-m-d H:i:s'), $shift['shiftName'])) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON(['error' => 'Mesa já está alugada neste período/turno']);
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

    private function getStartTimeForShift($shiftName)
    {
        switch ($shiftName) {
            case 'Manhã':
            case 'Integral':
                return ['hour' => 8, 'minute' => 0];
            case 'Tarde':
                return ['hour' => 13, 'minute' => 0];
            default:
                return ['hour' => 8, 'minute' => 0];
        }
    }

    private function getEndTimeForShift($shiftName)
    {
        switch ($shiftName) {
            case 'Manhã':
                return ['hour' => 12, 'minute' => 0];
            case 'Tarde':
                return ['hour' => 18, 'minute' => 0];
            case 'Integral':
                return ['hour' => 18, 'minute' => 0];
            default:
                return ['hour' => 18, 'minute' => 0];
        }
    }

    private function isDeskAvailableForShift($deskId, $startPeriod, $endPeriod, $shiftName)
    {
        $existingRentals = $this->model
            ->where('idDesk', $deskId)
            ->findAll();

        if (empty($existingRentals)) {
            return true;
        }

        $newStart = new \DateTime($startPeriod);
        $newEnd = new \DateTime($endPeriod);

        foreach ($existingRentals as $rental) {
            $existingStart = new \DateTime($rental['startPeriod']);
            $existingEnd = new \DateTime($rental['endPeriod']);

            $existingPlan = (new \App\Models\RentalPlansModel())->find($rental['idPlan']);
            $existingShift = (new \App\Models\RentalShiftsModel())->find($existingPlan['idShift']);

            if ($newStart < $existingEnd && $newEnd > $existingStart) {
                if (!$this->areShiftsCompatible($shiftName, $existingShift['shiftName'])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function areShiftsCompatible($newShift, $existingShift)
    {
        $compatibleShifts = [
            'Manhã' => ['Tarde'],
            'Tarde' => ['Manhã'],
            'Integral' => []
        ];

        return in_array($existingShift, $compatibleShifts[$newShift] ?? []);
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

    public function deleteDeskRental($id = null)
    {
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

    public function checkMonthAvailability()
    {
        $data = $this->request->getJSON(true);

        if (!$data || !isset($data['idDesk']) || !isset($data['startDate']) || !isset($data['endDate'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Dados inválidos']);
        }

        $deskId = $data['idDesk'];
        $startDate = new \DateTime($data['startDate']);
        $endDate = new \DateTime($data['endDate']);

        $rentals = $this->model
            ->where('idDesk', $deskId)
            ->where('startPeriod <=', $endDate->format('Y-m-d 23:59:59'))
            ->where('endPeriod >=', $startDate->format('Y-m-d 00:00:00'))
            ->findAll();

        $disponibilidade = [];

        foreach ($rentals as $rental) {
            $rentalStart = new \DateTime($rental['startPeriod']);
            $rentalEnd = new \DateTime($rental['endPeriod']);

            $plan = (new \App\Models\RentalPlansModel())->find($rental['idPlan']);
            $shift = (new \App\Models\RentalShiftsModel())->find($plan['idShift']);
            $shiftName = $shift['shiftName'] ?? 'Integral';

            $currentDate = clone $rentalStart;
            while ($currentDate <= $rentalEnd) {
                if ($currentDate >= $startDate && $currentDate <= $endDate) {
                    $dateString = $currentDate->format('Y-m-d');

                    if (!isset($disponibilidade[$dateString])) {
                        $disponibilidade[$dateString] = ['manha' => null, 'tarde' => null];
                    }

                    // Marcar turnos ocupados
                    if ($shiftName === 'Manhã' || $shiftName === 'Integral') {
                        $disponibilidade[$dateString]['manha'] = 'parcial';
                    }
                    if ($shiftName === 'Tarde' || $shiftName === 'Integral') {
                        $disponibilidade[$dateString]['tarde'] = 'parcial';
                    }

                    if ($disponibilidade[$dateString]['manha'] && $disponibilidade[$dateString]['tarde']) {
                        $disponibilidade[$dateString]['manha'] = 'integral';
                        $disponibilidade[$dateString]['tarde'] = 'integral';
                    }
                }
                $currentDate->modify('+1 day');
            }
        }

        return $this->response->setJSON([
            'disponibilidade' => $disponibilidade
        ]);
    }
}