const el = (id) => document.getElementById(id);

function setStatus(msg, kind = 'info') {
  el('status').textContent = msg;
  el('status').classList.remove('ok', 'err');
  if (kind === 'ok') el('status').classList.add('ok');
  if (kind === 'err') el('status').classList.add('err');
}

async function save() {
  const signedUrl = el('signedUrl').value.trim();
  const resumeVariant = el('resumeVariant').value.trim();
  await chrome.storage.local.set({ signedUrl, resumeVariant });
  setStatus('Saved.', 'ok');
}

async function testFetch() {
  setStatus('Testing…');
  chrome.runtime.sendMessage({ type: 'syncProfile' }, async (res) => {
    if (res?.ok) {
      setStatus('Success ✓', 'ok');
      await showPreview(res.profile);
    } else {
      setStatus(`Failed: ${res?.error || 'Unknown error'}`, 'err');
    }
  });
}

async function clearAll() {
  await chrome.storage.local.remove(['signedUrl', 'resumeVariant', 'profile', 'profileUpdatedAt']);
  setStatus('Cleared.', 'ok');
  await refreshPreview();
}

async function showPreview(profile) {
  el('p_name').textContent = profile?.name || '—';
  el('p_email').textContent = profile?.email || '—';
  el('p_phone').textContent = profile?.phone || '—';
  el('p_location').textContent = profile?.location || '—';
  el('p_website').textContent = profile?.website || '—';
  const { profileUpdatedAt } = await chrome.storage.local.get(['profileUpdatedAt']);
  el('updatedAt').textContent = profileUpdatedAt ? new Date(profileUpdatedAt).toLocaleString() : '—';
}

async function refreshPreview() {
  const { profile } = await chrome.storage.local.get(['profile']);
  await showPreview(profile || {});
}

el('save').addEventListener('click', save);
el('test').addEventListener('click', testFetch);
el('clear').addEventListener('click', clearAll);
el('refresh').addEventListener('click', refreshPreview);

(async function init() {
  const { signedUrl, resumeVariant } = await chrome.storage.local.get(['signedUrl','resumeVariant']);
  if (signedUrl) el('signedUrl').value = signedUrl;
  if (resumeVariant) el('resumeVariant').value = resumeVariant;
  await refreshPreview();
})();
