<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function timeStamp()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', $today)
                                ->first();

        return view('attendances.time-stamp', compact('attendance'));
    }

    public function punch(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'work_date' => $today,
        ]);

        $msg = null;

        if ($request->type === 'in') {
            if ($attendance->punched_in_at) return back()->with('error', '出勤済みです');
            $attendance->punched_in_at = $now;
        } elseif ($request->type === 'break_in') {
            $attendance->breakTimes()->create([
                'punched_in_at' => $now,
            ]);
        } elseif ($request->type === 'break_out') {
            $latestBreak = $attendance->breakTimes()
                ->whereNull('punched_out_at')
                ->latest()
                ->first();
            if (!$latestBreak) {
                return back()->with('error', '休憩が開始されていません');
            }
            $latestBreak->update([
                'punched_out_at' => $now,
            ]);
        } else {
            if (!$attendance->punched_in_at || $attendance->punched_out_at) return back()->with('error', '不正な操作です');
            $attendance->punched_out_at = $now;
            $msg = "お疲れ様でした。";
        }

        $attendance->save();

        if ($msg) {
            return back()->with('success', $msg);
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

        return view('attendances.show', compact('attendance'));
    }

    public function storeRequest(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = $attendance->work_date->format('Y-m-d');

        $request_in = $request->punched_in_at ? Carbon::parse("$date {$request->punched_in_at}") : null;
        $request_out = $request->punched_out_at ? Carbon::parse("$date {$request->punched_out_at}") : null;

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => Auth::id(),
            'punched_in_at' => $request_in,
            'punched_out_at' => $request_out,
            'remarks' => $request->remarks,
            'approved' => false,
        ]);

        if ($request->has('breaks')) {
            foreach ($request->breaks as $breakData) {
                if (!empty($breakData['punched_in_at']) || !empty($breakData['punched_out_at'])) {

                    $break_in = $breakData['punched_in_at'] ? Carbon::parse("$date {$breakData['punched_in_at']}") : null;
                    $break_out = $breakData['punched_out_at'] ? Carbon::parse("$date {$breakData['punched_out_at']}") : null;

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
}
