<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class ChosenSelect extends BaseComponent
{
    private $componentSelector;

    public function __construct(string $selector)
    {
        $this->componentSelector = $selector;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector(): string
    {
        return $this->componentSelector;
    }

    public function selectValue(Browser $browser, string $optionName): void
    {
        $browser->click('.chosen-single')
            ->type('.chosen-search input', $optionName)
            ->click('.active-result.highlighted');
    }
}
