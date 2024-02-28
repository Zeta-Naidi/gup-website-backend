<?php

namespace App\Security;

use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MfaHandler
{
  private Encrypter|null $encrypter;

  private string|null $key;
  private $userId;
  private string $userAgent;
  private string $ipAddress;

  public function __construct(int $userId, string $userAgent, string $ipAddress)
  {
    $user = User::where('id', $userId)->first();
    $this->userId = $userId;
    $this->userAgent = $this->_standardizeUserAgent($userAgent);
    $this->ipAddress = $ipAddress;
    $this->key = !empty($user->encryptionKey) ? Crypt::decryptString($user->encryptionKey) : null;
    if (!$this->key)
      return;
    $this->encrypter = new Encrypter($this->key, 'aes-256-cbc');
  }

  /**
   * @param $deviceIdEncrypted
   * @param $userAgent
   * @param $ipAddress
   * @return int 0 if no device is found, 1 if device is found and ip and userAgent are the same,
   * 2 if device is found but ip is different, 3 if device is found but with different userAgent,
   * 4 if device is found but ip and userAgent are different.
   * -1 if deviceId is not valid
   */
  public function verifyCookieDeviceId($deviceIdEncrypted): int
  {
    try {
      if (!$this->key)
        return 0;
      $deviceId = $this->encrypter->decryptString($deviceIdEncrypted);
      $userDevice = DB::table('user_devices')
        ->where('deviceId', $deviceId)
        ->where('userId', $this->userId)
        ->first();
      if (!$userDevice)
        return 0;
      else if ($userDevice->userAgent == $this->userAgent && in_array($this->ipAddress,json_decode($userDevice->ipAddress)))
        return 1;
      else if ($userDevice->userAgent == $this->userAgent && !in_array($this->ipAddress,json_decode($userDevice->ipAddress)))
        return 2;
      else if ($userDevice->userAgent != $this->userAgent && in_array($this->ipAddress,json_decode($userDevice->ipAddress)))
        return 3;
      else if ($userDevice->userAgent != $this->userAgent && !in_array($this->ipAddress,json_decode($userDevice->ipAddress)))
        return 4;
      else return 0;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return -1;
    }
  }

  public function createUserDevice(): bool
  {
    try {
      if (!$this->key) {
        //FIRST USER DEVICE
        $encryptionKey = random_bytes(32);
        $encryptedKey = Crypt::encryptString($encryptionKey);
        User::where('id', $this->userId)->update([
          'encryptionKey' => $encryptedKey
        ]);
        $this->key = $encryptionKey;
        $this->encrypter = new Encrypter($this->key, 'aes-256-cbc');
      }
      $deviceId = Str::random(64);
      DB::table('user_devices')->insert([
        'userId' => $this->userId,
        'deviceId' => $deviceId,
        'userAgent' => $this->userAgent, //already standardized
        'ipAddress' => json_encode([$this->ipAddress]),
        'lastAccess' => new \DateTime()
      ]);
      Cookie::queue('deviceId', $this->encrypter->encryptString($deviceId), 43200); // 30 days
      //Cookie::queue(Cookie::forever('name', 'value'));
      return true;

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public function updateUserDevice($deviceIdEncrypted, string $updateType): bool
  {
    try {
      if (!$this->key)
        return false;
      $deviceId = $this->encrypter->decryptString($deviceIdEncrypted);
      $infosToUpdate = [];
      if($updateType == "UPDATE_ONLY_LAST_ACCESS")
        $infosToUpdate['lastAccess'] = new \DateTime();
      else if($updateType == "UPDATE_USER_AGENT"){
        $infosToUpdate['userAgent'] = $this->userAgent;// already standardized
        $infosToUpdate['lastAccess'] = new \DateTime();
      }
      else if($updateType == "UPDATE_IP" || $updateType == "UPDATE_USER_AGENT_AND_IP"){
        $userDevice = DB::table('user_devices')
          ->where('deviceId', $deviceId)
          ->where('userId', $this->userId)
          ->first();
        $ipAddressesStored = json_decode($userDevice->ipAddress);
        if($updateType == "UPDATE_USER_AGENT_AND_IP"){
          $infosToUpdate['userAgent'] = $this->userAgent; // already standardized
          $infosToUpdate['lastAccess'] = new \DateTime();
          $ipAddressesStored[] = $this->ipAddress;
          $infosToUpdate['ipAddress'] = json_encode($ipAddressesStored);
        }
        else{
          $infosToUpdate['lastAccess'] = new \DateTime();
          $ipAddressesStored[] = $this->ipAddress;
          $infosToUpdate['ipAddress'] = json_encode($ipAddressesStored);
        }
      }
      DB::table('user_devices')
        ->where('deviceId', $deviceId)
        ->where('userId', $this->userId)
        ->update($infosToUpdate);
      return true;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public function updateLastAccess($deviceIdEncrypted): bool
  {
    try {
      if (!$this->key)
        return false;
      $deviceId = $this->encrypter->decryptString($deviceIdEncrypted);
      DB::table('user_devices')
        ->where('deviceId', $deviceId)
        ->where('userId', $this->userId)
        ->update([
          'lastAccess' => new \DateTime()
        ]);
      return true;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  private function _standardizeUserAgent($userAgent)
  {
    // Remove version numbers and extra information
    $stringToClear = preg_replace('/\([\s\S]*?\)/', '', $userAgent);

    // Extract operating system information
    preg_match('/\((.*?)\)/', $userAgent, $osMatch);
    $os = trim($osMatch[0] ?? '()', '()') ;

    return str_replace(' ', '_', "BROWSER: " .
      strtolower($stringToClear). ' OS: ' . strtolower($os));
  }

}
