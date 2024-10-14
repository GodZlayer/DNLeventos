<?php
namespace Database\Seeders;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class InstallationSeeder extends Seeder {
    public function run() {
        $user = User::updateOrCreate(['id' => 1], [
            'id'       => 1,
            'name'     => 'admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);
        $settings = [
            ['name' => 'website_url','value' => 'https://primeweb.wrteam.me/','type'=>'string'],
            ['name' => 'app_bar_title','value' => 'left', 'type'=>'string'],
            ['name' => 'loader_color','value' => '#893899' , 'type'=>'string'],
            ['name' => 'color_code' , 'value' => '#893899' , 'type' => 'string'],
            ['name' => 'pull_to_refresh','value' => 'true', 'type' => 'boolean'],
            ['name' => 'onboarding_screen','value' => 'true', 'type' => 'boolean'],
            ['name' => 'exit_popup_screen','value' => 'true', 'type' => 'boolean'],
            ['name' => 'app_drawer','value' => 'true', 'type' => 'boolean'],
            ['name' => 'show_bottom_navigation','value' => 'true', 'type'=>'string'],
            ['name' => 'style','value' => 'style1', 'type'=>'string'],
            ['name' => 'theme_color','value' => '#7027DB', 'type'=>'string'],
            ['name' => 'backgroundcolor','value' => '#f7f7f7', 'type'=>'string'],
            ['name' => 'admob_app_id_android','value' => 'your_admob_app_id_android', 'type'=>'string'],
            ['name' => 'banner_ad_id_android','value' => 'your_banner_ad_id_android', 'type'=>'string'],
            ['name' => 'interstitial_ad_id_android','value' => 'your_interstitial_ad_id_android', 'type'=>'string'],
            ['name' => 'admob_app_id_ios','value' => 'your_admob_app_id_ios', 'type'=>'string'],
            ['name' => 'banner_ad_id_ios','value' => 'your_banner_ad_id_ios', 'type'=>'string'],
            ['name' => 'interstitial_ad_id_ios','value' => 'your_interstitial_ad_id_ios', 'type'=>'string'],
            ['name' => 'admin_logo','value' => 'logo/logo1.png', 'type'=>'string'],
            ['name' => 'favicon','value' => 'logo/FavIcon.png', 'type'=>'string'],
            ['name' => 'background_image','value' => 'bg/login.jpg', 'type'=>'string'],
            ['name' => 'about_us','value' => 'About Us', 'type'=>'string'],
            ['name' => 'contact_us','value' => 'Contact Us', 'type'=>'string'],
            ['name' => 'terms_and_condition','value' => 'Terms And Condition', 'type'=>'string'],
            ['name' => 'privacy_policy','value' => 'Privacy policy', 'type'=>'string'],
            ['name' => 'android_app_version','value' => 'androidAppVersion', 'type'=>'string'],
            ['name' => 'ios_app_version','value' => 'iosAppVersion', 'type'=>'string'],
            ['name' => 'android_app_link','value' => 'androidAppLink', 'type'=>'string'],
            ['name' => 'ios_app_link','value' => 'iosAppLink', 'type'=>'string'],
            ['name' => 'app_force_update','value' => 'appForceUpdate', 'type'=>'string'],
            ['name' => 'app_maintenance_mode','value' => 'appMaintenanceMode', 'type'=>'string'],
            ['name' => 'admob_ad_status','value' => 'admobAdStatus', 'type'=>'string'],
            ['name' => 'banner_ad_status','value' => 'bannerAdStatus', 'type'=>'string'],
            ['name' => 'interstitial_ad_status','value' => 'interstitialAdStatus', 'type'=>'string'],
            ['name' => 'system_version','value' => '2.0.0', 'type'=>'string'],


            
        ];
        Setting::upsert($settings, ['id'], ['name'], ['value', 'type']);
        Setting::upsert(config('constants.DEFAULT_SETTINGS'), ['name'], ['value', 'type']);
    }
}
