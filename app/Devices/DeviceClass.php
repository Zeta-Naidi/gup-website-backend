<?php

namespace App\Devices;

/**
 * Class DeviceClass
 *
 * @property-read int $id
 * @property-read int|null $parentDeviceId
 * @property-read string|null $deviceName
 * @property-read string|null $modelName
 * @property-read int|null $enrollmentType
 * @property-read string|null $macAddress
 * @property-read string|null $meid
 * @property-read string $osType
 * @property-read string $osEdition
 * @property-read string $osVersion
 * @property-read string|null $udid
 * @property-read string|null $vendorId
 * @property-read string|null $osArchitecture
 * @property-read string|null $abbinationCode
 * @property-read int|null $mdmDeviceId
 * @property-read string $manufacturer
 * @property-read string|null $serialNumber
 * @property-read string|null $imei
 * @property-read bool $isDeleted
 * @property-read string|null $phoneNumber
 * @property-read bool $isOnline
 * @property-read string|null $brand
 * @property-read array|null $networkIdentity
 * @property-read array|null $configuration
 * @property-read array|null $deviceIdentity
 * @property-read string|null $createdAt
 */
abstract class DeviceClass
{
  /** @var int $deviceId */
  private int $id;

  /** @var int|null $parentDeviceId */
  private ?int $parentDeviceId;

  /** @var string|null $deviceName */
  private ?string $deviceName;

  /** @var string|null $modelName */
  private ?string $modelName;

  /** @var int|null $enrollmentType */
  private ?int $enrollmentType;

  /** @var string|null $macAddress */
  private ?string $macAddress;

  /** @var string|null $meid */
  private ?string $meid;

  /** @var string $osType */
  private string $osType;

  /** @var string $osEdition */
  private string $osEdition;

  /** @var string $osVersion */
  private string $osVersion;

  /** @var string|null $udid */
  private ?string $udid;

  /** @var string|null $vendorId */
  private ?string $vendorId;

  /** @var string|null $osArchitecture */
  private ?string $osArchitecture;

  /** @var string|null $abbinationCode */
  private ?string $abbinationCode;

  /** @var int|null $mdmDeviceId */
  private ?int $mdmDeviceId;

  /** @var string $manufacturer */
  private string $manufacturer;

  /** @var string|null $serialNumber */
  private ?string $serialNumber;

  /** @var string|null $imei */
  private ?string $imei;

  /** @var bool $isDeleted */
  private bool $isDeleted;

  /** @var string|null $phoneNumber */
  private ?string $phoneNumber;

  /** @var bool $isOnline */
  private bool $isOnline;

  /** @var string|null $brand */
  private ?string $brand;

  /** @var array|null $networkIdentity */
  private ?array $networkIdentity;

  /** @var array|null $configuration */
  private ?array $configuration;

  /** @var array|null $deviceIdentity */
  private ?array $deviceIdentity;

  /** @var string|null $createdAt */
  private ?string $createdAt;


  public function __construct(
    int $id,
    ?int $parentDeviceId,
    ?string $deviceName,
    ?string $modelName,
    ?int $enrollmentType,
    ?string $macAddress,
    ?string $meid,
    string $osType,
    string $osEdition,
    string $osVersion,
    ?string $udid,
    ?string $vendorId,
    ?string $osArchitecture,
    ?string $abbinationCode,
    ?int $mdmDeviceId,
    string $manufacturer,
    ?string $serialNumber,
    ?string $imei,
    bool $isDeleted,
    ?string $phoneNumber,
    bool $isOnline,
    ?string $brand,
    ?array $networkIdentity,
    ?array $configuration,
    ?array $deviceIdentity,
    ?string $createdAt,
  ) {
    $this->id = $id;
    $this->parentDeviceId = $parentDeviceId;
    $this->deviceName = $deviceName;
    $this->modelName = $modelName;
    $this->enrollmentType = $enrollmentType;
    $this->macAddress = $macAddress;
    $this->meid = $meid;
    $this->osType = $osType;
    $this->osEdition = $osEdition;
    $this->osVersion = $osVersion;
    $this->mdmDeviceId = $mdmDeviceId;
    $this->manufacturer = $manufacturer;
    $this->isDeleted = $isDeleted;
    $this->isOnline = $isOnline;
    $this->networkIdentity = $networkIdentity;
    $this->configuration = $configuration;
    $this->deviceIdentity = $deviceIdentity;
    $this->osArchitecture = $osArchitecture;
    $this->abbinationCode = $abbinationCode;
    $this->udid = $udid;
    $this->vendorId = $vendorId;
    $this->serialNumber = $serialNumber;
    $this->imei = $imei;
    $this->phoneNumber = $phoneNumber;
    $this->brand = $brand;
    $this->createdAt = $createdAt;
  }

  // TODO: methods to check device attributes (ktc, sdk, hasOpenSdk, ecc...)

  /*
   public function isCVTE(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->isOem === true) {
            $this->_initializeFullInfo();
            if (
                (stripos($this->manufacturer, "hisilicon") !== false && stripos($this->brand, "hidpt") !== false) ||
                (stripos($this->manufacturer, "rockchip") !== false && stripos($this->brand, "sahara_meeting") !== false) ||
                (stripos($this->manufacturer, "mstar") !== false && stripos($this->brand, "mstar") !== false) ||
                (stripos($this->manufacturer, "droidlogic") !== false) ||
                $this->hasOpenSDK()
            )
                $return = true;
        }

        return $return;
    }

    public function isKTC(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->isOem === true) {
            $this->_initializeFullInfo();
            if (
                (stripos($this->manufacturer, "ktc") !== false && stripos($this->brand, "ktc") !== false) ||
                (stripos($this->manufacturer, "ifp") !== false && stripos($this->brand, "ifp") !== false) ||
                (stripos($this->manufacturer, "kindermann") !== false && stripos($this->brand, "kindermann") !== false) ||
                (stripos($this->manufacturer, "skg") !== false && stripos($this->brand, "skg") !== false) ||
                $this->hasKtcSDK()
            )
                $return = true;
        }

        return $return;
    }

    public function isTouchViewAgentFlavor(): bool
    {
        return $this->agentFlavor == "touchview";
    }

    public function isSinocan(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->isOem === true) {
            $this->_initializeFullInfo();
            if (
                stripos($this->brand, "ist") !== false
            )
                $return = true;
        }

        return $return;
    }

    public function isIFP(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID)
            $return = $this->isSinocan() || $this->isCVTE() || $this->isKTC(); //todo aggiungere ulteriori modelli o produttori

        return $return;
    }

    public function hasOpenSDK(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->androidAgentVersion >= 3800 && $this->isOem === true) {
            $this->_initializeFullInfo();
            if (!is_null($this->customManufacturerInfo) && isset($this->customManufacturerInfo["MainCodeVersion"]) && strlen($this->customManufacturerInfo["MainCodeVersion"]) > 0)
                $return = true;
        }

        return $return;
    }

    public function hasKtcSDK(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->androidAgentVersion >= 3900 && $this->isOem === true) {
            $this->_initializeFullInfo();
            if (!is_null($this->customManufacturerInfo) && isset($this->customManufacturerInfo["systemVersion"]))
                $return = true;
        }

        return $return;
    }

    public function hasControlService(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->androidAgentVersion >= 3800) {
            $this->_initializeFullInfo();
            if (!is_null($this->androidControlServiceVersion) && $this->androidControlServiceVersion > 0)
                $return = true;
        }

        return $return;
    }

    public function isHeadsetVisor(): bool
    {
        $return = false;
        if ($this->exists && $this->osType == OsType::ANDROID && $this->androidAgentVersion >= 3800) {
            $this->_initializeFullInfo();
            if ($this->osType == OsType::ANDROID && (
                    (stripos($this->manufacturer, "epson") !== false && stripos($this->brand, "epson") !== false) ||
                    (stripos($this->manufacturer, "epson") !== false && stripos($this->brand, "moverio") !== false) ||
                    (stripos($this->manufacturer, "lenovo") !== false && stripos($this->productName, "VR-") !== false)
                ))
                $return = true;
        }

        return $return;
    }
  */


  // TODO: getPushDeviceToken, getMdmPushDeviceTokens
  //

/*  public static function getPushDeviceToken(int $deviceId, string $app = ChimpaAppUri::CHIMPA_LEARN)
  {

    $query = 'SELECT tblBindings.pushDeviceToken, tblDevices.osType FROM tblBindings RIGHT JOIN tblDevices ON tblBindings.deviceId=tblDevices.deviceId WHERE tblBindings.deviceId=' . $deviceId . ' AND tblBindings.isActive=1 AND tblBindings.app="' . $app . '" AND tblDevices.isDeleted=0 LIMIT 1;';

    $database = new Database();
    $result = $database->fetchQueryRow($query);
    unset($database);


    if (($result[0][1] == OsType::IOS || $result[0][1] == OsType::ANDROID) && is_string($result[0][0])) {
      return array(array('pushDeviceToken' => $result[0][0], 'osType' => $result[0][1]));

    } else {
      return NULL;
    }

  }

  public static function pushUpdateAndroidMdmInfo(array $deviceIds): bool
  {

    $pushDeviceTokens = self::getMdmPushDeviceTokens($deviceIds);

    PushNotifications::sendMdmNotification($pushDeviceTokens, "GET_PAYLOADS;UPDATE_DEVICE_INFO"); //UPDATE_DEVICE_INFO;UPDATE_CERT

    return true;

  }
*/


  // TODO: sendPushNotification()
  //

  /*
   *
   * public static function sendMdmNotification($devices, $contexts = null, bool $syncronuse = false, bool $splittedBackground = false,array $options = [],string $notificationType = 'alert',int $notificationPriority = 10, string $topicSuffix = '')
    {
        //in base all'osType prendo i deviceToken e lancio un thread della funzione dedicata
        $result = true; //this value is going to be changed only for iOs/macOs agent push notifications
        $silentMode = $options['silent'] ?? true;

        $deviceTokensGoogle = array();
        $deviceTokensApple = array();
        $deviceTokensAppleAgent = array();
        $deviceTokensWindows = array();
        $deviceTokensWindowsAgent = array();

        $deviceTokensGoogleToSend = array();
        $deviceTokensAppleToSend=array();
        $deviceTokensAppleAgentToSend=array();
        $deviceTokensWindowsToSend=array();
        $deviceTokensWindowsAgentToSend=array();

        $tmpContexts = !is_array($contexts) ? explode(';',$contexts) : $contexts;
        $isAppleAgentPush = (in_array('agentGetPayloads',$tmpContexts,false) || in_array('agentUpdateInfo',$tmpContexts,false));

        foreach ($devices as $device) {
                if($isAppleAgentPush && $device['osType'] !== OsType::IOS){
                    continue; //skip if we have an apple agent push notification and device is not iOS
                }

                if ($device['osType'] == OsType::ANDROID) {
                    if (!is_null($device['mdmPushDeviceToken'])) {
                        if(isset($device['isAndroidOem']) && $device['isAndroidOem']===true)
                        {
                            if($device['mdmDeviceId']>0 && stripos($device['mdmPushDeviceToken'],"OEM-")===0)
                                self::addCustomPushNotification((int)$device['mdmDeviceId']);
                        } elseif (!in_array($device['mdmPushDeviceToken'], $deviceTokensGoogle)) {
                            $deviceTokensGoogle[] = $device['mdmPushDeviceToken'];
                        }
                    }
                }
                elseif ($device['osType'] == OsType::IOS)
                {
                    if (!$isAppleAgentPush && !empty($device['mdmPushDeviceToken'])) {
                        if(is_null($device['pushMagic']) && isset($device['mdmPushMagic']))
                            $device['pushMagic']=$device['mdmPushMagic'];
                        if (!is_null($device['pushMagic']) && !in_array($device['mdmPushDeviceToken'], $deviceTokensApple))
                            $deviceTokensApple[] = array("mdmPushDeviceToken" => $device['mdmPushDeviceToken'], "pushContent" => json_encode(array('mdm' => $device['pushMagic'])));
                    }elseif($isAppleAgentPush && !empty($device['appleAgentPush'])){
                        $tkn = $topicSuffix === '.location-query' ? $device['locationToken'] : $device['appleAgentPush']; //we need a special token for location requests
                        $deviceTokensAppleAgent[] = ['mdmPushDeviceToken' => $tkn, 'pushContent' => json_encode(['aps' => $options]),'notificationType' => $notificationType,'notificationPriority' => $notificationPriority, 'topicSuffix' => $topicSuffix];
                    }
                }
                elseif ($device['osType'] == OsType::WINDOWS)
                {
                    //todo: differenziare agent e csp in base al context (vanno inseriti i context per actions agent)
                    if (!is_null($device['mdmPushDeviceToken'])) { //csp push notification
                        if (!in_array($device['mdmPushDeviceToken'], $deviceTokensWindows)) {
                            $deviceTokensWindows[] = $device['mdmPushDeviceToken'];
                        }
                    }

                    if (!is_null($device['pushMagic'])) { //agent push notification
                        if (!in_array($device['pushMagic'], $deviceTokensWindowsAgent)) {
                            $deviceTokensWindowsAgent[] = $device['pushMagic'];
                        }
                    }
                }

        }

        $loop=0;

        if($splittedBackground){
            $last=false;
            $finishPushGoogle=false;
            $finishPushApple=false;
            $finishPushAppleAgent=false;
            $finishPushWindows=false;
            $finishPushWindowsAgent = false;
        }
        else
        {
            $last=true;
        }


        do
        {
            $loop++;

            //suddivido le push per cicli e calcolo last NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE/2
            if($splittedBackground)
            {

                $fineGoogle=NULL;
                $fineApple=NULL;
                $fineAppleAgent = null;
                $fineWindows=NULL;
                $fineWindowsAgent = null;


                if($loop==1)
                {
                    $inizio=0;
                }
                else
                {
                    $inizio=$loop*NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE/2;
                }

                if(!$finishPushGoogle)
                {
                    if (empty($deviceTokensGoogle)){
                        $finishPushGoogle = true;
                    }
                    elseif(count($deviceTokensGoogle) <= ($loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2)) {
                        $fineGoogle = count($deviceTokensGoogle);
                        $finishPushGoogle = true;
                    } else {
                        $fineGoogle = $loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2;
                    }
                }

                if(!$finishPushApple) {
                    if (empty($deviceTokensApple)){
                        $finishPushApple = true;
                    }
                    elseif (count($deviceTokensApple) <= ($loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2)) {
                        $fineApple = count($deviceTokensApple);
                        $finishPushApple = true;
                    } else {
                        $fineApple = $loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2;
                    }
                }

                if(!$finishPushAppleAgent) {
                    if (empty($deviceTokensAppleAgent)){
                        $finishPushAppleAgent = true;
                    }
                    elseif (count($deviceTokensAppleAgent) <= ($loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2)) {
                        $fineAppleAgent = count($deviceTokensAppleAgent);
                        $finishPushAppleAgent = true;
                    } else {
                        $fineAppleAgent = $loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2;
                    }
                }

                if(!$finishPushWindows)
                {
                    if (empty($deviceTokensWindows)){
                        $finishPushWindows = true;
                    }
                    elseif(count($deviceTokensWindows) <= ($loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2)) {
                        $fineWindows = count($deviceTokensWindows);
                        $finishPushWindows = true;
                    } else {
                        $fineWindows = $loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2;
                    }
                }

                if(!$finishPushWindowsAgent)
                {
                    if (empty($deviceTokensWindowsAgent)){
                        $finishPushWindowsAgent = true;
                    }
                    elseif(count($deviceTokensWindowsAgent) <= ($loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2)) {
                        $fineWindowsAgent = count($deviceTokensWindowsAgent);
                        $finishPushWindowsAgent = true;
                    } else {
                        $fineWindowsAgent = $loop * NUMBER_OF_PUSH_IN_SINGLE_SEQUENCE / 2;
                    }
                }

                if(!is_null($fineGoogle)) $deviceTokensGoogleToSend = array_slice($deviceTokensGoogle, $inizio, $fineGoogle);
                if(!is_null($fineApple)) $deviceTokensAppleToSend = array_slice($deviceTokensApple, $inizio, $fineApple);
                if(!is_null($fineAppleAgent)) $deviceTokensAppleAgentToSend = array_slice($deviceTokensAppleAgent, $inizio, $fineAppleAgent);
                if(!is_null($fineWindows)) $deviceTokensWindowsToSend = array_slice($deviceTokensWindows, $inizio, $fineWindows);
                if(!is_null($fineWindowsAgent)) $deviceTokensWindowsAgentToSend = array_slice($deviceTokensWindowsAgent, $inizio, $fineWindowsAgent);

                if($finishPushGoogle && $finishPushApple && $finishPushWindows && $finishPushWindowsAgent) $last=true;

            }
            else
            {
                $deviceTokensGoogleToSend=$deviceTokensGoogle;
                $deviceTokensAppleToSend=$deviceTokensApple;
                $deviceTokensAppleAgentToSend=$deviceTokensAppleAgent;
                $deviceTokensWindowsToSend=$deviceTokensWindows;
                $deviceTokensWindowsAgentToSend=$deviceTokensWindowsAgent;
            }


            if ($deviceTokensGoogleToSend != NULL && !empty($deviceTokensGoogleToSend))
            {

                //TODO ??? aggiornare push attempts e lastPushTime in tblProfileStatus agli android

                if ($syncronuse)
                {
                    GoogleCloudMessaging::send(OsType::ANDROID, NULL, MDM_PUSH, $silentMode, $deviceTokensGoogleToSend, "", $contexts, "", false);
                } else {
                    if (isset($GLOBALS["TQ1"]))
                    {
                        $TQ1 = &$GLOBALS["TQ1"];
                    }
                    if (!isset($TQ1) || is_null($TQ1))
                    {
                        $GLOBALS["TQ1"] = new ThreadQueue("GoogleCloudMessaging::send");
                        $TQ1 = &$GLOBALS["TQ1"];
                    }
                    $TQ1->add(OsType::ANDROID, NULL, MDM_PUSH, $silentMode, $deviceTokensGoogleToSend, "", $contexts);
                    //$TQ1->tick();
                }
            }

            if ($deviceTokensAppleToSend != NULL && !empty($deviceTokensAppleToSend))
            {
                if ($syncronuse)
                {
                    //Log::logger("aaaaaaa");
                    foreach ($deviceTokensAppleToSend as $index2=>$deviceTokenAppleToSend) {
                        //if($index2>0) usleep(60000);
                        PushNotificationsApple::addToQueueStatic($deviceTokenAppleToSend["mdmPushDeviceToken"], $deviceTokenAppleToSend["pushContent"]);
                    }

                }
                else
                {
                    if (isset($GLOBALS["TQ2"])) {

                        $TQ2 = &$GLOBALS["TQ2"];

                    }
                    if (!isset($TQ2) || is_null($TQ2)) {

                        $GLOBALS["TQ2"] = new ThreadQueue("PushNotificationsApple::setQueueAndSendTask");
                        $TQ2 = &$GLOBALS["TQ2"];
                    }

                    $TQ2->add($deviceTokensAppleToSend);
                    //$TQ2->tick();
                }
            }

            if (!empty($deviceTokensAppleAgentToSend))
            {

                //if ($syncronuse)
                //{
                    foreach($deviceTokensAppleAgentToSend as $deviceTokenAppleAgentToSend) {
                        $result = PushNotificationsApple::addToQueueStatic($deviceTokenAppleAgentToSend["mdmPushDeviceToken"], $deviceTokenAppleAgentToSend["pushContent"],true,$deviceTokenAppleAgentToSend["notificationType"],$deviceTokenAppleAgentToSend["notificationPriority"],$deviceTokenAppleAgentToSend["topicSuffix"]);
                    }
                //} else {
               //     ServicePid::addThreadInStandbyQueue('appleAgentPush', [],[ 'tokens' => $deviceTokensAppleAgentToSend], 0);
                //}
            }

            if ($deviceTokensWindowsToSend != NULL && !empty($deviceTokensWindowsToSend))
            {
                //$syncronuse = true;
                if ($syncronuse)
                {
                    WindowsNotificationClass::sendPushNotification($deviceTokensWindowsToSend);
                } else {
                    ServicePid::addThreadInStandbyQueue('windowsPush', [],['tokens'=>$deviceTokensWindowsToSend], 0);
                }
            }

            if ($deviceTokensWindowsAgentToSend != NULL && !empty($deviceTokensWindowsAgentToSend))
            {
                //$syncronuse = true;
                //if ($syncronuse)
                //{
                    WindowsNotificationClass::sendPushNotification($deviceTokensWindowsAgentToSend,true);
                //} else {
                //    ServicePid::addThreadInStandbyQueue('windowsAgentPush', [],['tokens'=>$deviceTokensWindowsAgentToSend]);
                //}
            }

            if($splittedBackground) {
                waitForChild(false);
                if(!$last) sleep(WAIT_BEFORE_NEXT_SEQUENCE + random_int(-WAIT_RANDOM_MARGIN,WAIT_RANDOM_MARGIN));
            }

        }while(!$last);

        return $result;

    }
   * */
}
