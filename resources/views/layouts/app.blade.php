<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

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
                <h1><a href="{{ url('/tests') }}">ManageMe</a></h1>
                @if (Auth::check())
                    <nav class="navbar">
                        <div class="navbar-right">
                            <a href="/tests" class="btn btn-secondary">Home</a>
                            <a href="{{ route('projects.myProjects') }}" class="btn btn-primary">My Projects</a>
                            <a href="{{ url('/profile') }}" class="btn btn-secondary">Profile</a> 
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
    </body>
</html>