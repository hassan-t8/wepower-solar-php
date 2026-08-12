<?php
/**
 * Careers apply modal — PHP port of CareersModal.jsx. Included once by
 * careers.php. Opened via [data-job-title] buttons (specific job) or
 * #generalApplyBtn (general application, no job pre-selected).
 */
?>
<div class="cm-overlay" id="cmOverlay" hidden>
  <div class="cm-modal">
    <button type="button" class="cm-close" id="cmCloseBtn" aria-label="Close"><i class="bi bi-x-lg"></i></button>

    <div class="cm-side">
      <div class="cm-side-glow"></div>
      <div class="cm-side-icon"><i class="bi bi-briefcase-fill"></i></div>
      <div id="cmSideJob" hidden>
        <h3>Apply for Position</h3>
        <p class="cm-side-role" id="cmSideRole"></p>
        <div class="cm-side-meta" id="cmSideMeta"></div>
      </div>
      <div id="cmSideGeneral">
        <h3>Join WePower</h3>
        <p>Submit your application and become part of Pakistan's solar revolution.</p>
      </div>
      <ul class="cm-side-list">
        <li><i class="bi bi-check-circle-fill"></i>Fast review process</li>
        <li><i class="bi bi-check-circle-fill"></i>Competitive packages</li>
        <li><i class="bi bi-check-circle-fill"></i>Growth opportunities</li>
      </ul>
    </div>

    <div class="cm-body">
      <div id="cmFormWrap">
        <h2 class="cm-title">Submit Application</h2>
        <p class="cm-sub">Fill in your details below — all fields marked * are required.</p>
        <form id="careersForm">
          <div class="form-row">
            <div class="field"><label>Full Name *</label><input type="text" name="full_name" required placeholder="Your full name"></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" required placeholder="03xx-xxxxxxx"></div>
          </div>
          <div class="form-row">
            <div class="field"><label>Email *</label><input type="email" name="email" required placeholder="you@email.com"></div>
            <div class="field">
              <label>Position *</label>
              <select name="position" id="cmPositionSelect" required>
                <option value="">Select a position</option>
                <?php foreach ($jobs as $j): ?>
                  <option value="<?= h($j['title']) ?>"><?= h($j['title']) ?></option>
                <?php endforeach; ?>
                <option value="General Application">General Application</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="field"><label>Years of Experience</label><input type="number" name="experience_years" min="0" max="40" placeholder="e.g. 3"></div>
            <div class="field"><label>Education / Qualification</label><input type="text" name="education" placeholder="e.g. BE Electrical, NUST"></div>
          </div>
          <div class="field">
            <label>Resume / CV <span class="field-hint">(PDF, DOC, DOCX — max 5MB)</span></label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx">
          </div>
          <div class="cm-error" id="careersError" hidden><i class="bi bi-exclamation-triangle"></i> <span></span></div>
          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <i class="bi bi-send"></i> Submit Application
          </button>
        </form>
      </div>

      <div class="cm-success" id="cmSuccess" hidden>
        <div class="cm-check"><i class="bi bi-check-lg"></i></div>
        <h3>Application Submitted!</h3>
        <p>Thank you! Our HR team will review your application and reach out within 5–7 business days.</p>
        <button type="button" class="btn btn-outline" id="cmCloseDoneBtn">Close</button>
      </div>
    </div>
  </div>
</div>
