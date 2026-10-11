<div class="im-table-responsive">
    <table class="data-table im-table">
                    <?php 
                        $TableColumns = ["ID", "POSITION", "COMPANY", "INDUSTRY", "REQUIRED SKILLS", "APPLICATIONS", "DEADLINE", "STATUS", "ACTIONS"];
                        include __DIR__ . "/TableHeder.php";
                    ?>
                    <tbody id="imTableBody">

                        <!-- Row 1: Software Engineer Intern -->
                        <tr class="im-data-row"
                            data-id="#INT001"
                            data-position="Software Engineer Intern"
                            data-company="Tech Solutions"
                            data-industry="Software Development"
                            data-skills="Java React SQL"
                            data-status="Active"
                            data-duration="Full-time • Remote"
                            data-reason="">
                            <td class="im-internship-id">#INT001</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Software Engineer Intern</span>
                                    <span class="im-position-type">Full-time • Remote</span>
                                </div>
                            </td>
                            <td class="im-company-name">Tech Solutions</td>
                            <td>
                                <span class="im-industry-badge">Software Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Java</span>
                                    <span class="im-skill-tag">React</span>
                                    <span class="im-skill-tag">SQL</span>
                                </div>
                            </td>
                            <td class="im-applications-count">120</td>
                            <td class="im-deadline-date">30 Aug 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT001')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT001')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2: AI Research Intern -->
                        <tr class="im-data-row"
                            data-id="#INT002"
                            data-position="AI Research Intern"
                            data-company="Innovate Labs"
                            data-industry="Artificial Intelligence"
                            data-skills="Python ML"
                            data-status="Active"
                            data-duration="Part-time • On-site"
                            data-reason="">
                            <td class="im-internship-id">#INT002</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">AI Research Intern</span>
                                    <span class="im-position-type">Part-time • On-site</span>
                                </div>
                            </td>
                            <td class="im-company-name">Innovate Labs</td>
                            <td>
                                <span class="im-industry-badge ai">Artificial Intelligence</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Python</span>
                                    <span class="im-skill-tag">ML</span>
                                </div>
                            </td>
                            <td class="im-applications-count">85</td>
                            <td class="im-deadline-date">15 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT002')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT002')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 3: Mobile Developer Intern -->
                        <tr class="im-data-row"
                            data-id="#INT003"
                            data-position="Mobile Developer Intern"
                            data-company="AppWorks"
                            data-industry="Mobile Development"
                            data-skills="Flutter Firebase"
                            data-status="Active"
                            data-duration="Contract • Hybrid"
                            data-reason="">
                            <td class="im-internship-id">#INT003</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Mobile Developer Intern</span>
                                    <span class="im-position-type">Contract • Hybrid</span>
                                </div>
                            </td>
                            <td class="im-company-name">AppWorks</td>
                            <td>
                                <span class="im-industry-badge mobile">Mobile Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Flutter</span>
                                    <span class="im-skill-tag">Firebase</span>
                                </div>
                            </td>
                            <td class="im-applications-count">64</td>
                            <td class="im-deadline-date">10 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT003')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT003')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 4: Cloud Infrastructure Intern -->
                        <tr class="im-data-row"
                            data-id="#INT004"
                            data-position="Cloud DevOps Intern"
                            data-company="CloudScale Ltd"
                            data-industry="Software Development"
                            data-skills="AWS Docker Kubernetes"
                            data-status="Active"
                            data-duration="Full-time • Hybrid"
                            data-reason="">
                            <td class="im-internship-id">#INT004</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Cloud DevOps Intern</span>
                                    <span class="im-position-type">Full-time • Hybrid</span>
                                </div>
                            </td>
                            <td class="im-company-name">CloudScale Ltd</td>
                            <td>
                                <span class="im-industry-badge">Software Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">AWS</span>
                                    <span class="im-skill-tag">Docker</span>
                                </div>
                            </td>
                            <td class="im-applications-count">92</td>
                            <td class="im-deadline-date">22 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT004')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT004')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 5: Cybersecurity Analyst Intern -->
                        <tr class="im-data-row"
                            data-id="#INT005"
                            data-position="Cybersecurity Analyst Intern"
                            data-company="SecureNet Labs"
                            data-industry="Cybersecurity"
                            data-skills="Network Linux Wireshark"
                            data-status="Active"
                            data-duration="Full-time • On-site"
                            data-reason="">
                            <td class="im-internship-id">#INT005</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Cybersecurity Analyst Intern</span>
                                    <span class="im-position-type">Full-time • On-site</span>
                                </div>
                            </td>
                            <td class="im-company-name">SecureNet Labs</td>
                            <td>
                                <span class="im-industry-badge">Cybersecurity</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Network</span>
                                    <span class="im-skill-tag">Linux</span>
                                </div>
                            </td>
                            <td class="im-applications-count">47</td>
                            <td class="im-deadline-date">28 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT005')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT005')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 6: Data Analytics Intern (Suspended) -->
                        <tr class="im-data-row"
                            data-id="#INT006"
                            data-position="Data Analytics Intern"
                            data-company="Apex Data Corp"
                            data-industry="Software Development"
                            data-skills="Python PowerBI SQL"
                            data-status="Suspended"
                            data-duration="Part-time • Hybrid"
                            data-reason="Reported for deceptive stipend information and policy non-compliance."
                            data-company-action="We have revised the official internship agreement, clearly specified the LKR 50,000 monthly allowance, and agreed to adhere strictly to the platform's fair recruitment guidelines.">
                            <td class="im-internship-id">#INT006</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Data Analytics Intern</span>
                                    <span class="im-position-type">Part-time • Hybrid</span>
                                </div>
                            </td>
                            <td class="im-company-name">Apex Data Corp</td>
                            <td>
                                <span class="im-industry-badge">Software Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Python</span>
                                    <span class="im-skill-tag">PowerBI</span>
                                </div>
                            </td>
                            <td class="im-applications-count">38</td>
                            <td class="im-deadline-date">05 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-suspended">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT006')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-reactivate" title="Review Suspension & Decide" onclick="openReviewSuspensionModal('#INT006')">
                                    <span class="material-symbols-outlined">check_circle</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 7: Cloud Security Specialist (Terminated) -->
                        <tr class="im-data-row"
                            data-id="#INT007"
                            data-position="Cloud Security Specialist"
                            data-company="ShadowSys Global"
                            data-industry="Cybersecurity"
                            data-skills="AWS Security SOC"
                            data-status="Terminated"
                            data-duration="Full-time • Remote"
                            data-reason="Unresolved complaint regarding fraudulent company credentials and refusal to provide business registration."
                            data-company-action=""
                            data-terminate-reason="Failed to provide verified business registration within the given deadline. Opportunity permanently terminated by Administration.">
                            <td class="im-internship-id">#INT007</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Cloud Security Specialist</span>
                                    <span class="im-position-type">Full-time • Remote</span>
                                </div>
                            </td>
                            <td class="im-company-name">ShadowSys Global</td>
                            <td>
                                <span class="im-industry-badge">Cybersecurity</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">AWS</span>
                                    <span class="im-skill-tag">Security</span>
                                </div>
                            </td>
                            <td class="im-applications-count">19</td>
                            <td class="im-deadline-date">12 Aug 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-terminated">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Terminated
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT007')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action" disabled title="Permanently Terminated (Cannot be modified)" style="color: #cbd5e1; cursor: not-allowed;">
                                    <span class="material-symbols-outlined">lock</span>
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
