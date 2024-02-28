<?php

namespace App\Services;
class AndroidEnterpriseService
{
  const ANDROIDENTERPRISE = "https://www.googleapis.com/auth/androidenterprise";

  private \Google_Client $googleClient;
  private \Google\Service\AndroidEnterprise $enterpriseService;
  private \Google\Service\AndroidEnterprise\Resource\Enterprises $enterprises;

  public function __construct(\Google_Client $_googleClient)
  {
    $this->googleClient = $_googleClient;
    $this->enterpriseService = new \Google\Service\AndroidEnterprise($this->googleClient);
    $this->enterprises = $this->enterpriseService->enterprises;
  }

  public function generateSignupUrl($callbackUrl): \Google\Service\AndroidEnterprise\SignupInfo
  {
    return $this->enterprises->generateSignupUrl(['callbackUrl' => $callbackUrl]);
  }

  public function signupCallback(string $completionToken, string $enterpriseToken): \Google\Service\AndroidEnterprise\Enterprise
  {
    $this->googleClient->setApplicationName("AndroidEnterprise");
    return $this->enterpriseService->enterprises->completeSignup(["completionToken" => $completionToken, "enterpriseToken" => $enterpriseToken]);
  }

  //public function checkEmmConfigurationCompliancy() {}
}
