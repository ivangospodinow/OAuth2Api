<?php
namespace App\Tests\Traits;

use Symfony\Component\HttpFoundation\Request;

trait RequestMockerTrait
{
    public function createRequestMock()
    {
        $request = $this->createMock(Request::class);

        return $request;
    }
}
