@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-form">
  <h1 class="register-form__heading">会員登録</h1>
  <form class="register-form__form" action="/register" method="post" novalidate>
    @csrf
    <div class="register-form__group">
      <label class="register-form__label" for="name">ユーザー名</label>
      <input class="register-form__input" type="text" name="name" value="{{ old('name') }}" />
      <p class="register-form__error-message">
        @error('name')
        {{ $message }}
        @enderror
      </p>
    </div>
    <div class="register-form__group">
      <label class="register-form__label" for="email">メールアドレス</label>
      <input class="register-form__input" type="email" name="email" value="{{ old('email') }}" />
      <p class="register-form__error-message">
        @error('email')
        {{ $message }}
        @enderror
      </p>
    </div>
    <div class="register-form__group">
      <label class="register-form__label" for="password">パスワード</label>
      <input class="register-form__input" type="password" name="password" />
      <p class="register-form__error-message">
        @error('password')
        {{ $message }}
        @enderror
      </p>
    </div>
    <div class="register-form__group">
      <label class="register-form__label" for="password_confirmation">確認用パスワード</label>
      <input class="register-form__input" type="password" name="password_confirmation" />
      <p class="register-form__error-message">
        @error('password_confirmation')
        {{ $message }}
        @enderror
      </p>
    </div>
    <button class="register-form__button" type="submit">登録する</button>
  </form>
  <a class="register-form__login-link" href="/login">ログインはこちら</a>
</div>
@endsection
