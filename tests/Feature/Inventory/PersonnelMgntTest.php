<?php

namespace Inventory;

use Tests\TestCase;

class PersonnelMgntTest extends TestCase
{
    /**
     * @test
     */
    public function get_pesonnels(): void
    {
        $response = $this->get('api/inventory/personnels');
        $response->assertStatus(200);
    }
}
