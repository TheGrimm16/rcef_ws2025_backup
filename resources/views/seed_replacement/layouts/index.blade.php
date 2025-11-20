<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Seed Replacement App')</title>

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"  />

    {{-- App CSS --}}
    <!-- <link rel="stylesheet" href="{{ asset('public/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/app.css') }}"> -->

    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">

    <link rel="stylesheet" href="{{ asset('public/css/dialog/jquery-confirm.min.css') }}">

    {{-- Page-specific styles --}}
    @stack('styles')

    <style>
        .addBtn { margin-bottom: 10px; }
        .label { margin-right: 3px; }

        /* Dynamic layout */
        :root {
            --header-height: 60px;
            --sidebar-width: 240px;
        }

        #app-header { height: var(--header-height); }
        #app-sidebar { width: var(--sidebar-width); top: var(--header-height); }
        .content-wrapper {
            margin-left: var(--sidebar-width);
            padding: calc(var(--header-height) + 20px) 20px 20px;
            background: #f4f6f9;
            min-height: 100vh;
        }
    </style>
</head>

<body>

    @include('seed_replacement.layouts.header')
    @include('seed_replacement.layouts.sidebar')

    <div class="content-wrapper">
        @yield('content')
    </div>

    @include('seed_replacement.layouts.footer')
    
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Bootstrap JS --}}
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>

    {{-- Select2 JS (must be before your custom scripts) --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>

</html>
