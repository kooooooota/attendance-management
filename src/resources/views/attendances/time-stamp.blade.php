@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/time-stamp.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    @php
        $isBreaking = $attendance && $attendance->breakTimes()->whereNull('punched_out_at')->exists();
    @endphp
    @if(!$attendance || !$attendance->punched_in_at)
    <p>勤務外</p>
    @elseif(!$attendance->punched_out_at && !$isBreaking)
    <p>出勤中</p>
    @elseif($isBreaking)
    <p>休憩中</p>
    @else
    <p>退勤済</p>
    @endif
    <!-- リアルタイム時計 -->
    <p>{{ \Carbon\Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
    <div id="clock">00:00:00</div>

    <!-- 打刻フォーム -->
    <form action="{{ route('attendances.punch') }}" method="POST">
        @csrf
        @if(!$attendance || !$attendance->punched_in_at)
        <button name="type" value="in" class="btn">出勤</button>
        @elseif(!$attendance->punched_out_at && !$isBreaking)
        <button name="type" value="out" class="btn">退勤</button>
        <button name="type" value="break_in" class="btn">休憩入</button>
        @elseif($isBreaking)
        <button name="type" value="break_out" class="btn">休憩戻</button>
        @endif
    </form>

    <!-- メッセージ表示 -->
    @if(session('success')) <p>{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color: red;">{{ session('error') }}</p> @endif
</div>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = 
            now.getHours().toString().padStart(2, '0') + ":" +
            now.getMinutes().toString().padStart(2, '0');
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

@endsection