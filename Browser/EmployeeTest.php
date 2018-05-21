<?php

namespace Tests\Browser;

use App\Models\User;
use Tests\Browser\Helpers\TestHelper;
use Tests\Browser\Pages\Employees;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\DatabaseMigrations;

class EmployeeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @group user
     * @throws \Throwable
     */
    public function testAddEmployee()
    {
        $user = TestHelper::createAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Employees())
                ->waitFor('.btn-group')
                ->press('Add a new employee')
                ->assertSee('Personal details')
                ->press('Add user')
                ->seeNotification('The given data was invalid')
                ->waitUntilMissing('@notification')
                ->type('#formHorizontalForename', 'Peter')
                ->type('#formHorizontalSurname', 'Pan')
                ->type('#formHorizontalInternalId', '1')
                ->type('.formHorizontalDateBirth input', '24.02.1992')
                ->type('.formHorizontalDateJoined input', '24.02.2016')
                ->type('#formHorizontalSkype', 'peter.pan')
                ->type('#formHorizontalPhone', '55511100')
                ->check('is_external')
                ->press('Add user')
                ->seeNotification('New staff member added!');
        });
    }

    /**
     * @group user
     * @throws \Throwable
     */
    public function testEditEmployee()
    {
        $user = TestHelper::createAdminUser();
        factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Employees())
                ->waitFor('.fa-pencil')
                ->press('tr:nth-child(2) .fa-pencil')
                ->waitFor('.form-horizontal', 10)
                ->type('#formHorizontalAddress', 'Street')
                ->type('#formHorizontalSkype', 'b.b')
                ->type('#formHorizontalPhone', '55511100')
                ->press('Save')
                ->seeNotification('User information saved!');
        });
    }

    /**
     * @group user
     * @throws \Throwable
     */
    public function testDeactivateEmployee()
    {
        $user = TestHelper::createAdminUser();
        factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Employees())
                ->waitFor('.row-actions')
                ->press('tr:nth-child(2) .fa-pencil')
                ->waitFor('.form-horizontal', 15)
                ->press('#edit-user-tab-settings')
                ->waitFor('#btn-deactivate-user')
                ->press('Deactivate user')
                ->seeNotification('user account has been disabled')
                ->press('Close')
                ->waitUntilMissing('tr:nth-child(2)')
                ->assertDontSeeIn('.employee-cell', 'B');
        });
    }
}
