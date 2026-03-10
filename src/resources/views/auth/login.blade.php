@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-form">
  <h1>ログイン</h1>
  <form class="login-form__form" action="/login" method="post" novalidate>
    @csrf
    <div class="login-form__group">
      <label class="login-form__label" for="email">メールアドレス</label>
      <input class="login-form__input" type="email" name="email" value="{{ old('email') }}" />
      <p class="login-form__error-message">
        @error('email')
        {{ $message }}
        @enderror
      </p>
    </div>
    <div class="login-form__group">
      <label class="login-form__label" for="password">パスワード</label>
      <input class="login-form__input" type="password" name="password" />
      <p class="login-form__error-message">
        @error('password')
        {{ $message }}
        @enderror
      </p>
    </div>
    <button class="login-form__button" type="submit">ログインする</button>
  </form>
  <a class="login-form__register-link" href="/register">会員登録はこちら</a>
</div>
@endsection
