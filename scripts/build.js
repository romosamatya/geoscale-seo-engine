const fs = require('fs-extra');
const archiver = require('archiver');
const path = require('path');

const pluginName = 'wp-geoscale';
const rootDir = path.resolve(__dirname, '..');
const outDir = path.join(rootDir, 'dist');
const zipPath = path.join(outDir, `${pluginName}.zip`);

async function build() {
    console.log('🧹 Cleaning old build...');
    if (fs.existsSync(outDir)) {
        fs.removeSync(outDir);
    }
    fs.mkdirpSync(outDir);

    const output = fs.createWriteStream(zipPath);
    const archive = archiver('zip', { zlib: { level: 9 } });

    output.on('close', () => {
        console.log(`✅ Build complete! ${archive.pointer()} total bytes`);
        console.log(`📁 Your premium zip is ready at: ${zipPath}`);
    });

    archive.on('error', (err) => {
        throw err;
    });

    archive.pipe(output);

    // Whitelist approach: Only bundle what is needed for production
    const allowed = [
        'build',            // Compiled React UI
        'includes',         // PHP core and Pro logic
        'vendor',           // Freemius SDK & ActionScheduler
        'wp-geoscale.php',  // Plugin bootstrapper
    ];

    console.log('📦 Bundling files...');
    for (const item of allowed) {
        const itemPath = path.join(rootDir, item);
        if (fs.existsSync(itemPath)) {
            const stat = fs.statSync(itemPath);
            if (stat.isDirectory()) {
                // The second parameter puts it inside a root folder named wp-geoscale in the zip
                archive.directory(itemPath + '/', `${pluginName}/${item}`);
            } else {
                archive.file(itemPath, { name: `${pluginName}/${item}` });
            }
        } else {
            console.warn(`⚠️ Warning: ${item} not found!`);
        }
    }

    await archive.finalize();
}

build();
