<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />

  <title>@yield('title')</title>

  <link rel="stylesheet" href="/css/app.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700&display=swap&subset=cyrillic" />
</head>

<body>
  <header>
    @include('header')
  </header>

  @yield('content')

  <script src="/js/manifest.js"></script>
  <script src="/js/vendor.js"></script>
  <script src="/js/app.js"></script>
</body>

</html>
