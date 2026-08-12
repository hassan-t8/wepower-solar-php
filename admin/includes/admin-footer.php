    </div>
  </div>
</div>

<script src="/assets/js/toast.js"></script>
<script src="/assets/js/admin/admin-common.js"></script>
<?php foreach ($adminPageScripts ?? [] as $script): ?>
<script src="/assets/js/admin/<?= h($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
