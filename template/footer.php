    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width:420px">
            <div class="modal-header">
                <div class="modal-title">Konfirmasi Hapus</div>
                <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
            </div>
            <div class="modal-body">
                <div style="text-align:center;padding:20px 0">
                    <div style="font-size:50px;margin-bottom:16px">🗑️</div>
                    <p style="color:var(--text-secondary);font-size:14px;line-height:1.6">
                        Yakin ingin menghapus <strong id="deleteItemName" style="color:var(--text-primary)"></strong>?<br>
                        <span style="color:var(--text-muted);font-size:12px">Tindakan ini tidak dapat dibatalkan.</span>
                    </p>
                </div>
                <div style="display:flex;gap:12px;margin-top:8px">
                    <button class="btn btn-outline" style="flex:1" onclick="closeModal('deleteModal')">Batal</button>
                    <button class="btn btn-danger" style="flex:1" id="deleteConfirmBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer"></div>

</div><!-- end .main-content -->

<script src="<?= APP_URL ?>/public/assets/js/main.js"></script>
<?php if (isset($extraJS)) foreach ($extraJS as $js) echo "<script src='$js'></script>"; ?>
</body>
</html>
