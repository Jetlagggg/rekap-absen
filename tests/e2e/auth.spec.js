import { expect, test } from '@playwright/test';
import { ADMIN, login, logout } from './helpers.js';

test('tamu diarahkan ke halaman login', async ({ page }) => {
    await page.goto('/');

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByRole('heading', { name: 'Masuk ke Rekap Absen' })).toBeVisible();
});

test('login dengan kredensial salah ditolak', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email', { exact: true }).fill(ADMIN.email);
    await page.getByLabel('Kata Sandi', { exact: true }).fill('kata-sandi-yang-salah');
    await page.getByRole('button', { name: /Masuk/ }).click();

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.locator('.alert-danger')).toContainText('Email atau kata sandi tidak sesuai.');
});

test('admin dapat masuk dan keluar', async ({ page }) => {
    await login(page);
    await logout(page);
});
