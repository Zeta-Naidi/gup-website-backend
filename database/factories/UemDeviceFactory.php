<?php

namespace Database\Factories;

use App\Models\UemDevice;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UemDevice>
 */
class UemDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = UemDevice::class;

    private function getRandomInterfaces()
    {
        $network_interfaces = [];
        $numberOfInterfaces = rand(1, 10);
        for ($i = 0; $i < $numberOfInterfaces; $i++) {
            $interface_type = $this->faker->randomElement(['wlan', 'lan', 'eth', 'enp', 'dummy', 'rmnet_data', 'tunl', 'lo', 'sit', 'p2p', 'ifb', 'ip6tnl']);
            $interface_number = $this->faker->randomDigit;
            $network_interface_name = "{$interface_type}{$interface_number}";
            $network_interfaces[] = ["name" => $network_interface_name, "type" => $interface_type, "ip" => "{$this->faker->randomElement([$this->faker->ipv4(),$this->faker->ipv6()])}", "mac" => $this->faker->macAddress, "isConnected" => $this->faker->boolean];
        }
        return $network_interfaces;
    }

    private function getRandomNetworkIdentity()
    {
        return ["networkInterfaces" => $this->getRandomInterfaces(), "bluetoothMAC" => $this->faker->macAddress];
    }

    private function getRandomConfiguration()
    {
        return [
            "winAgentInstallPath" => "{$this->faker->randomElement(['storage', 'tmp', '/home', '\/root\/'])}/{$this->faker->word}/{$this->faker->word}",
            "hostName" => $this->faker->word,
            "googleUsernameAccounts" => $this->faker->word
        ];
    }

    private function getRandomDeviceIdentity()
    {
        return [
            "EASDeviceIdentifier" => $this->faker->uuid(),
            "deviceIdAppleTv" => $this->faker->randomDigit(),
        ];
    }

    public function definition(): array
    {
        return [
            'parentDeviceId' => $this->faker->numberBetween(),
            'deviceName' => $this->faker->word,
            'modelName' => $this->faker->word,
            'enrollmentType' => $this->faker->numberBetween(1, 5),
            'macAddress' => $this->faker->macAddress,
            'meid' => $this->faker->regexify('[A-Z0-9]{14}'),
            'osType' => $this->faker->randomElement(['windows', 'macos', 'linux', 'android', 'ios']),
            'osEdition' => $this->faker->word,
            'osVersion' => $this->faker->numberBetween(7, 16),
            'udid' => $this->faker->uuid(),
            'vendorId' => $this->faker->word,
            'osArchitecture' => $this->faker->randomElement(['x86', 'x86_64', 'arm', 'arm64']),
            'abbinationCode' => $this->faker->regexify('[A-Z0-9]{8}'),
            'mdmDeviceId' => $this->faker->numberBetween(),
            'manufacturer' => $this->faker->randomElement(['Aftershock', 'Apple', 'AGB Supreme Technology', 'Alienware', 'Avell', 'Avita', 'Axioo', 'Bitblaze', 'Bmax', 'BOXX Technologies', 'BTO Europe', 'Casper', 'CHUWI', 'Colourful', 'Corsair', 'Clevo', 'CyberPowerPC', 'Daten', 'Dere', 'Digital Storm', 'Durabook', 'Dynabook', 'Eluktronics', 'Eurocom', 'Evoo', 'Falcon Northwest', 'Framework Computer', 'Fujitsu', 'Gateway', 'Geo', 'Star Labs', 'Getac', 'Gigabyte', 'Google', 'Hansung', 'Hasee', 'Honor', 'Huawei', 'Hyundai Technology', 'Illegear', 'Infinitx', 'JOI', 'KUU', 'Lava International', 'LG', 'Machenike', 'MALIBAL', 'Maguay ', 'Medion', 'Micro-Star International (MSi)', 'Microsoft', 'Microtech', 'Monster Notebook ', 'Mouse Computer LuvBook', 'MNT Research', 'Multi', 'NEC', 'Njoy (brand by Dai-Tech )', 'Nokia', 'Obsidian-PC', 'Optima', 'Origin PC', 'OverPowered', 'Panasonic', 'Packard Bell', 'Positivo', 'Primebook', 'Purism', 'Razer', 'Realme', 'Sager Notebook computers (exports Clevo)', 'Samsung', 'Slimbook', 'SKIKK', 'Shenzhen Jumper Technology', 'System76', 'Teclast', 'Thunderobot', 'Tsinghua Tongfang', 'uLBx', 'UMAX', 'VAIO', 'Vant', 'Vastking', 'Velocity Micro', 'VIT', 'Walmart', 'Walton', 'Xiaomi', 'XMG (Schenker Technologies )', 'TUXEDO Computers', 'Xolo', 'Zeuslap', 'Zyrex']),
            'serialNumber' => $this->faker->regexify('[A-Z0-9]{10}'),
            'imei' => $this->faker->regexify('[0-9]{15}'),
            'isDeleted' => $this->faker->boolean,
            'phoneNumber' => $this->faker->phoneNumber,
            'isOnline' => $this->faker->boolean,
            'brand' => $this->faker->word,
            'configuration' => $this->getRandomConfiguration(),
            'deviceIdentity' => $this->getRandomDeviceIdentity(),
            'networkIdentity' => $this->getRandomNetworkIdentity(),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $this->faker->optional()->dateTime,
        ];
    }
}
