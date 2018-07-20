<?php

namespace App\Helpers;

use App\Facades\CustomLog;
use Illuminate\Support\Facades\Storage;

class LogHelper
{
    const LOG_FILE_PATH = 'logs/files-transfer.log';

    const LOG_FILE_NAME = 'FileName';
    const LOG_APP_FROM = 'AppFrom';
    const LOG_APP_TO = 'AppTo';

    public static function checkAccessToFile($fileName)
    {
        $appId = AppHelper::getAppId();
        $logFileContent = file(storage_path(self::LOG_FILE_PATH));

        try {
            foreach ($logFileContent as $line) {
                $result = self::getJsonPart($line);

                if ($result[self::LOG_FILE_NAME] != $fileName)
                    continue;
                $filePath = "/" . $result[self::LOG_APP_TO] . "/" . $fileName;
                $clientFileContent = file_get_contents(storage_path('files' . $filePath));

                if ($appId == $result[self::LOG_APP_FROM]) {
                    $encryptionKey = config('app_info')['ENCRYPTION_KEY'];
                    $decryptedFileContent = SSLHelper::decrypt($clientFileContent, $encryptionKey);
                    return response()->json(['code' => 200, 'content' => $decryptedFileContent]);
                } else if ($appId == $result[self::LOG_APP_TO]) {
                    return response()->json(['code' => 200, 'content' => $clientFileContent]);
                }
            }
            return response()->json(['code' => 404, 'message' => 'File not found.']);
        } catch (\Exception $e) {
            return response()->json(['code' => 404, 'message' => 'File not found.']);
        }
    }

    private static function getJsonPart($line)
    {
        $start = strpos($line, '{');
        $end = strpos($line, '}');
        $json = substr($line, $start, $end - $start + 1);
        return json_decode($json, true);
    }

    public static function writeLog($redirectUrl, $fileName)
    {
        $appId = AppHelper::getAppId();
        $receiverId = AppHelper::getAppId($redirectUrl);
        CustomLog::info("{\"" . self::LOG_FILE_NAME . "\": \"$fileName\", \"" . self::LOG_APP_FROM . "\": \"" . $appId . "\", \"" . self::LOG_APP_TO . "\": \"$receiverId\"}", 'files-transfer');
    }

}