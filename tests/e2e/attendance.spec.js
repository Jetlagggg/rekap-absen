import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

test('admin mencatat absensi massal lalu melihat rekap TPP', async ({ page }) => {
    await login(page);

    await page.goto('/input-absen');

    const rows = page.locator('.absen-row');
    await expect(rows.first()).toBeVisible();

    const totalRows = await rows.count();

    for (let index = 0; index < totalRows; index++) {
        await rows.nth(index).locator('select.select-status').selectOption('sakit');
    }

    const andi = rows.filter({ hasText: 'Andi Pratama' });
    await andi.locator('select.select-status').selectOption('hadir');
    await andi.locator('.input-masuk').fill('08:15');
    await andi.locator('.input-pulang').fill('16:00');

    const rina = rows.filter({ hasText: 'Rina Wulandari' });
    await rina.locator('select.select-status').selectOption('izin');

    await page.getByRole('button', { name: /Simpan Perubahan/ }).click();

    await expect(page.getByText(/Berhasil menyimpan \d+ baris absensi\./)).toBeVisible();

    const savedAndi = page.locator('.absen-row').filter({ hasText: 'Andi Pratama' });
    await expect(savedAndi.locator('select.select-status')).toHaveValue('hadir');
    await expect(savedAndi.locator('.input-masuk')).toHaveValue('08:15');
    await expect(savedAndi.locator('.input-pulang')).toHaveValue('16:00');

    await page.goto('/laporan-tpp');

    await expect(page.getByRole('heading', { name: 'Laporan Kinerja' })).toBeVisible();

    const andiReport = page.locator('tbody tr').filter({ hasText: 'Andi Pratama' });
    await expect(andiReport).toContainText('1 Hari');
    await expect(andiReport).toContainText('-0.5%');
    await expect(andiReport).toContainText('99.5%');

    const rinaReport = page.locator('tbody tr').filter({ hasText: 'Rina Wulandari' });
    await expect(rinaReport).toContainText('0 Hari');
    await expect(rinaReport).toContainText('-1.75%');
    await expect(rinaReport).toContainText('98.25%');
});
