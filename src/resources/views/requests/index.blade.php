@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/requests/index.css') }}">
@endsection

@section('content')
<div class="request-list">
    <h1 class="main-title">申請一覧</h1>
    <div class="request-list__tab">
        <a class="request-list__tab-link {{ $currentTab === 'pending' ? 'active' : '' }}" href="{{ route('requests.index', ['tab' => 'pending']) }}">承認待ち</a>
        <a class="request-list__tab-link {{ $currentTab === 'approved' ? 'active' : '' }}" href="{{ route('requests.index', ['tab' => 'approved']) }}">承認済み</a>
    </div>
    @if($requests->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                        <td>{{ $req->user->name }}</td>
                        <td>{{ $req->attendance->work_date->format('Y/m/d') }}</td>
                        <td>{{ $req->remarks }}</td>
                        <td>{{ $req->created_at->format('Y/m/d') }}</td>
                        <td>
                            <a class="td-detail" href="{{ route('attendances.show', $req->attendance_id) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>対象の申請はありません</p>
    @endif
</div>
@endsection