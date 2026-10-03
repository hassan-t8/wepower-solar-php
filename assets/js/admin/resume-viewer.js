/**
 * In-panel resume viewer for career applications — lets the admin read a
 * resume without downloading it. PDF is shown by the browser's built-in
 * viewer; DOCX is rendered client-side with docx-preview (loaded on first
 * use); legacy .doc can't be rendered by browsers, so it offers download.
 *
 *   openResumeViewer({ id, name, filename })
 */
(function () {
  const base = '/admin/api/resume-download.php?id=';
  const LIBS = [
    'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js',
    'https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js',
  ];
  let libsPromise = null;

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = src; s.onload = resolve; s.onerror = () => reject(new Error('Could not load ' + src));
      document.head.appendChild(s);
    });
  }
  function loadDocxLibs() {
    if (!libsPromise) libsPromise = LIBS.reduce((p, src) => p.then(() => loadScript(src)), Promise.resolve());
    return libsPromise;
  }

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  const overlay = document.createElement('div');
  overlay.className = 'rv-overlay';
  overlay.hidden = true;
  overlay.innerHTML = `
    <div class="rv-modal" role="dialog" aria-modal="true" aria-label="Resume preview">
      <div class="rv-header">
        <div class="rv-title"><i class="bi bi-file-earmark-person"></i><span></span></div>
        <div class="rv-actions">
          <a class="btn btn-sm btn-outline rv-newtab" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span>Open in new tab</span></a>
          <a class="btn btn-sm btn-primary rv-download"><i class="bi bi-download"></i><span>Download</span></a>
          <button type="button" class="rv-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
      <div class="rv-body"></div>
    </div>`;
  document.body.appendChild(overlay);

  const titleEl = overlay.querySelector('.rv-title span');
  const bodyEl = overlay.querySelector('.rv-body');
  const newTabBtn = overlay.querySelector('.rv-newtab');
  const downloadBtn = overlay.querySelector('.rv-download');

  function close() {
    overlay.hidden = true;
    bodyEl.innerHTML = '';
    document.body.style.overflow = '';
  }
  overlay.querySelector('.rv-close').addEventListener('click', close);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !overlay.hidden) close(); });

  function message(icon, text, withDownload) {
    bodyEl.innerHTML = `<div class="rv-msg"><i class="bi ${icon}"></i><p>${text}</p>${
      withDownload ? `<a class="btn btn-primary" href="${downloadBtn.href}"><i class="bi bi-download"></i> Download resume</a>` : ''}</div>`;
  }

  window.openResumeViewer = async function ({ id, name, filename }) {
    const ext = String(filename || '').split('.').pop().toLowerCase();
    const viewUrl = base + encodeURIComponent(id) + '&mode=view';
    const dlUrl = base + encodeURIComponent(id);

    titleEl.textContent = (name ? name + ' — ' : '') + 'Resume (' + ext.toUpperCase() + ')';
    downloadBtn.href = dlUrl;
    newTabBtn.href = viewUrl;
    newTabBtn.hidden = ext !== 'pdf';
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';

    if (ext === 'pdf') {
      bodyEl.innerHTML = `<iframe class="rv-frame" src="${viewUrl}#view=FitH" title="${esc(name)} resume"></iframe>
        <p class="rv-hint">PDF not showing on your phone? Use <strong>Open in new tab</strong>.</p>`;
      return;
    }
    if (ext === 'docx') {
      message('bi-hourglass-split', 'Loading preview…', false);
      try {
        const [blob] = await Promise.all([
          fetch(dlUrl, { credentials: 'include' }).then((r) => { if (!r.ok) throw new Error('File not found'); return r.blob(); }),
          loadDocxLibs(),
        ]);
        if (overlay.hidden) return;
        bodyEl.innerHTML = '<div class="rv-docx"></div>';
        await window.docx.renderAsync(blob, bodyEl.firstChild, null, { inWrapper: true, ignoreLastRenderedPageBreak: true });
      } catch (err) {
        message('bi-exclamation-triangle', 'Could not preview this file (' + esc(err.message) + ').', true);
      }
      return;
    }
    message('bi-file-earmark-word', 'Old Word <strong>.doc</strong> files can’t be previewed in the browser. Download it to view.', true);
  };
})();
