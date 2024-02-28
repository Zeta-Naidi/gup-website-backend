<?php

namespace App\Devices;

/**
 * Authorization object
 * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx#requesting_and_receiving_an_access_token
 */
class OAuthObject
{
  /**
   * OAuth token access
   * @var string
   */
  private $Token;
  /**
   * OAuth token type
   * @var string
   */
  private $TokenType;
  public function SetToken($val): void
  {
    $this->Token = $val;
  }
  public function GetToken(): string
  {
    return $this->Token;
  }
  public function SetTokenType($val): void
  {
    $this->TokenType = $val;
  }
  public function GetTokenType(): string
  {
    return $this->TokenType;
  }
  /**
   * Constructor, It can set also token and tokentype
   * @param array|null $oauth array of type ("token_type" => "value", "access_token" => "value")
   */
  public final function __construct(array $oauth = null)
  {
    if($oauth !== null)
    {
      if(isset($oauth["token_type"]) && isset($oauth["access_token"]))
      {
        $this->SetTokenType($oauth["token_type"]);
        $this->SetToken($oauth["access_token"]);
      }
      else
        throw new \InvalidArgumentException("Array doesn't contains Token or TokenType");
    }
  }
}
