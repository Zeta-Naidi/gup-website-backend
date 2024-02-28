<?php

namespace App\Payloads\PAYLOD_ROTTI;

use App\Exception;
use App\Models\Payload;
use App\Models\UemDevice;
use App\Payloads\Android\DeviceModel;
use App\Payloads\Android\OsType;

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

class Restrictions extends Payload  //Applicationaccess
{
  public array $availableOs = ['Mixed', 'Android', 'Apple', 'Windows'];


  protected $allowAccountModification;
  protected $allowAddingGameCenterFriends;
  protected $allowAirDrop;
  protected $allowAppCellularDataModification;
  protected $allowAppInstallation;
  protected $allowAppRemoval;
  protected $allowAssistant;
  protected $allowAssistantUserGeneratedContent;
  protected $allowAssistantWhileLocked;
  protected $allowBookstore;
  protected $allowBookstoreErotica;
  protected $allowCamera;
  protected $allowChat;
  protected $allowCloudBackup;
  protected $allowManagedProfiles;
  protected $allowCloudDocumentSync;
  protected $allowCloudKeychainSync;
  protected $allowDiagnosticSubmission;
  protected $allowExplicitContent;
  protected $allowFindMyFriendsModification;
  protected $allowFingerprintForUnlock;
  protected $allowGameCenter;
  protected $allowGlobalBackgroundFetchWhenRoaming;
  protected $allowInAppPurchases;
  protected $allowLockScreenControlCenter;
  protected $allowHostPairing;
  protected $allowLockScreenNotificationsView;
  protected $allowLockScreenTodayView;
  protected $allowMultiplayerGaming;
  protected $allowOpenFromManagedToUnmanaged;
  protected $allowOpenFromUnmanagedToManaged;
  protected $allowOTAPKIUpdates;
  protected $allowPassbookWhileLocked;
  protected $allowPhotoStream;
  protected $allowSafari;
  protected $safariAllowAutoFill;
  protected $safariForceFraudWarning;
  protected $safariAllowJavaScript;
  protected $safariAllowPopups;
  protected $safariAcceptCookies;
  protected $allowSharedStream;
  protected $allowUIConfigurationProfileInstallation;
  protected $allowUntrustedTLSPrompt;
  protected $allowVideoConferencing;
  protected $allowVoiceDialing;
  protected $allowYouTube;
  protected $allowiTunes;
  protected $autonomousSingleAppModePermittedAppIDs;
  protected $forceAssistantProfanityFilter;
  protected $forceEncryptedBackup;
  protected $forceITunesStorePasswordEntry;
  protected $forceLimitAdTracking;
  protected $forceAirPlayOutgoingRequestsPairingPassword;
  protected $forceAirPlayIncomingRequestsPairingPassword;
  protected $allowManagedAppsCloudSync;
  protected $allowEraseContentAndSettings;
  protected $allowSpotlightInternetResults;
  protected $allowEnablingRestrictions;
  protected $allowActivityContinuation;
  protected $allowEnterpriseBookBackup;
  protected $allowEnterpriseBookMetadataSync;
  protected $allowPodcasts;
  protected $allowDefinitionLookup;
  protected $allowPredictiveKeyboard;
  protected $allowAutoCorrection;
  protected $allowSpellCheck;
  protected $forceWatchWristDetection;
  protected $allowMusicService;
  protected $allowCloudPhotoLibrary;
  protected $allowNews;
  protected $forceAirDropUnmanaged;
  protected $allowUIAppInstallation;
  protected $allowScreenShot;
  protected $allowKeyboardShortcuts;
  protected $allowPairedWatch;
  protected $allowPasscodeModification;
  protected $allowDeviceNameModification;
  protected $allowWallpaperModification;
  protected $allowAutomaticAppDownloads;
  protected $allowEnterpriseAppTrust;
  protected $allowEnterpriseAppTrustModification;
  protected $allowRadioService;
  protected $blacklistedAppBundleIDs;
  protected $whitelistedAppBundleIDs;
  protected $allowNotificationsModification;
  protected $allowRemoteScreenObservation;
  protected $allowDiagnosticSubmissionModification;
  protected $allowBluetoothModification;
  protected $ratingApps;
  protected $ratingMovies;
  protected $ratingRegion;
  protected $ratingTVShows;
  protected $allowDictation;
  protected $forceWiFiWhitelisting;
  protected $forceUnpromptedManagedClassroomScreenObservation;
  protected $allowAirPrint;
  protected $allowAirPrintCredentialsStorage;
  protected $forceAirPrintTrustedTLSRequirement;
  protected $allowAirPrintiBeaconDiscovery;
  protected $allowVPNCreation;
  protected $allowSystemAppRemoval;
  protected $allowSwitchUser;
  protected $allowAppsControl;
  protected $allowDebug;
  protected $allowBluetooth; //106
  protected $allowUnknownSources;
  protected $allowFilesUSBDriveAccess;
  protected $allowOutgoingBeam;
  protected $forceMDMNetworksOnly;
  protected $allowUsbMassStorage;
  protected $forceGoogleSafeSearch;
  protected $forceYoutubeSafetyMode;
  protected $allowExternalMedia;
  protected $blacklistedAndroidAppBundleIDs;
  protected $whitelistedAndroidAppBundleIDs;
  protected $allowTethering;
  protected $wifiSleepPolicy;
  protected $enforcedSoftwareUpdateDelay;
  protected $forceClassroomAutomaticallyJoinClasses;
  protected $forceClassroomRequestPermissionToLeaveClasses;
  protected $forceClassroomUnpromptedAppAndDeviceLock;
  protected $forceWiFiWhitelistingResetPassword;
  protected $allowProximitySetupToNewDevice;
  protected $forceAuthenticationBeforeAutoFill;
  protected $allowCellularPlanModification;
  protected $allowRemoteAppPairing;
  protected $allowFingerprintModification;
  protected $forceAutomaticDateAndTime;
  protected $allowPasswordAutoFill;
  protected $allowPasswordProximityRequests;
  protected $allowPasswordSharing;
  protected $allowUSBRestrictedMode;
  protected $allowSamsungAppStore;
  protected $allowESIMModification;
  protected $forceLocationEnabled;
  protected $allowOpenUnmanagedManaged;
  protected $allowAirplaneMode;
  protected $allowManagedToWriteUnmanagedContacts;
  protected $allowUnmanagedToReadManagedContacts;
  protected $allowSafeBoot;
  protected $allowConfigMobileNetworks;
  protected $allowCrossProfileCopyPaste;
  protected $allowPrinting;
  protected $allowShareLocation;
  protected $allowCrossProfileContactsSearch;
  protected $allowCrossProfileCallerId;
  protected $allowBluetoothContactSharing;
  protected $allowParentProfileAppLinking;
  protected $allowConfigCredentials;
  protected $allowConfigCellBroadcast;
  protected $allowDataRoaming;
  protected $allowNetworkReset;
  protected $allowOutgoingCall;
  protected $allowPersonalHotspotModification;
  protected $allowSiriServerLogging;
  protected $distressDaysForEmergencyMode;
  protected $allowDeviceSleep;
  protected $allowContinuousPathKeyboard;
  protected $allowFindMyDevice;
  protected $allowFindMyFriends;
  protected $allowWiFiPowerModification;
  protected $allowFilesNetworkDriveAccess;
  protected $forceWiFiPowerOn;
  protected $allowSharedDeviceTemporarySession;
  protected $allowStatusBar;
  protected $allowSetPasscode;
  protected $raitingRegion; //speciale
  protected $forceDelayedSoftwareUpdates;
  protected $forceClassroomUnpromptedScreenObservation; //speciale
  protected $allowAppClips;
  protected $forceDelayedAppSoftwareUpdates;
  protected $allowAdjustVolume;
  protected $allowUnmuteMicrophone;
  protected $homeLauncher;
  protected $accessibilityTools;
  protected $allowPersonalApps;
  protected $maxPauseForWorkProfile;
  protected $allowApplePersonalizedAdvertising;
  protected $forceKeepScreenOnInCharge;
  protected $allowConfigWifi;
  protected $allowAmbientDisplay;
  protected $allowConfigPrivateDNS;
  protected $allowConfigBrightness;
  protected $locationPrecisionDefaultValue;
  protected $touchLock;
  protected $remoteLock;
  protected $keypadLock;
  protected $bootLock;
  protected $forceWifiAutoReconnect;
  protected $blacklistedAndroidPersonalAppBundleIDs;
  protected $whitelistedAndroidPersonalAppBundleIDs;
  protected $forceGuestUserOnly;
  protected $forceGoogleAccountOnCOPE;
  protected $forceLoginScreen;
  protected $allowGoogleAccountScreenOnEnroll;
  protected $allowLocalUsersLoginScreen;
  protected $allowSSOLoginScreen;
  protected $allowGuestLoginScreen;
  protected $allowAutoUnlock;
  protected $forceOnDeviceOnlyDictation;
  protected $allowUnpairedExternalBootToRecovery;
  protected $requireManagedPasteboard;
  protected $forceOnDeviceOnlyTranslation;
  protected $allowUsbDataSignaling;
  protected $permittedInputMethods;
  protected $blockNetworkSystemPane;
  protected $blockNetworkWifiSystemPane;
  protected $blockNetworkEthernetSystemPane;
  protected $blockNetworkHotspotSystemPane;
  protected $blockLanguageSystemPane;
  protected $blockAppsSystemPane;
  protected $blockControlSystemPane;
  protected $blockSourceVideoSettingPane;
  protected $blockSettingVideoSettingPane;
  protected $blockCheckUpdate;
  protected $blockAutoCheckUpdate;
  protected $blockChangeScreenLockPassword;
  protected $blockBootLockScreen;
  protected $blockResetFactory;
  protected $blockPowerOnTime;
  protected $blockPowerOffTime;
  protected $blockWol;
  protected $blockRemoteLock;
  protected $blockTouchLock;
  protected $blockKeypadLock;
  protected $autoUpdateStatus;
  protected $wolStatus;
  protected $allowOfflineUnenroll;
  protected $setWifiMinLevelSecurity;
  protected $crossProfilePackages;
  protected $setGrantKeyPairToApp;
  protected $allowCloudPrivateRelay;
  protected $allowIncomingCalls;
  protected $forceVerifyApps;
  protected $allowGoogleAccountModification;
  protected $allowEndTask;
  protected $allowAutomaticScreenSaver;
  protected $allowMailPrivacyProtection;
  protected $blockWallpaperSlideshow;
  protected $blockNetworkBluetoothPane;
  protected $blockNetworkAudioInputPane;
  protected $blockNetworkUsbCameraPane;
  protected $blockSecurityPane;
  protected $blockOtherPane;
  protected $blockAutoSourceSwitch;
  protected $blockSourceRenaming;
  protected $blockBootSource;
  protected $blockSourceLock;
  protected $blockNoSignalAutoShutdown;
  protected $setLanguage;
  protected $allowRapidSecurityResponseInstallation;
  protected $allowRapidSecurityResponseRemoval;
  protected $MSIAlwaysInstallWithElevatedPrivileges;
  protected $requirePrivateStoreOnly;
  protected $allowStorePurchases;
  protected $allowSnippingTool;
  protected $removeWindowsStore_User;
  protected $removeWindowsStore_Device;
  protected $allowLanguage;
  protected $allowDateTime;
  protected $allowInputPersonalization;
  protected $allowRegion;
  protected $disableMSI;
  protected $allowAddingNonMicrosoftAccountsManually;
  protected $allowMicrosoftAccountConnection;
  protected $allowMicrosoftAccountSignInAssistant;
  protected $restrictToEnterpriseDeviceAuthenticationOnly;
  protected $checkWiFiSecurityTimingInMinutes;

  /*protected $AndroidCommandType = "CommandType::RESTRICTION";
  protected $PayloadType = PayloadType::RESTRICTIONS;
  protected $ApplePayloadType = ApplePayloadType::RESTRICTIONS;*/

  public function checkCompatibility($deviceIdentifier = NULL, UemDevice $device = NULL, string $specialOsType = NULL): bool
  {
    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if (!is_null($device)) {
      //niente
    } elseif (!is_null($deviceIdentifier)) {
      if (is_null($GLOBALS["device"])) {
        $GLOBALS["device"] = new UemDevice($deviceIdentifier);
      }
      $device = &$GLOBALS["device"];
    } elseif (!is_null($specialOsType)) {
      //niente
    } else {
      throw new \Exception("all args are null");
    }

    if ($device->osType == OsType::ANDROID || $specialOsType == OsType::ANDROID) {
      $return = true;
    } elseif ($device->osType == OsType::IOS || $specialOsType == OsType::IOS) {
      if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD || $device->modelName == DeviceModel::APPLETV) {
        $return = true;
      } elseif ($specialOsType == OsType::IOS) {
        $return = true;
      }
    } elseif ($device->osType == OsType::WINDOWS || $specialOsType == OsType::WINDOWS) {
      return true;
    }

    return $return;
  }



  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/RESTRICTIONS.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Restrizioni",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/SECURITY_MTD.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    // TODO: RICONTROLLARE TUTTI I CAMPI, aggiungere "category" => ""
    $schema = [
      [
        "id" => 1,
        "category" => "Sistema",
        "label" => "Consenti l'uso della fotocamera",
        "field_id" => "Field id: (allowCamera)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattivata, le fotocamere sono disabilitate o l'icona della fotocamera viene rimossa dalla schermata Home. Gli utenti non possono acquisire fotografie o video, oppure utilizzare FaceTime.<br><b>Disponibilità:</b> Disponibile con Android, iOS e Windows. iOS 13 richiede che il dispositivo sia supervisionato.",
        "options" => []
      ],
      [
        "id" => 2,
        "category" => "Sistema",
        "label" => "Abilita strumento di cattura",
        "field_id" => "Field id: (allowSnippingTool)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "Se disabilitato lo strumento Snipping Tool non sarà utilizzabile.<br><b>Disponibilità:</b> Disponibile solo con Windows.",
        "options" => []
      ],
      [
        "id" => 3,
        "category" => "Sistema",
        "label" => "Abilita impostazioni lingua",
        "field_id" => "Field id: (allowLanguage)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 4,
        "category" => "Sistema",
        "label" => "Abilita impostazioni regione",
        "field_id" => "Field id: (allowRegion)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 5,
        "category" => "Sistema",
        "label" => "Abilita impostazioni data e ora",
        "field_id" => "Field id: (allowDateTime)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 6,
        "category" => "Sistema",
        "label" => "Consenti debug (solo con Supervisione)",
        "field_id" => "Field id: (allowDebug)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattivata l’utente non può abilitare le Opzioni Sviluppatore.<br><b>Disponibilità:</b> Disponibile solo con Android e Windows.",
        "options" => []
      ],
      [
        "id" => 7,
        "category" => "Sistema",
        "label" => "Consenti acquisti dallo store",
        "field_id" => "Field id: (allowStorePurchases)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Permetti agli utenti di effettuare acquisti sullo store Microsoft",
        "options" => []
      ],
      [
        "id" => 8,
        "category" => "Sistema",
        "label" => "Consenti l'accesso all Windows Store",
        "field_id" => "Field id: (removeWindowsStore_User)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "Abilitando questa impostazione di policy, l'accesso all'applicazione Store sarà negato. È richiesto un riavvio. <br><b>Disponibilità:</b> Disponibile solo sui dispositivi enrolled.",
        "options" => [
          "---",
          "Non configurato",
          "Abilitato",
          "Disabilitato"
        ]
      ],
      [
        "id" => 9,
        "category" => "Sistema",
        "label" => "Nega l'accesso all Windows Store (Supervisionato)",
        "field_id" => "Field id: (removeWindowsStore_Device)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "Abilitando questa policy, l'accesso all'applicazione Store sarà negato. È richiesto un riavvio. <br><b>Disponibilità:</b> Disponibile solo sui dispositivi supervisionati.",
        "options" => [
          "---",
          "Non configurato",
          "Abilitato",
          "Disabilitato"
        ]
      ],
      [
        "id" => 10,
        "category" => "Sistema",
        "label" => "Disabilita Windows Installer",
        "field_id" => "Field id: (disableMSI)",
        "value" => "Mai",
        "input_type" => "multiselect",
        "description" => "L'opzione 'Mai' indica che Windows Installer è abilitato, l'opzione 'Solo app non gestite' consente agli utenti di installare solo i programmi assegnati da un amministratore di sistema, 'Sempre' indica che Windows Installer è disabilitato e disabilita anche l'installazione tramite Chimpa.",
        "options" => [
          "---",
          "Mai",
          "Solo app non gestite",
          "Sempre"
        ]
      ],
      [
        "id" => 11,
        "category" => "Sistema",
        "label" => "Consenti l'aggiunta di account email diversi da Microsoft",
        "field_id" => "Field id: (allowAddingNonMicrosoftAccountsManually)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Specifica se l'utente è autorizzato ad aggiungere account email diversi da quello di Microsoft",
        "options" => []
      ],
      [
        "id" => 12,
        "category" => "Sistema",
        "label" => "Consenti l'utilizzo dell'account Microsoft per connessioni non relative a email",
        "field_id" => "Field id: (allowMicrosoftAccountConnection)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Specifica se l'utente è autorizzato a utilizzare un account Microsoft per l'autenticazione e i servizi di connessione non relativi alle email.",
        "options" => []
      ],
      [
        "id" => 13,
        "category" => "Sistema",
        "label" => "Consenti l'assistente per l'accesso con account Microsoft",
        "field_id" => "Field id: (allowMicrosoftAccountSignInAssistant)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Consente agli amministratori IT di disabilitare il servizio Microsoft Account Sign-In Assistant (wlidsvc).",
        "options" => []
      ],
      [
        "id" => 14,
        "category" => "Sistema",
        "label" => "Consenti l'autenticazione solo su dispositivi aziendali ",
        "field_id" => "Field id: (restrictToEnterpriseDeviceAuthenticationOnly)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "Questa impostazione determina se consentire solo l'autenticazione su dispositivi aziendali per il servizio Microsoft Account Sign-in Assistant (wlidsvc). Quando il valore è attivo, consentiamo solo l'autenticazione su dispositivi e blocchiamo l'autenticazione su utenti.",
        "options" => []
      ],
      [
        "id" => 15,
        "category" => "Sicurezza e Privacy",
        "label" => "Consenti videochiamata (iOS deve essere Supervisionato) ",
        "field_id" => "Field id: (allowVideoConferencing)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattivata, Android disabilita le APP VoIP e iOS disabilita la funzionalità di videoconferenza.<br><b>Disponibilità:</b> Disponibile con Android e iOS.",
        "options" => []
      ],
      [
        "id" => 16,
        "category" => "Network & Cellulare",
        "label" => "Limita a periodo temporale definito",
        "field_id" => "Field id: (limitOnDates)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "Limita l'attivazione del profilo nei periodi temporali specificati. <br> Compatibile con iOS, tvOS, iPadOS, Android e Windows. iOS, tvOS, iPadOS e Windows devono essere collegati ad internet per poter aggiornare la schedulazioni.",
        "options" => []
      ],
      [
        "id" => 17,
        "category" => "Network & Cellulare",
        "label" => "Consenti modifica configurazioni WiFi ",
        "field_id" => "Field id: (allowConfigWifi)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattivata, previene le modifiche alle configurazioni WiFi.<br><b>Disponibilità:</b> Disponibile con Android e Windows",
        "options" => []
      ],
      [
        "id" => 18,
        "category" => "Network & Cellulare",
        "label" => "Consenti creazione VPN (solo con Supervisione)",
        "field_id" => "Field id: (allowVPNCreation)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattivata, impedisce la creazione di configurazioni VPN. Su Android impedisce anche l'attivazione della VPN. A partire da Android Nougat 7.0 rimangono però funzionanti le VPN Always-on configurate.<br><b>Disponibilità:</b> Disponibile su Windows, da Android Marshmallow 6.0 e superiore o iOS 11.0 e superiore",
        "options" => []
      ],
      [
        "id" => 19,
        "category" => "Network & Cellulare",
        "label" => "Modalità emergenza se offline",
        "field_id" => "Field id: (distressDaysForEmergencyMode)",
        "value" => 0,
        "input_type" => "number",
        "description" => "Imposta il numero massimo di giorni che il dispositivo può essere utilizzato senza alcuna comunicazione con il server MDM (0 la funzione non è attiva).<br><b>Disponibilità:</b> Disponibile solo con Android e Windows.",
        "options" => []
      ],
      [
        "id" => 20,
        "category" => "Network & Cellulare",
        "label" => "Imposta livello minimo sicurezza Wifi ",
        "field_id" => "Field id: (setWifiMinLevelSecurity)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "Se il dispositivo è Supervisionato, le reti non sicure verranno disconnesse automaticamente. Negli altri casi, l'utente verrà solo notificato.<br><b>Disponibilità:</b> Disponibile solo con Android e Windows. I servizi di localizzazione devono essere attivi da Android 10 al 12.",
        "options" => [
          "---",
          "Nessun Limite",
          "Consentite solo wep, wpa, wpa2, wpa3, wpa enterprise, wpa2 enterprise",
          "Consentite solo wpa, wpa2, wpa3, wpa enterprise, wpa2 enterprise",
          "Consentite solo wpa2, wpa3, wpa2 enterprise"
        ]
      ],
      [
        "id" => 21,
        "category" => "Periferiche & interfacciamento",
        "label" => "Consenti modifiche impostazioni Bluetooth (solo con Supervisione)",
        "field_id" => "Field id: (allowBluetoothModification)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattivata, impedisce modifiche alle impostazioni Bluetooth.<br><b>Disponibilità:</b> Disponibile su Windows, Android Lollipop 5.0 e superiore o iOS 10.0. e superiore",
        "options" => []
      ]
    ];

    if($osType === 'Mixed'){
      return $schema;
    }else{
      if (in_array($osType, $this->availableOs)) {
        $filteredSchema = array_filter($schema, function ($item) use ($osType) {
          return (strtolower($item['os']) === strtolower($osType) || strtolower($item['os']) === 'mixed');
        });
        // Reindex the array to reset the keys
        return array_values($filteredSchema);
      }else{
        return [];
        // throw \Exception::class
      }
    }
  }
}
