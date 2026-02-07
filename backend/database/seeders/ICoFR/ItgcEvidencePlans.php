<?php

namespace Database\Seeders\ICoFR;

/**
 * IT General Controls (ITGC) Evidence Plans
 * Detailed SOX-compliant evidence plans for IT controls
 */
class ItgcEvidencePlans
{
    public static function get(string $code): string
    {
        return match($code) {
            'ITGC-01' => self::itgc01Evidence(),
            'ITGC-02' => self::itgc02Evidence(),
            'ITGC-03' => self::itgc03Evidence(),
            'ITGC-04' => self::itgc04Evidence(),
            'ITGC-05' => self::itgc05Evidence(),
            'ITGC-06' => self::itgc06Evidence(),
            'ITGC-07' => self::itgc07Evidence(),
            'ITGC-08' => self::itgc08Evidence(),
            'ITGC-09' => self::itgc09Evidence(),
            'ITGC-10' => self::itgc10Evidence(),
            default => self::defaultEvidence($code),
        };
    }

    private static function itgc01Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-01 User Access Provisioning and Removal</h3>
<p><strong>COSO Principle:</strong> P11 - Selects and develops general controls over technology | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 40 access events (20 new + 20 terminations)</li>
<li><strong>Selection:</strong> Random across all financial applications</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Access request tickets (ServiceNow/Jira)</li>
<li>Manager approval emails/forms</li>
<li>User provisioning logs from IT</li>
<li>Termination checklists from HR</li>
<li>Access revocation confirmations</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify access request submitted with business justification</li>
<li>Check manager approval obtained before provisioning</li>
<li>Verify access granted within 48 hours of approval</li>
<li>For terminations: verify access revoked within 24 hours</li>
<li>Confirm terminated employees cannot log in (live test)</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Authorization</td><td>Manager approval documented</td></tr>
<tr><td>Timeliness</td><td>Provisioning 48h, Termination 24h</td></tr>
<tr><td>Completeness</td><td>All applications covered</td></tr>
</table>

<p><em><strong>SOX Risk:</strong> Orphaned access of terminated employees is common audit finding.</em></p>
HTML;
    }

    private static function itgc02Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-02 Privileged Access Management</h3>
<p><strong>COSO Principle:</strong> P11 - General controls over technology | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All privileged users (100% if <20, else 20 samples)</li>
<li><strong>Include:</strong> All fire-call/emergency access usage</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Privileged access approval forms</li>
<li>Administrator account lists</li>
<li>Fire-call access logs</li>
<li>Privileged access usage reports</li>
<li>Monthly privileged access review documentation</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify privileged access limited to authorized personnel</li>
<li>Check fire-call access requires CFO approval</li>
<li>Verify privileged access is time-limited</li>
<li>Review monthly privileged access logs for anomalies</li>
<li>Confirm privileged access reviews performed by CISO/IT Director</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Authorization:</strong> Proper approval for privileged access</li>
<li><strong>Monitoring:</strong> All privileged activity logged</li>
<li><strong>Time-bound:</strong> Emergency access expires automatically</li>
</ul>

<p><em><strong>⚠️ Critical:</strong> Privileged users can bypass all controls. Extra scrutiny required.</em></p>
HTML;
    }

    private static function itgc03Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-03 Quarterly User Access Review</h3>
<p><strong>COSO Principle:</strong> P16 - Assesses internal control deficiencies | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All quarterly UARs in assessment period (100%)</li>
<li><strong>Include:</strong> All remediation of orphaned accounts</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Quarterly User Access Review reports</li>
<li>Department Head sign-off emails</li>
<li>Orphaned account reports</li>
<li>Remediation tickets for removed access</li>
<li>Exceptions documentation with business justification</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify UAR conducted within 5 business days of quarter start</li>
<li>Check Department Head sign-off obtained for all units</li>
<li>Verify orphaned accounts identified and disabled</li>
<li>Review inappropriate access removed timely</li>
<li>Check exceptions have valid business justification</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Timeliness</td><td>Within 5 days of quarter start</td></tr>
<tr><td>Completeness</td><td>All departments reviewed</td></tr>
<tr><td>Remediation</td><td>Orphaned accounts disabled</td></tr>
</table>

<p><em><strong>Best Practice:</strong> UAR is #1 ITGC control. Deficiencies often indicate broader control issues.</em></p>
HTML;
    }

    private static function itgc04Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-04 Segregation of Duties Enforcement</h3>
<p><strong>COSO Principle:</strong> P11 - Selects and develops general controls | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> Quarterly SOD conflict reports (100%)</li>
<li><strong>Include:</strong> All SOD exceptions with mitigating controls</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>SOD conflict reports from GRC tool</li>
<li>SOD matrix by application</li>
<li>Mitigating control documentation</li>
<li>Compensating control testing results</li>
<li>Management sign-off on accepted risks</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify SOD rules configured in system (e.g., cannot have AR create + post)</li>
<li>Check quarterly SOD conflict reports generated</li>
<li>Review conflicts identified and documented</li>
<li>Verify mitigating controls tested and effective</li>
<li>Check management approval for unavoidable conflicts</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Detection:</strong> SOD conflicts identified timely</li>
<li><strong>Mitigation:</strong> Compensating controls in place</li>
<li><strong>Approval:</strong> Management accepts residual risk</li>
</ul>

<p><em><strong>Common Conflicts:</strong> Request + Approve, Create Vendor + Pay Vendor, Post + Reconcile.</em></p>
HTML;
    }

    private static function itgc05Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-05 Program Change Management</h3>
<p><strong>COSO Principle:</strong> P11 - General controls over technology | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 changes (25 standard + 5 emergency)</li>
<li><strong>Stratification:</strong> By risk rating (High/Medium/Low)</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Change Request tickets with risk assessment</li>
<li>CAB (Change Advisory Board) meeting minutes</li>
<li>User Acceptance Testing sign-offs</li>
<li>Migration/deployment approvals</li>
<li>Post-implementation review reports</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify Change Request submitted with business justification</li>
<li>Check CAB approval for high-risk changes</li>
<li>Verify UAT completed and signed off by Business Owner</li>
<li>Confirm changes deployed only after approval</li>
<li>Review post-implementation verification performed</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Authorization</td><td>CAB approval for high-risk</td></tr>
<tr><td>Testing</td><td>UAT completed successfully</td></tr>
<tr><td>Separation</td><td>Developer ≠ Deployer</td></tr>
</table>
HTML;
    }

    private static function itgc06Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-06 Emergency Change Control</h3>
<p><strong>COSO Principle:</strong> P11 - General controls over technology | <strong>SOX Relevance:</strong> High</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All emergency changes in period (100%)</li>
<li><strong>Focus:</strong> Changes during month-end/quarter-end</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Emergency Change Request tickets</li>
<li>eCAB (Emergency CAB) meeting records</li>
<li>Post-implementation testing results</li>
<li>Retroactive risk assessment documentation</li>
<li>Business justification for emergency</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify emergency CAB convened for all emergency changes</li>
<li>Check business justification documented (why normal process not followed)</li>
<li>Verify post-implementation testing completed within 5 business days</li>
<li>Review retroactive risk assessment performed</li>
<li>Confirm emergency changes were truly emergencies</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Authorization:</strong> eCAB approval obtained</li>
<li><strong>Justification:</strong> Valid business reason</li>
<li><strong>Remediation:</strong> Post-testing completed</li>
</ul>

<p><em><strong>Warning:</strong> Pattern of "emergency" changes may indicate broken change process.</em></p>
HTML;
    }

    private static function itgc07Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-07 Developer Access to Production</h3>
<p><strong>COSO Principle:</strong> P11 - General controls over technology | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All production deployments in period</li>
<li><strong>Include:</strong> All fire-call access incidents</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Production access logs</li>
<li>Deployment logs showing who deployed</li>
<li>Fire-call access request tickets</li>
<li>Incident tickets justifying fire-call</li>
<li>Segregation of environments documentation</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify developers cannot access production without fire-call approval</li>
<li>Check all production deployments performed by Operations, not Development</li>
<li>Verify fire-call access requires incident ticket and manager approval</li>
<li>Review fire-call access logged and monitored</li>
<li>Confirm production data not accessible in non-production</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Separation</td><td>Dev and Prod environments segregated</td></tr>
<tr><td>Access Control</td><td>Fire-call requires approval</td></tr>
<tr><td>Monitoring</td><td>All prod access logged</td></tr>
</table>

<p><em><strong>Critical Risk:</strong> Developers in production can change anything without detection.</em></p>
HTML;
    }

    private static function itgc08Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-08 Backup and Recovery Control</h3>
<p><strong>COSO Principle:</strong> P11 - General controls over technology | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All backup jobs for 1 week + Annual restore test</li>
<li><strong>Focus:</strong> Financial system backups (ERP, GL, AR, AP)</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Backup job logs (daily incremental, weekly full)</li>
<li>Backup success/failure reports</li>
<li>Off-site backup storage receipts</li>
<li>Annual restore test reports</li>
<li>Recovery Time Objective (RTO) test results</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify daily incremental backups completed successfully</li>
<li>Check weekly full backups completed and verified</li>
<li>Verify off-site backup storage (7-year retention)</li>
<li>Review annual restore test performed and documented</li>
<li>Confirm RTO (4 hours) and RPO (1 hour) tested</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Completeness:</strong> All financial systems backed up</li>
<li><strong>Success:</strong> <1% backup failure rate</li>
<li><strong>Recovery:</strong> Restore test successful</li>
</ul>

<p><em><strong>SOX Requirement:</strong> Financial data must be recoverable. Failed restore = material weakness.</em></p>
HTML;
    }

    private static function itgc09Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-09 Job Scheduling and Monitoring</h3>
<p><strong>COSO Principle:</strong> P11 - General controls over technology | <strong>SOX Relevance:</strong> High</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 days of job logs (1 month sample)</li>
<li><strong>Focus:</strong> Critical financial batch jobs</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Job scheduler logs</li>
<li>Job failure alerts and notifications</li>
<li>Failed job remediation tickets</li>
<li>Job dependency documentation</li>
<li>Automatic restart logs</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify critical financial jobs scheduled correctly</li>
<li>Check failed job alerts sent to Operations</li>
<li>Verify failed jobs resolved within SLA</li>
<li>Test automatic restart procedures work</li>
<li>Confirm job dependencies properly configured</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Completion</td><td>>99% job success rate</td></tr>
<tr><td>Monitoring</td><td>Alerts for all failures</td></tr>
<tr><td>Recovery</td><td>Auto-restart functional</td></tr>
</table>
HTML;
    }

    private static function itgc10Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: ITGC-10 Problem and Incident Management</h3>
<p><strong>COSO Principle:</strong> P16 - Assesses internal control deficiencies | <strong>SOX Relevance:</strong> Medium</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 incidents (10 P1 + 10 P2 + 10 P3)</li>
<li><strong>Include:</strong> All financial system outages</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Incident tickets from IT Service Desk</li>
<li>Incident escalation records</li>
<li>Resolution documentation</li>
<li>Root cause analysis reports</li>
<li>Monthly incident review meeting minutes</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify all incidents logged in ticketing system</li>
<li>Check Priority 1 incidents escalated within 15 minutes</li>
<li>Verify resolution documented for all incidents</li>
<li>Review root cause analysis for repeat incidents</li>
<li>Confirm monthly incident review with trend analysis</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Timeliness:</strong> Resolved within SLA</li>
<li><strong>Documentation:</strong> Root cause identified</li>
<li><strong>Trending:</strong> Repeat issues addressed</li>
</ul>
HTML;
    }

    private static function defaultEvidence(string $code): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: {$code}</h3>
<p>Please refer to standard ITGC testing procedures.</p>
HTML;
    }
}
