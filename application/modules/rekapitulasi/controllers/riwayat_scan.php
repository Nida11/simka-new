<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include APPPATH.'controllers/site_utils.php';
class Riwayat_scan  extends Site_utils {

 public function __construct()
  {
    parent::__construct();
    $this->load->model('riwayat_model', 'm');
  }
  
  public function index()
{
  $limit  = 5;
  $offset = max(0, (int) $this->input->get('page'));
  $page   = floor($offset / $limit) + 1;


  $filter = [
    'no_polisi'    => trim($this->input->get('no_polisi')),
    'no_rangka'    => trim($this->input->get('no_rangka')),
    'no_mesin'     => trim($this->input->get('no_mesin')),
    'nama_pemilik' => trim($this->input->get('nama_pemilik'))
  ];

  $isSearch = false;
  foreach ($filter as $v) {
    if ($v !== '') {
      $isSearch = true;
      break;
    }
  }

  $rows = [];
  $pagination = '';

  if ($isSearch) {
    $rows = $this->m->get_history($limit, $offset, $filter);

    if (!empty($rows)) {
      $noScan = array_column($rows, 'no_scan');
      $images = $this->m->get_images($noScan);

      $imgMap = [];
      foreach ($images as $img) {
        $imgMap[$img->no_scan][] = $img->img_raw;
      }

      foreach ($rows as &$r) {
        $r->images = $imgMap[$r->no_scan] ?? [];
      }
    }

    // =====================
    // PAGINATION
    // =====================
    $total = $this->m->count_history($filter);
$this->load->library('pagination');

$query = array_filter($filter);
$query['page'] = ''; // placeholder untuk CI

$config['base_url'] = site_url('rekapitulasi/riwayat_scan') . '?' . http_build_query($query);
$config['total_rows'] = $total;
$config['per_page'] = $limit;
$config['page_query_string'] = TRUE;
$config['query_string_segment'] = 'page';
$config['reuse_query_string'] = TRUE;

$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
$config['full_tag_close'] = '</ul>';
$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';
$config['cur_tag_open'] = '<li class="active"><span>';
$config['cur_tag_close'] = '</span></li>';
$config['prev_tag_open'] = '<li>';
$config['prev_tag_close'] = '</li>';
$config['next_tag_open'] = '<li>';
$config['next_tag_close'] = '</li>';

$this->pagination->initialize($config);
$pagination = $this->pagination->create_links();

  }

  $content['data'] = $rows;
  $content['isSearch'] = $isSearch;
  $content['pagination'] = $pagination;

  $data['title'] = 'Riwayat Scan';
  $data['content_banner'] = $this->load->view('content_banner/riwayat', null, true);
  $data['content'] = $this->load->view('rekapitulasi/riwayat_scan_view', $content, true);
  $this->load->view('template_main', $data);
}


}