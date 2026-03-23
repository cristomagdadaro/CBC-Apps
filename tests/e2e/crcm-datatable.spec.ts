import { test, expect, Page } from '@playwright/test';

const mockedPaginatorPayload = {
    current_page: 1,
    data: [
        {
            id: 'test-row-1',
            barcode: 'CBC-TEST-0001',
            transac_type: 'incoming',
            quantity: 5,
            quantityWithUnit: '5 pcs',
            project_code: 'TEST-PROJ',
            created_at: '10:00 am Mar 24, 2026',
            item: { name: 'Mocked Item' },
            personnel: { fullName: 'Mocked Personnel' },
        },
    ],
    from: 1,
    last_page: 1,
    per_page: 25,
    to: 1,
    total: 1,
};

async function openTransactionsPage(page: Page) {
    await page.goto('/transactions');

    if (page.url().includes('/login')) {
        const email = process.env.E2E_EMAIL;
        const password = process.env.E2E_PASSWORD;

        test.skip(!email || !password, 'Transactions page requires auth. Set E2E_EMAIL and E2E_PASSWORD to run this test.');

        await page.fill('input[type="email"], input[name="email"]', email as string);
        await page.fill('input[type="password"], input[name="password"]', password as string);
        await page.locator('button[type="submit"]').click();
        await page.waitForLoadState('domcontentloaded');
        await page.goto('/transactions');
    }
}

test.describe('CRCMDatatable endpoint-driven rendering', () => {
    test('renders rows from Laravel paginator payload shape', async ({ page }) => {
        await page.route(/\/api\/.*inventory\/transactions/i, async (route) => {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify(mockedPaginatorPayload),
            });
        });

        await openTransactionsPage(page);

        await expect(page.locator('#dtContainer')).toBeVisible();
        await expect(page.locator('#dtTable')).toContainText('CBC-TEST-0001');
        await expect(page.locator('#dtTable')).toContainText('Mocked Item');
    });

    test('renders page summary and page input from normalized meta', async ({ page }) => {
        await page.route(/\/api\/.*inventory\/transactions/i, async (route) => {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify(mockedPaginatorPayload),
            });
        });

        await openTransactionsPage(page);

        await expect(page.locator('#dtPageDetails')).toContainText('Showing 1 to 1 of 1 entries');
        await expect(page.locator('#dtPaginatorContainer input')).toHaveValue('1');
    });
});
