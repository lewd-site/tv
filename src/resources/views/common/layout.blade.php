@prepend('styles')
<link rel="stylesheet" href="/css/app.css" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700&display=swap&subset=cyrillic" />
@endprepend

@prepend('scripts')
<script src="/js/manifest.js"></script>
<script src="/js/vendor.js"></script>
<script src="/js/app.js"></script>
@endprepend

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>@yield('title')</title>

  @stack('styles')
</head>

<body class="layout">
  @section('headers')
  <header class="layout__header header">
    @include('common.blocks.header')
  </header>
  @show

  @yield('content')

  @section('footer')
  <footer class="layout__footer footer">
    @include('common.blocks.footer')
  </footer>
  @show

  @stack('scripts')
</body>

</html>
