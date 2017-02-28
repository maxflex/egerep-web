<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SerpSpeedTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @test
     */
    public function serp_test()
    {
        $data = [
            'filter_used' => true,
            'page' => 1,
            'search' => [
                'id' => 10,
                'sort' => 2,
                'station_id' => null,
                'subjects' => [1, 2]
            ]
        ];
        $response = $this->call('POST', 'api/tutors/search', $data);
        // \Log::info($response->getContent());
    }

}
