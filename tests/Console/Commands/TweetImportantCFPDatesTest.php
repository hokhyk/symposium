<?php

namespace Tests\Console\Commands;

use App\Conference;
use App\Console\Commands\TweetImportantCFPDates;
use Carbon\Carbon;
use Tests\IntegrationTestCase;
use Thujohn\Twitter\Twitter;

class TweetImportantCFPDatesTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    function cfps_opening_today_should_be_tweeted()
    {
        // starts today, ends next week
        factory(Conference::class)->create([
            'cfp_starts_at' => Carbon::now(),
            'cfp_ends_at' => Carbon::now()->addWeek(),
        ]);

        $mock = $this->createMock(Twitter::class);
        $mock->expects($this->once())->method('postTweet');

        (new TweetImportantCFPDates($mock, 0))->handle();
    }

    /** @test */
    function cfps_closing_tomorrow_should_be_tweeted()
    {
        // started last week, ends tomorrow
        factory(Conference::class)->create([
            'cfp_starts_at' => Carbon::now()->subWeek(),
            'cfp_ends_at' => Carbon::now()->addDay(),
        ]);

        $mock = $this->createMock(Twitter::class);
        $mock->expects($this->once())->method('postTweet');

        (new TweetImportantCFPDates($mock, 0))->handle();
    }

    /** @test */
    function cfps_not_opening_today_nor_closing_tomorrow_should_not_be_tweeted()
    {
        // started last week, ends next week
        factory(Conference::class)->create([
            'cfp_starts_at' => Carbon::now()->subWeek(),
            'cfp_ends_at' => Carbon::now()->addWeek(),
        ]);

        $mock = $this->createMock(Twitter::class);
        $mock->expects($this->never())->method('postTweet');

        (new TweetImportantCFPDates($mock, 0))->handle();
    }

    /** @test */
    function cfps_that_open_and_close_same_day_should_not_be_tweeted()
    {
        factory(Conference::class)->create([
            'cfp_starts_at' => Carbon::now(),
            'cfp_ends_at' => Carbon::now(),
        ]);

        $mock = $this->createMock(Twitter::class);
        $mock->expects($this->never())->method('postTweet');

        (new TweetImportantCFPDates($mock, 0))->handle();
    }
}
