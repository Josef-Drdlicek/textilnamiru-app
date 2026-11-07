/**
 * Vyrobí webové varianty fotek z originálů v resources/images/.
 *
 *   npm run images
 *
 * Názvy originálů určují použití (viz .cursor/rules/obsah-a-fotky.mdc):
 *   hero*.jpg       → public/images/web/hero*-{960,1600,2400}.webp + -1600.jpg
 *   tile-*.jpg      → public/images/web/tile-*.webp + .jpg (1200 px)
 *   gallery-*.jpg   → public/images/web/gallery-*.webp + .jpg (1600 px)
 *   podhled-*.jpg   → public/images/web/podhled-*.webp + .jpg (1200 px)
 *   og.jpg          → public/images/web/og.jpg (1200×630)
 *   logo.png        → public/images/logo-72.png, logo-144.png (kruhový ořez, průhlednost)
 * Ostatní soubory dostanou WebP + JPG do 1600 px pod stejným názvem.
 */
import sharp from 'sharp';
import fs from 'node:fs';
import path from 'node:path';

const src = path.resolve('resources/images');
const webDir = path.resolve('public/images/web');
const imgDir = path.resolve('public/images');

if (!fs.existsSync(src)) {
    console.error(`Chybí složka ${src}. Vlož do ní originály fotek a spusť znovu.`);
    process.exit(1);
}
fs.mkdirSync(webDir, { recursive: true });

const files = fs.readdirSync(src).filter((f) => /\.(jpe?g|png|webp)$/i.test(f));
if (files.length === 0) {
    console.log('resources/images je prázdná, není co dělat.');
    process.exit(0);
}

const base = (file) => file.replace(/\.[^.]+$/, '');

async function responsive(file, widths, jpgWidth) {
    const name = base(file);
    const input = path.join(src, file);
    for (const w of widths) {
        await sharp(input).resize({ width: w, withoutEnlargement: true }).webp({ quality: 80 }).toFile(path.join(webDir, `${name}-${w}.webp`));
    }
    await sharp(input).resize({ width: jpgWidth, withoutEnlargement: true }).jpeg({ quality: 82, mozjpeg: true }).toFile(path.join(webDir, `${name}-${jpgWidth}.jpg`));
    console.log(`${file} → ${name}-{${widths.join(',')}}.webp + ${name}-${jpgWidth}.jpg`);
}

async function single(file, width) {
    const name = base(file);
    const input = path.join(src, file);
    await sharp(input).resize({ width, withoutEnlargement: true }).webp({ quality: 82 }).toFile(path.join(webDir, `${name}.webp`));
    await sharp(input).resize({ width, withoutEnlargement: true }).jpeg({ quality: 84, mozjpeg: true }).toFile(path.join(webDir, `${name}.jpg`));
    console.log(`${file} → ${name}.webp + ${name}.jpg (${width} px)`);
}

async function logo(file) {
    for (const size of [72, 144]) {
        const circle = Buffer.from(`<svg width="${size}" height="${size}"><circle cx="${size / 2}" cy="${size / 2}" r="${size / 2}"/></svg>`);
        await sharp(path.join(src, file)).resize(size, size).composite([{ input: circle, blend: 'dest-in' }]).png({ compressionLevel: 9 }).toFile(path.join(imgDir, `logo-${size}.png`));
    }
    console.log(`${file} → logo-72.png, logo-144.png`);
}

for (const file of files) {
    const name = base(file);
    if (name === 'logo') await logo(file);
    else if (name === 'og') await sharp(path.join(src, file)).resize(1200, 630, { fit: 'cover' }).jpeg({ quality: 82, mozjpeg: true }).toFile(path.join(webDir, 'og.jpg'));
    else if (name.startsWith('hero')) await responsive(file, [960, 1600, 2400], 1600);
    else if (name.startsWith('tile-') || name.startsWith('podhled-')) await single(file, 1200);
    else if (name.startsWith('gallery-')) await single(file, 1600);
    else await single(file, 1600);
}
console.log('Hotovo. Zkontroluj web a commitni resources/images i public/images.');
