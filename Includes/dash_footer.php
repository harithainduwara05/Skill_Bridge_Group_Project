<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="javascript:void(0)" onclick="openFooterPolicyModal('privacy')">Privacy Policy</a>
        <a href="javascript:void(0)" onclick="openFooterPolicyModal('terms')">Terms of Service</a>
    </div>
</footer>
</div><!-- end main-wrapper -->

<!-- Reusable Footer Policy & Rules Modal -->
<style>
    .sb-policy-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(2px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .sb-policy-backdrop.show {
        display: flex;
    }
    .sb-policy-dialog {
        background: #ffffff;
        border-radius: 14px;
        width: 100%;
        max-width: 600px;
        max-height: 82vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
        overflow: hidden;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .sb-policy-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }
    .sb-policy-header h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #0b2246;
    }
    .sb-policy-close {
        background: transparent;
        border: none;
        font-size: 22px;
        line-height: 1;
        color: #64748b;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .sb-policy-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .sb-policy-body {
        padding: 20px 22px;
        overflow-y: auto;
        color: #334155;
        font-size: 13.5px;
        line-height: 1.65;
    }
    .sb-policy-body h4 {
        margin: 14px 0 6px;
        font-size: 14px;
        font-weight: 700;
        color: #0b2246;
    }
    .sb-policy-body h4:first-child {
        margin-top: 0;
    }
    .sb-policy-body ul {
        margin: 0 0 10px 18px;
        padding: 0;
    }
    .sb-policy-body li {
        margin-bottom: 5px;
    }
    .sb-policy-footer {
        padding: 14px 22px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        background: #ffffff;
    }
    .sb-policy-btn {
        padding: 8px 18px;
        background: #0b2246;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .sb-policy-btn:hover {
        background: #16305b;
    }
</style>

<div class="sb-policy-backdrop" id="sbFooterPolicyModal" role="dialog" aria-modal="true" aria-labelledby="sbPolicyTitle">
    <div class="sb-policy-dialog">
        <div class="sb-policy-header">
            <h3 id="sbPolicyTitle">SkillBridge Policy</h3>
            <button type="button" class="sb-policy-close" onclick="closeFooterPolicyModal()" aria-label="Close">&times;</button>
        </div>
        <div class="sb-policy-body" id="sbPolicyBody"></div>
        <div class="sb-policy-footer">
            <button type="button" class="sb-policy-btn" onclick="closeFooterPolicyModal()">Close</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const policyContent = {
            privacy: {
                title: 'SkillBridge Privacy Policy',
                html: `
                    <h4>1. Information We Collect</h4>
                    <ul>
                        <li><strong>Student Profiles:</strong> Academic email, university, degree details, skills, portfolios, and uploaded resumes/certificates.</li>
                        <li><strong>Organizations &amp; Companies:</strong> Official contact details, company profiles, internship listings, and project briefs.</li>
                    </ul>
                    <h4>2. How We Use Your Data</h4>
                    <ul>
                        <li>Matching verified university students with industry internships and collaborative projects.</li>
                        <li>Verifying institutional eligibility via official university email domains.</li>
                        <li>Enabling administrative moderation, grievance resolution, and platform analytics.</li>
                    </ul>
                    <h4>3. Data Protection &amp; Access Control</h4>
                    <ul>
                        <li>Student resumes and contact details are only shared with companies/organizations when a student applies or submits a proposal.</li>
                        <li>Passwords are stored securely using cryptographic hashing, and role-based session guards restrict unauthorized access.</li>
                    </ul>
                `
            },
            terms: {
                title: 'Terms of Service & Platform Rules',
                html: `
                    <h4>1. Account Eligibility &amp; Verification</h4>
                    <ul>
                        <li>Students must register using an approved university domain email and provide accurate academic records.</li>
                        <li>Companies and partner organizations must undergo admin verification before publishing live opportunities.</li>
                    </ul>
                    <h4>2. Project &amp; Internship Moderation Rules</h4>
                    <ul>
                        <li><strong>Internships (7-Day Suspension Grace Period):</strong> A suspended internship listing is given <strong>7 days</strong> to resolve the reported violation and submit a valid reactivation request. If the employer fails to fix the issue within the given time, the listing will be <strong>permanently rejected/terminated</strong>.</li>
                        <li><strong>Permanent Internship Termination:</strong> An internship that has been <strong>permanently terminated</strong> will <strong>never be reopened</strong> under any circumstances.</li>
                        <li><strong>Projects (7-Day Hold Grace Period):</strong> A project placed <strong>On Hold</strong> is given <strong>7 days</strong> to address the feedback and respond. If the organization fails to resolve the issue within the given time, the project will be <strong>permanently rejected</strong>.</li>
                        <li><strong>Permanent Project Rejection:</strong> A project that has been <strong>permanently rejected</strong> will <strong>never be reopened</strong> under any circumstances.</li>
                    </ul>
                    <h4>3. Code of Conduct &amp; Grievances</h4>
                    <ul>
                        <li>Users must maintain professional communication across teams, interviews, and mentorship interactions.</li>
                        <li>Violations, spam, or disputes can be reported through the Complaints system for administrative review and resolution.</li>
                    </ul>
                `
            }
        };

        window.openFooterPolicyModal = function (type) {
            const modal = document.getElementById('sbFooterPolicyModal');
            const titleEl = document.getElementById('sbPolicyTitle');
            const bodyEl = document.getElementById('sbPolicyBody');
            const data = policyContent[type] || policyContent.terms;
            if (!modal || !titleEl || !bodyEl) return;

            titleEl.textContent = data.title;
            bodyEl.innerHTML = data.html;
            modal.classList.add('show');
        };

        window.closeFooterPolicyModal = function () {
            const modal = document.getElementById('sbFooterPolicyModal');
            if (modal) modal.classList.remove('show');
        };

        document.addEventListener('click', function (e) {
            const modal = document.getElementById('sbFooterPolicyModal');
            if (modal && e.target === modal) {
                closeFooterPolicyModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeFooterPolicyModal();
            }
        });
    })();
</script>
</body>
</html>
