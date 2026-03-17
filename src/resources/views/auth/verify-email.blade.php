@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<form class="verify-email__form" action="{{ route('verification.send') }}" method="post">
    @csrf
    <p class="verify-email__message">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>
    <div class="verify-email__action">
        <a class="verify-email__link" href="http://localhost:8025">認証はこちらから</a>
        <button class="verify-email__btn" type="submit">認証メールを再送する</button>
    </div>
</form>

@endsection