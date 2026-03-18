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
<h1>勤怠詳細</h1>
<form action="{{ route('attendances.attendance_request', $attendance->id) }}" method="post">
    @csrf
    <table>
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $attendance->work_date->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                <input type="time" name="punched_in_at" value="{{ old('punched_in_at', $attendance->punched_in_at ? $attendance->punched_in_at->format('H:i') : '') }}">
                <span>～</span>
                <input type="time" name="punched_out_at" value="{{ old('punched_out_at', $attendance->punched_out_at ? $attendance->punched_out_at->format('H:i') : '') }}">
            </td>
        </tr>
        @if($errors->has('punched_in_at') || $errors->has('punched_out_at'))
        <tr>
            <th></th>
            <td class="form__error">
                @error('punched_in_at') {{ $message }} @enderror
                @error('punched_out_at') {{ $message }} @enderror
            </td>
        </tr>
        @endif
        @foreach($attendance->breakTimes as $index => $bt)
        <tr>
            <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $bt->id }}">
            <th>休憩 {{ $index === 0 ? '' : $index + 1 }}</th>
            <td>
                <input type="time" name="breaks[{{ $index }}][punched_in_at]" value="{{ old("breaks.$index.punched_in_at", $bt->punched_in_at ? $bt->punched_in_at->format('H:i') : '') }}">
                <span>～</span>
                <input type="time" name="breaks[{{ $index }}][punched_out_at]" value="{{ old("breaks.$index.punched_out_at", $bt->punched_out_at ? $bt->punched_out_at->format('H:i') : '') }}">
            </td>
        </tr>
        @if($errors->has("breaks.$index.punched_in_at") || $errors->has("breaks.$index.punched_out_at"))
        <tr>
            <th></th>
            <td class="form__error">
                @error("breaks.$index.punched_in_at") {{ $message }} @enderror
                @error("breaks.$index.punched_out_at") {{ $message }} @enderror
            </td>
        </tr>
        @endif
        @endforeach
        @php
            $breakCount = $attendance->breakTimes->count();
        @endphp
        <tr>
            <th>休憩 {{ $breakCount === 0 ? '' : $breakCount + 1 }}</th>
            <td>
                <input type="time" name="breaks[{{ $breakCount }}][punched_in_at]" value="{{ old("breaks.$breakCount.punched_in_at") }}">
                <span>～</span>
                <input type="time" name="breaks[{{ $breakCount }}][punched_out_at]" value="{{ old("breaks.$breakCount.punched_out_at") }}">
            </td>
        </tr>
        <tr>
            <th></th>
            <td class="form__error">
                @error('breaks') {{ $message }} @enderror
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td><textarea name="remarks">{{ old('remarks', $attendance->remarks) }}</textarea></td>
        </tr>
        @if($errors->has('remarks'))
        <tr>
            <th></th>
            <td class="form__error">
                @error('remarks') {{ $message }} @enderror
            </td>
        </tr>
        @endif
    </table>
    <button type="submit">修正</button>
</form>
@endsection