const fs = require('fs-extra');
const AdmZip = require('adm-zip');
const path = require('path');

const pluginName = 'geoscale-free';
const rootDir = path.resolve(__dirname, '..');
const outDir = path.join(rootDir, 'dist');
const freeZipPath = path.join(outDir, `${pluginName}-free.zip`);

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
        'includes',         // PHP core
        'vendor',           // Freemius SDK & ActionScheduler
        'geoscale-free.php',  // Plugin bootstrapper
        'readme.txt',       // Plugin metadata
    ];

    console.log('📦 Bundling free zip for WordPress.org...');
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

    console.log('💾 Writing free zip file...');
    zip.writeZip(freeZipPath);
    console.log(`✅ Free zip ready for WordPress.org: ${freeZipPath}`);
}

build();
