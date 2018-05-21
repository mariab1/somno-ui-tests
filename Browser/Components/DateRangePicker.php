<?php

namespace Tests\Browser\Components;

use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class DateRangePicker extends BaseComponent
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector(): string
    {
        return '';
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements(): array
    {
        return [
            '@date-start-field' => '.input-daterange input#date_start',
            '@date-end-field' => '.input-daterange input#date_end',
            '@month-list' => 'div.datepicker-months',
            '@day-list' => 'div.datepicker-days',
        ];
    }

    /**
     * Select the given date range
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @param  Carbon $startDate
     * @param  Carbon $endDate
     * @return void
     */
    public function selectDate(Browser $browser, Carbon $startDate, Carbon $endDate)
    {
        $browser->click('@date-start-field')
            ->click('.datepicker-days .datepicker-switch')
            ->click('.datepicker-months .datepicker-switch')
            ->click('.datepicker-years .focused')
            ->click('.datepicker-months .focused')
            ->within('@day-list', function (Browser $browser) use ($startDate) {
                $browser->click('.day');
            });

        $browser->click('@date-end-field')
            ->click('.datepicker-days .datepicker-switch')
            ->click('.datepicker-months .datepicker-switch')
            ->click('.datepicker-years .focused')
            ->click('.datepicker-months .focused')
            ->within('@day-list', function (Browser $browser) use ($endDate) {
                $browser->click($endDate->format('d'));
            });
    }
}
