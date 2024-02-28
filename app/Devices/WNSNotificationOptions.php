<?php

namespace App\Devices;

use ReflectionException;

/**
 * This class represent the headers options.
 * For details:
 * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx
 */
class WNSNotificationOptions
{

  /**
   * Current token. It can be set by SetToken method. A token is valid for 24h
   * @var OAuthObject
   * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx#requesting_and_receiving_an_access_token
   */
  private $Authorization = null;
  /**
   * Notification type
   * @var string
   */
  private $X_WNS_TYPE = X_WNS_Type::__default;
  /**
   * Cache policy
   * @var string
   */
  private $X_WNS_CACHE_POLICY = X_WNS_Cache_Policy::__default;
  /**
   * The request status flag
   * @var string
   */
  private $X_WNS_REQUESTFORSTATUS = X_WNS_RequestForStatus::__default;
  /**
   * Suppress popup flag
   * @var string
   */
  private $X_WNS_SUPRESSPOPUP = X_WNS_SuppressPopup::__default;
  /**
   * The content type
   * @var string
   */
  private $ContentType = Content_Type::__default;

  /**
   * The notification Tag for notification queue (only for tile)
   * @var string
   */
  private $X_WNS_TAG = null;
  /**
   * TTL duration
   * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx#pncodes_x_wns_ttl
   * @var integer
   */
  private $X_WNS_TTL = null;
  /**
   * Either TAG this regroup notification in operation center
   * @var string
   */
  private $X_WNS_GROUP = null;
  /**
   * For notification delete
   * @var string
   */
  private ?string $X_WNS_MATCH = null;

  /**
   * Set the token for push requests
   * @param OAuthObject $token The token
   * @throws  \InvalidArgumentException if the provided argoument isn't of type string
   */
  public function SetAuthorization(OAuthObject $token): void
  {
    if($token instanceof OAuthObject)
      $this->Authorization = $token;
    else
      throw new \InvalidArgumentException("The token must be a type OAuthObject");
  }
  /**
   * Set the tile type
   * @param string $type
   * @throws  \InvalidArgumentException if the provided type isn't of type X_WNS_Type
   */
  public function SetX_WNS_TYPE(string $type): void
  {
    if($this->IsValidParam($type,"X_WNS_Type"))
      $this->X_WNS_TYPE = $type;
    else
      throw new \InvalidArgumentException("The type must be a X_WNS_Type value");
  }
  /**
   * Set the cache policy
   * @param string $cp
   * @throws  \InvalidArgumentException if the provided type isn't of type X_WNS_Cache_Policy
   */
  public function SetX_WNS_CACHE_POLICY(string $cp): void
  {
    if($this->IsValidParam($cp,"X_WNS_Cache_Policy"))
      $this->X_WNS_CACHE_POLICY = $cp;
    else
      throw new \InvalidArgumentException("The type must be a X_WNS_Cache_Policy value");
  }
  /**
   * Set the request for status header
   * @param string $request
   * @throws  \InvalidArgumentException if the provided type isn't of type X_WNS_RequestForStatus
   */
  public function SetX_WNS_REQUESTFORSTATUS(string $request): void
  {
    if($this->IsValidParam($request,"X_WNS_RequestForStatus"))
      $this->X_WNS_REQUESTFORSTATUS = $request;
    else
      throw new \InvalidArgumentException("The type must be a X_WNS_RequestForStatus value");
  }
  /**
   * Set the suppresspopup
   * @param string $val
   */
  public function SetX_WNS_SUPRESSPOPUP(string $val): void
  {
    if($this->IsValidParam($val,"X_WNS_SuppressPopup"))
      $this->X_WNS_SUPRESSPOPUP = $val;
    else
      throw new \InvalidArgumentException("The type must be a X_WNS_SuppressPopup value");
  }
  /**
   * Set the content type for notification header
   * @param string $val
   */
  public function SetContentType(string $val): void
  {
    if($this->IsValidParam($val,"Content_Type"))
      $this->ContentType = $val;
    else
      throw new \InvalidArgumentException("The type must be a Content_Type value");
  }

  /**
   * Set notification tag
   * @param string $val
   */
  public function SetX_WNS_TAG(string $val): void
  {
    if(is_string($val))
      $this->X_WNS_TAG = $val;
    else
      throw new \InvalidArgumentException("The type must be a string");
  }
  /**
   * Set the expiration time
   * @param int $val
   */
  public function SetX_WNS_TTL(int $val): void
  {
    if(is_int($val))
      $this->X_WNS_TTL = $val;
    else
      throw new \InvalidArgumentException("The type must be an integer");
  }
  /**
   * Set the group
   * @param string $val
   */
  public function SetX_WNS_GROUP(string $val): void
  {
    if(is_string($val))
      $this->X_WNS_GROUP = $val;
    else
      throw new \InvalidArgumentException("The type must be a string");
  }

  /**
   * Set the X-WNS-Match param. You shoud set array with the match type.
   * X_WNS_Match::Tag -> (1)
   * X_WNS_Match::TagAndGroup -> (2)
   * X_WNS_Match::Group -> (3)
   * NOTE: If you don't want use match, don't call the set!
   * NOTE: If omitted the request will be ALL
   * NOTE: This work only on windows phone, if you send this header for windows notification, you'll get an error
   * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx#pncodes_x_wns_match
   * @param int $match X_WNS_Match value
   * @param array $params array of type (1)->("tag"=>"value")|(2)->("tag"=>"value", "group" => "value")|(3)->("group"=>"value")
   * @throws  \InvalidArgumentException if the provided argouments are wrong
   */
  public function SetX_WNS_MATCH($match, $params = null): void
  {
    if($this->IsValidParam($match,"X_WNS_Match"))
    {
      switch($match)
      {
        case X_WNS_Match::Tag :
        {
          if(isset($params["tag"]))
          {
            $this->X_WNS_MATCH = "type=wns/toast;tag=".$params["tag"];
          }
          else
            throw new \InvalidArgumentException("The params must contain tag definition for this match type");
          break;
        }
        case X_WNS_Match::Group :
        {
          if(isset($params["group"]))
          {
            $this->X_WNS_MATCH = "type=wns/toast;group=".$params["group"];
          }
          else
            throw new \InvalidArgumentException("The params must contain group definition for this match type");
          break;
        }
        case X_WNS_Match::TagAndGroup :
        {
          if(isset($params["group"]) && isset($params["tag"]))
          {
            $this->X_WNS_MATCH = "type=wns/toast;group=".$params["group"].";tag=".$params["tag"];
          }
          else
            throw new \InvalidArgumentException("The params must contain group and tag definition for this match type");
          break;
        }
        case X_WNS_Match::All : { $this->X_WNS_MATCH = "type=wns/toast;all"; break; }
        default: $this->X_WNS_MATCH = null;
      }
    }
    else
      throw new \InvalidArgumentException("The match must be a X_WNS_Match value");
  }

  /**
   * Get the token for push requests
   * @return OAuthObject|null The authorization token
   */
  public function GetAuthorization(): ?OAuthObject
  {
    return $this->Authorization;
  }
  /**
   * Get the tile type
   * @return string Return the notification type
   */
  public function GetX_WNS_TYPE(): string
  {
    return $this->X_WNS_TYPE;
  }
  /**
   * Get the cache policy
   * @return string The cache policy
   */
  public function GetX_WNS_CACHE_POLICY(): string
  {
    return $this->X_WNS_CACHE_POLICY;
  }
  /**
   * Get the request for status header
   * @return string
   */
  public function GetX_WNS_REQUESTFORSTATUS(): string
  {
    return $this->X_WNS_REQUESTFORSTATUS;
  }
  /**
   * Get the SuppressPopup
   * @return string
   */
  public function GetX_WNS_SUPRESSPOPUP(): string
  {
    return $this->X_WNS_SUPRESSPOPUP;
  }

  /**
   * Get the content type
   * @return string
   */
  public function GetContentType(): string
  {
    return $this->ContentType;
  }

  /**
   * Get the notification tag setting
   * @return string|null
   */
  public function GetX_WNS_TAG(): ?string
  {
    return $this->X_WNS_TAG;
  }

  /**
   * Get the TTL setting
   * @return int|string|null
   */
  public function GetX_WNS_TTL(): int|string|null
  {
    return $this->X_WNS_TTL;
  }

  /**
   * Get the notification group
   * @return string|null
   */
  public function GetX_WNS_GROUP(): ?string
  {
    return $this->X_WNS_GROUP;
  }


  /**
   * Get the header for auth access the token for push requests
   * @return string The authorization token
   */
  public function GetHeaderAuthorization(): string
  {
    return "Authorization: ".$this->Authorization->GetTokenType()." ".$this->Authorization->GetToken();
  }
  /**
   * Get the Header for the tile type
   * @return string Return the notification type
   */
  public function GetHeaderX_WNS_TYPE(): string
  {
    return "X-WNS-Type: ".$this->X_WNS_TYPE;
  }
  /**
   * GetHeader the cache policy
   * @return string The cache policy
   */
  public function GetHeaderX_WNS_CACHE_POLICY(): string
  {
    return "X-WNS-Cache-Policy: ".$this->X_WNS_CACHE_POLICY;
  }
  /**
   * GetHeader the request for status header
   * @return string
   */
  public function GetHeaderX_WNS_REQUESTFORSTATUS(): string
  {
    return "X-WNS-RequestForStatus: ".$this->X_WNS_REQUESTFORSTATUS;
  }
  /**
   * GetHeader the SuppressPopup
   * @return string
   */
  public function GetHeaderX_WNS_SUPRESSPOPUP(): string
  {
    return "X-WNS-SuppressPopup: ".$this->X_WNS_SUPRESSPOPUP;
  }

  /**
   * Get the Header for the content type
   * @return string
   */
  public function GetHeaderContentType(): string
  {
    return "content-type: ".$this->ContentType;
  }
  /**
   * Return the content length
   * @param string $body the body
   * @return string
   */
  public function GetHeaderContentLenght(string $body): string
  {
    return "Content-length: ".strlen($body);
  }
  /**
   * GetHeader the notification tag setting
   * @return string
   */
  public function GetHeaderX_WNS_TAG(): string
  {
    return "X-WNS-Tag: ".$this->X_WNS_TAG;
  }
  /**
   * GetHeader the TTL setting
   * @return string
   */
  public function GetHeaderX_WNS_TTL(): string
  {
    return "X-WNS-TTL: ".$this->X_WNS_TTL;
  }
  /**
   * GetHeader the notification group
   * @return
   */
  public function GetHeaderX_WNS_GROUP(): string
  {
    return "X-WNS-Group: ".$this->X_WNS_GROUP;
  }

  /**
   * Get the X-WNS-Match param. You shoud Get array with the match type.
   * NOTE: If omitted the request will be ALL
   * NOTE: This work only on windows phone, if you send this header for windows notification, you'll get an error
   * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx#pncodes_x_wns_match
   * @throws  \InvalidArgumentException if the provided argouments are wrong
   * @return string
   */
  public function GetHeaderX_WNS_MATCH(): string
  {
    return "X-WNS-Match: ".$this->X_WNS_MATCH;
  }

  /**
   * Return the array with header setted for notification by the options settings
   * @return array Return the array with header setted for notification by the options settings
   * @throws ReflectionException
   */
  public function GetHeaderArray(): array
  {
    $result = array();
    $refl = new \ReflectionClass($this);
    foreach ($this as $key => $val)
    {
      if($val!==null)
        $result[$key] = $refl->getMethod("GetHeader".$key)->invoke($this);
    }
    return $result;
  }
  /**
   * Check if passed parameter belongs to a class
   * @param string $string value
   * @param string $class class name
   * @return true if yes, false elese
   */
  private function IsValidParam(string $string, string $class): bool
  {
    $result = false;
    $class = $this->GetClassConstants(__NAMESPACE__."\\".$class);
    foreach ($class as $value)
    {
      if($value == $string)
      {
        $result = true;
        break;
      }
    }
    return $result;
  }

  /**
   * Return class const from class name
   * @param string $class
   * @return array with constants
   * @throws ReflectionException
   */
  private function GetClassConstants(string $class): array
  {
    $class = new \ReflectionClass($class);
    return $class->getConstants();
  }

}
