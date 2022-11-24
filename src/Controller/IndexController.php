<?php

namespace App\Controller;

use Exception;

class IndexController extends AbstractController
{
    public function index()
    {
        // hello world
        return $this->json([
            'status' => 'ok',
        ]);
    }

    public function unauthorized()
    {
        throw new Exception('unauthorized');
    }

}
