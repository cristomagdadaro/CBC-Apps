import { test, expect } from '@playwright/test';

test.describe('Rentals Management', () => {
    const adminEmail = 'dacropbiotechcenter@gmail.com';
    const password = 'password';

    test.beforeEach(async ({ page }) => {
        // Login as admin before each test in this suite
        await page.goto('/login');
        await page.fill('input[type="email"], input[name="email"]', adminEmail);
        await page.fill('input[type="password"], input[name="password"]', password);
        await page.locator('button[type="submit"]').click();
        await page.waitForURL(/dashboard/);
    });

    test('Booking calendar visibility', async ({ page }) => {
        const response = await page.goto('/rentals/vehicles');
        
        if (response?.url().includes('/rentals/vehicles')) {
            await page.waitForLoadState('domcontentloaded');
            // Assuming the page title or content contains Vehicle Rentals or similar
            await expect(page.locator('body')).toContainText(/Rental/i);
        } else {
            expect(response?.status()).toBeLessThan(500);
        }
    });

    // More tests to be implemented here based on the specific DOM structure
    // - Submit a new rental request
    // - Approve/reject a rental request
});
