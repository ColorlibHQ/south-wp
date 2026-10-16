import { chromium } from 'playwright';
const [url, sel, prop] = process.argv.slice(2);
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1440, height: 900 } });
await p.goto(url, { waitUntil: 'domcontentloaded' }); await p.waitForTimeout(1500);
console.log(JSON.stringify(await p.evaluate(([sel, prop]) => {
  const el = document.querySelectorAll(sel)[1] || document.querySelector(sel); const out = [];
  for (const sh of document.styleSheets) { let rules; try { rules = sh.cssRules; } catch (e) { continue; }
    const walk = (rs) => { for (const r of rs) { if (r.cssRules && !r.selectorText) walk(r.cssRules); else if (r.selectorText && r.style.getPropertyValue(prop)) { try { if (el.matches(r.selectorText)) out.push(r.selectorText.slice(0,120) + ' => ' + r.style.getPropertyValue(prop) + (r.style.getPropertyPriority(prop)?' !':'')); } catch(e){} } } };
    walk(rules); }
  return { computed: getComputedStyle(el)[prop], out };
}, [sel, prop]), null, 1));
await b.close();
