@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/time-stamp.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="attendance-container__status">
        @if($alreadyFinishedToday)
        <p>退勤済</p>
        @elseif(!$attendance)
        <p class="status-outside">勤務外</p>
        @elseif(!$attendance->punched_out_at && !$isBreaking)
        <p>出勤中</p>
        @elseif($isBreaking)
        <p>休憩中</p>
        
        @endif
    </div>
    <!-- リアルタイム時計 -->
         <p class="attendance-container__date">{{ \Carbon\Carbon::now()->isoFormat('YYYY年M月D日(ddd)') }}</p>
         <div class="attendance-container__time" id="clock">{{ Carbon\Carbon::now()->format('H:i') }}</div>

    <!-- 打刻フォーム -->
    <form class="attendance-container__btn" action="{{ route('attendances.punch') }}" method="post">
        @csrf
        @if(!$attendance && !$alreadyFinishedToday)
            <button class="attendance-container__btn-attendance" name="type" value="in" class="btn">出勤</button>
        @elseif($alreadyFinishedToday)
        @elseif(!$attendance?->punched_out_at && !$isBreaking)
            <button class="attendance-container__btn-attendance" name="type" value="out" class="btn">退勤</button>
            <button class="attendance-container__btn-break" name="type" value="break_in" class="btn">休憩入</button>
        @elseif($isBreaking)
            <button class="attendance-container__btn-break" name="type" value="break_out" class="btn">休憩戻</button>
        @endif
        @if(session('success')) <p class="alert-success">{{ session('success') }}</p> @endif
        @if(session('error')) <p class="alert-error">{{ session('error') }}</p> @endif
    </form>
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