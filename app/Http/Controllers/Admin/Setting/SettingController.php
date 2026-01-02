<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use File;

class SettingController extends Controller
{
  public function setting()
  {
    $settings = Setting::get();
    return view('admin.pages.edit_setting', compact('settings'));
  }

  public function save(Request $request)
  {
    // return $request->all();
    foreach ($request->except('_token') as $index => $value) {
      $setting = Setting::where('key', $index)->first();

      //   return $setting->type;
      if ($setting->type == 'file') {
        if ($value) {
          $this->delete_previous_image($setting->value);
        }
        $setting->value = $this->upload_image($value, "settings");
      } else {
        $setting->value = $value;
      }
      $setting->save();
    }

    return redirect()->back()->with("success", "Settings has been updated");
  }
  // setting_value('logo_with_text')
  // delete previous image
  public function delete_previous_image($pre_file)
  {
    $path = str_replace(url('/') . '/', "", $pre_file);
    if (\File::exists($path)) {
      \File::delete($path);
    }
  }

  public function upload_image($file, $path = '',  $name = null)
  {
    $ext = $file->getClientOriginalExtension();
    $filename = time() . '.' . $ext;
    $file->move($path, $filename);
    $upload_path = $path . '/' . $filename;
    return url($upload_path);
  }

}
