const fs = require('fs-extra');
const AdmZip = require('adm-zip');
const path = require('path');
const { execSync } = require('child_process');

const pluginName = 'wp-geoscale';
const rootDir = path.resolve(__dirname, '..');
const outDir = path.join(rootDir, 'dist');
const premiumZipPath = path.join(outDir, `${pluginName}-premium.zip`);
const freeZipPath = path.join(outDir, `${pluginName}-free.zip`);
const freeRepoDir = path.join(outDir, 'geoscale-free-repo');
const FREE_REPO_URL = 'https://github.com/romosamatya/geoscale-seo-engine.git';

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
        'composer.json',    // Required by WordPress.org
    ];

    console.log('📦 Bundling premium zip...');
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

    console.log('💾 Writing premium zip file...');
    zip.writeZip(premiumZipPath);
    console.log(`✅ Premium zip ready: ${premiumZipPath}`);

    // ---------------------------------------------------------
    // Push free-only version to the public GitHub repo
    // Excludes: includes/pro/ (paid features)
    // ---------------------------------------------------------
    console.log('\n🌐 Pushing free version to public repo...');

    const freeAllowed = [
        'build',
        'includes',         // Will filter out /pro subfolder below
        'vendor',
        'wp-geoscale.php',
        'readme.txt',
        'composer.json',
        'package.json',
        'scripts',
    ];

    fs.mkdirpSync(freeRepoDir);

    // Clone or init the public repo
    try {
        execSync(`git clone ${FREE_REPO_URL} "${freeRepoDir}"`, { stdio: 'inherit' });
    } catch (e) {
        // Already cloned or init fresh
        execSync(`git -C "${freeRepoDir}" init`, { stdio: 'inherit' });
        execSync(`git -C "${freeRepoDir}" remote add origin ${FREE_REPO_URL}`, { stdio: 'pipe' });
    }

    // Clear existing contents (except .git)
    const existingFiles = fs.readdirSync(freeRepoDir).filter(f => f !== '.git');
    for (const f of existingFiles) {
        fs.removeSync(path.join(freeRepoDir, f));
    }

    // Copy allowed files — excluding includes/pro
    for (const item of freeAllowed) {
        const src = path.join(rootDir, item);
        const dest = path.join(freeRepoDir, item);
        if (!fs.existsSync(src)) continue;

        if (item === 'includes') {
            // Copy includes but skip the pro subfolder
            fs.copySync(src, dest, {
                filter: (srcPath) => {
                    const relativePath = path.relative(src, srcPath);
                    return !relativePath.startsWith('pro');
                }
            });
        } else {
            fs.copySync(src, dest);
        }
    }

    // ---------------------------------------------------------
    // Strip PRO features for WordPress.org compliance
    // ---------------------------------------------------------
    console.log('✂️  Stripping PRO code from free version...');
    const apiFile = path.join(freeRepoDir, 'includes/class-geoscale-api.php');
    if (fs.existsSync(apiFile)) {
        let content = fs.readFileSync(apiFile, 'utf8');
        content = content.replace(/\/\/ PRO-ONLY-START[\s\S]*?\/\/ PRO-ONLY-END\r?\n?/g, '');
        fs.writeFileSync(apiFile, content);
    }

    const mainFile = path.join(freeRepoDir, 'wp-geoscale.php');
    if (fs.existsSync(mainFile)) {
        let content = fs.readFileSync(mainFile, 'utf8');
        content = content.replace(/\/\/ PRO-ONLY-START[\s\S]*?\/\/ PRO-ONLY-END\r?\n?/g, '');
        content = content.replace(/'is_premium'\s*=>\s*true,/, "'is_premium'          => false,");
        fs.writeFileSync(mainFile, content);
    }

    // Commit and push
    execSync(`git -C "${freeRepoDir}" add -A`, { stdio: 'inherit' });
    try {
        execSync(`git -C "${freeRepoDir}" commit -m "Release: free version sync"`, { stdio: 'inherit' });
        execSync(`git -C "${freeRepoDir}" push origin main --force`, { stdio: 'inherit' });
        console.log('✅ Free version pushed to public repo!');
        console.log(`🔗 https://github.com/romosamatya/geoscale-seo-engine`);
    } catch (e) {
        console.log('ℹ️  Nothing new to push to public repo.');
    }

    // ---------------------------------------------------------
    // Create the Free ZIP for WordPress.org
    // ---------------------------------------------------------
    console.log('\n📦 Bundling free zip for WordPress.org...');
    const freeZip = new AdmZip();
    
    // We already copied the free files to freeRepoDir, so let's zip that folder
    const freeFiles = fs.readdirSync(freeRepoDir).filter(f => f !== '.git');
    for (const f of freeFiles) {
        const itemPath = path.join(freeRepoDir, f);
        const stat = fs.statSync(itemPath);
        if (stat.isDirectory()) {
            freeZip.addLocalFolder(itemPath, `${pluginName}/${f}`);
        } else {
            freeZip.addLocalFile(itemPath, pluginName);
        }
    }
    
    console.log('💾 Writing free zip file...');
    freeZip.writeZip(freeZipPath);
    console.log(`✅ Free zip ready: ${freeZipPath}`);
    console.log('\n🚀 ALL DONE!');
    console.log(`1. Upload ${pluginName}-premium.zip to Freemius`);
    console.log(`2. Upload ${pluginName}-free.zip to WordPress.org`);
}

build();
