import { test, expect } from '@playwright/test';

test.describe('Datatable Shared Components', () => {
    // Note: To test the actual datatable, we need to visit a page that uses it
    // For example, the inventory items page.

    const adminEmail = 'dacropbiotechcenter@gmail.com';
    const password = 'password';

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[type="email"], input[name="email"]', adminEmail);
        await page.fill('input[type="password"], input[name="password"]', password);
        await page.locator('button[type="submit"]').click();
        await page.waitForURL(/dashboard/);
    });

    test('Generic search/filter query parameters update URL', async ({ page }) => {
        // Assume inventory/items uses the shared datatable component
        const response = await page.goto('/inventory/items');
        
        if (response?.url().includes('/inventory/items')) {
            await page.waitForLoadState('domcontentloaded');
            
            // Check for search input
            const searchInput = page.locator('input[type="search"], input[placeholder*="Search"]');
            
            if (await searchInput.isVisible()) {
                await searchInput.fill('test search query');
                // Press enter or wait for debounce
                await searchInput.press('Enter');
                
                await page.waitForTimeout(1000); // Wait for URL update
                
                // Expect URL to contain the search query
                expect(page.url()).toContain('test%20search%20query');
            }
        } else {
            expect(response?.status()).toBeLessThan(500);
        }
    });

    // More tests to be implemented
    // - Sorting by clicking column headers
});
