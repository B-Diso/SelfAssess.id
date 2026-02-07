<?php

namespace Database\Seeders;

use App\Domain\Standard\Models\Standard;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Standard\Models\StandardSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StandardSeeder extends Seeder
{
    /**
     * Standards to be seeded
     */
    private array $standards = [
        [
            'name' => 'Global Internal Audit Standards',
            'code' => 'GIAS',
            'version' => '2024',
            'type' => 'internal',
            'description' => 'The Global Internal Audit Standards guide the worldwide professional practice of internal auditing.',
            'import_from_csv' => true,
        ],
        [
            'name' => 'ISO 9001:2015 - Quality Management System',
            'code' => 'ISO9001',
            'version' => '2015',
            'type' => 'international',
            'description' => 'International standard for quality management systems, focusing on customer satisfaction and continuous improvement.',
            'import_from_csv' => false,
            'domains' => [
                ['code' => 'QMS1', 'title' => 'Quality Management System', 'description' => 'Organizational quality management framework'],
                ['code' => 'QMS2', 'title' => 'Management Responsibility', 'description' => 'Leadership and commitment to quality'],
                ['code' => 'QMS3', 'title' => 'Resource Management', 'description' => 'Provision and management of resources'],
                ['code' => 'QMS4', 'title' => 'Product Realization', 'description' => 'Product/service creation processes'],
                ['code' => 'QMS5', 'title' => 'Measurement Analysis', 'description' => 'Monitoring and measurement processes'],
            ],
        ],
        [
            'name' => 'ISO 27001:2022 - Information Security Management',
            'code' => 'ISO27001',
            'version' => '2022',
            'type' => 'international',
            'description' => 'International standard for information security management systems, protecting information assets.',
            'import_from_csv' => false,
            'domains' => [
                ['code' => 'ISM1', 'title' => 'Information Security Policies', 'description' => 'Security policy framework'],
                ['code' => 'ISM2', 'title' => 'Organization of Information Security', 'description' => 'Internal organization and structure'],
                ['code' => 'ISM3', 'title' => 'Human Resource Security', 'description' => 'Security in employee lifecycle'],
                ['code' => 'ISM4', 'title' => 'Asset Management', 'description' => 'Information asset identification and protection'],
                ['code' => 'ISM5', 'title' => 'Access Control', 'description' => 'User access management'],
                ['code' => 'ISM6', 'title' => 'Cryptography', 'description' => 'Encryption and cryptographic controls'],
                ['code' => 'ISM7', 'title' => 'Physical Security', 'description' => 'Physical access and environmental security'],
                ['code' => 'ISM8', 'title' => 'Operations Security', 'description' => 'Operational procedures and responsibilities'],
                ['code' => 'ISM9', 'title' => 'Communications Security', 'description' => 'Network and information transfer security'],
                ['code' => 'ISM10', 'title' => 'System Acquisition', 'description' => 'Security in system development and maintenance'],
            ],
        ],
        [
            'name' => 'ISO 14001:2015 - Environmental Management',
            'code' => 'ISO14001',
            'version' => '2015',
            'type' => 'international',
            'description' => 'International standard for environmental management systems, minimizing environmental impact.',
            'import_from_csv' => false,
            'domains' => [
                ['code' => 'EMS1', 'title' => 'Environmental Policy', 'description' => 'Environmental commitment and objectives'],
                ['code' => 'EMS2', 'title' => 'Planning', 'description' => 'Environmental aspects and compliance'],
                ['code' => 'EMS3', 'title' => 'Implementation', 'description' => 'Support and operational controls'],
                ['code' => 'EMS4', 'title' => 'Evaluation', 'description' => 'Monitoring and measurement'],
                ['code' => 'EMS5', 'title' => 'Improvement', 'description' => 'Continual improvement processes'],
            ],
        ],
        [
            'name' => 'SOC 2 Type II - Service Organization Control',
            'code' => 'SOC2',
            'version' => '2017',
            'type' => 'industry',
            'description' => 'Service organization control criteria for security, availability, processing integrity, confidentiality, and privacy.',
            'import_from_csv' => false,
            'domains' => [
                ['code' => 'SOC1', 'title' => 'Security', 'description' => 'System protection against unauthorized access'],
                ['code' => 'SOC2', 'title' => 'Availability', 'description' => 'System accessibility for operations'],
                ['code' => 'SOC3', 'title' => 'Processing Integrity', 'description' => 'System processing completeness and accuracy'],
                ['code' => 'SOC4', 'title' => 'Confidentiality', 'description' => 'Information confidentiality protection'],
                ['code' => 'SOC5', 'title' => 'Privacy', 'description' => 'Personal information privacy controls'],
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('📚 Seeding quality standards...');
        $this->command->newLine();

        DB::transaction(function () {
            foreach ($this->standards as $standardData) {
                $importFromCsv = $standardData['import_from_csv'] ?? false;

                if ($importFromCsv) {
                    $this->seedStandardFromCsv($standardData);
                } else {
                    $this->seedStandardWithStructure($standardData);
                }
            }
        });

        $this->command->newLine();
        $this->command->info('✅ All standards seeded successfully!');
    }

    /**
     * Seed standard from CSV files (original GIAS standard)
     */
    private function seedStandardFromCsv(array $standardData): void
    {
        $this->command->info("Seeding {$standardData['name']}...");

        $standard = Standard::firstOrCreate(
            [
                'name' => $standardData['name'],
                'version' => $standardData['version'],
            ],
            [
                'type' => $standardData['type'],
                'description' => $standardData['description'],
                'is_active' => true,
            ]
        );

        if (!$standard->wasRecentlyCreated) {
            $this->command->warn("  ⚠️  Standard already exists, skipping CSV import.");
            return;
        }

        // Import Domains (Root Sections)
        $domainMap = [];
        $domains = $this->csvToArray(database_path('seeders/standard_domains.csv'));

        foreach ($domains as $row) {
            $section = StandardSection::create([
                'standard_id' => $standard->id,
                'parent_id' => null,
                'type' => 'domain',
                'code' => $row['code'],
                'title' => $row['title'],
                'description' => $row['description'],
            ]);
            $domainMap[$row['code']] = $section->id;
        }
        $this->command->info("  ✓ Imported " . count($domains) . " Domains");

        // Import Elements (Child Sections)
        $elementMap = [];
        $elements = $this->csvToArray(database_path('seeders/standard_elements.csv'));

        foreach ($elements as $row) {
            $parentUuid = $domainMap[$row['domain_id']] ?? null;
            if (!$parentUuid) {
                continue;
            }

            $section = StandardSection::create([
                'standard_id' => $standard->id,
                'parent_id' => $parentUuid,
                'type' => 'element',
                'code' => $row['code'],
                'title' => $row['title'],
                'description' => $row['description'],
            ]);
            $elementMap[$row['code']] = $section->id;
        }
        $this->command->info("  ✓ Imported " . count($elements) . " Elements");

        // Import Requirements
        $requirements = $this->csvToArray(database_path('seeders/standard_requirements.csv'));
        $reqCount = 0;

        foreach ($requirements as $row) {
            $parentUuid = $elementMap[$row['element_code']] ?? null;
            if (!$parentUuid) {
                continue;
            }

            StandardRequirement::create([
                'standard_section_id' => $parentUuid,
                'display_code' => $row['code'],
                'title' => $row['title'],
                'description' => $row['description'],
                'evidence_hint' => $row['evidence_hint'],
            ]);
            $reqCount++;
        }
        $this->command->info("  ✓ Imported {$reqCount} Requirements");
    }

    /**
     * Seed standard with programmatic structure
     */
    private function seedStandardWithStructure(array $standardData): void
    {
        $this->command->info("Seeding {$standardData['name']}...");

        $standard = Standard::firstOrCreate(
            [
                'name' => $standardData['name'],
                'version' => $standardData['version'],
            ],
            [
                'type' => $standardData['type'],
                'description' => $standardData['description'],
                'is_active' => true,
            ]
        );

        if (!$standard->wasRecentlyCreated) {
            $this->command->warn("  ⚠️  Standard already exists, skipping structure creation.");
            return;
        }

        $domains = $standardData['domains'] ?? [];
        $totalRequirements = 0;

        foreach ($domains as $domainData) {
            // Create Domain
            $domain = StandardSection::create([
                'standard_id' => $standard->id,
                'parent_id' => null,
                'type' => 'domain',
                'code' => $domainData['code'],
                'title' => $domainData['title'],
                'description' => $domainData['description'],
            ]);

            // Create Elements (3-5 per domain)
            $elementCount = rand(3, 5);
            for ($i = 1; $i <= $elementCount; $i++) {
                $element = StandardSection::create([
                    'standard_id' => $standard->id,
                    'parent_id' => $domain->id,
                    'type' => 'element',
                    'code' => "{$domainData['code']}.{$i}",
                    'title' => "{$domainData['title']} - Element {$i}",
                    'description' => "Detailed requirements for {$domainData['title']} element {$i}",
                ]);

                // Create Requirements (5-8 per element)
                $reqCount = rand(5, 8);
                for ($j = 1; $j <= $reqCount; $j++) {
                    StandardRequirement::create([
                        'standard_section_id' => $element->id,
                        'display_code' => "{$domainData['code']}.{$i}.{$j}",
                        'title' => "Requirement {$j} for {$element->title}",
                        'description' => $this->generateRequirementDescription($element->title, $j),
                        'evidence_hint' => $this->generateEvidenceHint($element->title),
                    ]);
                    $totalRequirements++;
                }
            }
        }

        $this->command->info("  ✓ Created " . count($domains) . " Domains");
        $this->command->info("  ✓ Created approximately {$totalRequirements} Requirements");
    }

    /**
     * Generate realistic requirement description
     */
    private function generateRequirementDescription(string $elementTitle, int $number): string
    {
        // Check if this is an ICoFR (financial) element
        $isIcofr = str_contains($elementTitle, 'Revenue') ||
                   str_contains($elementTitle, 'Expense') ||
                   str_contains($elementTitle, 'Procurement') ||
                   str_contains($elementTitle, 'Financial') ||
                   str_contains($elementTitle, 'Entity Level') ||
                   str_contains($elementTitle, 'Control Environment') ||
                   str_contains($elementTitle, 'Risk Assessment') ||
                   str_contains($elementTitle, 'Control Activities') ||
                   str_contains($elementTitle, 'Monitoring') ||
                   str_contains($elementTitle, 'ITGC') ||
                   str_contains($elementTitle, 'Inventory') ||
                   str_contains($elementTitle, 'Tax') ||
                   str_contains($elementTitle, 'Treasury');

        if ($isIcofr) {
            return $this->generateIcofrRequirementDescription($elementTitle, $number);
        }

        // Default non-financial requirement
        $templates = [
            "The organization shall establish, implement, maintain and continually improve {context} to ensure {outcome}.",
            "Management shall ensure that {context} are defined, documented and communicated to ensure {outcome}.",
            "The organization shall plan, design, implement, monitor, review and improve {context} to achieve {outcome}.",
            "Documented information shall be maintained to demonstrate {context} and ensure {outcome}.",
            "The organization shall determine {context} and evaluate the effectiveness of {outcome}.",
        ];

        $contexts = [
            "policies and procedures",
            "control measures",
            "management processes",
            "operational controls",
            "monitoring mechanisms",
        ];

        $outcomes = [
            "compliance with requirements",
            "continuous improvement",
            "risk mitigation",
            "quality objectives are met",
            "stakeholder confidence",
        ];

        $template = $templates[array_rand($templates)];
        $context = $contexts[array_rand($contexts)];
        $outcome = $outcomes[array_rand($outcomes)];

        return str_replace(['{context}', '{outcome}'], [$context, $outcome], $template);
    }

    /**
     * Generate ICoFR-specific requirement description with RCM detail
     */
    private function generateIcofrRequirementDescription(string $elementTitle, int $number): string
    {
        $rcmData = [
            'Entity Level' => [
                'risks' => ['Management override of controls', 'Lack of ethical framework leading to fraud', 'Inadequate oversight by Board of Directors'],
                'objectives' => ['To establish a strong tone at the top', 'Ensure ethical behavior across organization', 'Verify effectiveness of corporate governance'],
                'activities' => [
                    'The Board reviews and approves the Code of Conduct annually and monitors compliance through internal audit reports.',
                    'Management performs an annual fraud risk assessment and implements specific anti-fraud controls for high-risk areas.',
                    'The Audit Committee meets quarterly to review financial reporting integrity and internal control effectiveness.'
                ],
            ],
            'Revenue' => [
                'risks' => ['Fictitious revenue recognized', 'Revenue recorded in wrong period (Cut-off)', 'Incorrect pricing or quantities billed'],
                'objectives' => ['Ensure revenue is real and earned', 'Revenue is recorded accurately and in the correct period', 'Billing matches approved price lists and actual shipments'],
                'activities' => [
                    'All sales orders > $50k must be reviewed against signed customer contracts and approved by the Sales Controller.',
                    'Automated three-way match between Sales Order, Shipping Document, and Invoice is performed for every transaction.',
                    'Weekly reconciliation of accounts receivable sub-ledger to general ledger is reviewed and signed off by the Finance Manager.'
                ],
            ],
            'Procurement' => [
                'risks' => ['Unauthorized vendors or purchases', 'Payments made for goods/services not received', 'Duplicate payments or overpayments'],
                'objectives' => ['Ensure all purchases are valid and authorized', 'Payments only for verified deliveries', 'Accuracy and completeness of expenditures'],
                'activities' => [
                    'Vendor Master File changes require dual authorization from Procurement and Finance departments.',
                    'System-enforced three-way match: PO, Receiving Report, and Invoice must align within 1% tolerance for payment processing.',
                    'Monthly review of Open Purchase Orders over 60 days to ensure appropriate accrual or cancellation.'
                ],
            ],
            'Financial Reporting' => [
                'risks' => ['Inaccurate journal entries', 'Error in financial statement consolidation', 'Inadequate or misleading disclosures'],
                'objectives' => ['Ensure journal entries are valid and accurate', 'Consolidation is mathematically correct', 'Compliance with GAAP/IFRS disclosure requirements'],
                'activities' => [
                    'Manual journal entries > $10k require supporting documentation and independent review by the Controller before posting.',
                    'System access to post journal entries is restricted to authorized accounting staff and reviewed quarterly.',
                    'Checklist for financial statement disclosures is completed and reviewed by the CFO before final submission.'
                ],
            ],
            'ITGC' => [
                'risks' => ['Unauthorized access to financial data', 'Unauthorized changes to system code', 'Failure of data backups/recovery'],
                'objectives' => ['Protect integrity and confidentiality of data', 'Ensure system changes are tested and approved', 'Guarantee availability of financial systems'],
                'activities' => [
                    'Quarterly user access review for SAP/ERP systems is performed by department heads to ensure "need-to-know" access.',
                    'All system changes follow a formal SDLC process including UAT testing and sign-off by the Business Owner.',
                    'Daily backup of the financial database is performed, with weekly off-site storage and annual restoration testing.'
                ],
            ],
        ];

        // Find matching RCM category
        $category = 'General';
        foreach (array_keys($rcmData) as $key) {
            if (str_contains(strtolower($elementTitle), strtolower($key))) {
                $category = $key;
                break;
            }
        }

        if ($category === 'General') {
            // Fallback for categories not explicitly defined
            $risk = "Failure to establish proper internal controls for " . $elementTitle;
            $objective = "Ensure accuracy and reliability of " . $elementTitle . " processes";
            $activity = "Management shall implement formal review and approval procedures for all transactions related to " . $elementTitle;
        } else {
            $idx = ($number - 1) % count($rcmData[$category]['risks']);
            $risk = $rcmData[$category]['risks'][$idx];
            $objective = $rcmData[$category]['objectives'][$idx];
            $activity = $rcmData[$category]['activities'][$idx];
        }

        $frequency = ['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Annual', 'Transactional'][rand(0, 5)];
        $type = rand(0, 1) ? 'Manual' : 'Automated';
        $level = rand(0, 3) > 0 ? 'Key Control' : 'Standard Control';

        return "### Risk Control Matrix (RCM) Detail\n" .
               "**Risk Description:** {$risk}\n\n" .
               "**Control Objective:** {$objective}\n\n" .
               "**Control Activity:** {$activity}\n\n" .
               "**Frequency:** {$frequency} | **Type:** {$type} | **Level:** {$level}";
    }

    /**
     * Generate realistic evidence hint
     */
    private function generateEvidenceHint(string $elementTitle): string
    {
        // Check if this is an ICoFR element
        $isIcofr = str_contains($elementTitle, 'Revenue') ||
                   str_contains($elementTitle, 'Expense') ||
                   str_contains($elementTitle, 'Financial') ||
                   str_contains($elementTitle, 'Control Environment') ||
                   str_contains($elementTitle, 'Risk Assessment') ||
                   str_contains($elementTitle, 'Control Activities') ||
                   str_contains($elementTitle, 'Monitoring');

        if ($isIcofr) {
            return $this->generateIcofrEvidenceHint($elementTitle);
        }

        // Default non-financial hints
        $hints = [
            "Provide documented policies and procedures approved by management",
            "Show records of regular reviews and updates",
            "Include training records and competency assessments",
            "Submit monitoring and measurement logs",
            "Provide audit reports and corrective action records",
            "Include management review meeting minutes",
            "Show performance metrics and KPI tracking",
        ];

        return $hints[array_rand($hints)];
    }

    /**
     * Generate ICoFR-specific evidence hints
     */
    private function generateIcofrEvidenceHint(string $elementTitle): string
    {
        $categoryHints = [
            'Entity Level' => [
                'Signed Board of Directors meeting minutes approving the Code of Conduct',
                'Completed fraud risk assessment working paper signed by the CFO',
                'Audit Committee meeting decks and formal approval of annual audit plan'
            ],
            'Revenue' => [
                'Sample of 25 sales orders > $50k with evidence of reviewer approval',
                'System-generated report showing automated three-way match results and exceptions',
                'Monthly AR reconciliation spreadsheets with sign-off from the Finance Manager'
            ],
            'Procurement' => [
                'Change log for Vendor Master File with attached approval forms for all changes',
                'Sample of paid invoices showing system matching (PO, Receipt, Invoice) and payment approval',
                'Review checklist for aged open purchase orders signed by the Procurement Head'
            ],
            'Financial Reporting' => [
                'Sample of high-value journal entries with attached supporting documents and status "Approved"',
                'User access matrix for ERP system focusing on journal entry post permissions',
                'Completed Disclosure Checklist for the latest quarterly/annual report with CFO sign-off'
            ],
            'ITGC' => [
                'Evidence of quarterly user access reviews (email sign-offs or approved reports)',
                'Sample change request form including UAT results and Business Owner approval signature',
                'IT backup logs and results of the most recent backup restoration test'
            ],
        ];

        // Find matching RCM category
        $category = 'General';
        foreach (array_keys($categoryHints) as $key) {
            if (str_contains(strtolower($elementTitle), strtolower($key))) {
                $category = $key;
                break;
            }
        }

        if ($category === 'General') {
            return "Provide documented policies, approved transactions sample, and reconciliation records for this specific control.";
        }

        return $categoryHints[$category][array_rand($categoryHints[$category])];
    }

    /**
     * Convert CSV file to array
     */
    private function csvToArray($filename): array
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return [];
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($row === [null] || empty($row)) {
                    continue;
                }

                $row = array_map('trim', $row);

                if (!$header) {
                    $header = $row;
                } else {
                    if (count($row) !== count($header)) {
                        continue;
                    }
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
}
