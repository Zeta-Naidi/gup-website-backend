<?php

namespace App\Devices;

class WindowsMessage implements IWindowsMessage
{
  use ApiRequester;

  private $sessionID,$msgID,$commandType,$clientURI,
    $commandURI,$cmdID,$status,$deviceId,$params,$lastPush,$priority,
    $parentId,$isParent,$actionType,$profileID,$mdmGroupId;
  private mixed $actionId;
  private int $id;

  private static string $table = 'tblWindowsCheckins';
  public static string $agentVersion = '02.06.11';

  public static string $agentx64Id = '{774203A4-66F7-48B0-8EEA-F0BFC8500C2A}';
  public static string $agentx86Id = '{A75BD7D5-4FC6-48AF-A023-AB1C65CA4B0D}';
  public static string $agentx64Url = 'https://box.chimpa.eu/resources/win/02.06.11/Win.Agent.Setup.x64.msi';
  public static string $agentx86Url = 'https://box.chimpa.eu/resources/win/02.06.11/Win.Agent.Setup.msi';
  public static string $agentx64Hash = '4ae64d7357459f7c3ee81c5177ba600e4351ca688e436c157c7be408e21bb5d4';
  public static string $agentx86Hash = 'd28dc5867924073cb602508ed86fbef382dd4768c2f3f3151881ae5073dcc8f3';
  public static string $agentArmId = '{2D00166E-A14A-4F24-B94F-3D5E9ED21D67}';
  public static string $agentArmUrl = 'https://box.chimpa.eu/resources/win/02.00.02/Win.Agent.Setup.Arm.msi';
  public static string $agentArmHash = '26098538f00c52a7cc8f05bc6f48776088e2693283b2da873b72314872e275d0';


  /**
   * @return int
   */
  public function getId():int
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId(int $id): void
  {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getSessionID(): mixed
  {
    return $this->sessionID;
  }

  /**
   * @param mixed $sessionID
   */
  public function setSessionID(mixed $sessionID): void
  {
    $this->sessionID = $sessionID;
  }

  /**
   * @return mixed
   */
  public function getMsgID(): mixed
  {
    return $this->msgID;
  }

  /**
   * @param mixed $msgID
   */
  public function setMsgID(mixed $msgID): void
  {
    $this->msgID = $msgID;
  }

  /**
   * @return mixed
   */
  public function getCommandType(): mixed
  {
    return $this->commandType;
  }

  /**
   * @param mixed $commandType
   */
  public function setCommandType(mixed $commandType): void
  {
    $this->commandType = $commandType;
  }

  /**
   * @return mixed
   */
  public function getClientURI(): mixed
  {
    return $this->clientURI;
  }

  /**
   * @param mixed $clientURI
   */
  public function setClientURI(mixed $clientURI): void
  {
    $this->clientURI = $clientURI;
  }

  /**
   * @return mixed
   */
  public function getCommandURI(): mixed
  {
    return $this->commandURI;
  }

  /**
   * @param mixed $commandURI
   */
  public function setCommandURI(mixed $commandURI): void
  {
    $this->commandURI = $commandURI;
  }

  /**
   * @return mixed
   */
  public function getStatus(): mixed
  {
    return $this->status;
  }

  /**
   * @param mixed $status
   */
  public function setStatus(mixed $status): void
  {
    $this->status = $status;
  }

  /**
   * @return mixed
   */
  public function getCmdID(): mixed
  {
    return $this->cmdID;
  }

  /**
   * @param mixed $cmdID
   */
  public function setCmdID(mixed $cmdID): void
  {
    $this->cmdID = $cmdID;
  }

  /**
   * @return mixed
   */
  public function getDeviceId(): mixed
  {
    return $this->deviceId;
  }

  /**
   * @param mixed $deviceId
   */
  public function setDeviceId(mixed $deviceId): void
  {
    $this->deviceId = $deviceId;
  }

  /**
   * @return int
   */
  public function getProfileId(): int
  {
    return $this->profileID;
  }

  /**
   * @param mixed $profileID
   */
  public function setProfileId(mixed $profileID): void
  {
    $this->profileID = $profileID;
  }

  /**
   * @return string
   */
  public static function getTable(): string
  {
    return self::$table;
  }

  /**
   * @return mixed
   */
  public function getMdmGroupId(): mixed
  {
    return $this->mdmGroupId;
  }

  /**
   * @param mixed $mdmGroupId
   */
  public function setMdmGroupId(mixed $mdmGroupId): void
  {
    $this->mdmGroupId = $mdmGroupId;
  }


  /**
   * @return mixed
   */
  public function getParams(bool $asArray = false): mixed
  {
    if($asArray && self::isjson($this->params)){
      return json_decode($this->params,true);
    }
    return $this->params;
  }

  private static function isjson($string): bool
  {
    return is_string($string) && is_array(json_decode($string, true)) && json_last_error() == JSON_ERROR_NONE;
  }

  /**
   * @param mixed $params
   */
  public function setParams(mixed $params): void
  {
    $this->params = $params;
  }

  /**
   * @return mixed
   */
  public function getLastPush(): mixed
  {
    return $this->lastPush;
  }

  /**
   * @param mixed $lastPush
   */
  public function setLastPush(mixed $lastPush): void
  {
    $this->lastPush = $lastPush;
  }

  /**
   * @return int
   */
  public function getPriority():int
  {
    return $this->priority ? (int)$this->priority : 0;
  }

  /**
   * @param mixed $priority
   */
  public function setPriority(mixed $priority): void
  {
    $this->priority = $priority;
  }

  /**
   * @return mixed
   */
  public function getActionId(): mixed
  {
    return $this->actionId;
  }

  /**
   * @param mixed $actionId
   */
  public function setActionId(mixed $actionId): void
  {
    $this->actionId = $actionId ? (int)$actionId : null;
  }

  /**
   * @return mixed
   */
  public function getParentId(): mixed
  {
    return $this->parentId;
  }

  /**
   * @return int
   */
  public function getIsParent(): int
  {
    return $this->isParent ? 1 : 0;
  }

  /**
   * @param mixed $isParent
   */
  public function setIsParent(mixed $isParent): void
  {
    $this->isParent = $isParent;
  }



  /**
   * @param mixed $parentId
   */
  public function setParentId(mixed $parentId): void
  {
    $this->parentId = $parentId;
  }

  /**
   * @return mixed
   */
  public function getActionType(): mixed
  {
    return $this->actionType;
  }

  /**
   * @param string $dataSource
   * @param array $params
   */
  public function __construct(string $dataSource, array $params){
    parent::__construct($dataSource);
    foreach($params as $key => $value){
      $this->{$key} = $value;
    }
  }

  /**
   * Gets one or more messages from the db
   * @param int $sessionId
   * @param string $clientURI
   * @param int|null $cmdID
   * @param int|null $msgID
   * @return array|WindowsMessage
   */
  public function getMessagesByClientSessionID(int $sessionId,string $clientURI,int $cmdID = null,int $msgID = null): WindowsMessage|array
  {
    $whereCondition = "WHERE sessionID=$sessionId AND clientURI='$clientURI'";
    $limit = '';
    if($cmdID !== null && $msgID !== null){
      $whereCondition.=' AND cmdID = '.$cmdID.' AND msgID = '.$msgID;
      $limit = 'LIMIT 1';
    }
    return $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => '*',
      'whereCondition' => $whereCondition,
      'orderBy' => 'ORDER BY id DESC',
      'limit' => $limit,
      'asObject' => true,
      'stdClass' => get_class($this),
      'constructorArgs' =>['DbCrud',[]],
    ]);
  }

  /**
   * Stores message info in database
   * @return int
   */
  public function store():int{
    //check for other pending commands with same parameters
    $deviceCondition = '';

    if($this->getDeviceId()){
      $deviceCondition.= 't.deviceId = '.$this->getDeviceId();

      $whereCondition = 'WHERE t.commandType = \''.$this->getCommandType()
        .'\' AND t.commandURI = \''.$this->getCommandURI()
        .'\' AND t.status = 0 AND ('.$deviceCondition.')';

      $whereCondition.= ' AND (t.params= \''.addslashes(json_encode($this->getParams())).'\' OR t.params IS NULL)';

      $pendingMessages = $this->getDataSource()->select([
        'databaseType' => \DatabaseType::CLIENT,
        'table' => self::getTable().' AS t',
        'fields' => 't.id, t.actionId, a.actionType,t.deviceId',
        'whereCondition' => $whereCondition,
        'joinCondition' => 'LEFT JOIN tblActions as a ON a.actionId = t.actionId'
      ]);

      if(!empty($pendingMessages)){
        foreach($pendingMessages as $message){
          $this->getDataSource()->delete([
            'databaseType' => \DatabaseType::CLIENT,
            'table' => self::getTable(),
            'whereCondition' => 'WHERE id = '.$message['id']
          ]);
          if($message['actionId']){
            \Action::setDeliveryStatus($message['deviceId'],$message['actionType'],\DeliveryStatus::CANCELLED,null,null,$message['actionId']);
          }
        }
      }
    }

    //clear older profile bindings
    if($this->getProfileId() || $this->getMdmGroupId()) {
      $deviceCondition = 'WHERE ';
      if ($this->getProfileId()) {
        $deviceCondition .= 'profileId = ' . $this->getProfileId();
      }
      if ($this->getMdmGroupId()) {
        $condition = $this->getProfileId() ? ' AND' : '';
        $deviceCondition .= $condition . ' mdmGroupId = ' . $this->getMdmGroupId();
      }
      if($this->getDeviceId()){
        $deviceCondition .= ' AND deviceId = ' . $this->getDeviceId();
      }

      $this->getDataSource()->delete([
        'databaseType' => \DatabaseType::CLIENT,
        'table' => self::getTable(),
        'whereCondition' => $deviceCondition,
      ]);
    }


    $id = $this->getDataSource()->store([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'values' => [
        'deviceId' => $this->getDeviceId(),
        'actionId' => $this->getActionId(),
        'sessionID' => $this->getSessionID(),
        'msgID' => $this->getMsgID() ?? 1,
        'cmdID' => $this->getCmdID() ?? 1,
        'commandType' => $this->getCommandType(),
        'commandURI' => $this->getCommandURI(),
        'clientURI' => $this->getClientURI(),
        'status' => $this->getStatus() ?? \WindowsMessageStatus::SENT,
        'mdmGroupId' => $this->getMdmGroupId(),
        'profileID' => $this->getProfileID(),
        'params' => $this->getParams(),
        'lastPush' => $this->getLastPush() ? time() : null,
        'priority' => $this->getPriority(),
        'parentId' => $this->getParentId(),
        'isParent' => $this->getIsParent(),
        'actionType' => $this->getActionType(),
      ],
      'duplicateAction' => 'ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id),status = values(status)',
    ]);

    if($id){
      $this->setId($id);
    }
    return $id ?? 0;
  }


  /**
   * When the device sends responses to server this function updates the message status
   * @param int $status
   * @return bool
   */
  public function updateMsgStatus(int $status):bool{
    return (bool)$this->getDataSource()->update([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'values' => [
        'status' => $status,
      ],
      'whereCondition' => 'WHERE id = '.$this->getId()
    ]);
  }

  /**
   * Once the message is sent, this must be called to set updated msgID, cmdID, sessionID
   * @param int $dbId
   * @param int $cmdId
   * @param int $msgId
   * @param int $sessionID
   * @return bool
   */
  public function updateDbMessageIds(int $dbId,int $cmdId, int $msgId,int $sessionID):bool{
    return (bool)$this->getDataSource()->update([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'values' => [
        'msgID' => $msgId,
        'cmdID' => $cmdId,
        'sessionID' => $sessionID
      ],
      'whereCondition' => 'WHERE id = '.$dbId
    ]);
  }

  /**
   * Retrieve messages not sent from DB
   * @param int $deviceId
   * @return array
   */
  public function getMessagesToSend(int $deviceId):array{
    return $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => '*',
      'whereCondition' => "WHERE (deviceId = $deviceId OR (deviceId IS NULL AND mdmGroupId IS NOT NULL)) AND status = 0 AND lastPush IS NULL AND isParent = 0 ORDER BY priority DESC",
      'asObject' => true,
      'stdClass' => get_class($this),
      'constructorArgs' =>['DbCrud',[]],
    ]);
  }

  /**
   * @param int $deviceId
   * @param array $oldMergedProfile
   * Retrieves install profiles not sent from DB
   * @return array|false
   */
  public function getInstallProfilesToSend(int $deviceId,array $oldMergedProfile = null){
    $force = false;

    $checkinPending = $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => '*',
      'whereCondition' => "WHERE (actionType = 'InstallProfile' OR actionType = 'RemoveProfile') AND lastPush IS NULL AND status = 0 AND deviceId = $deviceId ORDER BY priority DESC",
      'asObject' => true,
      'stdClass' => get_class($this),
      'constructorArgs' =>['DbCrud',[]],
    ]);

    if(!empty($checkinPending)){
      $force = true;
    }
    $mergedProfile = \Management::mergeWindowsProfiles($deviceId,$oldMergedProfile);
    $mergedProfileHasChanged = \Management::windowsMergedProfileChanged($mergedProfile,$oldMergedProfile);
    return $mergedProfileHasChanged || $force ? $mergedProfile : false;
  }

  /**
   * Checks if there is at least one install profile pending
   * @param int $deviceId
   * @return int
   */
  public function profileInstallationPending(int $deviceId):int{
    $row = $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => 'id',
      'whereCondition' => "WHERE (actionType = 'InstallProfile' OR actionType = 'RemoveProfile') AND deviceId = $deviceId",
      'limit' => 'LIMIT 1',
      'orderBy' => 'ORDER BY id desc',
    ]);
    if(!isset($row['id'])){
      return 0;
    }
    return (int)$row['id'];
  }

  /**
   * Sets last push time in db
   * @param array $rowsIds
   * @return bool
   */
  public function updateCheckinsPushTime(array $rowsIds):bool{
    $rowsIds = \array_unique($rowsIds);
    $idsString = implode(',',$rowsIds);
    if(empty($rowsIds) || empty($idsString) || $idsString === ''){
      return false;
    }
    return (bool)$this->getDataSource()->update([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'values' => ['lastPush' => time()],
      'whereCondition' => "WHERE id IN ($idsString)"
    ]);
  }

  /**
   * Removes Checkins from DB
   * @param int $deviceId
   * @return bool
   */
  public function removeDeviceCheckins(int $deviceId):bool{
    return $this->getDataSource()->delete([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'whereCondition' => "WHERE deviceId = $deviceId"
    ]);
  }

  /**
   * Removes Checkins from DB
   * @param int|null $profileId
   * @param int|null $mdmGroupId
   * @return bool
   */
  public function removeProfileCheckins(int $profileId = null, int $mdmGroupId = null):bool{
    if(!$profileId && !$mdmGroupId){
      return false;
    }
    $whereCondition = 'WHERE ';
    if($profileId){
      $whereCondition .= 'profileID = '.$profileId.' ';
    }
    if($mdmGroupId){
      $whereCondition .= 'mdmGroupId = '.$mdmGroupId.' ';
    }
    return $this->getDataSource()->delete([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'whereCondition' => $whereCondition
    ]);
  }

  /**
   * Removes single checkin
   * @param int $checkinId
   * @return bool
   */
  public function cancel(int $checkinId):bool{
    return $this->getDataSource()->update([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'values' => ['status' => \DeliveryStatus::CANCELLED],
      'whereCondition' => "WHERE id = $checkinId"
    ]);
  }

  /**
   * Update actionId in self::$table
   * @param array $ids
   * @param int $actionId
   * @return bool
   */
  public static function linkToChimpaAction(string $dataSource,array $ids,int $actionId):bool{
    $instance = new self($dataSource,[]);
    $idsString = implode(',',$ids);
    return (bool)$instance->getDataSource()->update([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'values' => [
        'actionId' => $actionId,
      ],
      'whereCondition' => "WHERE id IN ($idsString)"
    ]);
  }

  public function getParentMessage(int $parentId){
    return $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => '*',
      'whereCondition' => "WHERE id = $parentId",
      'limit' => 'LIMIT 1',
      'asObject' => true,
      'stdClass' => get_class($this),
      'constructorArgs' =>['DbCrud',[]],
    ]);
  }

  public function getDeleteCheckin(int $deviceId,string $deletedApp){
    return $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => '*',
      'whereCondition' => " WHERE (commandType='Delete'  AND deviceId = $deviceId AND commandURI LIKE '%/$deletedApp%') ",
      'limit' => 'LIMIT 1',
      'orderBy' => 'ORDER BY id desc',
      'asObject' => true,
      'stdClass' => __CLASS__,
      'constructorArgs' =>['DbCrud',[]],
    ]);
  }

  public function getExecCheckin(int $deviceId,string $commandUri){
    return $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => '*',
      'whereCondition' => " WHERE (commandType='Exec'  AND deviceId = $deviceId AND commandURI = '$commandUri')",
      'limit' => 'LIMIT 1',
      'orderBy' => 'ORDER BY id desc',
      'asObject' => true,
      'stdClass' => __CLASS__,
      'constructorArgs' =>['DbCrud',[]],
    ]);
  }



  public function toSyncMLString(int &$currentResponseCommandID):string{
    $params = $this->getParams(true);
    $meta = $params['meta'] ?? null;
    $data = $params['data'] ?? null;

    $command = '<' . $this->getCommandType() . '><CmdID>'
      . $currentResponseCommandID . '</CmdID><Item>';

    if ($this->getCommandURI() !== null) {
      $command .= '<Target><LocURI>' . $this->getCommandURI() . '</LocURI></Target>';
    }
    if ($meta !== null) {
      $command .= '<Meta>' . $meta . '</Meta>';
    }
    if ($data !== null) {
      $command .= '<Data>' . $data . '</Data>';
    }
    $command .= '</Item></' . $this->getCommandType() . '>';

    return $command;
  }

  /**
   * @var array $singleProfile,
   * @var int $priority
   * @var int $deviceId
   * @var string $clientUri
   * @var int $parentId fakeInstallProfile message id
   * @throws Exception
   */
  public function processInstallProfile(array $singleProfile, int &$priority,int $deviceId,string $clientUri,int $parentId){
    $certificatePasswordParameter = array_filter( //check if we must encrypt certificate password parameter
      $singleProfile,
      static function ($key){ //
        return(str_contains($key, 'PFXCertPassword') && !str_contains($key, 'EncryptionType'));
      },
      ARRAY_FILTER_USE_KEY
    );
    $passwordEncryptionSucceded = true;
    if($certificatePasswordParameter){ //encrypt certificate password with mdm enrollment certificate
      $passwordEncryptionSucceded = false;
      $arrayKey = array_keys($certificatePasswordParameter)[0];
      $chimpaEncryptedPassword = $certificatePasswordParameter[$arrayKey]['data'];
      try{
        $clearPassword=\Security::aes256Decrypt("egai@w**8R9_!f12Ya!a5F.84!AaBc!@", $chimpaEncryptedPassword);
      }catch(Exception){ // we have an openssl exception when the password in already in windows blob format
        $passwordEncryptionSucceded = true;
        $parameters[$arrayKey]['data'] = $chimpaEncryptedPassword;
      }
      $deviceData = \Device::getDeviceData($deviceId,['windowsCspCertificate']);

      if(!$passwordEncryptionSucceded && isset($deviceData['windowsCspCertificate'])) {
        $encryptionResult = \Security::encryptWindowsContent($deviceData['windowsCspCertificate'],$clearPassword,$deviceId,true);
        if($encryptionResult){
          $passwordEncryptionSucceded = true;
          $parameters[$arrayKey]['data'] = $encryptionResult;
        }
      }
    }

    if(!$passwordEncryptionSucceded){
      throw new \Exception('Could not encrypt certifciate password');
    }

    unset($singleProfile['onSingleDevice']);// key 'onSingleDevice' is used to perform profiles merge

    foreach($singleProfile as $cspUri => $parameters){ //get payloads from profile
      if(isset($parameters['restrictionLevel'])) {
        unset($parameters['restrictionLevel']);//unset restrictionLevel, it's used only to merge profile
      }
      $singleProfile[$cspUri] = $parameters;
    }

    //add message with entire single profile to queue
    if(!empty($singleProfile)) {
      $this->insertInstallProfileMessage($deviceId, $clientUri, $priority, $singleProfile, $parentId);
    }
  }

  /**
   * @param int $deviceId
   * @param string $URI
   * @param string $clientURI
   * @param int $priority
   * @var int $parentId fakeInstallProfile message id
   * @return mixed
   */
  public function insertInstallProfileMessage(int $deviceId,string $clientURI,int $priority,array $payloadParameters,int $parentId){

    $PayloadName = 'Policy';
    if(isset($payloadParameters['payloadType'])  && ($payloadParameters['payloadType'] == PayloadType::WINDOWS_CUSTOM_PAYLOAD))
      $PayloadName = 'CustomPayload';
    else if(isset($payloadParameters['payloadType'])  && ($payloadParameters['payloadType'] == PayloadType::WINDOWS_ADMX_POLICIES))
      $PayloadName = 'AdmxPolicies';
    else if(isset($payloadParameters['payloadType'])  && ($payloadParameters['payloadType'] == PayloadType::SECURITY_MTD))
      $PayloadName = 'FirewallConfiguration';
    else if(isset($payloadParameters['payloadType']) && ($payloadParameters['payloadType'] == PayloadType::CELLULAR)){
      $PayloadName = 'Cellular';
    }

    $message = ModelFactory::getSource('WindowsMessages\\' . $PayloadName,
      ['DbCrud',
        [
          'deviceId' => $deviceId,
          'sessionID' => 0,
          'msgID' => 1,
          'commandType' => 'Replace',
          'commandURI' => './FakePayloadInstall',
          'clientURI' => $clientURI,
          'cmdID' => 1,
          'params' => $payloadParameters,
          'parentId' => $parentId,
          'isParent' => 0,
          'priority' => $priority,
          'actionType' => $PayloadName
        ]
      ]);

    return $message->store();
  }

  /**
   * @param int $parentId
   * @return bool
   */
  public function allChildrenSuccess(int $parentId):bool{
    $count = $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => 'COUNT(*) AS cnt',
      'whereCondition' => " WHERE (parentId = $parentId AND status <> 200
            )"
    ]);
    if(!$count || !$count[0]['cnt'] || (int)$count[0]['cnt'] === 0){
      return true;
    }
    return false;
  }

  /**
   * @param int $parentId
   * @return bool
   */
  public function executionInProgress(int $parentId):bool{
    $count = $this->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => self::getTable(),
      'fields' => 'COUNT(*) AS cnt',
      'whereCondition' => " WHERE (parentId = $parentId AND status = 0)"
    ]);
    if(!$count || !$count[0]['cnt'] || (int)$count[0]['cnt'] === 0){
      return false;
    }
    return true;
  }

  /**
   * Sets first provisioning expected policies
   * @param string $locURI
   * @param string $sessionId
   * @param int $deviceId
   * @param string $agentCertificateId
   * @return void
   * @throws Exception
   */
  public static function addFirstSyncExpectedCommands(string $locURI, string $sessionId,int $deviceId, string $agentCertificateId){

    $deviceData = Device::getDeviceData($deviceId, ['isSupervised']);
    $isSupervised = (int)$deviceData['isSupervised'];

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\FirstSyncStatus',
      ['DbCrud',
        [
          'clientURI' => $locURI,
          'commandURI' => \WindowsURIs::getWindowsUri(\WindowsURIs::DM_EXPECTED_POLICIES,$isSupervised),
          'commandType' => 'Add',
          'sessionID' => $sessionId,
          'deviceId' => $deviceId,
          'params' => [
            'data' => '',
            'meta' => ' <Format xmlns="syncml:metinf">chr</Format>'
          ],
          'priority' => 500,
          'actionType' => 'FirstSyncStatus'
        ]
      ]);
    $message->store();

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\FirstSyncStatus',
      ['DbCrud',
        [
          'clientURI' => $locURI,
          'commandURI' => \WindowsURIs::getWindowsUri(\WindowsURIs::DM_EXPECTED_NETWORK_PROFILES,$isSupervised),
          'commandType' => 'Add',
          'sessionID' => $sessionId,
          'deviceId' => $deviceId,
          'params' => [
            'data' => '',
            'meta' => ' <Format xmlns="syncml:metinf">chr</Format>'
          ],
          'priority' => 500,
          'actionType' => 'FirstSyncStatus'
        ]
      ]);
    $message->store();

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\FirstSyncStatus',
      ['DbCrud',
        [
          'clientURI' => $locURI,
          'commandURI' => \WindowsURIs::getWindowsUri(\WindowsURIs::DM_EXPECTED_MSI,$isSupervised),
          'commandType' => 'Add',
          'sessionID' => $sessionId,
          'deviceId' => $deviceId,
          'params' => [
            'data' => \WindowsUris::MSI_INSTALLATION.'/'.self::$agentx64Id.'/DownloadInstall',
            'meta' => ' <Format xmlns="syncml:metinf">chr</Format>'
          ],
          'priority' => 500,
          'actionType' => 'FirstSyncStatus'
        ]
      ]);
    $message->store();

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\FirstSyncStatus',
      ['DbCrud',
        [
          'clientURI' => $locURI,
          'commandURI' => \WindowsURIs::DM_EXPECTED_PFX,
          'commandType' => 'Add',
          'sessionID' => $sessionId,
          'deviceId' => $deviceId,
          'params' => [
            'data' => \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$agentCertificateId,
            'meta' => ' <Format xmlns="syncml:metinf">chr</Format>'
          ],
          'priority' => 500,
          'actionType' => 'FirstSyncStatus'
        ]
      ]);
    $message->store();
  }

  /**
   * Apply expected policies
   * @param string $locURI
   * @param string $sessionId
   * @param int $deviceId
   * @param string $agentCertificateId
   * @return void
   * @throws Exception
   */
  public static function addFirstProvisioning(string $locURI, int $deviceId){

    //install agent certificate
    self::addAgentCertificateCommand($deviceId,null,null,$locURI);

    self::installAgentService($locURI,$deviceId);

  }

  /**
   * Adds install agent certificate to execution queue
   * @param int $deviceId
   * @param string|null $oldCertificateId
   * @param string|null $newCertificateId
   * @return bool
   * @throws Exception
   */
  public static function addAgentCertificateCommand(int $deviceId, string $oldCertificateId = null,string $newCertificateId = null,string $locURI = ''):bool{

    if($oldCertificateId){
      $messageDelete = \Lib\Models\ModelFactory::getSource('WindowsMessages\\PfxInstall',
        ['DbCrud',
          [
            'commandType' => 'Delete',
            'commandURI' => \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$oldCertificateId,
            'params' => [],
            'deviceId' => $deviceId,
            'isParent' => false,
            'priority' => 900,
            'actionType' => 'PfxInstall',
            'sessionID' => 0,
            'msgID' => 0,
            'clientURI' => $locURI,
            'cmdID' => 1,
          ]
        ]);
      $messageDelete->store();
    }


    if(!$newCertificateId){
      $newCertificateId =  bin2hex(openssl_random_pseudo_bytes(20));
    }

    $passphrase = \Security::randomPassword();
    $certificateBlob = \Device::generatePKCS12Certificate($deviceId,$passphrase);

    $deviceData = \Device::getDeviceData($deviceId,['windowsCspCertificate']);

    if(isset($deviceData['windowsCspCertificate'])) {
      $encryptionResult = \Security::encryptWindowsContent($deviceData['windowsCspCertificate'], $passphrase, $deviceId, true);
      if (!$encryptionResult) {
        return false;
      }
    }

    $cryptedPassphrase = $encryptionResult;

    $params = [
      \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$newCertificateId.'/KeyLocation' => ['format' => 'int', 'data' => 3],
      \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$newCertificateId.'/PFXCertBlob' => ['format' => 'chr', 'data' => $certificateBlob],
      \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$newCertificateId.'/PFXKeyExportable' => ['format' => 'bool', 'data' =>  'true'],
      \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$newCertificateId.'/PFXCertPassword' => ['format' => 'chr', 'data' => $cryptedPassphrase],
      \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$newCertificateId.'/PFXCertPasswordEncryptionType' => ['format' => 'int', 'data' => 1],
    ];
    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\PfxInstall',
      ['DbCrud',
        [
          'commandType' => 'Add',
          'commandURI' => \WindowsURIs::CERTIFICATE_PFX_INSTALL.'/'.$newCertificateId,
          'params' => $params,
          'deviceId' => $deviceId,
          'isParent' => false,
          'priority' => 899,
          'actionType' => 'PfxInstall',
          'sessionID' => 0,
          'msgID' => 0,
          'clientURI' => $locURI,
          'cmdID' => 1,
        ]
      ]);
    return (bool)$message->store();
  }

  public static function addProvisioninFinishedMessage(int $deviceId, string $locURI){
    $deviceData = Device::getDeviceData($deviceId, ['isSupervised']);
    $isSupervised = (int)$deviceData['isSupervised'];

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\FirstSyncStatus',
      ['DbCrud',
        [
          'clientURI' => $locURI,
          'commandURI' => \WindowsURIs::getWindowsUri(\WindowsURIs::DM_SUCCESSFULLY_PROVISIONED,$isSupervised),
          'commandType' => 'Replace',
          'sessionID' => 0,
          'deviceId' => $deviceId,
          'params' => [
            'data' => 'true',
            'meta' => ' <Format xmlns="syncml:metinf">bool</Format>'
          ],
          'priority' => 0,
          'actionType' => 'FirstSyncStatus'
        ]
      ]);
    $message->store();
  }

  public static function installAgentService(string $locURI, int $deviceId, $isUpdate = false){
    $baseModel = \Lib\Models\ModelFactory::getSource('BaseModel',['DbCrud']);
    $dbRes = $baseModel->getDataSource()->select([
      'databaseType' => \DatabaseType::CLIENT,
      'table' => 'tblDevices',
      'fields' => ['cpuArchitecture'],
      'whereCondition' => 'WHERE deviceId ='.$deviceId,
      'limit' => 'LIMIT 1'
    ]);
    if(!isset($dbRes['cpuArchitecture']) || $dbRes['cpuArchitecture'] === null){
      return false;
    }



    switch(strtolower($dbRes['cpuArchitecture'])){
      case '86':
        $agentId = self::$agentx86Id;
        $agentUrl = self::$agentx86Url;
        $agentHash = self::$agentx86Hash;
        break;
      case 'arm':
      case 'arm64':
        $agentId = self::$agentArmId;
        $agentUrl = self::$agentArmUrl;
        $agentHash = self::$agentArmHash;
        break;
      case '8664':
        $agentId = self::$agentx64Id;
        $agentUrl = self::$agentx64Url;
        $agentHash = self::$agentx64Hash;
        break;
    }

    //install agent MSI

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\InstallMSIApp',
      ['DbCrud',
        [
          'commandType' => 'Add',
          'commandURI' => WindowsURIs::MSI_INSTALLATION.'/'.urlencode($agentId).'/DownloadInstall',
          'params' => [],
          'deviceId' => $deviceId,
          'isParent' => false,
          'priority' => 650,
          'actionType' => 'InstallMSIApp',
          'sessionID' => 0,
          'msgID' => 0,
          'clientURI' => $locURI,
          'cmdID' => 1,
        ]
      ]);
    $message->store();

    $message = \Lib\Models\ModelFactory::getSource('WindowsMessages\\InstallMSIApp',
      ['DbCrud',
        [
          'commandType' => 'Exec',
          'commandURI' => WindowsURIs::MSI_INSTALLATION.'/'.urlencode($agentId).'/DownloadInstall',
          'params' => [
            'meta' => '<Format xmlns="syncml:metinf">xml</Format><Type xmlns="syncml:metinf">text/plain</Type>',
            'data' => '<MsiInstallJob id="'.$agentId.'">
                                        <Product Version="'.self::$agentVersion.'">
                                          <Download>
                                            <ContentURLList>
                                              <ContentURL>'.$agentUrl.'</ContentURL>
                                            </ContentURLList>
                                          </Download>
                                          <Validation>
                                            <FileHash>'.$agentHash.'</FileHash>
                                          </Validation>
                                          <Enforcement>
                                            <CommandLine>/quiet</CommandLine>
                                            <TimeOut>10</TimeOut>
                                            <RetryCount>1</RetryCount>
                                            <RetryInterval>10</RetryInterval>
                                          </Enforcement>
                                        </Product>
                                      </MsiInstallJob>'
          ],
          'deviceId' => $deviceId,
          'isParent' => 0,
          'priority' => 650,
          'actionType' => 'InstallMSIApp',
          'sessionID' => 0,
          'msgID' => 0,
          'clientURI' => $locURI,
          'cmdID' => 1,
        ]
      ]);
    $message->store();


    $data = ['windowsAgentVersion' => self::$agentVersion];
    \Device::updateInfo($data,$deviceId);
  }
}
