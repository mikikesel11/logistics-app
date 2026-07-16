import { test, expect, type Locator } from '@playwright/test';

/**
 * The definition-of-done flow: log in, create a customer and a carrier, create
 * a load linking them, move it through its lifecycle, and generate + download a
 * Bill of Lading PDF. Requires a running, freshly seeded API (see
 * playwright.config.ts). A unique suffix keeps reruns from colliding on the
 * carrier's unique MC number.
 *
 * Fields are located by role + accessible name (Playwright best practice):
 * text inputs are `textbox`, number inputs `spinbutton`, selects `combobox`.
 * This resolves against the computed accessible name, so the visual required
 * asterisk (aria-hidden) doesn't interfere.
 */
const suffix = Date.now().toString().slice(-6);

const textbox = (scope: Locator, name: string) =>
  scope.getByRole('textbox', { name, exact: true });
const spinbutton = (scope: Locator, name: string) =>
  scope.getByRole('spinbutton', { name, exact: true });
const combobox = (scope: Locator, name: string) =>
  scope.getByRole('combobox', { name, exact: true });

test('CRM → Load → Bill of Lading end to end', async ({ page }) => {
  const customerName = `Acme Shipping ${suffix}`;
  const carrierName = `Road Runner ${suffix}`;
  const originName = `Origin DC ${suffix}`;
  const destName = `Dest DC ${suffix}`;

  async function createLocation(name: string, city: string, state: string) {
    await page.getByRole('button', { name: '+ New location' }).click();
    const dialog = page.getByRole('dialog');
    await textbox(dialog, 'Facility name').fill(name);
    await textbox(dialog, 'Address line 1').fill('100 Dock St');
    await textbox(dialog, 'City').fill(city);
    await textbox(dialog, 'State').fill(state);
    await textbox(dialog, 'Postal code').fill('43004');
    await dialog.getByRole('button', { name: 'Save location' }).click();
    await expect(page.getByRole('cell', { name })).toBeVisible();
  }

  // --- Log in ---
  await page.goto('/login');
  await textbox(page.locator('body'), 'Email').fill('admin@example.com');
  await page.getByRole('textbox', { name: 'Password', exact: true }).fill('password');
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

  // --- Create a customer ---
  await page.getByRole('link', { name: 'Customers' }).click();
  await page.getByRole('button', { name: '+ New customer' }).click();

  const customerDialog = page.getByRole('dialog');
  await textbox(customerDialog, 'Name').fill(customerName);
  await textbox(customerDialog, 'Email').fill(`ops-${suffix}@acme.test`);
  await customerDialog.getByRole('button', { name: 'Save customer' }).click();

  await expect(page.getByRole('cell', { name: customerName })).toBeVisible();

  // --- Create a carrier ---
  await page.getByRole('link', { name: 'Carriers' }).click();
  await page.getByRole('button', { name: '+ New carrier' }).click();

  const carrierDialog = page.getByRole('dialog');
  await textbox(carrierDialog, 'Name').fill(carrierName);
  await textbox(carrierDialog, 'MC number').fill(`MC${suffix}`);
  await carrierDialog.getByRole('button', { name: 'Save carrier' }).click();

  await expect(page.getByRole('cell', { name: carrierName })).toBeVisible();

  // --- Create origin + destination locations ---
  await page.getByRole('link', { name: 'Locations' }).click();
  await createLocation(originName, 'Columbus', 'OH');
  await createLocation(destName, 'Reno', 'NV');

  // --- Create a load linking customer + carrier + locations ---
  await page.getByRole('link', { name: 'Loads' }).click();
  await page.getByRole('button', { name: '+ New load' }).click();

  const loadDialog = page.getByRole('dialog');
  await textbox(loadDialog, 'Reference').fill(`L-${suffix}`);
  await textbox(loadDialog, 'Commodity').fill('Steel coils');
  await combobox(loadDialog, 'Customer').selectOption({ label: customerName });
  await combobox(loadDialog, 'Carrier').selectOption({ label: carrierName });
  await combobox(loadDialog, 'Origin').selectOption({ label: `${originName} — Columbus, OH` });
  await combobox(loadDialog, 'Destination').selectOption({ label: `${destName} — Reno, NV` });
  await spinbutton(loadDialog, 'Customer rate ($)').fill('2500');
  await spinbutton(loadDialog, 'Carrier cost ($)').fill('1900');
  await loadDialog.getByRole('button', { name: 'Save load' }).click();

  // Saving a new load navigates to its detail page.
  await expect(page.getByRole('heading', { name: `L-${suffix}` })).toBeVisible();
  // Margin = $2,500 - $1,900 = $600.
  await expect(page.getByText('$600.00')).toBeVisible();
  // The assigned addresses show on the load — these feed the BOL snapshot.
  await expect(page.getByText('Columbus, OH')).toBeVisible();
  await expect(page.getByText('Reno, NV')).toBeVisible();

  // --- Move through the status lifecycle: quoted → booked → dispatched ---
  await page.getByRole('button', { name: 'Move to Booked' }).click();
  await expect(page.getByRole('button', { name: 'Move to Dispatched' })).toBeVisible();
  await page.getByRole('button', { name: 'Move to Dispatched' }).click();
  await expect(page.getByRole('button', { name: 'Move to In transit' })).toBeVisible();

  // --- Generate the Bill of Lading ---
  await page.getByRole('button', { name: 'Generate BOL' }).click();
  await expect(page.getByRole('button', { name: 'Regenerate BOL' })).toBeVisible();

  // --- Download the PDF and assert it arrives ---
  const downloadPromise = page.waitForEvent('download');
  await page.getByRole('button', { name: 'Download PDF' }).click();
  const download = await downloadPromise;
  expect(download.suggestedFilename()).toContain('.pdf');
});
