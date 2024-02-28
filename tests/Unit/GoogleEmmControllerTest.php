<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\GoogleEmmController;

class GoogleEmmControllerTest extends TestCase
{
  public function controller_generates_signup_url()
  {
    $controller = new GoogleEmmController('AIzaSyAjNYTcsb5S9tSSg0ipscf3kUQeNqxTVlQ', base_path() . '\app\chimpa-mdm-9a94557cc550.json');
    $url = $controller->generateSignupUrl();

    $this->assertNotNull($url);
    echo ('url: ' . $url);
  }
}
