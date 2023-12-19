import { expect, test } from '@playwright/test';
import { STORAGE_STATE } from './playwright.config';

test('login', async ({ page }) => {
  // OpenCart

  await page.goto('/administration');

  await page.getByLabel('Username').fill('admin');
  await page.getByLabel('Password').fill('parcelpro1');
  await page.getByRole('button', { name: 'Login' }).click();

  await expect(page).toHaveTitle(/Dashboard/);

  // Store the user token query parameter in the env, for later use.
  const params = new URLSearchParams(page.url().split('?')[1]);
  process.env.OC_USER_TOKEN = params.get('user_token');

  // Parcel Pro

  await page.goto('https://login.parcelpro.nl');

  await page.locator('#email').fill(process.env.PP_USERNAME);
  await page.locator('#password').fill(process.env.PP_PASSWORD);
  await page.getByText('Inloggen').click();

  await expect(page).not.toHaveTitle(/Inloggen/);

  // Store the browser context.
  await page.context().storageState({ path: STORAGE_STATE });
});
