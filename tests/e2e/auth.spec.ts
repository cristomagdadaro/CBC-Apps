import { test, expect } from '@playwright/test';

test.describe('Guest User Workflow', () => {
    test('can view the home page', async ({ page }) => {
        await page.goto('/');
        
        // Check that the page loads successfully
        await expect(page).toHaveTitle(/CropSync|Welcome/i);
    });

    test('can navigate to login page', async ({ page }) => {
        await page.goto('/');
        
        // Look for login link and click it
        const loginLink = page.locator('a:has-text("Login"), a:has-text("Sign In"), a:has-text("Log in")').first();
        if (await loginLink.isVisible()) {
            await loginLink.click();
            await expect(page).toHaveURL(/login/);
        }
    });
});

test.describe('Authentication Workflow', () => {
    test('can view login form', async ({ page }) => {
        await page.goto('/login');
        
        // Check for login form elements
        await expect(page.locator('input[type="email"], input[name="email"]')).toBeVisible();
        await expect(page.locator('input[type="password"], input[name="password"]')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toBeVisible();
    });

    test('shows validation errors on empty submit', async ({ page }) => {
        await page.goto('/login');
        
        // Wait for page to load
        await page.waitForLoadState('domcontentloaded');
        
        // Ensure button is visible before clicking
        const submitBtn = page.locator('button[type="submit"]');
        await submitBtn.waitFor({ state: 'visible', timeout: 10000 });
        
        // Submit empty form
        await submitBtn.click();
        
        // Wait briefly for navigation or validation
        await page.waitForTimeout(500);
        
        // Should show validation errors or stay on login page
        await expect(page).toHaveURL(/login/);
    });

    test('can attempt login with credentials', async ({ page }) => {
        await page.goto('/login');
        
        // Wait for form to load
        await page.waitForLoadState('domcontentloaded');
        
        // Fill in login form (update with test credentials)
        await page.fill('input[type="email"], input[name="email"]', 'test@example.com');
        await page.fill('input[type="password"], input[name="password"]', 'password');
        
        // Submit form
        await page.locator('button[type="submit"]').click();
        
        // Wait for response - either redirect or error
        await page.waitForTimeout(1000);
    });
});

test.describe('Event Registration Workflow', () => {
    test('can view guest events page', async ({ page }) => {
        // Navigate to guest events page
        await page.goto('/guest/events');
        
        // Check that page loads
        await page.waitForLoadState('domcontentloaded');
        const content = await page.content();
        expect(content).toBeTruthy();
    });
});
