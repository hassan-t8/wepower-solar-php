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
        <h3><?= h(t('careersModal.applyForPosition')) ?></h3>
        <p class="cm-side-role" id="cmSideRole"></p>
        <div class="cm-side-meta" id="cmSideMeta"></div>
      </div>
      <div id="cmSideGeneral">
        <h3><?= h(t('careersModal.joinTitle')) ?></h3>
        <p><?= h(t('careersModal.joinDesc')) ?></p>
      </div>
      <ul class="cm-side-list">
        <?php foreach (t('careersModal.sideList') as $item): ?>
          <li><i class="bi bi-check-circle-fill"></i><?= h($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="cm-body">
      <div id="cmFormWrap">
        <h2 class="cm-title"><?= h(t('careersModal.title')) ?></h2>
        <p class="cm-sub"><?= h(t('careersModal.sub')) ?></p>
        <form id="careersForm">
          <div class="form-row">
            <div class="field"><label><?= h(t('careersModal.fullName')) ?></label><input type="text" name="full_name" required placeholder="<?= h(t('careersModal.fullNamePh')) ?>"></div>
            <div class="field"><label><?= h(t('careersModal.phone')) ?></label><input type="text" name="phone" required placeholder="<?= h(t('careersModal.phonePh')) ?>"></div>
          </div>
          <div class="form-row">
            <div class="field"><label><?= h(t('careersModal.email')) ?></label><input type="email" name="email" required placeholder="<?= h(t('careersModal.emailPh')) ?>"></div>
            <div class="field">
              <label><?= h(t('careersModal.position')) ?></label>
              <select name="position" id="cmPositionSelect" required>
                <option value=""><?= h(t('careersModal.selectPosition')) ?></option>
                <?php foreach ($jobs as $j): ?>
                  <option value="<?= h($j['title']) ?>"><?= h($j['title']) ?></option>
                <?php endforeach; ?>
                <option value="General Application"><?= h(t('careersModal.generalApplication')) ?></option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="field"><label><?= h(t('careersModal.experience')) ?></label><input type="number" name="experience_years" min="0" max="40" placeholder="<?= h(t('careersModal.experiencePh')) ?>"></div>
            <div class="field"><label><?= h(t('careersModal.education')) ?></label><input type="text" name="education" placeholder="<?= h(t('careersModal.educationPh')) ?>"></div>
          </div>
          <div class="field">
            <label><?= h(t('careersModal.resume')) ?> <span class="field-hint"><?= h(t('careersModal.resumeHint')) ?></span></label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx">
          </div>
          <div class="cm-error" id="careersError" hidden><i class="bi bi-exclamation-triangle"></i> <span></span></div>
          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <i class="bi bi-send"></i> <?= h(t('careersModal.submitApplication')) ?>
          </button>
        </form>
      </div>

      <div class="cm-success" id="cmSuccess" hidden>
        <div class="cm-check"><i class="bi bi-check-lg"></i></div>
        <h3><?= h(t('careersModal.successTitle')) ?></h3>
        <p><?= h(t('careersModal.successBody')) ?></p>
        <button type="button" class="btn btn-outline" id="cmCloseDoneBtn"><?= h(t('common.close')) ?></button>
      </div>
    </div>
  </div>
</div>
