<?php

namespace App\Controllers;

use App\Models\DesksModel;
use CodeIgniter\RESTful\ResourceController;
use ResponseTrait;


class DesksController extends BaseController
{
    protected $modelName = DesksModel::class;
    protected $format    = 'json';


    public function __construct()
    {
        $this->model = new \App\Models\DesksModel();
    }

    public function getAllDesks()
    {
        return $this->response->setJSON($this->model->findAll());
    }

}
