<?php

namespace Tests\Browser;

use App\Models\User;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * @throws \Throwable
     */
    public function testLogin()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('123456')
        ]);
        $this->browse(function ($browser) use ($user) {
            $browser->visit('/')
                ->assertPathIs('/auth/login')
                ->type('email', $user->email)
                ->type('password', '123456')
                ->press('Sign me in!')
                ->assertSee('Calendar');
        });
    }
}
