<?php

namespace Tests\Feature;

use App\Models\Voter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VotingTest extends TestCase
{
    // Test concurrent voting
    public function testConcurrentVoting()
    {
        $voter = Voter::factory()->create();

        $response = $this->actingAs($voter)->postJson('/api/vote',
        [
            'candidates' => ['2'],
        ]);

        print_r($response->getContent());

        $voter = Voter::factory()->create();

        $response = $this->actingAs($voter)->postJson('/api/vote',
            [
                'candidates' => ['2'],
            ]);

        print_r($response->getContent());
    }
}
