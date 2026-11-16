<?php

$repairServices = [
    // ---- Security & surveillance ----
    [
        'slug' => 'cctv-installation',
        'image' => 'cctv-bullet-cameras',
        'group' => 'security',
        'name' => 'CCTV installation',
        'icon' => 'camera',
        'summary' => 'Security camera systems planned and installed for homes, shops and workplaces.',
        'description' => 'Tell us about your property and the areas you want to monitor. We plan camera positions, recording and mobile viewing, then install a system suited to your site using brands such as Hikvision, HiLook, Dahua, EZVIZ, Imou and UNV.',
        'signs' => ['Moving into a new home or workplace', 'Want to monitor an entrance or boundary', 'Need to check a shop or business after hours', 'Looking for a camera system with night vision'],
        'includes' => ['Site and camera-position discussion', 'Camera and recorder setup', 'System configuration', 'Mobile viewing setup and handover'],
        'turnaround' => 'Scheduled after discussing your site',
    ],
    [
        'slug' => 'cctv-repair-service',
        'image' => 'cctv-dome-outdoor',
        'group' => 'security',
        'name' => 'CCTV repair & service',
        'icon' => 'wrench',
        'summary' => 'Help with camera, recording and viewing issues, plus CCTV system servicing.',
        'description' => 'If a camera is offline, footage is unclear or remote viewing has stopped working, contact us with a description of the system and the issue. We will discuss the next steps and arrange service.',
        'signs' => ['A camera is offline or showing no picture', 'Night-time footage is hard to see', 'The recorder is not saving footage', 'Remote viewing no longer connects'],
        'includes' => ['Camera and connection checks', 'Recorder and storage checks', 'Viewing and system configuration', 'Repair or service recommendations'],
        'turnaround' => 'Scheduled after discussing the issue',
    ],
    [
        'slug' => 'alarm-systems',
        'image' => 'cctv-dome-hikvision',
        'group' => 'security',
        'name' => 'Alarm systems',
        'icon' => 'bell',
        'summary' => 'Intruder alarm systems with sensors, sirens and phone alerts.',
        'description' => 'Protect doors, windows and key areas with an alarm system that sounds a siren and can alert your phone. We help you choose sensor positions and set the system up so it is easy to arm and disarm.',
        'signs' => ['You want to know if someone enters while you are away', 'You need protection for doors and windows', 'You want alerts on your phone', 'Your existing alarm is not working properly'],
        'includes' => ['Sensor and siren planning', 'Door, window and motion sensors', 'Control panel and app setup', 'Arming and disarming walkthrough'],
        'turnaround' => 'Scheduled after discussing your property',
    ],
    [
        'slug' => 'smart-door-locks',
        'image' => 'smart-lock',
        'group' => 'security',
        'name' => 'Smart door locks',
        'icon' => 'lock',
        'summary' => 'Keyless door locks with fingerprint, PIN code, card or app access.',
        'description' => 'Replace keys with a smart lock that opens with a fingerprint, PIN code, card or phone. We check your door and fit a lock that suits it, then set up access for your family or staff.',
        'signs' => ['You are tired of lost or copied keys', 'You want to give staff or family their own access', 'You want to know when the door is opened', 'You are fitting out a new home or office'],
        'includes' => ['Door suitability check', 'Lock supply and fitting', 'Fingerprint, PIN and card setup', 'App setup where supported'],
        'turnaround' => 'Scheduled after checking your door',
    ],
    [
        'slug' => 'intercom-systems',
        'image' => 'intercom',
        'group' => 'security',
        'name' => 'Intercom systems',
        'icon' => 'intercom',
        'summary' => 'Audio and video intercoms to see and talk to visitors at the gate or door.',
        'description' => 'See and speak to visitors before you open the gate or door. We install video and audio intercoms for houses, apartments and offices, and can connect gate or door release where suitable.',
        'signs' => ['You want to see who is at the gate', 'You need to talk to visitors without going outside', 'You manage an apartment or office entrance', 'You want to answer the door from your phone'],
        'includes' => ['Door station and indoor monitor setup', 'Cabling or wireless options', 'Gate or door release where suitable', 'Phone app setup where supported'],
        'turnaround' => 'Scheduled after discussing your entrance',
    ],
    // ---- Smart home & networking ----
    [
        'slug' => 'networking',
        'image' => 'network-switch',
        'group' => 'smart',
        'name' => 'Network installation',
        'icon' => 'network',
        'summary' => 'Structured cabling, switches and routers for homes, shops and offices.',
        'description' => 'A reliable network keeps your cameras, computers and devices connected. We plan and install cabling, network switches and routers, and tidy up existing networks.',
        'signs' => ['Your devices keep dropping off the network', 'You are setting up a new office or shop', 'You need cables run to cameras or computers', 'Your network cabinet is untidy or unlabelled'],
        'includes' => ['Network planning', 'Cabling and termination', 'Switch and router setup', 'Testing and labelling'],
        'turnaround' => 'Scheduled after discussing your site',
    ],
    [
        'slug' => 'wifi-solutions',
        'image' => 'network-ports',
        'group' => 'smart',
        'name' => 'Wi-Fi solutions',
        'icon' => 'wifi',
        'summary' => 'Strong, reliable Wi-Fi coverage for every room, floor and outdoor area.',
        'description' => 'Fix weak spots and slow Wi-Fi with the right routers, access points or mesh systems, including TP-Link solutions. We check your property and set up coverage where you need it.',
        'signs' => ['Wi-Fi is weak in some rooms or floors', 'Video calls and streaming keep buffering', 'You need Wi-Fi outdoors or in a second building', 'Too many devices are slowing your network'],
        'includes' => ['Coverage check', 'Router, access point or mesh setup', 'Secure Wi-Fi configuration', 'Guest network setup'],
        'turnaround' => 'Scheduled after discussing your property',
    ],
    [
        'slug' => 'smart-home',
        'image' => 'smart-home',
        'group' => 'smart',
        'name' => 'Smart home devices',
        'icon' => 'smarthome',
        'summary' => 'Smart cameras, plugs, switches and sensors you can control from your phone.',
        'description' => 'Make your home easier to manage with smart devices that you control from one app. We help you choose devices that work together and set them up on your Wi-Fi.',
        'signs' => ['You want to control lights or devices from your phone', 'You want a smart indoor camera or doorbell', 'You want reminders and alerts at home', 'You have smart devices that are not set up yet'],
        'includes' => ['Device recommendations', 'Installation and Wi-Fi setup', 'App setup and automations', 'Handover and walkthrough'],
        'turnaround' => 'Scheduled after discussing your needs',
    ],
    // ---- Appliance repair & A/C ----
    [
        'slug' => 'air-conditioning',
        'image' => 'ac-repair',
        'group' => 'appliance',
        'name' => 'A/C installation & service',
        'icon' => 'snow',
        'summary' => 'Air-conditioning installation, repair and service for homes and businesses.',
        'description' => 'We install, repair and service air-conditioning units. Contact us with your location and a short description of the work you need.',
        'signs' => ['Planning an A/C installation', 'The unit is not cooling as expected', 'The system needs servicing', 'The unit is leaking water or making noise'],
        'includes' => ['Installation', 'Repair and fault finding', 'Cleaning and servicing', 'Work discussed before scheduling'],
        'turnaround' => 'Scheduled after discussing your requirements',
    ],
    [
        'slug' => 'refrigerator-repair',
        'image' => 'ac-repair',
        'group' => 'appliance',
        'name' => 'Refrigerator repair',
        'icon' => 'fridge',
        'summary' => 'Repairs for fridges and freezers that are not cooling or are making noise.',
        'description' => 'If your refrigerator or freezer is not cooling, is leaking or is making unusual noises, tell us the brand, model and what is happening. We will discuss the problem and arrange a repair.',
        'signs' => ['The fridge is not cooling properly', 'Ice is building up in the freezer', 'Water is leaking inside or under the fridge', 'The fridge is noisy or keeps switching on and off'],
        'includes' => ['Fault finding', 'Gas and cooling checks', 'Part replacement where needed', 'Testing after repair'],
        'turnaround' => 'Scheduled after discussing the fault',
    ],
    [
        'slug' => 'washing-machine-repair',
        'image' => 'laundry',
        'group' => 'appliance',
        'name' => 'Washing machine repair',
        'icon' => 'washer',
        'summary' => 'Repairs for washing machines that will not spin, drain or start.',
        'description' => 'Tell us the brand and model of your washing machine and what it is doing, for example not spinning, not draining or showing an error. We will discuss the problem and arrange a repair.',
        'signs' => ['The machine will not start or spin', 'Water is not draining', 'It is leaking or very noisy', 'It shows an error code'],
        'includes' => ['Fault finding', 'Drain, motor and drum checks', 'Part replacement where needed', 'Testing after repair'],
        'turnaround' => 'Scheduled after discussing the fault',
    ],
    // ---- Solar & power backup ----
    [
        'slug' => 'solar-services',
        'image' => 'solar-roof',
        'group' => 'power',
        'name' => 'Solar power systems',
        'icon' => 'sun',
        'summary' => 'Solar power systems to cut electricity bills for homes and businesses.',
        'description' => 'Generate your own electricity with a solar power system. Tell us about your property, roof and monthly electricity use, and we will discuss a system that suits your needs.',
        'signs' => ['Your electricity bills are high', 'You are building or renovating', 'You want backup power during outages', 'You have a question about an existing solar system'],
        'includes' => ['Requirements and roof discussion', 'System sizing', 'Panel and inverter installation', 'Handover and monitoring setup'],
        'turnaround' => 'Scheduled after discussing your requirements',
    ],
    [
        'slug' => 'battery-backup',
        'image' => 'solar-install',
        'group' => 'power',
        'name' => 'Battery backup systems',
        'icon' => 'battery',
        'summary' => 'Inverter and battery backup to keep lights, Wi-Fi and CCTV running in power cuts.',
        'description' => 'Keep important equipment running during power cuts. We size and install battery backup and UPS systems for homes, shops and CCTV systems, so cameras keep recording when the power goes off.',
        'signs' => ['Power cuts switch off your CCTV or Wi-Fi', 'You work from home and need power for computers', 'Your shop needs lights and the till during outages', 'Your existing backup batteries do not last'],
        'includes' => ['Load and backup-time discussion', 'Inverter or UPS selection', 'Battery installation', 'Testing and handover'],
        'turnaround' => 'Scheduled after discussing your requirements',
    ],
];

return [
    'name' => env('APP_NAME', 'Last Solution (Pvt) Ltd'),
    'legal_name' => 'Last Solution (Pvt) Ltd',
    'reg_no' => '',
    'incorporated' => '',
    'phone' => env('SHOP_PHONE', '+94767223260'),
    'phone_display' => env('SHOP_PHONE_DISPLAY', '076 722 3260'),
    'other_phones' => [],
    'whatsapp' => env('SHOP_WHATSAPP', '94767223260'),
    'email' => env('SHOP_EMAIL', ''),
    'address' => env('SHOP_ADDRESS', 'No: 135/8 Ebulpuraya, Nugawela, Kandy, Sri Lanka'),
    'maps_url' => env('SHOP_MAPS_URL', 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('No: 135/8 Ebulpuraya, Nugawela, Kandy')),
    'reviews_url' => '',
    'facebook_url' => env('SHOP_FACEBOOK_URL', 'https://www.facebook.com/LASTSOLUTION1/'),
    'tiktok_url' => env('SHOP_TIKTOK_URL', 'https://www.tiktok.com/@last_solution_pvt_ltd'),
    'maps_embed' => env('SHOP_MAPS_EMBED', 'https://maps.google.com/maps?q='.rawurlencode('No: 135/8 Ebulpuraya, Nugawela, Kandy, Sri Lanka').'&z=16&output=embed'),

    // Google reviews section on the home page (see app/Services/GoogleReviews.php).
    // Without an API key the section links to the Google listing instead of showing reviews.
    'google_reviews' => [
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),
        'search' => 'LAST SOLUTION (PVT) LTD cctv installation kandy',
        'listing_url' => env('GOOGLE_REVIEWS_URL', 'https://www.google.com/maps?cid=12639943915634735336'),
    ],

    // Orders
    'currency' => env('SHOP_CURRENCY', 'Rs.'),
    'delivery_fee' => (float) env('SHOP_DELIVERY_FEE', 450),
    'free_delivery_over' => (float) env('SHOP_FREE_DELIVERY_OVER', 25000),
    'max_line_quantity' => 10,
    'bank_details' => env('SHOP_BANK_DETAILS', 'Bank details will be shared by our team when we confirm your order.'),

    // Where order / enquiry notification e-mails are sent. Leave empty to disable.
    'notify_email' => env('SHOP_NOTIFY_EMAIL'),

    // First admin account (used by AdminUserSeeder).
    'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
    'admin_password' => env('ADMIN_PASSWORD'),

    // One-time browser installer at /install/{token}. Set a long random value to
    // enable it on a host without terminal access, then REMOVE it after installing.
    'install_token' => env('INSTALL_TOKEN'),

    // Service families, in display order: key => [title, icon, short description, photo in public/assets/hero].
    'service_groups' => [
        'security' => ['Security & surveillance', 'shield', 'CCTV, alarms, smart locks and intercoms', 'cctv-bullet-cameras'],
        'smart' => ['Smart home & networking', 'wifi', 'Networks, Wi-Fi and smart devices', 'smart-home'],
        'appliance' => ['Appliance repair & A/C', 'snow', 'A/C, refrigerators and washing machines', 'ac-repair'],
        'power' => ['Solar & power backup', 'sun', 'Solar power and battery backup', 'solar-roof'],
    ],

    // Home page hero slides, one per service family:
    // [photo (public/assets/hero), photo focus, kicker, headline, highlighted headline, text, service slug, tab label, caption title, caption line]
    'hero_slides' => [
        ['cctv-dome-hikvision', 'left center', 'CCTV & SURVEILLANCE · KANDY', 'Your trusted', 'CCTV & security partner', 'HD and night-vision camera systems from Hikvision, Dahua, EZVIZ, Imou and UNV, installed for homes, shops and workplaces.', 'cctv-installation', 'CCTV', 'CCTV installation & repair', 'Day and night HD cameras'],
        ['smart-lock', 'center 40%', 'SMART LOCKS · ALARMS · INTERCOMS', 'Secure every', 'door and entrance', 'Fingerprint smart door locks, intruder alarm systems and video intercoms, so you always know who comes in.', 'smart-door-locks', 'Locks & alarms', 'Smart door locks', 'Fingerprint, PIN, card and app'],
        ['network-switch', 'center', 'NETWORKING & WI-FI', 'Fast, reliable', 'networks & Wi-Fi', 'Structured cabling, switches, routers and mesh Wi-Fi, including TP-Link, for homes, shops and offices.', 'networking', 'Network & Wi-Fi', 'Network installation', 'Cabling, switches and Wi-Fi'],
        ['smart-home', 'center', 'SMART HOME', 'Make your home', 'smarter & safer', 'Smart cameras, plugs, switches and sensors you control from your phone, set up and connected for you.', 'smart-home', 'Smart home', 'Smart home devices', 'Control it all from your phone'],
        ['ac-repair', 'center', 'A/C & APPLIANCE REPAIR', 'Cool, fixed and', 'running again', 'Air-conditioning installation and service, plus refrigerator and washing machine repairs for homes and businesses.', 'air-conditioning', 'A/C & repairs', 'A/C, fridge & washing machine', 'Installation, repair and service'],
        ['solar-roof', 'center', 'SOLAR & BATTERY BACKUP', 'Power that keeps', 'your life running', 'Solar power systems to cut electricity bills, and battery backup to keep lights, Wi-Fi and CCTV on during power cuts.', 'solar-services', 'Solar & backup', 'Solar power systems', 'Lower bills, backup in power cuts'],
    ],

    // Home "Get a quote" finder: popular quick picks (service names from repair_services).
    'finder_popular' => ['CCTV installation', 'Smart door locks', 'Wi-Fi solutions', 'A/C installation & service', 'Solar power systems'],

    // Home page tips: [icon, title, text, link label, service slug]
    'home_tips' => [
        ['camera', 'Keep your cameras clear', 'Wipe camera lenses every few months and trim plants that grow in front of them. Dust, spider webs and leaves are the most common reason for blurry or useless night footage.', 'Book a CCTV service', 'cctv-repair-service'],
        ['battery', 'Keep recording in power cuts', 'A camera system with no backup stops recording the moment the power goes off. A small battery backup keeps the recorder, cameras and router running.', 'See battery backup', 'battery-backup'],
        ['wifi', 'Secure your Wi-Fi', 'Change the default router password, use WPA2 or WPA3, and put guests on a separate network. A secure network protects your cameras and smart devices too.', 'Wi-Fi solutions', 'wifi-solutions'],
        ['snow', 'Service your A/C regularly', 'Clean the filters every few weeks and book a service when cooling drops, water drips or the unit gets noisy. Regular service saves power and avoids bigger repairs.', 'Book an A/C service', 'air-conditioning'],
    ],

    // Brands we install and sell (used for the brands section and the shop brand filter).
    'brands' => ['Hikvision', 'HiLook', 'EZVIZ', 'Dahua', 'Imou', 'TP-Link', 'UNV'],

    // Shop categories shown in the home page category strip (by slug), then "All services".
    'home_strip' => ['cctv-cameras', 'network-wi-fi', 'alarm-systems', 'smart-door-locks', 'intercom-systems'],

    // Service names used by the booking form (kept in sync with repair_services below).
    'services' => array_column($repairServices, 'name'),

    // Shown on the /repairs page. Edit freely: turnaround times are typical
    // estimates and every job is quoted after inspection.
    'repair_services' => $repairServices,

    'repair_process' => [
        ['Tell us what you need', 'Call or message us with your location and a few details about your project.'],
        ['Discuss the site', 'We will talk through your requirements and any existing equipment.'],
        ['Plan the work', 'Our team will discuss the appropriate service and arrange the next steps.'],
        ['Install or service', 'We carry out the agreed installation, repair or service and explain the system.'],
    ],

    'repair_faqs' => [
        ['What areas do you cover?', 'The team is based in Nugawela, Kandy. Contact us with your location to discuss your project.'],
        ['Which brands do you work with?', 'We work with Hikvision, HiLook, EZVIZ, Dahua, Imou, TP-Link and UNV. Tell us if you already have equipment from another brand.'],
        ['Can I get a CCTV installation quote?', 'Yes. Call or WhatsApp us with your location, the type of property and the areas you want to monitor.'],
        ['Can you service an existing CCTV system?', 'Yes. We provide CCTV repair and service. Let us know what is happening with the cameras or recorder.'],
        ['What other services do you offer?', 'Besides CCTV we install alarm systems, smart door locks, intercoms, networks, Wi-Fi and smart home devices. We also install and service A/C, repair refrigerators and washing machines, and install solar power and battery backup systems.'],
    ],

    // ------------------------------------------------------------------
    // Home page content. Edit freely; every section hides itself if emptied.
    // ------------------------------------------------------------------

    // [icon, title, text, what we usually cover]
    'property_solutions' => [
        ['home', 'Homes', 'Keep an eye on gates, doors, garages and gardens, day and night.', 'Gate · Front door · Garage · Garden'],
        ['store', 'Shops & retail', 'Watch the cash counter, aisles and entrance, even after closing time.', 'Cashier · Aisles · Entrance · Stockroom'],
        ['building', 'Offices', 'Monitor reception, work areas and server rooms from one screen.', 'Reception · Corridors · Parking'],
        ['factory', 'Warehouses & factories', 'Cover loading bays, stock areas and wide boundaries.', 'Loading bay · Yard · Perimeter'],
        ['school', 'Schools & institutions', 'Help keep entrances, halls and grounds safe for everyone.', 'Gates · Halls · Grounds'],
        ['shield', 'Construction sites', 'Protect tools, materials and machinery on site.', 'Site entrance · Storage · Equipment'],
    ],

    // [icon, name, best for, points, shop search term]
    'camera_guide' => [
        ['camera', 'Bullet cameras', 'Outdoors: gates, walls and driveways', ['Long viewing distance', 'Weatherproof housing', 'Visible deterrent'], 'bullet'],
        ['eye', 'Dome cameras', 'Indoors: shops, offices and corridors', ['Discreet ceiling mount', 'Wide coverage', 'Hard to tamper with'], 'dome'],
        ['moon', 'Colour night vision', 'Areas that need detail after dark', ['Full-colour images at night', 'Warm supplementary light', 'Clearer faces and vehicles'], 'colour'],
        ['wifi', 'Wi-Fi cameras', 'Homes and small rooms, no cabling', ['Quick to set up', 'Two-way audio', 'Pan and tilt control'], 'wi-fi'],
        ['bolt', 'IP / PoE cameras', 'Larger sites that need sharper detail', ['Higher resolution', 'Power and video on one cable', 'Easy to expand'], 'poe'],
    ],

    // [name, cameras, ideal for, includes, highlighted]
    'packages' => [
        ['Home starter', '4 cameras', 'Houses and small shops', ['4 HD night-vision cameras', '4-channel recorder', 'Surveillance hard drive', 'Cabling and installation', 'Mobile app set up on your phone'], false],
        ['Business', '8 cameras', 'Shops, offices and larger homes', ['8 HD cameras (bullet and dome)', '8-channel recorder', 'Larger surveillance hard drive', 'Power supply box and cabling', 'Mobile viewing for owners and staff'], true],
        ['Custom system', '16+ cameras', 'Factories, schools and large sites', ['Site visit and camera planning', 'IP / PoE cameras and NVR', 'Recording sized to your needs', 'Multiple viewing screens', 'Service after installation'], false],
    ],

    // [icon, title, text]
    'mobile_features' => [
        ['play', 'Live view anywhere', 'See your cameras in real time from your phone, at home or abroad.'],
        ['clock', 'Playback recordings', 'Go back to any time and check what happened.'],
        ['bell', 'Motion alerts', 'Get notified on your phone when movement is detected.'],
        ['users', 'Share with family or staff', 'Give the people you trust their own access.'],
    ],

    'home_faqs' => [
        ['How many cameras do I need?', 'It depends on the entrances and areas you want to cover. A typical house needs about 4 cameras; shops and offices often need 8 or more. Tell us about your property and we will suggest a layout.'],
        ['Can I watch my cameras on my phone?', 'Yes. We set up the mobile app on your phone during installation, so you can watch live view and recordings from anywhere with an internet connection.'],
        ['Do the cameras work at night?', 'Yes. Our cameras have infrared night vision, and colour night vision models show full-colour images after dark.'],
        ['How long are recordings kept?', 'It depends on the number of cameras, the recording quality and the hard drive size. We size the hard drive to keep the number of days you need.'],
        ['Do you repair systems you did not install?', 'Yes. We service and repair existing CCTV systems. Tell us what is happening with the cameras or recorder.'],
        ['Do you only do CCTV?', 'No. We also install alarm systems, smart door locks, intercoms, networks and Wi-Fi, repair A/C units, refrigerators and washing machines, and install solar power and battery backup systems.'],
    ],
];
