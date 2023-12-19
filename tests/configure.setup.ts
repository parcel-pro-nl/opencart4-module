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
