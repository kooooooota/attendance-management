@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/index.css') }}">
@endsection
@section('content')
<div class="attendance-list">
    <h1 class="main-title">スタッフ一覧</h1>
    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                @unless($user->is_admin)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a class="td-detail" href="{{ route('admins.users.show', $user->id) }}">詳細</a>
                    </td>
                </tr>
                @endunless
            @endforeach
        </tbody>
    </table>
</div>
@endsection
