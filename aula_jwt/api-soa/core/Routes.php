<?php
require_once 'Http.php';

// Auth
Http::post('/login', 'AuthController@login');

// Clientes
Http::get('/clientes/find/{id}', 'ClienteController@find');
Http::get('/clientes/findAll', 'ClienteController@listar');
Http::post('/clientes/add', 'ClienteController@criar');
Http::put('/clientes/edit/{id}', 'ClienteController@atualizar');
Http::delete('/clientes/delete/{id}', 'ClienteController@deletar');
Http::put('/clientes/inativo/{id}', 'ClienteController@marcarInativo');

// Veículos
Http::get('/veiculos/find/{id}', 'VeiculoController@find');
Http::get('/veiculos/findAll', 'VeiculoController@listar');
Http::post('/veiculos/add', 'VeiculoController@criar');
Http::put('/veiculos/edit/{id}', 'VeiculoController@atualizar');
Http::delete('/veiculos/delete/{id}', 'VeiculoController@deletar');

// Estacionamento
Http::post('/estacionamento/entrada/{id_veiculo}', 'EstacionamentoController@entrada');
Http::post('/estacionamento/saida/{id_veiculo}', 'EstacionamentoController@saida');
Http::get('/estacionamento/historico', 'EstacionamentoController@historico');

// **ROTAS QUE FALTAM:**
Http::get('/estacionamento/ativos', 'EstacionamentoController@veiculosAtivos'); // Veículos atualmente no estacionamento
Http::get('/clientes/ativos', 'ClienteController@listarAtivos'); // Apenas clientes ativos
Http::get('/veiculos/cliente/{id_cliente}', 'VeiculoController@listarPorCliente'); // Veículos de um cliente específico