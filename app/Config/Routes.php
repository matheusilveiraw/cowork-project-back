<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// $routes->get('customers', 'CustomersController::index');

$routes->setAutoRoute(true);

// CUSTOMERS ROUTES --------------------------------

$routes->get('customers', 'CustomersController::getAllCustomers'); // listar todos
// GET http://localhost/projetos/cowork-project-back/public/customers → lista todos

$routes->get('customers/(:num)', 'CustomersController::getCustomerById/$1'); // buscar por ID
// GET http://localhost/projetos/cowork-project-back/public/customers/1 

$routes->post('customers', 'CustomersController::insertCustomer'); // inserir
// POST http://localhost/projetos/cowork-project-back/public/customers
// (body JSON exemplo: { "name": "Matheus", "email": "teste@test.com", "phone": "12345", "address": "Rua X" })

$routes->put('customers/(:num)', 'CustomersController::updateCustomer/$1'); // atualizar
// PUT http://localhost/projetos/cowork-project-back/public/customers/1
// (body JSON exemplo: { "name": "Matheus", "email": "teste@test.com", "phone": "12345", "address": "Rua X" })

$routes->delete('customers/(:num)', 'CustomersController::deleteCustomer/$1'); // deletar
// DELETE http://localhost/projetos/cowork-project-back/public/customers/1

// CUSTOMERS ROUTES OFF  -------------------------------- 

// DESKS ROUTE ON -----------------------------------

$routes->get('desks', 'DesksController::getAllDesks');
// GET http://localhost/projetos/cowork-project-back/public/desks

$routes->get('desks/(:num)', 'DesksController::getDesksById/$1');
// GET http://localhost/projetos/cowork-project-back/public/desks/1 

$routes->post('desks', 'DesksController::insertDesk');
// POST http://localhost/projetos/cowork-project-back/public/desks
// (body JSON exemplo: { "deskNumber": "8", "deskName": "apotoat" })

$routes->put('desks/(:num)', 'DesksController::updateDesk/$1'); // atualizar
// PUT http://localhost/projetos/cowork-project-back/public/desk/1
// { "deskNumber": "8", "deskName": "apotoat" })

