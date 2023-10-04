<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testIsAvailableGetUsersList()
    {
        // create a fake request with the necessary parameters
        $response = $this->get('/api/randomUser', [
            'field' => 'name',
            'orderBy' => 'asc',
            'type' => 'xml',
            'limit' => 10,
            'page' => 1,
        ]);

        // check that the request is successful
        $response->assertStatus(200);
    }
}
