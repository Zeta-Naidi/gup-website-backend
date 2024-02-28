<?php

namespace App\Payloads\Apple;

use App\Models\Payload;
use App\Payloads\Common\Device;
use App\Payloads\Common\DeviceModel;
use App\Payloads\Common\Exception;
use App\Payloads\Common\OsType;

class Os_System_Update_Config extends Payload
{
  public array $availableOs = ['Apple'];

    protected $keepOsUpdated;
    protected $keepAppUpdated;

    //protected $AndroidCommandType = CommandType::OS_SYSTEM_UPDATE_CONFIG;
    //protected $PayloadType = PayloadType::OS_SYSTEM_UPDATE_CONFIG;
    protected $ApplePayloadType = 'com.apple.os.system.update.config';

  public function checkCompatibility($osType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ($osType == "android" || $osType == "ios") {
      $return = true;
    }

    return $return;

  }

  public function checkDeviceCompatibility($deviceIdentifier = NULL, Device $device = NULL, string $specialOsType = NULL): bool
  {
    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if (!is_null($device)) {
      //niente
    } elseif (!is_null($deviceIdentifier)) {
      if (is_null($GLOBALS["device"])) {
        $GLOBALS["device"] = new Device($deviceIdentifier);
      }
      $device =& $GLOBALS["device"];
    } elseif (!is_null($specialOsType)) {
      //niente
    } else {
      throw new Exception("all args are null");
    }

    if ($device->osType == OsType::ANDROID || $specialOsType == OsType::ANDROID) {
      $return = true;
    } elseif (($device->osType == OsType::IOS && $device->isSupervised === 1) || $specialOsType == OsType::IOS) {
      if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD) {
        $return = true;
      } elseif ($specialOsType == OsType::IOS) {
        $return = true;
      }
    }

    return $return;

  }

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WEB_CONTENT_FILTER.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "WEB_CONTENT_FILTER",
      "description" => "WEB_CONTENT_FILTER_DESCRIPTION",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WEB_CONTENT_FILTER.png",
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ["Windows", "Apple"],
        "label" => "Mantenere il sistema operativo aggiornato",
        "field_id" => "Field id: (keepOsUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 2,
        "os" => ["Windows"],
        "label" => "Mantenere aggiornate le app gestite tramite l'azione installa applicazione",
        "field_id" => "Field id: (keepAppUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 3,
        "os" => ["Apple"],
        "label" => "Mantenere il sistema operativo aggiornato",
        "field_id" => "Field id: (keepOsUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Mantenere aggiornate le app gestite tramite l'azione installa applicazione",
        "field_id" => "Field id: (keepAppUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
    ];

    if (in_array($osType, $this->availableOs)) {
      /*$filteredSchema = array_filter($schema, function ($item) use ($osType) {
         return (in_array(strtolower($osType), $item['os']) || in_array('mixed', $item['os']));
       });
       LOG::info(json_encode($filteredSchema));*/
      // Reindex the array to reset the keys
      return $schema; //array_values($filteredSchema);
    }else{
      return [];
      // throw \Exception::class
    }
  }

  /*public function getSchemaa(Device &$device = NULL)
  {

    $addappmodalignore = json_encode($GLOBALS['CHIMPA_APP_BUNDLE_IDS']);

    $vendorConfigFields = array(
      'key' => array('type' => 'text', 'width' => 0, 'required' => true),
      'value' => array('type' => 'text', 'width' => 200, 'required' => true),
    );
    $typeOptions = [self::ADULT_FILTER => 'ADULT_FILTER', self::SITES_WEB_FILTER => 'SITES_WEB_FILTER_WHITELIST'];
    $typeOptions[self::THIRD_PARTY_APP_FILTER] = 'THIRD_PARTY_APP_FILTER';

    $schema = array(
      'wiki' => array('id' => 'WIKIC-A-230', 'type' => 'wiki'),
      'type' => array('noTooltip' => true, 'osType' => OsType::IOS, 'paramtype' => 'string', 'type' => 'select', 'value' => ((!is_null($this->type)) ? $this->type : self::ADULT_FILTER), 'options' => $typeOptions),
      'permittedURLs' => array('arraymodalbuttonclass' => 'addPermittedUrls', 'arraymodal' => true, 'arrayfields' => ['URL'], 'arrayignorekey' => true, 'type' => 'hidden', 'paramtype' => 'array', 'value' => ((is_null($this->permittedURLs)) ? array() : $this->permittedURLs), 'showparentid' => 'type', 'showparentvalue' => [self::ADULT_FILTER, self::SITES_WEB_FILTER], 'isHideable' => true, 'isHidden' => ($this->type === self::THIRD_PARTY_APP_FILTER)),
      'blacklistedURLs' => array('arraymodalbuttonclass' => 'addPermittedUrls', 'arraymodal' => true, 'arrayfields' => ['URL'], 'arrayignorekey' => true, 'type' => 'hidden', 'paramtype' => 'array', 'value' => ((is_null($this->blacklistedURLs)) ? array() : $this->blacklistedURLs), 'showparentid' => 'type', 'showparentvalue' => [self::ADULT_FILTER, self::SITES_WEB_FILTER], 'isHideable' => true, 'isHidden' => ($this->type === self::THIRD_PARTY_APP_FILTER)),
      //'blacklistedURLs' => array('arraymodal' => true, 'arrayfields' => ['URL'], 'arrayignorekey' => true, 'type' => 'hidden', 'paramtype' => 'array', 'value' => ((is_null($this->blacklistedURLs)) ? array() : $this->blacklistedURLs), 'showparentid' => 'type', 'showparentvalue' => [self::ADULT_FILTER, self::SITES_WEB_FILTER], 'isHideable' => true, 'isHidden' => ($this->type === self::THIRD_PARTY_APP_FILTER)),
      //'whitelistedBookmarks' => array('osType' => OsType::IOS,'arraymodal' => true, 'arrayfields' => ['URL'], 'arrayignorekey' => true, 'type' => 'hidden', 'paramtype' => 'array', 'value' => ((is_null($this->whitelistedBookmarks)) ? array() : $this->whitelistedBookmarks)),
      'userDefinedName' => array('paramtype' => 'string', 'osType' => OsType::IOS, 'type' => 'text', 'required' => true, 'placeholder' => '', 'value' => $this->userDefinedName, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'pluginBundleID' => array('addappmodalcloseonbuttonconfirm' => true, 'osType' => OsType::IOS, 'style' => 'width:250px!Important', 'addappmodaladdtype' => 'single', 'required' => true, 'addappmodalignore' => $addappmodalignore, 'addappmodal' => true, 'addappmodalos' => OsType::IOS, 'addappmodaltype' => 'app', 'addappmodalinput' => 'input[name="pluginBundleID"]', 'paramtype' => 'string', 'osType' => OsType::IOS, 'type' => 'text', 'readonly' => true, 'value' => $this->pluginBundleID, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'serverAddress' => array('paramtype' => 'string', 'type' => 'text', 'osType' => OsType::IOS, 'placeholder' => '', 'value' => $this->serverAddress, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'organization' => array('paramtype' => 'string', 'type' => 'text', 'osType' => OsType::IOS, 'placeholder' => '', 'value' => $this->organization, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'username' => array('noTooltip' => true, 'paramtype' => 'string', 'osType' => OsType::IOS, 'type' => 'text', 'placeholder' => '', 'value' => $this->username, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'password' => array('noTooltip' => true, 'paramtype' => 'string', 'osType' => OsType::IOS, 'type' => 'password', 'placeholder' => '', 'value' => $this->password, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'PayloadCertificateUUID' => array('nolangkey' => true, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER), 'paramtype' => 'string', 'type' => 'select', 'value' => $this->PayloadCertificateUUID, 'getsmimecerts' => true, 'options' => ['' => '---'], 'certificatestypes' => [PayloadType::CERTIFICATE_PKCS12]),
      'filterBrowsers' => array('noTooltip' => true, 'paramtype' => 'bool', 'osType' => OsType::IOS, 'type' => 'checkbox', 'value' => $this->filterBrowsers, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'filterSockets' => array('noTooltip' => true, 'paramtype' => 'bool', 'osType' => OsType::IOS, 'type' => 'checkbox', 'value' => $this->filterSockets, 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER)),
      'vendorConfig' => array('osType' => OsType::IOS, 'type' => 'table', 'fields' => $vendorConfigFields, 'tablewidth' => '400px', 'value' => ((!is_null($this->vendorConfig)) ? $this->vendorConfig : array()), 'showparentid' => 'type', 'showparentvalue' => self::THIRD_PARTY_APP_FILTER, 'isHideable' => true, 'isHidden' => ($this->type !== self::THIRD_PARTY_APP_FILTER))
    );

    $schema = parent::checkSchemaLicense($schema);
    $schema = $this->checkParamsLevel($schema);

    return $schema;
  }*/

}
