<?php

namespace App\Http\Controllers;

class CaptchaController extends Controller
{
    public function generate()
    {
        $captcha = $this->scienceQuestion();
        session([
            'captcha_question' => $captcha['question'],
            'captcha_answer' => $captcha['answers'],
        ]);

        $width = 420;
        $height = 48;
        $image = imagecreatetruecolor($width, $height);

        // Background: subtle gradient
        $bgStart = imagecolorallocate($image, 245, 247, 250);
        $bgEnd   = imagecolorallocate($image, 232, 236, 243);

        for ($y = 0; $y < $height; $y++) {
            $r = intval(245 + (232 - 245) * ($y / $height));
            $g = intval(247 + (236 - 247) * ($y / $height));
            $b = intval(250 + (243 - 250) * ($y / $height));
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Random noise dots
        for ($i = 0; $i < 55; $i++) {
            $dotColor = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
            imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
        }

        // Random arcs (curved lines)
        for ($i = 0; $i < 2; $i++) {
            $arcColor = imagecolorallocate($image, rand(160, 200), rand(160, 200), rand(160, 200));
            imagearc(
                $image,
                rand(0, $width),
                rand(0, $height),
                rand(80, 150),
                rand(25, 65),
                rand(0, 180),
                rand(180, 360),
                $arcColor
            );
        }

        $textColor = imagecolorallocate($image, 37, 99, 235); // Blue #2563EB
        $labelColor = imagecolorallocate($image, 75, 85, 99);
        $shadowColor = imagecolorallocate($image, 200, 210, 230);

        $fontPath = $this->captchaFontPath();
        $question = $captcha['question'];

        if ($fontPath && function_exists('imagettftext')) {
            imagettftext($image, 8, 0, 14, 16, $labelColor, $fontPath, 'Captcha Ilmu Pengetahuan');
            imagettftext($image, 11, 0, 14, 35, $shadowColor, $fontPath, $question);
            imagettftext($image, 11, 0, 13, 34, $textColor, $fontPath, $question);
        } else {
            imagestring($image, 2, 14, 7, 'Captcha Ilmu Pengetahuan', $labelColor);
            imagestring($image, 3, 14, 26, $question, $textColor);
        }

        // Thin border
        $borderColor = imagecolorallocate($image, 200, 210, 225);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

        ob_start();
        imagepng($image);
        $buffer = ob_get_clean();
        imagedestroy($image);

        return response($buffer)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function scienceQuestion(): array
    {
        $questions = [
            ['question' => 'Planet terbesar?', 'answers' => ['jupiter']],
            ['question' => 'Rumus kimia air?', 'answers' => ['h2o', 'h₂o']],
            ['question' => 'Gas untuk bernapas?', 'answers' => ['oksigen', 'oxygen', 'o2', 'o₂']],
            ['question' => 'Pusat tata surya?', 'answers' => ['matahari', 'surya']],
            ['question' => 'Pengiring planet?', 'answers' => ['satelit', 'bulan']],
            ['question' => 'Pengukur suhu?', 'answers' => ['termometer', 'thermometer']],
            ['question' => 'Makanan tumbuhan?', 'answers' => ['fotosintesis']],
            ['question' => 'Satuan gaya SI?', 'answers' => ['newton', 'n']],
            ['question' => 'Planet merah?', 'answers' => ['mars']],
            ['question' => 'Planet bercincin?', 'answers' => ['saturnus', 'saturn']],
            ['question' => 'Planet terdekat Matahari?', 'answers' => ['merkurius', 'merkuri', 'mercury']],
            ['question' => 'Planet tempat manusia?', 'answers' => ['bumi', 'earth']],
            ['question' => 'Bintang terdekat Bumi?', 'answers' => ['matahari', 'surya']],
            ['question' => 'Galaksi kita?', 'answers' => ['bimasakti', 'milkyway']],
            ['question' => 'Ilmu tentang bintang?', 'answers' => ['astronomi']],
            ['question' => 'Batu dari angkasa?', 'answers' => ['meteorit']],
            ['question' => 'Ekor komet mengarah?', 'answers' => ['menjauhimatahari', 'menjauhi']],
            ['question' => 'Fase bulan penuh?', 'answers' => ['purnama']],
            ['question' => 'Gerak Bumi berputar?', 'answers' => ['rotasi']],
            ['question' => 'Gerak Bumi keliling?', 'answers' => ['revolusi']],
            ['question' => 'Lapisan udara Bumi?', 'answers' => ['atmosfer']],
            ['question' => 'Lapisan air Bumi?', 'answers' => ['hidrosfer']],
            ['question' => 'Lapisan batu Bumi?', 'answers' => ['litosfer']],
            ['question' => 'Pusat gempa?', 'answers' => ['episentrum']],
            ['question' => 'Titik awal gempa?', 'answers' => ['hiposentrum']],
            ['question' => 'Alat ukur gempa?', 'answers' => ['seismograf']],
            ['question' => 'Skala gempa umum?', 'answers' => ['richter']],
            ['question' => 'Gunung api aktif?', 'answers' => ['vulkan']],
            ['question' => 'Batuan cair panas?', 'answers' => ['magma']],
            ['question' => 'Magma keluar jadi?', 'answers' => ['lava']],
            ['question' => 'Air jatuh dari awan?', 'answers' => ['hujan']],
            ['question' => 'Awan dekat tanah?', 'answers' => ['kabut']],
            ['question' => 'Uap air jadi cair?', 'answers' => ['kondensasi']],
            ['question' => 'Air menguap disebut?', 'answers' => ['evaporasi']],
            ['question' => 'Siklus air?', 'answers' => ['hidrologi']],
            ['question' => 'Alat ukur hujan?', 'answers' => ['ombrometer', 'pluviometer']],
            ['question' => 'Alat ukur angin?', 'answers' => ['anemometer']],
            ['question' => 'Arah angin diukur?', 'answers' => ['windvane', 'penunjukangin']],
            ['question' => 'Alat tekanan udara?', 'answers' => ['barometer']],
            ['question' => 'Satuan suhu Celsius?', 'answers' => ['celsius']],
            ['question' => 'Satuan arus listrik?', 'answers' => ['ampere', 'a']],
            ['question' => 'Satuan tegangan?', 'answers' => ['volt', 'v']],
            ['question' => 'Satuan hambatan?', 'answers' => ['ohm']],
            ['question' => 'Satuan daya?', 'answers' => ['watt', 'w']],
            ['question' => 'Satuan energi?', 'answers' => ['joule', 'j']],
            ['question' => 'Satuan massa?', 'answers' => ['kilogram', 'kg']],
            ['question' => 'Satuan panjang?', 'answers' => ['meter', 'm']],
            ['question' => 'Satuan waktu?', 'answers' => ['sekon', 'detik', 'second']],
            ['question' => 'Satuan frekuensi?', 'answers' => ['hertz', 'hz']],
            ['question' => 'Satuan tekanan?', 'answers' => ['pascal', 'pa']],
            ['question' => 'Gaya tarik Bumi?', 'answers' => ['gravitasi']],
            ['question' => 'Energi gerak?', 'answers' => ['kinetik']],
            ['question' => 'Energi tersimpan?', 'answers' => ['potensial']],
            ['question' => 'Kecepatan cahaya?', 'answers' => ['cahaya']],
            ['question' => 'Bunyi merambat di?', 'answers' => ['medium', 'zat']],
            ['question' => 'Pantulan bunyi?', 'answers' => ['gema', 'echo']],
            ['question' => 'Pantulan cahaya?', 'answers' => ['refleksi']],
            ['question' => 'Pembiasan cahaya?', 'answers' => ['refraksi']],
            ['question' => 'Pemisahan warna?', 'answers' => ['dispersi']],
            ['question' => 'Kutub magnet ada?', 'answers' => ['utara', 'selatan']],
            ['question' => 'Listrik diam?', 'answers' => ['statis']],
            ['question' => 'Listrik mengalir?', 'answers' => ['dinamis']],
            ['question' => 'Penghantar listrik?', 'answers' => ['konduktor']],
            ['question' => 'Penghambat listrik?', 'answers' => ['isolator']],
            ['question' => 'Bahan setengah hantar?', 'answers' => ['semikonduktor']],
            ['question' => 'Muatan elektron?', 'answers' => ['negatif']],
            ['question' => 'Muatan proton?', 'answers' => ['positif']],
            ['question' => 'Muatan neutron?', 'answers' => ['netral']],
            ['question' => 'Pusat atom?', 'answers' => ['inti', 'nukleus']],
            ['question' => 'Partikel negatif?', 'answers' => ['elektron']],
            ['question' => 'Partikel positif?', 'answers' => ['proton']],
            ['question' => 'Partikel netral?', 'answers' => ['neutron']],
            ['question' => 'Nomor atom H?', 'answers' => ['1', 'satu']],
            ['question' => 'Simbol oksigen?', 'answers' => ['o']],
            ['question' => 'Simbol karbon?', 'answers' => ['c']],
            ['question' => 'Simbol besi?', 'answers' => ['fe']],
            ['question' => 'Simbol emas?', 'answers' => ['au']],
            ['question' => 'Simbol perak?', 'answers' => ['ag']],
            ['question' => 'Simbol natrium?', 'answers' => ['na']],
            ['question' => 'Simbol klorin?', 'answers' => ['cl']],
            ['question' => 'Garam dapur?', 'answers' => ['nacl']],
            ['question' => 'Gas karbon dioksida?', 'answers' => ['co2', 'co₂']],
            ['question' => 'Asam lambung?', 'answers' => ['hcl']],
            ['question' => 'pH netral?', 'answers' => ['7', 'tujuh']],
            ['question' => 'pH asam kurang dari?', 'answers' => ['7', 'tujuh']],
            ['question' => 'pH basa lebih dari?', 'answers' => ['7', 'tujuh']],
            ['question' => 'Perubahan padat ke cair?', 'answers' => ['mencair']],
            ['question' => 'Cair ke gas?', 'answers' => ['menguap']],
            ['question' => 'Gas ke cair?', 'answers' => ['mengembun']],
            ['question' => 'Cair ke padat?', 'answers' => ['membeku']],
            ['question' => 'Padat ke gas?', 'answers' => ['menyublim']],
            ['question' => 'Organ pompa darah?', 'answers' => ['jantung']],
            ['question' => 'Organ bernapas?', 'answers' => ['paruparu', 'paru']],
            ['question' => 'Organ penyaring darah?', 'answers' => ['ginjal']],
            ['question' => 'Organ pengatur tubuh?', 'answers' => ['otak']],
            ['question' => 'Sel darah merah?', 'answers' => ['eritrosit']],
            ['question' => 'Sel darah putih?', 'answers' => ['leukosit']],
            ['question' => 'Keping darah?', 'answers' => ['trombosit']],
            ['question' => 'Pembawa oksigen darah?', 'answers' => ['hemoglobin']],
            ['question' => 'Rangka tubuh?', 'answers' => ['tulang']],
            ['question' => 'Sendi gerak lutut?', 'answers' => ['engsel']],
            ['question' => 'Indra penglihatan?', 'answers' => ['mata']],
            ['question' => 'Indra pendengaran?', 'answers' => ['telinga']],
            ['question' => 'Indra penciuman?', 'answers' => ['hidung']],
            ['question' => 'Indra pengecap?', 'answers' => ['lidah']],
            ['question' => 'Indra peraba?', 'answers' => ['kulit']],
            ['question' => 'Vitamin dari matahari?', 'answers' => ['d']],
            ['question' => 'Zat pembangun tubuh?', 'answers' => ['protein']],
            ['question' => 'Sumber energi utama?', 'answers' => ['karbohidrat']],
            ['question' => 'Lemak disebut juga?', 'answers' => ['lipid']],
            ['question' => 'Unit kehidupan?', 'answers' => ['sel']],
            ['question' => 'Pembawa sifat?', 'answers' => ['gen']],
            ['question' => 'Materi genetik?', 'answers' => ['dna']],
            ['question' => 'Hijau daun?', 'answers' => ['klorofil']],
            ['question' => 'Lubang daun?', 'answers' => ['stomata']],
            ['question' => 'Akar menyerap?', 'answers' => ['air']],
            ['question' => 'Bunga menjadi?', 'answers' => ['buah']],
            ['question' => 'Serbuk sari?', 'answers' => ['polen']],
            ['question' => 'Hewan bertulang belakang?', 'answers' => ['vertebrata']],
            ['question' => 'Hewan tanpa tulang belakang?', 'answers' => ['invertebrata']],
            ['question' => 'Hewan menyusui?', 'answers' => ['mamalia']],
            ['question' => 'Hewan bertelur?', 'answers' => ['ovipar']],
            ['question' => 'Hewan beranak?', 'answers' => ['vivipar']],
            ['question' => 'Pemakan tumbuhan?', 'answers' => ['herbivora']],
            ['question' => 'Pemakan daging?', 'answers' => ['karnivora']],
            ['question' => 'Pemakan segalanya?', 'answers' => ['omnivora']],
            ['question' => 'Pengurai alami?', 'answers' => ['dekomposer']],
            ['question' => 'Hubungan makan?', 'answers' => ['rantaimakanan']],
            ['question' => 'Kumpulan rantai makan?', 'answers' => ['jaringmakanan']],
            ['question' => 'Tempat hidup makhluk?', 'answers' => ['habitat']],
            ['question' => 'Peran makhluk hidup?', 'answers' => ['niche', 'relung']],
            ['question' => 'Lingkungan hidup?', 'answers' => ['ekosistem']],
            ['question' => 'Banyak jenis hayati?', 'answers' => ['biodiversitas']],
            ['question' => 'Ilmu makhluk hidup?', 'answers' => ['biologi']],
            ['question' => 'Ilmu zat?', 'answers' => ['kimia']],
            ['question' => 'Ilmu alam benda?', 'answers' => ['fisika']],
            ['question' => 'Ilmu Bumi?', 'answers' => ['geologi']],
            ['question' => 'Ilmu cuaca?', 'answers' => ['meteorologi']],
            ['question' => 'Ilmu laut?', 'answers' => ['oseanografi']],
            ['question' => 'Ilmu fosil?', 'answers' => ['paleontologi']],
            ['question' => 'Ilmu serangga?', 'answers' => ['entomologi']],
            ['question' => 'Ilmu burung?', 'answers' => ['ornitologi']],
            ['question' => 'Ilmu ikan?', 'answers' => ['iktiologi']],
            ['question' => 'Ilmu jamur?', 'answers' => ['mikologi']],
            ['question' => 'Ilmu tumbuhan?', 'answers' => ['botani']],
        ];

        return $questions[array_rand($questions)];
    }

    private function captchaFontPath(): ?string
    {
        $candidates = [
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\Arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
