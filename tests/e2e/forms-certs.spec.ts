import { test, expect } from '@playwright/test';

test.describe('Forms & Certificates', () => {
    const adminEmail = 'dacropbiotechcenter@gmail.com';
    const password = 'password';

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[type="email"], input[name="email"]', adminEmail);
        await page.fill('input[type="password"], input[name="password"]', password);
        await page.locator('button[type="submit"]').click();
        await page.waitForURL(/dashboard/);
    });

    test('Forms management page loads', async ({ page }) => {
        const response = await page.goto('/forms');
        
        if (response?.url().includes('/forms')) {
            await page.waitForLoadState('domcontentloaded');
            await expect(page.locator('body')).toContainText(/Form/i);
        } else {
            expect(response?.status()).toBeLessThan(500);
        }
    });

    test('Certificates management page loads', async ({ page }) => {
        const response = await page.goto('/certificates');
        
        if (response?.url().includes('/certificates')) {
            await page.waitForLoadState('domcontentloaded');
            await expect(page.locator('body')).toContainText(/Certificate/i);
        } else {
            expect(response?.status()).toBeLessThan(500);
        }
    });

    // More tests to be implemented
    // - Create a new custom form
    // - Form response submission
    // - Certificate generation tracking
});
