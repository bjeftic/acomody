<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Acomody, Superadmin zone</title>

    <!-- Styles -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!};
    </script>

    @yield('head')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container-fluid"
            style="{{ App::environment('production') ? 'background:#ce2323;' : '' }}"
            >
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}"
                    style="{{ App::environment('production') ? 'color:white;' : '' }}"
                    >
                        Superadmin ({{ App::environment() }})
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                          <span style="line-height: 22px;
                            position: relative;
                            display: block;
                            padding: 14px 15px;
                            color: {{ App::environment('production') ? 'white' : '#ccc' }};">
                              {{ Auth::user()->name }}
                          </span>
                        </li>
                        <li>
                          <a href="{{ url('/superadmin/permission-cache') }}" style="{{ App::environment('production') ? 'color:white;' : '' }}">
                              Reset permission cache
                          </a>

                          <form id="permission-form" action="{{ url('/superadmin/permission-cache') }}" method="GET" style="display: none;">
                              {{ csrf_field() }}
                              <input type="hidden" name="name" value="" id="permission-name" />
                          </form>
                        </li>
                        <li>
                          <a href="{{ url('/superadmin/snapshot') }}"
                              onclick="event.preventDefault();
                                      document.getElementById('snapshot-name').value = prompt('Input snapshot name (optional):', '');
                                      document.getElementById('snapshot-form').submit();"
                              style="{{ App::environment('production') ? 'color:white;' : '' }}"
                                      >
                              New DB Snapshot
                          </a>

                          <form id="snapshot-form" action="{{ url('/superadmin/snapshot') }}" method="GET" style="display: none;">
                              {{ csrf_field() }}
                              <input type="hidden" name="name" value="" id="snapshot-name" />
                          </form>
                        </li>
                        <li>
                            <a href="{{ url('/admin/logout') }}"
                                onclick="event.preventDefault();
                                          document.getElementById('logout-form').submit();"
                                style="{{ App::environment('production') ? 'color:white;' : '' }}"
                                          >
                                Logout
                            </a>

                            <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>


        <div class="container-fluid">
            <div class="row">

                {{-- sidebar --}}
                <div class="col-md-2">
                    <div class="sidebar">
                        <nav>
                            <ul class="list-unstyled">
                                @if (Auth::user())
                                    <li {{ (Request::is('admin/home') ? 'class=active' : '') }}><a href="{{ url('/admin/home') }}">Home</a></li>
                                    <li {{ (Request::is('admin/users') ? 'class=active' : '') }}><a href="{{ url('/admin/users') }}">Users</a></li>
                                    <li {{ (Request::is('admin/superadmin-users') ? 'class=active' : '') }}><a href="{{ url('/admin/superadmin-users') }}">Superadmin users</a></li>
                                    <li {{ (Request::is('admin/locations') ? 'class=active' : '') }}><a href="{{ url('/admin/locations') }}">Locations</a></li>
                                    <li {{ (Request::is('admin/accommodation-drafts') ? 'class=active' : '') }}><a href="{{ url('/admin/accommodation-drafts') }}">Accommodation Drafts</a></li>
                                    <li {{ (Request::is('admin/accommodations') ? 'class=active' : '') }}><a href="{{ url('/admin/accommodations') }}">Accommodations</a></li>
                                    <li {{ (Request::is('admin/horizon') ? 'class=active' : '') }}><a href="{{ url('/admin/horizon') }}">Horizon</a></li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
                {{-- .sidebar --}}

                {{-- content --}}
                <div class="col-md-10">
                    @yield('content')
                </div>
                {{-- .content --}}

            </div>
        </div>

    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


    @yield('footer')
</body>
</html>
