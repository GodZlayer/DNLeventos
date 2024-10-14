@php
$admin_logo = getadminSettings('admin_logo');
$favicon = getadminSettings('favicon');


$admin_logo = $admin_logo['admin_logo'];
$favicon = $favicon['favicon'];
// dd($favicon);
@endphp
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-block">
                <div class="logo text-center">
                    <a href="#">
                        <img src="{{ $admin_logo ?? url('assets/images/logo/sidebarlogo2.png')}}" alt="Logo">
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu">


                <li class="sidebar-item">
                    <a href="{{ route('app_settings.index') }} " class='sidebar-link'>
                        <i class="bi bi-phone"></i>
                        <span class="menu-item ">{{ __('App Setup') }}</span>
                    </a>
                </li>
                <li class="sidebar-item has-sub">
                    <a href="#" class='sidebar-link'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M4 19H6C6 20.103 6.897 21 8 21H16C17.103 21 18 20.103 18 19H20C21.103 19 22 18.103 22 17V7C22 5.897 21.103 5 20 5H18C18 3.897 17.103 3 16 3H8C6.897 3 6 3.897 6 5H4C2.897 5 2 5.897 2 7V17C2 18.103 2.897 19 4 19ZM20 7V17H18V7H20ZM8 5H16L16.001 19H8V5ZM4 7H6V17H4V7Z" fill="#45536D"/>
                          </svg>
                        <span class="menu-item">{{ __('onborading') }}</span>
                    </a>
                    <ul class="submenu" style="padding-left: 0rem">

                        <li class="submenu-item">
                            <a href="{{ route('onboarding.create') }}">{{ __('Onboarding List') }}</a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('onboardingstyle.index') }}">{{ __('Onboarding Style') }}</a>
                        </li>
                    </ul>
                </li>

                {{-- <li class="sidebar-item" >
                    <a href="{{ route('draweritem.index') }}" class='sidebar-link'>
                        <i class="bi bi-card-text"></i>
                        <span class="menu-item">{{ __('Drawer Item') }}</span>
                    </a>
                </li> --}}
                <li class="sidebar-item">
                    <a href="{{ url('notification') }}" class='sidebar-link'>
                        <i class="bi bi-send"></i>
                        <span class="menu-item">{{ __('Send Notification') }}</span>
                    </a>
                </li>

                <li class="sidebar-item" >
                    <a href="{{ route('admob.index') }} " class='sidebar-link'>
                        <i class="bi bi-badge-ad"></i>
                        <span class="menu-item">{{ __('Ads Setup') }}</span>
                    </a>
                </li>

                <li class="sidebar-item" >
                    <a href="{{ route('system-update.index') }} " class='sidebar-link'>
                        <i class="bi bi-laptop"></i>
                        <span class="menu-item">{{ __('System Update') }}</span>
                    </a>
                </li>



                <li class="sidebar-item has-sub">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-gear"></i>
                        <span class="menu-item">{{ __('Setting') }}</span>
                    </a>
                    <ul class="submenu" style="padding-left: 0rem">
                        <li class="submenu-item">
                            <a href="{{ route('setting.index') }}">{{ __('General Settings') }}</a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('about-us') }}">{{ __('About Us') }}</a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('contact-us') }}">{{ __('Contact Us') }}</a>
                        </li>

                        <li class="submenu-item">
                            <a href="{{ route('terms-and-conditions') }}">{{ __('Terms & Conditions') }}</a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('app-settings') }}">{{ __('App Settings') }}</a>
                        </li>


                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>
