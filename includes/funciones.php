<?php

function debuguear($variable) {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) {
    $s = htmlspecialchars($html);
    return $s;
}

// Función que revisa que el usuario este autenticado
function isAuth() {
    session_start();
    if(!isset($_SESSION['auth_user']) || !isset($_SESSION['login'])) {
        header('Location: /proyecto011/login');
        exit;
    }
}

function isAuthApi() {
    getHeadersApi();
    session_start();
    if(!isset($_SESSION['auth_user']) || !isset($_SESSION['login'])) {
        echo json_encode([    
            "mensaje" => "No está autenticado",
            "codigo" => 4,
        ]);
        exit;
    }
}

function isNotAuth(){
    session_start();
    if(isset($_SESSION['auth_user'])) {
        header('Location: /proyecto011/inicio');
        exit;
    }
}

function hasPermission(array $permisos){
    $comprobaciones = [];
    foreach ($permisos as $permiso) {
        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        header('Location: /proyecto011/login');
        exit;
    }
}

function hasPermissionApi(array $permisos){
    getHeadersApi();
    $comprobaciones = [];
    foreach ($permisos as $permiso) {
        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        echo json_encode([     
            "mensaje" => "No tiene permisos",
            "codigo" => 4,
        ]);
        exit;
    }
}

function getHeadersApi(){
    return header("Content-type:application/json; charset=utf-8");
}

function asset($ruta){
    return "/proyecto011/public/" . $ruta;
}