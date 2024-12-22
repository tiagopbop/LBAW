<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        <link href="{{ url('css/app.css') }}" rel="stylesheet">

        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer>
        </script>
    </head>
    <body>
        <main>
            <header>
                <h1><a href="{{ url('/home') }}">ManageMe</a></h1>
                @if (Auth::check()&& !Auth::user()->suspended_status)
                    <nav class="navbar-background">
                        <div class="navbar-buttons">
                            <a href="/home" class="regular-button">Home</a>
                            <a href="{{ route('projects.myProjects') }}" class="projects-button">My Projects</a>
                            <a href="{{ url('/mytasks')}}" class="tasks-button">My tasks</a>
                            <a href="{{ route('profile.show', Auth::user()->username) }}" class="regular-button">Profile</a>
                            <a href="{{ url('/logout') }}" class="logout-button">Logout</a>
                        </div>
                    </nav>
                @endif
                @if (Auth::check()&& Auth::user()->suspended_status))
                <nav class="navbar">
                    <div class="navbar-right">
                         <a href="{{ url('/logout') }}" class="btn btn-danger">Logout</a>
                    </div>
                </nav>
                @endif

            </header>
            <section id="content">
                @yield('content')
            </section>
            @stack('scripts')
        </main>
        <footer class="footer">
            <div class="footer-links">
                <a href="{{ url('/about') }}" class="footer-link">About Us</a>
                <a href="{{ url('/contact') }}" class="footer-link">Contacts</a>
                <a href="{{ url('/faq') }}" class="footer-link">FAQ</a>
            </div>
        </footer>
    </body>
</html>