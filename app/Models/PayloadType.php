<?php

namespace App\Models;

class PayloadType
{
  const APPLE_MANAGEMENT = 'APPLE_MANAGEMENT';
  const CONFIGURATION = 'Configuration';
  const SECURITY_MTD = 'SECURITY_MTD';
  const PASSCODE = 'MOBILEDEVICE_PASSWORDPOLICY';
  const PIN_COMPLEXITY = 'PIN_COMPLEXITY';
  const RESTRICTIONS = 'APPLICATIONACCESS';
  const SECURITY_CERTIFICATETRANSPARENCY = 'SECURITY_CERTIFICATETRANSPARENCY';
  const WIFI = 'WIFI_MANAGED';
  const WIFI_MANAGED_WINDOWS = 'WIFI_MANAGED_WINDOWS';
  const GLOBAL_HTTP_PROXY = 'PROXY_HTTP_GLOBAL';
  const WEB_CONTENT_FILTER = 'WEBCONTENT_FILTER';
  const EMAIL = 'MAIL_MANAGED';
  const GOOGLE_ACCOUNT = 'GOOGLE_OAUTH';
  const HOME_SCREEN_LAYOUT = 'HOMESCREENLAYOUT';
  const NOTIFICATIONS = 'NOTIFICATIONSETTINGS';
  const ACTIVE_DIRECTORY_CERTIFICATE = 'ADCERTIFICATE_MANAGED';
  const AIRPLAY = 'AIRPLAY';
  const AIRPLAY_SECURITY = 'AIRPLAY_SECURITY';
  const AIRPRINT = 'AIRPRINT';
  const APP_LOCK = 'APP_LOCK';
  const WEBLOCK = 'WEBLOCK_PAYLOAD';
  const EDUCATION_CONFIGURATION = 'EDUCATION';
  const DOMAINS = 'DOMAINS';
  const WEB_CLIP = 'WEBCLIP_MANAGED';
  const CERTIFICATE_ROOT = 'SECURITY_ROOT';
  const CERTIFICATE_PEM = 'SECURITY_PEM';
  const CERTIFICATE_PKCS1 = 'SECURITY_PKCS1';
  const CERTIFICATE_PKCS12 = 'SECURITY_PKCS12';
  const VPN = 'VPN_MANAGED';
  const PER_APP_VPN = 'VPN_MANAGED_APPLAYER';
  const VPN_ALWAYS = 'VPNALWAYS_PAYLOAD';
  const CALDAV = 'CALDAV_ACCOUNT';
  const CALENDAR = 'SUBSCRIBEDCALENDAR_ACCOUNT';
  const CARDAV = 'CARDDAV_ACCOUNT';
  const CELLULAR = 'CELLULAR';
  const EXCHANGE = 'EAS_ACCOUNT';
  const WALLPAPER = 'WALLPAPER_PAYLOAD';
  const SHARED_DEVICE_CONFIGURATION = 'SHAREDDEVICECONFIGURATION';
  const CONFERENCE_ROOM = 'CONFERENCEROOMDISPLAY';
  const SINGLE_SIGN_ON = 'SSO';
  const EXTENSIBLESSO = 'EXTENSIBLESSO';
  const SECURITY_SCEP = 'SECURITY_SCEP';
  const NETWORK_USAGE = 'NETWORKUSAGERULES';
  const DNS_PROXY = 'DNSPROXY_MANAGED';
  const ANALYTICS = 'ANALYTICS_PAYLOAD';
  const GEOFENCE_LOGS = 'GEOFENCE_LOGS_PAYLOAD';
  const LDAP = 'LDAP_ACCOUNT';
  const POWERMANAGEMENT = 'POWERMANAGEMENT_PAYLOAD';
  const OVERRIDEVIDEOSOURCE = 'OVERRIDEVIDEOSOURCE_PAYLOAD';
  const INPUTVIDEOSETTINGS = 'INPUTVIDEOSETTINGS_PAYLOAD';
  const KPE = 'KPE_PAYLOAD';
  const TV_REMOTE = 'TVREMOTE';
  const FONT_PAYLOAD = 'FONT';
  const GOOGLE_DOMAINS = 'GOOGLE_DOMAINS';
  const DNSSETTINGS_MANAGED = 'DNSSETTINGS_MANAGED';
  const NETWORKPROXY_CSP = 'NETWORKPROXY_CSP';
  const CUSTOM_PROFILE = 'CUSTOM_PROFILE';
  const CERTIFICATE_REVOCATION = 'SECURITY_CERTIFICATEREVOCATION';
  const BITLOCKER_CSP = 'BITLOCKER_CSP';
  const WINDOWS_CONFIG = 'WINDOWS_CONFIG';
  const DEFENDER = 'DEFENDER';
  const WINDOWS_CUSTOM_PAYLOAD = 'WINDOWS_CUSTOM_PAYLOAD';
  const WINDOWS_ADMX_POLICIES = 'WINDOWS_ADMX_POLICIES';
  const OS_SYSTEM_UPDATE_CONFIG = 'OS_SYSTEM_UPDATE_CONFIG';
}