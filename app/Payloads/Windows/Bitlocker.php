<?php

namespace App\Payloads\Windows;

use App\Exception;
use App\Models\Payload;

/*enum PayloadType: string
{
  case RESTRICTIONS = 'RESTRICTIONS';
}

enum ApplePayloadType: string
{
  case RESTRICTIONS = 'RESTRICTIONS';
}

enum DeviceModel: string
{
  case IPAD = 'ipad';
  case IPHONE = 'iphone';
  case IPOD = 'ipod';
  case APPLETV = 'appletv';
}*/

class Bitlocker extends Payload
{
  public array $availableOs = ['Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/BITLOCKER_CSP.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Bitlocker",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        'id' => 1,
        "os" => ["Windows"],
        'label' => 'Richiedi cifratura dispositivo',
        'field_id' => 'Field id: (RequireDeviceEncryption)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 2,
        "os" => ["Windows"],
        'label' => 'Abilita cifratura per tipo di disco',
        'field_id' => 'Field id: (EncryptionMethodByDriveType)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 3,
        "os" => ["Windows"],
        'label' => 'Seleziona metodo di cifratura per i dischi del sistema operativo',
        'field_id' => 'Field id: (EncryptionMethodWithXtsOsDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 2,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'AES-CBC 128',
          2 => 'AES-CBC 256',
          3 => 'XTS-AES 128',
          4 => 'XTS-AES 256',
        ],
      ],
      [
        'id' => 4,
        "os" => ["Windows"],
        'label' => 'Seleziona metodo di cifratura per i dischi fissi',
        'field_id' => 'Field id: (EncryptionMethodWithXtsFdvDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 2,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'AES-CBC 128',
          2 => 'AES-CBC 256',
          3 => 'XTS-AES 128',
          4 => 'XTS-AES 256',
        ],
      ],
      [
        'id' => 5,
        "os" => ["Windows"],
        'label' => 'Seleziona metodo di cifratura per i dischi rimovibili',
        'field_id' => 'Field id: (EncryptionMethodWithXtsRdvDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 2,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'AES-CBC 128',
          2 => 'AES-CBC 256',
          3 => 'XTS-AES 128',
          4 => 'XTS-AES 256',
        ],
      ],
      [
        'id' => 6,
        "os" => ["Windows"],
        'label' => 'Abilita campo identificativo di Bitlocker',
        'field_id' => 'Field id: (EnableIdentificationField)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 23,
        "os" => ["Windows"],
        'label' => 'Campo identificativo BitLocker',
        'field_id' => 'Field id: (EnableIdentificationField)',
        'value' => '',
        'input_type' => 'text',
        'description' => '',
        'dependable' => [
          'id' => 6,
          'value' => true,
        ],
        'options' => [],
      ],
      [
        'id' => 24,
        "os" => ["Windows"],
        'label' => 'Campo abilitato identificazione BitLocker',
        'field_id' => 'Field id: (SecIdentificationField)',
        'value' => '',
        'input_type' => 'text',
        'description' => '',
        'dependable' => [
          'id' => 6,
          'value' => true,
        ],
        'options' => [],
      ],
      [
        'id' => 7,
        "os" => ["Windows"],
        'label' => 'Abilita pre-boot PIN sui dispositivi conformi a InstantGo o HSTI',
        'field_id' => 'Field id: (SystemDrivesEnablePreBootPinExceptionOnDECapableDevice)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 8,
        "os" => ["Windows"],
        'label' => 'Abilita enhanced PIN',
        'field_id' => 'Field id: (SystemDrivesEnhancedPIN)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 9,
        "os" => ["Windows"],
        'label' => 'Abilita utente standard alla modifica del PIN (dischi di sistema)',
        'field_id' => 'Field id: (SystemDrivesDisallowStandardUsersCanChangePIN)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 10,
        "os" => ["Windows"],
        'label' => 'Abilita opzioni di autenticazione pre-boot',
        'field_id' => 'Field id: (SystemDrivesEnhancedPIN)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 11,
        "os" => ["Windows"],
        'label' => 'Abilita tipo di crittografia (dischi di sistema)',
        'field_id' => 'Field id: (EnableSystemDrivesEncryptionType)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 25,
        "os" => ["Windows"],
        'label' => 'Tipo di crittografia',
        'field_id' => 'Field id: (SystemDrivesEncryptionType)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 11,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'Permetti all\'utente di scegliere',
          2 => 'Cifratura completa',
          3 => 'Cifratura sullo spazio in uso',
        ],
      ],
      [
        'id' => 12,
        "os" => ["Windows"],
        'label' => 'Richiedi autenticazione all\'avvio (dischi di sistema)',
        'field_id' => 'Field id: (SystemDrivesRequireStartupAuthentication)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
      ],
      [
        'id' => 26,
        "os" => ["Windows"],
        'label' => 'Abilita configurazione chiavi start-up',
        'field_id' => 'Field id: (ConfigureNonTPMStartupKeyUsage_Name)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'dependable' => [
          'id' => 12,
          'value' => true,
        ],
        'options' => [],
      ],
      [
        'id' => 27,
        "os" => ["Windows"],
        'label' => 'Configura chiave start-up TPM',
        'field_id' => 'Field id: (ConfigureTPMStartupKeyUsageDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 12,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'facoltativo',
          2 => 'richiesto',
          3 => 'Non consentito',
        ],
      ],
      [
        'id' => 28,
        "os" => ["Windows"],
        'label' => 'Configura PIN TPM',
        'field_id' => 'Field id: (ConfigurePINUsageDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 12,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'facoltativo',
          2 => 'richiesto',
          3 => 'Non consentito',
        ],
      ],
      [
        'id' => 29,
        "os" => ["Windows"],
        'label' => 'Configura PIN e chiave TPM',
        'field_id' => 'Field id: (ConfigureTPMPINKeyUsageDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 12,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'facoltativo',
          2 => 'richiesto',
          3 => 'Non consentito',
        ],
      ],
      [
        'id' => 30,
        "os" => ["Windows"],
        'label' => 'Configura avvio TPM',
        'field_id' => 'Field id: (ConfigureTPMUsageDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 12,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'facoltativo',
          2 => 'richiesto',
          3 => 'Non consentito',
        ],
      ],
      [
        'id' => 13,
        "os" => ["Windows"],
        'label' => 'Richiedi lunghezza minima PIN (dischi di sistema)',
        'field_id' => 'Field id: (SystemDrivesMinimumPINLength)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 31,
        "os" => ["Windows"],
        'label' => 'Lunghezza minima PIN',
        'field_id' => 'Field id: (MinPINLength)',
        'value' => '',
        'input_type' => 'number',
        'description' => '',
        'dependable' => [
          'id' => 13,
          'value' => true,
        ],
        'options' => [],
      ],
      [
        'id' => 14,
        "os" => ["Windows"],
        'label' => 'Abilita Messaggio di ripristino (dischi di sistema) ',
        'field_id' => 'Field id: (SystemDrivesRecoveryMessage)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 32,
        "os" => ["Windows"],
        'label' => 'Abilita Messaggio di ripristino (dischi di sistema) ',
        'field_id' => 'Field id: (PrebootRecoveryInfoDropDown_Name)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'dependable' => [
          'id' => 14,
          'value' => true,
        ],
        'options' => [
          0 => '---',
          1 => 'Vuoto',
          2 => 'Messaggio e URL di default',
          3 => 'Messaggio di ripristino personalizzato',
          4 => 'URL di ripristino personalizzato',
        ],
      ],
      [
        'id' => 33,
        "os" => ["Windows"],
        'label' => 'Testo messaggio di ripristino',
        'field_id' => 'Field id: (RecoveryMessage_Input)',
        'value' => '',
        'input_type' => 'text',
        'description' => '',
        'dependable' => [
          'id' => 32,
          'value' => 'Messaggio di ripristino personalizzato',
        ],
      ],
      [
        'id' => 34,
        "os" => ["Windows"],
        'label' => 'Url di informazioni sul ripristino',
        'field_id' => 'Field id: (RecoveryUrl_Input)',
        'value' => '',
        'input_type' => 'text',
        'description' => '',
        'dependable' => [
          'id' => 32,
          'value' => 'URL di ripristino personalizzato',
        ],
      ],
      [
        'id' => 15,
        "os" => ["Windows"],
        'label' => 'Scegli come dischi di sistema protetti da Bitlocker possono essere ripristinati',
        'field_id' => 'Field id: (SystemDrivesRecoveryOptions)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 16,
        "os" => ["Windows"],
        'label' => 'Opzioni avanzate crittografia (dischi fissi)',
        'field_id' => 'Field id: (FixedDrivesRecoveryOptions)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 17,
        "os" => ["Windows"],
        'label' => 'Disabilita accesso in scrittura ai dischi fissi non protetti da Bitlocker',
        'field_id' => 'Field id: (FixedDrivesRequireEncryption)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 18,
        "os" => ["Windows"],
        'label' => 'Tipo crittografia per dischi fissi',
        'field_id' => 'Field id: (FixedDrivesEncryptionType)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'options' => [
          0 => '---',
          1 => 'Permetti all\'utente di scegliere',
          2 => 'Cifratura completa',
          3 => 'Cifratura sullo spazio in uso',
        ],
      ],
      [
        'id' => 19,
        "os" => ["Windows"],
        'label' => 'Richiedi crittografia per dischi rimovibili',
        'field_id' => 'Field id: (RemovableDrivesRequireEncryption)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 20,
        "os" => ["Windows"],
        'label' => 'Consenti agli utenti di controllare BitLocker su disposistivi removibili',
        'field_id' => 'Field id: (RemovableDrivesConfigureBDE)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => '',
        'options' => [],
      ],
      [
        'id' => 21,
        "os" => ["Windows"],
        'label' => 'Abilita enhanced PIN',
        'field_id' => 'Field id: (SystemDrivesEnhancedPIN)',
        'value' => '---',
        'input_type' => 'multiselect',
        'description' => '',
        'options' => [
          0 => '---',
          1 => 'Messaggio di avviso disabilitato',
          2 => 'Messaggio di avviso consentito',
        ],
      ],
      [
        'id' => 22,
        "os" => ["Windows"],
        'label' => 'Forza criptaggio disco anche se in sessione utente standard',
        'field_id' => 'Field id: (AllowStandardUserEncryption)',
        'value' => false,
        'input_type' => 'checkbox',
        'description' => 'Disponibile solo per gli account Azure AD',
        'options' => [],
      ],
    ];

    if (in_array($osType, $this->availableOs)) {
      return $schema;
    }else{
      return [];
      // throw \Exception::class
    }
  }
}
