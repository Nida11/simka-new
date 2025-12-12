<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal Scan</th>
            <th>No Polisi</th>
            <th>Nama Pemilik</th>
            <th>Wilayah</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($scan_data)) : ?>
            <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
        <?php else: $no = 1; foreach ($scan_data as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row->tgl_scan ?></td>
                <td><?= $row->no_polisi ?></td>
                <td><?= $row->nama_pemilik ?></td>
                <td><?= $row->id_wilayah ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<div><?= $pagination_links ?></div>