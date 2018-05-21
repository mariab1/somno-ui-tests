<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Department;
use Tests\Browser\Helpers\TestHelper;
use Tests\Browser\Pages\Departments;
use Tests\DatabaseMigrations;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class DepartmentTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @var User
     */
    protected static $user;

    /**
     * @group departments
     * @throws \Throwable
     */
    public function testAddDepartment()
    {
        $user = TestHelper::createAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Departments())
                ->waitFor('a.btn-lg')
                ->clickLink('Click to add your first department')
                ->assertSee('Department info')
                ->press('Close')
                ->assertMissing('.modal')
                ->press('Add department')
                ->assertSee('Department info')
                ->press('@addDepartmentButton')
                ->seeNotification('The given data was invalid')
                ->type('#department-name', 'Department 1')
                ->type('#department-description', 'It is a department no 1')
                ->press('Add next')
                ->click('.Select--single')
                ->type('.Select-input input', $user->getRawName())
                ->click('.Select-option.is-focused')
                ->press('.btn-add-more')
                ->type('#department-name', 'Department 2')
                ->type('#department-description', 'It is a department no 2')
                ->press('@addDepartmentButton')
                ->waitFor('.ibox-title')
                ->assertSee('It is a department no 2');
        });
    }

    /**
     * @group departments
     * @throws \Throwable
     */
    public function testEditDepartment()
    {
        $user = TestHelper::createAdminUser();
        $department = factory(Department::class)->create([
            'name' => 'EditDepartment',
            'approver_user1_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $department) {
            $browser->loginAs($user)
                ->visit(new Departments())
                ->waitFor('.fa-pencil')
                ->click('.fa-pencil')
                ->waitForText($department->name)
                ->type('#department-name', 'DevDept')
                ->clear('#department-description')
                ->press('.input-addon-remove')
                ->press('Save')
                ->seeNotification('settings have been updated');
        });
    }

    /**
     * @group departments
     * @throws \Throwable
     */
    public function testDeleteDepartment()
    {
        $user = TestHelper::createAdminUser();
        factory(Department::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Departments())
                ->waitFor('.fa-trash-o')
                ->click('.fa-trash-o')
                ->waitFor('@confirmDialog')
                ->press('Yes, delete it!')
                ->seeNotification('The department was deleted successfully!')
                ->assertMissing('.team-members');
        });
    }
}
