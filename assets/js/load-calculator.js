/**
 * Load Calculator interactivity — vanilla JS port of LoadCalculator.jsx.
 * Formulas ported verbatim:
 *   recommendedKva = ceil(totalW * 1.25 / 1000 * 10) / 10
 *   dailyKwh       = Σ(power * qty * hours) / 1000
 *   monthlyKwh     = dailyKwh * 30
 *   estimatedBill  = round(monthlyKwh * 45)
 */
(function () {
  const addForm = document.getElementById('addApplianceForm');
  if (!addForm) return;

  const presetSelect = document.getElementById('presetSelect');
  const nameInput = document.getElementById('nameInput');
  const powerInput = document.getElementById('powerInput');
  const qtyInput = document.getElementById('qtyInput');
  const hoursInput = document.getElementById('hoursInput');

  const applianceListPanel = document.getElementById('applianceListPanel');
  const applianceRows = document.getElementById('applianceRows');
  const totalLoadLabel = document.getElementById('totalLoadLabel');
  const emptyState = document.getElementById('emptyState');
  const saveResultsPanel = document.getElementById('saveResultsPanel');
  const summaryEmpty = document.getElementById('summaryEmpty');
  const summaryFilled = document.getElementById('summaryFilled');

  let appliances = []; // { id, name, power, qty, hours }
  let nextId = 1;

  presetSelect.addEventListener('change', () => {
    const opt = presetSelect.selectedOptions[0];
    if (!opt || !opt.value) return;
    nameInput.value = opt.value;
    const power = opt.dataset.power;
    if (power && power !== '0') powerInput.value = power;
  });

  addForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = nameInput.value.trim() || presetSelect.value;
    const power = Number(powerInput.value);
    const qty = Number(qtyInput.value) || 1;
    const hours = Number(hoursInput.value) || 4;
    if (!name || !power || power <= 0) return;

    appliances.push({ id: nextId++, name, power, qty, hours });
    addForm.reset();
    qtyInput.value = 1;
    hoursInput.value = 4;
    render();
  });

  function removeAppliance(id) {
    appliances = appliances.filter((a) => a.id !== id);
    render();
  }

  function calc() {
    const totalW = appliances.reduce((s, a) => s + a.power * a.qty, 0);
    const recommendedKva = Math.ceil((totalW * 1.25) / 1000 * 10) / 10;
    const dailyKwh = appliances.reduce((s, a) => s + a.power * a.qty * a.hours, 0) / 1000;
    const monthlyKwh = dailyKwh * 30;
    const estimatedBill = Math.round(monthlyKwh * 45);
    return { totalW, recommendedKva, dailyKwh, monthlyKwh, estimatedBill };
  }

  function render() {
    const hasAppliances = appliances.length > 0;
    applianceListPanel.hidden = !hasAppliances;
    emptyState.hidden = hasAppliances;
    saveResultsPanel.hidden = !hasAppliances;
    summaryEmpty.hidden = hasAppliances;
    summaryFilled.hidden = !hasAppliances;

    if (!hasAppliances) return;

    applianceRows.innerHTML = appliances.map((a) => `
      <div class="appliance-row">
        <span style="font-weight:500;font-size:.92rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(a.name)}</span>
        <span style="color:var(--gray-600);font-size:.9rem">${a.power}W</span>
        <span style="color:var(--gray-600);font-size:.9rem">×${a.qty}</span>
        <span style="color:var(--gray-600);font-size:.9rem">${a.hours}h</span>
        <button class="del-btn" data-id="${a.id}" title="Remove"><i class="bi bi-trash"></i></button>
      </div>
    `).join('');

    applianceRows.querySelectorAll('.del-btn').forEach((btn) => {
      btn.addEventListener('click', () => removeAppliance(Number(btn.dataset.id)));
    });

    const c = calc();
    totalLoadLabel.textContent = c.totalW.toLocaleString() + ' W';
    document.getElementById('sumTotalW').innerHTML = c.totalW.toLocaleString() + '<span style="font-size:1rem;font-weight:400"> W</span>';
    document.getElementById('sumTotalKw').textContent = (c.totalW / 1000).toFixed(2) + ' kW';
    document.getElementById('sumKva').innerHTML = c.recommendedKva + '<span style="font-size:1rem;font-weight:400"> KVA</span>';
    document.getElementById('sumDaily').innerHTML = c.dailyKwh.toFixed(1) + '<span style="font-size:1rem;font-weight:400"> kWh/day</span>';
    document.getElementById('sumMonthly').innerHTML = c.monthlyKwh.toFixed(0) + '<span style="font-size:1rem;font-weight:400"> kWh/mo</span>';
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  // ---- Save & Get Quote form ----
  const saveForm = document.getElementById('saveResultsForm');
  const calcError = document.getElementById('calcError');
  const calcSuccess = document.getElementById('calcSuccess');
  const saveBtn = document.getElementById('saveResultsBtn');

  saveForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (appliances.length === 0) return;
    calcError.hidden = true;
    calcSuccess.hidden = true;
    saveBtn.disabled = true;
    const origHtml = saveBtn.innerHTML;
    const i18n = window.WP_I18N || {};
    saveBtn.innerHTML = '<span class="btn-spinner"></span> ' + (i18n.saving || 'Saving…');

    const c = calc();
    const payload = {
      name: saveForm.name.value,
      email: saveForm.email.value,
      phone: saveForm.phone.value,
      city: saveForm.city.value,
      property_type: '',
      num_people: 4,
      appliances: appliances.map((a) => ({
        name: a.name, quantity: a.qty, power: a.power, hours: a.hours, total: a.power * a.qty,
      })),
      total_load_w: c.totalW,
      recommended_kva: c.recommendedKva,
      estimated_bill: c.estimatedBill,
    };

    try {
      const res = await fetch('/api/calculator.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Error saving.');
      calcSuccess.hidden = false;
      showToast(i18n.savedMsg || 'Results saved! Our team will reach out shortly.', 'success');
    } catch (err) {
      calcError.hidden = false;
      calcError.querySelector('span').textContent = err.message;
    } finally {
      saveBtn.disabled = false;
      saveBtn.innerHTML = origHtml;
    }
  });

  render();
})();
