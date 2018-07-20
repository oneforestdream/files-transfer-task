<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function send(Request $request)
    {
        $redirectUrl = AppHelper::getRandomAppUrl();
        if (!$redirectUrl)
            return response()->json(['code' => 500, 'message' => "No connected apps."]);

        if (!$request->hasFile('fileName'))
            return response()->json(['code' => 422, 'message' => "File is empty."]);

        $file = $request->file('fileName');
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientMimeType();

        if ($fileType != 'text/plain')
            return response()->json(['code' => 422, 'message' => "File must be of type 'text/plain'."]);

        if (AppHelper::checkIfFileExists($fileName))
            return response()->json(['code' => 409, 'message' => "File with this name already exists."]);

        $redirectFullPath = $redirectUrl . '/api/files/save';
        $response = AppHelper::transferFile($redirectFullPath, $file);
        $responseResult = json_decode($response);

        if ($responseResult->code == 200)
            LogHelper::writeLog($redirectUrl, $fileName);

        return $response;
    }

    public function save(Request $request)
    {
        $appId = AppHelper::getAppId();
        $fileName = $request->get('fileName');
        $fileContent = $request->get('fileContent');
        $filePath = '/' . $appId . '/' . $fileName;

        try {
            Storage::disk('files')->put($filePath, $fileContent);
            return response()->json(['code' => 200, 'message' => "Success."]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => "File uploading failed."]);
        }
    }

    public function get(Request $request, $fileName)
    {
        return LogHelper::checkAccessToFile($fileName);
    }
}