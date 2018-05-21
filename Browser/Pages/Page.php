<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

abstract class Page extends BasePage
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements(): array
    {
        return [
            '@confirmDialog' => '.swal-modal',
            '@notification' => '#toast-container',
            '@addDepartmentButton' => '.btn-add',
            '@requestsWaitingForApproval' => '.waiting-requests',
            '@editButton' => '.fa.fa-pencil'
        ];
    }

    public function seeErrorMessage(Browser $browser, string $errorMessage, int $waitForSeconds = 5): Browser
    {
        $browser->waitFor('#toast-container', $waitForSeconds)
            ->assertSee($errorMessage);

        return $browser;
    }

    public function seeNotification(Browser $browser, string $notification, int $waitForSeconds = 5): Browser
    {
        $browser->waitFor('@notification', $waitForSeconds)
            ->assertSee($notification);

        return $browser;
    }
}
