<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class AppHelper
{
    const DEFAULT_APP_PORT = '8000';
    const JSON_CONFIG_FILENAME = 'app-info.json';

    public static function initAppConfig()
    {
        $url = self::getAppUrl();
        $appId = md5(microtime());
        $json = file_get_contents(base_path(self::JSON_CONFIG_FILENAME));
        $jsonData = json_decode($json, true);

        if (!array_key_exists($url, $jsonData)) {
            $appInfoData = [
                'APP_ID' => $appId,
                'ENCRYPTION_KEY' => SSLHelper::generateKey(),
            ];
            $jsonData[$url] = $appInfoData;

            if (!Storage::disk('files')->has($appId))
                Storage::disk('files')->makeDirectory($appId);
        } else {
            $appInfoData = $jsonData[$url];
        }
        config(['app_info' => $appInfoData]);
        config(['apps_info' => $jsonData]);
        file_put_contents(base_path(self::JSON_CONFIG_FILENAME), json_encode($jsonData));
    }

    public static function getAppUrl()
    {
        $args = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;
        if ($args)
            $port = explode('=', $args)[1];
        else
            $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : self::DEFAULT_APP_PORT;

        return env('APP_URL') . ':' . $port;
    }

    public static function getAppId($url = null)
    {
        if (!$url)
            $url = self::getAppUrl();

        $data = config('apps_info');
        return isset($data[$url]) ? $data[$url]['APP_ID'] : null;
    }

    public static function getRandomAppUrl()
    {
        $currentAppUrl = self::getAppUrl();
        $data = config('apps_info');

        $urls = array_keys($data);
        $position = array_search($currentAppUrl, $urls);
        if ($position != -1)
            unset($urls[$position]);

        return !empty($urls) ? array_random($urls) : null;
    }

    public static function transferFile($url, $file)
    {
        $content = file_get_contents($file);
        $encryptionKey = config('app_info')['ENCRYPTION_KEY'];
        $encryptedContent = SSLHelper::encrypt($content, $encryptionKey);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data;"]);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, ['fileContent' => $encryptedContent, 'fileName' => $file->getClientOriginalName()]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function checkIfFileExists($fileName)
    {
        $allFiles = Storage::disk('files')->allFiles();
        foreach ($allFiles as $file) {
            list($dir, $name) = explode('/', $file);
            if($fileName == $name)
                return true;
        }
        return false;
    }
}