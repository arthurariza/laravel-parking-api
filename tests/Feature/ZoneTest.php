<?php

namespace Tests\Feature;

use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneTest extends TestCase
{
    use RefreshDatabase;

    public function testPublicUserCanGetAllZones()
    {
        Zone::create(['name' => 'Green Zone', 'price_per_hour' => 100]);
        Zone::create(['name' => 'Yellow Zone', 'price_per_hour' => 200]);
        Zone::create(['name' => 'Red Zone', 'price_per_hour' => 300]);
        
        $response = $this->getJson('/api/v1/zones');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data'])
                 ->assertJsonCount(3, 'data')
                 ->assertJsonStructure(['data' => [
                     ['*' => 'id', 'name', 'price_per_hour'],
                 ]])
                 ->assertJsonPath('data.0.id', 1)
                 ->assertJsonPath('data.0.name', 'Green Zone')
                 ->assertJsonPath('data.0.price_per_hour', 100);
    }
}
