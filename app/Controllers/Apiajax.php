<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Apiajax extends ResourceController
{
    public function pGenerate()
    {
        if ($this->request->isAJAX()) {
            helper('text');
            $searchImg = $this->request->getPost('search_img');
            $typeQuotes = $this->request->getPost('type_quotes');
            $fonts = $this->request->getPost('fonts');
            if(!$this->validate([
                'search_img'    =>  'required',
                'type_quotes'   =>  'required',
                'fonts'         =>  'required'
            ])){
                $json           =
                [
                    'status'    =>  false,
                    'data'      =>  'Form wajib diisi'
                ];
            }else{
                $ch = curl_init();
                $secretKey = "SECRET_KEY_DISINI";
                $url = "https://api.unsplash.com/search/photos?query=".urlencode($searchImg)."&client_id=$secretKey";
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $res = curl_exec($ch);
                curl_close($ch);
                $resJson = json_decode($res, true);

                $patchOne = [];
                $jsonFilePath = FCPATH . 'assets/data/data.json';
                $jsonData = file_get_contents($jsonFilePath);
                $data = json_decode($jsonData, true);
                $folder_path = FCPATH . '/assets/images/';
                // Mengecek apakah path yang diberikan adalah sebuah folder
                if (is_dir($folder_path)) {
                    // Mendapatkan daftar file dalam folder
                    $files = glob($folder_path . '*');
                    // Melakukan iterasi pada setiap file dan menghapusnya
                    foreach ($files as $file) {
                        // Memastikan bahwa file yang dihapus adalah file, bukan folder
                        if (is_file($file)) {
                            // Menghapus file
                            if(unlink($file)){

                            }
                        }
                    }
                }
                foreach ($resJson['results'] as $rawResultOne) {
                    $urls = $rawResultOne['urls'];
                    $regular_url = $urls['regular'];
                    $authorImage = isset($rawResultOne['user']['links']) ? $rawResultOne['user']['links']['html'] : '';
                    if(empty($authorImage)){
                        $authorImage = isset($rawResultOne['user']) ? "https://unsplash.com/@".$rawResultOne['user']['username'] : '';
                    }
                    $image = imagecreatefromjpeg($regular_url);
                    // Tambahkan latar belakang hitam dengan opacity menggunakan lapisan
                    $black = imagecolorallocatealpha($image, 0, 0, 0, 100); // Opacity diset di sini
                    imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $black);
                    // Gabungkan gambar asli dengan lapisan hitam
                    imagecopymerge($image, $image, 0, 0, 0, 0, imagesx($image), imagesy($image), 100);
                    $text_color = imagecolorallocate($image, 255, 255, 255);
                    // Pilih teks acak dari file JSON
                    $randomText = $this->getRandomText($data, $typeQuotes);
                    // Tambahkan teks pada gambar
                    $text = $randomText ? $randomText['text'] : "";
                    $font = FCPATH . "assets/fonts/".$fonts; // Path ke file font Anda
                    $font_size = 40;
                    // Ukuran gambar
                    $image_width = imagesx($image);
                    $image_height = imagesy($image);
                    // Maksimum lebar teks yang diizinkan pada gambar
                    $max_text_width = 0.8 * $image_width; // Misalnya, 80% dari lebar gambar
                    // Pisahkan teks menjadi beberapa baris
                    $wrapped_text = wordwrap($text, 25, "\n"); // Ganti angka 25 dengan panjang karakter maksimum per baris yang Anda inginkan
                    // Ukuran teks
                    $text_box = imagettfbbox($font_size, 0, $font, $wrapped_text);
                    $text_width = $text_box[2] - $text_box[0];
                    $text_height = $text_box[7] - $text_box[1];
                    // Posisi X dan Y teks di tengah gambar
                    $x = ($image_width - $text_width) / 2;
                    $y = ($image_height + $text_height) / 2;

                    // Pisahkan teks menjadi beberapa baris jika melebihi lebar maksimum yang diizinkan
                    if ($text_width > $max_text_width) {
                        $lines = explode("\n", $wrapped_text);
                        $y -= (count($lines) - 1) * $text_height / 2;
                        foreach ($lines as $line) {
                            imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $line);
                            $y += $text_height;
                        }
                    } else {
                        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $wrapped_text);
                    }
                    // Tambahkan teks copyright
                    $copyright_text = $authorImage;
                    $copyright_font_size = 10;
                    $copyright_x = 10;
                    $copyright_y = $image_height - 20;
                    imagettftext($image, $copyright_font_size, 0, $copyright_x, $copyright_y, $text_color, $font, $copyright_text);
                    // Tambahkan teks di bawah kanan gambar
                    $additional_text = "ggQuotes - Generator Quotes by github.com/ajisml";
                    $additional_font_size = 12;
                    $additional_x = $image_width - $text_width - 10; // Adjust this value to position the text as needed
                    $additional_y = $image_height - 20; // Adjust this value to position the text as needed
                    imagettftext($image, $additional_font_size, 0, $additional_x, $additional_y, $text_color, $font, $additional_text);
                    // Simpan gambar dengan teks ke direktori
                    $output_file = FCPATH . 'assets/images/ggQuotes-' . random_string('alnum', 10) . '.jpg';
                    imagejpeg($image, $output_file);

                    $patchOne[] = $regular_url;
                }
                $json = [
                    'status' => true,
                    'data' => 'Yeay! Berhasil Diproses',
                    'row' => $randomText
                ];
            }
            return $this->response->setJSON($json);
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
    private function getRandomText($data, $type) {
        $filteredData = array_filter($data, function($item) use ($type) {
            return $item['type'] === $type;
        });
    
        if (!empty($filteredData)) {
            $randomIndex = array_rand($filteredData);
            return $filteredData[$randomIndex];
        }
    
        return null;
    }
    public function listQuotes(){
        if($this->request->isAJAX()){
            // Path folder yang akan dilihat isinya
            $folder_path = FCPATH.'/assets/images/';
            // Mengecek apakah path yang diberikan adalah sebuah folder
            if (is_dir($folder_path)) {
                // Mendapatkan daftar file dalam folder
                $files = scandir($folder_path);
                // Menampilkan daftar file
                $data               =   [];
                if (count($files) > 2) {
                    foreach ($files as $file) {
                        // Memastikan bahwa yang ditampilkan adalah file, bukan folder atau direktori '.' dan '..'
                        if (is_file($folder_path . $file) && $file != '.' && $file != '..') {
                            $data[]     =   $file;
                        }
                    }
                    $json               =
                    [
                        'status'        =>  true,
                        'data'          =>  $data
                    ];
                }else{
                    $json               =
                    [
                        'status'        =>  false,
                        'data'          =>  "File tidak ditemukan"
                    ];
                }
            } else {
                $json               =
                [
                    'status'        =>  false,
                    'data'          =>  "Path yang diberikan bukan merupakan folder"
                ];
            }
            return $this->response->setJSON($json);
        }else{
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}
