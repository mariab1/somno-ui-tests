<?php

namespace Tests\Browser;

use App\Models\LeaveTypeApprovalChain\LeaveTypeApprovalChain;
use App\Models\User;
use App\Models\LeaveAllowance;
use App\Models\LeaveType;
use Tests\Browser\Components\ChosenSelect;
use Tests\Browser\Helpers\TestHelper;
use Tests\Browser\Pages\Dashboard;
use Tests\DatabaseMigrations;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Carbon\Carbon;

class AddRequestTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @group request
     * @throws \Throwable
     */
    public function testAddNewRequest()
    {
        $user = TestHelper::createAdminUser();
        $leaveType = factory(LeaveType::class)->create();
        factory(LeaveAllowance::class)->create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Dashboard())
                ->press('New Request')
                ->waitFor('.modal.inmodal.in')
                ->assertSee('New Request')
                ->clickLink('+ add comment')
                ->type('#comment', 'Available by phone in case of urgent matters')
                ->assertMissing('.days-current-leave')
                ->type('date_start', Carbon::today()->addDays(8)->format('d.m.Y'))
                ->type('date_end', Carbon::today()->addDays(2)->format('d.m.Y'))
                ->select('salary_type', '1')
                ->press('Send for approval')
                ->seeErrorMessage('Date end cannot be earlier than date start')
                ->type('date_start', Carbon::today()->addDays(2)->format('d.m.Y'))
                ->type('date_end', Carbon::today()->addDays(8)->format('d.m.Y'))
                ->press('Save')
                ->waitForText('Leave request added!', 15);
        });
    }

    /**
     * @group request
     * @throws \Throwable
     */
    public function testAddNewRequestWithApprovalChain()
    {
        $user = TestHelper::createAdminUser();
        $leaveType = factory(LeaveType::class)->create();
        (new LeaveTypeApprovalChain)->initializeChain($leaveType);
        factory(LeaveAllowance::class)->create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id
        ]);
        $substituteUser = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user, $substituteUser) {
            $browser->loginAs($user)
                ->visit(new Dashboard())
                ->press('New Request')
                ->waitFor('.modal.inmodal.in')
                ->assertSee('New Request')
                ->assertMissing('.days-current-leave')
                ->type('date_start', Carbon::today()->addDays(2)->format('d.m.Y'))
                ->type('date_end', Carbon::today()->addDays(4)->format('d.m.Y'))
                ->select('salary_type', '1')
                ->within(new ChosenSelect('#request-substitute'), function (Browser $browser) use ($substituteUser) {
                    $browser->selectValue($substituteUser->getRawName());
                })
                ->press('Send for approval')
                ->waitForText('Leave request added!', 30);
        });
    }
}
