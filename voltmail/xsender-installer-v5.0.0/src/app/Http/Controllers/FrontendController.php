<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Service\Admin\Contact\ContactService;
use App\Service\Admin\Core\FileService;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    public FileService $fileService;
    public function __construct(FileService $fileService) { 

        $this->fileService = $fileService;
    }
    
    public function demoImportFilesms() {

        $path    = filePath()['demo']['path'].'/demo.csv';
        $title   = 'demo.csv';
        $headers = [
            'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length'            => filesize($path),
            'Cache-Control'             => 'must-revalidate',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition'       => 'attachment; filename='.$title
        ];
        return response()->download($path, 'demo.csv', $headers);
    }

    public function demoImportFile() {

        $path    = filePath()['demo']['path_email'].'/demo.csv';
        $title   = 'demo.csv';
        $headers = [
            'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length'            => filesize($path),
            'Cache-Control'             => 'must-revalidate',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition'       => 'attachment; filename='.$title
        ];
        return response()->download($path, 'demo.csv', $headers);
    }

    public function demoFileDownloader($extension, $type) {

        try {

            $exclude_headers  = $type == "sms" ? ["email_contact", "whatsapp_contact", "last_name"] : ($type == "email" ? ["sms_contact", "whatsapp_contact", "last_name"] : ["sms_contact", "email_contact", "last_name"]);
            return $this->fileService->generateContactDemo(type: $extension, condition_exlude: $exclude_headers, allow_attribute: false, single_channel_demo: true);

        } catch (\Exception $e) {

            $notify[] = ['error', 'Something went wrong, File could not generate'];
            return back()->withNotify($notify);
        }
    }

    public function defaultImageCreate($size=null) {
        if ($size === '400x400') {
            $avatarPath = base_path('../assets/file/images/user/profile/user_1_avatar.webp');
            if (file_exists($avatarPath)) {
                header('Content-Type: image/webp');
                readfile($avatarPath);
                exit;
            }
        }

        $parts = explode('x', (string)$size);
        $width = max((int)($parts[0] ?? 400), 10);
        $height = max((int)($parts[1] ?? 400), 10);

        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 248, 250, 252); // #f8fafc slate-50
        $borderColor = imagecolorallocate($image, 226, 232, 240); // #e2e8f0 slate-200
        imagefill($image, 0, 0, $bgColor);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

        header('Content-Type: image/jpeg');
        imagejpeg($image, null, 90);
        imagedestroy($image);
        exit;
    }

    public function apiDocumentation() {

        $title = "API Documentation";
        $layout = null;
        if (Auth::guard('admin')->user()) {
            $layout = "admin.layouts.app";
        }
        if (Auth::user()) {
            $layout = "user.layouts.app";
        }
        return view('api.index', compact('title','layout'));
    }
}
