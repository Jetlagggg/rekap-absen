<?php

namespace Tests\Unit;

use App\Services\AttendancePenaltyCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttendancePenaltyCalculatorTest extends TestCase
{
    private const MONDAY = '2025-01-06';

    private const FRIDAY = '2025-01-03';

    private AttendancePenaltyCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new AttendancePenaltyCalculator;
    }

    public function test_check_in_within_tolerance_has_no_penalty(): void
    {
        $result = $this->calculator->calculate(self::MONDAY, '08:10', '16:00');

        $this->assertSame(0, $result['telat']);
        $this->assertSame(0, $result['cepat']);
        $this->assertSame(0.0, $result['total_persen']);
    }

    #[DataProvider('latePenaltyProvider')]
    public function test_late_penalty_tiers(string $jamMasuk, int $expectedMinutes, float $expectedPenalty): void
    {
        $result = $this->calculator->calculate(self::MONDAY, $jamMasuk, '16:00');

        $this->assertSame($expectedMinutes, $result['telat']);
        $this->assertSame(0, $result['cepat']);
        $this->assertSame($expectedPenalty, $result['total_persen']);
    }

    /**
     * @return array<string, array{string, int, float}>
     */
    public static function latePenaltyProvider(): array
    {
        return [
            'satu menit terlambat' => ['08:11', 11, 0.5],
            'tiga puluh menit terlambat' => ['08:30', 30, 0.5],
            'tiga puluh satu menit terlambat' => ['08:31', 31, 1.0],
            'enam puluh menit terlambat' => ['09:00', 60, 1.0],
            'enam puluh satu menit terlambat' => ['09:01', 61, 1.25],
            'sembilan puluh menit terlambat' => ['09:30', 90, 1.25],
            'lebih dari sembilan puluh menit terlambat' => ['09:31', 91, 1.5],
        ];
    }

    public function test_early_check_out_adds_one_percent_penalty(): void
    {
        $result = $this->calculator->calculate(self::MONDAY, '07:55', '15:59');

        $this->assertSame(0, $result['telat']);
        $this->assertSame(1, $result['cepat']);
        $this->assertSame(1.0, $result['total_persen']);
    }

    public function test_check_out_at_standard_time_has_no_penalty(): void
    {
        $result = $this->calculator->calculate(self::MONDAY, '07:55', '16:00');

        $this->assertSame(0, $result['cepat']);
        $this->assertSame(0.0, $result['total_persen']);
    }

    public function test_friday_uses_eleven_am_as_the_standard_check_out_time(): void
    {
        $onTime = $this->calculator->calculate(self::FRIDAY, '07:55', '11:00');
        $early = $this->calculator->calculate(self::FRIDAY, '07:55', '10:59');
        $afternoon = $this->calculator->calculate(self::FRIDAY, '07:55', '16:00');

        $this->assertSame(0.0, $onTime['total_persen']);
        $this->assertSame(1.0, $early['total_persen']);
        $this->assertSame(1, $early['cepat']);
        $this->assertSame(0.0, $afternoon['total_persen']);
    }

    public function test_missing_check_out_time_adds_one_and_a_half_percent(): void
    {
        $result = $this->calculator->calculate(self::MONDAY, '07:55', null);

        $this->assertSame(0, $result['telat']);
        $this->assertSame(0, $result['cepat']);
        $this->assertSame(1.5, $result['total_persen']);
    }

    public function test_late_and_early_check_out_penalties_are_added_together(): void
    {
        $result = $this->calculator->calculate(self::MONDAY, '08:20', '15:30');

        $this->assertSame(20, $result['telat']);
        $this->assertSame(30, $result['cepat']);
        $this->assertSame(1.5, $result['total_persen']);
    }

    public function test_late_minutes_are_counted_from_eight_am_not_from_the_tolerance_limit(): void
    {
        $result = $this->calculator->calculate(self::MONDAY, '08:15', '16:00');

        $this->assertSame(15, $result['telat']);
        $this->assertSame(0.5, $result['total_persen']);
    }
}
