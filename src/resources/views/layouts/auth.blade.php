<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flea Market</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">
    @auth
      @if(auth()->user()->hasVerifiedEmail())
        <a class="header__logo" href="/"><img class="header__logo-img" src="{{ asset('images/header-logo.png') }}" alt="サイトロゴ"></a>
      @else
        <img class="header__logo-img" src="{{ asset('images/header-logo.png') }}" alt="サイトロゴ">
      @endif
    @else
      <a class="header__logo" href="/"><img class="header__logo-img" src="{{ asset('images/header-logo.png') }}" alt="サイトロゴ"></a>
    @endauth
    </div>
  </header>
  <main>
    @yield('content')
  </main>
</body>

</html>
