<?php

namespace App\Payloads\PAYLOD_ROTTI;

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

class Network extends Payload
{

  public function checkCompatibility($osType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ($osType == "android" || $osType == "ios" || $osType == "mixed" ) {
      $return = true;
    }

    return $return;
  }

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WIFI_MANAGED_WINDOWS.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Network",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(): array
  {
    //  ___ ANDROID:
    // SSID_STR
    // HIDDEN_NETWORK
    // ProxyType
    // EncryptionType
    // grantKeyPairToWifiAuth

    //  ___ IOS:
    // SSID_STR
    // HIDDEN_NETWORK
    // AutoJoin
    // CaptiveBypass
    // DisableAssociationMACRandomization
    // ProxyType
    // EncryptionType
    // IsHotspot

    //  ___ WINDOWS: (form, con form annidati)
    // Wlan_XML
    //    - NAME_WLAN
    //    - SSIDConfig (form)
    //        - SSID
    //        - nonBroadcast
    //    - Hotspot2 (form)
    //        - DomainName
    //        - NAIRealm
    //        - Network3GPP
    //        - RoamingConsortium
    //    - connectionType
    //    - connectionMode
    //    - autoSwitch
    //    - MSM_CONFIGURATION (form)
    //        - ALTRO....

    //  ___ MIXED (only Android e IOS):
    // SSID_STR
    // HIDDEN_NETWORK
    // AutoJoin
    // CaptiveBypass
    // DisableAssociationMACRandomization
    // ProxyType
    // EncryptionType
    // IsHotspot
    // grantKeyPairToWifiAuth

    return [
      [
        "id" => 1,
        "os" => "Mixed",
        "label" => "Schema profilo WLAN",
        "field_id" => "Field id: (Wlan_XML)",
        "value" => "",
        "input_type" => "form",
        "description" => "Parametri che descrivono la configurazione di rete",
        "form_inputs" => [
          [
            "id" => 1,
            "label" => "Nome",
            "field_id" => "Field id: (NAME_WLAN)",
            "value" => "",
            "input_type" => "text",
            "description" => "Nome del profilo LAN wirless"
          ],
          [
            "id" => 2,
            "label" => "Configurazione SSID",
            "field_id" => "Field id: (SSIDConfig)",
            "value" => "---",
            "input_type" => "form",
            "description" => "Contiene uno o più SSID per le reti LAN wirless",
            "form_inputs" => [
              [
                "id" => 1,
                "label" => "SSID",
                "field_id" => "Field id: (SSID)",
                "value" => "",
                "input_type" => "text",
                "description" => ""
              ],
              [
                "id" => 2,
                "label" => "Rete nascosta",
                "field_id" => "Field id: (nonBroadcast)",
                "value" => false,
                "input_type" => "checkbox",
                "description" => "",
                "options" => []
              ]
            ],
            "form_outputs" => [],
            "options" => []
          ],
          [
            "id" => 3,
            "label" => "Supporto rete Hotspot 2.0",
            "field_id" => "Field id: (Hotspot2)",
            "value" => "",
            "input_type" => "form",
            "description" => "Hotspot 2.0 consente la connessione automatica ai servizi di WI-FI pubblici usando le credenziali esistenti e l'appartenenza alle reti provider di servizi",
            "form_inputs" => [
              [
                "id" => 1,
                "label" => "Nome dominio",
                "field_id" => "Field id: (DomainName)",
                "value" => "",
                "input_type" => "text",
                "description" => ""
              ],
              [
                "id" => 2,
                "label" => "Area di autenticazione di rete (NAI)",
                "field_id" => "Field id: (NAIRealm)",
                "value" => "",
                "input_type" => "text",
                "description" => ""
              ],
              [
                "id" => 3,
                "label" => "ID della Rete Mobile (PLMN) pubblico",
                "field_id" => "Field id: (Network3GPP)",
                "value" => "",
                "input_type" => "text",
                "description" => ""
              ],
              [
                "id" => 4,
                "label" => "Identificatore univoco dell'organizzazione (OUI) assegnato da IEE",
                "field_id" => "Field id: (RoamingConsortium)",
                "value" => "",
                "input_type" => "text",
                "description" => ""
              ]
            ],
            "form_outputs" => [],
            "options" => []
          ],
          [
            "id" => 4,
            "label" => "Modalità operativa della rete",
            "field_id" => "Field id: (connectionType)",
            "value" => "---",
            "input_type" => "multiselect",
            "description" => "",
            "options" => [
              "---",
              "IBSS",
              "ESS"
            ]
          ],
          [
            "id" => 5,
            "label" => "Modalità di connessione della rete",
            "field_id" => "Field id: (connectionMode)",
            "value" => "---",
            "input_type" => "multiselect",
            "description" => "",
            "options" => [
              "---",
              "Automatico",
              "Manuale"
            ]
          ],
          [
            "id" => 6,
            "label" => "Connetti automaticamente",
            "field_id" => "Field id: (autoSwitch)",
            "value" => false,
            "input_type" => "checkbox",
            "description" => "",
            "options" => []
          ],
          [
            "id" => 7,
            "label" => "Configurazione MSM",
            "field_id" => "Field id: (MSM_CONFIGURATION)",
            "value" => "---",
            "input_type" => "form",
            "description" => "",
            "form_inputs" => [
              [
                "id" => 1,
                "label" => "Standard LAN wireless 802.11",
                "field_id" => "Field id: (phyType)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  "A",
                  "B",
                  "G",
                  "N"
                ]
              ],
              [
                "id" => 2,
                "label" => "Autenticazione",
                "field_id" => "Field id: (autentication)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  "OPEN",
                  "SHARED",
                  "WPA",
                  "WPAPSK",
                  "WPA2",
                  "WPA2PSK",
                  "WPA3SAE"
                ]
              ],
              [
                "id" => 3,
                "label" => "Crittografia",
                "field_id" => "Field id: (encryption)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  "NONE",
                  "WEP",
                  "TKIP",
                  "AES"
                ]
              ],
              [
                "id" => 4,
                "label" => "Autenticazione 802.1X",
                "field_id" => "Field id: (useOneX)",
                "value" => false,
                "input_type" => "checkbox",
                "description" => ""
              ],
              [
                "id" => 5,
                "label" => "Tipo chiave condivisa",
                "field_id" => "Field id: (keyType)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  "NETWORKKEY",
                  "PASSPHRASE"
                ]
              ],
              [
                "id" => 6,
                "label" => "Chiave di Rete/Passphrase",
                "field_id" => "Field id: (keyMaterial)",
                "value" => "",
                "input_type" => "text",
                "description" => "",
                "options" => []
              ],
              [
                "id" => 7,
                "label" => "Indice chiave",
                "field_id" => "Field id: (KeyIndex)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  0,
                  1,
                  2,
                  3
                ]
              ],
              [
                "id" => 8,
                "label" => "Memorizzazione della cache PMK",
                "field_id" => "Field id: (PMKCacheMode)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  "Abilitato",
                  "Non Attivo"
                ]
              ],
              [
                "id" => 9,
                "label" => "Chiave di Rete/Passphrase",
                "field_id" => "Field id: (keyMaterial)",
                "value" => "",
                "input_type" => "text",
                "description" => "",
                "options" => []
              ],
              [
                "id" => 10,
                "label" => "Chiave di Rete/Passphrase",
                "field_id" => "Field id: (keyMaterial)",
                "value" => "---",
                "input_type" => "multiselect",
                "description" => "",
                "options" => [
                  "---",
                  "Abilitato",
                  "Non Attivo"
                ]
              ],
              [
                "id" => 11,
                "label" => "Chiave di Rete/Passphrase",
                "field_id" => "Field id: (keyMaterial)",
                "value" => "",
                "input_type" => "text",
                "description" => "",
                "options" => []
              ]
            ],
            "form_outputs" => [],
            "options" => []
          ]
        ],
        "form_outputs" => []
      ]
    ];
  }
}
