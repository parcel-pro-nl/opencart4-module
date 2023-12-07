import { test } from '@playwright/test';
import { navigateAdmin } from './helpers/navigate';

test('configure geo zones', async ({ page }) => {
  await navigateAdmin(page, 'localisation/geo_zone');

  // TODO: Check if NL zone exists, create it.
});
