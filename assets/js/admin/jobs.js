/**
 * Job Postings CRUD page — port of AdminJobs.jsx.
 */
(function () {
  let jobs = [];

  const formCard = document.getElementById('jobFormCard');
  const form = document.getElementById('jobForm');
  const formTitle = document.getElementById('jobFormTitle');
  const saveBtnText = document.getElementById('jobSaveBtnText');
  const jobsBody = document.getElementById('jobsBody');
  const jobCount = document.getElementById('jobCount');
  const alertBox = document.getElementById('jobAlert');

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function showAlert(type, msg) {
    alertBox.hidden = false;
    alertBox.className = 'adm-alert ' + type;
    alertBox.style.marginBottom = '20px';
    alertBox.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle') + '"></i>' + esc(msg);
    setTimeout(() => { alertBox.hidden = true; }, 4000);
  }

  function openForm(job) {
    form.reset();
    if (job) {
      formTitle.textContent = 'Edit Job Posting';
      saveBtnText.textContent = 'Update Job';
      form.id.value = job.id;
      form.title.value = job.title;
      form.type.value = job.type;
      form.dept.value = job.dept || '';
      form.location.value = job.location || '';
      form.experience.value = job.experience || '';
      form.description.value = job.description || '';
      form.is_active.checked = !!Number(job.is_active);
    } else {
      formTitle.textContent = 'New Job Posting';
      saveBtnText.textContent = 'Create Job';
      form.id.value = '';
      form.is_active.checked = true;
    }
    formCard.hidden = false;
    formCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  function closeForm() { formCard.hidden = true; }

  document.getElementById('addJobBtn').addEventListener('click', () => openForm(null));
  document.getElementById('jobFormCancelBtn').addEventListener('click', closeForm);
  document.getElementById('jobFormCancelBtn2').addEventListener('click', closeForm);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const isNew = !form.id.value;
    const payload = {
      _action: isNew ? 'create' : 'update',
      id: form.id.value || undefined,
      title: form.title.value,
      type: form.type.value,
      dept: form.dept.value,
      location: form.location.value,
      experience: form.experience.value,
      description: form.description.value,
      is_active: form.is_active.checked,
    };
    try {
      const res = await fetch('/admin/api/jobs-crud.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error((await res.json()).error || 'Save failed');
      showAlert('success', isNew ? 'Job posting created!' : 'Job posting updated!');
      closeForm();
      load();
    } catch (err) {
      showAlert('error', err.message);
    }
  });

  async function remove(id) {
    if (!window.confirm('Delete this job posting?')) return;
    await fetch('/admin/api/jobs-crud.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _action: 'delete', id }),
    });
    load();
  }

  async function toggleActive(job) {
    await fetch('/admin/api/jobs-crud.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _action: 'update', id: job.id, title: job.title, type: job.type, dept: job.dept, location: job.location, experience: job.experience, description: job.description, is_active: !Number(job.is_active) }),
    });
    load();
  }

  const TYPE_LABEL = { full: 'Full-Time', part: 'Part-Time', contract: 'Contract', intern: 'Internship' };

  function render() {
    jobCount.textContent = `(${jobs.length})`;
    if (jobs.length === 0) {
      jobsBody.innerHTML = '<tr><td colspan="7"><div class="adm-empty" style="padding:48px 0">No job postings yet. Click "Add New Job" to create one.</div></td></tr>';
      return;
    }
    jobsBody.innerHTML = jobs.map((job) => `
      <tr>
        <td style="font-weight:600">${esc(job.title)}</td>
        <td><span class="status-badge status-${job.type === 'full' ? 'new' : 'read'}">${TYPE_LABEL[job.type] || esc(job.type)}</span></td>
        <td>${esc(job.dept || '—')}</td>
        <td>${esc(job.location || '—')}</td>
        <td>${esc(job.experience || '—')}</td>
        <td>
          <button class="job-toggle-btn" data-id="${job.id}" style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:var(--radius-pill);border:none;cursor:pointer;font-size:.8rem;font-weight:600;background:${Number(job.is_active) ? 'var(--green-50)' : 'var(--gray-100)'};color:${Number(job.is_active) ? 'var(--green-700)' : 'var(--gray-500)'}">
            <i class="bi bi-circle-fill" style="font-size:.5rem"></i>${Number(job.is_active) ? 'Active' : 'Inactive'}
          </button>
        </td>
        <td><div style="display:flex;gap:6px">
          <button class="adm-action-btn job-edit-btn" data-id="${job.id}" title="Edit"><i class="bi bi-pencil"></i></button>
          <button class="adm-action-btn danger job-del-btn" data-id="${job.id}" title="Delete"><i class="bi bi-trash"></i></button>
        </div></td>
      </tr>
    `).join('');

    jobsBody.querySelectorAll('.job-toggle-btn').forEach((b) => b.addEventListener('click', () => toggleActive(jobs.find((j) => j.id == b.dataset.id))));
    jobsBody.querySelectorAll('.job-edit-btn').forEach((b) => b.addEventListener('click', () => openForm(jobs.find((j) => j.id == b.dataset.id))));
    jobsBody.querySelectorAll('.job-del-btn').forEach((b) => b.addEventListener('click', () => remove(b.dataset.id)));
  }

  function load() {
    fetch('/admin/api/jobs-crud.php', { credentials: 'include' })
      .then((r) => r.json())
      .then((data) => { jobs = Array.isArray(data) ? data : []; render(); })
      .catch(() => { jobs = []; render(); });
  }

  load();
})();
