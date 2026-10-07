<?php
/**
 * Static site content — direct port of client/src/data/site.js.
 * Not admin-editable (matches the original app — the admin panel only
 * ever managed branding/contact/social/SMTP settings, never this content).
 */

$brand = [
    'name' => 'WePower Solar Solutions',
    'short' => 'WePower',
    'tagline' => 'Empower Yourself with Solar Energy',
    'logo' => '/assets/images/logo.png',
    'phones' => ['0335-5777898', '0304-7357138'],
    'instagram' => '@wepower__',
    'address' => 'Office 3, GF Plaza 179, Intellectual Village Spring North, Bahria Town Phase 7, Rawalpindi',
];

$company = [
    'who' => "WePower Solar Solutions is a professional solar EPC company delivering reliable, high-performance renewable energy solutions across residential, commercial, industrial, and government sectors. Backed by a technically strong in-house team, we specialize in end-to-end project execution — from design and procurement to installation, testing, and long-term support — ensuring quality, safety, and compliance at every stage.",
    'purpose' => "We exist to empower individuals and businesses with reliable solar energy solutions engineered for performance, safety, and long-term value — accelerating Pakistan's shift toward a cleaner, more sustainable energy future.",
    'vision' => "To become a trusted leader in renewable energy by driving Pakistan's transition towards clean, affordable, and reliable solar power for a sustainable future.",
    'mission' => "To deliver high-quality, safe, and efficient solar systems through engineering excellence and disciplined execution.",
    'values' => [
        ['title' => 'Integrity', 'desc' => 'Honest, transparent, and accountable in all our dealings.'],
        ['title' => 'Excellence', 'desc' => 'High technical standards with zero compromise on quality.'],
    ],
];

$ceo = [
    'name' => 'Mohammad Wasti',
    'role' => 'Founder & CEO · Electrical Engineer',
    'image' => '/assets/images/ceo.webp',
    'bio' => "Mohammad Wasti is the Founder and CEO of WePower Solar and a qualified Electrical Engineer with international professional experience in Turkey and the United Arab Emirates during the early years of his career. This global exposure strengthened his technical expertise, work ethic, and understanding of international engineering and quality standards. A charismatic, energetic, and results-driven leader, he combines hands-on engineering knowledge with a strong business vision to deliver high-performance solar solutions. The company is supported by a team of highly qualified professionals — graduates of well-reputed institutions such as NUST, FAST, and IST — alongside qualified installation teams.",
];

$whyChoose = [
    'Proven engineering capability with strict quality and safety compliance.',
    'End-to-end EPC execution by an experienced in-house team.',
    'Transparent pricing, timely delivery, and dependable after-sales support.',
];

$notableProjects = [
    'Army Cantonments & Private Colonies (Confidential)',
    'Al-Hayat Floor Mill — Attock',
    'Neelum Textiles Mill — Faisalabad',
    '5 Subway Branches',
    "Adam's Food Factory — Rawat",
];

$roadmap = [
    ['icon' => 'bi-battery-full', 'title' => 'Advanced Energy Storage', 'desc' => 'Next-generation battery and storage solutions.'],
    ['icon' => 'bi-graph-up-arrow', 'title' => 'Large-Scale Commercial', 'desc' => 'Expansion into utility-scale commercial projects.'],
    ['icon' => 'bi-cpu', 'title' => 'Smart Energy Management', 'desc' => 'Intelligent monitoring & energy management systems.'],
    ['icon' => 'bi-stars', 'title' => 'Continuous Improvement', 'desc' => 'Ongoing improvement in service quality.'],
];

$nav = [
    ['label' => 'Home', 'to' => '/'],
    ['label' => 'About', 'to' => '/about'],
    ['label' => 'Services', 'to' => '/services'],
    ['label' => 'Load Calculator', 'to' => '/load-calculator'],
    ['label' => 'Careers', 'to' => '/careers'],
    ['label' => 'Contact', 'to' => '/contact'],
];

$heroStats = [
    ['value' => 15, 'suffix' => 'MW+', 'label' => 'Total Installed'],
    ['value' => 1000, 'suffix' => '+', 'label' => 'Happy Clients'],
    ['value' => 2, 'suffix' => ' Yr', 'label' => 'Free O&M Support'],
];

$stats = [
    ['value' => 15, 'suffix' => 'MW+', 'label' => 'Solar Installed'],
    ['value' => 1000, 'suffix' => '+', 'label' => 'Homes Powered'],
    ['value' => 50, 'suffix' => '+', 'label' => 'Industrial Projects'],
    ['value' => 98, 'suffix' => '%', 'label' => 'Client Satisfaction'],
];

// Each service gets an "Apply Now" button that opens the "Get a Quote" modal.
$services = [
    [
        'id' => 'residential', 'icon' => 'bi-house-heart', 'title' => 'Residential Solar',
        'image' => '/assets/images/residential.webp',
        'desc' => 'Customized rooftop solar solutions for homes — high-efficiency systems designed for maximum savings and long-term performance.',
        'points' => ['Customized rooftop systems', 'Payback in 3–4 years', 'Warranty-backed install'],
    ],
    [
        'id' => 'commercial', 'icon' => 'bi-building', 'title' => 'Commercial & Industrial',
        'image' => '/assets/images/commercial.webp',
        'desc' => 'Tailored solar systems for offices, factories, and large facilities, designed for maximum efficiency and ROI.',
        'points' => ['Proven C&I track record', 'Strong, fast ROI', 'End-to-end EPC + O&M'],
    ],
    [
        'id' => 'hybrid', 'icon' => 'bi-lightning-charge', 'title' => 'On/Off-Grid & Hybrid',
        'image' => '/assets/images/on-off-grid.webp',
        'desc' => 'Grid-tied, independent, or hybrid setups optimized for efficiency — for homes, businesses, and industries.',
        'points' => ['24/7 power security', 'Smart load management', 'Net-metering compatible'],
    ],
    [
        'id' => 'netmetering', 'icon' => 'bi-speedometer2', 'title' => 'Net Metering',
        'image' => '/assets/images/ground-mount.webp',
        'desc' => 'Hassle-free net metering setup. Complete support from application to grid connection for maximum savings.',
        'points' => ['Application to approval', 'Maximum bill savings', 'Grid connection handled'],
    ],
    [
        'id' => 'om', 'icon' => 'bi-tools', 'title' => 'Operation & Maintenance',
        'image' => '/assets/images/om-maintenance.webp',
        'desc' => 'Regular monitoring, preventive maintenance, and prompt troubleshooting. 2 years of free O&M support.',
        'points' => ['Performance monitoring', 'Fast response time', 'Preventive maintenance'],
    ],
    [
        'id' => 'design', 'icon' => 'bi-clipboard-check', 'title' => 'Site Survey & Design',
        'image' => '/assets/images/solar-panel.webp',
        'desc' => 'Detailed load assessment, system design, and financial analysis to ensure the perfect fit for your needs.',
        'points' => ['On-site assessment', 'Engineered design', 'Financial analysis'],
    ],
];

$whyFeatures = [
    ['icon' => 'bi-shield-check', 'title' => 'Engineering Excellence', 'desc' => 'Strict quality and safety compliance with international installation standards.'],
    ['icon' => 'bi-people', 'title' => 'In-House Expert Team', 'desc' => 'Graduates from NUST, FAST, IST and qualified installation crews.'],
    ['icon' => 'bi-cash-stack', 'title' => 'Transparent Pricing', 'desc' => 'No hidden costs. Timely delivery with dependable after-sales support.'],
];

$process = [
    ['n' => 1, 'title' => 'Site Survey', 'desc' => 'On-site load & structural assessment.'],
    ['n' => 2, 'title' => 'System Design', 'desc' => 'Engineering & financial analysis.'],
    ['n' => 3, 'title' => 'Proposal', 'desc' => 'Transparent quote & client approval.'],
    ['n' => 4, 'title' => 'Procurement', 'desc' => 'Tier-1 panels & certified equipment.'],
    ['n' => 5, 'title' => 'Installation', 'desc' => 'Professional in-house team.'],
    ['n' => 6, 'title' => 'Testing', 'desc' => 'Commissioning & performance check.'],
    ['n' => 7, 'title' => 'Net Metering', 'desc' => 'Application to grid connection.'],
    ['n' => 8, 'title' => 'Aftercare', 'desc' => '2 years free maintenance.'],
];

$products = [
    ['icon' => 'bi-grid-3x3-gap', 'title' => 'Tier-1 Solar Panels', 'desc' => 'Premium monocrystalline panels with 25+ year warranties.'],
    ['icon' => 'bi-cpu', 'title' => 'Hybrid Inverters', 'desc' => 'High-efficiency on-grid & hybrid inverters with smart monitoring.'],
    ['icon' => 'bi-bricks', 'title' => 'Mounting Structures', 'desc' => 'Galvanized & aluminium structures for wind & load resistance.'],
    ['icon' => 'bi-battery-charging', 'title' => 'Battery Solutions', 'desc' => 'Lithium & lead-acid battery banks for backup & off-grid.'],
];

// Centralized FAQ — was duplicated verbatim across 3 React files in the
// original app; ported once here and included wherever needed.
$faq = [
    ['q' => 'How long does a solar installation take?', 'a' => 'A typical residential installation takes 2–4 days. Commercial and industrial projects range from 1 to 4 weeks depending on system size and site conditions.'],
    ['q' => 'What is the payback period for solar in Pakistan?', 'a' => 'With current electricity rates, most residential systems pay back in 3–4 years. Commercial systems often achieve payback in 2–3 years due to higher consumption.'],
    ['q' => 'Do you handle net metering applications?', 'a' => 'Yes — we manage the complete net metering process from application to DISCO approval and grid connection. No paperwork hassle for you.'],
    ['q' => 'What warranty do you offer?', 'a' => 'Solar panels come with 25+ year performance warranties, inverters typically 5–10 years, and our installation workmanship is backed by our own warranty terms.'],
    ['q' => 'Can solar work during a power outage?', 'a' => 'Grid-tied systems shut off during outages for safety unless paired with battery backup or a hybrid inverter — we can design a hybrid/off-grid setup if backup power is a priority for you.'],
    ['q' => 'Is a site survey mandatory before installation?', 'a' => 'Yes — a free on-site survey lets us assess your roof, load, and sunlight exposure so we can design an accurately sized, high-performance system.'],
];

// Appliance presets for the Load Calculator page (ported from LoadCalculator.jsx)
$appliancePresets = [
    ['name' => 'AC (1 ton)', 'power' => 1200],
    ['name' => 'AC (1.5 ton)', 'power' => 1750],
    ['name' => 'AC (2 ton)', 'power' => 2200],
    ['name' => 'Refrigerator', 'power' => 150],
    ['name' => 'LED Bulb', 'power' => 10],
    ['name' => 'Ceiling Fan', 'power' => 75],
    ['name' => 'Tube Light', 'power' => 40],
    ['name' => 'Water Pump (0.5 HP)', 'power' => 375],
    ['name' => 'Water Pump (1 HP)', 'power' => 750],
    ['name' => 'Washing Machine', 'power' => 500],
    ['name' => 'Iron', 'power' => 1000],
    ['name' => 'Microwave', 'power' => 1200],
    ['name' => 'TV (LED)', 'power' => 100],
    ['name' => 'Computer / Laptop', 'power' => 150],
    ['name' => 'Water Dispenser', 'power' => 500],
    ['name' => 'Custom Appliance', 'power' => 0],
];
