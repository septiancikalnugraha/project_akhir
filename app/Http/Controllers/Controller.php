<?php

namespace App\Http\Controllers;

class Controller
{
    protected function view($view, $data = [])
    {
        // Load view with data
        extract($data);
        require_once __DIR__ . "/../../../resources/views/{$view}.php";
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 