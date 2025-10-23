<?php


namespace App\Traits;

use App\Models\ScheduleDeleteSlot;
use App\Models\TourSchedule;
use App\Models\TourScheduleRepeats;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

trait TourScheduleHelper
{
    protected function updateTourScheduleMeta(int $tourId)
    {
        $disabledData = $this->getDisabledTourDates($tourId);
        // dd($disabledData);
        // return true;
        \DB::table('tour_schedule_meta')->where('tour_id', $tourId)->delete();
        foreach ($disabledData['per_schedule'] as $scheduleId => $meta) {
            \DB::table('tour_schedule_meta')->updateOrInsert(
                [
                    'tour_id' => $tourId,
                    'schedule_id' => NULL,
                ],
                [
                    'start_date'     => $disabledData['start_date'],
                    'until_date'     => $disabledData['until_date'],
                    'disabled_dates' => json_encode($disabledData['disabled_tour_dates']),
                    'updated_at'     => now(),
                ]
            );
        }
    }
    private function getDisabledTourDates(int $tourId, $schedules = null): array
    {
        if ($schedules === null) {
            $schedules = TourSchedule::where('tour_id', $tourId)
                ->orderBy('session_start_date')
                ->get();
        }

        if ($schedules->isEmpty()) {
            return [
                'disabled_tour_dates' => [],
                'per_schedule' => [],
            ];
        }

        $globalStart = Carbon::parse($schedules->min('session_start_date'));
        $globalEnd   = Carbon::parse($schedules->max('until_date'));

        // ✅ Prefetch deleted slots once
        // $storeDeleteSlot = $this->fetchDeletedSlot($tourId);
        // $deleteTypes = collect($storeDeleteSlot)->pluck('delete_type');

        // if ($deleteTypes->contains('all')) {
        //     $globalEnd = Carbon::yesterday();
        //     return [
        //         'disabled_tour_dates' => [], // same as original
        //         'per_schedule' => [],
        //         'start_date' => $globalStart->toDateString(),
        //         'until_date' => $globalEnd->toDateString(),
        //     ];
        // } elseif ($storeDeleteSlot->where('delete_type', 'after')->isNotEmpty()) {
        //     $minAfterDate = $storeDeleteSlot
        //         ->where('delete_type', 'after')
        //         ->pluck('slot_date')
        //         ->min();

        //     $globalEnd = Carbon::parse($minAfterDate);
        // }

        // ✅ Prefetch all repeats for all schedules in one query
        $scheduleIds = $schedules->pluck('id')->toArray();
        $allRepeats = TourScheduleRepeats::whereIn('tour_schedule_id', $scheduleIds)
            ->get()
            ->groupBy('tour_schedule_id');

        $perSchedule = [];
        $scheduleMeta = [];
        $today = Carbon::today();

        // Collect per schedule availability
        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->session_start_date);
            $end   = Carbon::parse($schedule->until_date);

            // ✅ Pass in preloaded repeats + deleted slots
            $scheduleRepeats = $allRepeats->get($schedule->id, collect())->groupBy('day')->all();
            $customDisabled  = $this->calculateDisabledDates($schedule, $today, $scheduleRepeats);

            $perSchedule[$schedule->id] = [
                'start_date' => $start->toDateString(),
                'until_date' => $end->toDateString(),
                'disabled'   => $customDisabled,
            ];

            $scheduleMeta[$schedule->id] = [
                'start' => $start,
                'end' => $end,
                'disabled' => $customDisabled,
            ];
        }

        // ✅ Compute overall disabled dates (same as original)
        $overallDisabled = [];
        $cursor = $globalStart->copy();
        while ($cursor->lte($globalEnd)) {
            $date = $cursor->toDateString();

            $openSomewhere = false;
            foreach ($scheduleMeta as $meta) {
                if ($cursor->between($meta['start'], $meta['end'])) {
                    // If inside this schedule range, but NOT in its disabled list → it's open
                    if (!in_array($date, $meta['disabled'])) {
                        $openSomewhere = true;
                        break;
                    }
                }
            }

            if (!$openSomewhere) {
                $overallDisabled[] = $date;
            }

            $cursor->addDay();
        }

        return [
            'disabled_tour_dates' => $overallDisabled,
            'per_schedule' => collect($perSchedule)->map(function ($data) {
                $data['disabled'] = array_slice($data['disabled'], 0, 90);
                return $data;
            })->toArray(),
            'start_date' => $globalStart->toDateString(),
            'until_date' => $globalEnd->toDateString(),
        ];
    }


    private function calculateDisabledDates($schedule, Carbon $today, $repeats): array
    {
        $start = Carbon::parse($schedule->session_start_date)->max($today);
        $end   = Carbon::parse($schedule->until_date)->endOfDay();

        if ($start->gt($end)) {
            return [];
        }

        $disabled = [];
        $period = CarbonPeriod::create($start->toDateString(), '1 day', $end->toDateString());

        foreach ($period as $date) {

            if (!$this->isDateAvailable($schedule, $date, $repeats)) {
                $disabled[] = $date->toDateString();
            }
        }
       
        return $disabled;
    }


    private function isDateAvailable($schedule, $date, array $repeatsByDay = [], $storeDeletedSlots = []): bool
    {
        // Hard bounds
        $startDate = Carbon::parse($schedule->session_start_date)->startOfDay();
        $endDate   = Carbon::parse($schedule->until_date)->endOfDay();
        if (!$date->between($startDate, $endDate)) {
            return false;
        }

        // Config
        $repeatType  = strtoupper((string)$schedule->repeat_period); 
        $repeatUnit  = max(1, (int)($schedule->repeat_period_unit ?? 1));
        $durationMin = $this->minutesFromUnit($schedule->estimated_duration_num, $schedule->estimated_duration_unit);
        $noticeMin   = $this->minutesFromUnit($schedule->minimum_notice_num, $schedule->minimum_notice_unit);

        $earliestAllow = now()->copy()->addMinutes($noticeMin);
        $allDay = (bool)($schedule->sesion_all_day ?? false);
        $dayStr = $date->toDateString();

        $at = fn(string $time) => Carbon::parse("{$dayStr} {$time}");

        $windowOk = function (Carbon $slotStart, Carbon $slotEnd) use ($earliestAllow): bool {
            if ($slotEnd->lt($slotStart)) return false;
            return $slotEnd->gte($earliestAllow);
        };

        $available = false;
        $slots = [];

        if ($repeatType === 'NONE') {
            if (!$date->isSameDay($startDate)) return false;
            $slotStart = $allDay
                ? $date->copy()->startOfDay()
                : $at($schedule->session_start_time ?? '00:00');
            $slotEnd = $allDay
                ? $date->copy()->endOfDay()
                : ($schedule->session_start_time
                    ? $at($schedule->session_start_time)
                    : $slotStart->copy()->addMinutes(0));

            $available = $windowOk($slotStart, $slotEnd);

        } elseif ($repeatType === 'DAILY') {
            $daysSinceStart = $startDate->diffInDays($date);
            if ($daysSinceStart % $repeatUnit === 0) {
                $slotStart = $at($schedule->session_start_time ?? '00:00');
                $slotEnd   = $slotStart->copy()->addMinutes(0);
                $available = $allDay
                    ? $windowOk($date->copy()->startOfDay(), $date->copy()->endOfDay())
                    : $windowOk($slotStart, $slotEnd);
            }

        } elseif ($repeatType === 'WEEKLY') {
            $weeksSinceStart = $startDate->diffInWeeks($date);
            if ($weeksSinceStart % $repeatUnit === 0) {
                $dayName = $date->format('l');
                $entries = $repeatsByDay[$dayName] ?? [];

                foreach ($entries as $rep) {
                    $slotStart = $at($rep->start_time ?? ($schedule->session_start_time ?? '00:00'));
                    $slotEnd   = $at($rep->end_time   ?? ($schedule->session_end_time   ?? '23:59'));
                    if ($windowOk($slotStart, $slotEnd)) {
                        $available = true;
                        break;
                    }
                }
            }

        } elseif ($repeatType === 'MONTHLY') {
            $monthsSinceStart = $startDate->diffInMonths($date);
            if ($monthsSinceStart % $repeatUnit === 0 && $date->day === $startDate->day) {
                $slotStart = $at($schedule->session_start_time ?? '00:00');
                $slotEnd   = $at($schedule->session_end_time   ?? '23:59');
                $available = $windowOk($slotStart, $slotEnd);
            }

        } elseif ($repeatType === 'YEARLY') {
            $yearsSinceStart = $startDate->diffInYears($date);
            if ($yearsSinceStart % $repeatUnit === 0 &&
                $date->day === $startDate->day &&
                $date->month === $startDate->month) {
                $slotStart = $at($schedule->session_start_time ?? '00:00');
                $slotEnd   = $slotStart->copy()->addMinutes(max(1, $durationMin));
                $available = $windowOk($slotStart, $slotEnd);
            }

        } elseif (in_array($repeatType, ['HOURLY', 'MINUTELY'])) {
            $dayName = $date->format('l');
            $entries = $repeatsByDay[$dayName] ?? [];

            if($repeatType == 'HOURLY'){
                $repeatUnit = $repeatUnit * 60;
            }

            foreach ($entries as $rep) {
                $slotStart0 = $at($rep->start_time);
                $slotEnd    = $at($rep->end_time);
                if (!$slotEnd->gt($slotStart0)) continue;

                $m0 = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false)));
                $m  = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));

                $candidate = $slotStart0->copy()->addMinutes($m);
                while ($candidate->lte($slotEnd)) {
                    $slots[] = $candidate->copy();
                    $candidate->addMinutes($repeatUnit);
                }
            }
            $available = count($slots) > 0;
        }

        // === Apply deletions ===
        if ($available) {
            $response = [
                'data' => [
                    $dayStr => !empty($slots) ? $slots : [$at($schedule->session_start_time ?? '00:00')]
                ]
            ];

            // $deleted = $this->fetchDeletedSlot($schedule->tour_id);
            // $response = $this->applySlotDeletions($response, $storeDeletedSlots);

            return !empty($response['data'][$dayStr]);
        }

        return false;
    }

    public function fetchDeletedSlot($id)
    {

        return ScheduleDeleteSlot::where('tour_id', $id)->get();

        return response()->json(['success' => true, 'message' => 'Slot saved successfully']);
    }

    private function minutesFromUnit(?int $num, ?string $unit): int
    {
        $num  = (int)($num ?? 0);
        $unit = strtolower((string)$unit);

        return match ($unit) {
            'minute', 'minutes' => $num,
            'hour', 'hours'     => $num * 60,
            'day', 'days',
            'daily'             => $num * 60 * 24,
            'month', 'months',
            'monthly'           => $num * 60 * 24 * 30,   // same as your code
            'year', 'years',
            'yearly'            => $num * 60 * 24 * 365,  // same spirit as your code
            default             => 0
        };
    }

    function normalizeTime(string $time): string
    {
        return date("H:i", strtotime($time));
    }

/**
 * Sort slots chronologically (keeps AM/PM format)
 */
    function sortSlots(array $slots): array
    {
        usort($slots, function ($a, $b) {
            return strtotime($a) <=> strtotime($b);
        });
        return $slots;
    }
}