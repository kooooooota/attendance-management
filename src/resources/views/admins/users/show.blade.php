@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/index.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="main-title">{{ $user->name }}さんの勤怠</h1>
    <div class="calendar-nav">
        
        {{-- 前月リンク --}}
        <a class="link-prev" href="{{ route('admins.users.show', ['id' => $user->id, 'month' => $prevMonth]) }}">
            <img class="link-prev__img" src="{{ asset('images/arrow-left.png') }}" alt="前月">
            <span class="link-prev__text">前月</span>
        </a>
    
        {{-- 年月選択フォーム --}}
        <form action="{{ route('admins.users.show', ['id' => $user->id]) }}" method="GET" id="month-form">
            <div class="select-month">
                <img class="select-month__img" src="{{ asset('images/calender-icon.png') }}" alt="カレンダー">
                <span class="select-month__text">
                    {{ $displayDate->format('Y/m') }}
                </span>
                <input class="select-month__input" type="month" name="month" value="{{ $displayDate->format('Y-m') }}" 
                       onchange="this.form.submit()"> {{-- ここだけ1行JS。変更時に自動送信 --}}
            </div>
        </form>
    
        {{-- 次月リンク --}}
        <a class="link-next" href="{{ route('admins.users.show', ['id' => $user->id, 'month' => $nextMonth]) }}">
            <span class="link-next__text">翌月</span>
            <img class="link-next__img" src="{{ asset('images/arrow-right.png') }}" alt="翌月">
        </a>
    </div>
    
    {{-- 勤怠一覧テーブル --}}
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= $displayDate->daysInMonth; $i++)
                @php
                    $carbonDate = $displayDate->copy()->day($i);
                    $dateStr = $displayDate->copy()->day($i)->toDateString();
                    $attendance = $attendances->get($dateStr);
                @endphp
                <tr>
                    <td>{{ $carbonDate->format('m/d') }}({{ $carbonDate->isoFormat('ddd') }})</td>
                    <td>{{ $attendance?->punched_in_at ? $attendance->punched_in_at->format('H:i') : '' }}</td>
                    <td>{{ $attendance?->punched_out_at ? $attendance->punched_out_at->format('H:i') : '' }}</td>
                    <td>{{ $attendance ? $attendance->getBreakTimeDisplay() : ''}}</td>
                    <td>{{ $attendance ? $attendance->getWorkTimeDisplay() : ''}}</td>
                    <td>
                        @if($attendance)
                            <a class="td-detail" href="{{ route('admins.attendances.show', ['id' => $attendance->id]) }}">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
    <div class="btn">
        <a class="btn-export" href="{{ route('csv.export', ['id' => $user->id]) }}">CSV出力</a>
    </div>
</div>

@endsection