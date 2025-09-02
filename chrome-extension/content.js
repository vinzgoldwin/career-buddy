// Content script: attempts to autofill fields using stored profile

function dispatchInputEvents(el) {
  el.dispatchEvent(new Event('input', { bubbles: true }));
  el.dispatchEvent(new Event('change', { bubbles: true }));
}

function setValue(el, value) {
  if (!el) return false;
  if (el.type === 'checkbox' || el.type === 'radio') {
    el.checked = Boolean(value);
  } else {
    el.value = value ?? '';
  }
  dispatchInputEvents(el);
  return true;
}

function findInputs(doc) {
  return Array.from(doc.querySelectorAll('input, textarea, select'));
}

function matches(el, patterns) {
  const attrs = [
    el.name || '',
    el.id || '',
    el.getAttribute('aria-label') || '',
    el.getAttribute('placeholder') || '',
  ].join(' ').toLowerCase();
  return patterns.some((p) => attrs.includes(p));
}

function labelTextFor(el) {
  const id = el.id;
  if (id) {
    const label = document.querySelector(`label[for="${CSS.escape(id)}"]`);
    if (label) return label.innerText.toLowerCase();
  }
  let parent = el.parentElement;
  for (let i = 0; i < 2 && parent; i++) {
    const lbl = parent.querySelector('label');
    if (lbl) return lbl.innerText.toLowerCase();
    parent = parent.parentElement;
  }
  return '';
}

function matchesWithLabel(el, patterns) {
  const label = labelTextFor(el);
  const combined = [
    el.name || '',
    el.id || '',
    el.getAttribute('aria-label') || '',
    el.getAttribute('placeholder') || '',
    label,
  ].join(' ').toLowerCase();
  return patterns.some((p) => combined.includes(p));
}

function recordFieldDetail(details, type, el) {
  try {
    details.push({
      type: type || '',
      name: el.name || '',
      id: el.id || '',
      ariaLabel: el.getAttribute('aria-label') || '',
      placeholder: el.getAttribute('placeholder') || '',
      label: labelTextFor(el) || '',
      tag: el.tagName || '',
    });
  } catch {}
}

function fillElems(elems, value, type, details) {
  let n = 0;
  for (const el of elems) {
    if (setValue(el, value)) {
      n++;
      if (details) recordFieldDetail(details, type, el);
    }
  }
  return n;
}

function autofillWithProfile(profile, details = []) {
  if (!profile) return { filled: 0, details };
  const inputs = findInputs(document);
  let filled = 0;

  const map = [
    { key: 'name', patterns: ['full name', 'name', 'first name last name'] },
    { key: 'email', patterns: ['email', 'e-mail'] },
    { key: 'phone', patterns: ['phone', 'mobile'] },
    { key: 'location', patterns: ['location', 'city', 'address'] },
    { key: 'website', patterns: ['website', 'portfolio', 'github', 'personal site', 'url', 'linkedin'] },
    { key: 'summary', patterns: ['summary', 'about', 'cover letter'] },
  ];

  for (const { key, patterns } of map) {
    const value = profile[key];
    if (!value) continue;
    for (const el of inputs) {
      if (matchesWithLabel(el, patterns)) {
        if (setValue(el, value)) { filled++; recordFieldDetail(details, key, el); }
      }
    }
  }

  // Try to fill a big textarea for additional info with summary if present
  if (profile.summary) {
    const largeAreas = inputs.filter((i) => i.tagName === 'TEXTAREA' && (i.rows || 0) >= 4);
    for (const ta of largeAreas) {
      if (ta.value && ta.value.length > 0) continue;
      if (setValue(ta, profile.summary)) {
        filled++;
        recordFieldDetail(details, 'summary', ta);
        break;
      }
    }
  }

  return { filled, details };
}

async function getStoredProfile() {
  return new Promise((resolve) => {
    chrome.storage.local.get(['profile'], (data) => resolve(data.profile || null));
  });
}

chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
  (async () => {
    if (msg?.type === 'autofill') {
      const profile = (await getStoredProfile()) || null;
      const result = await autofillDispatcher(profile);
      try { await logAutofill(profile, result); } catch {}
      sendResponse({ ok: true, ...result });
      return;
    }
  })();
  return true;
});

// ============== Site-specific mappings ==============

function splitName(full) {
  if (!full) return { first: '', last: '' };
  const parts = String(full).trim().split(/\s+/);
  if (parts.length === 1) return { first: parts[0], last: '' };
  return { first: parts.slice(0, -1).join(' '), last: parts.slice(-1)[0] };
}

function qAll(sel) { return Array.from(document.querySelectorAll(sel)); }
function fillAll(elems, value) { let n=0; for (const el of elems) { if (setValue(el, value)) n++; } return n; }
function fillNames(selectors, value, type, details) { return fillElems(qAll(selectors.map(s=>`input[name="${s}"]`).join(',')), value, type, details); }
function fillIds(selectors, value, type, details) { return fillElems(qAll(selectors.map(s=>`#${CSS.escape(s)}`).join(',')), value, type, details); }
function fillAriaLabel(patterns, value, type, details) {
  const elems = findInputs(document).filter(el => {
    const l = (el.getAttribute('aria-label') || '').toLowerCase();
    return patterns.some(p => l.includes(p));
  });
  return fillElems(elems, value, type, details);
}
function fillByPlaceholder(patterns, value, type, details) {
  const elems = findInputs(document).filter(el => {
    const l = (el.getAttribute('placeholder') || '').toLowerCase();
    return patterns.some(p => l.includes(p));
  });
  return fillElems(elems, value, type, details);
}

function currentHost() { try { return location.hostname; } catch { return ''; } }
function isHost(re) { return re.test(currentHost()); }

function greenhouse(profile) {
  let filled = 0; const details = [];
  const { first, last } = splitName(profile.name);
  filled += fillNames([
    'job_application[first_name]'
  ], first, 'first_name', details);
  filled += fillNames([
    'job_application[last_name]'
  ], last, 'last_name', details);
  filled += fillNames([
    'job_application[email]'
  ], profile.email, 'email', details);
  filled += fillNames([
    'job_application[phone]'
  ], profile.phone, 'phone', details);
  filled += fillNames([
    'job_application[location]','job_application[city]','job_application[address]'
  ], profile.location, 'location', details);

  // Links
  if (profile.website) {
    const site = String(profile.website).toLowerCase();
    if (site.includes('linkedin')) {
      filled += fillNames(['job_application[urls][LinkedIn]'], profile.website, 'url.linkedin', details);
    } else if (site.includes('github')) {
      filled += fillNames(['job_application[urls][GitHub]'], profile.website, 'url.github', details);
    }
    filled += fillNames(['job_application[urls][Portfolio]','job_application[website]'], profile.website, 'url.portfolio', details);
  }

  // Cover letter / summary
  filled += fillElems(qAll('textarea[name="job_application[cover_letter_text]"]'), profile.summary, 'cover_letter', details);

  return { filled, details };
}

function lever(profile) {
  let filled = 0; const details = [];
  const { first, last } = splitName(profile.name);
  // Lever supports combined name and split fields in some instances
  filled += fillElems(qAll('input[name="name"]'), profile.name, 'name', details);
  filled += fillElems(qAll('input[name="firstName"]'), first, 'first_name', details);
  filled += fillElems(qAll('input[name="lastName"]'), last, 'last_name', details);
  filled += fillElems(qAll('input[name="email"]'), profile.email, 'email', details);
  filled += fillElems(qAll('input[name="phone"]'), profile.phone, 'phone', details);
  filled += fillElems(qAll('input[name="location"]'), profile.location, 'location', details);

  if (profile.website) {
    filled += fillElems(qAll('input[name="urls[LinkedIn]"]'), profile.website, 'url.linkedin', details);
    filled += fillElems(qAll('input[name="urls[GitHub]"]'), profile.website, 'url.github', details);
    filled += fillElems(qAll('input[name="urls[Portfolio]"]'), profile.website, 'url.portfolio', details);
    filled += fillElems(qAll('input[name="website"]'), profile.website, 'website', details);
  }

  filled += fillElems(qAll('textarea[name="comments"], textarea[name="coverLetterText"]'), profile.summary, 'cover_letter', details);
  return { filled, details };
}

function workday(profile) {
  let filled = 0; const details = [];
  const { first, last } = splitName(profile.name);
  filled += fillAriaLabel(['first name'], first, 'first_name', details);
  filled += fillAriaLabel(['last name'], last, 'last_name', details);
  filled += fillAriaLabel(['email'], profile.email, 'email', details);
  filled += fillAriaLabel(['phone'], profile.phone, 'phone', details);
  filled += fillAriaLabel(['city','location','address'], profile.location, 'location', details);
  filled += fillAriaLabel(['linkedin','portfolio','website','url'], profile.website, 'website', details);
  filled += fillByPlaceholder(['cover letter','summary','about'], profile.summary, 'cover_letter', details);
  return { filled, details };
}

function sapSuccessFactors(profile) {
  let filled = 0; const details = [];
  const { first, last } = splitName(profile.name);
  filled += fillIds(['firstName'], first, 'first_name', details);
  filled += fillIds(['lastName'], last, 'last_name', details);
  filled += fillIds(['email','emailAddress'], profile.email, 'email', details);
  filled += fillIds(['phone','phoneNumber','mobileNumber'], profile.phone, 'phone', details);
  filled += fillIds(['city','location','address'], profile.location, 'location', details);
  // Fallback to aria-labels
  filled += fillAriaLabel(['first name','last name','email','phone','city','location'], profile.location, 'location', details);
  return { filled, details };
}

function genericApply(profile) {
  // Use existing heuristic as fallback
  return autofillWithProfile(profile, []);
}

function indeed(profile) { return genericApply(profile); }
function linkedin(profile) { return genericApply(profile); }
function glassdoor(profile) { return genericApply(profile); }
function ziprecruiter(profile) { return genericApply(profile); }

async function autofillDispatcher(profile) {
  if (!profile) return { filled: 0, details: [] };
  let result = { filled: 0, details: [] };
  const host = currentHost();
  try {
    if (/greenhouse\.io$/.test(host)) result = greenhouse(profile);
    else if (/lever\.co$/.test(host)) result = lever(profile);
    else if (/workday/.test(host)) result = workday(profile);
    else if (/successfactors\.com$/.test(host) || /sap\.com$/.test(host)) result = sapSuccessFactors(profile);
    else if (/indeed\.com$/.test(host)) result = indeed(profile);
    else if (/linkedin\.com$/.test(host)) result = linkedin(profile);
    else if (/glassdoor\.com$/.test(host)) result = glassdoor(profile);
    else if (/ziprecruiter\.com$/.test(host)) result = ziprecruiter(profile);
    else result = genericApply(profile);
  } catch (e) {
    // Swallow mapping errors and fallback
    try { result = genericApply(profile); } catch {}
  }
  return result;
}

// ============== In-page mini banner ==============

function supportedHost() {
  const host = currentHost();
  return (
    /greenhouse\.io$/.test(host) ||
    /lever\.co$/.test(host) ||
    /workday/.test(host) ||
    /successfactors\.com$/.test(host) ||
    /sap\.com$/.test(host) ||
    /indeed\.com$/.test(host) ||
    /linkedin\.com$/.test(host) ||
    /glassdoor\.com$/.test(host) ||
    /ziprecruiter\.com$/.test(host)
  );
}

function likelyFormPresent() {
  try {
    const count = findInputs(document).length;
    return count >= 3; // avoid showing on non-form pages
  } catch { return false; }
}

function createBannerRoot() {
  if (document.getElementById('cb-autofill-root')) return document.getElementById('cb-autofill-root');
  const host = document.createElement('div');
  host.id = 'cb-autofill-root';
  host.style.position = 'fixed';
  host.style.right = '16px';
  host.style.bottom = '16px';
  host.style.zIndex = '2147483647';
  host.style.all = 'initial';
  // Shadow DOM to isolate styles
  const shadow = host.attachShadow ? host.attachShadow({ mode: 'open' }) : null;
  document.documentElement.appendChild(host);
  return host;
}

function renderBanner(shadowOrHost, onClick) {
  const root = shadowOrHost.shadowRoot || shadowOrHost;
  // Clear
  while (root.firstChild) root.removeChild(root.firstChild);
  const style = document.createElement('style');
  style.textContent = `
    .wrap{display:flex;align-items:center;gap:8px;background:#11151a;color:#fff;border:1px solid #2a2f37;border-radius:999px;padding:8px 10px;box-shadow:0 6px 20px rgba(0,0,0,.35);font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;}
    .btn{cursor:pointer;background:#2563eb;color:#fff;border:none;border-radius:999px;padding:6px 10px;font-size:12px}
    .txt{font-size:12px;opacity:.9}
    .x{cursor:pointer;background:transparent;border:none;color:#9aa1aa;font-size:14px;margin-left:4px}
    .logo{width:14px;height:14px;opacity:.9}
  `;
  const wrap = document.createElement('div');
  wrap.className = 'wrap';

  const logo = document.createElementNS('http://www.w3.org/2000/svg','svg');
  logo.setAttribute('viewBox','0 0 24 24');
  logo.setAttribute('fill','currentColor');
  logo.classList.add('logo');
  logo.innerHTML = '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3Z" opacity=".22"></path><path d="M12 3v18M4 7.5l8 4.5 8-4.5" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></path>';

  const text = document.createElement('span');
  text.textContent = 'Fill with Career Buddy';
  text.className = 'txt';

  const btn = document.createElement('button');
  btn.textContent = 'Fill';
  btn.className = 'btn';
  btn.addEventListener('click', (e) => { e.preventDefault(); onClick && onClick(); });

  const close = document.createElement('button');
  close.textContent = '×';
  close.title = 'Hide';
  close.className = 'x';
  close.addEventListener('click', () => {
    const host = (shadowOrHost.shadowRoot ? shadowOrHost : shadowOrHost.parentElement);
    if (host) host.remove();
  });

  wrap.append(logo, text, btn, close);
  root.append(style, wrap);
}

async function ensureBanner() {
  if (window.self !== window.top) return; // top frame only
  if (!supportedHost()) return;
  if (!likelyFormPresent()) return;
  const profile = await getStoredProfile();
  if (!profile) return; // show only when ready to fill
  const host = createBannerRoot();
  renderBanner(host, async () => {
    try {
      const result = await autofillDispatcher(profile);
      try { await logAutofill(profile, result); } catch {}
      // Briefly change label to show filled count
      const root = host.shadowRoot || host;
      const btn = root.querySelector('.btn');
      if (btn) {
        const prev = btn.textContent; btn.textContent = `Filled ${result.filled}`;
        setTimeout(() => { try { btn.textContent = prev; } catch {} }, 1500);
      }
    } catch {}
  });
}

// Observe DOM for dynamic forms and inject banner once when conditions are met
let bannerAttempted = false;
function observeAndInject() {
  if (bannerAttempted) return;
  const doTry = async () => {
    if (bannerAttempted) return;
    if (supportedHost() && likelyFormPresent()) {
      bannerAttempted = true;
      await ensureBanner();
      obs.disconnect();
    }
  };
  const obs = new MutationObserver(() => { doTry(); });
  obs.observe(document.documentElement || document.body, { childList: true, subtree: true });
  // Also attempt after load
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    doTry();
  } else {
    window.addEventListener('DOMContentLoaded', doTry, { once: true });
    window.addEventListener('load', doTry, { once: true });
  }
}

observeAndInject();

// ============== Logging ==============

function guessJobDetails() {
  const url = location.href;
  const host = currentHost();
  let jobTitle = '';
  const h = document.querySelector('h1, h2');
  if (h) jobTitle = (h.textContent || '').trim().slice(0, 140);
  let company = '';
  // Try meta
  const metaSite = document.querySelector('meta[property="og:site_name"], meta[name="application-name"]');
  if (metaSite?.getAttribute('content')) company = metaSite.getAttribute('content');
  if (!company) {
    // Path-based heuristics
    const m1 = url.match(/greenhouse\.io\/(?:company|boards)\/([^\/]+)/i);
    const m2 = url.match(/lever\.co\/([^\/]+)/i);
    const m3 = host.split('.')
                  .filter(Boolean)
                  .slice(0, -2)
                  .join(' ');
    company = (m1?.[1] || m2?.[1] || m3 || '').replace(/[-_]/g, ' ');
  }
  company = (company || '').trim();
  return { jobTitle, company, host, url };
}

async function logAutofill(profile, result) {
  const { jobTitle, company, host, url } = guessJobDetails();
  const fields = [];
  if (profile.name) fields.push('name');
  if (profile.email) fields.push('email');
  if (profile.phone) fields.push('phone');
  if (profile.location) fields.push('location');
  if (profile.website) fields.push('website');
  if (profile.summary) fields.push('summary');
  return new Promise((resolve) => {
    chrome.runtime.sendMessage({
      type: 'logAutofill',
      jobTitle,
      company,
      host,
      pageUrl: url,
      fields,
      fieldDetails: Array.isArray(result?.details) ? result.details : [],
      filledCount: result?.filled || 0,
    }, (res) => resolve(res));
  });
}
