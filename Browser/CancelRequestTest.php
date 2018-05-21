<?php

namespace Tests\Browser;

use App\Models\LeaveAllowance;
use App\Models\Request;
use Tests\Browser\Pages\Dashboard;
use Tests\DatabaseMigrations;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Carbon\Carbon;

class CancelRequestTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @group cancel
     * @throws \Throwable
     */
    public function testUserCanCancelHisFutureLeaveRequest()
    {
        $request = factory(Request::class)->create([
            'date_start' => Carbon::today()->addDay()->format('Y-m-d'),
            'date_end' => Carbon::today()->addDays(2)->format('Y-m-d')
        ]);
        factory(LeaveAllowance::class)->create([
            'user_id' => $request->user->id,
            'leave_type_id' => $request->type_id
        ]);
        $user = $request->user;

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Dashboard())
                ->assertSee('Current and upcoming absences')
                ->click('#upcoming-absences .btn-xs')
                ->waitFor('@confirmDialog')
                ->press('Yes, cancel my request')
                ->seeNotification('Leave request was cancelled!')
                ->refresh()
                ->assertVisible('.leave-status.default');
        });
    }
}
