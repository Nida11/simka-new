<div class="panel panel-default">
  <div class="panel-body">

    <h4 style="margin-top:0">
      <a>History</a> &bullet; Scan Data<br>
      <small>Riwayat hasil scan kendaraan</small>
    </h4>

    <form method="get"
          action=""
          class="well well-sm"
          style="overflow:hidden; margin-bottom:15px">

      <div class="row">
        <div class="col-md-3">
          <input type="text" class="form-control input-sm" name="no_polisi"
                 placeholder="No Polisi"
                 value="<?= html_escape($this->input->get('no_polisi')) ?>">
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control input-sm" name="no_rangka"
                 placeholder="No Rangka"
                 value="<?= html_escape($this->input->get('no_rangka')) ?>">
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control input-sm" name="no_mesin"
                 placeholder="No Mesin"
                 value="<?= html_escape($this->input->get('no_mesin')) ?>">
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control input-sm" name="nama_pemilik"
                 placeholder="Nama Pemilik"
                 value="<?= html_escape($this->input->get('nama_pemilik')) ?>">
        </div>
      </div>

      <div class="text-right" style="margin-top:8px">
        <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
        <a href="<?= site_url($this->uri->uri_string()) ?>" class="btn btn-sm btn-default">Reset</a>
      </div>
    </form>

    <div class="history-container">
      <div class="list-group">

        <?php if (!$isSearch): ?>
          <div class="alert alert-info text-center">🔍 <strong>Gunakan filter di atas untuk mencari data</strong></div>
        <?php elseif (empty($data)): ?>
          <div class="alert alert-warning text-center">❌ <strong>Data tidak ditemukan</strong></div>
        <?php else: ?>

          <?php foreach ($data as $i => $row): ?>
            <div class="list-group-item">
              <div class="clearfix">
                <strong>
                  <?= html_escape($row->no_polisi ?? '-') ?> -
                  <?= html_escape($row->nama_pemilik ?? '-') ?>
                </strong>
                <span class="label label-success pull-right">
                  Tanggal Bayar:
                  <?= !empty($row->tgl_proses_bayar) ? date('d M Y', strtotime($row->tgl_proses_bayar)) : '-' ?>
                </span>
              </div>

              <div class="small text-muted" style="margin-top:4px">
                No Rangka: <?= html_escape($row->no_rangka ?? '-') ?> •
                No Mesin: <?= html_escape($row->no_mesin ?? '-') ?> •
                Tanggal Scan:
                <?= !empty($row->tgl_scan) ? date('d M Y', strtotime($row->tgl_scan)) : '-' ?>
              </div>

              <div style="margin-top:6px">
                <?php if (!empty($row->images)): ?>
                  <button class="btn btn-xs btn-info open-photo"
                          data-target="#photoBox<?= $i ?>">
                    Lihat Foto (<?= count($row->images) ?>)
                  </button>
                <?php else: ?>
                  <span class="text-muted small">Tidak ada foto</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>

        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<?php if (!empty($pagination)): ?>
  <div class="text-center" style="margin-top:10px">
    <?= $pagination ?>
  </div>
<?php endif; ?>


<!-- =====================
     CUSTOM MODAL (NO BOOTSTRAP)
     ===================== -->
<?php if (!empty($data)): ?>
  <?php foreach ($data as $i => $row): ?>
    <?php if (!empty($row->images)): ?>
      <div class="photo-overlay" id="photoBox<?= $i ?>">
        <div class="photo-modal">
          <button class="photo-close">&times;</button>
          <h4 style="margin-top:0">
            Foto Scan - <?= html_escape($row->no_polisi ?? '-') ?>
          </h4>
          <div class="photo-body">
            <?php foreach ($row->images as $img): ?>
              <?php
                if (preg_match('#^https?://#', $img) || strpos($img, '/') !== false) {
                  $src = base_url($img);
                } else {
                  $src = base_url('uploads/scan/' . $img);
                }
              ?>
              <img src="<?= $src ?>" loading="lazy">
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>


<style>
.history-container {
  min-height: 200px;
}

.history-container .list-group {
  margin-bottom: 0;
}

.history-container .list-group-item {
  border-radius: 4px;
}

.photo-overlay {
  display: none;
  position: fixed;
  z-index: 99999;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,.7);
}

.photo-modal {
  background: #fff;
  width: 90%;
  max-width: 1000px;
  margin: 40px auto;
  padding: 15px;
  border-radius: 6px;
  position: relative;
  max-height: 90vh;
  overflow: auto;
}

.photo-close {
  position: absolute;
  top: 6px;
  right: 10px;
  border: none;
  background: transparent;
  font-size: 26px;
  cursor: pointer;
}

.photo-body img {
  max-width: 100%;
  margin: 6px;
  border-radius: 4px;
}
</style>

<script>
$(document).on('click', '.open-photo', function () {
  var target = $(this).data('target');
  $(target).fadeIn(150);
});

$(document).on('click', '.photo-close, .photo-overlay', function (e) {
  if ($(e.target).closest('.photo-modal').length === 0 || $(e.target).hasClass('photo-close')) {
    $('.photo-overlay').fadeOut(150);
  }
});
</script>
<script>
$(function () {
  $('#loading_panel, #loading_panel_lite, #loading-overlay-layer').hide();
});
</script>