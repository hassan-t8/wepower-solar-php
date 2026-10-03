    </div>
  </div>
</div>

<script src="<?= h(asset('/assets/js/toast.js')) ?>"></script>
<script src="<?= h(asset('/assets/js/admin/admin-common.js')) ?>"></script>
<?php foreach ($adminPageScripts ?? [] as $script): ?>
<script src="<?= h(asset('/assets/js/admin/' . $script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
