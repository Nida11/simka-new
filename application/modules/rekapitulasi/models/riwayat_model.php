<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Riwayat_model extends CI_Model {

  private function convert_no_polisi($input)
  {
    // Bersihkan input: hilangkan spasi, uppercase
    $clean = strtoupper(str_replace(' ', '', $input));

    // Regex: huruf wilayah (1+), angka (1+), huruf singkatan (1+)
    // Contoh: E5427PCM → groups: E, 5427, PCM
    if (preg_match('/^([A-Z]+)(\d+)([A-Z]+)$/', $clean, $matches)) {
      $wilayah = $matches[1];  // E
      $angka   = $matches[2];  // 5427
      $singkat = $matches[3];  // PCM
      return $wilayah . ' ' . $singkat . $angka;  // "E PCM5427"
    }

    // Jika tidak match, return input asli (untuk fallback LIKE)
    return $input;
  }

  // =============================
  // PARENT DATA (VIEW)
  // =============================
  public function get_history($limit, $offset, $filter = [])
  {
    $db = $this->db->from('history_scan');

    // ===== FILTER OPSIONAL =====
    if (!empty($filter['no_polisi'])) {
      $converted = $this->convert_no_polisi($filter['no_polisi']);
      
      // Jika konversi berhasil (format standar), query exact match
      if ($converted !== $filter['no_polisi']) {
        $db->where('no_polisi', $converted);
      } else {
        // Fallback: LIKE substring (case-insensitive, tanpa spasi)
        $clean_input = str_replace(' ', '', strtolower($filter['no_polisi']));
        $db->where("REPLACE(LOWER(no_polisi), ' ', '') LIKE '%" . $this->db->escape_like_str($clean_input) . "%'");
      }
    }

    if (!empty($filter['no_rangka'])) {
      $db->like('no_rangka', $filter['no_rangka']);
    }

    if (!empty($filter['no_mesin'])) {
      $db->like('no_mesin', $filter['no_mesin']);
    }

    if (!empty($filter['nama_pemilik'])) {
      $db->like('nama_pemilik', $filter['nama_pemilik']);
    }

    return $db
      ->limit($limit, $offset)
      ->get()
      ->result();
  }


  // =============================
  // CHILD DATA (VIEW)
  // =============================
  public function get_images($noScanList)
{
  if (empty($noScanList)) return [];

  return $this->db
    ->select('no_scan, img_raw')
    ->from('history_scan_image')
    ->where_in('no_scan', $noScanList)
    ->get()
    ->result();
}


  public function count_history($filter = [])
{
  $db = $this->db->from('history_scan');

  if (!empty($filter['no_polisi'])) {
    $converted = $this->convert_no_polisi($filter['no_polisi']);
    if ($converted !== $filter['no_polisi']) {
      $db->where('no_polisi', $converted);
    } else {
      $clean_input = str_replace(' ', '', strtolower($filter['no_polisi']));
      $db->where("REPLACE(LOWER(no_polisi), ' ', '') LIKE '%" . $this->db->escape_like_str($clean_input) . "%'");
    }
  }

  if (!empty($filter['no_rangka'])) {
    $db->like('no_rangka', $filter['no_rangka']);
  }

  if (!empty($filter['no_mesin'])) {
    $db->like('no_mesin', $filter['no_mesin']);
  }

  if (!empty($filter['nama_pemilik'])) {
    $db->like('nama_pemilik', $filter['nama_pemilik']);
  }

  return $db->count_all_results();
}

}
