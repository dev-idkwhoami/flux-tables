<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test data with relationships
    $this->users = \App\Models\User::factory()
        ->count(50)
        ->create();

    // Create related data for JOIN optimization testing
    if (class_exists(\App\Models\Role::class)) {
        $this->roles = \App\Models\Role::factory()->count(5)->create();
        $this->users->each(fn ($user) => $user->roles()->attach($this->roles->random()));
    }
});

// ✅ Positive Test: Single query execution for sorting with relationships
it('executes only one query for relationship column sorting', function () {
    DB::enableQueryLog();

    $this->visit('/users-table')
        ->click('th:contains("Role")') // Sort by relationship column
        ->waitForLivewire()
        ->assertSee($this->users->first()->name);

    $queries = DB::getQueryLog();
    $dataQueries = collect($queries)->filter(function ($query) {
        // Filter out Livewire internal queries
        return !str_contains($query['query'], 'sessions') &&
               !str_contains($query['query'], '_livewire');
    });

    // Should be only 1 query for data + 1 for pagination count = 2 total
    expect($dataQueries->count())->toBeLessThanOrEqual(2);
})->skip(!class_exists(\App\Models\Role::class), 'Role model not available');

// ❌ Negative Test: Query performance doesn't degrade with complex filters
it('maintains performance with multiple active filters', function () {
    $startTime = microtime(true);

    $this->visit('/users-table')
        ->click('[icon="funnel"]')
        ->type('John', 'input[placeholder*="Search by name"]')
        ->select('admin', 'select[name="role"]')
        ->select('1', 'select[name="email_verified"]')
        ->click('Done')
        ->waitForLivewire();

    $executionTime = microtime(true) - $startTime;

    // Page should load within reasonable time (5 seconds)
    expect($executionTime)->toBeLessThan(5.0);
    $this->assertNoJavascriptErrors();
});

// ✅ Positive Test: PostgreSQL JSON search optimization works
it('efficiently searches JSON columns using PostgreSQL operators', function () {
    // Skip if not using PostgreSQL
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('PostgreSQL specific test');
    }

    DB::enableQueryLog();

    $this->visit('/users-table')
        ->type('some bio content', 'input[placeholder="Search..."]')
        ->waitForLivewire();

    $queries = DB::getQueryLog();
    $searchQuery = collect($queries)->first(function ($query) {
        return str_contains($query['query'], '::text') ||
               str_contains($query['query'], 'ILIKE');
    });

    // Should use PostgreSQL JSON operators for efficient search
    expect($searchQuery['query'])->toContain('::text');
});

// ❌ Negative Test: Large dataset doesn't cause memory issues
it('handles large datasets without memory issues', function () {
    // Create larger dataset
    \App\Models\User::factory()->count(500)->create();

    $initialMemory = memory_get_usage(true);

    $this->visit('/users-table')
        ->select('100', '[wire:model.live="perPage"]')
        ->waitForLivewire()
        ->assertSee('Users Management');

    $finalMemory = memory_get_usage(true);
    $memoryIncrease = $finalMemory - $initialMemory;

    // Memory increase should be reasonable (less than 50MB)
    expect($memoryIncrease)->toBeLessThan(50 * 1024 * 1024);
});

// ✅ Positive Test: Query optimization with eager loading
it('uses eager loading to prevent N+1 queries', function () {
    DB::enableQueryLog();

    $this->visit('/users-table')
        ->waitForLivewire()
        ->assertSee($this->users->first()->name);

    $queries = DB::getQueryLog();

    // Count actual data queries (excluding Livewire system queries)
    $dataQueries = collect($queries)->filter(function ($query) {
        return !str_contains($query['query'], 'sessions') &&
               !str_contains($query['query'], '_livewire') &&
               !str_contains($query['query'], 'components');
    })->count();

    // Should not have N+1 queries (should be minimal regardless of row count)
    expect($dataQueries)->toBeLessThan(10);
});

// ❌ Negative Test: Complex filtering doesn't cause query explosion
it('prevents query explosion with complex relationship filters', function () {
    if (!class_exists(\App\Models\Role::class)) {
        $this->markTestSkipped('Role model not available');
    }

    DB::enableQueryLog();

    $this->visit('/users-table')
        ->click('[icon="funnel"]')
        ->select(['admin', 'editor'], '[wire:model.live="role"]') // Multi-select filter
        ->select('1', '[wire:model.live="email_verified"]')
        ->click('Done')
        ->waitForLivewire();

    $queries = DB::getQueryLog();
    $totalQueries = count($queries);

    // Even with complex filters, should not exceed reasonable query count
    expect($totalQueries)->toBeLessThan(15);
    $this->assertNoJavascriptErrors();
});
