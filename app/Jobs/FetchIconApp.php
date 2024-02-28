<?php

namespace App\Jobs;

use DOMDocument;
use DOMXPath;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class FetchIconApp implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private string|null $identifier;
  private string|null $mod;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($identifier, $mod)
  {
    $this->identifier = $identifier;
    $this->mod = $mod;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      if ($this->mod === 'IOS')
        $baseImage64 = $this->fetchIosIcon();
      else if ($this->mod === 'ANDROID')
        $baseImage64 = $this->fetchAndroidIcon();
      else
        $baseImage64 = null;

      DB::table('appsIcons')->insert([
        "identifier" => $this->identifier,
        "iconBase64" => $baseImage64 ?? null,
        "osType" => $this->mod == 'IOS' ? 'ios' : 'android'
      ]);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return null;
    }
  }

  private function fetchAndroidIcon()
  {
    try {
      $url = "https://play.google.com/store/apps/details?id=$this->identifier";

      $client = new \GuzzleHttp\Client();
      $response = $client->get($url);

      $html = $response->getBody()->getContents();

      $dom = new DOMDocument();
      libxml_use_internal_errors(true); // Disable libxml errors and warnings
      $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
      libxml_clear_errors();

      $xpath = new DOMXPath($dom);


      // Perform an XPath query to select elements
      // First template page
      $elements = $xpath->query('//div[@class="Mqg6jb Mhrnjf"]/img[1]/@src');
      $imageUrl = $elements->item(0);
      if (!$imageUrl) {
        //Second template page
        $elements = $xpath->query('//div[@class="qxNhq"]/img/@src');
        $imageUrl = $elements->item(0)->nodeValue;
      } else $imageUrl = $elements->item(0)->nodeValue;

      $imageContent = file_get_contents($imageUrl);

      return base64_encode($imageContent);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return null;
    }
  }

  private function fetchIosIcon()
  {
    try {
      $url = "https://itunes.apple.com/lookup?bundleId=$this->identifier";

      $client = new \GuzzleHttp\Client();

      $response = $client->get($url);

      $html = (string)$response->getBody();
      $responseObject = json_decode($html);
      $imageContent = file_get_contents($responseObject->results[0]->artworkUrl60);

      return base64_encode($imageContent);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return null;
    }
  }
}
