import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';

const E2E_DATABASE = 'database/e2e.sqlite';

export default function globalSetup() {
    const env = readFileSync(resolve(process.cwd(), '.env.e2e'), 'utf8');

    if (!/^APP_ENV=e2e$/m.test(env) || !/^DB_DATABASE=database\/e2e\.sqlite$/m.test(env)) {
        throw new Error('Konfigurasi .env.e2e tidak aman: APP_ENV harus e2e dan DB_DATABASE harus database/e2e.sqlite.');
    }

    const databasePath = resolve(process.cwd(), E2E_DATABASE);

    mkdirSync(dirname(databasePath), { recursive: true });

    if (!existsSync(databasePath)) {
        writeFileSync(databasePath, '');
    }

    const commands = [
        ['artisan', 'optimize:clear', '--env=e2e'],
        ['artisan', 'migrate:fresh', '--env=e2e', '--force'],
        ['artisan', 'db:seed', '--class=E2eSeeder', '--env=e2e', '--force'],
    ];

    for (const args of commands) {
        execFileSync('php', args, { stdio: 'inherit' });
    }
}
