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
      <a class="header__logo" href="{{ Auth::user()->is_admin ? route('admins.attendances.index') : route('attendances.time_stamp') }}"><img class="header__logo-img" src="{{ asset('images/header-logo.png') }}" alt="サイトロゴ"></a>
      
      <div class="menu">
        <ul>
          @if(Auth::user()->is_admin)
            <li><a class="menu__list-link" href="{{ route('admins.attendances.index') }}">勤怠一覧</a></li>
            <li><a class="menu__attendance-link" href="{{ route('admins.users.index') }}">スタッフ一覧</a></li>
            <li><a class="menu__request-link" href="/stamp_correction_request/list">申請一覧</a></li>
            <li>
                <form class="menu__logout" action="/logout" method="post">
                @csrf
                <button class="menu__logout-button" type="submit">ログアウト</button>
                </form>
            </li>
          @else
            @if(Auth::user()->isPunchedOutToday())
            <li><a class="menu__list-link" href="{{ route('attendances.list') }}">今月の出勤一覧</a></li>
            <li><a class="menu__request-link" href="/stamp_correction_request/list">申請一覧</a></li>
            <li>
              <form class="menu__logout" action="/logout" method="post">
                @csrf
                <button class="menu__logout-button" type="submit">ログアウト</button>
              </form>
            </li>
            @else
            <li><a class="menu__attendance-link" href="{{ route('attendances.time_stamp') }}">勤怠</a></li>
            <li><a class="menu__list-link" href="{{ route('attendances.list') }}">勤怠一覧</a></li>
            <li><a class="menu__request-link" href="/stamp_correction_request/list">申請</a></li>
            <li>
              <form class="menu__logout" action="/logout" method="post">
                @csrf
                <button class="menu__logout-button" type="submit">ログアウト</button>
              </form>
            </li>
            @endif
          @endif
        </ul>
      </div>
    </div>
  </header>
  <main>
    @yield('content')
  </main>
</body>

</html>