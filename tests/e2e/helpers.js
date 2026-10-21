import { expect } from '@playwright/test';

export const ADMIN = {
    email: 'admin@e2e.test',
    password: 'e2e-admin-password',
};

export const OPERATOR = {
    email: 'operator@e2e.test',
    password: 'e2e-operator-password',
};

export async function login(page, user = ADMIN) {
    await page.goto('/login');
    await page.getByLabel('Email', { exact: true }).fill(user.email);
    await page.getByLabel('Kata Sandi', { exact: true }).fill(user.password);
    await page.getByRole('button', { name: /Masuk/ }).click();

    await expect(page).toHaveURL(/\/$/);
    await expect(page.getByRole('heading', { name: 'Dashboard', exact: true })).toBeVisible();
}

export async function logout(page) {
    await page.getByRole('button', { name: 'Keluar dari aplikasi' }).click();

    await expect(page).toHaveURL(/\/login$/);
}
