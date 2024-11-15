<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

    <head>

        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Admin Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Admin dashboard" name="description" />
        <meta content="PurnTech" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">

        <!-- plugin css -->
        <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Layout config Js -->
        <script src="{{ asset('assets/js/layout.js') }}"></script>
        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- custom Css-->
        <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
        {{-- <link href="{{ asset('/css/dataTables.dataTables.css') }}" rel="stylesheet" type="text/css" /> --}}
        <link href="{{ asset('/css/dataTables.bootstrap5.css') }}" rel="stylesheet" type="text/css" />

        <script src="{{ asset('/js/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('/js/dataTables.js') }}"></script>
        <script src="{{ asset('/js/dataTables.bootstrap5.js') }}"></script>
        <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>

    </head>

    <body>

        <!-- Begin page -->
        <div id="layout-wrapper">

            <header id="page-topbar">
                <div class="layout-width">
                    <div class="navbar-header">
                        <div class="d-flex">
                            <!-- LOGO -->
                            <div class="navbar-brand-box horizontal-logo">
                                <a href="{{ url('/') }}" class="logo logo-dark">
                                    <span class="logo-sm">
                                        <img src="{{asset('favicon.svg')}}" alt="" height="50">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="{{asset('logo.svg')}}" alt="" height="50">
                                    </span>
                                </a>

                                <a href="{{ url('/') }}" class="logo logo-light">
                                    <span class="logo-sm">
                                        <img src="{{asset('favicon.svg')}}" alt="" height="50">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="{{asset('logo.svg')}}" alt="" height="50">
                                    </span>
                                </a>
                            </div>

                            <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                                <span class="hamburger-icon">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </button>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="ms-1 header-item d-none d-sm-flex">
                                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                                    <i class='bx bx-fullscreen fs-22'></i>
                                </button>
                            </div>

                            <div class="ms-1 header-item d-none d-sm-flex">
                                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                                    <i class='bx bx-moon fs-22'></i>
                                </button>
                            </div>

                            

                            <div class="dropdown ms-sm-3 header-item topbar-user">
                                <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                    <span class="d-flex align-items-center">
                                        @if(Session::get('userPic'))
                                            <img class="rounded-circle header-profile-user" src="{{asset('uploads/admin/profile')}}/{{Session::get('userPic')}}" alt="Header Avatar">
                                        @else
                                            <img class="rounded-circle header-profile-user" src="{{asset('assets/images/users/avatar-1.jpg')}}" alt="Header Avatar">
                                        @endif
                                        <span class="text-start ms-xl-2">
                                            <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{Session::get('userName')}}</span>
                                            <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{Session::get('userType')}}</span>
                                        </span>
                                    </span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <h6 class="dropdown-header">Welcome {{Session::get('userName')}}!</h6>
                                    <a class="dropdown-item" href="{{url('admin/settings')}}">
                                        <span class="badge bg-success-subtle text-success mt-1 float-end">New</span>
                                        <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> 
                                        <span class="align-middle">Settings</span>
                                    </a>
                                    <a class="dropdown-item" href="{{url('logout')}}">
                                        <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                        <span class="align-middle" data-key="t-logout">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- ========== App Menu ========== -->
            <div class="app-menu navbar-menu">
                <!-- LOGO -->
                <div class="navbar-brand-box">
                    <!-- Dark Logo-->
                    <a href="{{ url('/') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{asset('favicon.svg')}}" alt="" height="50">
                        </span>
                        <span class="logo-lg">
                            <img src="{{asset('logo.svg')}}" alt="" height="50">
                        </span>
                    </a>
                    <!-- Light Logo-->
                    <a href="{{ url('/') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{asset('favicon.svg')}}" alt="" height="50">
                        </span>
                        <span class="logo-lg">
                            <img src="{{asset('logo.svg')}}" alt="" height="50">
                        </span>
                    </a>
                    <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                        <i class="ri-record-circle-line"></i>
                    </button>
                </div>

                <div id="scrollbar">
                    <div class="container-fluid">

                        <div id="two-column-menu">
                        </div>
                        <ul class="navbar-nav" id="navbar-nav">
                            <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                            <li class="nav-item">
                                <a class="nav-link menu-link dashboard  {{ Request::is('admin/dashboard') ? 'active' : '' }}" href="{{url('admin/dashboard')}}">
                                    <i class="mdi mdi-monitor-dashboard"></i> <span data-key="t-widgets">Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ Request::is('admin/merchant/approval') ? 'active' : '' }}" href="{{url('admin/merchant/approval')}}">
                                    <i class="mdi mdi-account-check"></i> <span data-key="t-widgets">Merchant Approval</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ Request::is('admin/account/details') ? 'active' : '' }}" href="{{url('admin/account/details')}}">
                                    <i class="mdi mdi-bank-check"></i> <span data-key="t-widgets">Account Details</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ Request::is('admin/url/whitelisting') ? 'active' : '' }}" href="{{url('admin/url/whitelisting')}}">
                                    <i class="mdi mdi-web-check"></i> <span data-key="t-widgets">URL Whitelisting</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ Request::is('/admin/settlement/report') ? 'active' : '' }}" href="{{url('/admin/settlement/report')}}">
                                    <i class="bx bx-notepad"></i> <span data-key="t-widgets">Settlement Report</span>
                                </a>
                            </li>
                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-components">Other</span></li>
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ Request::is('admin/settings') ? 'active' : '' }}" href="{{url('admin/settings')}}">
                                    <i class="ri-settings-5-line"></i> <span data-key="t-widgets">Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ Request::is('admin/logs') ? 'active' : '' }}" href="{{url('admin/logs')}}">
                                    <i class="bx bx-notepad"></i> <span data-key="t-widgets">Logs</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="{{url('logout')}}">
                                    <i class="bx bx-power-off"></i> <span data-key="t-widgets">Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- Sidebar -->
                </div>

                <div class="sidebar-background"></div>
            </div>
            <!-- Left Sidebar End -->
            <!-- Vertical Overlay-->
            <div class="vertical-overlay"></div>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
