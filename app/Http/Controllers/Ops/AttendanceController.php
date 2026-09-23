<?php

namespace App\Http\Controllers\Ops;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\UpdateAttendanceRequest;
use App\Models\AttendanceSession;
use App\Services\AttendanceSessionService;
use App\Services\OperationalCalendar;
use App\Support\DateLabel;
use App\Support\OperationalAccess;
use App\Support\WeekDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private OperationalAccess $access,
        private AttendanceSessionService $sessions,
        private OperationalCalendar $calendar,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->access->assertCanOperate($user);

        $today = now();
        $day = $today->isoWeekday();
        $isOffDay = $this->calendar->isOffDay($today);
        $todaySession = $isOffDay ? null : $this->sessions->ensureForDate($user, $today);

        $recent = AttendanceSession::query()
            ->whereDate('session_date', '<', $today->toDateString())
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('ops.attendance.index', [
            'todaySession' => $todaySession,
            'recent' => $recent,
            'todayDateLabel' => DateLabel::long($today),
            'dayLabel' => WeekDay::label($day),
            'isOffDay' => $isOffDay,
            'offDayMessage' => $isOffDay ? $this->calendar->offDayMessage($today) : null,
            'hasActiveSantri' => $this->access->activeSantriQuery()->exists(),
        ]);
    }

    public function show(Request $request, AttendanceSession $attendanceSession): View
    {
        $this->access->assertCanOperate($request->user());
        $this->sessions->syncMembers($attendanceSession);
        $attendanceSession->load(['attendances.santri.user']);

        return view('ops.attendance.show', [
            'session' => $attendanceSession,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function update(UpdateAttendanceRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $this->access->assertCanOperate($request->user());
        $this->sessions->save($attendanceSession, $request->validated('rows') ?? []);

        $hadir = collect($request->validated('rows') ?? [])
            ->contains(fn (array $row): bool => ($row['status'] ?? '') === AttendanceStatus::Hadir->value);

        if ($hadir) {
            return redirect()
                ->route('ops.setoran.create', ['sesi' => $attendanceSession->id])
                ->with('status', 'Absensi disimpan. Lanjut catat setoran santri yang hadir.');
        }

        return back()->with('status', 'Absensi disimpan. Tidak ada santri hadir untuk disetor.');
    }
}
