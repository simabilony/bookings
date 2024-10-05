<?php

namespace Tests\Unit;
use PHPUnit\Framework\TestCase;

class TimeConversionTest extends TestCase
{
    public function test_correctly_transforms_utc_to_any_date(): void
    {
        $this->assertEquals('01/01/2023', toUserDate('2023-01-01'));
        $this->assertEquals('12/31/2022', toUserDate('2023-01-01', timezone: 'America/New_York'));
        $this->assertEquals('01/01/2023', toUserDate('2023-01-01', timezone: 'Europe/London'));

        // DST tests
        $this->assertEquals('07/01/2021', toUserDate('2021-07-01'));
        $this->assertEquals('06/30/2021', toUserDate('2021-07-01', timezone: 'America/New_York'));
        $this->assertEquals('07/01/2021', toUserDate('2021-07-01', timezone: 'Europe/London'));

        // This can be expanded to include more tests and edge-cases that we encounter
    }

    public function test_correctly_transforms_utc_to_any_time(): void
    {
        $this->assertEquals('12:00 AM', toUserTime('00:00:00'));
        $this->assertEquals('8:00 PM', toUserTime('00:00:00', timezone: 'America/New_York'));
        $this->assertEquals('1:00 AM', toUserTime('00:00:00', timezone: 'Europe/London'));

        // This can be expanded to include more tests and edge-cases that we encounter
    }

    public function test_correctly_transforms_utc_to_any_date_time(): void
    {
        $this->assertEquals('01/01/2023 12:00 AM', toUserDateTime('2023-01-01 00:00:00'));
        $this->assertEquals('12/31/2022 7:00 PM', toUserDateTime('2023-01-01 00:00:00', timezone: 'America/New_York'));
        $this->assertEquals('01/01/2023 12:00 AM', toUserDateTime('2023-01-01 00:00:00', timezone: 'Europe/London'));

        // DST tests
        $this->assertEquals('07/01/2021 12:00 AM', toUserDateTime('2021-07-01 00:00:00'));
        $this->assertEquals('06/30/2021 8:00 PM', toUserDateTime('2021-07-01 00:00:00', timezone: 'America/New_York'));
        $this->assertEquals('07/01/2021 1:00 AM', toUserDateTime('2021-07-01 00:00:00', timezone: 'Europe/London'));

        // This can be expanded to include more tests and edge-cases that we encounter
    }

    public function test_correctly_transforms_user_date_to_utc(): void
    {
        $this->assertEquals('2023-01-01', fromUserDate('01/01/2023'));
        $this->assertEquals('2022-12-31', fromUserDate('12/31/2022', timezone: 'America/New_York'));
        $this->assertEquals('2023-01-01', fromUserDate('01/01/2023', timezone: 'Europe/London'));

        // DST tests
        $this->assertEquals('2021-07-01', fromUserDate('07/01/2021'));
        $this->assertEquals('2021-06-30', fromUserDate('06/30/2021', timezone: 'America/New_York'));
        $this->assertEquals('2021-06-30', fromUserDate('07/01/2021', timezone: 'Europe/London'));

        // This can be expanded to include more tests and edge-cases that we encounter
    }

    public function test_correctly_transforms_users_time_to_utc(): void
    {
        $this->assertEquals('00:00:00', fromUserTime('12:00 AM'));
        $this->assertEquals('00:00:00', fromUserTime('8:00 PM', timezone: 'America/New_York'));
        $this->assertEquals('00:00:00', fromUserTime('1:00 AM', timezone: 'Europe/London'));

        // This can be expanded to include more tests and edge-cases that we encounter
    }

    public function test_correctly_transforms_user_date_time_to_utc(): void
    {
        $this->assertEquals('2023-01-01 00:00:00', fromUserDateTime('01/01/2023 12:00 AM'));
        $this->assertEquals('2023-01-01 00:00:00', fromUserDateTime('12/31/2022 7:00 PM', timezone: 'America/New_York'));
        $this->assertEquals('2023-01-01 01:00:00', fromUserDateTime('01/01/2023 1:00 AM', timezone: 'Europe/London'));

        // DST tests
        $this->assertEquals('2021-07-01 00:00:00', fromUserDateTime('07/01/2021 12:00 AM'));
        $this->assertEquals('2021-07-01 00:00:00', fromUserDateTime('06/30/2021 8:00 PM', timezone: 'America/New_York'));
        $this->assertEquals('2021-07-01 00:00:00', fromUserDateTime('07/01/2021 1:00 AM', timezone: 'Europe/London'));

        // This can be expanded to include more tests and edge-cases that we encounter
    }

    public function test_correctly_transforms_user_date_time_from_settings_to_utc(): void
    {
        $this->assertEquals('2023-01-01 00:00:00', fromUserDateTime('01/01/2023 12:00 AM'));

        date_default_timezone_set('America/New_York');
        $this->assertEquals('2023-01-01 00:00:00', fromUserDateTime('12/31/2022 7:00 PM'));

        date_default_timezone_set('Europe/London');
        $this->assertEquals('2023-01-01 01:00:00', fromUserDateTime('01/01/2023 1:00 AM'));

        // DST tests
        date_default_timezone_set('UTC');
        $this->assertEquals('2021-07-01 00:00:00', fromUserDateTime('07/01/2021 12:00 AM'));

        date_default_timezone_set('America/New_York');
        $this->assertEquals('2021-07-01 00:00:00', fromUserDateTime('06/30/2021 8:00 PM'));

        date_default_timezone_set('Europe/London');
        $this->assertEquals('2021-07-01 00:00:00', fromUserDateTime('07/01/2021 1:00 AM'));

        // This can be expanded to include more tests and edge cases that we encounter
    }
}
