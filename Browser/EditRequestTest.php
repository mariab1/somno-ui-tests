<?php

namespace Tests\Browser;

use App\Models\LeaveTypeApprovalChain\LeaveTypeApprovalChain;
use App\Models\Request;
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

class EditRequestTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @group request
     * @throws \Throwable
     */
    public function testEditPeriodAndSubstitute()
    {
        $user = TestHelper::createAdminUser();
        $leaveType = factory(LeaveType::class)->create();
        $approvalChain = (new LeaveTypeApprovalChain)->initializeChain($leaveType);
        factory(LeaveAllowance::class)->create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id
        ]);
        $substituteUser = factory(User::class)->create();

        /** @var Request $request */
        $request = factory(Request::class)->create([
            'type_id' => $leaveType->id,
            'user_id' => $user->id,
            'date_start' => Carbon::today()->addDay()->format('Y-m-d'),
            'date_end' => Carbon::today()->addDays(2)->format('Y-m-d'),
            'status' => 'waiting_substitute'
        ]);
        $substitute = $request->substituteUser;
        $request->generateRequestApprovalSchedule([
            $approvalChain->steps[0]->id => $substitute->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $substituteUser) {
            $browser->loginAs($user)
                ->visit(new Dashboard())
                ->click('@editButton')
                ->waitFor('.modal.inmodal.in')
                ->assertSee('Edit request')
                ->type('date_start', Carbon::today()->addDays(4)->format('d.m.Y'))
                ->type('date_end', Carbon::today()->addDays(10)->format('d.m.Y'))
                ->within(new ChosenSelect('#request-substitute'), function (Browser $browser) use ($substituteUser) {
                    $browser->selectValue($substituteUser->getRawName());
                })
                ->press('Send for approval')
                ->waitForText('Leave request added!', 30);
        });
    }
}
