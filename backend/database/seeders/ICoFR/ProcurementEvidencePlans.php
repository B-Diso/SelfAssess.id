<?php

namespace Database\Seeders\ICoFR;

/**
 * Procurement & Expense Cycle Evidence Plans
 * Detailed SOX-compliant evidence plans for Purchase-to-Pay controls
 */
class ProcurementEvidencePlans
{
    public static function get(string $code): string
    {
        return match($code) {
            'EXP-01' => self::exp01Evidence(),
            'EXP-02' => self::exp02Evidence(),
            'EXP-03' => self::exp03Evidence(),
            'EXP-04' => self::exp04Evidence(),
            'EXP-05' => self::exp05Evidence(),
            'EXP-06' => self::exp06Evidence(),
            'EXP-07' => self::exp07Evidence(),
            'EXP-08' => self::exp08Evidence(),
            default => self::defaultEvidence($code),
        };
    }

    private static function exp01Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-01 Purchase Requisition and Approval</h3>
<p><strong>COSO Principle:</strong> Control Activities (P11) | <strong>SOX Relevance:</strong> High - Prevents unauthorized spend</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 requisitions</li>
<li><strong>Stratification:</strong>
    <ul>
    <li>15 requisitions <$10,000</li>
    <li>10 requisitions $10,000-$50,000</li>
    <li>5 requisitions >$50,000 (high-risk)</li>
    </ul>
</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Purchase requisition forms</li>
<li>Vendor quotations for purchases >$5,000</li>
<li>Business justification documentation</li>
<li>Budget approval evidence</li>
<li>System audit trail</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify requisitions are sequentially numbered</li>
<li>Check approval signatures match authorized signatory matrix</li>
<li>Compare requisition amounts to supporting quotations</li>
<li>Verify requestor and approver are different individuals</li>
<li>Check approval within SLA (48h standard, 24h urgent)</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Authorization</td><td>100% valid approval</td></tr>
<tr><td>Budget</td><td>Budget code verified</td></tr>
<tr><td>SOD</td><td>Requestor ≠ Approver</td></tr>
</table>
HTML;
    }

    private static function exp02Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-02 Vendor Selection and Management</h3>
<p><strong>COSO Principle:</strong> Risk Assessment (P7) | <strong>SOX Relevance:</strong> High - Fraud prevention</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 25 vendor onboarding files</li>
<li><strong>Include:</strong> All vendors with payments >$100,000 (100%)</li>
<li><strong>Selection:</strong> Random sample by payment volume</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Vendor Registration Forms</li>
<li>KYC documentation (Tax ID, bank verification)</li>
<li>Conflict of Interest declarations</li>
<li>Procurement Committee approvals</li>
<li>Vendor approval workflow logs</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify all mandatory fields completed on registration forms</li>
<li>Check bank account verification performed</li>
<li>Verify conflict of interest declarations obtained</li>
<li>Check strategic vendors have Committee approval</li>
<li>Verify vendor approval workflow enforced</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Completeness:</strong> All KYC documents on file</li>
<li><strong>Authorization:</strong> Proper approval hierarchy</li>
<li><strong>Integrity:</strong> No conflicts of interest</li>
</ul>

<p><em><strong>⚠️ Fraud Alert:</strong> Verify vendor bank accounts don't match employee accounts.</em></p>
HTML;
    }

    private static function exp03Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-03 Purchase Order Authorization</h3>
<p><strong>COSO Principle:</strong> Control Activities (P11) | <strong>SOX Relevance:</strong> Medium</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 40 purchase orders</li>
<li><strong>Selection:</strong> Systematic random sample</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Purchase orders issued by system</li>
<li>Approved requisitions linked to POs</li>
<li>PO approval workflow logs</li>
<li>Budget availability checks</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify POs generated only from approved requisitions</li>
<li>Check system enforces approval limits</li>
<li>Verify POs within approved budget</li>
<li>Test sequential PO numbering</li>
<li>Check PO modifications have approval trail</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Authorization:</strong> POs properly approved</li>
<li><strong>Budget:</strong> Within approved amounts</li>
<li><strong>Traceability:</strong> Linked to requisitions</li>
</ul>
HTML;
    }

    private static function exp04Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-04 Three-Way Match Control</h3>
<p><strong>COSO Principle:</strong> Control Activities (P11) | <strong>SOX Relevance:</strong> Critical</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 45 invoices</li>
<li><strong>Stratification:</strong>
    <ul>
    <li>30 matched invoices (random)</li>
    <li>15 exception invoices (deliberate selection)</li>
    </ul>
</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>PO, Receiving Report, and Invoice (3-way match set)</li>
<li>Three-way match exception reports</li>
<li>Manual override approvals</li>
<li>System match logs</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify PO, Receipt, and Invoice quantities match within tolerance</li>
<li>Check prices match approved PO prices</li>
<li>Verify exceptions >1% or >$100 have manual approval</li>
<li>Test system blocks payment for unmatched items</li>
<li>Verify receiving report is signed and dated</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th></tr>
<tr><td>Match Accuracy</td><td>Qty/Price within tolerance</td></tr>
<tr><td>Exception Handling</td><td>Approved overrides documented</td></tr>
<tr><td>Segregation</td><td>Receipt independent of AP</td></tr>
</table>
HTML;
    }

    private static function exp05Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-05 Invoice Processing and Coding</h3>
<p><strong>COSO Principle:</strong> Control Activities (P11) | <strong>SOX Relevance:</strong> High</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 35 invoices</li>
<li><strong>Selection:</strong> Random + focus on cutoff dates</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Vendor invoices with approval stamps</li>
<li>OCR validation reports</li>
<li>GL coding sheets</li>
<li>Duplicate invoice check reports</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify invoice amounts match supporting PO/receipt</li>
<li>Check GL coding is appropriate for expense type</li>
<li>Verify mathematical accuracy of invoice calculations</li>
<li>Confirm duplicate invoice check performed</li>
<li>Check invoice date for proper period recording</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Accuracy:</strong> Invoice amounts correct</li>
<li><strong>Classification:</strong> Proper GL coding</li>
<li><strong>Cut-off:</strong> Recorded in correct period</li>
</ul>
HTML;
    }

    private static function exp06Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-06 Payment Authorization and Processing</h3>
<p><strong>COSO Principle:</strong> Control Activities (P11) | <strong>SOX Relevance:</strong> Critical - Cash disbursement</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 40 payments</li>
<li><strong>Stratification:</strong>
    <ul>
    <li>20 wire transfers</li>
    <li>15 ACH payments</li>
    <li>5 checks (if applicable)</li>
    </ul>
</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Payment batch approval forms</li>
<li>Bank Positive Pay reports</li>
<li>Payment registers from ERP</li>
<li>Voided payment documentation</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify payment batch approved by Treasury Manager</li>
<li>Check high-value payments (>$100K) have CFO approval</li>
<li>Verify payee bank details match approved vendor file</li>
<li>Test Positive Pay file submitted to bank</li>
<li>Verify segregation: Invoice processing ≠ Payment authorization</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th><th>Fraud Protection</th></tr>
<tr><td>Dual Authorization</td><td>High-value has 2 approvals</td><td>Prevents fraud</td></tr>
<tr><td>Payee Verification</td><td>Bank details match VMF</td><td>Prevents redirection</td></tr>
<tr><td>SOD</td><td>Preparer ≠ Approver ≠ Releaser</td><td>Requires collusion</td></tr>
</table>

<p><em><strong>⚠️ Critical Warning:</strong> Payment processing is final checkpoint. Extra scrutiny for bank account changes and rush payments.</em></p>
HTML;
    }

    private static function exp07Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-07 Expense Accruals</h3>
<p><strong>COSO Principle:</strong> Control Activities (P12) | <strong>SOX Relevance:</strong> Critical - GAAP compliance</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 accrual entries</li>
<li><strong>Include:</strong> All accruals >$50,000 (100%)</li>
<li><strong>Include:</strong> All reversing accruals</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Accrual support worksheets with calculations</li>
<li>Receiving reports for un-invoiced receipts</li>
<li>Service period documentation</li>
<li>Accrual approval forms</li>
<li>Prior period comparative data</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Recalculate accrual amount based on supporting data</li>
<li>Compare to prior period for reasonableness</li>
<li>Verify accruals reverse in subsequent period</li>
<li>Compare actual invoice to estimated accrual</li>
<li>Investigate variances >10% or $5,000</li>
<li>Verify Controller/CFO approval for material accruals</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Completeness:</strong> All un-invoiced receipts accrued</li>
<li><strong>Accuracy:</strong> Estimate within 10% of actual</li>
<li><strong>Timing:</strong> Accruals reverse next period</li>
</ul>

<p><em><strong>GAAP Requirement:</strong> Accrual accounting requires expenses recognized when incurred, not when paid.</em></p>
HTML;
    }

    private static function exp08Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: EXP-08 Vendor Master File Changes</h3>
<p><strong>COSO Principle:</strong> General IT Controls | <strong>SOX Relevance:</strong> Critical - Fraud prevention</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 35 VMF changes</li>
<li><strong>Include:</strong> 100% of bank account changes for active vendors >$50K</li>
<li><strong>Include:</strong> All vendor reactivations</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Vendor Master File Change Request Forms</li>
<li>Bank account verification letters</li>
<li>Dual approval evidence</li>
<li>System change logs</li>
<li>Independent verification call records</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify all changes have documented business justification</li>
<li>Check dual approval (Procurement + Finance) obtained</li>
<li>Verify independent confirmation with vendor for bank changes</li>
<li>Check new bank account not associated with employee names</li>
<li>Review system access for VMF maintenance roles</li>
</ol>

<h4>✅ Attributes to Test</h4>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr style="background-color: #f0f0f0;"><th>Attribute</th><th>Pass Criteria</th><th>Fraud Prevention</th></tr>
<tr><td>Dual Authorization</td><td>Two approvals for all changes</td><td>Prevents single-person fraud</td></tr>
<tr><td>Independent Verification</td><td>Bank changes verified with vendor</td><td>Prevents redirection</td></tr>
<tr><td>Audit Logging</td><td>All changes logged</td><td>Enables investigation</td></tr>
</table>

<p><em><strong>Red Flags:</strong> Vendor bank = employee bank, multiple vendors same account, PO Box addresses, vendors added before large payments.</em></p>
HTML;
    }

    private static function defaultEvidence(string $code): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: {$code}</h3>
<p>Please refer to standard ICoFR testing procedures.</p>
HTML;
    }
}
