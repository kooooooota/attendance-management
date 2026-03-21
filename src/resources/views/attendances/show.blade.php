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
        $breakCount = $attendance->breakTimes->count();
    @endphp
    <form class="request-form__form" action="{{ route('attendances.request', $attendance->id) }}" method="post">
        @csrf
        <table>
            <tr>
                <th>名前</th>
                <td class="name">{{ $attendance->user->name }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $attendance->work_date->format('Y年') }}</td>
                <td></td>
                <td>{{ $attendance->work_date->format('m月d日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                @if($pendingRequest)
                    <td>{{ $pendingRequest->punched_in_at->format('H:i') }}</td>
                    <td>～</td>
                    <td>{{ $pendingRequest->punched_out_at->format('H:i') }}</td>
                @else
                <td>
                    <input class="attendance-input" type="time" name="punched_in_at" value="{{ old('punched_in_at', $attendance->punched_in_at ? $attendance->punched_in_at->format('H:i') : '') }}">
                </td>
                <td>～</td>
                <td>
                    <input class="attendance-input" type="time" name="punched_out_at" value="{{ old('punched_out_at', $attendance->punched_out_at ? $attendance->punched_out_at->format('H:i') : '') }}">
                </td>
                @endif
            </tr>
            @if($errors->has('punched_in_at') || $errors->has('punched_out_at'))
            <tr>
                <th class="form__error"></th>
                <td class="form__error" colspan="3">
                    @error('punched_in_at') {{ $message }} @enderror
                    @error('punched_out_at') {{ $message }} @enderror
                </td>
            </tr>
            @endif
            @if($pendingRequest)
                @foreach($pendingRequest->breakTimeRequests as $index => $br)
                    <tr>
                        <th>休憩 {{ $index + 1 }}</th>
                        <td>{{ $br->punched_in_at->format('H:i') }}</td>
                        <td>～</td>
                        <td>{{ $br->punched_out_at->format('H:i') }}</td>
                    </tr>
                @endforeach
            @else
                @foreach($attendance->breakTimes as $index => $bt)
                    <tr>
                        <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $bt->id }}">
                        <th>休憩 {{ $index === 0 ? '' : $index + 1 }}</th>
                        <td><input class="attendance-input" type="time" name="breaks[{{ $index }}][punched_in_at]" value="{{ old("breaks.$index.punched_in_at", $bt->punched_in_at ? $bt->punched_in_at->format('H:i') : '') }}"></td>
                        <td>～</td>
                        <td><input class="attendance-input" type="time" name="breaks[{{ $index }}][punched_out_at]" value="{{ old("breaks.$index.punched_out_at", $bt->punched_out_at ? $bt->punched_out_at->format('H:i') : '') }}"></td>
                    </tr>
                    @if($errors->has("breaks.$index.punched_in_at") || $errors->has("breaks.$index.punched_out_at"))
                    <tr>
                        <th class="form__error"></th>
                        <td class="form__error" colspan="3">
                            @php
                                $allBreakErrors = collect([
                                    ...$errors->get("breaks.$index.punched_in_at"),
                                    ...$errors->get("breaks.$index.punched_out_at")
                                ])->unique();
                            @endphp
                            @foreach($allBreakErrors as $message)
                                <div class="form__error">{{ $message }}</div>
                            @endforeach
                        </td>
                    </tr>
                    @endif
                @endforeach
                <tr>
                    <th>休憩 {{ $breakCount === 0 ? '' : $breakCount + 1 }}</th>
                        <td><input class="attendance-input" type="time" name="breaks[{{ $breakCount }}][punched_in_at]" value="{{ old("breaks.$breakCount.punched_in_at") }}"></td>
                        <td>～</td>
                        <td><input class="attendance-input" type="time" name="breaks[{{ $breakCount }}][punched_out_at]" value="{{ old("breaks.$breakCount.punched_out_at") }}"></td>
                </tr>
                @if($errors->has("breaks.$breakCount.punched_in_at") || $errors->has("breaks.$breakCount.punched_out_at"))
                <tr>
                    <th class="form__error"></th>
                    <td class="form__error" colspan="3">
                        @php
                            $allBreakErrors = collect([
                                ...$errors->get("breaks.$breakCount.punched_in_at"),
                                ...$errors->get("breaks.$breakCount.punched_out_at")
                            ])->unique();
                        @endphp
                        @foreach($allBreakErrors as $message)
                            <div class="form__error">{{ $message }}</div>
                        @endforeach
                        @error('breaks') 
                            <div class="form__error">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @endif
            @endif
            <tr>
                <th>備考</th>
                <td class="remarks-input" colspan="3">
                    @if($pendingRequest)
                        {{ $pendingRequest->remarks }}
                    @else
                    <textarea name="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>
                    @endif
                </td>
            </tr>
            @if($errors->has('remarks'))
            <tr>
                <th class="form__error"></th>
                <td class="form__error" colspan="3">
                    @error('remarks') {{ $message }} @enderror
                </td>
            </tr>
            @endif
        </table>
        @if($pendingRequest)
            <div class="request-form__form-pending">*承認待ちのため修正はできません。</div>
        @else
            <button class="request-form__form-button" type="submit">修正</button>
        @endif
    </form>
</div>
@endsection