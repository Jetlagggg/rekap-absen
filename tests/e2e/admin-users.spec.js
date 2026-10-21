import { expect, test } from '@playwright/test';
import { ADMIN, login, logout } from './helpers.js';

test('admin membuat operator dan operator tidak dapat membuka panel pengguna', async ({ page }) => {
    const email = `operator-${Date.now()}@e2e.test`;
    const password = 'operator-baru-123';

    await login(page, ADMIN);

    await page.getByRole('link', { name: 'Pengguna', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Pengguna & Role' })).toBeVisible();

    await page.getByLabel('Nama lengkap', { exact: true }).fill('Operator Baru');
    await page.getByLabel('Email', { exact: true }).fill(email);
    await page.getByLabel('Role', { exact: true }).selectOption('operator');
    await page.getByLabel('Kata sandi', { exact: true }).fill(password);
    await page.getByLabel('Ulangi kata sandi', { exact: true }).fill(password);
    await page.getByRole('button', { name: /Buat Pengguna/ }).click();

    await expect(page.getByText('Pengguna baru berhasil dibuat.')).toBeVisible();
    await expect(page.locator('tbody tr').filter({ hasText: email })).toBeVisible();

    await logout(page);
    await login(page, { email, password });

    await expect(page.getByRole('link', { name: 'Pengguna', exact: true })).toHaveCount(0);

    const response = await page.goto('/admin/users');

    expect(response?.status()).toBe(403);
    await expect(page.getByText('403')).toBeVisible();
});
