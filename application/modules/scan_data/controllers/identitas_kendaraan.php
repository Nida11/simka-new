<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	include APPPATH.'controllers/site_utils.php';
	class Identitas_kendaraan extends Site_utils {
		
		function __construct(){
			parent::__construct();
			$this->check_islogin();
			$this->v_default = 'v_scan_data/';
			$this->load->model('identitas_kendaraan_model', 'model_data');
			$this->load->model('admin/wilayah_model', 'wilayah_model');
			$this->_get_request();
		}
		
		function _get_request(){
			$this->id_identitas_kendaraan = $this->get_default_request('id_identitas_kendaraan', '');
			$this->no_polisi = $this->get_default_request('no_polisi', '');
			$this->kd_plat = $this->get_default_request('kd_plat', '');
			$this->no_polisi_format = $this->get_default_request('no_polisi_format', '');
			$this->kd_mohon	 = $this->get_default_request('kd_mohon', '');
			$this->no_mesin = $this->get_default_request('no_mesdata', '');
			$this->no_rangka = $this->get_default_request('no_rangka', '');
			$this->id_wilayah = $this->get_default_request('id_wilayah', '');
			$this->id_kecamatan = $this->get_default_request('id_kecamatan', '');
			$this->nama_pemilik = $this->get_default_request('nama_pemilik', '');
			$this->tgl_akhir_pajak = $this->get_default_request('tgl_akhir_pajak', '');
			$this->tgl_akhir_stnk = $this->get_default_request('tgl_akhir_stnk', '');
			
			$this->mode_input = $this->get_default_request('mode_input', 'save');
			$this->value = $this->get_default_request('value', '');
			$this->objectname = $this->get_default_request('objectname', '');
		}
		
		function index(){
			$data['title'] = 'Manajemen Data Identitas Kendaraan';
			$content['js'] = get_js_modules(array('scan_data/ngidentitas_kendaraan'));
			$content['css'] = get_css_modules(array('scan_data/identitas_kendaraan'));
			$data['content_banner'] = $this->load->view('content_banner/manajemen_data', null, true);
			$data['content'] = $this->load->view($this->v_default.'identitas_kendaraan_index', $content ,true);
			$this->load->view('template_main', $data);
		}
		
		function get_data(){
			$data = array(
			'id_identitas_kendaraan' => $this->id_identitas_kendaraan,
			'no_polisi' => $this->no_polisi,
			'no_polisi_format' => $this->no_polisi_format,
			'no_mesin' => $this->no_mesdata,
			'kd_mohon' => $this->kd_mohon,
			'no_rangka' => $this->no_rangka,
			'a.id_wilayah' => $this->id_wilayah,
			'a.id_kecamatan' => $this->id_kecamatan,
			'nama_pemilik' => $this->nama_pemilik,
			'tgl_akhir_pajak' => $this->tgl_akhir_pajak,
			'tgl_akhir_stnk' => $this->tgl_akhir_stnk,
			);
			$response= $this->model_data->get_data($data);
			$this->_build_result($response, 'info data', 200);
		}


		function sync_data($no_polisi = '')
{
    $url = 'http://15.1.1.100/api/new-sakti/v1/integrasi-simka/get-data-objek?';

    try {
        $data_config_wilayah = array();

        // Ambil input nopol
        $this->no_polisi = (!empty($no_polisi)) ? $no_polisi : $this->no_polisi;

        // Normalisasi user input
        $no_polisi = strtoupper(trim($this->no_polisi));
        $no_polisi = preg_replace('/\s+/', ' ', $no_polisi);

        // PECABAN NOMOR POLISI SESUAI RULE
        // Huruf depan: 1 huruf
        // Angka: 1–4 digit
        // Huruf belakang: 0–3 huruf
        if (preg_match('/^([A-Z]{1})\s*(\d{1,4})\s*([A-Z]{0,3})$/i', $no_polisi, $m)) {

            $huruf_depan     = strtoupper($m[1]);
            $angka           = $m[2];
            $huruf_belakang  = strtoupper($m[3]);  // boleh kosong

            // FORMAT API = depan + belakang + angka
            if ($huruf_belakang === '') {
                // contoh: D 77 → "D 77"
                $no_polisi_api = $huruf_depan . ' ' . $angka;
            } else {
                // contoh: D 5123 VDE → "D VDE5123"
                $no_polisi_api = $huruf_depan . ' ' . $huruf_belakang . $angka;
            }

        } else {
            throw new Exception("Format nomor polisi tidak valid");
        }

        // Konversi final
        $no_polisi_api = strtoupper(trim($no_polisi_api));

        // Default kd_plat = 1
        $kd_plat = (!empty($this->kd_plat)) ? $this->kd_plat : 1;

        // Build URL
        $full_url = $url . 'no_polisi=' . urlencode($no_polisi_api) . '&kd_plat=' . urlencode($kd_plat);

        // CURL invoke API
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $full_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic c2lta2E6NnVuRHVsTXU=',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $dataArray = json_decode($response, true);

        if ($http_code !== 200 || empty($dataArray['data'])) {
            throw new Exception("Error, Data Kosong");
        }

        // Ambil data API
        $data_api = $dataArray['data'][0];

        // Ambil wilayah
        $id_wilayah = isset($data_api['kd_wil']) ? $data_api['kd_wil'] : '';

        if (!empty($id_wilayah)) {
            $query = $this->wilayah_model->get_data($id_wilayah);
            foreach ($query as $v_wilayah) {
                $url_local_path = rtrim($v_wilayah['url_path'], '\\');

                $data_config_wilayah = array(
                    'url_path' => $v_wilayah['url_path'],
                    'url_local_path' => URL_LOCAL_PARENT . $url_local_path . DIRECTORY_SEPARATOR,
                    'nama_singkat' => $v_wilayah['nama_singkat'],
                );
            }

            $data_api = array_merge($data_api, $data_config_wilayah);
        } else {
            throw new Exception("Error, Data Kosong");
        }

        $data_api = array_filter(array_map('trim', $data_api));
        $response = array('data_simka' => array($data_api));
        $this->_build_result($response, 'info data', 200);

    } catch (Exception $e) {

        $response = array(
            'type' => 'error',
            'msg'  => $e->getMessage()
        );

        $this->_build_error_result($response, 500);
    }
}

		
// 		function sync_data($no_polisi = '') {
// 			$url = 'http://15.1.1.100/api/new-sakti/v1/integrasi-simka/get-data-objek?';
		
// 			try {
// 				$data_config_wilayah = array();
		
// 				// Ambil inputan no_polisi dari form
// 				$this->no_polisi = (!empty($no_polisi)) ? $no_polisi : $this->no_polisi;
// 				$no_polisi = strtoupper(trim($this->no_polisi)); // Pastikan huruf besar & hapus spasi berlebih
// 				$no_polisi = preg_replace('/\s+/', ' ', $no_polisi); // hilangkan semua spasi double
// 				$no_polisi = strtoupper($no_polisi);


// 				// Pastikan regex berhasil pertama kali
// 				// Normalisasi format nomor polisi
// 				//if (preg_match('/^([A-Z])\s*(\d{1,4})\s*([A-Z]{0,3})$/i', $no_polisi, $matches)) {


// if (preg_match('/^([A-Z]{1,3})\s*(\d{1,4})\s*([A-Z]{1,3})$/i', $no_polisi, $matches)) {

// 					$huruf_depan = strtoupper($matches[1]);
// 					$angka = $matches[2];
// 					$huruf_belakang = strtoupper($matches[3]);




// 					$len_angka = strlen($angka);
// 					$len_huruf = strlen($huruf_belakang);

// 					if ($len_huruf === 0) {
// 						// Contoh: Z1 → Z    1
// 						$no_polisi = $huruf_depan . str_repeat(' ', 4) . $angka;
// 					} elseif ($len_huruf === 1) {
// 						// Contoh: Z1M → Z M  1
// 						$no_polisi = $huruf_depan . ' ' . $huruf_belakang . str_repeat(' ', 2) . $angka;
// 					} elseif ($len_huruf === 2) {
// 						// Contoh: Z1TL → Z TL 1
// 						$no_polisi = $huruf_depan . ' ' . $huruf_belakang . ' ' . $angka;
// 					} elseif ($len_huruf === 3) {
// 						// Contoh: D147VBO → D VBO147
// 						$no_polisi = $huruf_depan . ' ' . $huruf_belakang . $angka;
// 					}
// 				} else {
// 					throw new Exception("Format nomor polisi tidak valid");
// 				}

// 				// Konversi ke uppercase jika diperlukan
// 				$no_polisi = strtoupper($no_polisi);

// 				// Default kd_plat = 1 jika kosong
// 				$kd_plat = (!empty($this->kd_plat)) ? $this->kd_plat : 1;
		
// 				// Buat URL lengkap dengan encoding
// 				$full_url = $url . 'no_polisi=' . urlencode($no_polisi) . '&kd_plat=' . urlencode($kd_plat);

// 				//echo $full_url;
// 				// Inisialisasi CURL
// 				$curl = curl_init();
// 				curl_setopt_array($curl, array(
// 					CURLOPT_URL => $full_url,
// 					CURLOPT_RETURNTRANSFER => true,
// 					CURLOPT_ENCODING => '',
// 					CURLOPT_MAXREDIRS => 10,
// 					CURLOPT_TIMEOUT => 30,
// 					CURLOPT_FOLLOWLOCATION => true,
// 					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// 					CURLOPT_CUSTOMREQUEST => 'GET',
// 					CURLOPT_HTTPHEADER => array(
// 						'Authorization: Basic c2lta2E6NnVuRHVsTXU=', // Auth
// 						'Content-Type: application/json'
// 					),
// 				));
		
// 				// Eksekusi API
// 				$response = curl_exec($curl);
// 				$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
// 				curl_close($curl);
		
// 				// Decode response JSON
// 				$dataArray = json_decode($response, true);
		
// 				if ($http_code !== 200 || empty($dataArray['data'])) {
// 					throw new Exception("Error, Data Kosong");
// 				}
		
// 				// Ambil data dari response API
// 				$data_api = $dataArray['data'][0];
		
// 				// Ambil kd_wil dari response
// 				$id_wilayah = isset($data_api['kd_wil']) ? $data_api['kd_wil'] : '';
		
// 				if (!empty($id_wilayah)) {
// 					// Ambil konfigurasi wilayah
// 					$query = $this->wilayah_model->get_data($id_wilayah);
// 					foreach ($query as $v_wilayah) {
// 						// Pastikan 'url_local_path' tidak memiliki backslash ganda
// 						$url_local_path = rtrim($v_wilayah['url_path'], '\\'); // Menghapus backslash ekstra
// 						$data_config_wilayah = array(
// 							'url_path' => $v_wilayah['url_path'],
// 							'url_local_path' => URL_LOCAL_PARENT . $url_local_path . DIRECTORY_SEPARATOR, // Menggunakan DIRECTORY_SEPARATOR untuk penyesuaian OS
// 							'nama_singkat' => $v_wilayah['nama_singkat'],
// 						);
// 					}
// 					$data_api = array_merge($data_api, $data_config_wilayah);
// 				} else {
// 					throw new Exception("Error, Data Kosong", 1);
// 				}
		
// 				// Filter hasil & return
// 				$data_api = array_filter(array_map('trim', $data_api));
// 				$response = array('data_simka' => array($data_api));
// 				$this->_build_result($response, 'info data', 200);
// 				//echo "<pre>";
// 				//echo "Final Data API:\n";
// 				//print_r($data_api);
// 				//echo "</pre>";
		
// 			} catch (Exception $e) {
// 				// Tangani error dan tampilkan pesan
// 				$response = array(
// 					'type' => 'error',
// 					'msg' => $e->getMessage()
// 				);
// 				//echo "<pre>";
// 				//echo "Catch Exception:\n";
// 				//print_r($response);
// 				//echo "</pre>";
// 				$this->_build_error_result($response, 500);
// 			}
// 		}
		
		
		
		function save_data(){
			try{
				if(empty($this->id_wilayah)) throw new Exception("Error, id_wilayah tidak boleh kosong", 1);
				$data = array(
				'no_polisi' => $this->no_polisi,
				'no_polisi_format' => $this->no_polisi_format,
				'kd_mohon'=> $this->kd_mohon,
				'no_mesin' => $this->no_mesdata,
				'no_rangka' => $this->no_rangka,
				'id_wilayah' => $this->id_wilayah,
				'id_kecamatan' => $this->id_kecamatan,
				'nama_pemilik' => $this->nama_pemilik,
				);
				$response = $this->model_data->save_data($data, $this->mode_input);
				if(!$response) throw new Exception("Error, $this->mode_input Data Modul Gagal", 1);
				$response = array(
				'type' => 'Success',
				'msg' => 'Data Berhasil '.$this->mode_input
				);
				$this->_build_result($response, $this->mode_input.' data', 200);
				}catch(Exception $e){
				$response = array(
				'type' => 'error',
				'msg' => $e->getMessage()
				);
				$this->_build_error_result($response, 500);
			}
		}
		
		function toogle_data(){
			try{
				if(empty($this->id_identitas_kendaraan)) throw new Exception("Error, id_identitas_kendaraan Isian Wajib Diisi", 1);
				$data = array(
				'id_identitas_kendaraan' => $this->id_identitas_kendaraan,
				$this->objectname => $this->value
				);
				$response = $this->model_data->save_data($data, $this->mode_input);
				if(!$response) throw new Exception("Error, $this->mode_input Data Gagal", 1);
				$response = array(
				'type' => 'Success',
				'msg' => $this->objectname.' Berhasil '.$this->mode_input
				);
				$this->_build_result($response, $this->mode_input.' data', 200);
				}catch(Exception $e){
				$response = array(
				'type' => 'error',
				'msg' => $e->getMessage()
				);
				$this->_build_error_result($response, 500);
			}	
		}
	}
