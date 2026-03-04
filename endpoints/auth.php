<?php
// ============ RUTAS DE AUTENTICACIÓN (PÚBLICAS) ============
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');
$router->get('/auth/verify', 'AuthController@verify');
$router->post('/auth/refresh', 'AuthController@refresh');
$router->post('/auth/logout', 'AuthController@logout');
$router->get('/auth/check-email', 'AuthController@checkEmail');
// Solicitar recuperación (genera token y envía correo)
$router->post('/auth/forgot-password', 'AuthController@forgotPassword');

// Restablecer contraseña (valida token y cambia la clave)
$router->post('/auth/reset-password', 'AuthController@resetPassword');
?>