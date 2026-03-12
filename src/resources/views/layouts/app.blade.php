<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Management</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <a class="header__logo" href="/attendance"><img class="header__logo-img" src="{{ asset('images/header_logo.png') }}" alt="サイトロゴ"></a>
      
      <div class="menu">
        <ul>
          <li><a class="menu__attendance-link" href="/attendance">勤怠</a></li>
          <li><a class="menu__list-link" href="/attendance/list">勤怠一覧</a></li>
          <li><a class="menu__request-link" href="/stamp_correction_request/list">申請</a></li>
          <li>
            <form class="menu__logout" action="/logout" method="post">
              @csrf
              <button class="menu__logout-button" type="submit">ログアウト</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </header>
  <main>
    @yield('content')
  </main>
</body>

</html>