<?php

namespace App\Http\Controllers\Ops;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\UpdateAttendanceRequest;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Services\AttendanceSessionService;
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
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $halaqahIds = $this->access->halaqahQuery($user)->aktif()->pluck('id');
        $today = now()->toDateString();
        $day = now()->isoWeekday();

        $todaySlots = Schedule::query()
            ->with(['halaqah.ustaz', 'location'])
            ->with(['sessions' => fn ($query) => $query->whereDate('session_date', $today)])
            ->whereIn('halaqah_id', $halaqahIds)
            ->where('is_active', true)
            ->where('day_of_week', $day)
            ->orderBy('start_time')
            ->get();

        $recent = AttendanceSession::query()
            ->with(['schedule.halaqah', 'schedule.location'])
            ->whereHas('schedule', fn ($query) => $query->whereIn('halaqah_id', $halaqahIds))
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('ops.attendance.index', [
            'todaySlots' => $todaySlots,
            'recent' => $recent,
            'dayLabel' => WeekDay::label($day),
        ]);
    }

    public function open(Request $request, Schedule $schedule): RedirectResponse
    {
        $schedule->load('halaqah');
        $this->access->assertHalaqah($request->user(), $schedule->halaqah);

        $session = $this->sessions->open($schedule, $request->user(), now());

        return redirect()
            ->route('ops.attendance.show', $session)
            ->with('status', 'Sesi absensi siap diisi.');
    }

    public function show(Request $request, AttendanceSession $attendanceSession): View
    {
        $attendanceSession->load(['schedule.halaqah.ustaz', 'schedule.location']);
        $this->access->assertHalaqah($request->user(), $attendanceSession->schedule->halaqah);
        $this->sessions->syncMembers($attendanceSession);
        $attendanceSession->load(['attendances.santri.user']);

        return view('ops.attendance.show', [
            'session' => $attendanceSession,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function update(UpdateAttendanceRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $attendanceSession->load('schedule.halaqah');
        $this->access->assertHalaqah($request->user(), $attendanceSession->schedule->halaqah);
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
