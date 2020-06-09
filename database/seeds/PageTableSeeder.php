<?php

use App\Models\Page;

class PageTableSeeder extends BaseSeeder
{
    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    public function runFake()
    {
        $this->runProduction();
        factory(Page::class)->times(5)->create();
    }

    /**
     * @return void
     */
    public function runProduction()
    {
        factory(Page::class)->create(['title' => 'Privacy Policy']);
    }
}
