<?php
$adminPageTitle = 'Job Postings';
$adminActive = 'jobs';
$adminPageScripts = ['jobs.js'];
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
  <div>
    <h2 style="margin-bottom:4px">Job Postings</h2>
    <p style="color:var(--gray-400);font-size:.88rem">Manage openings shown on the Careers page.</p>
  </div>
  <button class="btn btn-primary btn-sm" id="addJobBtn"><i class="bi bi-plus-lg"></i> Add New Job</button>
</div>

<div id="jobAlert" hidden></div>

<div class="adm-table-card" id="jobFormCard" style="margin-bottom:28px" hidden>
  <div class="adm-table-header">
    <div class="adm-table-title" id="jobFormTitle">New Job Posting</div>
    <button class="btn btn-outline btn-sm" id="jobFormCancelBtn"><i class="bi bi-x-lg"></i> Cancel</button>
  </div>
  <div style="padding:20px 24px">
    <form id="jobForm">
      <input type="hidden" name="id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div class="field" style="grid-column:span 2">
          <label>Job Title *</label>
          <input type="text" name="title" required placeholder="e.g. Solar Design Engineer">
        </div>
        <div class="field">
          <label>Employment Type</label>
          <select name="type">
            <option value="full">Full-Time</option>
            <option value="part">Part-Time</option>
            <option value="contract">Contract</option>
            <option value="intern">Internship</option>
          </select>
        </div>
        <div class="field"><label>Department</label><input type="text" name="dept" placeholder="e.g. Engineering"></div>
        <div class="field"><label>Location</label><input type="text" name="location" placeholder="e.g. Rawalpindi / Field"></div>
        <div class="field"><label>Experience Required</label><input type="text" name="experience" placeholder="e.g. 2–4 years"></div>
        <div class="field" style="grid-column:span 2">
          <label>Job Description</label>
          <textarea name="description" placeholder="Describe the role, responsibilities, and requirements…" style="min-height:100px;resize:vertical"></textarea>
        </div>
        <div class="field" style="display:flex;align-items:center;gap:12px">
          <input type="checkbox" name="is_active" id="jobActiveCheck" checked style="width:16px;height:16px">
          <label for="jobActiveCheck" style="margin-bottom:0;cursor:pointer">Active (visible on website)</label>
        </div>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary" id="jobSaveBtn"><i class="bi bi-check-lg"></i> <span id="jobSaveBtnText">Create Job</span></button>
        <button type="button" class="btn btn-outline" id="jobFormCancelBtn2">Cancel</button>
      </div>
    </form>
  </div>
</div>

<div class="adm-table-card">
  <div class="adm-table-header">
    <div class="adm-table-title">All Positions <span id="jobCount"></span></div>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead><tr><th>Title</th><th>Type</th><th>Dept</th><th>Location</th><th>Experience</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="jobsBody"><tr><td colspan="7"><div class="adm-empty">Loading…</div></td></tr></tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
