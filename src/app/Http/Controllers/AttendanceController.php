<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
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
}
