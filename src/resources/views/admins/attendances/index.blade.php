@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/index.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="main-title">{{ $displayDate->format('Y年m月d日') }}の勤怠</h1>
    <div class="calendar-nav">
        
        {{-- 前日リンク --}}
        <a class="link-prev" href="{{ route('admins.attendances.index', ['date' => $prevDate]) }}">
            <img class="link-prev__img" src="{{ asset('images/arrow-left.png') }}" alt="前日">
            <span class="link-prev__text">前日</span>
        </a>
    
        {{-- 年月選択フォーム --}}
        <form action="{{ route('admins.attendances.index') }}" method="GET" id="month-form">
            <div class="select-month">
                <img class="select-month__img" src="{{ asset('images/calender-icon.png') }}" alt="カレンダー">
                <span class="select-month__text">
                    {{ $displayDate->format('Y/m/d') }}
                </span>
                <input class="select-month__input" type="date" name="date" value="{{ $displayDate->toDateString() }}" 
                       onchange="this.form.submit()"> {{-- ここだけ1行JS。変更時に自動送信 --}}
            </div>
        </form>
    
        {{-- 翌日リンク --}}
        <a class="link-next" href="{{ route('admins.attendances.index', ['date' => $nextDate]) }}">
            <span class="link-next__text">翌日</span>
            <img class="link-next__img" src="{{ asset('images/arrow-right.png') }}" alt="翌日">
        </a>
    </div>
    
    {{-- 勤怠一覧テーブル --}}
    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                @unless($attendance->user->is_admin)
                    <tr>
                        <td>{{ $attendance?->user->name }}</td>
                        <td>{{ $attendance?->punched_in_at?->format('H:i') }}</td>
                        <td>{{ $attendance?->punched_out_at?->format('H:i') }}</td>
                        <td>{{ $attendance ? $attendance->getBreakTimeDisplay() : ''}}</td>
                        <td>{{ $attendance ? $attendance->getWorkTimeDisplay() : ''}}</td>
                        <td>
                            @if($attendance)
                                <a class="td-detail" href="{{ route('admins.attendances.show', ['id' => $attendance->id]) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endunless
            @endforeach
        </tbody>
    </table>

</div>

@endsection