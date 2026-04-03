<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceCorrectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function timeStamp()
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereNull('punched_out_at')
                                ->first();

        $isBreaking = false;
        $alreadyFinishedToday = false;

        if ($attendance) {
            $isBreaking = $attendance->breakTimes()->whereNull('punched_out_at')->exists();
        } else {
            $alreadyFinishedToday = Attendance::where('user_id', $user->id)
                                            ->where('work_date', $now->toDateString())
                                            ->whereNotNull('punched_out_at')
                                            ->exists();
        }

        return view('attendances.time-stamp', compact('attendance', 'isBreaking', 'alreadyFinishedToday'));
    }

    public function punch(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();

        if ($request->type === 'in') {
            $todayAttendance = Attendance::where('user_id', $user->id)
                                        ->where('work_date', $now->toDateString())
                                        ->first();
            
            if ($todayAttendance) return back()->with('error', '本日は出勤済みです');

            $attendance = new Attendance([
                'user_id' => $user->id,
                'work_date' => $now->toDateString(),
                'punched_in_at' => $now,
            ]);
            $attendance->save();
            return back();
        }

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereNull('punched_out_at')
                                ->latest('work_date')
                                ->first();

        if (!$attendance) return back()->with('error', '出勤データが見つかりません');

        if ($request->type === 'break_in') {
            $attendance->breakTimes()->create(['punched_in_at' => $now]);
            return back();
        } elseif ($request->type === 'break_out') {
            $latestBreak = $attendance->breakTimes()->whereNull('punched_out_at')->latest()->first();
            if (!$latestBreak) return bach()->with('error', '休憩が開始されていません');
            $latestBreak->update(['punched_out_at' => $now]);
            return back();
        } elseif ($request->type === 'out') {
            $attendance->update(['punched_out_at' => $now]);
            return back()->with('success', 'お疲れ様でした。');
        }

        return back();
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        $month = $request->query('month', now()->format('Y-m'));
        $displayDate = Carbon::parse($month);

        $prevMonth = $displayDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $displayDate->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $displayDate->year)
            ->whereMonth('work_date', $displayDate->month)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->work_date)->toDateString();
            });

            return view('attendances.index', compact('attendances', 'displayDate', 'prevMonth', 'nextMonth'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        $pendingRequest = AttendanceRequest::where('attendance_id', $id)
                            ->where('status', 'pending')
                            ->first();

        return view('attendances.show', compact('attendance', 'pendingRequest'));
    }

    public function storeRequest(AttendanceCorrectRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = $attendance->work_date->format('Y-m-d');

        $request_in = $request->punched_in_at ? Carbon::parse("$date {$request->punched_in_at}") : null;
        $request_out = null;

        if ($request->punched_out_at) {
            $request_out = Carbon::parse("$date {$request->punched_out_at}");

            if ($request_in && $request_out->lt($request_in)) {
                $request_out->addDay();
            }
        }

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => Auth::id(),
            'punched_in_at' => $request_in,
            'punched_out_at' => $request_out,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        if ($request->has('breaks')) {
            foreach ($request->breaks as $breakData) {
                if (!empty($breakData['punched_in_at']) || !empty($breakData['punched_out_at'])) {

                    $break_in = $breakData['punched_in_at'] ? Carbon::parse("$date {$breakData['punched_in_at']}") : null;
                    $break_out = $breakData['punched_out_at'] ? Carbon::parse("$date {$breakData['punched_out_at']}") : null;

                    if ($break_in && $request_in && $break_in->lt($request_in)) {
                        $break_in->addDay();
                    }

                    if ($break_out && $break_in && $break_out->lt($break_in)) {
                        $break_out->addDay();
                    }

                    $attendanceRequest->breakTimeRequests()->create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'break_time_id' => $breakData['id'] ?? null,
                        'punched_in_at' => $break_in,
                        'punched_out_at' => $break_out,
                    ]);
                }
            }
        }

        return redirect()->route('attendances.show', $id)->with('success', '修正申請を送信しました');

    }

    public function requestList(Request $request)
    {
        $user = auth()->user();
        
        if ($user->is_admin) {
            $currentTab = $request->query('tab', 'pending');
            $requests = AttendanceRequest::with('user', 'attendance')
                ->where('status', $currentTab)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admins.requests.index', compact('requests', 'currentTab'));
        }

        $currentTab = $request->query('tab', 'pending');
        $requests = AttendanceRequest::with('user', 'attendance')
            ->where('user_id', Auth::id())
            ->where('status', $currentTab)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('requests.index', compact('requests', 'currentTab'));
    }
}
