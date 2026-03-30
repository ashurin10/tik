<?php
$url = 'https://upload.wikimedia.org/wikipedia/commons/c/cd/Lambang_Kabupaten_Subang.png';
$options = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "Cookie: foo=bar\r\n" .
              "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
  )
);
$context = stream_context_create($options);
$data = file_get_contents($url, false, $context);
if ($data !== false) {
    file_put_contents('logo-subang.png', $data);
    echo "Success: Downloaded logo to public/logo-subang.png\n";
} else {
    echo "Error: Failed to fetch image\n";
}
?>
