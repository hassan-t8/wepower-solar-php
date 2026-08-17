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
      <h3><?= h(t('apply.sideTitle')) ?></h3>
      <p><?= h(t('apply.sideDesc')) ?></p>
      <ul class="apply-side-list">
        <?php foreach (t('apply.sideList') as $item): ?>
          <li><i class="bi bi-check-circle-fill"></i> <?= h($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="apply-body">
      <div id="applyFormWrap">
        <h3 class="apply-title"><?= h(t('apply.title')) ?></h3>
        <p class="apply-sub"><?= h(t('apply.sub')) ?></p>

        <form id="applyForm">
          <div class="form-row">
            <div class="field"><label><?= h(t('apply.fullName')) ?></label><input type="text" name="name" required placeholder="<?= h(t('apply.fullNamePh')) ?>"></div>
            <div class="field"><label><?= h(t('apply.phone')) ?></label><input type="text" name="phone" required placeholder="<?= h(t('apply.phonePh')) ?>"></div>
          </div>
          <div class="form-row">
            <div class="field"><label><?= h(t('apply.email')) ?></label><input type="email" name="email" required placeholder="<?= h(t('apply.emailPh')) ?>"></div>
            <div class="field"><label><?= h(t('apply.city')) ?></label><input type="text" name="city" placeholder="<?= h(t('apply.cityPh')) ?>"></div>
          </div>
          <div class="form-row">
            <div class="field">
              <label><?= h(t('apply.service')) ?></label>
              <select name="service_type" id="applyServiceSelect">
                <option value=""><?= h(t('apply.selectService')) ?></option>
                <?php foreach ($services as $s): ?>
                  <option value="<?= h($s['title']) ?>"><?= h($s['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label><?= h(t('apply.propertyType')) ?></label>
              <select name="property_type">
                <option value=""><?= h(t('apply.selectOption')) ?></option>
                <option><?= h(t('apply.propHome')) ?></option>
                <option><?= h(t('apply.propCommercial')) ?></option>
                <option><?= h(t('apply.propIndustrial')) ?></option>
                <option><?= h(t('apply.propAgri')) ?></option>
              </select>
            </div>
          </div>
          <div class="field">
            <label><?= h(t('apply.notes')) ?></label>
            <textarea name="notes" placeholder="<?= h(t('apply.notesPh')) ?>"></textarea>
          </div>

          <div class="apply-error" id="applyError" hidden><i class="bi bi-exclamation-triangle"></i> <span></span></div>

          <button type="submit" class="btn btn-primary btn-block btn-lg" id="applySubmitBtn">
            <i class="bi bi-send"></i> <?= h(t('apply.submitRequest')) ?>
          </button>
        </form>
      </div>

      <div class="apply-success" id="applySuccess" hidden>
        <div class="apply-check"><i class="bi bi-check-lg"></i></div>
        <h3><?= h(t('apply.successTitle')) ?></h3>
        <p id="applySuccessMsg"><?= h(t('apply.successBody')) ?></p>
        <button type="button" class="btn btn-primary" id="applyDoneBtn"><?= h(t('common.done')) ?></button>
      </div>
    </div>
  </div>
</div>
