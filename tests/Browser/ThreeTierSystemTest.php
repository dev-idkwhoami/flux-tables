<?php

use Idkwhoami\FluxTables\Livewire\SimpleTable;
use Idkwhoami\FluxTables\Livewire\AdvancedTable;
use Idkwhoami\FluxTables\Livewire\EnhancedTable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test data
    $this->users = collect([
        ['name' => 'John Doe', 'email' => 'john@example.com', 'email_verified_at' => now()],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'email_verified_at' => null],
        ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'email_verified_at' => now()],
    ])->map(fn ($data) => \App\Models\User::factory()->create($data));
});

// ✅ Positive Test: SimpleTable displays data without interactive features
it('renders SimpleTable with basic features only', function () {
    $this->visit('/simple-table')
        ->assertSee('Users List')
        ->assertSee('John Doe')
        ->assertSee('jane@example.com')
        ->assertSee('Search...') // Has search
        ->assertDontSee('funnel') // No filter button
        ->assertDontSee('view-columns') // No column toggle
        ->assertElementIsNotClickable('th:contains("Name")') // Headers not sortable
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: SimpleTable doesn't have sorting functionality
it('SimpleTable column headers are not interactive', function () {
    $this->visit('/simple-table')
        ->click('th:contains("Name")') // Try to click header
        ->waitForLivewire()
        ->assertSee('John Doe') // Data should remain in original order
        ->assertDontSee('chevron-up') // No sort indicators
        ->assertDontSee('chevron-down')
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: AdvancedTable has sorting but no filtering
it('renders AdvancedTable with sorting capabilities', function () {
    $this->visit('/advanced-table')
        ->assertSee('Users Management')
        ->assertSee('John Doe')
        ->assertSee('view-columns') // Has column toggle
        ->assertElementIsClickable('th:contains("Name")') // Headers are sortable
        ->assertDontSee('funnel') // No filter button (key difference from Enhanced)
        ->click('th:contains("Name")') // Test sorting
        ->waitForLivewire()
        ->assertSee('chevron-up') // Should show sort indicator
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: AdvancedTable doesn't have filtering functionality
it('AdvancedTable does not provide filtering options', function () {
    $this->visit('/advanced-table')
        ->assertDontSee('funnel') // No filter button
        ->assertDontSee('Filter Users Management') // No filter modal
        ->execute('window.dispatchEvent(new KeyboardEvent("keydown", {key: "f", ctrlKey: true}))') // Try shortcut
        ->waitFor(500) // Wait for any potential modals
        ->assertDontSee('Filter Users Management') // Still no filter modal
        ->assertNoJavascriptErrors();
});

// ✅ Positive Test: EnhancedTable has all features
it('renders EnhancedTable with all features enabled', function () {
    $this->visit('/enhanced-table')
        ->assertSee('Users Management')
        ->assertSee('John Doe')
        ->assertSee('funnel') // Has filter button
        ->assertSee('view-columns') // Has column toggle
        ->assertElementIsClickable('th:contains("Name")') // Headers are sortable
        ->click('[icon="funnel"]') // Open filters
        ->waitForLivewire()
        ->assertSee('Filter Users Management') // Filter modal appears
        ->assertSee('Role') // Filter options visible
        ->assertNoJavascriptErrors();
});

// ❌ Negative Test: EnhancedTable performance doesn't degrade with all features
it('EnhancedTable maintains performance with all features enabled', function () {
    // Create more data to test performance
    \App\Models\User::factory()->count(100)->create();

    $startTime = microtime(true);

    $this->visit('/enhanced-table')
        ->assertSee('Users Management')
        ->click('[icon="funnel"]')
        ->waitForLivewire()
        ->type('John', 'input[placeholder*="Search by name"]')
        ->click('th:contains("Name")') // Sort
        ->waitForLivewire();

    $executionTime = microtime(true) - $startTime;

    // Should complete within reasonable time even with all features
    expect($executionTime)->toBeLessThan(10.0);
    $this->assertNoJavascriptErrors();
});

// ✅ Positive Test: Feature progression works correctly
it('shows clear feature progression across table types', function () {
    // Test SimpleTable features
    $simpleFeatures = $this->visit('/simple-table')
        ->hasElement('[icon="search"]') && // Has search
        !$this->hasElement('[icon="funnel"]') && // No filters
        !$this->hasElement('[icon="view-columns"]'); // No column toggle

    // Test AdvancedTable features
    $advancedFeatures = $this->visit('/advanced-table')
        ->hasElement('[icon="search"]') && // Has search
        !$this->hasElement('[icon="funnel"]') && // No filters
        $this->hasElement('[icon="view-columns"]'); // Has column toggle

    // Test EnhancedTable features
    $enhancedFeatures = $this->visit('/enhanced-table')
        ->hasElement('[icon="search"]') && // Has search
        $this->hasElement('[icon="funnel"]') && // Has filters
        $this->hasElement('[icon="view-columns"]'); // Has column toggle

    expect($simpleFeatures)->toBeTrue('SimpleTable should have basic features only');
    expect($advancedFeatures)->toBeTrue('AdvancedTable should add sorting and column controls');
    expect($enhancedFeatures)->toBeTrue('EnhancedTable should have all features');
});

// ❌ Negative Test: Each table type doesn't have features it shouldn't
it('enforces proper feature boundaries between table types', function () {
    // SimpleTable: Should NOT have sorting indicators when clicking headers
    $this->visit('/simple-table')
        ->click('th:contains("Name")')
        ->waitForLivewire()
        ->assertDontSee('chevron-up')
        ->assertDontSee('chevron-down');

    // AdvancedTable: Should NOT have filter button
    $this->visit('/advanced-table')
        ->assertDontSee('[icon="funnel"]')
        ->assertDontSee('Filter Users Management');

    // All tables should maintain their boundaries
    $this->assertNoJavascriptErrors();
});
