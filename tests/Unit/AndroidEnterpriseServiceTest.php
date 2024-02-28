<?php

namespace Tests\Unit;

use Tests\TestCase;
use Google_Client;
use App\Services\AndroidEnterpriseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;

class AndroidEnterpriseServiceTest extends TestCase
{
  use RefreshDatabase;
  use InteractsWithExceptionHandling;
  public function testGenerateSignupUrl()
  {
    $this->withoutExceptionHandling();

    $mockGoogleClient = $this->createMock(Google_Client::class);
//    $mockGoogleClient = factory(Google_Client::class);
    $service = new AndroidEnterpriseService($mockGoogleClient);
    $response = $service->generateSignupUrl('www.test.it');
    $this->assertNotNull($response);
    echo ($response);
  }
}
