<?php

namespace App\Security;

use Illuminate\Support\Facades\Log;

class SecurityPostureDeviceHandler
{
  private $configuration = [
    "criticKeys" => [
      "INTEGRITY" => "NOTIFICATION_INTEGRITY",
      "JAILBREAK_ROOT" => "NOTIFICATION_JAILBREAK_ROOT",
      "MALWARE_SCAN" => "NOTIFICATION_MALWARE_SCAN",
      "PROFILES" => "NOTIFICATION_PROFILES",  // IMPORTANTE (solo ios), profile not validated
      "NOT_VALIDATED_APP" => "NOTIFICATION_NOT_VALIDATED_APP",
      //ANTIVIRUS_PRESENCE (OPTIONAL FOR NOW)
    ],
    "highKeys" => [
      //------------------------------IMPORTANT KEYS------------------------------------------------------------------------------
      "DEBUG" => "NOTIFICATION_DEBUG", //attiva la modalità debug
      "UNKNOWN_SOURCES" => "NOTIFICATION_UNKNOWN_SOURCES", //Puoi installare apk
      //------------------------------NORMAL KEYS------------------------------------------------------------------------------
      "PASSCODE" => "NOTIFICATION_PASSCODE", // No password
      "RESTRICTIONS" => "NOTIFICATION_RESTRICTIONS",  // serie di restrizioni che dovrebbero essere settate ma non lo sono
      "CLOUD_BACKUP" => "NOTIFICATION_CLOUD_BACKUP", //attivo i dati in cloud
      "ENCRYPTION" => "NOTIFICATION_ENCRYPTION", //disco non è criptato
    ],
    "mediumKeys" => [
      "AV_DB_OUTDATED" => "NOTIFICATION_AV_DB_OUTDATED",  // database delle firme è aggiornato
      "AV_SCAN_OUTDATED" => "NOTIFICATION_AV_SCAN_OUTDATED", // scan non fatto da un pò
      "ADHOC_APP" => "NOTIFICATION_ADHOC_APP", // app di cui si consiglia la rimozione, più di uno
    ],
    "lowKeys" => [
      "LOCAL_URL_IP" => "NOTIFICATION_LOCAL_URL_IP", // più di uno
      "WEB_CONTENT_CATEGORY_MATCH" => "NOTIFICATION_WEB_CONTENT_CATEGORY_MATCH",
      "HOST_BLOCKED" => "NOTIFICATION_HOST_BLOCKED",
      "IP" => "NOTIFICATION_IP",
      "DOMAIN" => "NOTIFICATION_DOMAIN",
      "URL" => "NOTIFICATION_URL",
    ],

    "cveKey" => "NOTIFICATION_CVE",

    "androidScorePermissions" => [
      "ACCESS_FINE_LOCATION" => 24,   // This permission allows access to the precise location of the device using GPS. It is crucial for location-based services and navigation apps.
      "ACCESS_COARSE_LOCATION" => 23, // This permission provides access to the approximate location of the device using network-based methods.
      "CAMERA" => 22,                 // This permission grants access to the device's camera, which is important for photography, video recording, and augmented reality apps.
      "RECORD_AUDIO" => 21,           // This permission enables access to the device's microphone, which is necessary for audio recording, voice recognition, and voice communication apps.
      "READ_CONTACTS" => 20,          // This permission allows access to the user's contact list, which is often required for contact synchronization and communication apps.
      "WRITE_CONTACTS" => 19,         // This permission allows modifying or adding contacts in the user's contact list.
      "CALL_PHONE" => 18,             // This permission allows making phone calls from the app.
      "GET_ACCOUNTS" => 17,           // This permission allows accessing the user's accounts configured on the device.
      "SEND_SMS" => 16,               // This permission allows sending SMS messages, which is important for messaging and communication apps.
      "READ_PHONE_STATE" => 15,       // This permission provides access to information about the device's telephony status, such as network details and call state.
      "WRITE_CALL_LOG" => 14,         // This permission allows modifying or adding entries in the call history.
      "READ_CALL_LOG" => 13,          // This permission enables access to the user's call history.
      "RECEIVE_SMS" => 12,            // This permission enables the app to receive incoming SMS messages.
      "READ_SMS" => 11,               // This permission allows access to the user's SMS messages.
      "USE_SIP" => 10,                // This permission grants access to Session Initiation Protocol (SIP) services for Internet telephony.
      "ADD_VOICEMAIL" => 9,           // This permission grants access to manage voicemail messages.
      "PROCESS_OUTGOING_CALLS" => 8,  // This permission provides access to process outgoing calls, such as intercepting and modifying the number dialed.
      "RECEIVE_WAP_PUSH" => 7,        // This permission enables the app to receive WAP push messages.
      "RECEIVE_MMS" => 6,             // This permission allows the app to receive and process multimedia messages (MMS).
      "WRITE_CALENDAR" => 5,          // This permission allows modifying or adding calendar events in the user's calendar.
      "READ_CALENDAR" => 4,           // This permission grants access to the user's calendar events, enabling apps to synchronize and display calendar data.
      "READ_EXTERNAL_STORAGE" => 3,   // This permission grants read access to external storage (SD card, etc.).
      "WRITE_EXTERNAL_STORAGE" => 2,  // This permission allows writing to external storage.
      "BODY_SENSORS" => 1,            // This permission allows access to data from sensors on the device, such as heart rate monitors or accelerometers.
    ],

    "appsAndroidTotalPossibleScore" => 300 //sum of 24 + 23 + 22 + .. + 1, CHANGE IF ADDING KEYS
  ];

  public function __construct(array $configuration = [])
  {
    if (!empty($configuration))
      $this->configuration = $configuration;
  }

  public function calcPostureDevice2(array $events, callable $appsFetcher, string $serialNumber): array
  {
    //events should be grouped by keys
    //CRITIC SECTION
    $vulnerabilityScore = 0;
    foreach ($this->configuration["criticKeys"] as $criticKey) {
      if (isset($events[$criticKey])) {
        return ["score" => 0];
      }
    }
    //HIGH SECTION
    $counterHighImportantKeys = 0; // 2 keys
    $counterHighNormalKeys = 0; // 4 keys
    foreach ($this->configuration["highKeys"] as $highKey) {
      if ($highKey == $this->configuration['highKeys']['DEBUG'] || $highKey == $this->configuration['highKeys']['UNKNOWN_SOURCES']) {
        if (isset($events[$highKey]))
          $counterHighImportantKeys++;
      } else if (isset($events[$highKey]))
        $counterHighNormalKeys++;
    }
    if ($counterHighImportantKeys > 1) {
      return ["score" => 0];
    } else
      $vulnerabilityScore = 50 * $counterHighImportantKeys + 12.5 * $counterHighNormalKeys;

    //Log::info("score prima di chiavi medie: $vulnerabilityScore");
    //MEDIUM SECTION
    $counterMediumKeys = 0;
    foreach ($this->configuration["mediumKeys"] as $mediumKey) {
      if (isset($events[$mediumKey]))
        $counterMediumKeys++;
    }
    $vulnerabilityScore += $counterMediumKeys * 10;
    //ESCAPE CASE
    if ($vulnerabilityScore >= 100) {
      return ["score" => 0];
    }
    //LOW SECTION
    $counterLowKeys = 0;
    foreach ($this->configuration["lowKeys"] as $lowKey) {
      if (isset($events[$lowKey]))
        $counterLowKeys++;
    }
    $vulnerabilityScore += $counterLowKeys * 5;
    //ESCAPE CASE
    if ($vulnerabilityScore >= 100) {
      return ["score" => 0];
    }

    //CVE SECTION 0.7 + 0.3 numero cve e max criticality level:  30/60 e tre livelli di score
    if (isset($events[$this->configuration['cveKey']])) {
      $cveEvents = $events[$this->configuration['cveKey']];
      $maxPossibleCveScore = 50; //To recheck in case
      $scoreNum = 0;
      $scoreCriticality = 0;
      switch ($cveEvents->num) {
        case $cveEvents->num > 0 && $cveEvents->num < 30:
          $scoreNum = 0.33;
          break;
        case $cveEvents->num >= 30 && $cveEvents->num < 60 :
          $scoreNum = 0.66;
          break;
        case $cveEvents->num > 60:
          $scoreNum = 1;
          break;
      }
      switch ($cveEvents->maxCriticality) {
        case 'low':
          $scoreCriticality = 0.25;
          break;
        case 'medium':
          $scoreCriticality = 0.50;
          break;
        case 'high':
          $scoreCriticality = 0.75;
          break;
        case 'critic':
          $scoreCriticality = 1;
          break;
      }

      $cveScore = (20 * $scoreNum) + (30 * $scoreCriticality);
      $vulnerabilityScore += $cveScore;
      //ESCAPE CASE
      if ($vulnerabilityScore >= 100)
        return ["score" => 0];
    }
    //APPS SECTION 30/60 livelli di score. vulnerability score still under 100
    $apps = $appsFetcher($serialNumber); // in the appsFetcher it should already have permissions calculated
    $appsScore = $this->_calcSecurityPostureAppsScore($apps);
    $vulnerabilityScore += $appsScore;
    //Log::info("alla fine con score: $vulnerabilityScore e cveScore: $cveScore e appScore: $appsScore");
    return [
      "score" => number_format(100 - $vulnerabilityScore, 2),
      /*"countEvents" => (function () use ($events) {
        $counterCriticality = ["low" => 0, "medium" => 0, "high" => 0, "critic" => 0];
        foreach ($events as $event)
      })(),*/
    ];
  }

  /**
   * Used to calculate permissions app score and active list of permissions
   * @param array $permissions is an array of array, every element is a sub-group
   * @return array score from 0 to 100, less is worse, and list of active permissions
   */
  public function calcAppPermissionScore(array $permissions)
  {
    if (count($permissions) == 0) return [
      'score' => 100,
      'permissionsActiveList' => []
    ];
    $totalPossiblePermissions = 0;
    $permissionsNotActiveCount = 0;
    $permissionsActiveList = [];
    foreach ($permissions as $subGroupPermissions) {
      if (is_array($subGroupPermissions)) {
        $totalPossiblePermissions += count($subGroupPermissions);
        $permissionsActiveSubGroup = array_filter($subGroupPermissions, fn($el) => $el >= 0);
        foreach ($permissionsActiveSubGroup as $key => $value)
          $permissionsActiveList [] = $key;
        $permissionsNotActiveCount += count(array_filter($subGroupPermissions, fn($el) => $el < 0));
      }
    }
    return [
      'score' => $this->_calcAppsPermissionsScore($permissionsNotActiveCount, $totalPossiblePermissions, $permissionsActiveList),
      'permissionsActiveList' => $permissionsActiveList
    ];
  }

  private function _calcAppsPermissionsScore(int $permissionsNotActiveCount, int $totalPossiblePermissions, array $permissionsActive, string $osType = 'android')
  {
    if ($osType == 'android') {
      $scorePermission = 0;
      foreach ($permissionsActive as $permission) {
        $scorePermission += $this->configuration['androidScorePermissions'][$permission] ?? 0;
      }
      return (
          ($permissionsNotActiveCount / (!empty($totalPossiblePermissions) ? $totalPossiblePermissions : 1) ) * 0.3 +
          (1 - $scorePermission / $this->configuration['appsAndroidTotalPossibleScore']) * 0.7
        ) * 100;
    } else
      return 100;
  }

  /**
   * @param array $apps
   * @return float|int
   */
  private function _calcSecurityPostureAppsScore(array $apps): float|int
  {
    //APPS SECTION 30/60 livelli di score. 50% number of apps, 50% average permissions score, max value 30
    $score = 0;
    $numOfAppsConsidered = 0;
    $calcScoreNumberOfApps = function (int $numberOfApps) {
      switch ($numberOfApps) {
        case $numberOfApps < 30:
          return 0.33;
        case $numberOfApps >= 30 && $numberOfApps < 60:
          return 0.66;
        case $numberOfApps > 60:
          return 1;
      }
    };
    foreach ($apps as $app) {
      $score += isset($app['permissionsScore']) ? $app['permissionsScore']['score'] : 0;
      if (isset($app['permissionsScore']))
        $numOfAppsConsidered++;
    }
    return ($numOfAppsConsidered != 0) ?
      (1 - ($score / (100 * $numOfAppsConsidered))) * 15 + $calcScoreNumberOfApps(count($apps)) * 15:
      $calcScoreNumberOfApps(count($apps)) * 15;
  }


  /*  private static function _calcEventsIncreasing(array $lastWeeks, array $thisWeek)
    {
      $counter = 0;
      foreach ($lastWeeks as $oldWeek) {
        if ($thisWeek['total'] <= $oldWeek['total'])
          $counter++;
      }
      return $counter / 9;
    }*/

  /*public static function calcPostureDevice($eventsLastMonth, $apps)
  {

    //$areEventsIncreasingWeight = 0.1;
    $appsPermissionsWeight = 0.2;

    //THIS WEEK
    //WEIGHTS
    $hasHighEventsInLastWeekWeight = 0.25;
    $hasMediumEventsInLastWeekWeight = 0.1;
    //SCORES
    $hasCriticEventsInLastWeekScore = 0;
    $hasHighEventsInLastWeekScore = 0;
    $hasMediumEventsInLastWeekScore = 0;

    //LAST WEEKS
    //WEIGHTS
    $hasCriticEventsInLastWeeksWeight = 0.25;
    $hasHighEventsInLastWeeksWeight = 0.15;
    $hasMediumEventsInLastWeeksWeight = 0.05;
    //SCORES
    $hasCriticEventsInLastWeeksScore = 0;
    $hasHighEventsInLastWeeksScore = 0;
    $hasMediumEventsInLastWeeksScore = 0;

    $lastWeeks = [];
    $thisWeek = null;
    foreach ($eventsLastMonth as $key => $week) {
      if ($key == 'firstWeek') {
        $thisWeek = $week;
        $hasCriticEventsInLastWeekScore = (integer)isset($week['critic']);
        $hasHighEventsInLastWeekScore = (integer)isset($week['high']);
        $hasMediumEventsInLastWeekScore = (integer)isset($week['medium']);
      } else if ($key != "doesntCount") {
        $lastWeeks[] = $week;
        if (!$hasCriticEventsInLastWeeksScore && isset($week['critic']))
          $hasCriticEventsInLastWeeksScore = 1;
        if (!$hasHighEventsInLastWeeksScore && isset($week['high']))
          $hasHighEventsInLastWeeksScore = 1;
      } else continue;
    }
    if ($hasCriticEventsInLastWeekScore) {
      // Critic event in this week is TOO dangerous
      return [
        "score" => 0,
        "thisWeek" => $thisWeek ?? ['total' => 0],
        "lastWeeks" => $lastWeeks ?? ['total' => 0],
      ];
    }
    $scoreVulnerability =
      //self::_calcEventsIncreasing($lastThreeWeeks ?? ['total' => 0], $thisWeek ?? ['total' => 0]) * $areEventsIncreasingWeight +
      self::_calcAppsPermissionAverage($apps) * $appsPermissionsWeight +
      $hasHighEventsInLastWeekScore * $hasHighEventsInLastWeekWeight +
      $hasMediumEventsInLastWeekScore * $hasMediumEventsInLastWeekWeight +
      $hasCriticEventsInLastWeeksScore * $hasCriticEventsInLastWeeksWeight +
      $hasHighEventsInLastWeeksScore * $hasHighEventsInLastWeeksWeight +
      $hasMediumEventsInLastWeeksScore + $hasMediumEventsInLastWeeksWeight;
    return [
      "score" => (1 - $scoreVulnerability),//security score = opposite of vulnerability score
      "thisWeek" => $thisWeek ?? ['total' => 0],
      "lastWeeks" => $lastWeeks,
    ];
  }*/
}
