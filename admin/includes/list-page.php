<?php
/**
 * Shared list-table page shell. The calling admin/*.php page sets:
 *   $listTitle, $listSubtitle, $listHeaderCols (array of <th> labels,
 *   NOT including #, Status, Date, Actions which are added automatically
 *   unless $listNoStatus is true), $listSearchPlaceholder, then includes
 *   this file, then outputs a <script> defining and calling initAdminList().
 */
$listNoStatus = $listNoStatus ?? false;
?>
<h2 style="margin-bottom:6px"><?= h($listTitle) ?></h2>
<p style="color:var(--gray-400);font-size:.88rem;margin-bottom:24px"><?= h($listSubtitle) ?></p>

<div class="adm-table-card">
  <div class="adm-table-header">
    <div class="adm-table-title"><?= h($listTitle) ?> <span id="listCount" style="color:var(--gray-400);font-weight:400;font-size:.85rem"></span></div>
    <div class="adm-table-actions">
      <input class="adm-search" id="listSearch" placeholder="<?= h($listSearchPlaceholder ?? 'Search…') ?>">
      <select class="adm-filter" id="listFilter"></select>
      <button class="adm-export-btn" id="listExportBtn"><i class="bi bi-download"></i> CSV</button>
    </div>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>#</th>
          <?php foreach ($listHeaderCols as $c): ?><th><?= h($c) ?></th><?php endforeach; ?>
          <?php if (!$listNoStatus): ?><th>Status</th><?php endif; ?>
          <th>Date</th><th>Actions</th>
        </tr>
      </thead>
      <tbody id="listBody"></tbody>
    </table>
  </div>
  <div class="adm-pagination" id="listPagination"></div>
</div>

<div class="adm-modal-overlay" id="listModalOverlay" hidden>
  <div class="adm-modal" id="listModalBox">
    <button type="button" class="adm-modal-close" id="listModalCloseBtn"><i class="bi bi-x-lg"></i></button>
    <div id="listModalBody"></div>
  </div>
</div>
