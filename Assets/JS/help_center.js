/**
 * ==============================================================================
 * SkillBridge - Help & Support Center JavaScript Engine
 * Live search, modal workflows, AJAX complaint lodging, and knowledge base reader.
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. Element Selectors
    // --------------------------------------------------------------------------
    const searchInput = document.getElementById('hcSearchInput');
    const tagPills = document.querySelectorAll('.hc-tag-pill');
    const tableBody = document.getElementById('hcComplaintsTableBody');
    const emptyState = document.getElementById('hcEmptyState');
    const countNumber = document.getElementById('hcCountNumber');

    // Modals
    const lodgeModal = document.getElementById('hcLodgeModal');
    const detailModal = document.getElementById('hcDetailModal');
    const knowledgeModal = document.getElementById('hcKnowledgeModal');

    // Open Lodge Buttons
    const btnNavLodge = document.getElementById('hcNavLodgeBtn');
    const btnHeroLodge = document.getElementById('hcHeroLodgeBtn');
    const btnEmptyLodge = document.getElementById('hcEmptyLodgeBtn');
    const btnKnowledgeCTA = document.getElementById('btnKnowledgeLodgeCTA');

    // Close Modal Buttons
    const btnCloseLodge = document.getElementById('btnCloseLodgeModal');
    const btnCancelLodge = document.getElementById('btnCancelLodge');
    const btnCloseDetail = document.getElementById('btnCloseDetailModal');
    const btnCloseDetailFooter = document.getElementById('btnCloseDetailFooter');
    const btnCloseKnowledge = document.getElementById('btnCloseKnowledgeModal');
    const btnCloseKnowledgeFooter = document.getElementById('btnCloseKnowledgeFooter');

    // Form
    const lodgeForm = document.getElementById('hcLodgeComplaintForm');
    const btnSubmitLodge = document.getElementById('btnSubmitLodge');

    // Toast
    const toast = document.getElementById('hcToast');
    const toastMsg = document.getElementById('hcToastMsg');

    // Knowledge Cards
    const knowledgeCards = document.querySelectorAll('.hc-knowledge-card');
    const btnEscalation = document.getElementById('btnEscalationPolicy');

    // Dynamic Priority Auto-Assignment Elements
    const categorySelect = document.getElementById('hcLodgeCategory');
    const priorityPill = document.getElementById('hcPriorityPill');
    const priorityReason = document.getElementById('hcPriorityReason');
    const priorityInput = document.getElementById('hcLodgePriority');

    const priorityRules = {
        'Account & Access': {
            level: 'URGENT',
            cssClass: 'priority-urgent',
            reason: 'Account lock or authentication access disruption'
        },
        'Technical': {
            level: 'HIGH',
            cssClass: 'priority-high',
            reason: 'System bug or operational platform impediment'
        },
        'Organization Dispute': {
            level: 'HIGH',
            cssClass: 'priority-high',
            reason: 'Interpersonal or organizational dispute requiring arbitration'
        },
        'Project Milestones & Review': {
            level: 'MEDIUM',
            cssClass: 'priority-medium',
            reason: 'Deliverable submission and milestone feedback review'
        },
        'Academic': {
            level: 'MEDIUM',
            cssClass: 'priority-medium',
            reason: 'Faculty and university academic credit evaluation'
        },
        'Other': {
            level: 'LOW',
            cssClass: 'priority-low',
            reason: 'General inquiry and standard administrative guidance'
        }
    };

    function updateAssignedPriority() {
        if (!categorySelect || !priorityPill || !priorityInput) return;
        const selected = categorySelect.value;
        const rule = priorityRules[selected] || priorityRules['Technical'];

        priorityPill.className = `hc-priority-pill ${rule.cssClass}`;
        priorityPill.textContent = rule.level;
        if (priorityReason) priorityReason.textContent = rule.reason;
        priorityInput.value = rule.level;
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', updateAssignedPriority);
        updateAssignedPriority();
    }

    // --------------------------------------------------------------------------
    // 2. Modal Helper Functions
    // --------------------------------------------------------------------------
    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('show');
        if (!document.querySelector('.hc-modal-backdrop.show')) {
            document.body.style.overflow = '';
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.hc-modal-backdrop.show').forEach(m => m.classList.remove('show'));
        document.body.style.overflow = '';
    }

    // Modal Triggers
    if (btnNavLodge) btnNavLodge.addEventListener('click', () => openModal(lodgeModal));
    if (btnHeroLodge) btnHeroLodge.addEventListener('click', () => openModal(lodgeModal));
    if (btnEmptyLodge) btnEmptyLodge.addEventListener('click', () => openModal(lodgeModal));
    if (btnKnowledgeCTA) btnKnowledgeCTA.addEventListener('click', () => {
        closeModal(knowledgeModal);
        openModal(lodgeModal);
    });

    if (btnCloseLodge) btnCloseLodge.addEventListener('click', () => closeModal(lodgeModal));
    if (btnCancelLodge) btnCancelLodge.addEventListener('click', () => closeModal(lodgeModal));
    if (btnCloseDetail) btnCloseDetail.addEventListener('click', () => closeModal(detailModal));
    if (btnCloseDetailFooter) btnCloseDetailFooter.addEventListener('click', () => closeModal(detailModal));
    if (btnCloseKnowledge) btnCloseKnowledge.addEventListener('click', () => closeModal(knowledgeModal));
    if (btnCloseKnowledgeFooter) btnCloseKnowledgeFooter.addEventListener('click', () => closeModal(knowledgeModal));

    // Close on backdrop click
    [lodgeModal, detailModal, knowledgeModal].forEach(m => {
        if (m) {
            m.addEventListener('click', (e) => {
                if (e.target === m) closeModal(m);
            });
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAllModals();
    });

    // --------------------------------------------------------------------------
    // 3. Toast Notification Helper
    // --------------------------------------------------------------------------
    let toastTimeout = null;
    function showToast(message, isError = false) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        toast.style.backgroundColor = isError ? '#991b1b' : '#0f172a';
        toast.classList.add('show');

        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 4000);
    }

    // --------------------------------------------------------------------------
    // 4. Search & Filter Engine
    // --------------------------------------------------------------------------
    let activeCategoryFilter = 'all';

    function filterTable() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const rows = document.querySelectorAll('#hcComplaintsTableBody tr.hc-data-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowCase = (row.dataset.case || '').toLowerCase();
            const rowTitle = (row.dataset.title || '').toLowerCase();
            const rowCategory = (row.dataset.category || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const rowPriority = (row.dataset.priority || '').toLowerCase();
            const rowDesc = (row.dataset.desc || '').toLowerCase();

            // Match text search
            const matchesQuery = !query || 
                rowCase.includes(query) || 
                rowTitle.includes(query) || 
                rowCategory.includes(query) || 
                rowStatus.includes(query) ||
                rowPriority.includes(query) ||
                rowDesc.includes(query);

            // Match category pill
            let matchesCategory = true;
            if (activeCategoryFilter !== 'all') {
                const targetCat = activeCategoryFilter.toLowerCase();
                matchesCategory = rowCategory.includes(targetCat);
            }

            if (matchesQuery && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update counter and empty state
        if (countNumber) countNumber.textContent = visibleCount;
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    // Tag pills click
    tagPills.forEach(pill => {
        pill.addEventListener('click', () => {
            tagPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            activeCategoryFilter = pill.dataset.filter || 'all';
            filterTable();
        });
    });

    // --------------------------------------------------------------------------
    // 5. AJAX Form Submission: Lodge Complaint
    // --------------------------------------------------------------------------
    if (lodgeForm) {
        lodgeForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('hcLodgeEmail').value.trim();
            const title = document.getElementById('hcLodgeTitle').value.trim();
            const category = document.getElementById('hcLodgeCategory').value;
            const priority = document.getElementById('hcLodgePriority').value;
            const description = document.getElementById('hcLodgeDesc').value.trim();

            if (!email || !title || !description) {
                showToast('Please fill in all required fields.', true);
                return;
            }

            // Disable button during submit
            const originalBtnHtml = btnSubmitLodge.innerHTML;
            btnSubmitLodge.disabled = true;
            btnSubmitLodge.innerHTML = `
                <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size:18px;">autorenew</span>
                <span>Submitting...</span>
            `;

            const formData = new FormData();
            formData.append('ajax_action', 'lodge_complaint');
            formData.append('email', email);
            formData.append('title', title);
            formData.append('category', category);
            formData.append('priority', priority);
            formData.append('discription', description);

            fetch('/Skill_Bridge_Group_Project/help_center.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSubmitLodge.disabled = false;
                btnSubmitLodge.innerHTML = originalBtnHtml;

                if (data.success && data.complaint) {
                    showToast(data.message || 'Complaint lodged successfully!');
                    closeModal(lodgeModal);

                    // Prepend new row to the table
                    prependComplaintRow(data.complaint);

                    // Reset form fields
                    lodgeForm.reset();
                    updateAssignedPriority();
                    // Keep email filled if user is logged in
                    const loggedInEmail = lodgeModal.dataset.userEmail;
                    if (loggedInEmail) {
                        document.getElementById('hcLodgeEmail').value = loggedInEmail;
                    }

                    // Re-run filter
                    filterTable();

                    // Smooth scroll to table
                    const compSection = document.getElementById('complaintsSection');
                    if (compSection) {
                        compSection.scrollIntoView({ behavior: 'smooth' });
                    }
                } else {
                    showToast(data.message || 'Failed to lodge complaint. Please check your inputs.', true);
                }
            })
            .catch(err => {
                btnSubmitLodge.disabled = false;
                btnSubmitLodge.innerHTML = originalBtnHtml;
                console.error('Submission error:', err);
                showToast('A network error occurred. Please try again.', true);
            });
        });
    }

    // Helper: Prepend newly created row
    function prependComplaintRow(comp) {
        if (!tableBody) return;

        let statusClass = 'status-pending';
        let statusLabel = 'Pending';
        if (comp.status === 'IN_REVIEW') { statusClass = 'status-review'; statusLabel = 'In Review'; }
        else if (comp.status === 'RESOLVED') { statusClass = 'status-resolved'; statusLabel = 'Resolved'; }
        else if (comp.status === 'DISMISSED') { statusClass = 'status-dismissed'; statusLabel = 'Dismissed'; }

        let priorityClass = 'priority-medium';
        if (comp.priority === 'LOW') priorityClass = 'priority-low';
        if (comp.priority === 'HIGH') priorityClass = 'priority-high';
        if (comp.priority === 'URGENT') priorityClass = 'priority-urgent';

        const tr = document.createElement('tr');
        tr.className = 'hc-data-row';
        tr.style.backgroundColor = '#fff7ed';
        tr.dataset.id = comp.id;
        tr.dataset.case = comp.case_id;
        tr.dataset.title = comp.title;
        tr.dataset.category = comp.category;
        tr.dataset.priority = comp.priority;
        tr.dataset.status = comp.status;
        tr.dataset.email = comp.email;
        tr.dataset.date = comp.submitted_date;
        tr.dataset.desc = comp.discription;
        tr.dataset.notes = comp.resolution_notes || '';

        tr.innerHTML = `
            <td>
                <span class="hc-case-id">${comp.case_id}</span>
            </td>
            <td>
                <div class="hc-meta-title">${comp.title}</div>
                <div class="hc-meta-category">
                    <span class="material-symbols-outlined" style="font-size:13px; vertical-align:-2px;">folder_open</span>
                    Category: ${comp.category}
                </div>
            </td>
            <td style="color: #64748b; font-size: 12.5px; white-space: nowrap;">
                ${comp.submitted_date}
            </td>
            <td>
                <span class="hc-priority-pill ${priorityClass}">${comp.priority}</span>
            </td>
            <td>
                <span class="hc-status-pill ${statusClass}">
                    <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor;"></span>
                    ${statusLabel}
                </span>
            </td>
            <td style="text-align: right;">
                <button type="button" class="hc-btn-view-details" data-id="${comp.id}">
                    <span>View Details</span>
                    <span class="material-symbols-outlined" style="font-size: 15px;">chevron_right</span>
                </button>
            </td>
        `;

        tableBody.prepend(tr);

        // Attach click listener to the newly created button
        const newBtn = tr.querySelector('.hc-btn-view-details');
        if (newBtn) {
            newBtn.addEventListener('click', () => populateAndOpenDetail(tr));
        }

        // Fade background highlight to normal after 3 seconds
        setTimeout(() => {
            tr.style.transition = 'background-color 1s ease';
            tr.style.backgroundColor = '';
        }, 3000);
    }

    // --------------------------------------------------------------------------
    // 6. View Complaint Details Modal
    // --------------------------------------------------------------------------
    function populateAndOpenDetail(row) {
        if (!row) return;

        const caseId = row.dataset.case || '#CMP-0000';
        const title = row.dataset.title || 'Untitled Complaint';
        const category = row.dataset.category || 'General';
        const priority = row.dataset.priority || 'MEDIUM';
        const status = row.dataset.status || 'PENDING';
        const email = row.dataset.email || '-';
        const date = row.dataset.date || '-';
        const desc = row.dataset.desc || 'No description provided.';
        const notes = row.dataset.notes || '';

        // Header
        document.getElementById('hcDetailModalTitle').textContent = title;
        document.getElementById('hcDetailCaseId').textContent = caseId;

        // Status Badge
        const statusBadge = document.getElementById('hcDetailStatusBadge');
        let statusClass = 'status-pending';
        let statusLabel = 'Pending';
        if (status === 'IN_REVIEW') { statusClass = 'status-review'; statusLabel = 'In Review'; }
        else if (status === 'RESOLVED') { statusClass = 'status-resolved'; statusLabel = 'Resolved'; }
        else if (status === 'DISMISSED') { statusClass = 'status-dismissed'; statusLabel = 'Dismissed'; }
        statusBadge.className = `hc-status-pill ${statusClass}`;
        statusBadge.innerHTML = `<span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor;"></span> ${statusLabel}`;

        // Priority Badge
        const priorityBadge = document.getElementById('hcDetailPriorityBadge');
        let priorityClass = 'priority-medium';
        if (priority === 'LOW') priorityClass = 'priority-low';
        if (priority === 'HIGH') priorityClass = 'priority-high';
        if (priority === 'URGENT') priorityClass = 'priority-urgent';
        priorityBadge.className = `hc-priority-pill ${priorityClass}`;
        priorityBadge.textContent = priority;

        // Fields
        document.getElementById('hcDetailCategory').textContent = category;
        document.getElementById('hcDetailDate').textContent = date;
        document.getElementById('hcDetailEmail').textContent = email;
        document.getElementById('hcDetailDescription').textContent = desc;

        // Resolution Notes / Dismissal Reason
        const notesEl = document.getElementById('hcDetailResolutionNotes');
        const notesLabelEl = document.getElementById('hcDetailResolutionLabel');

        if (status === 'DISMISSED') {
            if (notesLabelEl) {
                notesLabelEl.style.color = '#b91c1c';
                notesLabelEl.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px; vertical-align:-2px;">cancel</span> Dismissal Reason & Administrative Findings`;
            }
            if (notesEl) {
                notesEl.textContent = (notes && notes.trim() !== '') 
                    ? notes 
                    : 'This complaint was dismissed by administrators. No further action was deemed required.';
                notesEl.style.backgroundColor = '#fef2f2';
                notesEl.style.borderColor = '#fecaca';
                notesEl.style.color = '#991b1b';
            }
        } else if (status === 'RESOLVED') {
            if (notesLabelEl) {
                notesLabelEl.style.color = '#065f46';
                notesLabelEl.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px; vertical-align:-2px;">verified</span> Admin Resolution & Audit Log`;
            }
            if (notesEl) {
                notesEl.textContent = (notes && notes.trim() !== '') ? notes : 'Issue resolved and verified by SkillBridge administration.';
                notesEl.style.backgroundColor = '#ecfdf5';
                notesEl.style.borderColor = '#a7f3d0';
                notesEl.style.color = '#065f46';
            }
        } else {
            // PENDING or IN_REVIEW
            if (notesLabelEl) {
                notesLabelEl.style.color = '#0369a1';
                notesLabelEl.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px; vertical-align:-2px;">sync</span> Investigation Progress & Notes`;
            }
            if (notesEl) {
                notesEl.textContent = (notes && notes.trim() !== '') 
                    ? notes 
                    : 'Awaiting administrative investigation and formal notes. Our triage team is actively processing your ticket.';
                notesEl.style.backgroundColor = '#f8fafc';
                notesEl.style.borderColor = '#e2e8f0';
                notesEl.style.color = '#64748b';
            }
        }

        openModal(detailModal);
    }

    // Attach click listeners to all existing "View Details" buttons
    document.querySelectorAll('.hc-btn-view-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr.hc-data-row');
            if (row) populateAndOpenDetail(row);
        });
    });

    // --------------------------------------------------------------------------
    // 7. Self-Help Knowledge Base Reader
    // --------------------------------------------------------------------------
    const knowledgeGuides = {
        security: {
            title: 'Account Security & Access',
            subtitle: 'Solutions for credential recovery, university email authentication, and 2FA',
            articles: [
                {
                    q: 'How do I reset my account password?',
                    a: 'Go to the login screen and click "Forgot Password". Enter your registered university or institutional email to receive an instant verification link.'
                },
                {
                    q: 'My institutional university email is not recognized',
                    a: 'SkillBridge validates domains against partnered Sri Lankan universities (e.g. stu.ucsc.cmb.ac.lk, sliit.lk). If your domain is new, reach out to your faculty administrator or lodge a ticket under the Academic category.'
                },
                {
                    q: 'How to revoke compromised active sessions?',
                    a: 'Navigate to your Dashboard Profile -> Security Settings and click "Log Out All Other Devices". All active tokens will be immediately invalidated.'
                },
                {
                    q: 'Role permission mismatches (Student vs Organization)',
                    a: 'If your account was incorrectly registered under the wrong portal tier, lodge a formal complaint with category "Account & Access" for an admin role elevation audit.'
                }
            ]
        },
        skills: {
            title: 'Skills, Milestones & Mentorship',
            subtitle: '100% Free educational platform policy, milestone evaluation, skill badges, and certificates',
            articles: [
                {
                    q: 'Is SkillBridge completely free for students and organizations?',
                    a: 'Yes! SkillBridge is a 100% free skill development and academic collaboration platform. No fees are charged for posting projects or internships, and no monetary transactions are permitted on the platform.'
                },
                {
                    q: 'Can organizations pay or charge students for projects?',
                    a: 'No. Projects on SkillBridge are non-monetary academic learning opportunities designed strictly for student skill development, hands-on portfolio building, and academic recognition. Monetary transactions are strictly forbidden.'
                },
                {
                    q: 'How are project milestones reviewed and verified?',
                    a: 'Students submit deliverable files and links through their project dashboard. Assigned industry mentors and university supervisors review submissions and approve milestones.'
                },
                {
                    q: 'How do students earn verified skill badges and certificates?',
                    a: 'Upon successful mentor verification of all project milestones, verified digital skill badges and completion certificates are automatically awarded to the student\'s portfolio.'
                }
            ]
        },
        billing: null,
        platform: {
            title: 'Platform Guide & Project Workflows',
            subtitle: 'Step-by-step guidance for proposals, internship matching, and deliverable review',
            articles: [
                {
                    q: 'How do I submit a project proposal?',
                    a: 'Open the Projects catalog, select the target opportunity, and click "Submit Proposal". Upload your technical proposal PDF and outline your team member roles.'
                },
                {
                    q: 'How does the Internship Application process work?',
                    a: 'Browse available internship posts, review requirements, and click "Apply". Ensure your CV/portfolio link is updated before confirming submission.'
                },
                {
                    q: 'Can students collaborate across different universities?',
                    a: 'Yes, SkillBridge supports cross-faculty and cross-university agile squads for open industry projects.'
                },
                {
                    q: 'File upload limits and format restrictions',
                    a: 'Supported formats include PDF, DOCX, ZIP, PNG, and JPG. Maximum single file upload size is capped at 25MB.'
                }
            ]
        },
        policies: {
            title: 'Policies, SLAs & Escalation Matrix',
            subtitle: 'Service level agreements, dispute arbitration, and academic honesty codes',
            articles: [
                {
                    q: 'What is the Guaranteed 24h SLA?',
                    a: 'SkillBridge commits that all lodged complaints will receive an initial triage review and assigned case manager within 24 operational hours.'
                },
                {
                    q: 'How does the 48-Hour Escalation Safeguard work?',
                    a: 'If a complaint remains in "Pending" status beyond 48 hours, it automatically bypasses standard queues and routes to the Lead Administrative Oversight Council.'
                },
                {
                    q: 'Code of conduct and dispute handling',
                    a: 'Any form of intellectual property infringement, harassment, or ghosting by partner organizations results in immediate suspension following our arbitration process.'
                },
                {
                    q: 'Academic Integrity & Plagiarism Standards',
                    a: 'All project submissions are scanned against repositories. Flagged submissions are reviewed jointly with university faculty representatives.'
                }
            ]
        }
    };

    function openKnowledgeCategory(catKey) {
        const guide = knowledgeGuides[catKey] || (catKey === 'billing' ? knowledgeGuides.skills : null);
        if (!guide) return;

        document.getElementById('hcKnowledgeModalTitle').textContent = guide.title;
        document.getElementById('hcKnowledgeModalSubtitle').textContent = guide.subtitle;

        const body = document.getElementById('hcKnowledgeModalBody');
        let html = '<div style="display:flex; flex-direction:column; gap:14px;">';

        guide.articles.forEach((item, idx) => {
            html += `
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
                    <h4 style="font-size:14px; font-weight:700; color:#0f172a; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#ea580c;">help</span>
                        ${item.q}
                    </h4>
                    <p style="font-size:13px; color:#475569; line-height:1.55; margin:0; padding-left:24px;">
                        ${item.a}
                    </p>
                </div>
            `;
        });

        html += '</div>';
        body.innerHTML = html;

        openModal(knowledgeModal);
    }

    knowledgeCards.forEach(card => {
        card.addEventListener('click', () => {
            const key = card.dataset.categoryKey;
            openKnowledgeCategory(key);
        });
    });

    if (btnEscalation) {
        btnEscalation.addEventListener('click', () => {
            openKnowledgeCategory('policies');
        });
    }

    // Footer Links
    const privacyLink = document.getElementById('footerPrivacyLink');
    const termsLink = document.getElementById('footerTermsLink');
    if (privacyLink) {
        privacyLink.addEventListener('click', (e) => {
            e.preventDefault();
            openKnowledgeCategory('policies');
        });
    }
    if (termsLink) {
        termsLink.addEventListener('click', (e) => {
            e.preventDefault();
            openKnowledgeCategory('policies');
        });
    }

});
