<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Http\Requests\AttendanceCorrectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $displayDate = Carbon::parse($date);

        $prevDate = $displayDate->copy()->subDay()->toDateString();
        $nextDate = $displayDate->copy()->addDay()->toDateString();

        $attendances = Attendance::with('user')
            ->where('work_date', $displayDate->toDateString())
            ->get();
            
            return view('admins.attendances.index', compact('attendances', 'displayDate', 'prevDate', 'nextDate'));
    }

     public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        $pendingRequest = AttendanceRequest::where('attendance_id', $id)
                            ->where('status', 'pending')
                            ->first();

        return view('attendances.show', compact('attendance', 'pendingRequest'));
    }

    public function update(AttendanceCorrectRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = Carbon::parse($attendance->work_date)->format('Y-m-d');

            DB::transaction(function () use ($request, $attendance, $date) {
                $attendance->update([
                    'punched_in_at' => $request->punched_in_at,
                    'punched_out_at' => $request->punched_out_at,
                ]);

                $attendance->breakTimes()->delete();

                if ($request->has('breaks')) {
                    foreach ($request->breaks as $break) {
                        if (!empty($break['punched_in_at']) && !empty($break['punched_out_at'])) {

                            $break_in = $break['punched_in_at'] ? Carbon::parse("$date {$break['punched_in_at']}") : null;
                            $break_out = $break['punched_out_at'] ? Carbon::parse("$date {$break['punched_out_at']}") : null;

                            $attendance->breakTimes()->create([
                                'punched_in_at' => $break_in,
                                'punched_out_at' => $break_out,
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('admins.attendances.index', $id)->with('success', '勤怠データを修正しました');
    }

    public function usersIndex()
    {
        $users = User::get();
        return view('admins.users.index', compact('users'));
    }

    public function usersShow(Request $request, $id)
    {
        $user = User::findOrFail($id);

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

        return view('admins.users.show', compact('user', 'attendances', 'displayDate', 'prevMonth', 'nextMonth'));
    }

    public function requestsShow($id)
    {
        $attendanceRequest = AttendanceRequest::with(['user', 'breakTimeRequests'])->findOrFail($id);

        return view('admins.requests.show', compact('attendanceRequest'));
    }

    public function approval(Request $request, $id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance')->findOrFail($id);

        $attendanceRequest->update([
            'status' => 'approved',
        ]);

        $attendance = $attendanceRequest->attendance;

        $date = Carbon::parse($attendance->work_date)->format('Y-m-d');

            DB::transaction(function () use ($request, $attendance, $date) {
                $attendance->update([
                    'punched_in_at' => $date . ' ' . $request->punched_in_at,
                    'punched_out_at' => $date . ' ' . $request->punched_out_at,
                ]);

                $attendance->breakTimes()->delete();

                if ($request->has('breaks')) {
                    foreach ($request->breaks as $break) {
                        if (!empty($break['punched_in_at']) && !empty($break['punched_out_at'])) {

                            $break_in = $break['punched_in_at'] ? Carbon::parse("$date {$break['punched_in_at']}") : null;
                            $break_out = $break['punched_out_at'] ? Carbon::parse("$date {$break['punched_out_at']}") : null;

                            $attendance->breakTimes()->create([
                                'punched_in_at' => $break_in,
                                'punched_out_at' => $break_out,
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('admins.requests.show', $id)->with('success', '修正申請を承認しました');
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $targetMonth = $request->input('month', date('Y-m'));

        $attendances = Attendance::where('user_id', $user->id)
                                ->where('work_date', 'like', "{$targetMonth}%")
                                ->orderBy('work_date', 'asc')
                                ->get();

        $fileName = "attendance_{$id}_{$targetMonth}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return response()->streamDownload(function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            stream_filter_prepend($stream, 'convert.iconv.utf-8/cp932//TRANSLIT');

            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計', '詳細']);

            foreach ($attendances as $attendance) {
                fputcsv($stream, [
                    $attendance->work_date->format('m/d'),
                    $attendance?->punched_in_at ? $attendance->punched_in_at->format('H:i') : '',
                    $attendance?->punched_out_at ? $attendance->punched_out_at->format('H:i') : '',
                    $attendance->getBreakTimeDisplay(),
                    $attendance->getWorkTimeDisplay(),
                    $attendance ? '詳細' : '',
                ]);
            }

            fclose($stream);
        }, $fileName, $headers);
    }
}
