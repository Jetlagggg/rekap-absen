<?php

namespace App\Services;

use Carbon\Carbon;

class AttendancePenaltyCalculator
{
    public const TOLERANCE_MINUTES = 10;

    public const CHECK_IN_HOUR = 8;

    public const FRIDAY_CHECK_OUT_HOUR = 11;

    public const CHECK_OUT_HOUR = 16;

    /**
     * @return array{telat: int, cepat: int, total_persen: float}
     */
    public function calculate(string $tanggal, string $jamMasuk, ?string $jamPulang): array
    {
        $isJumat = Carbon::parse($tanggal)->isFriday();

        $jamMasukResmi = Carbon::parse($tanggal)->setTime(self::CHECK_IN_HOUR, 0);
        $jamBatasToleransi = $jamMasukResmi->copy()->addMinutes(self::TOLERANCE_MINUTES);
        $jamPulangKantor = Carbon::parse($tanggal)->setTime(
            $isJumat ? self::FRIDAY_CHECK_OUT_HOUR : self::CHECK_OUT_HOUR,
            0
        );

        $waktuMasuk = Carbon::parse("$tanggal $jamMasuk");
        $waktuPulang = $jamPulang ? Carbon::parse("$tanggal $jamPulang") : null;

        $totalPotongan = 0;
        $telatMenit = 0;
        $cepatMenit = 0;

        if ($waktuMasuk->gt($jamBatasToleransi)) {

            $telatMenit = (int) $jamMasukResmi->diffInMinutes($waktuMasuk);

            if ($telatMenit <= 30) {
                $totalPotongan += 0.5;
            } elseif ($telatMenit <= 60) {
                $totalPotongan += 1.0;
            } elseif ($telatMenit <= 90) {
                $totalPotongan += 1.25;
            } else {
                $totalPotongan += 1.5;
            }
        }

        if ($waktuPulang) {

            if ($waktuPulang->lt($jamPulangKantor)) {
                $cepatMenit = (int) $waktuPulang->diffInMinutes($jamPulangKantor);
                $totalPotongan += 1.0;
            }

        } elseif ($jamMasuk) {

            $totalPotongan += 1.5;
        }

        return [
            'telat' => $telatMenit,
            'cepat' => $cepatMenit,
            'total_persen' => (float) $totalPotongan,
        ];
    }
}
