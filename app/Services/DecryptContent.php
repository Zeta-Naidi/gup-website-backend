<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class DecryptContent
{
  public static function decryptAgentContent($alias, $identifier1, $identifier2, $encData, bool $use_old_signature = false)
  {
    $certsPath = resource_path('\certs'); // Assuming your certs are in the storage directory

    /*if (!file_exists($certsPath . '\signcerts')) {
      @mkdir($certsPath . '\signcerts', 0777, true);
      @chown($certsPath . '\signcerts', 'apache');
      @chmod($certsPath . '\signcerts', 0777);
    }

    if (!file_exists($certsPath . '\signcerts\sign.cer')) {
      copy($certsPath . '\sign.cer', $certsPath . '\signcerts\sign.cer');
      copy($certsPath . '\sign.key', $certsPath . '\signcerts\sign.key');
      copy($certsPath . '\old_sign.key', $certsPath . '\signcerts\old_sign.key');
      copy($certsPath . '\old_sign.cer', $certsPath . '\signcerts\old_sign.cer');
      //copy($certsPath . 'chain.cer', $certsPath . '/signcerts/chain.cer');
    }*/

    $privkeyPath = $use_old_signature ? $certsPath . '\signcerts\old_sign.key' : $certsPath . '\signcerts\sign.key';
    $pubkeyPath = $use_old_signature ? $certsPath . '\signcerts\old_sign.cer' : $certsPath . '\signcerts\sign.cer';

    $privkey = file_get_contents($privkeyPath);
    $pubkey = file_get_contents($pubkeyPath);

    $encData = base64_decode($encData);

    $filetmp = "app\Temp\PublicFiles\'" . $identifier1 . "#" . $identifier2 . "_" . /*Network::getLocalIp(NetworkEntityType::CLIENT) .  "_".*/ $alias . "_" . round((float)microtime() * 100000) . rand(100000, 999999) . ".tmp";

    if (!is_dir(dirname($filetmp))) {
      mkdir(dirname($filetmp), 0777, true);
    }

    file_put_contents($filetmp, $encData);

    $private_key = openssl_pkey_get_private($privkey);

    $ttt = openssl_pkcs7_decrypt($filetmp, $filetmp . "unenc", $pubkey, $private_key);

    unlink($filetmp);
    unset($private_key);

    if (file_exists($filetmp . "unenc")) {
      $data = file_get_contents($filetmp . "unenc");
      unlink($filetmp . "unenc");
    } else {
      $data = null;
    }

    return $data;
  }
}
