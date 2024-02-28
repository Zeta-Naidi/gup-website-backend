<?php

namespace App\Models;
//namespace App\Payloads\Common;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Payloads\Common\WebContent_Filter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use CFPropertyList\{
  CFPropertyList, CFDictionary, CFString, CFNumber, CFData, CFDate, CFArray, CFBoolean
};

$payloadDirectories = [
  'Windows',
  'Common',
  'Apple',
  'Android',
];

foreach ($payloadDirectories as $directory) {
  $pattern = "/app/Payloads/{$directory}/*.php";

  foreach (glob($pattern) as $payloadFilename) {
    require_once $payloadFilename;
  }
}

/**
 * @property-read int $payloadId
 * @property-read int $profileId
 * @property-read string $PayloadType
 * @property-read string $AndroidCommandType
 * @property-read string $ApplePayloadType
 * @property bool $changed
 * @property            string $PayloadDisplayName
 * @property-read       string $PayloadUUID
 * @property-read       bool $toRemove
 * @property            string $PayloadDescription
 *
 * @property-read       CFDictionary $payloadPlist
 *
 * @method              array|string  getParameters()
 * @method                            getSchema()
 * @method                            setParameters()
 * @method              array         getAndroidParameters(bool $clearValues = false, bool $legacyMode = false)
 * @method              bool    checkCompatibility(int | string $deviceIdentifier = NULL, Device $device = NULL, string $specialOsType = NULL)
 */

class Payload extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

    use HasFactory;
    public $timestamps = false;

    protected $table = 'payloads';

    protected $profileId;
    protected $payloadId;
  protected $PayloadDisplayName;
  protected $PayloadUUID;
  protected $PayloadDescription;
  protected $PayloadOrganization;
  protected $PayloadVersion;
  protected $PayloadType;
  protected $AndroidCommandType;
  protected $ApplePayloadType;
  protected $changed;
  protected $fromDb;
  protected $toRemove;
  protected $plistMode;
  protected $payloadPlist;

  public function getPayloadType():string{
    return $this->PayloadType;
  }

  public function setPlistMode(bool $isPlistMode){
    $this->plistMode = $isPlistMode;
  }

  public function getApplePayloadType():string{
    return $this->ApplePayloadType;
  }

  public function setPayloadDisplayName(string $displayName){
    $this->PayloadDisplayName = $displayName;
  }
  public function setPayloadDescription(string $description){
    $this->PayloadDescription = $description;
  }

  /*public function __construct(string $PayloadDisplayName = NULL, string $PayloadUUID = NULL, string $PayloadDescription = NULL, string $PayloadOrganization = NULL, int $PayloadVersion = NULL, bool $plistMode = NULL, int $specialPayloadId = NULL, bool $ovverrideName = false)
  {

    $this->PayloadVersion = 1;
    if (!is_null($PayloadDisplayName)) $this->PayloadDisplayName = $PayloadDisplayName;   //mandatory
    if (!is_null($PayloadUUID)) $this->PayloadUUID = $PayloadUUID;  //mandatory
    if (!is_null($PayloadDescription)) $this->PayloadDescription = $PayloadDescription;   //optional
    if (!is_null($PayloadOrganization)) $this->PayloadOrganization = $PayloadOrganization; //optional
    if (!is_null($PayloadVersion)) $this->PayloadVersion = $PayloadVersion;   //optional
    if (!is_null($plistMode) && is_bool($plistMode)) { //optional
      $this->plistMode = $plistMode;
    } else {
      $this->plistMode = false;
    }

    if (is_null($this->PayloadUUID)) $this->PayloadUUID = UUID::v4();
    if (is_null($this->PayloadOrganization)) {
      if (defined("SCHOOL_CODE")) {
        $this->PayloadOrganization = "SCHOOL_CODE";
      } else {
        $this->PayloadOrganization = "ORGANIZATION";
      }
    }

    try {
      if (!$ovverrideName) {
        $classTemp = new ReflectionClass ('PayloadType');
        $constantsTemp = $classTemp->getConstants();
        $keyTemp = array_search($this->PayloadType, $constantsTemp);

        if (in_array(strtoupper($keyTemp), array("CERTIFICATE_PKCS1", "CERTIFICATE_PKCS12", "CERTIFICATE_ROOT", "CERTIFICATE_PEM"))) $keyTemp = "CERTIFICATES";

        $this->PayloadDisplayName = langText("PAYLOAD_" . strtoupper($keyTemp), "en", 'panel') . " (" . $this->PayloadUUID . ")";
      }
    } catch (Exception $exception) {

    }

    if ($plistMode) {
      $this->changed = false;
      $this->fromDb = false;
      $this->toRemove = false;

      $this->payloadPlist = new CFDictionary();
      $this->payloadPlist->add('PayloadDisplayName', new CFString($this->PayloadDisplayName));

      if (!is_null($this->PayloadDescription)) {
        $this->payloadPlist->add('PayloadDescription', new CFString($this->PayloadDescription));
      }
      if (!is_null($this->PayloadOrganization)) {
        $this->payloadPlist->add('PayloadOrganization', new CFString($this->PayloadOrganization));
      } elseif (defined("SCHOOL_CODE")) {
        $this->payloadPlist->add('PayloadOrganization', new CFString("SCHOOL_CODE"));
      } else {
        $this->payloadPlist->add('PayloadOrganization', new CFString("ORGANIZATION"));
      }

      $this->payloadPlist->add('PayloadVersion', new CFNumber($this->PayloadVersion));
      $this->payloadPlist->add('PayloadUUID', new CFString($this->PayloadUUID));
    } else {
      $this->toRemove = false;
      if (!is_null($specialPayloadId)) {
        $this->payloadId = $specialPayloadId;
        $this->changed = false;
        $this->fromDb = true;
      } else {
        $this->changed = true;
        $this->fromDb = false;
      }
    }


  }*/

  public static function getApplePayloadTypeFromPayloadType(string $payloadType = PayloadType::WEB_CONTENT_FILTER): string
  {
    if (class_exists('Payload__' . $payloadType)) {
      $className = 'Payload__' . $payloadType;
      /** @var Payload $tmpPayload */
      $tmpPayload = new $className();
      $applePayloadType = $tmpPayload->getApplePayloadType();
      if (!is_null($applePayloadType)) {
        return self::getPayloadTypeFromApplePayloadType($applePayloadType);
      }

    } else {
      return 0;
    }
    return 0;
  }

  public static function getPayloads(string $osType = null) {

  }

  protected function checkWhitelabelIsCompatible(): bool
  {
    $compatibility = true;
    /*if (defined("IS_WHITELABEL") && IS_WHITELABEL === true) {
      LoadConfig::setConstant('WHITELABEL_CONFIGS');
      if (defined('WHITELABEL_CONFIGS_DECODED') && isset(WHITELABEL_CONFIGS_DECODED["disabledFunctions"]) && !empty(WHITELABEL_CONFIGS_DECODED["disabledFunctions"]) && !empty(WHITELABEL_CONFIGS_DECODED["disabledFunctions"]["disabledPayloads"])) {
        if (!is_null($this)) {
          $payloadType = $this->PayloadType;
        } else {
          eval('$payloadTempFallback = new ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]["class"] . "();");
          $payloadType = $payloadTempFallback->payloadType;
        }
        $compatibility = !in_array($payloadType, WHITELABEL_CONFIGS_DECODED["disabledFunctions"]["disabledPayloads"]);
      }
    }*/

    return $compatibility;
  }


  public static function getCountByApplePayloadType(string $applePayloadType): int
  {

    $query = 'SELECT COUNT(applePayloadType) FROM payloads WHERE applePayloadType = "' . $applePayloadType . '"';
    $db = new Database();
    $count = $db->fetchQueryRow($query);
    $count = (int)$count[0][0];
    unset($db);
    if (!isset($count) || $count === null)
      $count = 0;
    return $count;
  }


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
      'payloadUUID',
      'profileId',
      'payloadDisplayName',
      'payloadDescription',
      'payloadOrganization',
      'applePayloadType',
      'params',
      'payloadVersion',
  ];
}
