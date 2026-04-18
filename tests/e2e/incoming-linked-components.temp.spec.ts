import { test, expect, Page } from '@playwright/test';

const loginEmail = process.env.E2E_EMAIL;
const loginPassword = process.env.E2E_PASSWORD;
const parentItemName = process.env.E2E_PARENT_ITEM_NAME || 'E2E Parent Equipment 20260418';
const childItemName = process.env.E2E_CHILD_ITEM_NAME || 'E2E Child Component 20260418';
const personnelSearch = process.env.E2E_PERSONNEL_SEARCH || 'E2E-INV-UI-20260418';
const storageLabel = process.env.E2E_STORAGE_LABEL || 'Central Bodega';
const parentProjectCode = process.env.E2E_PROJECT_CODE || 'E2E-PROJECT-20260418';
const parentPrriBarcode = process.env.E2E_PARENT_PRRI || 'PRRI-E2E-PARENT-20260418';
const childPrriBarcode = process.env.E2E_CHILD_PRRI || 'PRRI-E2E-CHILD-20260418';

function inputByLabel(page: Page, label: string, index = 1) {
  return page.locator(`xpath=(//div[.//span[contains(normalize-space(.), "${label}")]]//input)[${index}]`);
}

function clickableContainerByLabel(page: Page, label: string) {
  return page.locator(`xpath=(//div[.//span[contains(normalize-space(.), "${label}")]]//*[contains(@class, "border-gray-700")])[1]`);
}

async function login(page: Page) {
  test.skip(!loginEmail || !loginPassword, 'Set E2E_EMAIL and E2E_PASSWORD to run the incoming browser workflow test.');

  await page.goto('/login');
  const acknowledgeNoticeButton = page.getByRole('button', { name: /Has Read and Acknowledged/i });

  if (await acknowledgeNoticeButton.isVisible().catch(() => false)) {
    const noticeDialog = page.locator('dialog');

    for (let attempt = 0; attempt < 20; attempt++) {
      if (await acknowledgeNoticeButton.isEnabled()) {
        break;
      }

      await noticeDialog.hover();
      await page.mouse.wheel(0, 1400);
      await page.waitForTimeout(150);
    }

    await expect(acknowledgeNoticeButton).toBeEnabled({ timeout: 10000 });
    await acknowledgeNoticeButton.click();
  }

  const emailInput = page.getByRole('textbox', { name: /Email/i });
  const passwordInput = page.locator('input[type="password"], input[name="password"]').first();
  const loginButton = page.getByRole('button', { name: /^Log in$/i });

  await expect(emailInput).toBeVisible({ timeout: 15000 });
  await expect(passwordInput).toBeVisible({ timeout: 15000 });

  await emailInput.evaluate((element, value) => {
    const input = element as HTMLInputElement;
    input.focus();
    input.value = value as string;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }, loginEmail as string);

  await passwordInput.evaluate((element, value) => {
    const input = element as HTMLInputElement;
    input.focus();
    input.value = value as string;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }, loginPassword as string);

  await expect(emailInput).toHaveValue(loginEmail as string);
  await expect(passwordInput).toHaveValue(loginPassword as string);
  await expect(loginButton).toBeEnabled({ timeout: 10000 });

  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    loginButton.click(),
  ]);

  await page.waitForTimeout(1000);

  if (page.url().includes('/login')) {
    throw new Error('Login did not complete successfully for the browser verification account.');
  }
}

async function chooseSearchFieldOption(page: Page, label: string, search: string, optionText: string) {
  const input = inputByLabel(page, label);
  await input.click();
  await input.fill(search);
  const option = page.locator('div.z-[999]').getByText(optionText, { exact: false }).first();
  await option.waitFor({ state: 'visible', timeout: 15000 });
  await option.click();
}

async function chooseDropdownOption(page: Page, label: string, optionText: string) {
  const trigger = clickableContainerByLabel(page, label);
  await trigger.click();
  const option = page.locator('div.z-50').getByText(optionText, { exact: false }).first();
  await option.waitFor({ state: 'visible', timeout: 10000 });
  await option.click();
}

test.describe('Incoming linked component browser workflow', () => {
  test('creates parent and linked sub-component transactions through the UI', async ({ page }) => {
    test.setTimeout(180000);

    await login(page);
    await page.goto('/apps/inventory/transactions/incoming');
    await page.waitForLoadState('domcontentloaded');

    await chooseSearchFieldOption(page, 'Item', parentItemName, parentItemName);
    await inputByLabel(page, 'Project Code').fill(parentProjectCode);
    await chooseDropdownOption(page, 'Accountable Personnel', personnelSearch);
    await chooseDropdownOption(page, 'Storage Location', storageLabel);
    await inputByLabel(page, 'PRRI Barcode').fill(parentPrriBarcode);
    await inputByLabel(page, 'Condition').fill('Operational parent equipment');
    await inputByLabel(page, 'Quantity').fill('1');
    await inputByLabel(page, 'Unit Price').fill('100');
    await inputByLabel(page, 'Unit').fill('set');
    await inputByLabel(page, 'Total Cost').fill('100');
    await inputByLabel(page, 'Expiration').fill('2026-12-31');
    await page.locator('textarea').fill('Parent equipment created by Playwright verification.');

    const parentResponsePromise = page.waitForResponse((response) => {
      return response.request().method() === 'POST'
        && response.url().includes('/api/inventory/transactions')
        && response.status() === 201;
    });

    await page.getByRole('button', { name: /^Save$/ }).click();
    const parentResponse = await parentResponsePromise;
    const parentPayload = await parentResponse.json();
    const parentId = parentPayload.id as string;
    const parentBarcode = parentPayload.barcode as string;

    expect(parentId).toBeTruthy();
    expect(parentBarcode).toBeTruthy();

    await chooseSearchFieldOption(page, 'Item', childItemName, childItemName);
    await inputByLabel(page, 'Parent CBC / PRRI Barcode').fill(parentBarcode);
    await inputByLabel(page, 'PRRI Barcode').fill(childPrriBarcode);
    await inputByLabel(page, 'Condition').fill('Operational child component');
    await inputByLabel(page, 'Quantity').fill('1');
    await inputByLabel(page, 'Unit Price').fill('25');
    await inputByLabel(page, 'Unit').fill('pc');
    await inputByLabel(page, 'Total Cost').fill('25');
    await inputByLabel(page, 'Expiration').fill('2026-12-31');
    await page.locator('textarea').fill('Child component linked through parent barcode.');

    const childResponsePromise = page.waitForResponse((response) => {
      return response.request().method() === 'POST'
        && response.url().includes('/api/inventory/transactions')
        && response.status() === 201;
    });

    await page.getByRole('button', { name: /^Save$/ }).click();
    const childResponse = await childResponsePromise;
    const childPayload = await childResponse.json();
    const childId = childPayload.id as string;

    expect(childId).toBeTruthy();

    await page.goto(`/apps/inventory/transactions/${parentId}`);
    await page.waitForLoadState('domcontentloaded');
    await expect(page.getByText(childItemName, { exact: false }).first()).toBeVisible();

    const componentRow = page.locator(`xpath=//div[contains(@class, 'p-3')][.//*[contains(text(), "${childItemName}")]]`).first();
    await expect(componentRow.getByText('Open Transaction', { exact: false })).toBeVisible();
    await componentRow.getByText('Open Transaction', { exact: false }).click();

    await expect(page).toHaveURL(new RegExp(`/apps/inventory/transactions/${childId}$`));
    await expect(page.getByText('Parent Transaction', { exact: false })).toBeVisible();
    await expect(page.getByText(parentItemName, { exact: false }).first()).toBeVisible();

    await page.getByText('Open Parent', { exact: false }).click();
    await expect(page).toHaveURL(new RegExp(`/apps/inventory/transactions/${parentId}$`));
  });
});
