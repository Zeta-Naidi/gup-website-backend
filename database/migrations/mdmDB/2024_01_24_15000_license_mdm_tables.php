<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EnrollmentCode;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('enrollmentCodes', function (Blueprint $table) {
      $table->id();
      $table->string('type', 64);
      $table->longtext('value');
    });

    Schema::create('enrollmentCodesHistory', function (Blueprint $table) {
        $table->id();
        $table->string('type', 64);
        $table->longtext('value');
    });

    $enrollmentCodes = [
      [
        'id' => 1,
        'type' => 'standard',
        'value' => '{SCHOOL_CODE}#{LICENSE_ID}-{unique code}',
      ],
        [
            'id' => 2,
            'type' => 'AndroidEnterpriseEnroll',
            'value' => '{
        "android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME":"eu.chimpa.mdmagent/eu.chimpa.mdmagent.receiver.ChimpaAgentDeviceAdminReceiver",
        "android.app.extra.PROVISIONING_DEVICE_ADMIN_SIGNATURE_CHECKSUM":"Fli_GhKKKv9dHWAHxZsapDUpF7CS_7yHzeTuIOEU-Eg",
        "android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION":"https://play.google.com/managed/downloadManagingApp?identifier=chimpa",
        "android.app.extra.EXTRA_PROVISIONING_KEEP_SCREEN_ON":"true",
        "android.app.extra.PROVISIONING_LOCALE":"it_it",
        "android.app.extra.PROVISIONING_SKIP_EDUCATION_SCREENS":"false",
        "android.app.extra.PROVISIONING_SKIP_USER_CONSENT":"false",
        "android.app.extra.PROVISIONING_LEAVE_ALL_SYSTEM_APPS_ENABLED":"false",
        "android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE":{
            "chimpa_activationCode":"DEMOXN2#106-DF7D-BA32",
            "provisionType":0
        }
    }',
        ],
    ];

    foreach ($enrollmentCodes as $data) {
      EnrollmentCode::create($data);
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('enrollmentCodes');
    //
  }
};
