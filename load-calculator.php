<?php
$pageTitle = 'Solar Load Calculator — WePower Solar Solutions';
$pageDescription = 'Calculate your solar system size instantly. Add your appliances and get an accurate KVA recommendation, daily energy usage, and estimated monthly savings.';
$headerVariant = 'solid';
$pageScripts = ['load-calculator.js'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/"><?= h(t('common.home')) ?></a><i class="bi bi-chevron-right"></i><span><?= h(t('calc.breadcrumb')) ?></span>
    </div>
    <span class="tag"><?= h(t('calc.heroTag')) ?></span>
    <h1 class="display"><?= h(t('calc.heroTitle')) ?></h1>
    <p><?= h(t('calc.heroLead')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="calc-wrap">

      <div>
        <div class="calc-panel" style="margin-bottom:28px">
          <h3 style="margin-bottom:18px"><i class="bi bi-plus-circle-fill" style="color:var(--green-600);margin-right:8px"></i><?= h(t('calc.addTitle')) ?></h3>
          <form id="addApplianceForm">
            <div class="form-row">
              <div class="field">
                <label><?= h(t('calc.appliance')) ?></label>
                <select id="presetSelect">
                  <option value=""><?= h(t('calc.selectPreset')) ?></option>
                  <?php foreach ($appliancePresets as $p): ?>
                    <option value="<?= h($p['name']) ?>" data-power="<?= (int)$p['power'] ?>"><?= h($p['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label><?= h(t('calc.customName')) ?></label>
                <input type="text" id="nameInput" placeholder="<?= h(t('calc.customNamePh')) ?>">
              </div>
            </div>
            <div class="calc-add-row">
              <div class="field">
                <label><?= h(t('calc.watts')) ?></label>
                <input type="number" id="powerInput" min="1" required placeholder="<?= h(t('calc.wattsPh')) ?>">
              </div>
              <div class="field">
                <label><?= h(t('calc.qty')) ?></label>
                <input type="number" id="qtyInput" min="1" max="100" value="1">
              </div>
              <div class="field">
                <label><?= h(t('calc.hoursDay')) ?></label>
                <input type="number" id="hoursInput" min="0.5" max="24" step="0.5" value="4">
              </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
              <i class="bi bi-plus-lg"></i> <?= h(t('calc.addToList')) ?>
            </button>
          </form>
        </div>

        <div class="calc-panel" id="applianceListPanel" hidden>
          <h3 style="margin-bottom:18px"><?= h(t('calc.yourAppliances')) ?></h3>
          <div class="appliance-table-head">
            <span><?= h(t('calc.thAppliance')) ?></span><span><?= h(t('calc.thWatts')) ?></span><span><?= h(t('calc.thQty')) ?></span><span><?= h(t('calc.thHrs')) ?></span><span></span>
          </div>
          <div id="applianceRows"></div>
          <div style="border-top:1.5px solid var(--gray-200);margin-top:14px;padding-top:14px;display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:600;color:var(--gray-700)"><?= h(t('calc.totalLoad')) ?></span>
            <span style="font-weight:700;color:var(--green-700);font-size:1.1rem" id="totalLoadLabel">0 W</span>
          </div>
        </div>

        <div id="emptyState" style="text-align:center;padding:48px 20px;background:var(--gray-50);border-radius:var(--radius-lg);border:1.5px dashed var(--gray-300);color:var(--gray-400)">
          <i class="bi bi-lightning-charge" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>
          <p><?= h(t('calc.emptyState')) ?></p>
        </div>

        <div class="calc-panel" id="saveResultsPanel" style="margin-top:28px" hidden>
          <h3 style="margin-bottom:6px"><?= h(t('calc.saveTitle')) ?></h3>
          <p class="muted" style="font-size:.9rem;margin-bottom:20px"><?= h(t('calc.saveSub')) ?></p>
          <form id="saveResultsForm" novalidate data-validate>
            <div class="form-row">
              <div class="field"><label><?= h(t('calc.name')) ?></label><input type="text" name="name" placeholder="<?= h(t('calc.namePh')) ?>"></div>
              <div class="field"><label><?= h(t('contact.phoneLabel')) ?></label><input type="tel" name="phone" data-phone inputmode="numeric" autocomplete="tel-national" placeholder="<?= h(t('calc.phonePh')) ?>"></div>
            </div>
            <div class="form-row">
              <div class="field"><label><?= h(t('contact.email')) ?></label><input type="email" name="email" maxlength="254" autocomplete="email" data-email placeholder="<?= h(t('calc.emailPh')) ?>"></div>
              <div class="field"><label><?= h(t('calc.city')) ?></label><input type="text" name="city" placeholder="<?= h(t('calc.cityPh')) ?>"></div>
            </div>
            <div class="form-error" id="calcError" hidden><span></span></div>
            <div id="calcSuccess" style="background:var(--green-50);color:var(--green-700);border:1px solid var(--green-200);border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:.9rem" hidden>
              <i class="bi bi-check-circle" style="margin-right:8px"></i><?= h(t('calc.savedMsg')) ?>
            </div>
            <button type="submit" class="btn btn-primary btn-block" id="saveResultsBtn">
              <i class="bi bi-send"></i> <?= h(t('calc.saveResults')) ?>
            </button>
          </form>
        </div>
      </div>

      <div class="calc-summary">
        <h3><i class="bi bi-calculator" style="margin-right:10px"></i><?= h(t('calc.summaryTitle')) ?></h3>
        <div id="summaryEmpty">
          <p style="opacity:.75;font-size:.9rem;margin-top:8px"><?= h(t('calc.summaryEmpty')) ?></p>
        </div>
        <div id="summaryFilled" hidden>
          <div class="calc-metric">
            <div class="calc-metric-label"><?= h(t('calc.totalLoadLabel')) ?></div>
            <div class="calc-metric-val" id="sumTotalW">0<span style="font-size:1rem;font-weight:400"> W</span></div>
            <div class="calc-metric-unit" id="sumTotalKw">0.00 kW</div>
          </div>
          <div class="calc-metric">
            <div class="calc-metric-label"><?= h(t('calc.recSystem')) ?></div>
            <div class="calc-metric-val" id="sumKva">0<span style="font-size:1rem;font-weight:400"> KVA</span></div>
            <div class="calc-metric-unit"><?= h(t('calc.safetyMargin')) ?></div>
          </div>
          <div class="calc-metric">
            <div class="calc-metric-label"><?= h(t('calc.dailyUsage')) ?></div>
            <div class="calc-metric-val" id="sumDaily">0<span style="font-size:1rem;font-weight:400"> kWh/day</span></div>
          </div>
          <div class="calc-metric">
            <div class="calc-metric-label"><?= h(t('calc.monthlyEnergy')) ?></div>
            <div class="calc-metric-val" id="sumMonthly">0<span style="font-size:1rem;font-weight:400"> kWh/mo</span></div>
          </div>
          <div style="margin-top:8px;background:rgba(255,255,255,.08);border-radius:var(--radius);padding:14px 16px;font-size:.82rem;opacity:.85">
            <i class="bi bi-info-circle" style="margin-right:8px"></i>
            <?= h(t('calc.estimateNote')) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
