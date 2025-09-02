const el = (id) => document.getElementById(id);

async function getProfile() {
  return new Promise((resolve) => {
    chrome.runtime.sendMessage({ type: 'getProfile' }, (res) => resolve(res));
  });
}

async function syncProfile() {
  return new Promise((resolve) => {
    chrome.runtime.sendMessage({ type: 'syncProfile' }, (res) => resolve(res));
  });
}

async function getActiveTab() {
  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
  return tab;
}

function setStatus(text, kind = 'info') {
  el('status').textContent = text;
  const dot = el('statusDot');
  dot.classList.remove('ok', 'err');
  if (kind === 'ok') dot.classList.add('ok');
  if (kind === 'err') dot.classList.add('err');
}

function setBusy(busy) {
  el('sync').disabled = busy;
  el('fill').disabled = busy;
}

function humanTime(ts) {
  if (!ts) return '—';
  try {
    const d = new Date(ts);
    return d.toLocaleString();
  } catch { return '—'; }
}

function detectSite(url) {
  try {
    const u = new URL(url);
    const h = u.hostname;
    if (/greenhouse\.io$/.test(h)) return 'Greenhouse';
    if (/lever\.co$/.test(h)) return 'Lever';
    if (/workday/.test(h)) return 'Workday';
    if (/smartrecruiters\.com$/.test(h)) return 'SmartRecruiters';
    if (/ashbyhq\.com$/.test(h)) return 'Ashby';
    return h;
  } catch { return '—'; }
}

async function refreshHeader() {
  const res = await getProfile();
  const tab = await getActiveTab();
  const host = tab?.url ? detectSite(tab.url) : '—';
  el('sitePill').textContent = host || '—';
  if (res?.ok && res.profile) {
    el('profileName').textContent = res.profile.name || 'Profile';
    const { profileUpdatedAt } = await chrome.storage.local.get(['profileUpdatedAt']);
    el('lastSync').textContent = `Last sync: ${humanTime(profileUpdatedAt)}`;
    setStatus('Ready', 'ok');
  } else {
    el('profileName').textContent = 'No profile';
    el('lastSync').textContent = 'Not synced';
    setStatus('No profile cached. Open Options to connect.');
  }
}

el('sync').addEventListener('click', async () => {
  setBusy(true);
  setStatus('Syncing…');
  const res = await syncProfile();
  if (res?.ok) {
    await refreshHeader();
    setStatus(`Synced ✓`, 'ok');
  } else {
    setStatus(`Sync failed: ${res?.error || 'Unknown error'}`, 'err');
  }
  setBusy(false);
});

el('fill').addEventListener('click', async () => {
  setBusy(true);
  const tab = await getActiveTab();
  if (!tab?.id) { setStatus('No active tab.', 'err'); setBusy(false); return; }

  chrome.tabs.sendMessage(tab.id, { type: 'autofill' }, async (res) => {
    if (chrome.runtime.lastError) {
      try {
        await chrome.scripting.executeScript({ target: { tabId: tab.id, allFrames: true }, files: ['content.js'] });
        chrome.tabs.sendMessage(tab.id, { type: 'autofill' }, (res2) => {
          if (chrome.runtime.lastError) { setStatus('Could not reach page content.', 'err'); setBusy(false); return; }
          if (res2?.ok) { setStatus(`Filled ${res2.filled || 0} field(s).`, 'ok'); } else { setStatus('Autofill failed.', 'err'); }
          setBusy(false);
        });
      } catch (e) {
        setStatus('Could not reach page content.', 'err');
        setBusy(false);
      }
      return;
    }
    if (res?.ok) { setStatus(`Filled ${res.filled || 0} field(s).`, 'ok'); } else { setStatus('Autofill failed.', 'err'); }
    setBusy(false);
  });
});

(async function init() {
  const tab = await getActiveTab();
  el('detected').textContent = tab?.url ? new URL(tab.url).hostname : 'No tab';
  await refreshHeader();
})();
