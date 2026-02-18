import { defineConfig } from 'vite';
import { resolve } from 'path';
import liveReload from 'vite-plugin-live-reload';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        liveReload([
            '**/*.php',
            'assets/scss/**/*.scss',
        ]),
        {
            name: 'copy-css-on-build',
            writeBundle() {
                const header = `/*
Theme Name: Medical Custom
Theme URI: https://alexis.dydlabs.com/
Author: Alexis Tomaselli
Author URI: https://alexis.dydlabs.com/
Description: Custom theme for Medical semi-senior technical test simulation.
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: medical-theme
*/\n`;

                const cssPath = resolve(__dirname, 'dist/assets/main.css');
                const destPath = resolve(__dirname, 'style.css');

                if (fs.existsSync(cssPath)) {
                    const cssContent = fs.readFileSync(cssPath, 'utf8');
                    fs.writeFileSync(destPath, header + cssContent);
                    console.log('✅ CSS compilado y copiado a style.css con cabecera de WordPress');
                } else {
                    console.error('❌ Error: No se encontró dist/assets/main.css');
                }
            },
        },
    ],
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'assets/scss/style.scss'),
            },
            output: {
                entryFileNames: 'style.js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name === 'style.css') {
                        return 'assets/main.css';
                    }
                    return 'assets/[name].[ext]';
                },
            },
        },
    },
    css: {
        devSourcemap: true,
    },
    server: {
        port: 3000,
        strictPort: false,
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
});
