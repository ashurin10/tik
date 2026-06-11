<?php

namespace App\Http\Controllers;

class CaptchaController extends Controller
{
    public function generate()
    {
        $captcha = $this->technologySecurityQuestion();
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
            imagettftext($image, 8, 0, 14, 16, $labelColor, $fontPath, 'Captcha Teknologi & Keamanan');
            imagettftext($image, 11, 0, 14, 35, $shadowColor, $fontPath, $question);
            imagettftext($image, 11, 0, 13, 34, $textColor, $fontPath, $question);
        } else {
            imagestring($image, 2, 14, 7, 'Captcha Teknologi & Keamanan', $labelColor);
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

    private function technologySecurityQuestion(): array
    {
        $questions = [
            ['question' => 'Otak komputer?', 'answers' => ['cpu', 'processor', 'prosesor']],
            ['question' => 'Memori sementara?', 'answers' => ['ram']],
            ['question' => 'Penyimpanan cepat?', 'answers' => ['ssd']],
            ['question' => 'Penyimpanan magnetik?', 'answers' => ['hdd', 'harddisk']],
            ['question' => 'Papan utama PC?', 'answers' => ['motherboard', 'mainboard']],
            ['question' => 'Kartu grafis?', 'answers' => ['gpu', 'vga']],
            ['question' => 'Catu daya PC?', 'answers' => ['psu', 'powersupply']],
            ['question' => 'Perangkat ketik?', 'answers' => ['keyboard']],
            ['question' => 'Perangkat penunjuk?', 'answers' => ['mouse']],
            ['question' => 'Layar komputer?', 'answers' => ['monitor']],
            ['question' => 'Pencetak dokumen?', 'answers' => ['printer']],
            ['question' => 'Pemindai dokumen?', 'answers' => ['scanner']],
            ['question' => 'Port jaringan kabel?', 'answers' => ['ethernet', 'lan', 'rj45']],
            ['question' => 'Kabel jaringan umum?', 'answers' => ['utp']],
            ['question' => 'Jaringan nirkabel?', 'answers' => ['wifi', 'wi-fi']],
            ['question' => 'Alamat perangkat jaringan?', 'answers' => ['ip', 'alamatip']],
            ['question' => 'Alamat fisik jaringan?', 'answers' => ['mac', 'macaddress']],
            ['question' => 'Pembagi IP otomatis?', 'answers' => ['dhcp']],
            ['question' => 'Penerjemah nama domain?', 'answers' => ['dns']],
            ['question' => 'Gerbang jaringan?', 'answers' => ['gateway']],
            ['question' => 'Mask jaringan?', 'answers' => ['subnet', 'subnetmask']],
            ['question' => 'Pengarah paket?', 'answers' => ['router']],
            ['question' => 'Penghubung LAN?', 'answers' => ['switch']],
            ['question' => 'Penguat sinyal WiFi?', 'answers' => ['repeater']],
            ['question' => 'Titik akses WiFi?', 'answers' => ['accesspoint', 'ap']],
            ['question' => 'Jaringan lokal?', 'answers' => ['lan']],
            ['question' => 'Jaringan luas?', 'answers' => ['wan']],
            ['question' => 'Jaringan pribadi virtual?', 'answers' => ['vpn']],
            ['question' => 'Protokol web aman?', 'answers' => ['https']],
            ['question' => 'Protokol web biasa?', 'answers' => ['http']],
            ['question' => 'Protokol kirim email?', 'answers' => ['smtp']],
            ['question' => 'Protokol ambil email?', 'answers' => ['imap', 'pop3']],
            ['question' => 'Protokol transfer file?', 'answers' => ['ftp']],
            ['question' => 'Remote shell aman?', 'answers' => ['ssh']],
            ['question' => 'Remote desktop Windows?', 'answers' => ['rdp']],
            ['question' => 'Port HTTP default?', 'answers' => ['80']],
            ['question' => 'Port HTTPS default?', 'answers' => ['443']],
            ['question' => 'Port SSH default?', 'answers' => ['22']],
            ['question' => 'Port DNS default?', 'answers' => ['53']],
            ['question' => 'Port SMTP default?', 'answers' => ['25']],
            ['question' => 'Port RDP default?', 'answers' => ['3389']],
            ['question' => 'Sistem operasi bebas?', 'answers' => ['linux']],
            ['question' => 'OS dari Microsoft?', 'answers' => ['windows']],
            ['question' => 'OS komputer Apple?', 'answers' => ['macos']],
            ['question' => 'Kernel Android?', 'answers' => ['linux']],
            ['question' => 'Baris perintah Windows?', 'answers' => ['cmd', 'powershell']],
            ['question' => 'Shell umum Linux?', 'answers' => ['bash']],
            ['question' => 'Manajer paket Debian?', 'answers' => ['apt']],
            ['question' => 'Manajer paket RedHat?', 'answers' => ['yum', 'dnf']],
            ['question' => 'Kontainer populer?', 'answers' => ['docker']],
            ['question' => 'Orkestrasi kontainer?', 'answers' => ['kubernetes', 'k8s']],
            ['question' => 'Kontrol versi populer?', 'answers' => ['git']],
            ['question' => 'Tempat repo Git?', 'answers' => ['github', 'gitlab']],
            ['question' => 'Bahasa web browser?', 'answers' => ['javascript', 'js']],
            ['question' => 'Struktur halaman web?', 'answers' => ['html']],
            ['question' => 'Gaya halaman web?', 'answers' => ['css']],
            ['question' => 'Bahasa Laravel?', 'answers' => ['php']],
            ['question' => 'Database relasional?', 'answers' => ['sql']],
            ['question' => 'Database ringan file?', 'answers' => ['sqlite']],
            ['question' => 'Database populer web?', 'answers' => ['mysql', 'mariadb']],
            ['question' => 'Database objek dokumen?', 'answers' => ['mongodb']],
            ['question' => 'Kunci utama tabel?', 'answers' => ['primarykey', 'pk']],
            ['question' => 'Kunci relasi tabel?', 'answers' => ['foreignkey', 'fk']],
            ['question' => 'Perintah ambil SQL?', 'answers' => ['select']],
            ['question' => 'Perintah tambah SQL?', 'answers' => ['insert']],
            ['question' => 'Perintah ubah SQL?', 'answers' => ['update']],
            ['question' => 'Perintah hapus SQL?', 'answers' => ['delete']],
            ['question' => 'Cadangan data?', 'answers' => ['backup']],
            ['question' => 'Pulihkan data?', 'answers' => ['restore']],
            ['question' => 'Catatan aktivitas?', 'answers' => ['log']],
            ['question' => 'Jejak audit?', 'answers' => ['audittrail']],
            ['question' => 'Kata sandi?', 'answers' => ['password', 'sandi']],
            ['question' => 'Kode sekali pakai?', 'answers' => ['otp']],
            ['question' => 'Login dua faktor?', 'answers' => ['2fa', 'mfa']],
            ['question' => 'Enkripsi data?', 'answers' => ['enkripsi', 'encryption']],
            ['question' => 'Ubah sandi jadi kode?', 'answers' => ['hash', 'hashing']],
            ['question' => 'Garam pada hash?', 'answers' => ['salt']],
            ['question' => 'Token sesi?', 'answers' => ['session', 'sesi']],
            ['question' => 'Token akses API?', 'answers' => ['token']],
            ['question' => 'Serangan tebak sandi?', 'answers' => ['bruteforce']],
            ['question' => 'Email penipuan?', 'answers' => ['phishing']],
            ['question' => 'Malware tebusan?', 'answers' => ['ransomware']],
            ['question' => 'Perangkat lunak jahat?', 'answers' => ['malware']],
            ['question' => 'Program memata-matai?', 'answers' => ['spyware']],
            ['question' => 'Iklan mengganggu?', 'answers' => ['adware']],
            ['question' => 'Virus menyamar?', 'answers' => ['trojan']],
            ['question' => 'Jaringan bot?', 'answers' => ['botnet']],
            ['question' => 'Serangan banjir trafik?', 'answers' => ['ddos', 'dos']],
            ['question' => 'Sisip SQL berbahaya?', 'answers' => ['sqli', 'sqlinjection']],
            ['question' => 'Skrip lintas situs?', 'answers' => ['xss']],
            ['question' => 'Pemalsuan request?', 'answers' => ['csrf']],
            ['question' => 'Pemalsuan identitas?', 'answers' => ['spoofing']],
            ['question' => 'Penyadapan jaringan?', 'answers' => ['sniffing']],
            ['question' => 'Orang di tengah?', 'answers' => ['mitm']],
            ['question' => 'Dinding pengaman jaringan?', 'answers' => ['firewall']],
            ['question' => 'Deteksi intrusi?', 'answers' => ['ids']],
            ['question' => 'Cegah intrusi?', 'answers' => ['ips']],
            ['question' => 'Antivirus bawaan Windows?', 'answers' => ['defender']],
            ['question' => 'Perbaikan celah aplikasi?', 'answers' => ['patch']],
            ['question' => 'Celah keamanan?', 'answers' => ['vulnerability', 'celah']],
            ['question' => 'Eksploit celah?', 'answers' => ['exploit']],
            ['question' => 'Izin akses?', 'answers' => ['permission', 'izin']],
            ['question' => 'Hak admin tertinggi?', 'answers' => ['root', 'administrator']],
            ['question' => 'Prinsip akses minimum?', 'answers' => ['leastprivilege']],
            ['question' => 'Daftar boleh akses?', 'answers' => ['whitelist', 'allowlist']],
            ['question' => 'Daftar blokir?', 'answers' => ['blacklist', 'blocklist']],
            ['question' => 'Mode rahasia browser?', 'answers' => ['incognito', 'private']],
            ['question' => 'Cookie web disimpan?', 'answers' => ['browser']],
            ['question' => 'Cache mempercepat?', 'answers' => ['akses', 'loading']],
            ['question' => 'Sertifikat web aman?', 'answers' => ['ssl', 'tls']],
            ['question' => 'TLS penerus dari?', 'answers' => ['ssl']],
            ['question' => 'Kunci publik?', 'answers' => ['publickey']],
            ['question' => 'Kunci privat?', 'answers' => ['privatekey']],
            ['question' => 'Tanda tangan digital?', 'answers' => ['signature']],
            ['question' => 'Otoritas sertifikat?', 'answers' => ['ca']],
            ['question' => 'Alamat website?', 'answers' => ['url']],
            ['question' => 'Nama website?', 'answers' => ['domain']],
            ['question' => 'Subdomain contoh www?', 'answers' => ['www']],
            ['question' => 'Format data ringan?', 'answers' => ['json']],
            ['question' => 'Format markup data?', 'answers' => ['xml']],
            ['question' => 'API web umum?', 'answers' => ['rest']],
            ['question' => 'Metode ambil HTTP?', 'answers' => ['get']],
            ['question' => 'Metode kirim HTTP?', 'answers' => ['post']],
            ['question' => 'Metode ubah HTTP?', 'answers' => ['put', 'patch']],
            ['question' => 'Metode hapus HTTP?', 'answers' => ['delete']],
            ['question' => 'Kode HTTP sukses?', 'answers' => ['200']],
            ['question' => 'Kode tidak ditemukan?', 'answers' => ['404']],
            ['question' => 'Kode server error?', 'answers' => ['500']],
            ['question' => 'Kode tidak berizin?', 'answers' => ['401']],
            ['question' => 'Kode terlarang?', 'answers' => ['403']],
            ['question' => 'File konfigurasi env?', 'answers' => ['env', '.env']],
            ['question' => 'Mode debug produksi?', 'answers' => ['false', 'mati', 'off']],
            ['question' => 'Folder publik Laravel?', 'answers' => ['public']],
            ['question' => 'CLI Laravel?', 'answers' => ['artisan']],
            ['question' => 'Templat Laravel?', 'answers' => ['blade']],
            ['question' => 'ORM Laravel?', 'answers' => ['eloquent']],
            ['question' => 'Migrasi database?', 'answers' => ['migration', 'migrasi']],
            ['question' => 'Pengisi data awal?', 'answers' => ['seeder']],
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
