<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Last Solution's real products, taken from the shop's own advertisements.
 * Prices are the advertised prices (null = not advertised, shown as "Ask for price").
 * Stock is left at 0 so customers enquire on WhatsApp until stock is entered in /admin.
 * Safe to re-run: products are matched on name and only catalogue fields are updated.
 */
class ProductCatalogSeeder extends Seeder
{
    private const PACKAGE_NOTE = "Professional CCTV installation and reliable support from Last Solution. Watch your cameras live on your phone from anywhere.";

    public function run(): void
    {
        $cat = Category::pluck('id', 'slug');

        foreach ($this->products() as $p) {
            $description = $p['description']."\n\n".collect($p['features'])->map(fn ($f) => '• '.$f)->implode("\n");
            if ($p['category'] === 'cctv-packages') {
                $description .= "\n\n".self::PACKAGE_NOTE;
            }

            Product::updateOrCreate(['name' => $p['name']], [
                'category_id' => $cat[$p['category']] ?? null,
                'brand' => $p['brand'],
                'oem_part_number' => $p['model'],
                'tag' => $p['tag'],
                'summary' => $p['summary'],
                'specification' => $p['spec'],
                'description' => $description,
                'price' => $p['price'],
                'warranty' => '3 years',
                'image' => 'assets/products/'.$p['image'].'.jpg',
                'is_featured' => $p['featured'] ?? false,
                'is_active' => true,
                'meta_description' => $p['summary'],
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function products(): array
    {
        $pkg = fn (int $cams, string $dvr, int $bnc, string $psu = 'Hitech power supplies') => [
            "{$cams} × 2MP cameras", "1 × {$dvr} DVR", '1 × 500GB WD Purple hard disk', "{$cams} × {$psu}",
            '1 × 100m 3C2V CCTV cable roll', "{$bnc} × BNC connectors", '1 × DVR cabinet / locker',
        ];

        return [
            // ---- CCTV packages -------------------------------------------------
            [
                'name' => 'Hikvision IR Night Vision 4CH CCTV Package (2MP)', 'image' => 'hikvision-4ch-ir-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-EXIPF', 'price' => 42500,
                'tag' => '4 CAMERAS', 'featured' => true,
                'summary' => 'Complete 4-camera Full HD system with IR night vision, recorder, hard disk and all accessories.',
                'spec' => '4 × 2MP (1920×1080) IR cameras, 4CH DVR, 500GB HDD, IP66 weatherproof',
                'description' => 'An affordable, complete CCTV system for homes and small shops. Four Hikvision DS-2CE16D0T-EXIPF 2MP bullet cameras give clear Full HD video by day and IR night vision after dark.',
                'features' => [...$pkg(4, 'Hikvision 4CH', 8, 'Hitech HT-212HQ (DC12V 2A) power supplies'), 'IR night vision, day & night mode', 'Waterproof cameras'],
            ],
            [
                'name' => 'Dahua Smart Full Colour + Audio 4CH CCTV Package (2MP)', 'image' => 'dahua-4ch-full-color-package',
                'category' => 'cctv-packages', 'brand' => 'Dahua', 'model' => 'HAC-B1A21-U-IL-A', 'price' => 43500,
                'tag' => 'COLOUR + AUDIO', 'featured' => true,
                'summary' => 'Four Dahua 2MP cameras with full-colour day & night video and built-in audio recording.',
                'spec' => '4 × 2MP 1080p Dahua cameras, 4CH DVR, 500GB WD Purple, audio recording',
                'description' => 'Four Dahua HAC-B1A21-U-IL-A smart dual-light cameras record full-colour video day and night, with a built-in microphone so you hear as well as see what happens.',
                'features' => [...$pkg(4, '4CH', 8, 'Hitech DC12V 2A power supplies'), 'Full colour day & night', 'Audio recording'],
            ],
            [
                'name' => 'Hikvision IR Night Vision 5CH CCTV Package (2MP)', 'image' => 'hikvision-5ch-ir-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-EXIPF', 'price' => 51500,
                'tag' => '5 CAMERAS',
                'summary' => 'Five Full HD IR cameras on an 8-channel DVR, with room to add more cameras later.',
                'spec' => '5 × 2MP IR cameras, 8CH DVR, 500GB WD Purple, IP66',
                'description' => 'Five Hikvision DS-2CE16D0T-EXIPF 2MP cameras with IR night vision, recorded on an 8-channel DVR so three more cameras can be added later.',
                'features' => [...$pkg(5, '8CH', 10, 'Hitech HT-212HQ (DC12V 2A) power supplies'), 'IR night vision', 'IP66 weatherproof'],
            ],
            [
                'name' => 'Hikvision Smart Full Colour + Audio 4CH CCTV Package (2MP)', 'image' => 'hikvision-4ch-full-color-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-LPFS', 'price' => 52500,
                'tag' => 'COLOUR + AUDIO', 'featured' => true,
                'summary' => 'Four Hikvision cameras with smart full-colour night vision and built-in audio recording.',
                'spec' => '4 × 2MP DS-2CE16D0T-LPFS cameras, Hikvision 4CH DVR, 500GB WD Purple',
                'description' => 'Four Hikvision DS-2CE16D0T-LPFS smart hybrid light cameras: colour video at night and a built-in microphone for audio recording.',
                'features' => [...$pkg(4, 'Hikvision 4CH', 8, 'Hitech DC12V 2A power supplies'), 'Smart full colour night vision', 'Audio recording (built-in mic)', 'Weatherproof'],
            ],
            [
                'name' => 'Hikvision Smart Full Colour + Audio 5CH CCTV Package (2MP)', 'image' => 'hikvision-5ch-full-color-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-LPFS', 'price' => 61500,
                'tag' => 'COLOUR + AUDIO',
                'summary' => 'Five full-colour audio cameras on an 8-channel DVR.',
                'spec' => '5 × 2MP LPFS audio cameras, Hikvision 8CH DVR, 500GB WD Purple, IP67',
                'description' => 'Five Hikvision DS-2CE16D0T-LPFS audio bullet cameras with smart hybrid light for full-colour night video, on an 8-channel DVR.',
                'features' => [...$pkg(5, 'Hikvision 8CH', 10), 'Full colour night vision', 'Audio recording', 'Smart hybrid light', 'IP67 weatherproof'],
            ],
            [
                'name' => 'Hikvision IR Night Vision 7CH CCTV Package (2MP)', 'image' => 'hikvision-7ch-ir-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-EXIPF', 'price' => 63500,
                'tag' => '7 CAMERAS',
                'summary' => 'Seven Full HD IR cameras for larger homes, shops and offices.',
                'spec' => '7 × 2MP IR cameras, Hikvision 8CH DVR, 500GB WD Purple',
                'description' => 'Seven Hikvision DS-2CE16D0T-EXIPF 2MP cameras with IR night vision and live mobile view, on an 8-channel Hikvision DVR.',
                'features' => [...$pkg(7, 'Hikvision 8CH', 14, 'Hitech HT-212HQ power supplies'), 'BNC connectors & DC pins', 'IR night vision', 'Waterproof', 'Live mobile view'],
            ],
            [
                'name' => 'Hikvision Smart Full Colour + Audio 6CH Premium Package (2MP)', 'image' => 'hikvision-6ch-full-color-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-LPFS', 'price' => 68500,
                'tag' => 'PREMIUM',
                'summary' => 'Six full-colour cameras with audio recording on an 8-channel Hikvision DVR.',
                'spec' => '6 × 2MP LPFS audio cameras, Hikvision 8CH DVR, 500GB WD Purple, IP67',
                'description' => 'Six Hikvision DS-2CE16D0T-LPFS cameras with smart hybrid light: full-colour night video and audio recording.',
                'features' => [...$pkg(6, 'Hikvision 8CH', 12), 'Full colour night vision', 'Audio recording', 'Smart hybrid light', 'IP67 waterproof'],
            ],
            [
                'name' => 'Hikvision IR Night Vision 8-Camera CCTV Kit (2MP)', 'image' => 'hikvision-8-camera-ir-kit',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-EXIPF', 'price' => 69500,
                'tag' => 'SPECIAL OFFER', 'featured' => true,
                'summary' => 'Eight Full HD IR cameras for full coverage, at a special package price.',
                'spec' => '8 × 2MP IR cameras, 8CH DVR, 500GB HDD, IP66',
                'description' => 'Eight Hikvision DS-2CE16D0T-EXIPF 2MP cameras with smart IR night vision and 24/7 recording on an 8-channel DVR.',
                'features' => [...$pkg(8, '8-channel', 16, 'Hitech HT-212HQ power supplies'), 'Smart IR night vision', 'IP66 weatherproof', '24/7 recording'],
            ],
            [
                'name' => 'Hikvision Smart Full Colour + Audio 7CH CCTV Package (2MP)', 'image' => 'hikvision-7ch-full-color-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => null, 'price' => 75500,
                'tag' => 'COLOUR + AUDIO',
                'summary' => 'Seven full-colour audio cameras on an 8-channel DVR.',
                'spec' => '7 × 2MP audio cameras, 8CH DVR, 500GB WD Purple, IP67',
                'description' => 'Seven Hikvision 2MP audio cameras with smart hybrid light for full-colour night video, recorded on an 8-channel DVR.',
                'features' => [...$pkg(7, '8CH', 14), 'Full colour night vision', 'Audio recording', 'Smart hybrid light', 'IP67 weatherproof'],
            ],
            [
                'name' => 'Hikvision Smart Full Colour + Audio 8CH CCTV Package (2MP)', 'image' => 'hikvision-8ch-full-color-package',
                'category' => 'cctv-packages', 'brand' => 'Hikvision', 'model' => 'DS-2CE16D0T-LPFS', 'price' => 82500,
                'tag' => '8 CAMERAS',
                'summary' => 'Eight full-colour audio cameras for complete coverage of larger properties.',
                'spec' => '8 × 2MP LPFS audio cameras, 8CH DVR, 500GB WD Purple',
                'description' => 'Eight Hikvision DS-2CE16D0T-LPFS cameras with full-colour night vision and built-in microphones, on an 8-channel DVR.',
                'features' => [...$pkg(8, '8CH', 16, 'Hitech HT-212HQ power supplies'), 'Full colour night vision', 'Audio recording (built-in mic)', 'Smart hybrid light'],
            ],

            // ---- Smart door locks ----------------------------------------------
            [
                'name' => 'EZVIZ DL50FVS Smart Lock with Video Doorbell', 'image' => 'ezviz-dl50fvs',
                'category' => 'smart-door-locks', 'brand' => 'EZVIZ', 'model' => 'DL50FVS', 'price' => null,
                'tag' => 'FACE ID + VIDEO', 'featured' => true,
                'summary' => 'Face recognition smart lock with a video doorbell and 4" indoor screen.',
                'spec' => '3D face, fingerprint, passcode, app & key; 4" IPS display; Wi-Fi + Bluetooth',
                'description' => 'See who is there and unlock with confidence. Unlock by 3D face recognition, fingerprint, passcode, Bluetooth key, the EZVIZ app or a mechanical key, and answer visitors with a two-way video call.',
                'features' => [
                    '3D face recognition with dual-lens depth sensing', '4" IPS colour display (480 × 800)', 'Video doorbell and two-way video call',
                    'Up to 50 users (50 face IDs / 50 fingerprints / 50 passcodes)', '2.4GHz Wi-Fi + Bluetooth, remote unlock via EZVIZ app',
                    '5000mAh rechargeable battery, up to 4 months per charge, Type-C emergency power', 'IR night vision (2m), 8GB eMMC storage + EZVIZ Cloud',
                    'Anti-tamper alarm, anti-peeping code, wandering detection, voice changer', 'C-level security cylinder, IP54',
                    'Door thickness 40–120mm, working temperature -10°C to 55°C', 'Aluminium alloy, metallic grey',
                ],
            ],
            [
                'name' => 'EZVIZ DL05 Smart Fingerprint Lock', 'image' => 'ezviz-dl05',
                'category' => 'smart-door-locks', 'brand' => 'EZVIZ', 'model' => 'DL05', 'price' => null,
                'tag' => 'FINGERPRINT + APP',
                'summary' => 'Wi-Fi fingerprint lock with app control and up to 1 year battery life.',
                'spec' => 'Fingerprint, passcode, card, Bluetooth & key; Wi-Fi; 8 × AA batteries',
                'description' => 'Open your door with a fingerprint, passcode, proximity card, Bluetooth key or mechanical key, and unlock remotely from the EZVIZ app.',
                'features' => [
                    'Up to 50 users: 50 fingerprints, 50 passcodes, 50 cards', '2.4GHz Wi-Fi + Bluetooth, remote unlocking', 'Real-time mobile notifications',
                    'Built-in doorbell, privacy mode, child safety lock', 'Anti-peeping code (up to 20 digits), anti-tamper alarm',
                    '8 × AA batteries, up to 1 year battery life, low-battery warning, Type-C emergency charging',
                    'IP65 front panel', 'Door thickness 30–110mm, working temperature -25°C to 70°C',
                ],
            ],
            [
                'name' => 'Hikvision DS-K4SL500MKW Pro Smart Lock', 'image' => 'hikvision-ds-k4sl500mkw',
                'category' => 'smart-door-locks', 'brand' => 'Hikvision', 'model' => 'DS-K4SL500MKW', 'price' => null,
                'tag' => 'WI-FI + CARD',
                'summary' => 'Wi-Fi smart door lock with card access, voice navigation and 100 users.',
                'spec' => 'Card + mechanical key, 2.4G Wi-Fi, 100 users / 100 cards, 8 × AA',
                'description' => 'Smart security, easy access, modern living. An aluminium alloy smart lock with Wi-Fi, card access and voice navigation.',
                'features' => [
                    '2.4G Wi-Fi', '100 users and 100 cards (up to 5 cards per user)', 'Card reaction time under 1 second',
                    'Voice navigation', 'Anti-pry alarm and low-voltage alarm', '8 × AA batteries, Micro USB emergency power',
                    'Stores 1,000 door-opening records after power loss', 'Aluminium alloy panel 355 × 70 × 25mm, black',
                    'C-level cylinder, lift-up lock body, left/right opening', 'Working temperature -25°C to 70°C',
                ],
            ],

            // ---- Intercom --------------------------------------------------------
            [
                'name' => 'Hikvision DS-KIS602-B Smart Video Doorphone Kit', 'image' => 'hikvision-ds-kis602-b',
                'category' => 'intercom-systems', 'brand' => 'Hikvision', 'model' => 'DS-KIS602-B', 'price' => null,
                'tag' => 'IP INTERCOM KIT',
                'summary' => 'IP video intercom kit with door station, 7" touch screen and PoE switch.',
                'spec' => '2MP door station, 7" touch screen (1024 × 600), 4-port PoE switch, 16GB card',
                'description' => 'See and talk to visitors, then unlock the door from the indoor screen or remotely with the Hik-Connect app.',
                'features' => [
                    '2MP HD video intercom', '7" touch screen 1024 × 600', 'IR 3m supplement light',
                    'Two-way audio with noise suppression and echo cancellation', 'IP65 door station', 'Wi-Fi + PoE (802.11 b/g/n)',
                    'Hik-Connect remote unlock and live view', '16GB TF card included', '4-port PoE switch included', 'Long-range PoE up to 300m',
                ],
            ],
            [
                'name' => 'Hikvision DS-KIS603-P IP Video Intercom Villa Kit', 'image' => 'hikvision-ds-kis603-p',
                'category' => 'intercom-systems', 'brand' => 'Hikvision', 'model' => 'DS-KIS603-P', 'price' => null,
                'tag' => 'VILLA KIT', 'featured' => true,
                'summary' => 'Full HD door station and 7" indoor touch monitor with card access and remote unlock.',
                'spec' => 'DS-KD8003-IME1 door station + DS-KH6320-WTE1 7" indoor station',
                'description' => 'Secure your home, stay connected. A one-family villa kit with a 2MP door station and a 7-inch indoor touch monitor; unlock and view live from the Hik-Connect app.',
                'features' => [
                    'Door station: 2MP (1920 × 1080), 131° × 78° view, IR up to 3m, true WDR', 'Mifare card access (13.56 MHz)',
                    'Two-way audio with noise suppression & echo cancellation', 'Tamper-proof, IP65 weatherproof door station',
                    'Indoor station: 7" touch screen 1024 × 600, Wi-Fi + PoE, microSD for pictures & messages',
                    'Remote unlock and live view via Hik-Connect', 'Door station 138 × 65 × 28mm, indoor station 194 × 121 × 18mm',
                ],
            ],

            // ---- Network & storage ---------------------------------------------
            [
                'name' => 'Hikvision DS-3WR4G3N 4G LTE Wi-Fi Router', 'image' => 'hikvision-ds-3wr4g3n',
                'category' => 'network-wi-fi', 'brand' => 'Hikvision', 'model' => 'DS-3WR4G3N', 'price' => null,
                'tag' => '4G LTE',
                'summary' => 'Indoor 4G SIM router: fast, stable, secure internet anywhere.',
                'spec' => '4G LTE (FDD/TDD), 300Mbps Wi-Fi, 4 × LAN, Micro SIM, 2 antennas',
                'description' => 'Insert a SIM and get Wi-Fi anywhere there is mobile coverage. Ideal for homes, shops, offices, sites and remote CCTV viewing.',
                'features' => ['4G LTE high-speed internet (FDD/TDD)', '300Mbps wireless (2.4GHz)', '4 × 100Mbps LAN ports', 'Micro SIM card slot, plug & play', '2 × external antennas', 'Easy setup via web interface'],
            ],
            [
                'name' => 'Hikvision DS-3WRE12C AC1200 Dual-Band Wi-Fi Extender', 'image' => 'hikvision-ds-3wre12c',
                'category' => 'network-wi-fi', 'brand' => 'Hikvision', 'model' => 'DS-3WRE12C', 'price' => null,
                'tag' => 'EXTEND WI-FI',
                'summary' => 'Extend your Wi-Fi coverage with up to 1167Mbps dual-band speed.',
                'spec' => 'AC1200, 2.4GHz 300Mbps + 5GHz 867Mbps, 2×2 MIMO, 1 × LAN',
                'description' => 'Remove Wi-Fi dead zones. Works as a repeater or as an access point.',
                'features' => ['802.11ac Wave 2', '2 × 3dBi dual-band antennas', '2×2 MIMO', 'Repeater mode + access point mode', '1 × 10/100Mbps RJ45 port', 'WPS button and signal LED', 'WPA/WPA2 security', 'Working temperature 0°C to 40°C'],
            ],
            [
                'name' => 'Hikvision 2TB Surveillance Hard Drive', 'image' => 'hikvision-2tb-hdd',
                'category' => 'surveillance-hard-drives', 'brand' => 'Hikvision', 'model' => 'DS20HKVS-VX1', 'price' => null,
                'tag' => '24/7 RECORDING',
                'summary' => '3.5" SATA surveillance hard drive for DVR / NVR CCTV storage.',
                'spec' => '2TB, 3.5" SATA 6Gb/s, 5400 RPM, 128MB cache',
                'description' => 'Built for 24/7 CCTV recording, supporting up to 32 high-definition camera streams.',
                'features' => ['2TB formatted capacity', '3.5" form factor, SATA 6.0 / 3.0 / 1.5 Gb/s', '5400 RPM, 128MB cache', '176.4 MB/s max sustained transfer (typical)', '180 TB/year workload', '1,000,000 hours MTTF', 'Up to 32 HD camera streams'],
            ],
        ];
    }
}
