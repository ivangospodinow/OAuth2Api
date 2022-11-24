<?php

namespace App\Controller;

class IndexController extends AbstractController
{
    public function index()
    {
        // hello world
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
