<?php

namespace Tests\Browser;

use App\Models\LeaveAllowance;
use App\Models\LeaveType;
use App\Models\LeaveTypeApprovalChain\LeaveTypeApprovalChain;
use App\Models\Request;
use Tests\Browser\Pages\Dashboard;
use Tests\DatabaseMigrations;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Carbon\Carbon;

class DeclineRequestTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @group decline
     * @throws \Throwable
     */
    public function testIfSubstituteUserCanCancelRequestWithComment()
    {
        $leaveType = factory(LeaveType::class)->create();
        $approvalChain = (new LeaveTypeApprovalChain)->initializeChain($leaveType);
        /** @var Request $request */
        $request = factory(Request::class)->create([
            'type_id' => $leaveType->id,
            'date_start' => Carbon::today()->addDay()->format('Y-m-d'),
            'date_end' => Carbon::today()->addDays(2)->format('Y-m-d'),
            'status' => 'waiting_substitute'
        ]);
        $substitute = $request->substituteUser;
        $request->generateRequestApprovalSchedule([
            $approvalChain->steps[0]->id => $substitute->id
        ]);
        $approvalStep = $request->approvalChain->steps()->first();
        $approvalStep->status = 'waiting_approval';
        $approvalStep->save();
        factory(LeaveAllowance::class)->create([
            'user_id' => $request->user->id,
            'leave_type_id' => $request->type_id
        ]);

        $this->browse(function (Browser $browser) use ($substitute) {
            $browser->loginAs($substitute)
                ->visit(new Dashboard())
                ->waitForText('Requests looking for your attention')
                ->press('Decline')
                ->waitForText('Decline request')
                ->with('.modal', function (Browser $modal) {
                    $modal->press('Decline');
                })
                ->seeNotification('Leave request declined');
        });
    }
}
