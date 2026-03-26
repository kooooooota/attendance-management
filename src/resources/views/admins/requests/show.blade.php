@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<div class="request-form">
    <h1 class="main-title">勤怠詳細</h1>
    @php
        $breakCount = $attendanceRequest->breakTimeRequests?->count() ?? 0;
    @endphp
    <form class="request-form__form" action="{{ route('admins.requests.approval', $attendanceRequest->id) }}" method="post">
        @csrf
        <table>
            <tr>
                <th>名前</th>
                <td class="name">{{ $attendanceRequest->user->name }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $attendanceRequest->attendance?->work_date?->format('Y年') }}</td>
                <td></td>
                <td>{{ $attendanceRequest->attendance?->work_date?->format('m月d日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input class="attendance-input" type="time" name="punched_in_at" value="{{ old('punched_in_at', $attendanceRequest->punched_in_at ? $attendanceRequest->punched_in_at->format('H:i') : '') }}" readonly>
                </td>
                <td>～</td>
                <td>
                    <input class="attendance-input" type="time" name="punched_out_at" value="{{ old('punched_out_at', $attendanceRequest->punched_out_at ? $attendanceRequest->punched_out_at->format('H:i') : '') }}" readonly>
                </td>
            </tr>
            @foreach($attendanceRequest->breakTimeRequests as $index => $bt)
                <tr>
                    <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $bt->id }}">
                    <th>休憩 {{ $index === 0 ? '' : $index + 1 }}</th>
                    <td><input class="attendance-input" type="time" name="breaks[{{ $index }}][punched_in_at]" value="{{ old("breaks.$index.punched_in_at", $bt->punched_in_at ? $bt->punched_in_at->format('H:i') : '') }}" readonly></td>
                    <td>～</td>
                    <td><input class="attendance-input" type="time" name="breaks[{{ $index }}][punched_out_at]" value="{{ old("breaks.$index.punched_out_at", $bt->punched_out_at ? $bt->punched_out_at->format('H:i') : '') }}" readonly></td>
                </tr>
            @endforeach
            <tr>
                <th>休憩 {{ $breakCount === 0 ? '' : $breakCount + 1 }}</th>
                <td><input class="attendance-input" type="time" name="breaks[{{ $breakCount }}][punched_in_at]" value="{{ old("breaks.$breakCount.punched_in_at") }}" readonly></td>
                <td>～</td>
                <td><input class="attendance-input" type="time" name="breaks[{{ $breakCount }}][punched_out_at]" value="{{ old("breaks.$breakCount.punched_out_at") }}" readonly></td>
            </tr>
            <tr>
                <th>備考</th>
                <td class="remarks-input" colspan="3">
                    <textarea name="remarks" readonly>{{ old('remarks', $attendanceRequest->remarks) }}</textarea>
                </td>
            </tr>
        </table>
            @if($attendanceRequest->status === 'approved')
            <button class="request-form__form-button" type="submit" disabled>承認済み</button>
            @else
            <button class="request-form__form-button" type="submit">承認</button>
            @endif
    </form>
</div>
@endsection