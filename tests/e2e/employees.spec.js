import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

test('pegawai dapat ditambah, dicari, lalu dihapus', async ({ page }) => {
    const suffix = Date.now();
    const nama = `Pegawai E2E ${suffix}`;
    const nip = `99${suffix}`;

    await login(page);
    await page.goto('/tambah-pegawai');

    await page.locator('#nip').fill(nip);
    await page.locator('#nama').fill(nama);
    await page.locator('#jabatan').fill('Staf E2E');
    await page.getByRole('button', { name: /Simpan Pegawai/ }).click();

    await expect(page.getByText('Pegawai berhasil ditambahkan!')).toBeVisible();

    const row = page.locator('.employee-row').filter({ hasText: nama });
    await expect(row).toBeVisible();

    await page.getByLabel('Cari nama, NIP, atau jabatan').fill(nama);
    await expect(row).toBeVisible();

    page.once('dialog', (dialog) => dialog.accept());
    await row.getByRole('button', { name: `Hapus data ${nama}` }).click();

    await expect(page.getByText('Pegawai dihapus.')).toBeVisible();
    await expect(page.locator('.employee-row').filter({ hasText: nama })).toHaveCount(0);
});
