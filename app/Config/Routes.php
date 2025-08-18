<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// $routes->get('customers', 'CustomersController::index');

$routes->setAutoRoute(true);

$routes->get('customers', 'CustomersController::index'); // listar todos
$routes->get('customers/(:num)', 'CustomersController::show/$1'); // buscar por ID
$routes->post('customers', 'CustomersController::create'); // inserir
$routes->put('customers/(:num)', 'CustomersController::update/$1'); // atualizar
$routes->delete('customers/(:num)', 'CustomersController::delete/$1'); // deletar


// GET http://localhost/projetos/cowork-backend/public/customers → lista todos

// GET http://localhost/projetos/cowork-backend/public/customers/1 → pega o ID 1

// POST http://localhost/projetos/cowork-backend/public/customers
// (body JSON exemplo: { "name": "Matheus", "email": "teste@test.com", "phone": "12345", "address": "Rua X" })

// PUT http://localhost/projetos/cowork-backend/public/customers/1

// DELETE http://localhost/projetos/cowork-backend/public/customers/1