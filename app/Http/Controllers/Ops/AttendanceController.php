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
use App\Support\Role;
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
        $halaqahIds = $this->access->halaqahQuery($user)->aktif()->pluck('id');
        $today = now();
        $day = $today->isoWeekday();
        $isOffDay = $this->calendar->isOffDay($today);

        $todaySlots = $isOffDay
            ? collect()
            : $this->sessions->ensureForDate($user, $halaqahIds, $today);

        $recent = AttendanceSession::query()
            ->with(['schedule.halaqah.ustaz'])
            ->whereHas('schedule', fn ($query) => $query->whereIn('halaqah_id', $halaqahIds))
            ->whereDate('session_date', '<', $today->toDateString())
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('ops.attendance.index', [
            'todaySlots' => $todaySlots,
            'recent' => $recent,
            'todayDateLabel' => DateLabel::long($today),
            'dayLabel' => WeekDay::label($day),
            'isOffDay' => $isOffDay,
            'offDayMessage' => $isOffDay ? $this->calendar->offDayMessage($today) : null,
            'hasAssignedHalaqah' => $halaqahIds->isNotEmpty(),
            'showUstazOnSlots' => $todaySlots->count() > 1
                && $user->hasAnyRole([Role::Ketua, Role::KetuaPengajar]),
        ]);
    }

    public function show(Request $request, AttendanceSession $attendanceSession): View
    {
        $attendanceSession->load(['schedule.halaqah.ustaz']);
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
