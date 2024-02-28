<?php

namespace Database\Factories;

use App\Models\UemDeviceDetails;
use Illuminate\Database\Eloquent\Factories\Factory;

enum SensorTypeEnum: string
{
    case CPU = 'CPU';
    case BATTERY = 'BATTERY';
    case SKIN = 'SKIN';
    case GPU = 'GPU';
    case JOKE = 'REACTOR';
}

enum LocalesTypeEnum: string
{
    case IT = 'IT_it';
    case EN = 'EN_en';
    case US = 'EN_us';
    case FR = 'FR_fr';
    case DE = 'DE_de';
}

class UemDeviceDetailsFactory extends Factory
{
    protected $model = UemDeviceDetails::class;
    private function getRandomInstalledApps()
    {
        $installedApps = [];
        $numberOfInstalledApps = rand(1, 20);
        for ($i = 0; $i < $numberOfInstalledApps; $i++) {
            $installedApps[] = [
                "AdHocCodeSigned" => $this->faker->boolean,
                "AppStoreVendable" => $this->faker->boolean,
                "BetaApp" => $this->faker->boolean,
                "BundleSize" => $this->faker->numberBetween(50000000, 500000000),
                "DeviceBasedVPP" => $this->faker->boolean,
                "DynamicSize" => $this->faker->numberBetween(10000, 100000),
                "ExternalVersionIdentifier" => $this->faker->numberBetween(1024, 999999999),
                "HasUpdateAvailable" => $this->faker->boolean,
                "Identifier" => "com.{$this->faker->word}.{$this->faker->word}",
                "Installing" => $this->faker->boolean,
                "IsValidated" => $this->faker->boolean,
                "Name" => $this->faker->word,
                "ShortVersion" => "{$this->faker->randomDigit()}.{$this->faker->randomDigit()}.{$this->faker->randomDigit()}",
                "Version" => $this->faker->randomNumber(2, false)
            ];
        }
        return $installedApps;
    }

    public function definition()
    {
        return [
            // 'osVersion' => $this->faker->numberBetween(0, 15),
            // 'parentDeviceId' => null,
            'miscellaneous' => json_encode([
                'ipAddress' => $this->faker->ipv4(),
                'wifiLinkSpeed' => $this->faker->numberBetween(0.0, 1024.0),
                'wifiBSSID' => $this->faker->word,
            ]),
            'hardwareDetails' => json_encode([
                'batteryLevel' => $this->faker->numberBetween(0, 100),
                'ram' => $this->faker->randomElement([2048, 4096, 8192]), // Example values
                'isBatteryCharging' => $this->faker->boolean(),
                'cpuArchitecture' => $this->faker->randomElement(['x86', 'x86_64', 'arm', 'arm64']),
                'tempSensors' => $this->faker->randomElement(SensorTypeEnum::cases()),
                'externalCapacity' => $this->faker->numberBetween(0.0, 107.0),
            ]),
            'technicalDetails' => json_encode([
                'timeZone' => $this->faker->timezone(),
                'freeDeviceSpace' => $this->faker->numberBetween(0, 100),
                'locales' => $this->faker->randomElement(LocalesTypeEnum::cases()),
            ]),
            'restrictions' => json_encode([
                'allowScreenShot' =>  $this->faker->boolean(),
                'allowAppInstallation' =>  $this->faker->boolean(),
                'allowAppRemoval' =>  $this->faker->boolean(),
                'allowRemoveUser' =>  $this->faker->boolean(),
                'allowAccountModification' =>  $this->faker->boolean(),
                'allowSwitchUser' =>  $this->faker->boolean(),
                'forceGuestUserOnly' =>  $this->faker->boolean(),
                'forceLoginScreen' =>  $this->faker->boolean(),
                'forceGoogleAccountOnCOPE' =>  $this->faker->boolean(),
                'allowGoogleAccountScreenOnEnroll' =>  $this->faker->boolean(),
                'allowLocalUsersLoginScreen' =>  $this->faker->boolean(),
                'allowSSOLoginScreen' =>  $this->faker->boolean(),
                'allowGuestLoginScreen' =>  $this->faker->boolean(),
                'allowEraseContentAndSettings' =>  $this->faker->boolean(),
                'allowCamera' =>  $this->faker->boolean(),
                'allowExternalMedia' =>  $this->faker->boolean(),
                'allowDebug' =>  $this->faker->boolean(),
                'ratingApps' =>  $this->faker->numberBetween(0, 1000),
                'allowBluetoothModification' =>  $this->faker->boolean(),
                'allowWallpaperModification' =>  $this->faker->boolean(),
                'allowAppsControl' =>  $this->faker->boolean(),
                'allowUnknownSources' =>  $this->faker->boolean(),
                'allowUsbFileTransfer' =>  $this->faker->boolean(),
                'allowOutgoingBeam' =>  $this->faker->boolean(),
                'forceWiFiWhitelisting' =>  $this->faker->boolean(),
                'allowUsbMassStorage' =>  $this->faker->boolean(),
                'allowTethering' =>  $this->faker->boolean(),
                'wifiSleepPolicy' =>  $this->faker->randomElement([0, 1, 2, 3, 4]),
            ]),
            'locationDetails' => json_encode([
                'locationToken' => $this->faker->regexify('[a-z0-9]{64}'),
                'lastLongitude' => $this->faker->longitude(-180, 180),
                'lastAltitude' => $this->faker->randomFloat(1, -4000.0, 8000.0),
                'lastSpeed' => $this->faker->randomFloat(0.0, 300.0),
                'lastLatitude' => $this->faker->latitude(-90, 90),
            ]),
            'networkDetails' => json_encode([
                'networkInterfaces' => $this->faker->macAddress(),
                'bluetoothMAC' => $this->faker->macAddress(),
                "cellularNetworkType" => 'NETWORK_TYPE_LTE',
                "simState" => 'SIM_STATE_READY',
                "simOperatorName" => 'WINDTRE',
                "wifiIpAddress" => $this->faker->ipv4(),
            ]),
            'accountDetails' => json_encode([
                'iTunesStoreAccountHash' => $this->faker->sha1(),
                'googleUsernameAccounts' => $this->faker->word,
                'gplayManagedAccountStatus' => $this->faker->randomElement(['active', 'suspended', 'deleted', 'enrolled']),
            ]),
            'osDetails' => json_encode([
                'keepOsUpdated' => $this->faker->boolean(),
                'systemIntegrity' => $this->faker->boolean(),
                'osVersion' => $this->faker->numberBetween(0, 15),
            ]),
            'securityDetails' => json_encode([
                'securityPatchDate' => $this->faker->date(),
                'unlockToken' => $this->faker->sha1(),
            ]),
            'androidConfigs' => json_encode([
                'isAndroidOem' => $this->faker->boolean(),
                'hasAndroidPlayServices' => $this->faker->boolean(),
            ]),
            'appleConfigs' => json_encode([
                'managedAppleID' => $this->faker->numberBetween(),
                'deviceIdAppleTv' => $this->faker->numberBetween(),
                'inAppleDeploymentProgram' => $this->faker->boolean(),
            ]),
            'installedApps' => json_encode($this->getRandomInstalledApps()),
        ];
    }
}
