<?php

namespace Tests\Feature;

use App\Models\Parking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $zone;
    private $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        Zone::create(['name' => 'Green Zone', 'price_per_hour' => 100]);

        $this->user = User::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);
        $this->zone = Zone::first();
    }

    public function testUserCanStartParking()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/parkings/', [
            'vehicle_id' => $this->vehicle->id,
            'zone_id'    => $this->zone->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data'])
                 ->assertJson([
                     'data' => [
                         'start_time'  => now()->toDateTimeString(),
                         'stop_time'   => null,
                         'total_price' => 0,
                     ],
                 ]);

        $this->assertDatabaseCount('parkings', '1');
    }

    public function testUserCanGetOngoingParkingWithCorrectPrice()
    {
        $this->actingAs($this->user)->postJson('/api/v1/parkings/', [
            'vehicle_id' => $this->vehicle->id,
            'zone_id'    => $this->zone->id,
        ]);

        $this->travel(2)->hours();

        $parking = Parking::first();
        $response = $this->actingAs($this->user)->getJson('/api/v1/parkings/' . $parking->id);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data'])
                 ->assertJson([
                     'data' => [
                         'stop_time'   => null,
                         'total_price' => $this->zone->price_per_hour * 2,
                     ],
                 ]);
    }

    public function testUserCanStopParking()
    {
        $this->actingAs($this->user)->postJson('/api/v1/parkings/', [
            'vehicle_id' => $this->vehicle->id,
            'zone_id'    => $this->zone->id,
        ]);

        $this->travel(2)->hours();

        $parking = Parking::first();
        $response = $this->actingAs($this->user)->putJson('/api/v1/parkings/' . $parking->id);

        $updatedParking = Parking::find($parking->id);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data'])
                 ->assertJson([
                     'data' => [
                         'start_time'  => $updatedParking->start_time->toDateTimeString(),
                         'stop_time'   => $updatedParking->stop_time->toDateTimeString(),
                         'total_price' => $updatedParking->total_price,
                     ],
                 ]);

        $this->assertDatabaseCount('parkings', '1');
    }
}
