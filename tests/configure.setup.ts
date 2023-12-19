import { test } from '@playwright/test';
import { navigateAdmin } from './helpers/navigate';

test('configure geo zones', async ({ page }) => {
  await navigateAdmin(page, 'localisation/geo_zone');

  if (await page.getByText('NL Shipping').count() === 0) {
    await navigateAdmin(page, 'localisation/geo_zone.form');

    // Fill the name and description.
    await page.getByLabel('Geo Zone Name').fill('NL Shipping');
    await page.getByLabel('Description').fill('Netherlands');

    // Add a country, select "Netherlands".
    await page.locator('#button-geo-zone').click();
    await page.locator('[name="zone_to_geo_zone[0][country_id]"]').selectOption('Netherlands');

    // Save the geo zone.
    await page.locator('button[type="submit"]').click();
  }
});

test('configure Parcel Pro module', async ({page}) => {
  // Get the user id and api key from Parcel Pro.
  await page.goto('https://login.parcelpro.nl/koppeling/single.php?type=api');
  const userId = await page
    .locator('[data-bind="value: LoginId "]')
    .inputValue();
  const apiKey = await page
    .locator('[data-bind="value: ApiKey "]')
    .inputValue();

  await navigateAdmin(page, 'extension/parcelpro/module/parcelpro');
  await page.locator('#parcelpro_Id').fill(userId);
  await page.locator('#parcelpro_ApiKey').fill(apiKey);
  await page.locator('button[type="submit"]').click();
});
