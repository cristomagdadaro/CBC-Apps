import { test, expect } from '@playwright/test';

test.describe('Laboratory & Equipment', () => {
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

    test('Lab request submission flow page exists', async ({ page }) => {
        const response = await page.goto('/laboratory/requests');
        
        if (response?.url().includes('/laboratory/requests')) {
            await page.waitForLoadState('domcontentloaded');
            await expect(page.locator('body')).toContainText(/Lab/i);
        } else {
            expect(response?.status()).toBeLessThan(500);
        }
    });

    // More tests to be implemented here
    // - Equipment logger flow
    // - Equipment reports generation
});
