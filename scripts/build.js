const fs = require('fs-extra');
const AdmZip = require('adm-zip');
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

    const zip = new AdmZip();

    // Whitelist approach: Only bundle what is needed for production
    const allowed = [
        'build',            // Compiled React UI
        'includes',         // PHP core and Pro logic
        'vendor',           // Freemius SDK & ActionScheduler
        'wp-geoscale.php',  // Plugin bootstrapper
        'readme.txt',       // Plugin metadata
    ];

    console.log('📦 Bundling files...');
    for (const item of allowed) {
        const itemPath = path.join(rootDir, item);
        if (fs.existsSync(itemPath)) {
            const stat = fs.statSync(itemPath);
            if (stat.isDirectory()) {
                zip.addLocalFolder(itemPath, `${pluginName}/${item}`);
            } else {
                zip.addLocalFile(itemPath, pluginName);
            }
        } else {
            console.warn(`⚠️ Warning: ${item} not found!`);
        }
    }

    console.log('💾 Writing zip file...');
    zip.writeZip(zipPath);
    console.log(`✅ Build complete!`);
    console.log(`📁 Your premium zip is ready at: ${zipPath}`);
}

build();
