// Background service worker for Career Buddy Autofill

async function fetchProfileFromSignedUrl(signedUrl) {
  if (!signedUrl) throw new Error('Signed URL is not configured');

  const res = await fetch(signedUrl, {
    method: 'GET',
    // No CORS needed when fetching from service worker with host_permissions
    headers: { 'Accept': 'application/json' }
  });
  if (!res.ok) {
    const text = await res.text().catch(() => '');
    throw new Error(`Fetch failed: ${res.status} ${text}`);
  }
  const json = await res.json();
  return json.profile;
}

async function syncProfile() {
  const { signedUrl } = await chrome.storage.local.get(['signedUrl']);
  if (!signedUrl) throw new Error('Signed URL not set');
  const profile = await fetchProfileFromSignedUrl(signedUrl);
  await chrome.storage.local.set({ profile, profileUpdatedAt: Date.now() });
  return profile;
}

chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
  (async () => {
    try {
      if (msg?.type === 'syncProfile') {
        const profile = await syncProfile();
        sendResponse({ ok: true, profile });
        return;
      }
      if (msg?.type === 'getProfile') {
        const { profile } = await chrome.storage.local.get(['profile']);
        sendResponse({ ok: true, profile: profile || null });
        return;
      }
      if (msg?.type === 'logAutofill') {
        const { signedUrl, resumeVariant } = await chrome.storage.local.get(['signedUrl','resumeVariant']);
        if (!signedUrl) throw new Error('Signed URL not set');
        const u = new URL(signedUrl);
        const q = new URLSearchParams(u.search);
        const payload = {
          user: Number(q.get('user')),
          expires: Number(q.get('expires')),
          signature: String(q.get('signature') || ''),
          resume_variant: resumeVariant || null,
          job_title: msg.jobTitle || null,
          company: msg.company || null,
          source_host: msg.host || null,
          page_url: msg.pageUrl || null,
          fields: Array.isArray(msg.fields) ? msg.fields : [],
          field_details: Array.isArray(msg.fieldDetails) ? msg.fieldDetails : [],
          filled_count: Number(msg.filledCount || 0),
        };
        const endpoint = u.origin + '/api/autofill-events/signed';
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload),
        });
        if (!res.ok) {
          const t = await res.text().catch(() => '');
          throw new Error(`Log failed: ${res.status} ${t}`);
        }
        sendResponse({ ok: true });
        return;
      }
    } catch (e) {
      sendResponse({ ok: false, error: String(e) });
    }
  })();
  return true; // keep channel open for async
});
