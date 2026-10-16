import { chromium } from 'playwright';
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1440, height: 900 } });
const fails = []; p.on('requestfailed', r => fails.push(r.url().slice(-60) + ' ' + r.failure().errorText)); p.on('response', r => { if (r.status() >= 400) fails.push(r.status() + ' ' + r.url().slice(-60)); });
await p.goto(process.argv[2], { waitUntil: 'load', timeout: 120000 });
await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 400) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 300)); } });
await p.waitForTimeout(5000);
console.log(await p.evaluate(() => [...document.images].filter(i => !i.naturalWidth).map(i => i.currentSrc.slice(-50) + ' loading=' + i.loading + ' vis=' + (i.offsetParent!==null))));
console.log(fails.slice(0, 10)); await b.close();
