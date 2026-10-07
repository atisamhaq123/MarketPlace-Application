import { copyFileSync, mkdirSync, watch } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { resolve } from 'node:path';

const root = fileURLToPath(new URL('../../', import.meta.url));
const files = [['resources/css/app.css', 'public/css/marketplace.css']];
function build() {
    mkdirSync(resolve(root, 'public/css'), { recursive: true });
    for (const [source, destination] of files) {
        copyFileSync(resolve(root, source), resolve(root, destination));
    }
    console.log('Marketplace assets ready.');
}
build();
if (process.argv.includes('--watch')) {
    for (const [source] of files) watch(resolve(root, source), build);
    console.log('Watching styles. Refresh your browser after editing app.css.');
}
