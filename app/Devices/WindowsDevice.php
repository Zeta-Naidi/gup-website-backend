<?php

namespace App\Devices;


use App\Repositories\ActionRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ReflectionException;

/**
 * Class WindowsDevice
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
class WindowsDevice {

  /**
   * Package security identifier for universal windows app
   * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465407.aspx
   * @var string
   */
  private static string $SID = "ms-app://s-1-15-2-3148538811-2544804391-3890369675-1715934417-1640841055-1843928913-1327183388"; //chimpa development wns
  private static string $agentSID = "ms-app://S-1-15-2-2591352551-3268106436-1778234298-2440169320-2899084117-3282934130-2030765334"; //chimpa agent app

  /**
   * Secret token for WNS OAuth authentication
   * @see https://msdn.microsoft.com/en-us/library/windows/apps/hh465407.aspx
   * @var string
   */
  private static string $ClientSecret = "8mCuDsjGDhT3XeJ4h6u5o9S7oa9l2bc4"; //chimpa development wns
  private static string $agentSecret = "U3w8Q~Rhn624p8jo1VOlub6mQN6r0c0sPcZiTcE4"; //chimpa agent

  /**
   * Link for WNS authentication
   * @var string
   */
  private static string $AuthUrl = "https://login.live.com/accesstoken.srf";

  private static string $PFN = "XNOOVAsrl.ChimpaAgentTest_44evp4fwrtjde";

  /**
   * The options configuration for the WNS
   * @var WNSNotificationOptions
   */
  private static WNSNotificationOptions $Options;

  private static $authObj;

  /**
   * @var $channelURI: the uri to POST notifications to
   */
  private static $channelURI;

  /**
   * The response to send to the client
   * @var string
   */
  private static string $response;

  /**
   * Array of commands to send to the client
   * @var array
   */
  private static array $responseCommands = [];

  /**
   * Next commandID to use in response
   * @var int
   */
  private static int $currentResponseCommandID = 1;

  /**
   * MsgID of current session
   * @var int
   */
  private static int $msgID = 1;

  /**
   * @return int
   */
  public static function getMsgID(): int
  {
    return self::$msgID;
  }

  /**
   * Returns current session ID
   * @return int
   */
  public static function getSessionId(): int
  {
    return self::$sessionId;
  }

  /**
   * SessionId of current session
   * @var int
   */
  private static int $sessionId = 0;

  /**
   * Unique identifier of device in the session
   * @var string
   */
  private static string $targetLocURI;

  /**
   * Array of alerts received from the client
   * @var array
   */
  private static array $requestAlerts = [];

  /**
   * @return array
   */
  public static function getRequestAlerts(): array
  {
    return self::$requestAlerts;
  }

  /**
   * Array of commands received from the client
   * @var array
   */
  private static array $requestCommands = [];

  /**
   * @return array
   */
  public static function getRequestCommands(): array
  {
    return self::$requestCommands;
  }

  /**
   * @param string $channelURI
   */
  public static function setChannelURI(string $channelURI): void
  {
    self::$channelURI = $channelURI;
  }

  /**
   * Set the header settings for notification request
   * @param WNSNotificationOptions $options The token
   */
  public static function SetOptions(WNSNotificationOptions $options): void
  {
    self::$Options = $options;
  }

  /**
   * @return mixed
   */
  public static function getChannelURI(): mixed
  {
    return self::$channelURI;
  }



  /**
   * send the push notification
   * @param array $channelURIs
   * @param bool $isAgent
   * @return bool
   */
  public static function sendPushNotification(array $channelURIs, bool $isAgent = false): bool
  {
    try {
      $res = true;
      foreach($channelURIs as $URI){
        if(!self::authenticate($isAgent) || self::initOMADMSession($URI) !== 200) {
          $res = false;
        }
      }
      return $res;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * Authenticates against WNS, return true on successful auth, false otherwise
   * @param $isAgent bool
   * @return bool
   */
  public static function authenticate(bool $isAgent = false):bool{
    /**  authenticate against WNS */
    try {
      $wnsAuth = self::AuthenticateService($isAgent);
      if($wnsAuth->response_status === 200){ // WNS_RESPONSE_CODES::SUCCESS
        $authObj = new OAuthObject();
        $authObj->SetToken($wnsAuth->access_token);
        $authObj->SetTokenType($wnsAuth->token_type);
        $options = new WNSNotificationOptions();
        $options->SetAuthorization($authObj);
        self::SetOptions($options);
        self::$authObj = $authObj;
        return true;
      }
      return false;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * Build the body content string for authentication request
   *
   * @param bool $isAgent
   * @return string
   */
  private static function buildRequest(bool $isAgent = false): string
  {
    try {
      $encodedSID = urlencode($isAgent ? self::$agentSID : self::$SID);
      $encodedSecret = urlencode($isAgent ? self::$agentSecret : self::$ClientSecret);

      return "grant_type=client_credentials&client_id=$encodedSID&client_secret=$encodedSecret&scope=notify.windows.com";
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }

  }

  /**
   * Authenticate service
   *
   * @param bool $isAgent
   * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-5.2
   * @return mixed
   */
  public static function authenticateService(bool $isAgent = false): mixed
  {
    try {
      $response = Http::withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
        ->post(self::$AuthUrl, [
          'body' => self::buildRequest($isAgent),
          'verify' => false, // Disable SSL verification (consider removing this in a production environment)
        ]);

      $responseData = $response->json();
      LOG::info(json_encode($responseData));
      LOG::info(json_encode(self::buildRequest($isAgent)));

      $responseData['token_type'] = ucfirst($responseData['token_type']);
      $responseData['response_status'] = $response->status();

      return $responseData;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * @param string $channelURI
   * @return mixed
   */
  public static function initOMADMSession(string $channelURI): mixed
  {
    try {
      self::setChannelURI($channelURI);
      $message = self::buildMessage(true);
      $status = self::Send($message);
      return $status['response'];
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * @param bool $firstMessage
   * @return string
   */
  private static function buildMessage(bool $firstMessage = false): string
  {
    try {
      $dataToStore = [];
      self::initSyncMLResponse();
      self::addSyncHdrToResponse();
      self::addBodyToResponse();
      if(!$firstMessage){
        self::addSyncHdrStatusToResponse();
        self::processRequestAlerts($dataToStore);
      }else{
        self::addAlertToResponse('1200'); // WNS_ALERT_CODES::SERVER_INIT
      }
      self::processRequestCommands();
      //TODO: WindowsMessage, ModelFactory::getSource
      /**
       * @var $command WindowsMessage
       * @var $windowsMessage IWindowsMessage
       */

      LOG::info(json_encode(self::$responseCommands));

      foreach (self::$responseCommands as $command) {
        try{
          $windowsMessage =  ModelFactory::getSource('WindowsMessages\\'.$command->getActionType(),
            ['DbCrud',
              [
                'commandType' => $command->getCommandType(),
                'commandURI' => $command->getCommandURI(),
                'params' => $command->getParams(),
                'deviceId' => $command->getDeviceId(),
                'isParent' => $command->getIsParent(),
                'priority' => $command->getPriority(),
                'actionType' => $command->getActionType(),
                'id' => $command->getId(),
                'clientURI' => $command->getClientURI(),
              ]
            ]);
        }catch(\Exception $exception){
          \App\Exceptions\CatchedExceptionHandler::handle($exception);
          $windowsMessage = null;
        }

        if($windowsMessage instanceof IWindowsMessage) {
          self::$response.= $windowsMessage->toSyncMLString(self::$currentResponseCommandID);
        }else{
          self::$response.= $command->toSyncMLString(self::$currentResponseCommandID);
        }

        /**
         * @var ActionRepository $repository
         */
        $repository = app(ActionRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $updated = $repository->updateWindowsCheckins($command->getId(),self::$currentResponseCommandID, self::getMsgID(), self::getSessionId())->formatControllerResponse();

        //$command->updateDbMessageIds($command->getId(),self::$currentResponseCommandID, self::getMsgID(), self::getSessionId());
        self::$currentResponseCommandID++;
      }
      self::addFinalSyncMLTag();
      self::closeResponseBody();
      self::closeSyncMLResponse();
      return self::$response;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * @return void
   */
  public static function processRequestCommands(): void
  {
    try {
      foreach ( self::getRequestCommands() as $cmdId => $command) {
        self::addStatusToResponse($cmdId, $command, 200); // WNS_RESPONSE_CODES::SUCCESS
      }
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @param array $dataToStore
   * @return void
   */
  public static function processRequestAlerts(array &$dataToStore): void
  {
    try {
      foreach (self::getRequestAlerts() as $cmdId => $alert) {
        if ($alert['Data'] === '1201') { //  WNS_ALERT_CODES::CLIENT_INIT
          self::addStatusToResponse($cmdId, 'Alert', 200); // WNS_RESPONSE_CODES::SUCCESS
        }elseif($alert['Data'] === '1226' || $alert['Data'] === '1224'){ // WNS_ALERT_CODES::GENERIC,  WNS_ALERT_CODES::CLIENT_EVENT
          self::addStatusToResponse($cmdId,'Alert',self::processAlertInfo($alert,$dataToStore));
        }
      }
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @param array $alert
   * @param array $dataToStore
   * @return int
   */
  public static function processAlertInfo(array $alert,array &$dataToStore):int{
    try {
      // TODO: RIVEDERE COMPLETAMENTE
      /**
       * @var $model IWindowsAlertReceiver | null
       */
      $item = $alert['Item'];
      if(isset($GLOBALS['deviceId'],$item['Meta']['Type'])) {
        switch($item['Meta']['Type']){
          case 'Reversed-Domain-Name:com.microsoft.mdm.EnterpriseAppUninstall.result': //uninstall app alert
            $model = ModelFactory::getSource('WindowsMessages\\RemoveApp',['DbCrud',[]]);
            break;
          case 'Reversed-Domain-Name:com.microsoft.mdm.EnterpriseStoreAppInstall.result': //install app from store alert
            $model = ModelFactory::getSource('WindowsMessages\\InstallStoreApp',['DbCrud',[]]);
            break;
          case 'Reversed-Domain-Name:com.microsoft.mdm.win32csp_install': //install app from MSI
            $model = ModelFactory::getSource('WindowsMessages\\InstallMSIApp',['DbCrud',[]]);
            break;
          case 'com.microsoft:mdm.unenrollment.userrequest': //unenroll request
            $model = ModelFactory::getSource('WindowsMessages\\Unenroll',['DbCrud',[]]);
            break;
          default:
            $model = null;
            break;
        }
        if($model instanceof IWindowsAlertReceiver){
          $model->onDeviceAlert($item,$dataToStore);
        }
      }
      return 200;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return 500;
    }
  }

  /**
   * @param string $commandId
   * @param string $commandType
   * @param int $responseCode
   * @return bool
   */
  public static function addStatusToResponse(string $commandId, string $commandType, int $responseCode): bool
  {
    try {
      self::$response .= '<Status><CmdID>'
        . self::$currentResponseCommandID . '</CmdID><MsgRef>'
        . self::$msgID . '</MsgRef><CmdRef>'
        . $commandId . '</CmdRef><Cmd>'
        . $commandType . '</Cmd><Data>'
        . $responseCode . '</Data></Status>';
      self::$currentResponseCommandID++;
      return true;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * Add alert to response with $data code
   * @param string $data
   */
  public static function addAlertToResponse(string $data): void
  {
    try {
      self::$response .= '<Alert><CmdID>'.self::$currentResponseCommandID.'</CmdID><Data>'.$data.'</Data></Alert>';
      self::$currentResponseCommandID++;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * Send a notification
   *
   * @param string $toastTemplate Toast XML template with values
   * @param string $method HTTP method (default is Post)
   * @return array
   * @throws ReflectionException
   */
  public static function send(string $toastTemplate, string $method = 'POST'): array
  {
    try {
      $headers = self::$Options->getHeaderArray();

      $response = Http::withHeaders($headers)
        ->withBody($toastTemplate, 'text/xml')
        ->request($method, self::getChannelURI());

      return [
        'WNS' => explode("\n", $response->headers()),
        'response' => $response->status(),
      ];
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return [];
    }
  }

  /**
   * @return void
   */
  public static function initSyncMLResponse(): void
  {
    try {
      self::$response = '<SyncML xmlns="SYNCML:SYNCML1.2">';
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @return void
   */
  public static function closeSyncMLResponse(): void
  {
    try {
      self::$response .= '</SyncML>';
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @return void
   */
  public static function addFinalSyncMLTag(): void
  {
    try {
      self::$response .= '<Final />';
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @return void
   */
  public static function addSyncHdrStatusToResponse(): void
  {
    try {
      self::$response .= '<Status><CmdID>'
        . self::$currentResponseCommandID . '</CmdID><MsgRef>'
        . self::$msgID . '</MsgRef><CmdRef>0</CmdRef><Cmd>SyncHdr</Cmd><Data>200</Data></Status>';
      self::$currentResponseCommandID++;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @return void
   */
  public static function addSyncHdrToResponse(): void
  {
    try {
      self::$response .= '<SyncHdr><VerDTD>1.2</VerDTD><VerProto>DM/1.2</VerProto><SessionID>'
        . self::$sessionId . '</SessionID><MsgID>' . (self::$msgID) . '</MsgID><Target><LocURI>' . self::$targetLocURI . '</LocURI></Target><Source><LocURI>'
        . "CHIMPSKY_URL_SECURE" . 'api/latest/mdm/windows/omadm/wns</LocURI></Source></SyncHdr>';
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }
  // TODO: CHIMPSKY_URL_SECURE  ___  'https://' . HOST_PUBLIC . '/' . CHIMPSKY_ALIAS_FOLDER_NAME . '/';

  /**
   * @return void
   */
  public static function addBodyToResponse(): void
  {
    try {
      self::$response .= '<SyncBody>';
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

  /**
   * @return void
   */
  public static function closeResponseBody(): void
  {
    try {
      self::$response .= '</SyncBody>';
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }

}
