<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;

class UpdateController extends Controller
{
    public function version()
    {
        return view('updater.version');
    }

    public function recurse_copy($src, $dst)
    {

        $dir = opendir(base_path($src));
        @mkdir(base_path($dst));
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir(base_path($src) . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy(base_path($src) . '/' . $file, base_path($dst) . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function upversion(Request $request)
    {

        $assets = array(
            ['path' => 'app', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'config', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'database/migrations', 'type' => 'folder', 'action' => 'add'],
            ['path' => 'resources/views', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'routes/web.php', 'type' => 'file', 'action' => 'replace'],
            ['path' => 'routes/admin.php', 'type' => 'file', 'action' => 'replace'],
            ['path' => 'composer.json', 'type' => 'file', 'action' => 'replace'],
            ['path' => 'composer.lock', 'type' => 'file', 'action' => 'replace'],
            ['path' => 'version.json', 'type' => 'file', 'action' => 'replace']
        );

        foreach ($assets as $key => $asset) {
            // if updater need to replace files / folder (with/without content)
            if ($asset['action'] == 'replace') {
                if ($asset['type'] == 'file') {
                    copy(base_path('public/updater/' . $asset["path"]), base_path($asset["path"]));
                }
                if ($asset['type'] == 'folder') {
                    $this->delete_directory($asset["path"]);
                    $this->recurse_copy('public/updater/' . $asset["path"], $asset["path"]);
                }
            }
            // if updater need to add files / folder (with/without content)
            elseif ($asset['action'] == 'add') {
                if ($asset['type'] == 'folder') {
                    // @mkdir($asset["path"] . '/', 0775, true);
                    $this->recurse_copy('public/updater/' . $asset["path"], $asset["path"]);
                }
            }
        }

        Artisan::call('config:clear');
        // run migration files
        Artisan::call('migrate');


        \Session::flash('success', 'Updated successfully');
        return redirect('updater/success.php');
    }

    function delete_directory($dirname)
    {
        $dir_handle = null;
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);

        if (!$dir_handle)
            return false;
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file))
                    unlink($dirname . "/" . $file);
                else
                    $this->delete_directory($dirname . '/' . $file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

    public function redirectToWebsite(Request $request)
    {
        $arr = ['WEBSITE_HOST' => $request->website_host];
        setEnvironmentValue($arr);
        \Artisan::call('config:clear');

        return redirect()->route('front.index');
    }
}
