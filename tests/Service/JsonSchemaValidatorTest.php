<?php

namespace Test;

use App\Service\JsonSchemaValidator;
use App\Tests\Traits\InvokeObject;
use App\Tests\Traits\RequestMockerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JsonSchemaValidatorTest extends TestCase
{
    use RequestMockerTrait, InvokeObject;

    /**
     * @var JsonSchemaValidator
     */
    protected $instance;

    protected function setUp(): void
    {
        $this->instance = new class extends JsonSchemaValidator
        {

        };
    }

    public function testValidateSchemaWithErrorReponseNotValidWithEmptyRequest()
    {
        $request = $this->createRequestMock();
        $errorsReponse = $this->instance->validateSchemaWithErrorReponse($request, 'ProjectCreate.json');
        $this->assertInstanceOf(JsonResponse::class, $errorsReponse);

        $arrayReponse = json_decode($errorsReponse->getContent(), true);
        $this->assertSame(422, $arrayReponse['code']);
        $this->assertNotEmpty($arrayReponse['errors']);
    }

    public function testValidateSchemaWithErrorReponseNotValidWithSomeParams()
    {
        $post = [
            'title' => 'This a title prop',
        ];
        $request = $this->createRequestMock();
        $request->method('getContent')->willReturn(json_encode($post));

        $errorsReponse = $this->instance->validateSchemaWithErrorReponse($request, 'ProjectCreate.json');
        $this->assertInstanceOf(JsonResponse::class, $errorsReponse);

        $arrayReponse = json_decode($errorsReponse->getContent(), true);
        $this->assertSame(422, $arrayReponse['code']);
        $this->assertNotEmpty($arrayReponse['errors']);
        $this->assertSame('client', $arrayReponse['errors'][0]['property']);
    }

    public function testValidateSchemaWithErrorReponseValid()
    {
        $post = [
            'title' => 'This a title prop',
            'description' => 'This a description prop',
            'status' => 'pending',
            'duration' => 'P1M',
            'client' => 'Client name',
        ];
        $request = $this->createRequestMock();
        $request->method('getContent')->willReturn(json_encode($post));

        $errorsReponse = $this->instance->validateSchemaWithErrorReponse($request, 'ProjectCreate.json');
        $this->assertFalse($errorsReponse);

        $this->assertSame(array(
            'title' => 'This a title prop',
            'description' => 'This a description prop',
            'status' => 'pending',
            'duration' => 'P1M',
            'client' => 'Client name',
        ), (array) $this->instance->getData($request));

        $this->assertSame(array(
            'title' => 'This a title prop',
            'description' => 'This a description prop',
            'status' => 'pending',
            'duration' => 'P1M',
            'client' => 'Client name',
        ), $this->instance->getArrayData($request));
    }

    public function testIntervalValidator()
    {
        $this->assertTrue($this->invokeMethod($this->instance, 'validateDateInterval', ['P1D']));
        $this->assertFalse($this->invokeMethod($this->instance, 'validateDateInterval', ['this is not a period']));
    }

    public function testCreateErrorsResponse()
    {
        $response = $this->invokeMethod($this->instance, 'createErrorsResponse', [['error']]);
        $arrayReponse = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $arrayReponse['code']);
    }

}
