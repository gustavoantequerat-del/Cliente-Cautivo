<?php

namespace Tests;

use App\Models\BusinessUnit;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function institution(array $units = ['BCS'], array $attrs = []): Institution
    {
        $inst = Institution::create($attrs + ['name' => 'Banco '.uniqid(), 'slug' => uniqid('b-')]);
        foreach ($units as $code) {
            $unit = BusinessUnit::firstOrCreate(['code' => $code], ['name' => $code]);
            $inst->units()->attach($unit->id);
        }

        return $inst;
    }

    protected function client(?Institution $inst = null, string $role = 'client'): User
    {
        return User::factory()->create(['institution_id' => ($inst ?? $this->institution())->id, 'role' => $role]);
    }
}
