<?php

namespace Database\Seeders\ICoFR;

/**
 * Revenue Cycle Evidence Plans
 * Detailed SOX-compliant evidence plans for Revenue Recognition controls
 */
class RevenueEvidencePlans
{
    public static function get(string $code): string
    {
        return match($code) {
            'RC-01' => self::rc01Evidence(),
            'RC-02' => self::rc02Evidence(),
            'RC-03' => self::rc03Evidence(),
            'RC-04' => self::rc04Evidence(),
            default => self::defaultEvidence($code),
        };
    }

    private static function rc01Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: RC-01 Revenue Cut-off and Timing Control</h3>
<p><strong>COSO Principle:</strong> Control Activities (P10) | <strong>SOX Relevance:</strong> Critical - Prevents period misstatement</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 25 transactions (15 pre-year-end + 10 post-year-end)</li>
<li><strong>Selection:</strong> All transactions 3 days before/after fiscal year-end >$25,000</li>
<li><strong>Stratification:</strong> By business unit (min 5 per division)</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Sales invoices with shipping documents (BOL, delivery receipts)</li>
<li>Customer acceptance confirmations (for services)</li>
<li>Period-end cut-off memo with management sign-off</li>
<li>ASC 606/IFRS 15 contract review documentation</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Trace sales invoices to shipping documents - verify delivery date matches posting period</li>
<li>Review customer acceptance documentation where contractually required</li>
<li>Verify revenue posted to correct GL period (not deferred)</li>
<li>Test for subsequent credit memos indicating cut-off errors</li>
<li>Review Cut-off Memo sign-off by Controller/CFO</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Completeness:</strong> All revenue earned recorded</li>
<li><strong>Cut-off:</strong> Revenue in correct accounting period</li>
<li><strong>Occurrence:</strong> Revenue from valid transactions</li>
</ul>

<p><em><strong>SOX 404:</strong> Deficiencies in cut-off may indicate material weakness. Retain evidence 7 years.</em></p>
HTML;
    }

    private static function rc02Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: RC-02 Revenue Accuracy and Pricing Authorization</h3>
<p><strong>COSO Principle:</strong> Control Activities (P10) | <strong>SOX Relevance:</strong> High - Prevents revenue overstatement</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 transactions</li>
<li><strong>Stratification:</strong>
    <ul>
    <li>Tier 1: >$100,000 (15 samples)</li>
    <li>Tier 2: $25,000-$100,000 (10 samples)</li>
    <li>Tier 3: <$25,000 (5 samples)</li>
    </ul>
</li>
<li><strong>Include:</strong> All manual price overrides and discounts >10%</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Sales invoices with line item detail</li>
<li>Approved price lists / master price file extracts</li>
<li>Customer contracts with pricing terms</li>
<li>Discount authorization forms</li>
<li>Sales order approval workflow logs</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Compare invoice prices to approved price lists - investigate variances</li>
<li>Verify discounts >5% have Sales Manager approval</li>
<li>Recalculate invoice totals (qty × price - discount + tax)</li>
<li>Verify FOB terms considered for revenue timing</li>
<li>For multi-currency: verify exchange rates match approved rates</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Accuracy:</strong> Mathematical correctness</li>
<li><strong>Authorization:</strong> Approval for price deviations</li>
<li><strong>Valuation:</strong> Correct discounts and FX rates</li>
</ul>
HTML;
    }

    private static function rc03Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: RC-03 Returns, Allowances, and Credit Memo Control</h3>
<p><strong>COSO Principle:</strong> Control Activities (P10) | <strong>SOX Relevance:</strong> High - Affects revenue valuation</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All credit memos >$10,000 + random 20 smaller items</li>
<li><strong>Focus:</strong> Credit memos within 30 days of year-end</li>
<li><strong>Include:</strong> All write-offs >$5,000</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Credit memo requests with business justification</li>
<li>Return Material Authorization (RMA) forms</li>
<li>Receiving reports for returned goods</li>
<li>Credit memo approval workflow logs</li>
<li>Sales return reserve calculations</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify credit memos >$5,000 have dual approval (Sales + Finance Manager)</li>
<li>Review business justification for reasonableness</li>
<li>Verify credit memos recorded in same period as return event</li>
<li>For goods returns: verify receiving reports exist</li>
<li>Recalculate sales return reserve vs. actual rates</li>
<li>Trace credit memos to original sales invoices</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Authorization:</strong> Proper approval levels</li>
<li><strong>Timing:</strong> Recorded in correct period</li>
<li><strong>Valuation:</strong> Reserve estimates reasonable</li>
</ul>
HTML;
    }

    private static function rc04Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: RC-04 Contract Review and Performance Obligation</h3>
<p><strong>COSO Principle:</strong> Control Activities (P10) | <strong>SOX Relevance:</strong> Critical - ASC 606 compliance</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All contracts >$500,000 + 10 random $100K-$500K</li>
<li><strong>Include:</strong> All multi-element arrangements</li>
<li><strong>Include:</strong> All contracts with variable consideration</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Executed customer contracts with amendments</li>
<li>ASC 606 revenue recognition checklists</li>
<li>Standalone selling price (SSP) analysis</li>
<li>Performance obligation evaluation memos</li>
<li>Contract modification assessments</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Review contracts for key terms: deliverables, acceptance criteria, termination</li>
<li>Verify identification of distinct performance obligations</li>
<li>Check SSP analysis based on historical data</li>
<li>Verify revenue timing: over-time vs point-in-time</li>
<li>For variable consideration: assess estimation methodology</li>
<li>For modifications: verify proper accounting treatment</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Classification:</strong> Proper PO identification</li>
<li><strong>Accuracy:</strong> Correct allocation of transaction price</li>
<li><strong>Completeness:</strong> All contract terms considered</li>
</ul>
HTML;
    }

    private static function defaultEvidence(string $code): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: {$code}</h3>
<p><strong>COSO Principle:</strong> Control Activities | <strong>SOX Relevance:</strong> Review Required</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 25-30 items</li>
<li><strong>Selection:</strong> Random sample stratified by value</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Control documentation and SOPs</li>
<li>Evidence of control execution</li>
<li>Review and approval documentation</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify control is designed appropriately</li>
<li>Test control operates as designed</li>
<li>Verify evidence is complete and accurate</li>
<li>Review control owner sign-offs</li>
</ol>
HTML;
    }
}
