<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users
    $this->users = collect([
        ['name' => 'John Doe', 'email' => 'john@example.com', 'email_verified_at' => now()],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'email_verified_at' => null],
        ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'email_verified_at' => now()],
    ])->map(fn ($data) => \App\Models\User::factory()->create($data));
});

// ✅ Positive Test: Table displays data correctly
it('displays table data correctly', function () {
    $this->visit('/users-table')
        ->assertSee('Users Management')
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('Jane Smith')
        ->assertSee('Bob Wilson')
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: Table handles empty data gracefully
it('handles empty table state gracefully', function () {
    \App\Models\User::query()->delete();

    $this->visit('/users-table')
        ->assertSee('Users Management')
        ->assertDontSee('John Doe')
        ->assertNoConsoleLogs()
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: Text filter works correctly
it('filters data using text filter', function () {
    $this->visit('/users-table')
        ->click('[icon="funnel"]') // Open filter modal
        ->type('John', 'input[placeholder*="Search by name"]')
        ->click('Done')
        ->waitForLivewire()
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith')
        ->assertDontSee('Bob Wilson')
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: Filter handles no results gracefully
it('handles filter with no results gracefully', function () {
    $this->visit('/users-table')
        ->click('[icon="funnel"]')
        ->type('NonexistentUser', 'input[placeholder*="Search by name"]')
        ->click('Done')
        ->waitForLivewire()
        ->assertDontSee('John Doe')
        ->assertDontSee('Jane Smith')
        ->assertDontSee('Bob Wilson')
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: Sorting works correctly
it('sorts data correctly when clicking column headers', function () {
    $this->visit('/users-table')
        ->click('th:contains("Name")') // Sort by name ascending
        ->waitForLivewire()
        ->assertSee('Bob Wilson') // Should appear first alphabetically
        ->click('th:contains("Name")') // Sort by name descending
        ->waitForLivewire()
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: Sorting handles invalid column gracefully
it('handles invalid sort column gracefully', function () {
    $this->visit('/users-table')
        ->execute('window.Livewire.find("' . $this->getComponentId() . '").call("sortBy", "invalid_column")')
        ->waitForLivewire()
        ->assertSee('John Doe') // Data should still be visible
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: Search functionality works
it('searches across multiple columns correctly', function () {
    $this->visit('/users-table')
        ->type('john@example', 'input[placeholder="Search..."]')
        ->waitForLivewire()
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith')
        ->assertDontSee('Bob Wilson')
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: Search handles empty results
it('handles empty search results gracefully', function () {
    $this->visit('/users-table')
        ->type('nonexistent@example.com', 'input[placeholder="Search..."]')
        ->waitForLivewire()
        ->assertDontSee('John Doe')
        ->assertDontSee('Jane Smith')
        ->assertDontSee('Bob Wilson')
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: Active filter pills display correctly
it('displays active filter badges with close buttons', function () {
    $this->visit('/users-table')
        ->click('[icon="funnel"]')
        ->type('John', 'input[placeholder*="Search by name"]')
        ->click('Done')
        ->waitForLivewire()
        ->assertSee('Name: John') // Filter badge should show
        ->click('[icon="x-mark"]') // Click close button on badge
        ->waitForLivewire()
        ->assertDontSee('Name: John') // Badge should disappear
        ->assertSee('Jane Smith') // All users should be visible again
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: Filter reset handles empty state
it('handles filter reset when no filters are active', function () {
    $this->visit('/users-table')
        ->click('[icon="funnel-x"]') // Reset filters when none are active
        ->waitForLivewire()
        ->assertSee('John Doe') // Data should still be visible
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: Pagination works correctly
it('handles pagination correctly', function () {
    // Create more users than per-page limit
    \App\Models\User::factory()->count(20)->create();

    $this->visit('/users-table')
        ->select('10', '[wire:model.live="perPage"]') // Set items per page
        ->waitForLivewire()
        ->assertSee('Next') // Pagination should appear
        ->click('Next')
        ->waitForLivewire()
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: Pagination handles edge cases
it('handles pagination with insufficient data', function () {
    $this->visit('/users-table')
        ->select('100', '[wire:model.live="perPage"]') // Set high per-page limit
        ->waitForLivewire()
        ->assertDontSee('Next') // No pagination should appear
        ->assertSee('John Doe') // Data should still be visible
        ->assertNoJavascriptErrors();
});
