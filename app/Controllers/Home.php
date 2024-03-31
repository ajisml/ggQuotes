<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $folder_path = FCPATH.'/assets/fonts/';
        // Mengecek apakah path yang diberikan adalah sebuah folder
        if (is_dir($folder_path)) {
            // Mendapatkan daftar file dalam folder
            $files = scandir($folder_path);
            // Menampilkan daftar file
            $dataFont           =   [];
            if (count($files) > 2) {
                foreach ($files as $file) {
                    // Memastikan bahwa yang ditampilkan adalah file, bukan folder atau direktori '.' dan '..'
                    if (is_file($folder_path . $file) && $file != '.' && $file != '..') {
                        $dataFont[]     =   $file;
                    }
                }
            }
        }
        $data                   =   
        [
            'title'             =>  'ggQuotes - Generator Quotes',
            'fonts'             =>  $dataFont
        ];
        echo view('home', $data);
    }
}
