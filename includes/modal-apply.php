<?php
/**
 * Global "Get a Quote" modal — PHP port of ApplyModal.jsx.
 * Included once by footer.php on every public page. Opened via any
 * [data-open-apply-modal] button (see assets/js/apply-modal.js).
 * Optional data-service="<title>" attribute pre-fills the service field.
 */
?>
<div class="apply-overlay" id="applyOverlay" hidden>
  <div class="apply-modal" id="applyModal">
    <button type="button" class="apply-close" id="applyCloseBtn" aria-label="Close"><i class="bi bi-x-lg"></i></button>

    <div class="apply-side">
      <div class="apply-side-bg"><div class="apply-side-glow"></div></div>
      <i class="bi bi-sun-fill apply-side-icon"></i>
      <h3>Go Solar with WePower</h3>
      <p>Free site survey &amp; a tailored proposal — no obligation. Our experts reply within 24 hours.</p>
      <ul class="apply-side-list">
        <li><i class="bi bi-check-circle-fill"></i> Free load assessment</li>
        <li><i class="bi bi-check-circle-fill"></i> Transparent pricing</li>
        <li><i class="bi bi-check-circle-fill"></i> 2 years free O&amp;M</li>
      </ul>
    </div>

    <div class="apply-body">
      <div id="applyFormWrap">
        <h3 class="apply-title">Apply Now</h3>
        <p class="apply-sub">Tell us about your project and we'll get you a quote.</p>

        <form id="applyForm">
          <div class="form-row">
            <div class="field"><label>Full Name *</label><input type="text" name="name" required placeholder="Your name"></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" required placeholder="03xx-xxxxxxx"></div>
          </div>
          <div class="form-row">
            <div class="field"><label>Email *</label><input type="email" name="email" required placeholder="you@email.com"></div>
            <div class="field"><label>City</label><input type="text" name="city" placeholder="e.g. Rawalpindi"></div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Service</label>
              <select name="service_type" id="applyServiceSelect">
                <option value="">Select a service</option>
                <?php foreach ($services as $s): ?>
                  <option value="<?= h($s['title']) ?>"><?= h($s['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Property Type</label>
              <select name="property_type">
                <option value="">Select</option>
                <option>Home</option>
                <option>Commercial</option>
                <option>Industrial</option>
                <option>Agricultural</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label>Notes</label>
            <textarea name="notes" placeholder="Roof size, monthly bill, anything helpful…"></textarea>
          </div>

          <div class="apply-error" id="applyError" hidden><i class="bi bi-exclamation-triangle"></i> <span></span></div>

          <button type="submit" class="btn btn-primary btn-block btn-lg" id="applySubmitBtn">
            <i class="bi bi-send"></i> Submit Request
          </button>
        </form>
      </div>

      <div class="apply-success" id="applySuccess" hidden>
        <div class="apply-check"><i class="bi bi-check-lg"></i></div>
        <h3>Request Received!</h3>
        <p id="applySuccessMsg">Thank you. Our solar team will reach out shortly to confirm your free consultation.</p>
        <button type="button" class="btn btn-primary" id="applyDoneBtn">Done</button>
      </div>
    </div>
  </div>
</div>
