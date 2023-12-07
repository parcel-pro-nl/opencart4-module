import { Page } from '@playwright/test';

export async function navigateAdmin(page: Page, route: string) {
  await page.goto(`/administration/index.php?route=${route}&user_token=${process.env.OC_USER_TOKEN}`);
}
